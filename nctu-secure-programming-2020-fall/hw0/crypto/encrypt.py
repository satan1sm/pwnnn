#!/usr/bin/env python3
import time
import random
import typing

def pos(data, size=4):
    ret = []
    for x in range(0, len(data), size):
        print('-->' + str(x))
        ret.append( int.from_bytes(data[x:x+size], 'big') )
    return ret

def neg(data, size=4):
    return b''.join([e.to_bytes(size, 'big') for e in data])


def _encrypt(v: typing.List[int], key: typing.List[int]):
    counter, delta, mask = 0, 0xFACEB00C, 0xffffffff
    for i in range(32):
        counter = counter + delta & mask
        v[0] += ((v[1] << 4) + key[0] ^ (v[1] + counter) & mask ^ (v[1] >> 5) + key[1] & mask) & mask
        v[1] += ((v[0] << 4) + key[2] ^ (v[0] + counter) & mask ^ (v[0] >> 5) + key[3] & mask) & mask
    return v

def encrypt(clear_text: bytes, key: bytes):
    cipher_text = b''
    for i in range(0, len(clear_text), 8):
        cipher_text += neg(_encrypt(pos(clear_text[i:i+8]), pos(key)))
    return cipher_text


if __name__ == '__main__':
    flag = open('flag', 'rb').read()
    assert len(flag) == 16
    random.seed(int(time.time()))
    key = random.getrandbits(128).to_bytes(16, 'big')
    cipher_text = encrypt(flag, key)
    print(f'密文 = {cipher_text.hex()}')
