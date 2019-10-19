## Source code
```
#include <stdio.h>
#include <string.h>

int filter(char* cmd){
	int r=0;
	r += strstr(cmd, "flag")!=0;
	r += strstr(cmd, "sh")!=0;
	r += strstr(cmd, "tmp")!=0;
	return r;
}
int main(int argc, char* argv[], char** envp){
	putenv("PATH=/thankyouverymuch");
	if(filter(argv[1])) return 0;
	system( argv[1] );
	return 0;
}
```

## Pwn
幾個點：

1. PATH 被改了，所以執行指令的時候要給 full path (e.g., /bin/cat)

2. `filter()` 會檢查 argv[1] 中是否包含 flag, sh, tmp 等字串，有任何一個 r 就會 > 0， 例如 `if (2)` -> evaluate to True!

如果沒有 filter() 的話這樣就可以了：
```
$ ./cmd1 "/bin/cat flag"
```

但因爲有 filter()，所以插個空白丟給 system() 執行，''會被吃掉
```
./cmd1 "/bin/cat f''lag"
```

即：
```
const char* cmd = "/bin/cat f''lag";
system(cmd);
```

## Note
1. `const char* substr(const char* s1, const char* s2);` // 從s1找s2，回傳 char ptr to first occurrence || NULL
