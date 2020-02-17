```
(gdb) disas _start
Dump of assembler code for function _start:
   0x08049000 <+0>:	push   esp
   0x08049001 <+1>:	add    esp,0x4
   0x08049004 <+4>:	xor    eax,eax
   0x08049006 <+6>:	mov    al,0x1
   0x08049008 <+8>:	xor    ebx,ebx
   0x0804900a <+10>:	int    0x80
End of assembler dump.
(gdb) break *_start+0
Breakpoint 1 at 0x8049000
(gdb) r
Starting program: /mnt/data/Code/pwnnn/x86-push-esp/main 

Breakpoint 1, 0x08049000 in _start ()
(gdb) x $esp
0xffffc870:	0x00000001
(gdb) si
0x08049001 in _start ()
(gdb) disas _start
Dump of assembler code for function _start:
   0x08049000 <+0>:	push   esp
=> 0x08049001 <+1>:	add    esp,0x4
   0x08049004 <+4>:	xor    eax,eax
   0x08049006 <+6>:	mov    al,0x1
   0x08049008 <+8>:	xor    ebx,ebx
   0x0804900a <+10>:	int    0x80
End of assembler dump.
(gdb) x $esp
0xffffc86c:	0xffffc870
(gdb)
```
