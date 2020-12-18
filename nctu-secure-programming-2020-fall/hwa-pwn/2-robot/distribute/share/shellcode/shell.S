SYS_READ   equ 0
SYS_WRITE  equ 1
PIPE_READ  equ 0
PIPE_WRITE equ 1
BUF_SIZE   equ 64

[section .special write exec]
[global _start]
_start:
  ; Copy shellcode's start address to `addr`
  mov [rel addr], rdx

loop:
  ; Clear `buf`
  lea rbx, [rel buf]
  xor rsi, rsi
__clear_buf:
  mov byte [rbx+rsi], 0x00
  inc rsi
  cmp rsi, BUF_SIZE
  jl  __clear_buf

  ; Read data from pipe into `buf`
  mov rax, SYS_READ
  mov rdi, PIPE_READ
  lea rsi, [rel buf]
  mov rdx, BUF_SIZE
  syscall

  ; write(int fd, const char *buf, size_t count)
  mov rax, SYS_WRITE
  mov rdi, PIPE_WRITE
  lea rsi, [rel buf]
  mov rdx, BUF_SIZE
  syscall
  jmp loop

  ; --------------------------------------------
  strW: db "W", 0
  strS: db "S", 0
  strA: db "A", 0
  strD: db "D", 0
  addr: dq 0
  buf:  db 64 dup(0)
