The standard palettes
=====================

A large part of BeepBeep's functionalities is dispersed across a number of *palettes*. These palettes are additional libraries (i.e. JAR files) that define new processors or functions for use along with BeepBeep's core elements. Each palette is optional, and has to be included in your project only if you need its contents.

This modular organization has three advantages. First, palettes are a flexible and generic way to extend the engine to various application domains, in ways unforeseen by its original designers. Second, they make the engine's core (and each palette individually) relatively small and self-contained, easing the development and debugging process. Finally, it is hoped that BeepBeep's palette architecture, combined with its simple extension mechanisms, will help third-party users contribute to the BeepBeep ecosystem by developing and distributing extensions suited to their own needs.

There exist palettes for many things: reading special file types, producing plots, accessing a network, and so on. In this chapter, we explore a few "standard" palettes that are more frequently used than others.

## Tuples

Input files are seldom made of a single value per line of text. A more frequent file format is called **comma-separated values** (<!--\index{CSV@CSV (file format)}CSV-->CSV<!--/i-->). In such a format, each line contains the value of multiple **attributes**, separated by a comma. The following gives an example of such a file:

    # This is a simple file in CSV format
    
    A,B,C
    3,2,1
    1,7,1
    4,1,2
    1,8,3
    6,3,5

Blank lines and lines that begin with the hash symbol (`#`) are typically ignored (although the latter is not standard). The first non-ignored line in the file gives the *name* of each attribute. In the example above, the file defines three attributes named "A", "B" and "C". All the remaining lines of the file defines what are called <!--\index{tuple} \textbf{tuples}-->**tuples**<!--/i-->; a tuple is a data object that associates each attribute to a value. For example, the fourth line of the file defines a tuple that associates attribute A to value 3, attribute B to value 2, and attribute C to value 1. In other words, a CSV file is similar to a **table** in a relational database.

### Reading tuples

The following program reads a CSV file called `file1.csv`, and extracts tuples from this file one by one:

{@snipm tuples/CsvReaderExample.java}{/}

The first two lines are now familiar: they consist of opening an `InputStream` on a file, and passing this stream to a `ReadLines` processor to read it line by line. The next instruction creates a new processor called a <!--\index{TupleFeeder@\texttt{TupleFeeder}} \texttt{TupleFeeder}-->`TupleFeeder`<!--/i-->. This processor receives lines of text, and returns on its output pipe `Tuple` objects. The rest of the program simply pulls and prints these tuples. The output of this program is:

```
((A,3),(B,2),(C,1))
((A,1),(B,7),(C,1))
((A,4),(B,1),(C,2))
((A,1),(B,8),(C,3))
((A,6),(B,3),(C,5))
```

As you can see from the format of the output, a tuple can also be seen as a set of attribute-value pairs. `Tuple` objects implement Java's <!--\index{Map@\texttt{Map} (interface)} \texttt{Map}-->`Map`<!--/i--> interface; therefore, their contents can be queried just like any other associative map:

{@snipm tuples/CsvReaderExample.java}{\*}

If `tup` refers to the last `Tuple` pulled from `tuples`, the previous lines of code will print:

```
6,String
```

Note that **the values in tuples produced by `TupleFeeder` are always strings**. That is, `TupleFeeder` does not try to be smart and guess if a string is actually a number.

Graphically, this program can be represented as follows:

{@img doc-files/tuples/CsvReaderExample.png}{Converting strings into tuples.}{.6}

This drawing introduces the symbol for the `TupleFeeder`, whose pictogram on the box represents a tuple. It also shows the color we use to represent tuple feeds (brown/orange).

### Querying tuples

The previous example has shown us how to read tuples, but not how to manipulate them. The `tuples` palette defines a few handy `Function` objects that allow us, among other things, to fetch the value of an attribute and also to merge tuples. From the same input file as above, let us create an output stream made of the sum of attributes A and B in each line. The following piece of code performs exactly that:

{@snipm tuples/SumAttributes.java}{/}

This program is probably better explained through its graphical representation, which goes as follows:

{@img doc-files/tuples/SumAttributes.png}{Adding two attributes in each tuple.}{.6}

From a `ReadLines` processor, a `TupleFeeder` is instantiated. The stream of tuples is then forked along two branches. In the first branch, the value of attribute "A" for each tuple is extracted. This is done by using an `ApplyFunction` processor, and giving to this processor an instance of a new function called <!--\index{FetchAttribute@\texttt{FetchAttribute}} \texttt{FetchAttribute}-->`FetchAttribute`<!--/i-->. When instantiated, function `FetchAttribute` is given the name of the attribute to fetch in the tuple. This value (a String) is converted into a number and sent into an `ApplyFunction` processor that computes a sum. The same thing is done along the bottom branch for attribute "B". From the same input file as above, the output of this program is:

```
5.0
8.0
5.0
9.0
9.0
```

which indeed corresponds to the sum of A and B in each line. However, this processor chain is needlessly verbose. The successive application of all three functions can be collapsed into a single function tree, yielding this much simpler graph:

{@img doc-files/tuples/SumAttributesTree.png}{Adding two attributes in each tuple (alternate version).}{.6}

We leave as an exercise to the reader the task of writing this processor chain in code.

### Other tuple functions

The `tuples` palette provides a few other functions to manipulate tuples. We mention them briefly:

- The function <!--\index{ScalarIntoTuple@\texttt{ScalarIntoTuple}} \texttt{ScalarIntoTuple}-->`ScalarIntoToTuple`<!--/i--> takes a scalar value *x* (for example, a number) and creates a tuple with a single attribute-value pair A=*x*. Here "A" is a name passed to the function when it is instantiated.

- The function <!--\index{MergeTuples@\texttt{MergeTuples}} \texttt{MergeTuples}-->`MergeTuples`<!--/i--> merges the key-value pairs of multiple tuples into a single tuple. If two tuples have the same key, the value in the resulting tuple is that of <em>one</em> of these tuples; which one is left undefined. However, if the tuples have the same value for their common keys, the resuting tuple is equivalent to that of a elational JOIN operation.

- The function <!--\index{BlowTuples@\texttt{BlowTuples}} \texttt{BlowTuples}-->`BlowTuples`<!--/i--> breaks a single tuple into multiple tuples, one for each key-value pair of the original tuple. The output of this function is a *set* of tuples, and not a single tuple.

- The function <!--\index{ExpandAsColumns@\texttt{ExpandAsColumns}} \texttt{ExpandAsColumns}-->`ExpandAsColumns`<!--/i--> transforms a tuple by replacing two key-value pairs by a single new key-value pair. The new pair is created by taking the value of a column as the key, and the value of another column as the value. For example, with the tuple: {(foo,1), (bar,2), (baz,3)}, using "foo" as the key column and "baz" as the value column, the resulting tuple would be: {(1,3), (bar,2)}. The value of foo is the new key, and the value of baz is the new value. If the value of the "key" pair is not a string, it is converted into a string by calling its `toString()` method (since the key of a tuple is always a string). The other key-value pairs are left unchanged.

## Finite-state machines

Sometimes, a stream is made of events representing a sequence of "actions". It may be interesting to check whether these actions follow a predefined pattern, which stipulates in what order the actions in a stream can be observed to be considered valid. A convenient way of specifiying these patterns is through a device called a <!--\index{finite-state machine} \emph{finite-state machine}-->*finite-state machine*<!--/i--> (FSM). BeepBeep's FSM palette allows users to create such machines.

### Defining a Moore machine

As a simple example, suppose that a log contains a list of calls on a single Java `Iterator` object. Typical method calls on an iterator are `next`, `hasNext`, `reset`, etc. Such a log could look like this:

```
hasNext
next
hasNext
hasNext
next
reset
...
```

The proper use of an iterator stipulates that one should never call method `next()` before first calling method `hasNext()`. The correct ordering of these calls can be expressed by a finite-state machine with three states like in the following picture.

{@img HasNextFSM.png}{A finite-state machine representing the constraint that `next()` cannot be called before calling `hasNext()` first.}{.6}

In this FSM, states are numbered 0, 1 and 2; transitions bewtween states are labelled with the method name they represent; for example, when the machine is in state 1, receiving a `next` event will make it move to state 0. One of these states is called the *intitial state*, and is identified by an arrow that is not attached to any source state. In our case, the initial state is 0. The "star" label on state 2's arrow indicates that this transition matches any incoming event.

In BeepBeep's FSM palette, finite-state machines are materialized by an object called <!--\index{MooreMachine@\texttt{MooreMachine}} \texttt{MooreMachine}-->`MooreMachine`<!--/i-->; the origin of that name will be explained a little later. The creation of the machine is made by the following code example:

{@snipm finitestatemachines/SimpleMooreMachine.java}{/}

The first step is to create an empty `MooreMachine`; this machine receives one stream of events as its input, and produces one stream of events as its output --hence the two `1` in the object's constructor. In a `MooreMachine`, each state must be given a unique numerical identifier. Rather than hard-coding these numbers, we adopt a cleaner approach, and define symbolic constants for the three states of the Moore machine. It is recommended that the actual numbers for each state form a contiguous interval of integers starting at 0. Here, we associate numbers 0, 1 and 2 to the constants `UNSAFE`, `SAFE` and `ERROR`, respectively.

We are now ready to define the transitions (i.e. the "arrows" between states) for this machine. This is just a tedious enumeration of all the arrows that are present in the graphical representation of the FSM. Adding a transition to the machine is done through a method called `addTransition()`. This method must provide the number of the "source" state *n*<sub>*s*</sub>, and a <!--\index{Transition@\texttt{Transition}} \texttt{Transition}-->`Transition`<!--/i--> object. There exist multiple types of such objects, but a frequent subclass is the <!--\index{FunctionTransition@\texttt{FunctionTransition}} \texttt{FunctionTransition}-->`FunctionTransition`<!--/i-->. This object specifies:

- a `Function` *f* that determines when the transition should fire. This function must have the same input arity as the machine itself, and return a Boolean value;
- the number of the "destination" state *n*<sub>*d*</sub>.

Intuitively, a `FunctionTransition` transition stipulates that when the machine is currently in state *n*<sub>*s*</sub> and receives an event *e*, if *f*(*e*) returns `true`, the machine shall move to state *n*<sub>*d*</sub>. For example, the first line states that in state 0 (`UNSAFE`), if the incoming event is "hasnext", go to state 1 (`SAFE`). The condition itself is expressed by creating a `FunctionTree` that checks if the incoming event (which is put into the `StreamVarible`) is equal to the `Constant` "hasnext". By default, the first state number that is ever given to the `MooreMachine` object is taken as the initial state of that machine. So here, `UNSAFE` will be the initial state. In BeepBeep's implementation of FSMs, there can be only one initial state.

The remaining instructions simply add the other transitions to the machine. A special remark must be made about state 2, which is a *sink state*; in other words, once you reach this state, you stay there forever. These states are typically used to indicate that the system has entered into an irrecoverable error condition. A possible way to say so is to define the condition on its only transition as the `Constant` true; it will fire whatever the incoming event may be.

These seven lines of code completely define our FSM. However, as it is, `machine` is not instructed to output any event at any time. We said earlier that this FSM is of a particular kind, called a *Moore machine*. Such a machine outputs a symbol when jumping into a new state. This means that we can associate arbitrary events to each states of the machine. In our case, let us simply associate the Boolean values `true` to states `UNSAFE` and `SAFE`, and the value `false` to state `ERROR`. This is done using a method called `addSymbol()`:

{@snipm finitestatemachines/SimpleMooreMachine.java}{\*}

Method `addSymbol` takes as arguments the number of a state, and a `Function` object that is expected to return the desired symbol. This function is expected to ignore its input arguments, and to have the same output arity as the Moore machine itself. In the present case, the function is a simple `Constant` that returns a `Boolean` object. We stress that the machine does not need to return a Boolean, and that any Java object can be associated to a state.

We are now done. We can try our Moore machine on a sequence of events, by connecting it upstream to a `QueueSource` as usual, and by pulling the events that come out of it.

{@snipm finitestatemachines/SimpleMooreMachine.java}{!}

From the input events given to the source, the output of the machine should be:

```
true
true
true
true
true
false
false
```

A complete graphical representation of the chain of processors in this program would be the following. Note how the transitions that were simply labelled with a method name in the original picture are replaced by a `Function` tree that checks for equality. Note also that the state numbers have been omitted, but that the output event associated to each state is shown instead.

{@img doc-files/finitestatemachines/SimpleMooreMachine.png}{A complete representation of the `MooreMachine` example.}{.6}

### Using the machine's context

We have seen in the previous chapter how each `Processor` object carries an associative map called the <!--\index{processor!context} \texttt{Context}-->`Context`<!--/i-->. A `MooreMachine` is one example of a processor that can put this `Context` object to good use, by employing it as a storage location for local variables. These variables can be initialized by the `MooreMachine` when it is created, modified when a transition is taken, and their value can be used in the conditions that determine which transition should fire. In this respect, such variables work in a very similar way to the same kind of local variables one can find in UML state machines.

Let us modify the previous example to illustrate the use of variables. We shall tweak the state machine, and impose the (arguably bizarre) constraint that the number of calls to `hasNext()` between each call to `next()` should increase by 1 every time. Since this constraint involves counting, and we impose no upper bound on the count, it cannot be represented by a classical finite-state machine. However, this becomes possible using additional variables. The principle is to update two variables: *c* keeps the number of calls to `hasNext()` since the last call to `next()`, and *n* keeps the expected number of calls to `hasNext()` in the current "cycle". Every time `hasNext()` is called, *c* should be incremented. Every time `next` is called, *c* should be reset to zero and *n* should be incremented. An error occurs whenever `hasNext` is called and *c* is greater than *n*, or when `next` is called and *c* is not equal to *n*. This could be illustrated as follows:

{@img HasNextFSMContext.png}{A finite-state machine representing the constraint our our second example.}{.6}

This FSM looks very different as the previous one. As you can see, transitions now have conditions attached to them: these conditions are called *guards*. For example, the loop transition on the left-hand side of state 0 can be fired only if the incoming event is `hasNext` *and* the current value of local variable *c* is less than the current value of local variable *n*. In addition, transitions now also have *side effects* --that is, actions that change the processor's internal configuration other than simply moving it from one state to another. These side effects, in the figure, are seprated from the guard by a slash, and consist of assignments to the local variables. When a state has multiple outgoing transitions, the `*` is interpreted as the transition that fires when no other does.

Take two minutes to convince yourself that this "extended" Moore machine indeed corresponds to the constraint we want to enforce. Let us now attempt to create this machine in code using BeepBeep processors. The first step is to create an empty 1:1 `MooreMachine` object, and to set variables *c* and *n* to their initial values. This is done in the following code snippet. We use `Processor`'s method `setContext` to give values to two new keys, called `c` and `n`, which are added to `machine`'s `Context` object:

{@snipm finitestatemachines/ExtendedMooreMachine.java}{/}

The next step is to define transitions, as before. Let us first consider the case of the loop on state 0 located on the left-hand side of the figure. The guard on this transition should express the condition that:

- the current event is the string `hasNext` **and**
- the current value of *c* in the processor's context is less than the current value of *n*

Such a Boolean function can be created with the help of a `FunctionTree`, as is shown by the code below:

{@snipm finitestatemachines/ExtendedMooreMachine.java}{!}

The novelty of this line of code is the use of a new type of variable, called the <!--\index{ContextVariable@\texttt{ContextVariable}} \texttt{ContextVariable}-->`ContextVariable`<!--/i-->. When a `Function` object is evaluated inside a `Processor` (as will be the case here), a `ContextVariable` returns the value associated to the specified key in the processor's `Context` at the moment the function is evaluated. Therefore, in the present case, the function will compare the current value of *c* and *n*, every time the guard is evaluated by the Moore machine.

The transition has a guard, but also a side effect, which in this case is to increment the value of *c* by one. To indicate such a side effect, we need to use yet another new object, called <!--\index{ContextAssignment@\texttt{ContextAssignment}} \texttt{ContextAssignment}-->`ContextAssignment`<!--/i-->. The constructor of the `ContextAssignment` takes two arguments: a string that indicates the context key to modify, and a `Function` object whose return value determines the new value associated to this key. The code for creating this object looks like this:

{@snipm finitestatemachines/ExtendedMooreMachine.java}{\*}

In our case, the function we pass is a `FunctionTree` that adds the constant 1 to the current value of *c* in the processor's context. This has indeed for effect of incrementing the processor's variable *c* by one.

We are now ready to add the transition to the Moore machine. This is done, as before, by using method `addTransition`:

{@snipm finitestatemachines/ExtendedMooreMachine.java}{%}

Note that, this time, method `addTransition` takes three arguments: the `Function` corresponding to the guard, the number of the destination state, and the `ContextAssignment` corresponding to the side effect to apply on that transition. As a matter of fact, `addTransition` accepts any number of `ContextAssignment`s after its first two arguments; this makes it possible to change the value of multiple context keys in the same transition.

Once we understand these concepts, defining the other self-loop on state 0 becomes straightforward. Instead of creating separate `guard` and `asg` objects, we put everything into the same method call:

{@snipm finitestatemachines/ExtendedMooreMachine.java}{#}

We agree that this notation tends to be a bit verbose, but in counterpart, it makes the definition of transitions and side effects very flexible.

A last remark must be made about the definition of the "star" transitions. In our previous example, we used the constant `true` as the condition for the sole star transition there was in the Moore machine. This worked, because there was no other outgoing transition on state 2. However, the order in which a Moore machine evaluates the guard on each of the outgoing transitions is non-deterministic. Setting `true` as the condition on the transition from state 0 to state 1 could lead to strange results: the FSM could move from 0 to 1 even if the condition on the other transition is true, just because it is the first one to be evaluated. 

To alleviate this problem, we must use a different kind of transition, called <!--\index{TransitionOtherwise@\texttt{TransitionOtherwise}} \texttt{TransitionOtherwise}-->`TransitionOtherwise`<!--/i-->. This transition fires *if and only if* none of the other outgoing transitions from the same source state fires first. This is the object we use to define the transition from state 0 to state 1, and also the the self-loop on state 1:

{@snipm finitestatemachines/ExtendedMooreMachine.java}{@}

The single argument of `TransitionOtherwise`'s constructor is the destination state of the transition.

We are almost done. The remaining step is to associate output symbols to the each state of the machine. We shall illustrate another feature of BeepBeep's `MooreMachine` object: instead of giving fixed symbols to states, we make the machine output values of their local variables. This is possible since the method `addSymbol()` requires a `Function` object; our previous example was just the particular case where this function was a `Constant`. Here, we pass a `ContextVariable` fetching the value of *c* in the processor's context, and associate it to state 0:

{@snipm finitestatemachines/ExtendedMooreMachine.java}{\(}

Whenever it reaches state 0, our Moore machine will query the current value of its local variable *c* and send it as its output event. This machine can be illustrated graphically as in the following figure.

{@img doc-files/finitestatemachines/ExtendedMooreMachine.png}{The `MooreMachine` of our second example.}{.6}

We can now try this machine on a feed of events, by connecting it to a queue source as before. If the source is made of the following sequence of strings:

```
hasNext
next
hasNext
hasNext
next
next
hasNext
```

the machine should output:

```
1.0
0
1.0
2.0
0
```

Notice how the count increments, then resets to 0 upon receiving a `next` event. Moreover, upon receiving the last `next` event, the machine moves to state 1 and no longer outputs anything, as expected.

The purpose of this section is not to have an in-depth discussion on the theory of finite-state machines. The previous two examples have shown all the features of BeepBeep's `MooreMachine` processor, and highlighted its flexibility in defining guards, side effects, and associating symbols to states. In particular, our FSMs are not restricted to outputting Boolean values, and can also accept any kind of input event. A few use cases in the next chapter will further show how the `MooreMachine` can be used in various scenarios, and mixed with other BeepBeep processors.

## First-order logic and temporal logic

The `Booleans` utility class provides basic logical functions for combining Boolean values together; anybody who does a little bit of programming has already used operators such as "and", "or" and "not". However, there is more to logic than these simple connectives. BeepBeep provides two palettes, called FOL and LTL, that extend classical logic with new operators pertaining to *first-order logic* and *linear temporal logic*, respectively. Let us examine these operators and see what they can do.

### First-order logic

Often, we want to express the fact that a condition applies "for all objects" of some kind. For example, given a set of numbers, we could say that each of them is even; given a set of strings, we could say that each of them has at most five characters. Instead of repeating the same condition for each object, a cleaner approach consists of using what are called *quantifiers*.

The FOL palette contains two important functions called <!--\index{ForAllFunction@\texttt{ForAllFunction}} \texttt{ForAllFunction}-->`ForAllFunction`<!--/i--> and <!--\index{ExistsFunction@\texttt{ExistsFunction}} \texttt{ExistsFunction}-->`ExistsFunction`<!--/i-->.

### Linear temporal logic


## Plots

One interesting purpose of processing event streams is to produce visualizations of their content --that is, to derive <!--\index{plots} plots-->plots<!--/i--> from data extracted from events. BeepBeep's `plots` palette provides a few processors to easily generate dynamic plots.

Under the hood, the palette makes use of the [MTNP](https://github.com/liflab/mtnp) library (MTNP stands for "Manipulate Tables N'Plots"), which itself relies on either [GnuPlot](https://gnuplot.info) or [GRAL](http://trac.erichseifert.de/gral/) to generate the <!--\index{MTNP} plots-->plots<!--/i-->. The technique can be summarized as follows:

1. Event streams are used to update the contents of a structure called a **table**
2. The contents of this table can be processed by applying a series of **transformations**
3. The resulting table is given as the source for a **plot** object
4. The plot is asked to produce a picture from the contents of the table

Let us start with the table. This data structure is represented by the <!--\index{Table@\texttt{Table}} \texttt{Table}-->`Table`<!--/i--> class of the MTNP library. A table is simply a collection of *entries*, with each entry containing a fixed number of key-value pairs. An entry therefore corresponds to a "line" of a table, and each key corresponds to one of its "columns". 

A table can be created from the contents of event streams with the use of BeepBeep's <!--\index{UpdateTable@\texttt{UpdateTable}} \texttt{UpdateTable}-->`UpdateTable`<!--/i--> processor. This processor exists in two flavors: <!--\index{UpdateTableStream@\texttt{UpdateTableStream}} \texttt{UpdateTableStream}-->`UpdateTableStream`<!--/i--> takes multiple input streams, one for the value of each column; <!--\index{UpdateTableArray@\texttt{UpdateTableArray}} \texttt{UpdateTableArray}-->`UpdateTableArray`<!--/i--> takes a single stream, which must be made of arrays of values or `TableEntry` objects. Both processors perform the same action: they update an underlying `Table` object, adding one new entry to the table for each event front they receive.

The following code sample illustrates the operation of `UpdateTableStream`:

{@snipm plots/UpdateTableStreamExample.java}{/}

Two sources of numbers are created and are piped into an `UpdateTableStream` processor. This processor is instantiated by giving two strings to its constructor. These strings correspond to the names of the columns in the table, and also determine the input arity of the processor. The first input pipe will receive values for column "x", while the second input pipe will receive values for column "y". A pump and a print processor are connected to the output of the table updater.

After a single activation of the pump, the program should print:

```
x,y
---
1,2
```

Values 1 and 2 have been extracted from `src1` and `src2`, respectively. From this event front, the `UpdateTableStream` processor creates one table entry with x=1 and y=2, adds it to its table and outputs the table. This is relayed to the `Print` processor which displays its content. The output of the program shows that upon each new event front, one new entry in the table is added; therefore, after activating the pump four times, the last output is:

```
x,y
---
1,2
2,3
3,5
4,7
```

The next part of the process is to draw plots from the content of a table. This is the job of the <!--\index{DrawPlot@\texttt{DrawPlot}} \texttt{DrawPlot}-->`DrawPlot`<!--/i--> processor. This processor is instantiated by being given an empty `Plot` object from the MTNP library. When it receives a `Table` from its input pipe, it passes it to the plot, and calls the plot's `render` method to create an image out of it. Therefore, the output events of `DrawPlot` are *pictures* --or more precisely, arrays of bytes corresponding to a bitmap in some image format (PNG by default).

As a more elaborate example, take a look at the following program.

{@snipm plots/CumulativeScatterplot.java}{/}

A stream of (x,y) pairs is first created, with x an incrementing integer, and y a randomly selected number. This is done through a special-purpose processor that is called `RandomTwoD`. It actually is a `GroupProcessor` that internally forks an input of stream of numbers. The first fork is left as is and becomes the first output stream. The second fork is sent through a `RandomMutator` (which converts any input into a random integer) and becomes the second output stream. The resulting x-stream and y-streams are then pushed into an `UpdateTableStream` processor. This creates a processor with two input streams, one for the "x" values, and the other for the "y" values. Each pair of values from the x and y streams is used to append a new line to the (initially empty) table. We connect the two outputs of the random processor to these two inputs. 

The next step is to create a plot out of the table's content. The `DrawPlot` processor receives a Table and passes it to a `Plot` object from the MTNP library. In our case, we want to create a <!--\index{plots!scatterplot@scatterplot} scatterplot-->scatterplot<!--/i--> from the table's contents; therefore, we pass an empty `Scatterplot` object. As we said, each event that comes out of the `DrawPlot` processor is an array of bytes corresponding to a bitmap image. To display that image, we use yet another special processor called <!--\index{BitmapJFrame@\texttt{BitmapJFrame}} \texttt{BitmapJFrame}-->`BitmapJFrame`<!--/i-->. This processor is a sink that manages a `JFrame` window; when it receives an input event (i.e. an array of bytes), it turns that array into an image and displays it inside the window.

Graphically, this chain of processors can be illustrated as follows:

{@img doc-files/plots/CumulativeScatterplot.png}{Producing a scatterplot from a source of random values.}{.6}

This drawing introduces a few new boxes. The one at the far right is the `BitmapJFrame`; its input pipe is colored in light green, which represents byte arrays. The box at its left is the `DrawPlot` processor. This processor is depicted with an icon indicating the type of plot that must be produced (here, a two-dimensional scatterplot). Yet to the left of this box is the `TableUpdateStream` processor. Next to each of its input pipes, a label indicates the name of the column that will be populated by values from that stream. The output pipe of this processor is colored in dark blue, which represents `Table` objects.

Running this program will pop a window containing a plot, whose contents are updated once every second (due to the action of an intermediate `Pump` object). The window should look like this one:

{@img doc-files/plots/window-plot.png}{The window produced by the `BitmapJFrame` processor.}{.6}

Each new table entry increments the value of *x* by one; the value of *y* is chosen at random. The end result is a dynamic plot created from event streams; the whole chain, from source to the actual bitmaps being displayed, amounts to only 12 lines of code. Obviously, sending the images into a bland JFrame is only done for the sake of giving an example. In a real-world situation, you will be more likely to divert the stream of byte arrays somewhere else, such as a file, or as a component of the user interface of some other software.

Besides scatterplots, any other plot type supported by the MTNP library can be passed to `DrawPlot`'s constructor. This includes histograms, pie charts, heatmaps, and so on. The only important point is that each plot type expects to receive tables structured in a particular way; for example, a heatmap requires a table with three columns, corresponding to the *x*-coordinate, *y*-coordinate, and "temperature" value, in this order. It is up to the upstream processor chain to produce a `Table` object with the appropriate structure.

Plots can also be customized by applying modifications to the `Plot` object passed to `DrawPlot`. For example, to set a custom title, one simply has to pass an instance of `Scatterplot` whose title has been changed using its `setTitle` method:

``` java
Scatterplot plot = new Scatterplot();
plot.setTitle("Some title");
DrawPlot dp = new DrawPlot(plot);
```

Since the `plots` palette is a simple wrapper around MTNP objects, the reader is referred to this library's online documentation for complete details about plots, tables, and table transformations.

## Networking

We have already seen in the previous chapter the `HttpGet` processor that allows you to fetch a character string remotely through an <!--\index{HTTP} HTTP-->HTTP<!--/i--> GET request. The `http` palette provides additional processors that make it possible to push and pull events across a network using HTTP. By splitting a processor chain on two machines and having both ends use HTTP to send and receive events, we are achieving what amounts to a rudimentary form of <!--\index{distributed computing} \textbf{distributed computing}-->**distributed computing**<!--/i-->.

In line with BeepBeep's general design principles, these functionalities are accessible through just a few lines of code. More precisely, send and receive operations are taken care of by two "gateway" processors, respectively called the `HttpUpstreamGateway` and the `HttpDownstreamGateway`.

The {@link jdc:ca.uqac.lif.cep.http.HttpUpstreamGateway HttpUpstreamGateway} is a sink processor that works in push mode only. It receives character strings, and is instructed to send the over the network through an HTTP request to a specific address. Thus, when instantiating the gateway, we must tell it the URL at which the request will be sent.

The {@link jdc:ca.uqac.lif.cep.http.HttpDownstreamGateway HttpDownstreamGateway} works in reverse. It continually listens for incoming HTTP requests on a specific TCP port; when a request matches the URL that was specified to its constructor, its contents are pushed to its output pipe in the form of a character string.

The following program shows a simple use of these two gateways.

{@snipm network/httppush/PushLocalSimple.java}{/}

We first create an upstream gateway, and tell it to send requests at a specific URL on our local machine (`localhost`) on TCP port 12144 (we chose this number arbitrarily; any unused port number would work). Additionally, we specify a "page" the gateway should push to; in this case, its name is `push`, but this could be any character string. We do the same thing with a downstream gateway, which is instructed to listen to port 12144, watch for URLs with the string `/push` (this is the same page name we gave to the upstream gateway), and to answer only to HTTP requests that use method POST. This gateway is connected to a `Print` processor to show what it receives on the console.

Both upstream and downstream gateways must be started in order to work; method `start` takes care of initializing the objects required for the network connection. Ideally, the gateways should also be stopped at the end of the program. Other than that, they work like any normal source and sink. You can see that we push strings to `up_gateway`; after the call to push, the standard output should display the contents of that string.

So far, this looks like we have merely pushed events and printed them at the console. What happened is actually a bit more complex than this: note how the upstream and the downstream gateways have never been linked using a call to `connect`. Rather, they used an HTTP request to pass the strings around. Therefore, this program is structured as if there were two "machines" running in parallel; Machine A pushes strings through HTTP requests, and Machine B receives and prints them. This could be illustrated as follows:

{@img doc-files/network/httppush/PushLocalSimple.png}{Using gateways to send events through HTTP.}{.6}

It just happens that in this simple program, the HTTP requests are sent to `localhost`; therefore, they never leave your computer. However, the whole process would be identical if the character strings were sent over an actual network: we would simply replace `localhost` by the IP address of some other computer.

### Serialization

Sending character strings over a network is an arguably simple task. Very often, the events that are exchanged between processors are more complex: what if we want to transmit a set, a list, or some other complex object that has member fields and all? The HTTP gateways always expect character strings, both for sending and for receiving.

A first solution would be to create a custom `Function` object that takes care of converting the object we want to send into a character string, and another one to do the process in reverse, and transform a string back into an object with identical contents. This process is called <!--\index{serialization} \textbf{serialization}-->**serialization**<!--/i-->. However, doing so manually means that for every different type of object, we need to create a different pair of functions to convert them to and from strings. Moreover, this process can soon become complicated. Take the following class:

``` java
public class CompoundObject
{
	int a;
	String b;
	CompoundObject c;
}
```

This class has for member fields an integer, a string and yet another instance of `CompoundObject`. Converting such an object into a character string requires adding delimiters to separate the int and String fields, and yet more delimiters to represent the contents of the inner `CompoundObject` --and so on recursively.

Luckily, there exist what are called *serialization libraries* that can automate part of the serialization process. BeepBeep has a palette called `serialization` whose purpose is to provide a few functions to serialize generic objects; under the hood, it uses the <!--\index{Azrael (library)} Azrael-->Azrael<!--/i--> serialization library. The palette defines two main `Function` objects:

- The <!--\index{JsonSerializeString@\texttt{JsonSerializeString}} \texttt{JsonSerializeString}-->`JsonSerializeString`<!--/i--> function converts an object into a character string in the <!--\index{JSON} \textbf{JSON}-->**JSON**<!--/i--> format.
- The <!--\index{JsonDeserializeString@\texttt{JsonDeserializeString}} \texttt{JsonDeserializeString}-->`JsonDeserializeString`<!--/i--> function works in reverse: it takes a JSON string and recreates an object from its contents.

These two functions can be passed to an `ApplyFunction` processor, and be used as a pre-processing step before and after passing strings to the HTTP gateways.

Let us add a constructor and a `toString` method to our `CompoundObject` class:

{@snipm network/CompoundObject.java}{/}

Consider now the following code example, which is a slightly modified version of our first program:

{@snipm network/httppush/PushLocalSerialize.java}{/}

The main difference is that a processor applying `JsonSerializeString` has been inserted before the upstream gateway, and another processor applying `JsonDeserializeString` has been inserted after the downstream gateway; the rest is identical. The serialization/deserialization functions must be passed the class of the objects to be manipulated. Here, we decide to use instances of `CompoundObject`s, as defined earlier. Graphically, our processor chain becomes:

{@img doc-files/network/httppush/PushLocal.png}{Serializing objects before using HTTP gateways.}{.6}

Note the pictogram used to illustrate the serialization processor: the picture represents an event that is "packed" into a box with a barcode, representing its serialized form. The deserialization processor conversely represents an event that is "unpacked" from a box with a barcode. Although these processors are actually plain `ApplyFunction` processors, we represent them with these special pictograms to improve the legibility of the drawings.

We can now push `CompoundObject`s through the serializer, as is shown in the following instructions:

{@snipm network/httppush/PushLocalSerialize.java}{!}

The expected output of the program should be:

```
a=0, b=foo, c=(null)
a=0, b=foo, c=(a=6, b=z, c=(null))
```

Not very surprising, but think about all the magic that happened in the background:

- The object was converted into a JSON string.
- The string was sent over the network through an HTTP request...
- converted back into a `CompoundObject` identical to the original...
- and pushed downstream to be handled by the rest of the processors as usual.

All this process requires only about 10 lines of code.

### All together now: distributed twin primes

As we mentioned earlier, the use of HTTP gateways can make for a simple way to distribute computation over multiple computers. As a matter of fact, any chain of processors can be "cut" into parts, with the loose ends attached to upstream and downstream gateways.

As a slightly more involved example, let us compute <!--\index{twin primes} twin primes-->twin primes<!--/i--> by splitting the process across two machines over a network. Twin primes are pairs of numbers *p* and *p*+2 such that both are prime. For example, (3,5), (11,13) and (17,19) are three such pairs. The [twin prime conjecture](https://en.wikipedia.org/wiki/Twin_prime) asserts that there exists an infinity of such pairs.

Our setup will be composed of two machines, called A and B. Machine A will be programmed to check if each odd number 3, 5, 7, etc. is prime. If so, it will send the number *n* to Machine B, which will then check if *n*+2 is prime. If this is the case, Machine B will print to the console the values of *n* and *n*+2. The interest of this setup is that checking if a number is prime is an operation that becomes very long for large integers (especially with the algorithm we use here). By having the verification for *n* and *n*+2 on two separate machines, the whole processor chain can actually run two primality checks at the same time.

Since we want to make computations over very large numbers, we shall use Java's <!--\index{BigInteger@\texttt{BigInteger}} \texttt{BigInteger}-->`BigInteger`<!--/i--> class instead of the usual `int`s or `long`s. Furthermore, we assume the existence of a function object called `IsPrime`, whose purpose is to check whether a big integer is a prime number. (The code for `IsPrime` can be viewed in BeepBeep's code example repository.) Let us start with the program for Machine A.

{@snipm network/httppush/twinprimes/TwinPrimesA.java}{/}

We first specify the URL where prime numbers will be pushed downstream. The first processor is a source that will push the BigInteger "2" repeatedly.  The second processor is a simple counter. We feed it with the BigInteger "2" repeatedly, and it returns the cumulatve sum of those "2" as its output. Since the start value of BigIntegerAdd is one, the resulting sequence is made of all odd numbers. The events output from the counter are duplicated along two paths. Along the first path, the numbers are checked for primality. Along the second path, we feed a filter and use the primality verdict as the filtering condition. What comes out of the filter are only prime numbers. We then fork the output of the filter, just so that we can print what comes out of it. BigIntegers are converted to Strings, and pushed across the network to Machine B using the `HttpUpstreamGateway`.

Graphically, the chain of processors for Machine A can be represented as follows:

{@img doc-files/network/httppush/twinprimes/MachineA.png}{The chain of processors for Machine A.}{.6}

Let us now move to Machine B. We will not show the code, but only the processor chain:

{@img doc-files/network/httppush/twinprimes/MachineB.png}{The chain of processors for Machine B.}{.6}

First, we create an HttpDownstreamGateway to receive strings from Machine A. The next step is to convert the string received from the gateway back into a BigInteger. We then increment this number by 2 using the addition function for BigIntegers. The rest of the chain is similar to Machine A: we use a filter to only let prime numbers through, and print them to the console.

All in all, in this example we have written less than 50 lines of code. For that price we got a distributed, streaming algorithm for finding twin prime numbers. Note that this chain of processors is only meant to illustrate a possible use of the HTTP gateways. As such, it is not a very efficient way of finding twin primes: when *n* and *n*+2 are both prime, three primality checks will be done: Machine A will first discover that *n* is prime, which will trigger Machine B to check if *n*+2 also is. However, since Machine A checks all odd numbers, it will also check for *n*+2 in its next computation step. Could you think of a better way of using processors to make this more efficient?

A few things you might want to try:

- Machine B's program depends on the numbers sent by Machine A. Therefore, if you stop Machine A and restart it, you will see Machine B starting the the sequence of twin primes from the beginning.

## Exercises

1. Create a processor chain that takes as input a stream of numbers. Create a scatterplot that shows two lines:
  - A first line of (x,y) points where x is a counter that increments by 1 on each new point, and y is the value of the input stream at position x
  - A second line of (x,y) points which is the "smoothed" version of the original. You can achieve smoothing by taking the average of the values at position x-1, x and x+1.
As an extra, try to make your processor chain so that the amount of smoothing can be parameterized by a number *n*, indicating how many events behind and ahead of the current one are included in the average.

2. Modify the second Moore machine example so that the machine outputs the *cumulative* number of times `hasNext()` has been received when `next` is the current input event, and nothing the rest of the time.

2. Modify the first example in the *Networking* section, so that the upstream and downstream gateways are in two separate programs. Run the program of Machine A on a computer, and the program of Machine B on a different one. What do you need to change for the communication to succeed?

3. Modify the twin primes example: instead of Machine A pushing numbers to Machine B, make it so that Machine B pulls numbers from Machine A.

<!-- :wrap=soft: -->