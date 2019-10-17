# Unpack the executable with upx
```
$ upx -d flag
```

# Run gdb (output omitted)
```
$ gdb ./flag
(gdb) disas main
(gdb) b *main+39
(gdb) r
```

The address of the flag (a Cstring) is at 0x6c2070
```
(gdb) x/s *0x6c2070
```
