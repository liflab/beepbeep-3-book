Creating custom functions
=========================

In the case where none of the available functions (or a composition thereof) suits your needs, BeepBeep also offers the possibility to create your own `Function` objects, composed of arbitrary Java code.

If your intended function is 1:1 or 2:1 (that is, it has an input arity of 1 or 2, and an output arity of 1), the easiest way is to create a new class that extends either [UnaryFunction](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/functions/UnaryFunction.html) or [BinaryFunction](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/functions/BinaryFunction.html). These classes take care of most of the housekeeping associated to functions, and require you to simply implement a method called `getValue()`, responsible for computing the output, given some input(s). In this method, you can write whatever Java code you want.

As an example, let us create a function that, given a number, returns whether this number is prime. It is therefore a 1:1 function, so we will create a class that extends `UnaryFunction`.

```java
public class IsPrime extends UnaryFunction&lt;Number,Boolean&gt;
{
}
```

As you can see, you must also declare the input and output type for the function; here, the function accepts a `Number` and returns a `Boolean`. These types must also be reflected in the function's constructor, where you must call the superclass constructor and pass it a `Class` instance of each input and output argument.

<pre><code>Source code not found</code></pre>

Method `getValue()` is where the output of the function is computed for the input. For the sake of our example, the actual way to check if x is prime does not matter; we'll simply enumerate all numbers up to sqrt(x) until we find one that divides x, and otherwise return true.

{@snips Examples/src/functions/IsPrime.java}{public Boolean getValue(Number x)}

<!-- :wrap=soft: -->
