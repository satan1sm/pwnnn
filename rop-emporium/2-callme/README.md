## Static Link 与 Dynamic Link
程式的 link 分为 `static link` 和 `dynamic link`

| Method | Desc | Pros & Cons |
| --- | --- | --- |
| Static Link | 会把外部函数的代码，一起打包到单一个执行档里 | 快，占空间 |
| Dynamic Link | 需先载入 shared library，等到要执行 library 里某个函数的时候 ，再 call (jmp进去) | 慢，省空间 |
<br>

* 为何后者省空间：若三个程式都要用 printf()，static link 需要加载三份 printf()，而 dynamic link 只需要加载一份。
* shared library = shared object (.so)

<br>

## GOT 与 PLT
问题：Dynamically linked 的程序，在 runtime 是如何執行到 shared library 里的函數呢？

先看看 GOT 与 PLT 两大功臣。

| Table | Section | Name | 功用 |
| --- | --- | --- | --- |
| GOT | .got.plt | Global Offset Table | 存 shared library 函数在内存中的实际位址（一个函数一条entry） |
| PLT | .plt | Procedure Linkage Table | (1) 存 shared library 函数在 GOT 对应到的 entry (2) 解析函数实际位址 |

简单来说：

1. 调用一个 shared library function 时，eip 会先跳到 PLT，然后立马跳到 GOT。
2. 如果是第一次呼叫某个外部函数，它在 GOT 中记录的「实际 address」，会是「未解析」的状态。
3. 该 GOT entry 此时所填的位址，会指向 PLT 中的「函数位址解析」函数
4. 此时会再跳回 PLT ，调用这个特殊函数来解析目标函数的位址。
5. 解析完成后， GOT entry 所记录的「实际 address」 就会自动更新，然后就正式调用该函数。
6. 下次调用该函数时，由于 GOT 已记录该函数的「实际 address」，已不再是「未解析」的状态了，所以可以直接调用。

<br>

## Lazy Binding
问题：为什么需要 Lazy Binding？不能在程式执行前，就填好 GOT 吗？

可以。只要执行 `export LD_BIND_NOW=1` 然后运行你的程式即可，

但 startup time 会慢一点，因为要事先解析所有 shared library functions 的「实际address」（小程式感觉没啥差异）
