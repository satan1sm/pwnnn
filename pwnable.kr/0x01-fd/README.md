## Source code
```
#include <stdio.h>
#include <stdlib.h>
#include <string.h>
char buf[32];
int main(int argc, char* argv[], char* envp[]){
	if(argc<2){
		printf("pass argv[1] a number\n");
		return 0;
	}
	int fd = atoi( argv[1] ) - 0x1234;
	int len = 0;
	len = read(fd, buf, 32);
	if(!strcmp("LETMEWIN\n", buf)){
		printf("good job :)\n");
		system("/bin/cat flag");
		exit(0);
	}
	printf("learn about Linux file IO\n");
	return 0;

}
```

## Pwn
1. 程式需要一個 command line argument

2. 傳入的參數被 `atoi()` 轉數字，0x1234 = 4660，所以傳入4660可得到 stdin 的 fd

3. 從 stdin 進行 `read()` 的時候直接輸入 LETMEWIN 即可拿到 flag
