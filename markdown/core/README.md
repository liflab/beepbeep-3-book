Fundamental Processors and Functions
====================================

BeepBeep is organized along a modular architecture. The main part of BeepBeep is called the *engine*, which provides the basic classes for creating processors and functions, and contains a handful of general-purpose processors for manipulating traces. The rest of BeepBeep's functionalities is dispersed across a number of *palettes*. In this chapter, we describe the basic processors and functions provided by BeepBeep's engine.

## Function objects {#functions}

A **function** is something that accepts *arguments* and produces a return *value*. In BeepBeep, functions are "first-class citizens"; this means that every function that is to be applied on an event is itself an object, which inherits from a generic class called [Function](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/functions/Function.html). For example, the negation of a Boolean value is a function object called [Negation](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/util/Booleans/Negation.html); the sum of two numbers is also a function object called [Addition](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/util/Numbers/Addition.html).

Function objects can be instantiated and manipulated directly. The BeepBeep classes [Booleans](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/util/Booleans.html), [Numbers](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/util/Numbers.html) and [Sets](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/util/Sets.html) define multiple function objects to manipulate Boolean values, numbers and sets. These functions can be accessed through static member fields of these respective classes. Consider for example the following code snippet:

``` java
       Function negation = Booleans.not;
       Object[] out = new Object[1];
       negation.evaluate(new Object[]{true}, out);
       System.out.println("The return value of the function is: " + out[0]);
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/functions/FunctionUsage.java#L37)


The first instruction gets a reference to a `Function` object, corresponding to the static member field `not` of class `Booleans`. This field refers to an instance of a function called [Negation](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/util/Booleans/Negation.html). As a matter of fact, this is the only way to get an instance of `Negation`: its constructor is declared as `private`, which makes it impossible to create a new instance of the object using `new`. This is done on purpose, so that only one instance of `Negation` ever exists in a program --effectively making `Negation` a *singleton* object. We shall see that the vast majority of `Function` objects are singletons, and are referred to using a static member field of some other object.

In order to perform a computation, every function defines a method called [evaluate()](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/functions/Function.html#evaluate(Object[], Object[])). This method takes two arguments; the first is an array of objects, corresponding to the input values of the function. The second is another array of objects, intended to receive the output values of the function. Hence, like for a processor, a function also has an input arity and an output arity.

For function `Negation`, both are equal to one: the negation takes one Boolean value as its argument, and returns the negation of that value. The second line of the example creates an array of size 1 to hold the return value of the function. Line 3 calls `evaluate`, with the Boolean value `true` used as the argument of the function. Finally, line 4 prints the result:

    The return value of the function is: false

Functions with an input arity of size greater than 1 work in the same way. In the following example, we get an instance of the [Addition](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/util/Numbers/Addition.html) function, and make a call on `evaluate` to get the value of 2+3.

``` java
       Function addition = Numbers.addition;
       addition.evaluate(new Object[]{2, 3}, out);
       System.out.println("The return value of the function is: " + out[0]);
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/functions/FunctionUsage.java#L52)


As expected, the program prints:

    The return value of the function is: 5.0

While the use of input/output arrays may appear cumbersome at first, it is mitigated by two things. First, you will seldom have to call `evaluate` on functions directly. Second, this mechanism makes it possible for functions to have arbitrary input and output arity; in particular, a function can have an output arity of 2 or more. Consider this last code example:

``` java
Function int_division = IntegerDivision.instance;
Object[] outs = new Object[2];
int_division.evaluate(new Object[]{14, 3}, outs);
System.out.println("14 divided by 3 equals " +
        outs[0] + " remainder " + outs[1]);
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/functions/FunctionUsage.java#L64)


The first instruction creates a new instance of another `Function` object, this time called `IntegerDivision`.  From two numbers *x* and *y*, it outputs **two** numbers: the quotient and the remainder of the division of x by y. Note that contrary to the previous examples, this function was created by accessing the `instance` static field on class `IntegerDivision`. Most `Function` objects outside of utility classes such as `Booleans` or `Numbers` provide a reference to their singleton instance in this way. The remaining lines are again a call to `evaluate`: however, this time, the array receiving the output from the function is of size 2. The first element of the array is the quotient, the second is the remainder. Hence the last line of the program prints this:

    14 divided by 3 equals 4 remainder 2

## Applying a function on a stream {#applyfunction}

A function is a "static" object: a call to `evaluate` receives a single set of arguments, computes a return value, and ends. In many cases, it may be desirable to apply a function to each event of a stream. In other words, we would like to "turn" a function into a processor that applies this function. The processor responsible for this is called [ApplyFunction](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/functions/ApplyFunction.html). When instantiated, `ApplyFunction` must be given a `Function` object; it calls this function's `evaluate` on each input event, and returns the result on its output pipe.

In the following bit of code, an `ApplyFunction` is created by applying the Boolean negation function to an input trace of Boolean values:

``` java
QueueSource source = new QueueSource();
source.setEvents(false, true, true, false, true);
ApplyFunction not = new ApplyFunction(Booleans.not);
Connector.connect(source, not);
Pullable p = not.getPullableOutput();
for (int i = 0; i < 5; i++)
{
    boolean x = (Boolean) p.pull();
    System.out.println("The event is: " + x);
}
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/basic/SimpleFunction.java#L53)


The first lines should be familiar to you at this point: they create a `QueueSource`, and give it a list of events to be fed upon request. In this case, we give the source a list of five Boolean values. In line 3, we create a new `ApplyFunction` processor, and give to its constructor the instance of the `Negation` function referred to by the static member field `Booleans.not`. Graphically, we can represent this as follows:

![Applying a function on each input event transforms an input stream into a new output stream.](SimpleFunction.png)

The `ApplyFunction` processor is represented by a box with a yellow *f* as its pictogram. This processor has an argument, which is the actual function it is asked to apply. By convention, function objects are represented by small rounded rectangles; the rectangle placed on the bottom side of the box represents the `Negation` function. Following the colour coding we introduced in the previous chapter, the stream we are manipulating is made of Boolean values; hence all pipes are painted in the blue-gray shade representing Booleans.

Calling `pull` on the `not` processor will return, as expected, the negation of the events given to the source. The program will print:

    The event is: true
    The event is: false
    The event is: false
    The event is: true
    The event is: false

The input and output arity of the `ApplyFunction` matches that of the `Function` object given as its argument. Hence, a binary function will result in a binary processor. For example, the following code example computes the pairwise addition of numbers from two streams:

``` java
QueueSource source1 = new QueueSource();
source1.setEvents(2, 7, 1, 8, 3);
QueueSource source2 = new QueueSource();
source2.setEvents(3, 1, 4, 1, 6);
ApplyFunction add = new ApplyFunction(Numbers.addition);
Connector.connect(source1, 0, add, 0);
Connector.connect(source2, 0, add, 1);
Pullable p = add.getPullableOutput();
for (int i = 0; i < 5; i++)
{
    float x = (Float) p.pull();
    System.out.println("The event is: " + x);
}
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/functions/FunctionBinary.java#L40)


The reader may notice that this example is very similar to one we saw in the previous chapter. The difference lies in the fact that the original example used a special processor called `Adder` to perform the addition. Here, we use a generic `ApplyFunction` processor, to which the addition function is passed as a parameter. This difference is important: in the original case, there was no easy way to replace the addition by some other operation --apart from finding another purpose-built processor to do it. In the present case, changing the operation to some other binary function on numbers simply amounts to changing the function object given to `ApplyFunction`.

Function processors can be chained to perform more complex calculations, as is illustrated by the following code fragment:

``` java
QueueSource source1 = new QueueSource().setEvents(2, 7, 1, 8, 3);
QueueSource source2 = new QueueSource().setEvents(3, 1, 4, 1, 6);
QueueSource source3 = new QueueSource().setEvents(1, 1, 2, 3, 5);
ApplyFunction add = new ApplyFunction(Numbers.addition);
Connector.connect(source1, 0, add, 0);
Connector.connect(source2, 0, add, 1);
ApplyFunction mul = new ApplyFunction(Numbers.multiplication);
Connector.connect(source3, 0, mul, 0);
Connector.connect(add, 0, mul, 1);
Pullable p = mul.getPullableOutput();
for (int i = 0; i < 5; i++)
{
    float x = (Float) p.pull();
    System.out.println("The event is: " + x);
}
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/functions/FunctionChain.java#L41)


Here, we create three sources of numbers; events from the first two are added, and the result is multiplied by the event at the corresponding position in the third stream. The schema of such a program becomes more interesting:

![Chaining function processors.](SimpleFunction.png)

The expected output of the program should look like this:

    The event is: 5.0
    The event is: 8.0
    The event is: 10.0
    The event is: 27.0
    The event is: 45.0

Indeed, (2+3)×1=5, (7+1)×1=8, (1+4)×2=10, and so on.

## Function trees {#trees}

Trees.
    
## Forking a stream {#fork}

The fork

## Trimming events {#trim}

Coupled with the fork, `Trim` can be useful to perform a computation on successive events. For example, we can compute the sum of each pair of two successive events...

We can also use trim to check if an event is 

## Cumulate values {#cumulate}

A variant of the function processor is the {@link ca.uqac.lif.cep.functions.CumulativeProcessor CumulativeProcessor}. Contrarily to the processors above, which are stateless, a cumulative processor is stateful. A `CumulativeProcessor` is given a binary function *f*. Intuitively, if *x* is the previous value returned by the processor, its output on the next event *y* will be *f(x,y)*. The processor requires an initial value *t* to compute its first output.

Depending on the function *f*, cumulative processors can represent many things. In the following code example, *f* is addition and 0 is the start value.

<pre><code>Source code not found: ../beepbeep-3-examples/Source/src/Examples/src/queries/CumulativeSum.java</code></pre>

The processor outputs the cumulative sum of all values received so far:

    The event is: 1
    The event is: 3
    The event is: 6
    The event is: 10
    ...

As another example, if *f* is the [three-valued logical conjunction](https://en.wikipedia.org/wiki/Three-valued_logic#Kleene_and_Priest_logics) and "?" is the start value, then the processor computes the three-valued conjunction of events received so far, and has the same semantics as the LTL3 "Globally" operator.

<!-- :wrap=soft: -->