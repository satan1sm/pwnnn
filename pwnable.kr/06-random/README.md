## Source code
```
#include <stdio.h>

int main(){
	unsigned int random;
	random = rand();	// random value!

	unsigned int key=0;
	scanf("%d", &key);

	if( (key ^ random) == 0xdeadbeef ){
		printf("Good!\n");
		system("/bin/cat flag");
		return 0;
	}

	printf("Wrong, maybe you should try 2^32 cases.\n");
	return 0;
}
```

## Pwn
1. `rand()` 前沒有呼叫 `srand(time(NULL))`，所以 `rand()` 的值是可預測的: 0xb526fb88

2. XOR Encryption/Decryption:
   * 加密：cleartext ^ secret_key ==> ciphertext
   * 解密：ciphertext ^ secret_key ==> cleartext
   
   所以令 x 爲我們的 input，透過下列關系可找出x的值
   ```
   x ^ random ==> 0xdeadbeef
   0xdeadbeef ^ random ==> x
   ```
   
   x = 0xdeadbeef ^ 0xb526fb88 = 3039230856
   
3. Pwn
   ```
   echo 3039230856 | ./random
   ```
