.intel_syntax noprefix
.bits 32
	
.global asm2

asm2:
	push   	ebp
	mov    	ebp,esp
	sub    	esp,0x10 ; reserve a 16-byte buffer
	mov    	eax,DWORD PTR [ebp+0xc] ; eax = arg2
	mov 	DWORD PTR [ebp-0x4],eax ; local_var1 = eax
	mov    	eax,DWORD PTR [ebp+0x8] ; eax = arg1
	mov	DWORD PTR [ebp-0x8],eax ; local_var2 = eax
	jmp    	part_b
part_a:	
	add    	DWORD PTR [ebp-0x4],0x1 ; local_var++;
	add	DWORD PTR [ebp+0x8],0x64 ; arg2 += 0x64
part_b:	
	cmp    	DWORD PTR [ebp+0x8],0x1d89 ; arg2 <= 0x1d89
	jle    	part_a
	mov    	eax,DWORD PTR [ebp-0x4] ; return local_var1
	mov	esp,ebp
	pop	ebp
	ret

; asm2(0x4, 0x2d) 

; eax = 0x4
; arg1 = 0x4
; arg2 = 0x2d += 0x64

; local_var1 -> ebp-0x4 = 0x2d ++
; local_var2 -> ebp-0x8 = 0x4

; 0x2d + 0x64 * n >= 0x1d89
; 0x64 * n >= 0x1d5c
; n >= 0x1d5c / 0x64
; n >= 75.16, n = 76
; eax = 0x2d + 76 = 121 = 0x79
