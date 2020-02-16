```
void func(int param1, int param2, int param3) {
  int local = 0x666;
  local++;
}

int main(int argc, char *argv[]) {
  func(1, 2, 3);
}
```

```
> gdb ./main
pwndbg: loaded 174 commands. Type pwndbg [filter] for a list.
pwndbg: created $rebase, $ida gdb functions (can be used with print/break)
Reading symbols from ./main...
(No debugging symbols found in ./main)
pwndbg> discontextup
Undefined command: "discontextup".  Try "help".
pwndbg> info func
All defined functions:

Non-debugging symbols:
0x00001000  _init
0x00001030  __stack_chk_fail@plt
0x00001040  __libc_start_main@plt
0x00001050  __cxa_finalize@plt
0x00001060  _start
0x000010a0  __x86.get_pc_thunk.bx
0x00001195  __x86.get_pc_thunk.dx
0x00001199  func
0x000011b7  main
0x000011d9  __x86.get_pc_thunk.ax
0x000011e0  __libc_csu_init
0x00001270  __libc_csu_fini
0x000012a0  __stack_chk_fail_local
0x000012c0  _fini
pwndbg> disas main
Dump of assembler code for function main:
   0x000011b7 <+0>:	push   ebp
   0x000011b8 <+1>:	mov    ebp,esp
   0x000011ba <+3>:	call   0x11d9 <__x86.get_pc_thunk.ax>
   0x000011bf <+8>:	add    eax,0x2e41
   0x000011c4 <+13>:	push   0x3
   0x000011c6 <+15>:	push   0x2
   0x000011c8 <+17>:	push   0x1
   0x000011ca <+19>:	call   0x1199 <func>
   0x000011cf <+24>:	add    esp,0xc
   0x000011d2 <+27>:	mov    eax,0x0
   0x000011d7 <+32>:	leave  
   0x000011d8 <+33>:	ret    
End of assembler dump.
pwndbg> disas func
Dump of assembler code for function func:
   0x00001199 <+0>:	push   ebp
   0x0000119a <+1>:	mov    ebp,esp
   0x0000119c <+3>:	sub    esp,0x10
   0x0000119f <+6>:	call   0x11d9 <__x86.get_pc_thunk.ax>
   0x000011a4 <+11>:	add    eax,0x2e5c
   0x000011a9 <+16>:	mov    DWORD PTR [ebp-0x4],0x666
   0x000011b0 <+23>:	add    DWORD PTR [ebp-0x4],0x1
   0x000011b4 <+27>:	nop
   0x000011b5 <+28>:	leave  
   0x000011b6 <+29>:	ret    
End of assembler dump.
pwndbg>
```
