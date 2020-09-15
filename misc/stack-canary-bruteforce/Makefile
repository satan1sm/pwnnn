CXX=gcc
CXXFLAGS=-m64 -no-pie
SRC=server.c
BIN=server

all:
	$(CXX) -o $(BIN) $(SRC) $(CXXFLAGS)

clean:
	rm $(BIN)

run:
	./$(BIN)
