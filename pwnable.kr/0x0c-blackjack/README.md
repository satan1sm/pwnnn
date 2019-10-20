## Source code
[Link](https://cboard.cprogramming.com/c-programming/114023-simple-blackjack-program.html)

輸入賭注金額的部分，作者未針對 input 做全面的檢查（作者只檢查了 bet > cash，但如果 bet < 0 呢？）
```
int betting() //Asks user amount to bet
{
 printf("\n\nEnter Bet: $");
 scanf("%d", &bet);
 
 if (bet > cash) //If player tries to bet more money than player has
 {
        printf("\nYou cannot bet more money than you have.");
        printf("\nEnter Bet: ");
        scanf("%d", &bet);
        return bet;
 }
 else return bet;
} // End Function
```

## Pwn
輸入一個很大的數，如 -10000000，輸了的時候應該會執行 `cash - bet` 之類的動作，於是可以得到 flag

```
YaY_I_AM_A_MILLIONARE_LOL


Cash: $10000500
-------
|S    |
|  1  |
|    S|
-------

Your Total is 1

The Dealer Has a Total of 10

Enter Bet: $
```

flag: `YaY_I_AM_A_MILLIONARE_LOL`
