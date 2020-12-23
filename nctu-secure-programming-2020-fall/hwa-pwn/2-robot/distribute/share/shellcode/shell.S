SYS_READ   equ 0
SYS_WRITE  equ 1
STDIN      equ 0
STDOUT     equ 1
STDERR     equ 2
PIPE_READ  equ 3
PIPE_WRITE equ 6
BUF_SIZE   equ 256


; Clear buffer `buf`
%macro clear_buf 0
  lea rbx, [rel buf]
  xor rsi, rsi
%%next:
  mov byte [rbx+rsi], 0x00
  inc rsi
  cmp rsi, BUF_SIZE
  jl  %%next
%endmacro

%macro invoke_syscall 4
  mov rax, %1
  mov rdi, %2
  lea rsi, [rel %3]
  mov rdx, %4
  syscall
%endmacro


[section .special write exec]
[global _start]
_start:
  clear_buf
  ; Read data from stdin into `buf`
  invoke_syscall SYS_READ, STDIN, buf, BUF_SIZE
  ; Write `buf` content to parent via pipe
  invoke_syscall SYS_WRITE, PIPE_WRITE, buf, BUF_SIZE
  clear_buf
  ; Read data from parent via pipe into `buf`
  invoke_syscall SYS_READ, PIPE_READ, buf, BUF_SIZE
  ; Write `buf` content to STDOUT
  invoke_syscall SYS_WRITE, STDOUT, buf, BUF_SIZE
  jmp _start

; --------------------------------------------
strW: db "W", 0
strS: db "S", 0
strA: db "A", 0
strD: db "D", 0
addr: dq 0
buf:  db 64 dup(0)
