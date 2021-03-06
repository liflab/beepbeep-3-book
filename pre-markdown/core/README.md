Fundamental Processors and Functions
====================================

BeepBeep is organized along a modular architecture. The main part of BeepBeep is called the *engine*, which provides the basic classes for creating processors and functions, and contains a handful of general-purpose processors for manipulating traces. BeepBeep's remaining functionalities are dispersed across a number of *palettes*. In this chapter, we describe the basic processors and functions provided by BeepBeep's engine.

## Function Objects

A <!--\index{Function@\texttt{Function}} \textbf{function}-->**function**<!--/i--> is something that accepts *arguments* and produces a return *value*. In BeepBeep, functions are "first-class citizens"; this means that every function that is to be applied on an event is itself an object, which inherits from a generic class called {@link jdc:ca.uqac.lif.cep.functions.Function Function}. For example, the negation of a Boolean value is a function object called {@link jdc:ca.uqac.lif.cep.util.Booleans.Negation Negation}; the sum of two numbers is also a function object called {@link jdc:ca.uqac.lif.cep.util.Numbers.Addition Addition}.

Function objects can be instantiated and manipulated directly. The BeepBeep classes {@link jdc:ca.uqac.lif.cep.util.Booleans Booleans}, {@link jdc:ca.uqac.lif.cep.util.Numbers Numbers} and {@link jdc:ca.uqac.lif.cep.util.Sets Sets} define multiple function objects to manipulate <!--\index{Booleans@\texttt{Booleans}} Boolean-->Boolean<!--/i--> values, <!--\index{Numbers@\texttt{Numbers}} numbers-->numbers<!--/i--> and <!--\index{Sets@\texttt{Sets}} sets-->sets<!--/i-->. These functions can be accessed through static member fields of these respective classes. Consider for example the following code snippet:

{@snipm functions/FunctionUsage.java}{/}

The first instruction gets a reference to a `Function` object, corresponding to the static member field `not` of class `Booleans`. This field refers to an instance of a function called {@link jdc:ca.uqac.lif.cep.util.Booleans.Negation Negation}. As a matter of fact, this is the only way to get an instance of <!--\index{Booleans@\texttt{Booleans}!Not@\texttt{Not}} \texttt{Negation}-->`Negation`<!--/i-->: its constructor is declared as `private`, which makes it impossible to create a new instance of the object using `new`. This is done on purpose, so that only one instance of `Negation` ever exists in a program --effectively making `Negation` a <!--\index{singleton} \emph{singleton}-->*singleton*<!--/i--> object. We shall see that the vast majority of `Function` objects are singletons, and are referred to using a static member field of some other object.

In order to perform a computation, every function defines a method called {@link jdm:ca.uqac.lif.cep.functions.Function#evaluate(Object[], Object[]) evaluate()}. This method takes two arguments; the first is an array of objects, corresponding to the input values of the function. The second is another array of objects, intended to receive the output values of the function. Hence, as for a processor, a function also has an input arity and an output arity.

For function `Negation`, both are equal to one: the negation takes one Boolean value as its argument, and returns the negation of that value. The second line of the example creates an array of size 1 to hold the return value of the function. Line 3 calls `evaluate`, with the Boolean value `true` used as the argument of the function. Finally, line 4 prints the result:

    The return value of the function is: false

Functions with an input arity of size greater than 1 work in the same way. In the following example, we get an instance of the {@link jdc:ca.uqac.lif.cep.util.Numbers.Addition Addition} function, and make a call on `evaluate` to get the value of 2+3.

{@snipm functions/FunctionUsage.java}{\*}

As expected, the program prints:

    The return value of the function is: 5.0

While the use of input/output arrays may appear cumbersome at first, it is mitigated by two things. First, you will seldom have to call `evaluate` on functions directly. Second, this mechanism makes it possible for functions to have arbitrary input and output arity; in particular, a function can have an output arity of 2 or more. Consider this last code example:

{@snipm functions/FunctionUsage.java}{#}

The first instruction creates a new instance of another `Function` object, this time called <!--\index{IntegerDivision@\texttt{IntegerDivision}} \texttt{IntegerDivision}-->`IntegerDivision`<!--/i-->.  From two numbers *x* and *y*, it outputs **two** numbers: the quotient and the remainder of the division of x by y. Note that contrary to the previous examples, this function was created by accessing the `instance` static field on class `IntegerDivision`. Most `Function` objects outside of utility classes such as `Booleans` or `Numbers` provide a reference to their singleton instance in this way. The remaining lines are again a call to `evaluate`: however, this time, the array receiving the output from the function is of size 2. The first element of the array is the quotient, the second is the remainder. Hence, the last line of the program prints this:

    14 divided by 3 equals 4 remainder 2

## Applying a Function on a Stream

A function is a "static" object: a call to `evaluate` receives a single set of arguments, computes a return value, and ends. In many cases, it may be desirable to apply a function to each event of a stream. In other words, we would like to "turn" a function into a processor applying this function. The processor responsible for this is called {@link jdc:ca.uqac.lif.cep.functions.ApplyFunction ApplyFunction}. When instantiated, <!--\index{ApplyFunction@\texttt{ApplyFunction}} \texttt{ApplyFunction}-->`ApplyFunction`<!--/i--> must be given a `Function` object; it calls this function's `evaluate` on each input event, and returns the result on its output pipe.

In the following bit of code, an `ApplyFunction` is created by applying the Boolean negation function to an input trace of Boolean values:

{@snipm basic/SimpleFunction.java}{/}

The first lines should be familiar at this point: they create a `QueueSource`, and give it a list of events to be fed upon request. In this case, we give the source a list of five Boolean values. In line 3, we create a new `ApplyFunction` processor, and give to its constructor the instance of the `Negation` function referred to by the static member field `Booleans.not`. Graphically, they can be represented as follows:

{@img doc-files/basic/SimpleFunction.png}{Applying a function on each input event transforms an input stream into a new output stream.}{.6}

The `ApplyFunction` processor is represented by a box with a yellow *f* as its pictogram. This processor has an argument, the actual function it is asked to apply. By convention, function objects are represented by small rounded rectangles; the rectangle placed on the bottom side of the box represents the `Negation` function. Following the colour coding we introduced in the previous chapter, the stream manipulated is made of Boolean values; hence all pipes are painted in the blue-grey shade representing Booleans.

Calling `pull` on the `not` processor will return, as expected, the negation of the events given to the source. The program will print:

    The event is: true
    The event is: false
    The event is: false
    The event is: true
    The event is: false

The input and output arity of the `ApplyFunction` matches that of the `Function` object given as its argument. Hence, a binary function will result in a binary processor. For instance, the following code example computes the pairwise addition of numbers from two streams:

{@snipm functions/FunctionBinary.java}{/}

The reader may notice that this example is very similar to one we saw in the previous chapter. The difference lies in the fact that the original example used a special processor called `Adder` to perform the addition. Here, a generic `ApplyFunction` processor is used, to which the addition function is passed as a parameter. This difference is important: in the original case, there was no easy way to replace the addition by some other operation --apart from finding another purpose-built processor to do it. In the present case, changing the operation to some other binary function on numbers simply amounts to changing the function object given to `ApplyFunction`.

Function processors can be chained to perform more complex calculations, as is illustrated by the following code fragment:

{@snipm functions/FunctionChain.java}{/}

Here, three sources of numbers are created; events from the first two are added, and the result is multiplied by the event at the corresponding position in the third stream. The diagram of such a program becomes more interesting:

{@img doc-files/functions/FunctionChain.png}{Chaining function processors.}{.6}

The expected output of the program should look like this:

    The event is: 5.0
    The event is: 8.0
    The event is: 10.0
    The event is: 27.0
    The event is: 45.0

Indeed, (2+3)×1=5, (7+1)×1=8, (1+4)×2=10, and so on.

## Function Trees

In the previous example, if the three input streams were named *x*, *y* and *z*, the processor chain created corresponds informally to the expression (*x*+*y*)×*z*. However, having to write each arithmetical operator as an individual processor can become tedious. After all, (*x*+*y*)×*z* is itself a function *f*(*x*,*y*,*z*) of three variables; isn't there a way to create a `Function` object corresponding to this expression, and to give this expression to a single `ApplyFunction` processor?

Fortunately, the answer is yes. It is possible to create complex functions by composing simpler ones, through the use of a special `Function` object called the {@link jdc:ca.uqac.lif.cep.functions.FunctionTree FunctionTree}. As its name implies, a <!--\index{FunctionTree@\texttt{FunctionTree}} \texttt{FunctionTree}-->`FunctionTree`<!--/i--> is effectively a tree structure whose nodes can either be:

- a `Function` object
- another `FunctionTree`
- a special type of variable, called a `StreamVariable`.

By nesting function trees within each other, it is possible to create complex expressions from simpler functions. As an example, let us revisit the previous program, and simplify the chain of `ApplyFunction` processors:

{@snipm functions/FunctionTreeUsage.java}{/}

We instantiate a new `FunctionTree` object after creating the three sources. The first argument is the function at the root of the tree; in an expression using parentheses, this corresponds to the operator that is to be evaluated *last* (here, the multiplication). The number of arguments that follow is variable: it corresponds to the expressions that are the arguments of the operator. In the example provided, the left-hand side of the multiplication is itself a `FunctionTree`. The operator of this inner tree is the addition, followed by its two arguments. Since we want to add the events coming from the first and second streams, these arguments are two <!--\index{PullableException@\texttt{PullableException}} \texttt{PullableException}-->`PullableException`<!--/i--> objects. By convention, `StreamVariable.X` corresponds to input stream number 0, while `StreamVariable.Y` corresponds to input stream number 1. Finally, the right-hand side of the multiplication is `StreamVariable.Z`, which by convention corresponds to input stream number 2.

This single-line instruction effectively created a new `Function` object with three arguments, which is then given to an `ApplyFunction` processor like any other function. Processor `exp` has an input arity of 3; all three sources can directly be connected into it: `source1` into input stream 0, `source2` into input stream 1, and `source3` into input stream 2. Graphically, this can be illustrated as follows:

{@img doc-files/functions/FunctionTreeUsage.png}{Chaining function processors.}{.6}

As one can see, the single `ApplyFunction` processor is attached to a tree of functions, which corresponds to the object built by line 4. By convention, stream variables are represented by diamond shapes, with either the name of a stream variable (*x*, *y* or *z*), or equivalently with a number designating the input stream. Again, the colour of the nodes depicts the type of objects being manipulated. In the rest of the book and for the sake of clarity, the representation of a function as a tree will sometimes be forsaken; an inline notation such as (*x*+*y*)×*z* will be used to simplify the drawing.

Pulling events from `exp` will result in a similar pattern as before:

    The event is: 5.0
    The event is: 8.0
    The event is: 10.0
    The event is: 27.0
    The event is: 45.0

Note that a stream variable may appear more than once in a function tree. Hence, an expression such as (*x*+*y*)×(*x*+*z*) is perfectly fine.

## Forking a Stream

Sometimes, it may be useful to perform multiple separate computations over the same stream. In order to do so, one must be able to <!--\index{Fork@\texttt{Fork}} split-->split<!--/i--> the original stream into multiple identical copies. This is the purpose of the {@link jdc:ca.uqac.lif.cep.tmf.Fork Fork} processor.

As a first example, let us connect a queue source to create a fork processor that will replicate each input event in two output streams. This is the meaning of the number 2 passed as an argument to the fork's constructor.

{@snipm basic/ForkPull.java}{/}

{@img doc-files/basic/ForkPull.png}{Pulling events from a fork.}{.6}

We get Pullables on both outputs of the fork (`p0` and `p1`), and then pull a first event from `p0`. As expected, `p1` returns the first event of the source, which is the `Number` 1:

    Output from p0: 1

We then pull an event from `p1`. Surprisingly enough, the output is:

    Output from p1: 1

...and not 2 as might have been expected. This can be explained by the fact that each input event in the fork is replicated to all its output pipes. The fact that we pulled an event from `p0` has no effect on `p1`, and vice versa. The independence between the fork's two outputs is further illustrated by this sequence of calls:

{@snipm basic/ForkPull.java}{\*}

Producing the output:

    Output from p0: 2
    Output from p0: 3
    Output from p1: 2
    Output from p0: 4
    Output from p1: 3

Notice how each pullable moves through the input stream independently of calls to the other pullable.

Forks also exhibit a special behaviour in push mode. Consider the following example:

{@snipm basic/ForkPush.java}{/}

We create a fork processor that will replicate each input event in three output streams. We now create three "print" processors. Each of them simply prints to the console whatever event they receive. Each of them is asked to append its printed line with a different prefix ("Px") to define who is printing what. Finally, we connect each of the three outputs streams of the fork (numbered 0, 1 and 2) to the input of each print processor. This corresponds to the following diagram:

{@img doc-files/basic/ForkPush.png}{Pushing events into a fork.}{.6}

Let's now push an event to the input of the fork and see what happens. We should see on the console:

    P0 foo
    P1 foo
    P2 foo

The three lines should be printed almost instantaneously. This shows that all three print processors received their input event at the "same" time. This is not exactly true: the fork processor pushes the event to each of its outputs in sequence; however, since the time it takes to do so is so short, we can consider this to be instantaneous.

An important thing to keep in mind is that the fork, like almost all other BeepBeep processors, passes **references** to objects. In the previous example, the output events that are sent out are just three references to the same input event. This can cause bizarre side effects if the input event is a <!--\index{mutable object} mutable-->mutable<!--/i--> object, and one of the downstream branches modifies that object. Consider a modified version of the previous example, as follows:

{@snipm basic/ForkMutable.java}{/}

The difference lies in the fact that a special processor called `RemoveFirst` has been introduced between the fork's second output branch and the second `Print` processor. Let us suppose that this processor removes the first element of the list it receives and returns that list. This can be illustrated like this:

{@img doc-files/basic/ForkMutable.png}{Pushing a mutable object into a fork and modifying that object in one of the downstream branches.}{.6}

Let us now create a list and push it into the fork:

{@snipm basic/ForkMutable.java}{\*}

The output of this program is:

```
P0 [3, 1, 4]
P1 [1, 4]
P2 [1, 4]
```

Notice how, this time, the `Print` processors do not all print the same thing. The input list `[3,1,4]` is first pushed into `p0`, which produces the first line of output. The list is then pushed into `rf`, which removes the first element of that list, and passes it to `p1`, which prints the second line of the output. The surprise is on the third line of output, as we would expect `p2` to receive the input list `[3,1,4]`. However, since elements are passed by reference, processor `p2` is given a reference to the input list; it so happens that this list has been modified by `rf` just before, and is no longer in the same state as when it was pushed to the fork at the beginning of the program.

We do not recommend exploiting this side effect in your BeepBeep programs; although the fork seems to push events from top to bottom, the ordering is in fact undefined and should not be taken for granted. Most BeepBeep processors that are specific to mutable objects such as lists or sets take care of creating and returning a *copy* of the original object to avoid such unwanted behaviour (`RemoveFirst` is an exception, crafted only for this example). However, one must still be careful when passing around mutable objects that are referenced from multiple points in a program --as is the case in Java programming in general.

## Cumulating Values

A variant of the function processor is the {@link jdc:ca.uqac.lif.cep.functions.Cumulate Cumulate} processor. Contrary to all the processors we have seen so far, which are stateless, <!--\index{Cumulate@\texttt{Cumulate}} \texttt{Cumulate}-->`Cumulate`<!--/i--> is our first example of a <!--\index{stateful processor} \textbf{stateful}-->**stateful**<!--/i--> processor: this means that the output it returns for a given event depends on what it has output in the past. In other words, a stateful processor has a "memory", and the same input event may produce different outputs. 

A `Cumulate` is given a function *f* of two arguments. Intuitively, if *x* is the previous value returned by the processor, its output on the next event *y* will be *f(x,y)*. Upon receiving the first event, since no previous value was ever set, the processor requires an initial value *t* to use in place of *x*.

As its name implies, `Cumulate` is intended to compute a cumulative "sum" of all the values received so far. The simplest example is when *f* is addition, and 0 is used as the start value *t*.

{@snipm basic/CumulativeSum.java}{/}

We first wrap the `Addition` function into a {@link jdc:ca.uqac.lif.cep.functions.CumulativeFunction  CumulativeFunction}. This object extends addition by defining a start value *t*. It is then given to the `Cumulate` processor. Graphically, this can be represented as follows:

{@img doc-files/basic/CumulativeSum.png}{Computing the cumulative sum of numbers.}{.6}

The `Cumulate` processor is represented by a box with the Greek letter sigma. On one side of the box is the function used for the cumulation (here addition), and on the other side is the start value *t* used when receiving the first event (here 0).

Upon receiving the first event *y*=1, the cumulate processor computes *f*(*x*,1). Since no previous value *x* has yet been output, the processor uses the start value *t*=0 instead. Hence, the processor computes *f*(0,1), that is, 0+1=1, and returns 1 as its first output event.

Upon receiving the second event *y*=2, the cumulate processor computes *f*(*x*,2), with *x* being the event output at the previous step --in other words, *x*=1. This amounts to computing *f*(1,2), that is 1+2=3. Upon receiving the third event *y*=3, the processor computes *f*(3,3) = 3+3 = 6. As can be seen, the processor outputs the cumulative sum of all values received so far:

    The event is: 1.0
    The event is: 3.0
    The event is: 6.0
    The event is: 10.0
    ...

Cumulative processors and function processors can be put together into a common pattern, illustrated by the following diagram:

{@img doc-files/basic/Average.png}{The running average of a stream of numbers.}{.6}

We first create a source of arbitrary numbers. The output of this processor is piped to a cumulative processor. Then, we create a source of 1s and sum it; this is done with the same process as above, but on a stream outputting the value 1 all the time. This effectively creates a counter outputting 1, 2, 3, etc. We finally divide one stream by the other.

Consider for example the stream of numbers 2, 7, 1, 8, etc. After reading the first event, the cumulative average is 2÷1 = 2. After reading the second event, the average is (2+7)÷(1+1), and after reading the third, the average is (2+7+1)÷(1+1+1) = 3.33 --and so on. The output is the average of all numbers seen so far. This is called the <!--\index{running average} \textbf{running average}-->**running average**<!--/i-->, and it occurs very often in stream processing. Coded, this corresponds to the following instructions:

{@snipm basic/Average.java}{/}

This example, however, requires a second queue just to count events received. Our chain of processors can be refined by creating a counter out of the original stream of values, as shown here:

{@img doc-files/basic/AverageFork.png}{Running average not relying on an external counter.}{.45}

We first fork the original stream of values in two copies. The topmost copy is used for the cumulative sum of values, as before. The bottom copy is sent into a processor called {@link jdc:ca.uqac.lif.cep.functions.TurnInto TurnInto}; this processor replaces whatever input event it receives by the same predefined object. Here, it is instructed to <!--\index{TurnInto@\texttt{TurnInto}} turn-->turn<!--/i--> every event into the number 1. This stream of 1s is then summed, effectively creating a counter that produces the stream 1, 2, 3, etc. The two streams are then divided as in the previous example.

It shall be noted that, `Cumulate` does not have to work only with addition, or even with numbers. Depending on the function *f*, cumulative processors can represent many other things. For example, in the next code snippet, a stream of Boolean values is created, and piped into a `Cumulate` processor, using <!--\index{Booleans@\texttt{Booleans}!And@\texttt{And}} logical conjunction-->logical conjunction<!--/i--> ("and") as the function, and `true` as the start value:

{@snipm functions/CumulateAnd.java}{/}

{@img doc-files/functions/CumulateAnd.png}{Using the Boolean "and" operator in a `Cumulate` processor.}{.6}

When receiving the first event (`true`), the processor computes its conjunction with the start value (also `true`), resulting in the first output event (`true`). The same thing happens for the second input event, resulting in the output event `true`. The third input event is `false`; its conjunction with the previous output event (`true`) results in `false`. From then on, the processor will return `false`, regardless of the input events. This is because the conjunction of `false` (the previous output event) with anything always returns `false`. Hence, the expected output of the program is this:

    The event is: true
    The event is: true
    The event is: false
    The event is: false
    The event is: false

Intuitively, this processor performs the logical conjunction of all events received so far. This conjunction becomes false forever, as soon as a `false` event is received.

## Trimming Events

Up until now, all the processors studied were <!--\index{uniform processor} \textbf{uniform}-->**uniform**<!--/i-->: for each input event, they emitted exactly one output event (or more precisely, for each input *front*, they emitted exactly one output *front*). Not all processors need to be uniform; as a first example, let us have a look at the {@link jdc:ca.uqac.lif.cep.tmf.Trim Trim} processor.

The purpose of <!--\index{Trim@\texttt{Trim}} \texttt{Trim}-->`Trim`<!--/i--> is simple: it discards a fixed number of events from the beginning of a stream. This number is specified by passing it to the processor's constructor. Consider for example the following code:

{@snipm basic/TrimPull.java}{/}

The `Trim` processor is connected to a source, and is instructed to trim 3 events from the beginning of the stream. Graphically, this is represented as follows:

{@img doc-files/basic/TrimPull.png}{Pulling events from a `Trim` processor.}{.6}

As one can see, the `Trim` processor is depicted as a box with a pair of scissors; the number of events to be trimmed is shown in a small box on one of the sides of the processor. Let us see what happens when we `pull` is called six times on `Trim`. The first call to `pull` produces the following line:

    The event is: 4

This indeed corresponds to the *fourth* event in `source`'s list of events; the first three seem to have been cut off. But how can `trim` instruct `source` to start sending events at the fourth? In fact, the answer is that it does not. There is no way for a processor upstream or downstream to "talk" to another and give it instructions as to how to behave. What `trim` does is much easier: upon its first call to `pull`, it simply calls `pull` on its upstream processor four times, and discards the events returned by the first three calls.

At this point, `pull` behaves like `Passthrough`: it lets all events out without modification. The rest of the program goes as follows:

    The event is: 5
    The event is: 6
    The event is: 1
    The event is: 2
    The event is: 3

Do not forget that a `QueueSource` loops through its list of events; this is why after reaching 6, it goes back to the beginning and outputs 1, 2 and 3.

The `Trim` processor behaves in a similar way in push mode, such as in this example:

{@snipm basic/TrimPush.java}{/}

{@img doc-files/basic/TrimPush.png}{Pushing events into a `Trim` processor.}{.6}

Here, we connect a `Trim` to a `Print` processor. The `for` loop pushes integers 0 to 5 into `trim`; however, the first three events are discarded, and do not reach `print`. It is only at the fourth event that a push on `trim` will result in a downstream push on `print`. Hence, the output of the program is:

    3,4,5,

The `Trim` processor introduces an important point: from now on, the number of calls to `pull` or `push` is not necessarily equal across all processors of a chain. For example, in the last piece of code, we performed six `push` calls on `trim`, but `print` was pushed events only three times.

Coupled with `Fork`, the `Trim` processor can be useful to create two copies of a stream, offset by a fixed number of events. This makes it possible to output events whose value depends on multiple input events of the same stream. The following example shows how a source of numbers is forked in two; on one of the copies, the first event is discarded. Both streams are then sent to a processor that performs an addition.

{@img doc-files/basic/SumTwo.png}{Computing the sum of two successive events.}{.45}

{@snipi basic/SumTwo.java}{/}

On the first call on `pull`, the addition processor first calls `pull` on its first (top) input pipe, and receives from the source the number 1. The processor then calls `pull` on its second (bottom) input pipe. Upon being pulled, the `Trim` processor calls `pull` on its input pipe *twice*: it discards the first event it receives from the fork (1), and returns the second (2). The first addition that is computed is hence 1+2=3, resulting in the output 3.

From this point on, the top and the bottom pipe of the addition processor are always offset by one event. When the top pipe receives 2, the bottom pipe receives 3, and so on. The end result is that the output stream is made of the sum of each successive pair of events: 1+2, 2+3, 3+4, etc. This type of computation is called a <!--\index{window!sliding} \textbf{sliding window}-->**sliding window**<!--/i-->. Indeed, we repeat the same operation (here, addition) to a list of two events that progressively moves down the stream.

## Sliding Windows

For a window of two events, like in the previous example, using a `Trim` processor may be sufficient. However, as soon as the window becomes larger, doing such a computation becomes very impractical (an exercise at the end of this chapter asks you to try with three events instead of two). The use of sliding windows is so prevalent in event stream processing that BeepBeep provides a processor that does just that. It is called, as you may guess, {@link jdc:ca.uqac.lif.cep.tmf.Window Window}.

The <!--\index{Window@\texttt{Window}} \texttt{Window}-->`Window`<!--/i--> processor is one of the two most complex processors in BeepBeep's core, and deserves some explanation. Suppose that we want to compute the sum of input events over a sliding window of width 3. That is, the first output event should be the sum of input events at positions 0 to 2; the second output event should be the sum of input events at positions 1 to 3, and so on. Each of these sequences of three events is called a **window**. The first step is to think of a processor that performs the appropriate computation on each window, as if the events were fed one by one. In our case, the answer is easy: it is a `Cumulate` processor with addition as its function. If we pick any window of three successive events and feed them to a fresh instance of `Cumulate` one by one, the last event we collect is indeed the sum of all events in the window.

The second step is to encase this `Cumulate` processor within a `Window` processor, and to specify a window width (3 in our present case). A simple example of a window processor is the following piece of code:

{@snipm basic/WindowSimple.java}{/}

This code is relatively straightforward. The main novelty is the fact that the `Cumulate` processor, `sum`, is instantiated, and then given as a *parameter* to the `Window` constructor. As you can see, `sum` never appears in a call to `connect`. This is because the cumulative sum is what `Window` should compute internally on each window. Graphically, this is illustrated as follows:

{@img doc-files/basic/WindowSimple.png}{Using the `Window` processor to perform a computation over a sliding window of events.}{.6}

The `Window` processor is depicted by a box with events grouped by a curly bracket. The number under that bracket indicates the width of the window. On one side of the box is a circle leading to yet another box. This is to represent the fact that `Window` takes another processor as a parameter; in this box, we recognize the cumulative sum processor we used before. Notice how that processor lies alone in its box; as in the code fragment, it is not connected to anything. **Calling `pull` or `push` on that processor does not make sense, and will cause incorrect results, if not runtime exceptions.**

Let us now see what happens when we call `pull` on `win`. The window processor requires three events before being able to output anything. Since we just started the program, `win`'s window is currently empty. Therefore, three calls to `pull` are made on the source, in order to fetch the events 1, 2 and 3. Now that `win` has the correct number of input events, it pushes them into `sum` one by one. Since `sum` is a cumulative processor, it will successively output the events 1, 3 and 6 --corresponding to the sum of the first, the first two, and all three events, respectively. The window processor ignores all of these events except the last (6): this is the event that is returned from the first call to `pull`:

    First window: 6.0

Things are slightly different on the second call to `pull`. This time, `win`'s window already contains three events; it only needs to discard the first event it received (1), and to let in one new event at the other end of the window. Therefore, it makes only one `pull` on `source`; this produces the event 4, and the contents of the window become 2, 3 and 4. As we can see, the window of three events has shifted one event forward, and now contains the second, third and fourth event of the input stream.

The window processor cannot push these three events to `sum` immediately. Remember that `sum` is a cumulative processor, and that it has already received three events. Pushing three more would not result in the sum of events in the current window. In fact, `sum` has a "memory", which must be wiped so that the processor returns to its original state. Every processor has a method allowing this, called `reset`. `Window` first calls <!--\index{Processor@\texttt{Processor}!reset@\texttt{reset}} \texttt{reset}-->`reset`<!--/i--> on `sum`, and then proceeds to push the three events of the current window into it. The last collected event is 2+3+4=9, and hence the second line printed by the program is:
    
    Second window: 9.0

The process then restarts for the third window, exactly in the same way as before. This results in the third printed line:

    Third window: 12.0

Computing an average over a sliding window is a staple of event stream processing. This example pops up in every textbook on the topic, and virtually all event stream processing engines provide facilities to make such kinds of computations. However, typically, sliding windows only apply to streams of numerical values, and the computation over each window is almost always one of a few <!--\index{aggregation function} \emph{aggregation}-->*aggregation*<!--/i--> functions, such as `min`, `max`, `avg` (average) or `sum`. BeepBeep distinguishes itself from most other tools in that `Window` computations are much more generic. Basically, **any computation can be encased in a sliding window**. To prove our point, consider the following chain of processors:

{@img doc-files/basic/WindowEven.png}{Sliding windows can be applied on streams that are not numeric.}{.6}

{@snipi basic/WindowEven.java}{/}

A numerical stream is passed into an `ApplyFunction` processor; the function evaluates whether a number is even, using a built-in function called {@link jdc:ca.uqac.lif.cep.util.Numbers.IsEven IsEven}. This function takes a number as input, and returns a Boolean value. This stream of *Booleans* is then piped into a `Window` processor, which will handle windows of Booleans. On each window, a `Cumulate` processor computes the disjunction (logical "or") of all events in the window. On a given window of three successive events, the output is `true` if and only if there is at least one even number. The end result of this whole chain is a stream of Booleans; it returns `false` whenever three input events in a row are odd, and `true` otherwise.

As we can see, although this example makes use of a `Window` processor, its meaning is far from the numerical aggregation functions used in classical event stream processing systems. As a matter of fact, BeepBeep's very general way of handling windows is unique among existing stream processors.

This example also marks the first time we have a chain of processors where multiple event types are mixed. The first end of the chain manipulates numbers (green pipes), while the last part of the chain has Boolean events (grey-blue). Notice how function <!--\index{IsEven@\texttt{IsEven}} \texttt{IsEven}-->`IsEven`<!--/i--> in the diagram has two colours. The bottom part represents the input (green, for numbers), while the top part represents the output (grey-blue, for Booleans). Similarly, the input pipe of the `ApplyFunction` processor is green, while its output pipe is grey-blue, for the same reason.

## Grouping Processors

We claimed a few moments ago that "anything can be encased in a sliding window". This means that, instead of a single processor, we could give `Window` a more complex chain, like the one that computes the <!--\index{running average} running average-->running average<!--/i--> of a stream of numbers, as illustrated below.

{@img doc-files/basic/RunningAverage.png}{A chain of processors computing the running average of a stream.}{.6}

But how exactly can we give this *chain* of processors as a parameter to `Window`? Its constructor expects a *single* `Processor` object, so which one shall we give? If we pass the input fork, how is `Window` supposed to know where the output of the chain is? And conversely, if we pass the downstream processor that computes the division, how is `Window` supposed to learn where to push events?

The answer to this is a special type of processor called {@link jdc:ca.uqac.lif.cep.GroupProcessor GroupProcessor}. The <!--\index{GroupProcessor@\texttt{GroupProcessor}} \texttt{GroupProcessor}-->`GroupProcessor`<!--/i--> allows a user to encapsulate a complete chain of processors into a composite object which can be manipulated as if it were a single `Processor`. In other words, `GroupProcessor` hides its contents into a "black box", and only exposes the input and output pipes at the very ends of the chain.

Let us revisit a previous example ({@snipi basic/SumTwo.java}{/}), and use a group processor, as in the following code fragment.

{@snipm basic/GroupSimple.java}{/}

After creating a source of numbers, we create a new empty `GroupProcessor`. The constructor takes two arguments, corresponding to the input and output <!--\index{processor!arity} arity-->arity<!--/i--> of the group. Here, our group processor will have one input pipe, and one output pipe. The block of instructions enclosed inside the pair of braces put contents inside the group. The first six lines work as usual: we create a fork, a trim and a function processor, and connect them all together. The remaining three lines are specific to the creation of a group. The seventh line calls method {@link jdm:GroupProcessor#addProcessors(ca.uqac.lif.cep.Processor...) addProcessors()}; this puts the created processors inside the group object.

However, merely putting processors inside a group is not sufficient. The `GroupProcessor` has no way to know what are the inputs and outputs of the chain. This is done with calls to `associateInput()` and `associateOutput()`. The eighth line tells the group processor that its input pipe number 0 should be connected to input pipe number 0 of `fork`. The ninth line tells the group processor that its output pipe number 0 should be connected to output pipe number 0 of `add`.

It is now possible to use `group` as if it were a single processor box. The remaining lines connect `source` to `group`, and fetch a `Pullable` object from `group`'s output pipe. Graphically, this is illustrated as follows:

{@img doc-files/basic/GroupSimple.png}{Simple usage of a `GroupProcessor`.}{.45}

Note how the chain of processors is enclosed in a large rectangle, which has one input and one output pipe. The calls to `associateInput()` and `associateOutput()` correspond to the dashed lines that link the group's input pipe to the input pipe of the enclosed chain, and similarly for the output pipe.

Equipped with a `GroupProcessor`, it now becomes easy to compute the average over a sliding window we started this section with. This can be illustrated as follows:

{@img doc-files/basic/WindowAverage.png}{Computing the running average over a sliding window.}{FOOO}

The code corresponding to this picture is shown below:

{@snipm basic/WindowAverage.java}{/}

Groups can have an arbitrary input and output arity, as is shown in the example below:

{@img doc-files/basic/GroupBinary.png}{A group processor with more than one output pipe.}{.45}

Here, we create two copies of the input stream offset by one event. These two streams are sent to an `ApplyFunction` processor that evaluates function <!--\index{IntegerDivision@\texttt{IntegerDivision}} \texttt{IntegerDivision}-->`IntegerDivision`<!--/i-->, which we encountered earlier in this chapter. This function has an input and output arity of 2. We want the group processor to output both the quotient and the remainder of the division as two output streams. Since the group has two output pipes, two calls to `associateOutput` must be made. The first associates output 0 of the function processor to output 0 of the group, and the second associates output 1 of the function processor to output 1 of the group. The code creating the group is hence written as follows:

{@snipm basic/GroupBinary.java}{/}

## Decimating Events

A common task in event stream processing is to discard events from an input stream at periodic intervals. This process is called <!--\index{decimation} \textbf{decimation}-->**decimation**<!--/i-->. The two common ways to decimate events are:

- based on a fixed number of events (*count decimation*), and
- based on a fixed interval of time (*time decimation*).

In this section, we concentrate on the former. To perform count decimation, BeepBeep provides a processor called <!--\index{CountDecimate@\texttt{CountDecimate}} \texttt{CountDecimate}-->`CountDecimate`<!--/i-->. Let us push events to such a processor, as in the following code fragment.

{@snipm basic/CountDecimateSimple.java}{/}

Here, a `CountDecimate` processor is created and connected into a `Print` processor. The decimate processor is instructed to keep one event for every 3, and to discard the others. This is the meaning of value 3 passed to its constructor, which is called the *decimation interval*, as shown in the following:

{@img doc-files/basic/CountDecimateSimple.png}{Pushing events to a `CountDecimate` processor.}{.6}

The `CountDecimate` processor is designated by a pictogram in which some events are transparent, representing decimation. Like many other processors receiving parameters, the decimation interval is written on one side of the box. Let us now push the integers 0 to 9 into this processor, and watch the output printed at the console. The result is the following:

    0,3,6,9,

As expected, the processor passed the first event (0), discarded the next two (1 and 2), then passed the fourth (3), and so on.

An important point must be made when `CountDecimate` is used in pull mode, as in the following chain:

{@img doc-files/basic/CountDecimatePull.png}{Pulling events from a `CountDecimate` processor.}{.6}

In such a case, the events received by each call to `pull` will be 1, 4, 7, etc. That is, after outputting event 1, the decimate processor does not ignore our next two calls to `pull` by returning nothing. Rather, it pulls three events from the queue source and discards the first two.

The decimate processor can be mixed with the other processors seen so far. For example, we have seen earlier how we can use a `Window` processor to calculate the sum of events on a sliding window of width *n*. We can affix a `CountDecimate` processor to the end of such a chain to create what is called a <!--\index{window!hopping} \textbf{hopping window}-->**hopping window**<!--/i-->. Contrary to sliding windows, where the content of two successive windows overlap, hopping windows are disjoint. For example, one can compute the sum of the first five events, then the sum of the next five, and so on. The difference between the two types of windows is illustrated in the following figure; sliding windows are shown at the left, and hopping windows are shown at the right.

{@img doc-files/basic/Hopping.png}{Difference between a sliding window (left) and a hopping window (right).}{.6}

As one can see, hopping windows can be created out of sliding windows of width *n* by simply keeping one window out of every *n*.

## Filtering Events

The `CountDecimate` processor acts as a kind of filter, based on the events' position. If an input event is at a position that is an integer multiple of the decimation interval, it is sent in the output; otherwise, it is discarded. Apart from the `Trim` processor we have encountered earlier, this is so far the only way to discard events from an input stream.

The {@link jdc:ca.uqac.lif.cep.tmf.Filter Filter} processor allows a user to keep or discard events from an input stream in a completely arbitrary way. In its simplest form, a <!--\index{Filter@\texttt{Filter}} \texttt{Filter}-->`Filter`<!--/i--> has two input pipes and one output pipe. The first input pipe is called the *data pipe*: it consists of the stream of events that needs to be filtered. The second input pipe is called the *control pipe*: it receives a stream of Boolean values. As its name implies, this Boolean stream is responsible for deciding what events coming into the data pipe will be kept, and what events will be discarded. The event at position *n* in the data stream is sent to the output, if and only if the event at position *n* in the control stream is the Boolean value `true`.

As a first example, consider the following piece of code, which connects two sources to a `Filter` processor:

{@img doc-files/basic/FilterSimple.png}{Filtering events.}{.6}

The first source corresponds to the data stream, and in this case consists of a sequence of arbitrary numbers. The second source corresponds to the control stream, which we populate with randomly chosen Boolean values. These two sources are connected to a `Filter`. By convention, the *last* input pipe of a filter is the control stream; the remaining input pipes are the data streams. It is a common mistake to connect what is intended to be the control stream into the wrong pipe of the filter. This is illustrated below:

{@snipm basic/FilterSimple.java}{/}

The `Filter` is represented by a box with a traffic light as a pictogram. Since the data stream is made of numbers, both the data input pipe and the output pipes are coloured in green. Obviously, the control pipe, which is made of Booleans, is always grey-blue.

The last part of the program, as usual, simply pulls on the output of the `Filter` and prints what is received. In this case, the output of the program is:

    Output event #0 is 6
    Output event #1 is 3
    Output event #2 is 8
    Output event #3 is 1
    Output event #4 is 4

As we can see, the events from `source_values` that are output are only those at a position where the corresponding value in `source_bool` is `true`. At position 0, the event in `source_bool` is `true`, so the value 6 is output. On the second call to `pull`, `filter` pulls on both its input pipes; it receives the value 5 from `source_values`, and the value `false` from `source_bool`. Since the control pipe holds the value `false`, the number 5 has to be discarded, meaning that `filter` has nothing to output. Consequently, it pulls again on its input pipes to receive another event front. This time, it receives the pair 3/`true`, so it can return 3 as its second event.

Since the output of events depends entirely on the contents of the control stream, the relative positions of the events in the input and output streams do not follow any predictable pattern:

- Event at position 0 in the output corresponds to event at position 0 in the input;
- Event at position 1 in the output corresponds to event at position 2 in the input;
- Event at position 2 in the output corresponds to event at position 3 in the input;
- Event at position 3 in the output corresponds to event at position 7 in the input.

Note also that on a call to `pull`, a filter *must* return something. Therefore, it will keep pulling on its input pipes until it receives an event front where the control event is `true`. If that event never comes, **the call to `pull` will never end**. As a small exercise, try to replace all the Boolean values in `source_bool` by `false`, and run the program again. You will see that nothing is printed on the console, and that the program loops forever.

Like other processors in BeepBeep, the filtering mechanism is very generic and flexible. Any stream can be filtered, as long as a control stream is provided. As we have seen in our example, this control stream does not even need to be related to the data stream: any Boolean stream will do. In many cases, though, the decision on whether to filter an event or not depends on the event itself. For example, we would like to keep an event only if it is an even number. How can we accommodate such a situation?

The solution is to combine the `Filter` with another processor we have seen earlier, the `Fork`. From a given input stream, we use a fork to create two copies. The first copy is our data stream, and is sent directly to the filter's data pipe. We then use the second copy of the stream to evaluate a condition that will serve as our data stream. This is exactly what is done in the following example:

{@img doc-files/basic/FilterConditionSimple.png}{Filtering events.}{.6}

{@snipm basic/FilterConditionSimple.java}{/}

As we can see, the bottom part of the chain passes the input stream through an `ApplyFunction` processor, which evaluates the function {@link jdc:ca.uqac.lif.cep.util.Numbers.IsEven IsEven}. This function turns the stream of numbers into a stream of Booleans, which is then connected to the filter's control pipe. The end result of this chain is to produce an output stream where all odd numbers from the input stream have been removed. Obviously, if a more complex condition needs to be evaluated, a `FunctionTree` can be used instead of a single function. As a matter of fact, users are not limited to a single `ApplyFunction` processor, and can create whatever chain of processors they wish, as long as it produces a Boolean stream!

## Slicing a Stream

The `Filter` is a powerful processor in our toolbox. Using a filter, we can take a larger stream and create a "sub-stream" --that is, a stream that contains a subset of the events of the original stream. Using forks, we can even create *multiple* different sub-streams from the same input stream. For example, we can separate a stream of numbers into a sub-stream of even numbers on one side, and a sub-stream of odd numbers on the other. This is perfectly possible, as the picture below shows.

{@img doc-files/basic/OddEvenSubstreams.png}{Creating two sub-streams of events: a stream of odd numbers, and a stream of even numbers.}{.45}

However, we can see that this drawing contains lots of repetitions. The chains of processors at both ends of the first fork are almost identical; the only difference is the function passed to each instance of `ApplyFunction`: in the top chain, even numbers are kept, while in the bottom chain, a negation is added to the condition, so that odd numbers are kept. The two output pipes at the far right of the diagram hence produce a stream of even numbers (at the top) and a stream of odd numbers (at the bottom).

Suppose, however, that we need to perform further processing on both these sub-streams. For example, we would like to compute their cumulative sum. We would need to repeat the same chain of processors at the end of both pipes. Suppose further that we would like to create *three* sub-streams instead of two, by filtering events according to their value modulo 3 (which returns either 0, 1 or 2): we would then need to copy-paste even more processors and pipes. There should be a better way to proceed.

Fortunately, there is. In fact, there are many situations in which we would like to separate a stream into multiple sub-streams, and perform the same computation over each of these sub-streams separately. Because this situation is a recurrent one, BeepBeep provides `Slice`, a processor dedicated to this specific task.

Creating a <!--\index{Slice@\texttt{Slice}} \texttt{Slice}-->`Slice`<!--/i--> processor works in a similar way to `Window`. Two parameters are needed to construct `Slice`:

1. The first is a **slicing function**, which is evaluated on each incoming event. The value of that function determines to which sub-stream that event belongs. Typically, there will exist as many sub-streams as there are possible output values for the slicing function. These sub-streams are called *slices*, hence the name of the processor.
2. The second is a **slice processor**. A different instance of this processor is created for each possible value of the slicing function. When an incoming event is evaluated by the slicing function, it is then pushed to the instance of the slice processor associated to that value.

As with the `Window` processor, the `Slice` processor expects a single object as its slice processor. To pass a chain of multiple processors, it must be encapsulated into a `GroupProcessor`, as seen previously.

To illustrate the operation of a slice processor, consider the following code example:

{@snipm basic/SlicerSimple.java}{/}

In this program, we first create a simple source of numbers, and connect it to an instance of `Slice`. In this case, the slicing function is the {@link jdc:ca.uqac.lif.cep.functions.IdentityFunction IdentityFunction}: this function returns its input as is. The slice processor is a simple counter that increments every time an event is received, which we encapsulate into a `GroupProcessor`. Since there will be one such counter instance for each different input event, the slicer effectively keeps count of how many times each value has been seen in its input stream. Graphically, this can be represented as: 

{@img doc-files/basic/SlicerSimple.png}{Using a `Slice` processor.}{.6}

The `Slice` processor is represented by a box with a piece of cheese (yes, cheese) as its pictogram. Like the `Window` processor, one of its arguments (the slicing function) is placed on one side of the box, and the other argument (the slice processor) is linked to the box by a circle and a line. We took the liberty of putting the slice processor inside a "cloud" instead of a plain rectangle. As expected, this slice processor is itself a group that encapsulates a `TurnInto` and a `Cumulate` processor.

Let us now see what happens when we start pulling events on `slicer`. On the first call to `pull`, `slicer` pulls on the source and receives the number 1. It evaluates the slicing function, which (obviously) returns 1. It then seeks into its memory for an instance of the slice processor associated to the value 1. Since there is none, `slicer` creates a new copy of the slice processor, and pushes the value 1 into it. It then collects the output from that slice processor, which is (again) the value 1.

The last step is to return something to the call to `pull`. What a slicer outputs is always a Java `Map` object. The keys of that map correspond to values of the slicing function, and the value for each key is the last event produced by the corresponding slice processor. Every time an event is received, the slicer returns as its output the newly updated map. At the beginning of the program, the map is empty; this first call to `pull` will add a new entry to the map, associating the value 1 to the slice "1". The first line printed by the program is the contents of the map, namely:

    {1=1.0}

The second call to `pull` works in a similar fashion. The slicer receives the value 6 from the source; no slice processor exists for that value, so a new one is created. Event 6 is pushed into it, and the output value (1) is collected. A new entry is added to the map, associating slice 6 to the value 1. Note that the previous entry is still there, so that the next printed line is:

    {1=1.0, 6=1.0}

A similar process occurs for the next three input events, creating three new map entries:

    {1=1.0, 4=1.0, 6=1.0}
    {1=1.0, 3=1.0, 4=1.0, 6=1.0}
    {1=1.0, 2=1.0, 3=1.0, 4=1.0, 6=1.0}

Something slightly different happens in the next call to `pull`. The `slicer` receives the number 1, evaluates the slice function, which returns 1. It turns out that this is a value for which a slice processor already exists. Therefore, `slicer` retrieves that processor instance, and pushes the value 1 into it. Note that for this slice processor, this is the *second* time it is given an event; since it acts as a counter, it returns the value 2. Then, `slicer` updates its map by associating the value 2 to slice 1, which replaces the original entry. The map that is returned on the call to `pull` is:

    {1=2.0, 2=1.0, 3=1.0, 4=1.0, 6=1.0}

The end result of this processor chain is that it keeps track of how many times each number has been seen in the input stream so far.

As we can see, each copy of the slice processor is fed the sub-trace of all events for which the slicing function returns the same value. Different results can be obtained by using a different slicing function. Let us go back to our original example, where we would like to create sub-streams of odd and even numbers, and to compute their cumulative sum separately. This time, the slicing function will determine if a number is odd or even; this task can be done using the function <!--\index{IsEven@\texttt{IsEven}} \texttt{IsEven}-->`IsEven`<!--/i-->. Passing it to the `Slice` processor will generate two streams: one comprising the numbers for which `IsEven` returns `true` (the even numbers), and another comprising the numbers for which `IsEven` returns `false` (the odd numbers). We then affix as the slice processor a `GroupProcessor` that encapsulates a chain computing the cumulative sum of numbers.

{@img doc-files/basic/SlicerOddEven.png}{Adding odd and even numbers separately.}{.6}

{@snipi basic/SlicerOddEven.java}{/}

The end result of this program is a map with two keys (`true` and `false`), associated with the cumulative sum of even numbers and odd numbers, respectively.

## Keeping the Last Event

In some cases, it may be desirable to take an action only when all the events from an input source have been consumed. For example, one may want to decimate a stream by keeping one event every 100, but still output the last event if the stream has, say, 560 events. When writing a processor chain that produces a plot from an input file, it can be useful to first read and process all the file, before triggering the generation of the plot; this would bring a better performance than producing a new plot upon every input event. In BeepBeep, a few processors have functionalities allowing users to deal with the "last" event of a stream.

The first is called <!--\index{KeepLast@\texttt{KeepLast}} \texttt{KeepLast}-->`KeepLast`<!--/i-->. As its name implies, its task is to discard every event received from upstream, and to output only the last. This can be illustrated by the following program:

{@img doc-files/basic/KeepLastPull.png}{Keeping the last event.}{.6}

A `QueueSource` is connected to the `KeepLast` processor, represented by a box with a checkered flag. In code, this corresponds to the following program:

{@snipm basic/KeepLastPull.java}{/}

Notice how the source is instructed *not* to loop through its list of events. This means that after outputting the number 5, any subsequent calls to `hasNext` will return `false`. The program then enters a loop, and pulls events from the output of the `KeepLast` processor until none is available. Running this program produces the single number `5`, which, indeed, is the last event produced by the upstream source `src`.

Once the `KeepLast` processor has output the last event received from upstream, it does not return any other event. Subsequent calls to `hasNext` on `kl` will all return `false`, which means that the loop in the program is executed only once. Conversely, `KeepLast` will keep pulling on its upstream processor until it receives the indication that the last event has been produced. This means that on a processor chain that has "no end", such as a `QueueSource` that loops through its list of events forever, the call to `hasNext` on `KeepLast` will never return.

In pull mode, Identifying the last event can easily be done, precisely by looking at the return value of `hasNext` when pulling on an upstream processor. The situation is less obvious in push mode, such as in the following diagram:

{@img doc-files/basic/KeepLastPush.png}{Pushing events on the `KeepLast` processor.}{.6}

How can a processor push an event, and indicate that this is the last? This is illustrated by the following program:

{@snipm basic/KeepLastPull.java}{/}

Here, events are repeatedly pushed to the `KeepLast` processor. The first three calls to `push` have no noticeable effect: the `Print` processor does not print anything. Only the last line of the program will trigger the printing of an event. As a matter of fact, the `Pushable` interface defines a method called <!--\index{Pushable@\texttt{Pushable}!notifyEndOfTrace@\texttt{notifyEndOfTrace}} \texttt{notifyEndOfTrace}-->`notifyEndOfTrace`<!--/i-->. Calling this method is the way of telling the underlying processor: "the last event I pushed was the last event of the stream". In the case of `KeepLast`, this triggers a call to `push` on its downstream processor, containing the last event that was received. Obviously, it makes no sense to call `notifyEndOfTrace`, and push more events afterwards. As a matter of fact, the behaviour of a processor in such a situation is undefined, and it is not recommended doing so.

Not all processors react to a call to `notifyEndOfTrace`. For example, `ApplyFunction` does nothing special when reaching the end of an input stream. However, the `CountDecimate` processor *can* be told to output the last event of a stream, regardeless of whether it is placed at an integer multiple of the decimation interval. To this end, it suffices to pass the Boolean value `true` as a second argument to `CountDecimate`'s constructor. To illustrate this, we revisit an earlier example using `CountDecimate` in the following program.

{@snipm basic/CountDecimateLast.java}{/}

The difference lies in the fact that `CountDecimate` has been instantiated with `true`; the processor behaves normally when calling `push`, and sends to the `Print` processor every third input event. However, the call to `notifyEndOfTrace` triggers the output of the last event. Therefore, the program prints at the console:

```
0,3,6,7,
```

Notice how number 7 should not have been output under normal circumstances.

- - -

In this chapter, we have covered the dozen or so fundamental processors provided by BeepBeep's core. These processors allow us to manipulate event streams in various ways: applying a function to each event, filtering, decimating, slicing and creating sliding windows. Most of these processors are "type agnostic": the actual type of events they handle has no influence in the way they operate. Therefore, a large number of event-processing tasks can be achieved by appropriately combining these basic building blocks together. We could show many other examples of graphs combining processors in various ways; these were rather left as exercises in the section below. A little time is required to get used to decomposing a problem in terms of streams; this is why we recommend that you try some of these exercises and develop your intuition before moving on to the next chapter.

## Exercises

1. Write a processor chain that computes the sum of each event with the one two positions away in the stream. That is, output event 0 is the sum of input events 0 and 2; output event 1 is the sum of input events 1 and 3, and so on. You can do this using a very slight modification to one of the examples in this chapter.

2. Using `CountDecimate` and `Trim`, write a processor chain that outputs events at position 3*n*+1 and discards the others. That is, from the input stream, the output should contain events at position 1, 4, 7, 10, etc.

3. Using only the `Fork`, `Trim` and `ApplyFunction` processors, write a processor chain that computes the sum of all three successive events. (Hint: you will need two `Trim`s.)

4. Write a processor chain that outputs events at position *n*². That is, from the input stream, the output should contain events at position 1, 4, 9, 16, etc.

5. Write a processor chain that computes the Fibonacci sequence. The sequence starts with numbers 1 and 1; every subsequent number is the sum of the previous two.

6. Write a processor chain receiving a stream of numerical values, and which flattens to zero any input value that lies below a predefined threshold *k*. The chain should leave the values greater than *k* as they are. (Hint: use a function called <!--\index{IfThenElse@\texttt{IfThenElse}} \texttt{IfThenElse}-->`IfThenElse`<!--/i-->.)

6. Write a `GroupProcessor` that takes a stream of numbers, and alternates their sign: it multiplies the first event by -1, the second by 1, the third by -1, and so on. This processor only needs to work in pull mode.

7. The value of pi can be estimated using <!--\index{Leibniz formula} the-->the<!--/i--> [Leibniz formula](https://en.wikipedia.org/wiki/Leibniz_formula_for_%CF%80). According to this formula, pi is four times the infinite expression 1/1 - 1/3 + 1/5 - 1/7 + 1/9... Create a chain of processors that produces an increasingly precise approximation of the value of pi using this formula.

8. Write a processor chain that computes the running variance of a stream of numbers. The variance can be calculated by the expression E[*X*²]-E[*X*]², where E[*X*] is the running average, and E[*X*²] is the running average of the square of each input event.

9. Write a processor chain that takes as input a stream of numbers, and outputs a stream of Booleans. Output event at position *i* should be true if and only if input event at position *i* is more than two standard deviations away from the running average of the stream at this point. (Hint: the standard deviation is the square root of the running variance.)

10. Write a processor chain that prints "This is a multiple of 5" when a multiple of 5 is pushed, and prints "This is something else" otherwise.

11. From a stream of Boolean values, write a processor chain that computes the number of times a window of width 3 contains more `false` than `true`. That is, from the input stream TTFFTFTT, the processor should output the values 0, 1, 2, 3, 3, 3.

12. Write a processor chain that counts the number of times a positive number is immediately followed by a negative number.

<!-- :wrap=soft: -->