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
