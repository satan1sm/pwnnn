#!/usr/bin/env sh

ssh random@pwnable.kr -p2222 "echo `./crack_pw` | ./random"
