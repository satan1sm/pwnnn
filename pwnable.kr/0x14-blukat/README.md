## Source code
```
#include <stdio.h>
#include <string.h>
#include <stdlib.h>
#include <fcntl.h>
char flag[100];
char password[100];
char* key = "3\rG[S/%\x1c\x1d#0?\rIS\x0f\x1c\x1d\x18;,4\x1b\x00\x1bp;5\x0b\x1b\x08\x45+";
void calc_flag(char* s){
	int i;
	for(i=0; i<strlen(s); i++){
		flag[i] = s[i] ^ key[i];
	}
	printf("%s\n", flag);
}
int main(){
	FILE* fp = fopen("/home/blukat/password", "r");
	fgets(password, 100, fp);
	char buf[100];
	printf("guess the password!\n");
	fgets(buf, 128, stdin);
	if(!strcmp(password, buf)){
		printf("congrats! here is your flag: ");
		calc_flag(password);
	}
	else{
		printf("wrong guess!\n");
		exit(0);
	}
	return 0;
}
```

## Pwn
源碼看來看去，沒什麼可以利用的地方（抱歉...我嫩），於是就 scp 下載 password 到本地端。
```
$ scp -P 2222 blukat@pwnable.kr:/home/blukat/password .
blukat@pwnable.kr's password:
password    
                                                                       100%   33     0.2KB/s   00:00  
```

按理來說，如果權限不足，scp 應該會提示：Permission denied。但竟然下載成功了！？檔案內容如下
```
$ cat password
cat: password: Permission denied
```

其實這就是密碼...貼進去就噴 flag 了 =_=#...

仔細一看，發現 blukat 用戶同時也在 blukat_pwn 羣組內，而 `-rw-r-----` 說明我們是有權限可以讀取 password 的。
```
blukat@prowl:~$ groups
blukat blukat_pwn

blukat@prowl:~$ ls -la
total 36
drwxr-x---   4 root blukat     4096 Aug 16  2018 .
drwxr-xr-x 114 root root       4096 May 19 15:59 ..
-r-xr-sr-x   1 root blukat_pwn 9144 Aug  8  2018 blukat
-rw-r--r--   1 root root        645 Aug  8  2018 blukat.c
dr-xr-xr-x   2 root root       4096 Aug 16  2018 .irssi
-rw-r-----   1 root blukat_pwn   33 Jan  6  2017 password
drwxr-xr-x   2 root root       4096 Aug 16  2018 .pwntools-cache
blukat@prowl:~$
```

flag: `Pl3as_DonT_Miss_youR_GrouP_Perm!!`
