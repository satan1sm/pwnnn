AS = nasm
ASFLAGS = -f elf64 -g -F stabs

SRC = shell
BIN = shell.bin

all:
	make clean
	make shellcode
	make bin

shellcode:
	$(AS) $(ASFLAGS) $(SRC).S -o $(SRC).o
	
bin:
	for i in $$(objdump -d $(SRC).o | tr '\t' ' ' | tr ' ' '\n' | egrep '^[0-9a-f]{2}$$'); do \
	  echo -ne "\\x$$i" >> $(BIN); \
	done

clean:
	rm -f $(BIN) $(SRC).o
