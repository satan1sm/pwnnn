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

## Pwn
首先用 GNU Make 做出 shell.bin 之後 scp 上傳到 server:/tmp 下自己的目錄

直接把 shell.bin 的內容 pipe 到 process 裏
```
asm@prowl:~$ cat /tmp/aesophor_asm/shell.bin | nc 0 9026
Welcome to shellcoding practice challenge.
In this challenge, you can run your x64 shellcode under SECCOMP sandbox.
Try to make shellcode that spits flag using open()/read()/write() systemcalls only.
If this does not challenge you. you should play 'asg' challenge :)
give me your x64 shellcode: Mak1ng_shelLcodE_i5_veRy_eaSy
```

flag: `Mak1ng_shelLcodE_i5_veRy_eaSy`
