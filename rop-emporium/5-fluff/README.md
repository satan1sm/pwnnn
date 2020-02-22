> There's not much more to this challenge, we just have to think about ways to move data into the registers we want to control.
Sometimes we'll need to take an indirect approach

有点挑战性的一题，和 3-write4 一样要自己写一个 "/bin/sh\x00" 到内存某处，然后 ret 到 `system("/bin/sh")`。

只是这次的 ROP Gadgets 不像之前的那么直观、好用了，必须绕点路。

<br>

## Reverse
列举一下 functions，bof 的点一样是在 pwnme() 里调用 fgets() 处。
```
pwndbg> info func
（省略若干行）
0x0804857b  main
0x080485f6  pwnme
0x0804864c  usefulFunction
0x08048670  questionableGadgets
```

看一下 questionableGadgets。经过一番思考，我总共找到四个有用的 gadgets，但其中一个不在这里，待会会提到。
```
pwndbg> disas questionableGadgets
Dump of assembler code for function questionableGadgets:
   0x08048670 <+0>:     pop    edi
   0x08048671 <+1>:     xor    edx,edx
   0x08048673 <+3>:     pop    esi
   0x08048674 <+4>:     mov    ebp,0xcafebabe
   0x08048679 <+9>:     ret
   0x0804867a <+10>:    pop    esi
   0x0804867b <+11>:    xor    edx,ebx             <------ gadget3
   0x0804867d <+13>:    pop    ebp
   0x0804867e <+14>:    mov    edi,0xdeadbabe
   0x08048683 <+19>:    ret
   0x08048684 <+20>:    mov    edi,0xdeadbeef
   0x08048689 <+25>:    xchg   edx,ecx             <------ gadget4
   0x0804868b <+27>:    pop    ebp
   0x0804868c <+28>:    mov    edx,0xdefaced0
   0x08048691 <+33>:    ret
   0x08048692 <+34>:    pop    edi
   0x08048693 <+35>:    mov    DWORD PTR [ecx],edx <------ gadget1
   0x08048695 <+37>:    pop    ebp
   0x08048696 <+38>:    pop    ebx
   0x08048697 <+39>:    xor    BYTE PTR [ecx],bl
   0x08048699 <+41>:    ret
   0x0804869a <+42>:    xchg   ax,ax
   0x0804869c <+44>:    xchg   ax,ax
   0x0804869e <+46>:    xchg   ax,ax
End of assembler dump.
```

接着用 ROPGadget 搜索所有 Gadgets，又找到找到一个可用的，我们就叫他 gadget2。
```
$ ROPGadget --binary fluff32 | grep 'pop ebx'
（省略若干行）
0x080483e1 : pop ebx ; ret                         <------ gadget2
（省略若干行）
```

<br>

## ROP Gadgets
各个 Gadgets 的功用：
1. gadget1：这里给了我们一个方法，把数据写入内存，但我们必须能控制 ecx, edx。写入后会 `xor byte ptr [ecx], bl`
2. gadget4：`xhcg ecx, edx`，并且 `mov edx, 0xdefaced0`
3. gadget3：`xor edx, ebx`
4. gadget2：让我们能控制 ebx 值

有了这四个 gadgets，就可以写入任意数据到内存了，只是有点绕。。。
1. 先用 gadget4，设定 edx = 0xdefaced0
2. 再用 gadget2，设定 ebx = 我们给的值
3. 再用 gadget3，xor edx, ebx，异或结果会存在 edx
4. 最后 gadget4，`xhcg ecx, edx` 把 edx 结果存入 ecx，然后重新设定 edx = 0xdefaced0

到这里，我们已经有办法把 address 写入 ecx 了，再着：
1. 再用 gadget2，设定 ebx = 我们给的值
2. 再用 gadget3，xor edx, ebx，异或结果会存在 edx
3. 最后 gadget1，`mov [ecx], edx`

就完成一个 32-bit 数据的写入（到内存）。

<br>

## Pwn
那么 ebx 怎么设呢？很明显，我们的 "xor_key" 就是 0xdefaced0，假设今天我们要传入 "/bin"（0x6e69622f）
```
0x6e69622f ^ 0xdefaced0 = 0xb093acff
0xb093acff ^ 0xdefaced0 = 0x6e69622f
```

有发现吗？只要 xor_key 不变，XOR 两次之后就会变成最初的数字。因此如果想传入 0x6e69622f，我们传 0xb093acff 就行了。
但有个坑点要注意，因为 gadget1 在执行内存写入之后，还会 `xor byte ptr [ecx], bl`，
只要我们让 bl = 0xd0（也就是 defaced0 最后这个 d0），那么第一个 byte 就会被用同一个 xor_key XOR 两次。
既然会被 XOR 两次，这代表我们第一个 byte 直接传入 2f 就行了而不是 ff。

0xb093ac"**ff**" -> 0xb093ac“**2f**”

<br>

目前我们已经实现了「写入任意数据到内存的任意位址」，我们如果要写入 "/bin/sh\x00"，只要进行两次以上的步骤即可。

至于要写入哪里，一样用 `readelf -S fluff32`，找到 .data 的头位址，写入该处即可。（写入其他 writable sections 也可）
