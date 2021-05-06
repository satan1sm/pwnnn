CXX=g++
CXXFLAGS=-g -std=c++17 -O0 -no-pie -D_GLIBCXX_USE_CXX11_ABI=0
SRC=divinity.cc
BIN=divinity

all:
	$(CXX) -o $(BIN) $(SRC) $(CXXFLAGS)

clean:
	rm $(BIN)

run:
	./$(BIN)
