## Source code
```
.intel_syntax noprefix
.bits 32
	
.global asm0

asm0:
	push	ebp
	mov	ebp,esp
	mov	eax,DWORD PTR [ebp+0x8] ; function arg1
	mov	ebx,DWORD PTR [ebp+0xc] ; function arg2
	mov	eax,ebx ; eax = function return value
	mov	esp,ebp ; leave
	pop	ebp	; leave
	ret
```

## Solution
求 `asm0(0xc9, 0xb0)` 的回傳值？

在 x86 Linux 裏：
* ebp 存 stack base pointer (指向當前 function 的 stack frame 底端, esp 指向頂端)
* ebp + 0x4 存 function return address
* ebp + 0x8 * 0 存 function arg1
* ebp + 0x8 * 1 存 function arg2

ans: `0xb0`

這題也太水了吧 =_=# ....
