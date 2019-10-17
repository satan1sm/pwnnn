#!/usr/bin/env sh

pw=`./crack_pw`
ssh random@pwnable.kr -p2222 "echo $pw | ./random"
