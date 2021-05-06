// Copyright (c) 2021 Marco Wang <m.aesophor@gmail.com>. All rights reserved.
extern "C" {
#include <fcntl.h>
#include <signal.h>
#include <unistd.h>
}
#include <iostream> 
#include <fstream>
#include <cstdlib>
#include <string>

#define NR_ENEMIES 5  /* number of angels to kill */

using namespace std;

class Divine {
 public:
  Divine(const string& name) : _name(name), _kill_count(0) {}
  virtual ~Divine() = default;

  virtual void kill(Divine*) = 0;

 protected:
  virtual void unlock_secret_power() const {}

  string _name;
  int _kill_count;
};

class Angel final : public Divine {
 public:
  Angel(const string& name) : Divine(name) {}
  virtual ~Angel() = default;

  // Forgives the enemy and do nothing...
  virtual void kill(Divine*) override {}
};

class Devil final : public Divine {
 public:
  Devil(const string& name) : Divine(name) {}
  virtual ~Devil() = default;

  // Actually kills the enemy...
  virtual void kill(Divine* other) override {
    delete other;
    _kill_count++;
  }

 private:
  virtual void unlock_secret_power() const override {
    if (_kill_count >= NR_ENEMIES) {
      cout << "You've slained the god..." << endl;
      system("/bin/sh");
    } else {
      cout << "You haven't killed all the angels guarding the god..." << endl;
      exit(0);
    }
  }
};


void sigsegv_handler(int) {
  cout << "\nYou've been casted away from the heaven again..." << endl;
  exit(0);
}

int main() {
  // Disable stdout buffering
  setvbuf(stdout, NULL, _IONBF, 0);

  signal(SIGSEGV, sigsegv_handler);

  cout << "Reborn to divinity\n"
          "==================\n"
          "You are Lucifer, the fallen arch-angel and\n"
          "the infamous ruler of hell. After being\n"
          "casted away from the heaven by the god himself\n"
          "for a millenium, you finally returned to the heaven...\n"
          "This time you're going to usurp the god's throne."
       << endl;


  // The god created lucifer and all the other angels...
  Divine* divines[1 + NR_ENEMIES] = {
    new Devil("Lucifer"),
    new Angel("Michael"),
    new Angel("Gabriel"),
    new Angel("Uriel"),
    new Angel("Raphael"),
    new Angel("Selaphiel"),
  };

  char* data;
  unsigned int op;
  Divine* satan = divines[0];

  while (1) {
    cout << endl
         << "1. Enter battle\n"
         << "2. Resurrect last dead soul\n"
         << "choice: ";

    cin >> op;

    switch (op) {
      case 1: {
        for (size_t i = 1; i < 1 + NR_ENEMIES; i++) {
          satan->kill(divines[i]);
        }
        cout << "you died in the battle..." << endl;
        delete satan;
        break;
      }

      case 2: {
        cout << "As the ruler of hell, you can bestow this soul a gift: ";
        data = new char[24];
        read(0, data, 24);
        cout << "resurrected one soul." << endl;
        break;
      }

      default:
        break;
    }
  }

  return 0;	
}
