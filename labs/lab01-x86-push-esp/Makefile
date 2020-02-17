ASSEMBLER=nasm
ASSEMBLER_FLAGS=-f elf32
BIN=main
SRC=main.S

all:
	$(ASSEMBLER) $(ASSEMBLER_FLAGS) -o $(BIN).o $(SRC)
	ld -m elf_i386 -o $(BIN) $(BIN).o

clean:
	rm $(BIN) $(BIN).o
