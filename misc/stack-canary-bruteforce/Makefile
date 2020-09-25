CXX=gcc
CXXFLAGS=-m64 -no-pie -g -O0 -Wl,-z,relro,-z,now 
SRC=server.c
BIN=server

all:
	$(CXX) -o $(BIN) $(SRC) $(CXXFLAGS)

clean:
	rm $(BIN)

run:
	./$(BIN)
