## Source code
```
#include <stdio.h>
#include <string.h>

#define yes_len 3
const char *yes = "yes";

int main()
{
    char flag[99];
    char permission[10];
    int i;
    FILE * file;


    file = fopen("/problems/absolutely-relative_0_d4f0f1c47f503378c4bb81981a80a9b6/flag.txt" , "r");
    if (file) {
    	while (fscanf(file, "%s", flag)!=EOF)
    	fclose(file);
    }   
	
    file = fopen( "./permission.txt" , "r");
    if (file) {
    	for (i = 0; i < 5; i++){
            fscanf(file, "%s", permission);
        }
        permission[5] = '\0';
        fclose(file);
    }
    
    if (!strncmp(permission, yes, yes_len)) {
        printf("You have the write permissions.\n%s\n", flag);
    } else {
        printf("You do not have sufficient permissions to view the flag.\n");
    }
    
    return 0;
}
```

1. `/problems/absolutely-relative_0_d4f0f1c47f503378c4bb81981a80a9b6/flag.txt` 會被讀入 `char flag[99]`
2. `./permission.txt` 會被讀入 `char permission[10]`
3. `!strncmp(permission, yes, yes_len)`：如果 permission.txt 內容是 yes，就會印出 flag

## Solution
```
$ cd /tmp; mkdir aesophor; cd aesophor 
$ echo yes > permission.txt
$ /problems/absolutely-relative_0_d4f0f1c47f503378c4bb81981a80a9b6/absolutely-relative
You have the write permissions.                                                                                
picoCTF{3v3r1ng_1$_r3l3t1v3_befc0ce1}
```

flag: `picoCTF{3v3r1ng_1$_r3l3t1v3_befc0ce1}`
