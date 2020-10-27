AS = nasm
ASFLAGS = -f elf32 -g -F stabs

LD = ld
LDFLAGS = -melf_i386

SRC = shell
BIN = shell.bin

all:
	make clean
	make asm
	make bin

asm:
	$(AS) $(ASFLAGS) $(SRC).S -o $(SRC).o

elf:
	make asm
	$(LD) $(LDFLAGS) -o $(BIN) $(SRC).o

clean:
	rm -f $(BIN) $(SRC).o

bin:
	for i in $$(objdump -d $(SRC).o | tr '\t' ' ' | tr ' ' '\n' | egrep '^[0-9a-f]{2}$$'); do \
	  echo -ne "\\x$$i" >> $(BIN); \
	done
