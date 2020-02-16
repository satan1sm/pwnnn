CXX=gcc
CXXFLAGS=-m32 -fno-stack-protector
SRC=main.c
BIN=main

all:
	$(CXX) -o $(BIN) $(SRC) $(CXXFLAGS)

clean:
	rm $(BIN)

run:
	./$(BIN)
