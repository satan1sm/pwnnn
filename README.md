## Binary Exploitation Learning Notes and Resources
Before starting to learn binary exploitation, one is recommended to learn at least:
1. C
2. Python
3. x86 Assembly
4. OS concepts (especially virtual memory layout)
5. Debugging programs with gdb

* [Introduction To Reverse Engineering With Radare2](https://www.youtube.com/watch?v=LAkYW5ixvhg&t=669s)
* [Reversing and Cracking first simple Program - bin 0x05](https://www.youtube.com/watch?v=VroEiMOJPm8)

<br>

### Fundamentals
1. [Understand x86 Stack Frames](https://github.com/aesophor/pwnnn/tree/master/labs/lab00-x86-stack-frame-tracing)
2. [checksec (RELRO, Stack, NX, PIE)](https://github.com/aesophor/pwnnn/tree/master/rop-emporium/1-split)
3. [ASLR vs PIE](https://github.com/aesophor/pwnnn/tree/master/rop-emporium/1-split)
4. [Static vs Dynamic Linking](https://github.com/aesophor/pwnnn/tree/master/rop-emporium/2-callme)
5. [GOT, PLT, Lazy Binding](https://github.com/aesophor/pwnnn/tree/master/rop-emporium/2-callme)

<br>

### Return Oriented Programming (ROP)
1. [Use buffer overflow to replace a return address](https://github.com/aesophor/pwnnn/tree/master/rop-emporium/0-ret2win)
2. [ret2libc](https://github.com/aesophor/pwnnn/blob/master/picoctf-2018/got-2-learn-libc/exploit.py)
3. [Chain functions with arguments](https://github.com/aesophor/pwnnn/tree/master/rop-emporium/2-callme#结论)
4. [Find Gadgets](https://github.com/aesophor/pwnnn/tree/master/rop-emporium/3-write4#rop-gadgets)
5. [Use readelf to find writable sections](https://github.com/aesophor/pwnnn/tree/master/rop-emporium/3-write4#pwn)
6. [Stack Pivot](https://github.com/satan1sm/pwnnn/tree/master/rop-emporium/6-pivot#stack-pivot)
7. [x86_64 Return-to-csu](https://github.com/satan1sm/pwnnn/tree/master/rop-emporium/7-ret2csu#return-to-csu)

<br>

### Heap Exploitation
1. [Use-after-free](https://github.com/aesophor/pwnnn/tree/master/pwnable.kr/0x10-uaf)

<br>

### Shellcode
1. [Bamboofox Trainning 2015 Summer - Shellcode](https://www.youtube.com/watch?v=auv-64HUBw8)
