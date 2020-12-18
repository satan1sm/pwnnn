AS = nasm
ASFLAGS = -f elf64 -g -F stabs

LD = ld
LDFLAGS = -melf_x86_64

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
	for i in $$(objdump -d $(SRC).o --no-addresses | tr '\t' ' ' | tr ' ' '\n' | egrep '^[0-9a-f]{2}$$'); do \
	  echo -ne "\\x$$i" >> $(BIN); \
	done
	#for i in $$(objdump -d $(SRC).o | tr '\t' ' ' | tr ' ' '\n' | egrep '^[0-9a-f]{2}$$' ) ; do \
	#  echo -ne "\\x$$i" >> $(BIN);\
	#done
