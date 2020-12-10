package main

import (
  "fmt"
  "strings"
  "strconv"
)


func check_input(tokens []string) {
  for i := 0; i < 0x24; i++ {
    // if tokens[i] ?? {
    //   break
    // }
    i, _ := strconv.Atoi(tokens[i])
    fmt.Println(i)
  }
}

func main() {
  var input string

  for {
    fmt.Println("+================+")
    fmt.Println("|    Go Crazy    |")
    fmt.Println("+================+")
    fmt.Println("Say something :")
    fmt.Scanln(&input)

    if input[0] == 'x' && input[len(input)-1] == 'x' {
      tokens := strings.Split(input[1:len(input)-1], ",")
      check_input(tokens)
    }

    fmt.Println("no ! tOo NORmAL ! yoU hAvE To Be crAzY eNoUgh BeForE GEtTING flag")
  }
}
