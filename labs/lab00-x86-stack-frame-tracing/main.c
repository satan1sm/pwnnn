void func(int num1, int num2) {
  int local1 = 0x666;
  int local2 = 0x777;
}

int main(int argc, char *argv[], char *envp[]) {
  func(1, 2);
}
