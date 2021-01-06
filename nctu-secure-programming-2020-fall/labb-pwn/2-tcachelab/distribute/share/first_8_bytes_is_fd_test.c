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
