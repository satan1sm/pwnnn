## Source code
```
#include <stdio.h>
#include <string.h>
unsigned long hashcode = 0x21DD09EC;
unsigned long check_password(const char* p){
	int* ip = (int*)p;
	int i;
	int res=0;
	for(i=0; i<5; i++){
		res += ip[i];
	}
        printf("user hashcode: 0x%08x\n", res);
	return res;
}

int main(int argc, char* argv[]){
	if(argc<2){
		printf("usage : %s [passcode]\n", argv[0]);
		return 0;
	}
	if(strlen(argv[1]) != 20){
		printf("passcode length should be 20 bytes\n");
		return 0;
	}

	if(hashcode == check_password( argv[1] )){
		system("/bin/cat flag");
		return 0;
	}
	else
		printf("wrong passcode.\n");
	return 0;
}
```

## Pwn
1. 題目要我們輸入20 characters的passcode

2. 如果 `check_password()` 所 return 的 hashcode 爲 0x21DD09EC，就會印出 flag

3. `check_password()` will cast passed pointer into int\*，20/4=5，將5個 int 加總計算 user hashcode

4. 問題：要傳什麼進去？
   * 0x21dd09ec / 5 = 163626824.8 無法整除
   * 但我們可以讓前四個數字都是 163626824 = 0x6c5cec8
   * 前四個數字的總和：4 * 0x6c5cec8 = 0x1b173b20
   * 所以最後一個數字：0x21dd09ec - 0x1b173b20 = 0x06c5cecc
   * 這樣一來：0x1b173b20 + 0x06c5cecc = 0x21dd09ec
   
5. 打造payload，數字部分需注意 x86 儲存數字的方式是 little endian（low to low, high to high, i.e., 低位數在前面）
   ```
   $ ./col `python2 -c 'print 4 * "\xc8\xce\xc5\x06" + "\xcc\xce\xc5\x06"'`
   ```
