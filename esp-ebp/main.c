void func(int param1, int param2, int param3) {
  int local = 0x666;
  local++;
}

int main(int argc, char *argv[]) {
  func(1, 2, 3);
}
