[global _start]
  
_start:
  jmp ed

st:
  ; execve(const char *filename, const char * const argv[], const char * const envp[])
  pop ebx
  xor eax, eax
  add al, 0xb
  xor ecx, ecx
  xor edx, edx
  int 0x80
  ret
 
ed:
  call st
  db "/bin/sh", 0
