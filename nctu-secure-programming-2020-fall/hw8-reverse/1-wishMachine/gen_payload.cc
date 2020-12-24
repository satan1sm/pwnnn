#include<iostream>
#include<vector>
#include<fstream>
#include<cstring>
#include<cstdio>

static void fibonacci(int i);
static void func_40102D(int i);
static void func_401138(int i);
static void func_4010C8(int i);
static void func_400FBE(int i);

::std::vector<signed int> mem;
char input[70];

int main(int, char *[], char*[])
{
  ::std::ifstream fin("./dump", std::ios::in);
  signed int n;

  while (fin >> n) {
    mem.push_back(n);
  }

  memset(input, '0', 70);
  int i = 0;
  int count = 0;

  for (int k = 0; k < 1000; k++) {
    for(int j = 0; j < 70; j += count) {
      int func = mem[10*i] + mem[10*i + 4];
      count = mem[10*i + 6];
      switch(func) {  
        case 4198870:
          fibonacci(i);
          break;

        case 4198445:
          func_40102D(i);
          break;

        case 4198712:
          func_401138(i);
          break;

        case 4198334:
          func_400FBE(i); 
          break;

        case 4198600:
          func_4010C8(i);
          break;
      }
      i++;
    }
    ::std::cout << ::std::string(input, 70);
  }
  return 0;
}

void fibonacci(int i)
{
  for (int j = 0; j < mem[10*i + 6]; j++) {
    unsigned int v3 = 0;
    unsigned int v4 = 1;
    unsigned int v1 = 0;
    for ( int k = 0; ; ++k ) {
      v1 = v3 + v4;
      v3 = v4;
      v4 = v1;

      if (v1 == mem[10*i + 7 + j]) {
        input[mem[10*i + 5] + j] = (char)(k+1);
        break;
      }
    }
  }
}

void func_40102D(int i)
{
  for (int j = 0; j < mem[10*i + 6]; j++) {
    int v1 = 0;
    for (signed int k = 0; ; ++k ) {
      if ( k & 1 )
        v1 += 2;
      else
        v1 += 11;
      if (v1 == mem[10*i + 7 + j]) {
        input[mem[10*i + 5] + j] = (char)(k+1);
        break;
      }
    }
  }
}

void func_401138(int i)
{
  for(int j = 0; j < mem[10*i + 6]; j++) {
    signed int v1 = -88035316;
    for ( signed int k = 0; ; ++k ) {
      if ( k & 1 )
        v1 -= 120;
      else
        v1 -= 30600;
      if(v1 == mem[10*i + 7 + j]) {
        input[mem[10*i + 5] + j] = (char)(k+1);
        break;
      }
    }
  }
}

void func_4010C8(int i)
{
  for (int j = 0; j < mem[10*i + 6]; j++) {
    for (int k = 0; ; k++ ) {
      if ((k ^ 1383424633) == mem[10*i + 7 + j]) {
        input[mem[10*i + 5] + j] = (char)(k);
        break;
      }
    }
  }
}

void func_400FBE(int i)
{
  for (int j = 0; j < mem[10*i + 6]; j++) {
    signed int v1 = 0;
    for (int k = 0; ; ++k ) {
      v1 = (k * 135);
      if(v1 == mem[10*i + 7 + j]) {
        input[mem[10*i + 5] + j] = (char)(k);
        break;
      }
    }
  }
}
