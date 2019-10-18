## Source code
```
#include <stdio.h>
#include <fcntl.h>

#define PW_LEN 10
#define XORKEY 1

void xor(char* s, int len){
	int i;
	for(i=0; i<len; i++){
		s[i] ^= XORKEY;
	}
}

int main(int argc, char* argv[]){
	
	int fd;
	if(fd=open("/home/mistake/password",O_RDONLY,0400) < 0){
		printf("can't open password %d\n", fd);
		return 0;
	}

	printf("do not bruteforce...\n");
	sleep(time(0)%20);

	char pw_buf[PW_LEN+1];
	int len;
	if(!(len=read(fd,pw_buf,PW_LEN) > 0)){
		printf("read error\n");
		close(fd);
		return 0;		
	}

	char pw_buf2[PW_LEN+1];
	printf("input password : ");
	scanf("%10s", pw_buf2);

	// xor your input
	xor(pw_buf2, 10);

	if(!strncmp(pw_buf, pw_buf2, PW_LEN)){
		printf("Password OK\n");
		system("/bin/cat flag\n");
	}
	else{
		printf("Wrong Password\n");
	}

	close(fd);
	return 0;
}
```

## Pwn
題目提示 operator precedence，有一行很可疑：
```
if(fd = open("/home/mistake/password",O_RDONLY,0400) < 0){
```

簡化一下：
```
if (fd = open(...) < 0) {
```

由於 operator< 的優先權大於 operator=，所以這行等於
```
if (fd = (open(...) < 0)) {
```

正常來說，`open()` 成功的情況下會回傳一個file descriptor，失敗才會 < 0

所以：open() < 0 嗎？ False (i.e., 0)

fd = 0

因此後面 `read()` 的部分其實會從 stdin 讀取，而非 /home/mistake/password

回顧一下 XOR Truth table：

| x | y | F |
|---|---|---|
| 0 | 0 | 0 |
| 0 | 1 | 1 |
| 1 | 0 | 1 |
| 1 | 1 | 0 |


`x ^ 1111111111 = y`，其中 x,y 我們可控，所以我們可以湊出這個等式（非唯一答案）：

```
0000000000 ^ 1111111111 = 1111111111
```

Pwn
```
echo -e "1111111111\n0000000000" | ./mistake
```


