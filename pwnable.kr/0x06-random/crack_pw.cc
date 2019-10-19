#include <iostream>

int main(int argc, char* args[]) {
  std::cout << (0xdeadbeef ^ rand()) << std::endl;
}
