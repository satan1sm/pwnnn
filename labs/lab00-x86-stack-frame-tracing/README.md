## 動畫詳解 x86 stack frame 運作方式

* Slides: https://docs.google.com/presentation/d/1CCSYlMxAvGjm-cimbDCaTUu7DTQobb1t_wANE7pi7mc/edit?usp=sharing
* Video version: https://youtu.be/rqgi82j-qRE

# Stack Frame Layout: x86 and x86_64
x86 (32-bit):

<img src="https://www.cs.virginia.edu/~evans/cs216/guides/stack-convention.png" width="500"/>

x86_64 (64-bit):

```
long myfunc(long a, long b, long c, long d,
            long e, long f, long g, long h)
{
    long xx = a * b * c * d * e * f * g * h;
    long yy = a + b + c + d + e + f + g + h;
    long zz = utilfunc(xx, yy, xx % yy);
    return zz + 20;
}
```
<img src="https://eli.thegreenplace.net/images/2011/08/x64_frame_nonleaf.png" width="500"/>
