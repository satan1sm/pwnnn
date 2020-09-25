#!/usr/bin/env python3
import time
import random
import typing
import sys

def pos(data, size=4):
    ret = []
    for x in range(0, len(data), size):
        ret.append( int.from_bytes(data[x:x+size], 'big') )
    return ret

def neg(data, size=4):
    s =  b''.join([e.to_bytes(size, 'big') for e in data])
    return s


def _encrypt(v: typing.List[int], key: typing.List[int]):
    counter, delta, mask = 0, 0xFACEB00C, 0xffffffff
    for i in range(32):
        counter = counter + delta & mask
        v[0] = v[0] + ((v[1] << 4) + key[0] & mask ^ (v[1] + counter) & mask ^ (v[1] >> 5) + key[1] & mask) & mask
        v[1] = v[1] + ((v[0] << 4) + key[2] & mask ^ (v[0] + counter) & mask ^ (v[0] >> 5) + key[3] & mask) & mask
    return v

def _decrypt(v, key):
    deltas = get_delta()
    mask = 0xffffffff
    for i in range(32):
        counter = deltas[31-i]
        v[1] = v[1] - ((v[0] << 4) + key[2] & mask ^ (v[0] + counter) & mask ^ (v[0] >> 5) + key[3] & mask) & mask
        v[0] = v[0] - ((v[1] << 4) + key[0] & mask ^ (v[1] + counter) & mask ^ (v[1] >> 5) + key[1] & mask) & mask
    return v

def get_delta():
    counter, delta, mask = 0, 0xFACEB00C, 0xffffffff
    deltas = []

    for i in range(32):
        counter = counter + delta & mask
        deltas.append(counter)
    return deltas

def decrypt(cipher_text, key):
    clear_text = b''
    for i in range(0, len(cipher_text), 8):
        clear_text += neg(_decrypt(pos(cipher_text[i:i+8]), pos(key)))
    return clear_text

def _encrypt(v: typing.List[int], key: typing.List[int]):
    counter, delta, mask = 0, 0xFACEB00C, 0xffffffff
    for i in range(32):
        counter = counter + delta & mask
        v[0] = v[0] + ((v[1] << 4) + key[0] & mask ^ (v[1] + counter) & mask ^ (v[1] >> 5) + key[1] & mask) & mask
        v[1] = v[1] + ((v[0] << 4) + key[2] & mask ^ (v[0] + counter) & mask ^ (v[0] >> 5) + key[3] & mask) & mask

    return v

def encrypt(clear_text: bytes, key: bytes):
    cipher_text = b''
    for i in range(0, len(clear_text), 8):
        cipher_text += neg(_encrypt(pos(clear_text[i:i+8]), pos(key)))
        print(cipher_text[:8])
    return cipher_text

if __name__ == '__main__':
    rand_seed = int(time.time())

    while True:
        random.seed(rand_seed)
        key = random.getrandbits(128).to_bytes(16, 'big')
        clear_text = decrypt(bytes.fromhex("77f905c39e36b5eb0deecbb4eb08e8cb"), key)

        print(rand_seed)
        if clear_text.lower().startswith(b'flag'):
            print(clear_text)
            sys.exit(0)
        else:
            rand_seed -= 1
