[section .data]
  greet db "Hello world", 0

[section .text]
[global _start]
_start:
  ; Question: If push x is equivilent to...
  ; sub esp, 4
  ; mov [esp], x
  ;
  ; then what's the value which will be pushed
  ; by executing `push esp`?
  ;
  ; Ans: the original value held by esp will be pushed,
  ; not the value after `sub esp, 4`
  push esp
  add esp, 4  ; release our stuff

  ; sys_exit(0)
  xor eax, eax
  mov al, 1
  xor ebx, ebx
  int 0x80
