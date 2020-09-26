#include <stdio.h>
#include <string.h>

int main()
{
        char buf[32];
        memset(buf, 0x00, 32);
        scanf("%s", buf);

        for (int i = 0; i < 32; i++) {
                putchar(buf[i]);
        }
        return 0;
}

// $ python2 -c "print 15 * 'A' + '\x00' + 16 * 'B'" | ./scanf_test
// AAAAAAAAAAAAAAABBBBBBBBBBBBBBBB%

// $ python2 -c "print '\x00' + 16 * 'B'" | ./scanf_test
// BBBBBBBBBBBBBBBB% 

// $ python2 -c "print 'b' + '\x0d' + 16 * 'B'" | ./scanf_test
// b%
