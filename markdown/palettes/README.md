The Standard Palettes
=====================

A large part of BeepBeep's functionalities is dispersed across a number of *palettes*. These palettes are additional libraries (i.e. JAR files) that define new processors or functions to be used with BeepBeep's core elements. Each palette is optional, and has to be included in your project only if you need its contents.

This modular organization has three advantages. First, palettes are a flexible and generic way to extend the engine to various application domains, in ways unforeseen by its original designers. Second, they compose the engine's core (and each palette individually) relatively small and self-contained, easing the development and debugging process. Palettes have many purposes: reading special file types, producing plots, accessing a network, and so on. In the same way that the C programming language generally ships with a "standard" library, in this chapter, we explore a few "standard" palettes of BeepBeep that are more frequently used than others.

## Tuples

Input files are seldom made of a single value per line of text. A more frequent file format is called **comma-separated values** (<!--\index{CSV@CSV (file format)}CSV-->CSV<!--/i-->). In such a format, each line contains the value of multiple **attributes**, separated by a comma. The following is an example of such a file:

    # This is a simple file in CSV format
    
    A,B,C
    3,2,1
    1,7,1
    4,1,2
    1,8,3
    6,3,5

Blank lines and lines beginning with the hash symbol (`#`) are typically ignored (although the latter is not standard). The first non-ignored line in the file provides the *name* of each attribute. In the example above, the file defines three attributes named "A", "B" and "C". All the remaining lines of the file defines what are called <!--\index{tuple} \textbf{tuples}-->**tuples**<!--/i-->; a tuple is a data object that associates each attribute to a value. For example, the fourth line of the file defines a tuple that associates attribute A to value 3, attribute B to value 2, and attribute C to value 1. In other words, a CSV file is similar to a **table** in a relational database.

### Reading Tuples

The following program reads a CSV file called `file1.csv`, and extracts tuples from this file one by one:

``` java
InputStream is = CsvReaderExample.class.getResourceAsStream("file1.csv");
ReadLines reader = new ReadLines(is);
TupleFeeder tuples = new TupleFeeder();
Connector.connect(reader, tuples);
Pullable p = tuples.getPullableOutput();
Tuple tup = null;
while (p.hasNext())
{
    tup = (Tuple) p.next();
    System.out.println(tup);
}
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/tuples/CsvReaderExample.java#L50)


The first two lines are now familiar: they consist of opening an `InputStream` on a file, and passing this stream to a `ReadLines` processor to read it line by line. The next instruction creates a new processor called a <!--\index{TupleFeeder@\texttt{TupleFeeder}} \texttt{TupleFeeder}-->`TupleFeeder`<!--/i-->. This processor receives lines of text, and returns on its output pipe `Tuple` objects. The rest of the program simply pulls and prints these tuples. The output of this program is:

```
((A,3),(B,2),(C,1))
((A,1),(B,7),(C,1))
((A,4),(B,1),(C,2))
((A,1),(B,8),(C,3))
((A,6),(B,3),(C,5))
```

As you can see from the format of the output, a tuple can also be seen as a set of attribute-value pairs. `Tuple` objects implement Java's <!--\index{Map@\texttt{Map} (interface)} \texttt{Map}-->`Map`<!--/i--> interface; therefore, their contents can be queried just like any other associative map:

``` java
Object o = tup.get("A");
System.out.println(o + "," + o.getClass().getSimpleName());
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/tuples/CsvReaderExample.java#L63)


If `tup` refers to the last `Tuple` pulled from `tuples`, the previous lines of code will print:

```
6,String
```

Note that **the values in tuples produced by `TupleFeeder` are always strings**. The `TupleFeeder` does not attempt to cast a string into a number.

Graphically, this program can be represented as follows:

![Converting strings into tuples.](CsvReaderExample.png)

This diagram introduces the symbol for the `TupleFeeder`, a pictogram on the box representing a tuple. It also shows the colour used to represent tuple feeds (brown/orange).

### Querying Tuples

The previous example has shown us how to read tuples, but not how to manipulate them. The `tuples` palette defines a few handy `Function` objects allowing us, among other things, to fetch the value of an attribute and also to merge tuples. From the same input file as above, let us create an output stream made of the sum of attributes A and B in each line. The following piece of code performs exactly that:

``` java
InputStream is = SumAttributes.class.getResourceAsStream("file1.csv");
ReadLines reader = new ReadLines(is);
TupleFeeder tuples = new TupleFeeder();
Connector.connect(reader, tuples);
Fork fork = new Fork(2);
Connector.connect(tuples, fork);
ApplyFunction get_a = new ApplyFunction(new FetchAttribute("A"));
Connector.connect(fork, 0, get_a, 0);
ApplyFunction get_b = new ApplyFunction(new FetchAttribute("B"));
Connector.connect(fork, 1, get_b, 0);
ApplyFunction cast_a = new ApplyFunction(Numbers.numberCast);
Connector.connect(get_a, cast_a);
ApplyFunction cast_b = new ApplyFunction(Numbers.numberCast);
Connector.connect(get_b, cast_b);
ApplyFunction sum = new ApplyFunction(Numbers.addition);
Connector.connect(cast_a, 0, sum, 0);
Connector.connect(cast_b, 0, sum, 1);
Pullable p = sum.getPullableOutput();
while (p.hasNext())
{
    System.out.println(p.next());
}
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/tuples/SumAttributes.java#L57)


This program is probably better explained through its graphical representation, as the following:

![Adding two attributes in each tuple.](SumAttributes.png)

From a `ReadLines` processor, a `TupleFeeder` is instantiated. The stream of tuples is then forked along two branches. In the first branch, the value of attribute "A" for each tuple is extracted. This is done by using an `ApplyFunction` processor, and giving an instance of a new function called <!--\index{FetchAttribute@\texttt{FetchAttribute}} \texttt{FetchAttribute}-->`FetchAttribute`<!--/i--> to this processor. When instantiated, function `FetchAttribute` is given the name of the attribute to fetch in the tuple. This value (a String) is converted into a number and sent into an `ApplyFunction` processor that computes a sum. The same thing is done along the bottom branch for attribute "B". From the same input file as above, the output of this program is:

```
5.0
8.0
5.0
9.0
9.0
```

Indeed, it corresponds to the sum of A and B in each line. However, this processor chain is needlessly verbose. The successive application of all three functions can be collapsed into a single function tree, yielding this much simpler graph:

![Adding two attributes in each tuple (alternate version).](SumAttributesTree.png)

As an exercise, we leave to the reader the task of writing this chain of processors in code.

### Other Tuple Functions

The `tuples` palette provides a few other functions to manipulate tuples. Here, let us briefly describe a few of them:

- The function <!--\index{ScalarIntoTuple@\texttt{ScalarIntoTuple}} \texttt{ScalarIntoTuple}-->`ScalarIntoToTuple`<!--/i--> takes a scalar value *x* (for example, a number) and creates a tuple with a single attribute-value pair A=*x*. Here "A" is a name passed to the function when it is instantiated.

- The function <!--\index{MergeTuples@\texttt{MergeTuples}} \texttt{MergeTuples}-->`MergeTuples`<!--/i--> merges the key-value pairs of multiple tuples into a single tuple. If two tuples have the same key, the value in the resulting tuple is that of <em>one</em> of these tuples, selected arbitrarily. However, if the tuples have the same value for their common keys, the resulting tuple is equivalent to that of a relational JOIN operation.

- The function <!--\index{BlowTuples@\texttt{BlowTuples}} \texttt{BlowTuples}-->`BlowTuples`<!--/i--> breaks a single tuple into multiple tuples, one for each key-value pair of the original tuple. The output of this function is a *set* of tuples, and not a single one.

- The function <!--\index{ExpandAsColumns@\texttt{ExpandAsColumns}} \texttt{ExpandAsColumns}-->`ExpandAsColumns`<!--/i--> transforms a tuple by replacing two key-value pairs by a single new key-value pair. The new pair is created by taking the value of a column as the key, and the value of another column as the value. For example, with the tuple: {(foo,1), (bar,2), (baz,3)}, using "foo" as the key column and "baz" as the value column, the resulting tuple would be: {(1,3), (bar,2)}. The value of foo is the new key, and the value of baz is the new value. <!--If the value of the "key" pair is not a string, it is converted into a string by calling its `toString()` method (since the key of a tuple is always a string).--> The other key-value pairs are left unchanged.

### Relational Databases

We have already seen how a log of events stored in a file can be fed, line by line, to a BeepBeep processor chain and act as a pre-recorded event source. A BeepBeep palette allows users to do the same thing, this time using a relational database as the source of events. To this end, BeepBeep leverages Java's facilities for interacting with databases, regrouped under the name *Java Database Connectivity* (<!--\index{JDBC} JDBC-->JDBC<!--/i-->).

Suppose you have a local database server running on your machine. This server hosts a database called `mydb`, which itself contains a table called `mytable`. The contents of `mytable` are shown below:

+-----------------------+------------+
| **Name**              | **Salary** |
+=======================+============+
| Fred Flintstone       | 1000       |
+-----------------------+------------+
| Barney Rubble         | 1200       |
+-----------------------+------------+
| Wilma Filntstone      | 1300       |
+-----------------------+------------+
| George Jetson         | 1100       |
+-----------------------+------------+

It is possible to use the lines of this table as a source of events, each of which will consist of a `Tuple` object with the data of the corrresponding line. To this end, one uses a special BeepBeep processor called <!--\index{JdbcSource@\texttt{JdbcSource}} \texttt{JdbcSource}-->`JdbcSource`<!--/i-->, which converts an <!--\index{SQL} SQL-->SQL<!--/i--> query sent to a database server into a stream of tuples. Consider the following program:

``` java
Connection conn = DriverManager.getConnection(
    "jdbc:mysql//localhost/mydb", "betty", "foo");
String query = "SELECT * FROM mytable";
JdbcSource src = new JdbcSource(conn, query);
Pullable p = src.getPullableOutput();
while (p.hasNext())
{
  Tuple t = (Tuple) p.pull();
  System.out.println(t);
}
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/jdbc/SourceExample.java#L14)


The first line of this program is standard JDBC: it creates a `Connection` object to a database, based on a JDBC URL, a user name and a password. In the present case, the program connects to a server hosted on the local machine (`localhost`), using the MySQL driver, on a database called `mydb`, using "betty" and "foo" as the user/password credentials. The next line defines a query to be executed on this database; in this case, this amounts to a simple `SELECT` statement picking all the columns and all the lines from table `mytable`.

The next instruction creates a BeepBeep `JdbcSource` object; this objects acts as a gateway between JDBC objects and BeepBeep processors. This source is given the database `Connection` object and the query to execute. From then on, `src` can be used like any other BeepBeep `Source` object. The next line obtains a reference to `src`'s `Pullable`, and repeatedly pulls events from it. As one can see by looking at the console, each event is indeed a `Tuple` object corresponding to a line of the result:

```
{"Name": "Fred Flintstone", "Salary" : "1000"}
{"Name": "Barney Rubble", "Salary" : "1200"}
...
```

It is useful to know that, under the hood, the `JdbcSource` does not call the database multiple times. It does so a single time, upon the first call to `pull`; this triggers the evaluation of the SQL query and the retrieval of its result as a JDBC `ResultSet` object. Each subsequent call to `pull` simply amounts to pulling one new line from the result set, until all lines have been enumerated.

Obviously, the basic `SELECT` statement we used in this example can be replaced by a more complex expression. Moreover, since tables are, by definition, unordered collections of tuples, the ordering in which `src` enumerates the tuples may vary from one execution to the next, unless an `ORDER BY` clause is present in the statement.

## Finite-state Machines

Sometimes, a stream is made of events representing a sequence of "actions". It may be interesting to check whether these actions follow a predefined pattern, which stipulates in what order the actions in a stream can be observed to be considered valid. A convenient way of specifying these patterns is through a device called a <!--\index{finite-state machine} \emph{finite-state machine}-->*finite-state machine*<!--/i--> (FSM). BeepBeep's FSM palette allows users to create such machines.

### Defining a Moore Machine

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

The proper use of an iterator stipulates that one should never call method `next()` before first calling method `hasNext()`. The correct ordering of these calls can be expressed by a finite-state machine with three states, as in the following picture.

![A finite-state machine representing the constraint that `next()` cannot be called before calling `hasNext()` first.](HasNextFSM.png)

In this FSM, states are numbered 0, 1 and 2; transitions between states are labelled with the method name they represent; for example, when the machine is in State 1, receiving a `next` event will make it move to State 0. One of these states is called the *initial state*, and is identified by an arrow that is unattached to any source state. In the present case, the initial state is 0. The "star" label on State 2's arrow indicates that this transition matches any incoming event.

In BeepBeep's FSM palette, finite-state machines are materialized by an object called <!--\index{MooreMachine@\texttt{MooreMachine}} \texttt{MooreMachine}-->`MooreMachine`<!--/i-->; the origin of that name will be explained subsequently. The creation of the machine is made by the following code example:

``` java
MooreMachine machine = new MooreMachine(1, 1);
final int UNSAFE = 0, SAFE = 1, ERROR = 2;
machine.addTransition(UNSAFE, new FunctionTransition(
    new FunctionTree(Equals.instance,
        StreamVariable.X, new Constant("hasNext")), SAFE));
machine.addTransition(UNSAFE, new FunctionTransition(
    new FunctionTree(Equals.instance,
        StreamVariable.X, new Constant("next")), ERROR));
machine.addTransition(SAFE, new FunctionTransition(
    new FunctionTree(Equals.instance,
        StreamVariable.X, new Constant("next")), UNSAFE));
machine.addTransition(SAFE, new FunctionTransition(
    new FunctionTree(Equals.instance,
        StreamVariable.X, new Constant("hasNext")), SAFE));
machine.addTransition(ERROR, new FunctionTransition(
    new Constant(true), ERROR));
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/finitestatemachines/SimpleMooreMachine.java#L63)


The first step is to create an empty `MooreMachine`; this machine receives one stream of events as its input, and produces one stream of events as its output --hence the two `1` in the object's constructor. In a `MooreMachine`, each state must be given a unique numerical identifier. Rather than hard-coding these numbers, we adopt a cleaner approach, and define symbolic constants for the three states of the Moore machine. It is recommended that the actual numbers for each state form a contiguous interval of integers starting at 0. Here, we associate numbers 0, 1 and 2 to the constants `UNSAFE`, `SAFE` and `ERROR`, respectively.

We are now ready to define the transitions (i.e. the "arrows" between states) for this machine. This is just a tedious enumeration of all the arrows that are present in the graphical representation of the FSM. Adding a transition to the machine is done through a method called `addTransition()`. This method must provide the number of the "source" state *n*<sub>*s*</sub>, and a <!--\index{Transition@\texttt{Transition}} \texttt{Transition}-->`Transition`<!--/i--> object. There are multiple types of such objects, but a frequent subclass is the <!--\index{FunctionTransition@\texttt{FunctionTransition}} \texttt{FunctionTransition}-->`FunctionTransition`<!--/i-->. This object specifies:

- A `Function` *f* that determines when the transition should fire. This function must have the same input arity as the machine itself, and return a Boolean value.
- The number of the "destination" state *n*<sub>*d*</sub>.

Intuitively, a `FunctionTransition` transition stipulates that when the machine is currently in state *n*<sub>*s*</sub> and receives an event *e*, if *f*(*e*) returns `true`, the machine shall move to state *n*<sub>*d*</sub>. For example, the first line states that in State 0 (`UNSAFE`), if the incoming event is "hasNext", go to State 1 (`SAFE`). The condition itself is expressed by creating a `FunctionTree` that checks if the incoming event (which is put into the `StreamVariable`) is equal to the `Constant` "hasNext". By default, the first state number that is ever given to the `MooreMachine` object is taken as the initial state of that machine. So here, `UNSAFE` will be the initial state. In BeepBeep's implementation of FSMs, there can only be one initial state.

The remaining instructions simply add the other transitions to the machine. A special remark must be made about State 2, which is a *sink state*; in other words, once you reach this state, you remain there forever. These states are typically used to indicate that the system has entered into an irrecoverable error condition. A possible way to say so is to define the condition on its only transition as the `Constant` true; it will fire whatever the incoming event may be.

These seven lines of code completely define our FSM. However, as it is, the machine is not instructed to output any event at any time. We mentioned earlier that this FSM is of a particular kind, called a *Moore machine*. Such a machine outputs a symbol when jumping into a new state. This means that arbitrary events can be associated to each state of the machine. In the present case, let us simply associate the Boolean values `true` to states `UNSAFE` and `SAFE`, and the value `false` to state `ERROR`. This is done using a method called `addSymbol()`:

``` java
machine.addSymbol(UNSAFE, new Constant(true));
machine.addSymbol(SAFE, new Constant(true));
machine.addSymbol(ERROR, new Constant(false));
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/finitestatemachines/SimpleMooreMachine.java#L109)


The `addSymbol` method takes as arguments the number of a state, and a `Function` object that is expected to return the desired symbol. This function is expected to ignore its input arguments, and to have the same output arity as the Moore machine itself. In the present case, the function is a simple `Constant` that returns a `Boolean` object. We stress that the machine does not need to return a Boolean, and that any Java object can be associated to a state.

The Moore machine is now ready. It can be applied on a sequence of events, by connecting it upstream to a `QueueSource` as usual, and by pulling the events it produces.

``` java
QueueSource source = new QueueSource();
source.setEvents("hasNext", "next", "hasNext",
    "hasNext", "next", "next");
Connector.connect(source, machine);
Pullable p = machine.getPullableOutput();
for (int i = 0; i < 7; i++)
{
  Boolean b = (Boolean) p.pull();
  System.out.println(b);
}
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/finitestatemachines/SimpleMooreMachine.java#L116)


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

![A complete representation of the `MooreMachine` example.](SimpleMooreMachine.png)

### Using the Machine's Context

We have seen in the previous chapter how each `Processor` object carries an associative map called a <!--\index{processor!context} \texttt{Context}-->`Context`<!--/i-->. A `MooreMachine` is one example of a processor that can put this `Context` object to good use, by employing it as a storage location for local variables. These variables can be initialized by the `MooreMachine` when it is created, modified when a transition is taken, and their value can be used in the conditions that determine which transition should fire. In this respect, such variables work in a very similar way to the same kind of local variables one can find in UML state machines.

Let us modify the previous example to illustrate the use of variables. We shall tweak the state machine, and impose the (arguably bizarre) constraint that the number of calls to `hasNext()` between each call to `next()` should increase by 1 every time. Since this constraint involves counting, and we impose no upper bound on the count, it cannot be represented by a classical finite-state machine. However, this becomes possible using additional variables. The principle is to update two variables: *c* keeps the number of calls to `hasNext()` since the last call to `next()`, and *n* keeps the expected number of calls to `hasNext()` in the current "cycle". Every time `hasNext()` is called, *c* should be incremented. Every time `next` is called, *c* should be reset to zero and *n* should be incremented. An error occurs whenever `hasNext` is called and *c* is greater than *n*, or when `next` is called and *c* is not equal to *n*. This could be illustrated as follows:

![A finite-state machine representing the constraint of the second example.](HasNextFSMContext.png)

This FSM looks very different as the previous one. As you can see, transitions now have conditions attached to them: these conditions are called *guards*. For example, the loop transition on the left-hand side of State 0 can be fired only if the incoming event is `hasNext` *and* the current value of local variable *c* is less than the current value of local variable *n*. In addition, transitions now also have *side effects* --that is, actions that change the processor's internal configuration other than simply moving it from one state to another. These side effects, in the figure, are separated from the guard by a slash, and consist of assignments to the local variables. When a state has multiple outgoing transitions, the `*` is interpreted as the transition that fires when no other does.

Take two minutes to convince yourself that this "extended" Moore machine indeed corresponds to the constraint we want to enforce. Let us now attempt to create this machine in code using BeepBeep processors. The first step is to create an empty 1:1 `MooreMachine` object, and to set variables *c* and *n* to their initial values. This is done in the following code snippet. We use `Processor`'s method `setContext` to give values to two new keys, called `c` and `n`, which are added to `machine`'s `Context` object:

``` java
MooreMachine machine = new MooreMachine(1, 1);
machine.setContext("c", 0);
machine.setContext("n", 1);
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/finitestatemachines/ExtendedMooreMachine.java#L50)


The next step is to define transitions, as before. Let us first consider the case of the loop on State 0 located on the left-hand side of the figure. The guard on this transition should express the condition that:

- The current event is the string `hasNext` **and**,
- The current value of *c* in the processor's context is less than the current value of *n*.

Such a Boolean function can be created with the help of a `FunctionTree`, as is shown by the code below:

``` java
FunctionTree guard = new FunctionTree(And.instance,
new FunctionTree(Equals.instance,
    StreamVariable.X, new Constant("hasNext")),
new FunctionTree(Numbers.isLessThan,
    new ContextVariable("c"), new ContextVariable("n")));
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/finitestatemachines/ExtendedMooreMachine.java#L56)


The novelty of this line of code is the use of a new type of variable, called the <!--\index{ContextVariable@\texttt{ContextVariable}} \texttt{ContextVariable}-->`ContextVariable`<!--/i-->. When a `Function` object is evaluated inside a `Processor` (as will be the case here), a `ContextVariable` returns the value associated to the specified key in the processor's `Context` at the moment the function is evaluated. Therefore, in the present case, the function will compare the current value of *c* and *n*, every time the guard is evaluated by the Moore machine.

The transition has a guard, but also a side effect, which in this case is to increment the value of *c* by one. To indicate such a side effect, we need to use yet another new object, called <!--\index{ContextAssignment@\texttt{ContextAssignment}} \texttt{ContextAssignment}-->`ContextAssignment`<!--/i-->. The constructor of the `ContextAssignment` takes two arguments: a string that indicates the context key to modify, and a `Function` object whose return value determines the new value associated to this key. The code for creating this object looks like this:

``` java
ContextAssignment asg = new ContextAssignment("c",
    new FunctionTree(Numbers.addition,
        new ContextVariable("c"), new Constant(1))
    );
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/finitestatemachines/ExtendedMooreMachine.java#L63)


In the present case, the function passed is a `FunctionTree` adding the constant 1 to the current value of *c* in the processor's context. Indeed, this has the effect of incrementing the processor's variable *c* by one.

We are now ready to add the transition to the Moore machine. This is done, as before, by using the `addTransition` method:

``` java
machine.addTransition(0, new FunctionTransition(
  guard, 0, asg));
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/finitestatemachines/ExtendedMooreMachine.java#L69)


Note that, this time, the `addTransition` methods takes three arguments: the `Function` corresponding to the guard, the number of the destination state, and the `ContextAssignment` corresponding to the side effect to apply on that transition. As a matter of fact, `addTransition` accepts any number of `ContextAssignment`s after its first two arguments; this makes it possible to change the value of multiple context keys in the same transition.

Once we understand these concepts, defining the other self-loop on State 0 becomes straightforward. Instead of creating separate `guard` and `asg` objects, we put everything into the same method call:

``` java
machine.addTransition(0, new FunctionTransition(
    new FunctionTree(And.instance,
        new FunctionTree(Equals.instance,
            StreamVariable.X, new Constant("next")),
        new FunctionTree(Equals.instance,
            new ContextVariable("c"), new ContextVariable("n"))),
    0,
    new ContextAssignment("c", new Constant(0)),
    new ContextAssignment("n",
        new FunctionTree(Numbers.addition,
            new ContextVariable("n"), new Constant(1))
        )
    ));
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/finitestatemachines/ExtendedMooreMachine.java#L73)


Obviously, this notation tends to be a bit verbose, but in counterpart, it makes the definition of transitions and side effects very flexible.

One last comment must be made about the definition of the "star" transitions. In the previous example, we used the constant `true` as the condition for the sole star transition there was in the Moore machine. This worked, since there was no other outgoing transition on State 2. However, the order in which a Moore machine evaluates the guard on each of the outgoing transitions is non-deterministic. Setting `true` as the condition on the transition from State 0 to State 1 could lead to strange results: the FSM could move from 0 to 1 even if the condition on the other transition is true, just because it is the first one to be evaluated. 

To alleviate this problem, we must use a different kind of transition, called <!--\index{TransitionOtherwise@\texttt{TransitionOtherwise}} \texttt{TransitionOtherwise}-->`TransitionOtherwise`<!--/i-->. This transition fires *if and only if* none of the other outgoing transitions from the same source state can fire. This is the object used to define the transition from State 0 to State 1, and also the self-loop on State 1:

``` java
machine.addTransition(0, new TransitionOtherwise(1));
machine.addTransition(1, new TransitionOtherwise(1));
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/finitestatemachines/ExtendedMooreMachine.java#L88)


The single argument of `TransitionOtherwise`'s constructor is the destination state of the transition.

The remaining step is to associate output symbols to each state of the machine. We shall illustrate another feature of BeepBeep's `MooreMachine` object: instead of giving fixed symbols to states, we make the machine output values of their local variables. This is possible since the `addSymbol()` method requires a `Function` object; in the previous example, this function was a `Constant`. Here, a `ContextVariable` is passed, fetching the value of *c* in the processor's context, and associating it to State 0:

``` java
machine.addSymbol(0, new ContextVariable("c"));
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/finitestatemachines/ExtendedMooreMachine.java#L92)


Whenever it reaches State 0, the Moore machine will query the current value of its local variable *c* and send it as its output event. This machine can be illustrated graphically as in the following figure.

![The `MooreMachine` for the second example.](ExtendedMooreMachine.png)

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

Notice how the count increments, then resets to 0 upon receiving a `next` event. Moreover, upon receiving the last `next` event, the machine moves to State 1 and no longer outputs anything, as expected.

The purpose of this section is not to have an in-depth discussion on the theory of finite-state machines. The previous two examples have shown all the features of BeepBeep's `MooreMachine` processor, and highlighted its flexibility in defining guards, side effects, and associating symbols to states. In particular, our FSMs are not restricted to outputting Boolean values, and can also accept any kind of input event. A few use cases in the next chapter will further show how the `MooreMachine` can be used in various scenarios, and mixed with other BeepBeep processors.

## First-order Logic and Temporal Logic

The `Booleans` utility class provides basic logical functions for combining Boolean values together; anybody who does a bit of programming has already used operators such as "and", "or" and "not". However, there is more to logic than these simple connectives. BeepBeep provides two palettes, called FOL and LTL, which extend classical logic with new operators pertaining to *first-order logic* and *linear temporal logic*, respectively. Let us examine these operators and see what they can do.

### First-order Logic

Often, <!--\index{first-order logic} we-->we<!--/i--> want to express the fact that a condition applies "for all objects" of some kind. For example, given a set of numbers, we could say that each of them is even; given a set of strings, we could say that each of them has at most five characters. Instead of repeating the same condition for each object, a cleaner approach consists of using what are called <!--\index{quantifier} \emph{quantifiers}-->*quantifiers*<!--/i-->.

In the BeepBeep world, a quantifier is a function *Q* that takes as parameter a String *x*, called the **quantification variable**, and another function *f*, which must have a Boolean output type. *Q* receives a Java `Collection` *C* as its input argument; for each element *e* in *C*, it evaluates *f* by passing it a `Context` object with the association *x*=*e*; it collects the Boolean value returned by each such call. The *universal* quantifier computes the conjunction (logical "and") of those values and returns it. In other words, a universal quantifier returns `true` if *f* returns `true` every time we assign to *x* an element in *C*. The *existential* quantifier rather computes the disjunction (logical "or") of those values; it returns `true` as soon as *f* returns `true` by replacing *x* by some element in *C*.

In BeepBeep's FOL palette, universal and existential quantifiers are implemented by two `Function` objects called <!--\index{ForAll@\texttt{ForAll}} \texttt{ForAll}-->`ForAll`<!--/i--> and <!--\index{Exists@\texttt{Exists}} \texttt{Exists}-->`Exists`<!--/i-->. Let us illustrate the use of such quantifiers on a simple example. Consider the following piece of code:

``` java
Function f = new FunctionTree(Numbers.isEven, new ContextVariable("x"));
ForAll fa = new ForAll("x", f);
List<Number> nums = new ArrayList<Number>();
nums.add(2);
nums.add(6);
Object[] outputs = new Object[1];
fa.evaluate(new Object[]{nums}, outputs);
System.out.println(outputs[0]);
nums.add(3);
fa.evaluate(new Object[]{nums}, outputs);
System.out.println(outputs[0]);
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/fol/ForAllFunctionSimple.java#L33)


The first line creates a new `FunctionTree` `f` that simply checks if the current value of context variable *x* is an even number. The next line creates a `ForAll` called `fa`, with *x* as its quantification variable and *f* as its function. We then create a list containing two numbers. We proceed to evaluate `fa` on this list (you may want to go back to the beginning of chapter 3 to recall the syntax to evaluate `Function` objects). This has for effect of evaluating *f* twice: the first time by setting *x* to 2 in the context, and the second time by setting *x* to 6. Both calls return `true`; the conjunction of these values is also `true`, which is the value returned by `fa` and printed at the console. This corresponds to the intuition that `fa` verifies that "all the numbers in its input set are even".

We then modify the list `nums` by appending number 7 at its tail. Re-evaluating `fa` on this list this time yields the value `false`. Three calls to *f* occur in the background, and the last one (corresponding to the context where *x*=7) returns `false`. This indeed matches the fact that not all numbers in the input set are even.

Graphically, `fa` can be represented as in the following picture:

![A graphical representation of the `ForAll` processor.](ForAllFunctionSimple.png)

In this diagram, the quantified variable (in the grey box), as well as the `FunctionTree` that is used as the quantifier's function (made of the application of function `IsEven` on context variable *x*) can be identified. In addition, note the consistency of the colour coding:

- The quantifier accepts a collection (pink) of numbers (teal), represented by the polka dot pattern; it also returns a Boolean value (grey-blue).
- Function `IsEven` accepts a number (teal) and returns a Boolean value (grey-blue).

Quantifier `Exists` performs what is called the *dual* of the universal quantifier. It returns `true` when at least one call to the underlying function *f* returns `true`. In our example, replacing `ForAll` with `Exists` would check that at least one number in the input list is even.

Quantifiers can also be *nested*. That is, the underlying function given to a quantifier can itself be another quantifier. Consider a condition such as this: "all strings in a collection have the same length". It can be represented graphically as follows:

![Nesting two quantifiers.](NestedQuantifiers.png)

In this case, a first quantifier `fa1` creates a context object by setting the quantification variable *x* successively to each of the strings in the input collection. It then evaluates its underlying function using each context. This function turns out to be another quantifier, which is given the same input collection. Given a context and an input collection, this second quantifier (`fa2`) creates yet more context objects by taking the incoming context, and setting the quantification variable *y* successively to each of the strings in the input collection. This quantifier also evaluates an underlying function `f`, which checks the equality between the length of the string associated to context variable *x* and the length of the string associated to context variable *y*.

Programmatically, the previous figure is represented by the following program; note how `fa2` is given as the `Function` argument to the constructor of `fa1`:

``` java
Function f = new FunctionTree(Equals.instance,
    new FunctionTree(Size.instance, new ContextVariable("x")),
    new FunctionTree(Size.instance, new ContextVariable("y")));
ForAll fa2 = new ForAll("y", f);
ForAll fa1 = new ForAll("x", fa2);
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/fol/NestedQuantifiers.java#L33)


We can then evaluate `fa1` as in the previous example, but this time on collections of strings:

``` java
Set<String> strings = new HashSet<String>();
strings.add("foo");
strings.add("bar");
Object[] outputs = new Object[1];
fa1.evaluate(new Object[]{strings}, outputs);
System.out.println(outputs[0]);
strings.add("bazz");
fa1.evaluate(new Object[]{strings}, outputs);
System.out.println(outputs[0]);
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/fol/NestedQuantifiers.java#L40)


As expected, the output of the program is:

```
true
false
```

Since quantifiers are `Function` objects like any other, there is no constraint on how quantifiers can be mixed with other `Function` objects --provided that the input and output types match, obviously. For those who know what *prenex form* is, BeepBeep functions using quantifiers do not have to be put into prenex form to be evaluated.

Each quantifier also exists in a variant which, instead of taking a set as its input, accepts an arbitrary object. When instantiated, this variant requires an extra `Function`, called the *domain function*, which is used to compute a set of elements from the input argument. 

### Linear Temporal Logic: Operator "G"

While first-order logic provides quantifiers allowing us to repeat a condition on each element of a collection, another branch of logic concentrates on ordering relationships between events in a sequence. This is called *temporal logic*, and we shall concentrate in this section on <!--\index{Linear Temporal Logic (LTL)} \emph{linear temporal logic}-->*linear temporal logic*<!--/i-->, also called LTL.

LTL adds four new *operators* that can be used in a logical expression; these are called **G**, **F**, **X** and **U**. An LTL expression is a mix of these four operators with the traditional Boolean connectives (negation, conjunction, disjunction, implication). Let us examine the meaning of each of these operators successively. There already exists ample documentation on LTL as a logical language. In this section, we take a slightly different approach, and describe each operator by viewing it as a `Processor` on Boolean streams.

Operator **G** means "globally"; this operator is represented by a processor called (unsurprisingly) <!--\index{Globally@\texttt{Globally}} \texttt{Globally}-->`Globally`<!--/i-->. Its purpose is to make sure that the input stream remains `true` indefinitely. 

![The intuitive meaning of the four LTL temporal operators.](LTLOperators.png)

The next figure illustrates this fact graphically. Its topmost section shows a timeline of events, represented by circles. Time flows from left to right, and the larger circle represents the current event. The colour of each circle indicates whether the input stream *p* is true (green) or false (red) in a particular event. As can be seen, for **G** *p* to return `true` on the current event, *p* itself must be true in the current event, but also in all subsequent events.

Consider the following code example, represented by the illustration below:

``` java
Globally g = new Globally();
Print print = new Print();
print.setPrefix("Output: ").setSeparator("\n");
Connector.connect(g, print);
Pushable p = g.getPushableInput();
System.out.println("Pushing true");
p.push(true);
System.out.println("Pushing true");
p.push(true);
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/ltl/GloballySimple.java#L37)


![Pushing Boolean events to `Globally`.](GloballySimple.png)

We create a new instance of `Globally`, to which we push Boolean events --these correspond to the values of *p*. Before each call to `push`, we print a line at the console. However, the first lines of output of the program may look surprising:

```
Pushing true
Pushing true
```

We have pushed two events into `g`, but `g` in turn did not output anything. To understand why, we must go back to the definition we gave of operator **G**: it returns `true` on the current input event, if and only if *p* is true for the current event *and* all subsequent events. But how can `g` know about future events? Therefore, after receiving the first event (`true`), no definite output value for this event can be determined yet. The same reasoning applies for the second event that is pushed to `g`, which again produces no output.

Let us see what happens when we push some more events to `g`:

``` java
Globally g = new Globally();
Print print = new Print();
print.setPrefix("Output: ").setSeparator("\n");
Connector.connect(g, print);
Pushable p = g.getPushableInput();
System.out.println("Pushing true");
p.push(true);
System.out.println("Pushing true");
p.push(true);
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/ltl/GloballySimple.java#L37)


These additional lines of code produce this output:

```
Pushing false
Output: false
Output: false
Output: false
Pushing true
```

We get another surprise: pushing event `false` makes `g` push *three* output events: the constant `false` three times --but this is explainable. Upon the third call to `push()`, the stream of events *e*<sub>1</sub>, *e*<sub>2</sub>, *e*<sub>3</sub> received so far is the sequence `true`, `true`, `false`. Now, `g` has enough information to determine what to output for *e*<sub>1</sub>: since the stream starting at this position is not made entirely of the value `true`, the corresponding output should be `false`, which explains the first output event.

However, `g` also has enough information to determine what to output for *e*<sub>2</sub> as well: for the same reason as above, the stream starting at this position is not made entirely of the value `true`; this is why `g` can afford to output a second `false` event. The third output event can also be explained: obviously, the stream starting at *e*<sub>3</sub> is not made entirely of the value `true` (as *e*<sub>3</sub> itself is false), and hence `g` can output `false` for *e*<sub>3</sub> right away.

It takes some time to get used to this principle. What must be remembered is that `Globally` delays its output for an input event until enough is known about the future to provide a definite value. As a matter of fact, `Globally` can never return `true` --how could one be sure in advance that all future events are going to be true? It can only return the value `false`, in bursts, when it receives a `false` event. As an exercise, try pushing more events to `g` in order to train your intuition.

### Other LTL Operators

Once you grasp the meaning of `Globally`, other operators are easier to understand. The LTL operator **F** is the dual of **G**, and means "eventually" (the "F" stands for "in the *future*"). If *e*<sub>1</sub>, *e*<sub>2</sub>, ... is a stream of Boolean events, and *p* is an arbitrary LTL expression, an expression of the form **F** *p* stipulates that *p* must be true at least once at some point in the future. This is illustrated in the second section of the previous figure. As you can see, for **F** *p* to return true in the current event, it suffices that *p* be true right now, or in some event in the future. This is illustrated in the following code example:

``` java
Eventually e = new Eventually();
Print print = new Print();
print.setPrefix("Output: ").setSeparator("\n");
Connector.connect(e, print);
Pushable p = e.getPushableInput();
System.out.println("Pushing false");
p.push(false);
System.out.println("Pushing false");
p.push(false);
System.out.println("Pushing true");
p.push(true);
System.out.println("Pushing false");
p.push(false);
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/ltl/EventuallySimple.java#L37)


We perform similar operations to what we did with `Globally` in the previous example. Note that the behaviour of `Eventually` can be explained in the same way, with values `true` and `false` swapped. That is, `e` outputs a burst of `true` events as soon as it receives a `true` event, and delays its output as long as it receives `false` events. Thus, the program above outputs the following lines:

```
Pushing false
Pushing false
Pushing true
Output: true
Output: true
Output: true
Pushing false
```

The third LTL operator is **X**, which means "next".  It simply checks that the next event in the stream is `true`. This is illustrated in the third section of the previous figure. In BeepBeep, operator **X** is implemented by processor <!--\index{Next@\texttt{Next}} \texttt{Next}-->`Next`<!--/i-->. Let us push events to this processor in this piece of code:

``` java
Next n = new Next();
Print print = new Print();
print.setPrefix("Output: ").setSeparator("\n");
Connector.connect(n, print);
Pushable p = n.getPushableInput();
System.out.println("Pushing true");
p.push(true);
System.out.println("Pushing true");
p.push(true);
System.out.println("Pushing false");
p.push(false);
System.out.println("Pushing true");
p.push(true);
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/ltl/NextSimple.java#L38)


As expected, the processor does not output any event on the first call to `push()`: this output should be `true`, if and only if the *next* event in the stream is true (something we don't know about yet). As a matter of fact, the *i*-th output event is simply that of the *i*+1-th input event. Therefore, the program outputs:

```
Pushing true
Pushing true
Output: true
Pushing false
Output: false
Pushing true
Output: true
```

The last temporal operator is **U**, which stands for "until". Contrary to the previous processors, the <!--\index{Until@\texttt{Until}} \texttt{Until}-->`Until`<!--/i--> processor takes as input two Boolean streams, which we shall call *p* and *q*. The processor checks that the event on stream *q* is `true` on some future input front, and that until then, the event on stream *p* is `true` on every input front. In other words, *p* must be true until *q* becomes true. This can be seen in the figure describing the LTL operators.

Let us interact with the `Until` processor, as in the following code snippet:

``` java
Until u = new Until();
Print print = new Print();
print.setPrefix("Output: ").setSeparator("\n");
Connector.connect(u, print);
Pushable p = u.getPushableInput(0);
Pushable q = u.getPushableInput(1);
System.out.println("Pushing p=true, q=false");
p.push(true); q.push(false);
System.out.println("Pushing p=true, q=false");
p.push(true); q.push(false);
System.out.println("Pushing p=true, q=true");
p.push(true); q.push(true);
System.out.println("Pushing p=false, q=false");
p.push(false); q.push(false);
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/ltl/UntilSimple.java#L37)



The program produces the following output:

```
Pushing p=true, q=false
Pushing p=true, q=false
Pushing p=true, q=true
Output: true
Output: true
Output: true
Pushing p=false, q=false
Output: false
```

At this point, we are more familiar with the behaviour of LTL processors. Note how `Until` delays its first output until it receives its third event front, at which point three definite output events can be produced. Indeed, starting at the first event front, we have that *p* has value `true` for all event fronts until *q* has value `true` in the third one. Hence, the first output event of the processor is `true`. The same reasoning applies when one starts at the second and third event front.

Note that `Until`, like any other synchronous processor with an arity greater than 1, waits until a complete event front is available before performing a processing step. That is, if we push events only on `p` or on `q`, processor `u` will not produce any output --but this time, this will be because it is waiting for events at matching positions in the other input stream.

### Nesting LTL Operators

Like quantifiers, temporal operators can be *nested*: the output of an LTL processor can be fed to the input of another one. Consider a stream of basic events called *a*, *b*, *c* and *d*, and the constraint: "between an *a* and a *d*, there cannot be a *b* immediately followed by a *c*". For example, the stream *baccbbd* satisfies this constraint, while *accbcbd* would not. In LTL parlance, this would correspond to the formula: *a* → (¬ (*b* ∧ **X** *c*) **U** *d*). A processor chain that checks this constraint is shown in the next figure ([⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/ltl/Nested.java)).

![A more complex example involving multiple "nested" temporal operators.](Nested.png)

Although this chain looks a little more complex than the previous examples, one can follow the construction of the LTL formula by reading the figure from right to left. The rightmost `ApplyFunction` implements the implication *a* → *P*, where *P* is a Boolean trace of events created upstream. *P* itself corresponds to the stream coming out of the `Until` processor, which implements the sub-expression *Q* **U** *d*. In turn, *Q* corresponds the output of the `ApplyFunction` processor that evaluates ¬ (*b* ∧ *R*), while *R* is the output of a processor evaluating **X** *c*. One can observe that, by replacing each sub-expression in succession, the resulting LTL formula we obtain is indeed *a* → (¬ (*b* ∧ **X** *c*) **U** *d*).

The chain has also been fitted with two `Print` processors, to print the events that are pushed on the left, and the events that come out on the right. Pushing some events yields an output like this:

```
Pushing: c
Output: true
Pushing: d
Output: true
Pushing: a
Pushing: c
Pushing: b
Pushing: d
Output: true
Output: true
Output: true
Output: true
Pushing: f
Output: true
```

Notice how the use of an <!--\index{ApplyFunctionPartial@\texttt{ApplyFunctionPartial}} \texttt{ApplyFunctionPartial}-->`ApplyFunctionPartial`<!--/i--> processor on the rightmost processor has for effect of yielding an immediate verdict in some cases. The top-level expression that is ultimately evaluated is of the form *a* → *P*; when the current input event is not an *a*, it is not necessary to wait for the truth value of *P* to output the value `true`. Only when the input event is an *a* must the implication "wait" before returning a value. The output of the `ApplyFunctionPartial` processor is delayed, until the processor chain taking care of the right-hand side of the implication outputs a value.

Intuitively, this processor chain can be seen as a "safeguard" mechanism. Suppose we want to prevent a program from producing a stream that violates the LTL constraint. Therefore, we would like to "monitor" an input stream, and only output its contents when we are certain that it respects the property. As long as the input stream contains events other than *a*, no constraint applies on future events. In other words, the input events, in this case, can be immediately output without fearing of violating the LTL formula.                                                                         

However, when the input event is an *a*, we must make sure that no *b* is immediately followed by a *c*, and moreover, that a *d* event eventually occurs. Since we do not know what future events may come, we must delay the output of event *a* until we are sure the constraint is respected --for example by putting it into a temporary buffer. When a *d* finally comes in, we can inspect the contents of the buffer, make sure that no *b* is followed by a *c*, and, if this is the case, output the whole contents of the buffer at once. In other words, our "monitor" would act as a gatekeeper, and let the input stream get through in chunks of events that are always guaranteed to comply with the constraints.

This process is a special case of what is called <!--\index{monitoring!enforcement} \emph{enforcement monitoring}-->*enforcement monitoring*<!--/i-->. It turns out that in BeepBeep, creating an enforcement monitor of this kind can be done easily, by using the Boolean output of our LTL processor as the control stream of a <!--\index{Filter@\texttt{Filter}} \texttt{Filter}-->`Filter`<!--/i-->. As a simple example, suppose we are monitoring a stream of operations made on a file, such as `read`, `open`, `close`, etc.). A possible constraint on this stream would be that an `open` operation must be followed later on by a `close`. In LTL, this would correspond to the expression *open* → **F** *close*. Consider the following processor chain ([⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/ltl/OpenClose.java)):

![Filtering events that follow a temporal property.](OpenClose.png)

The bottom part of the chain corresponds to the monitoring of the LTL formula. This output is then sent to the control pipe of a `Filter` processor, which receives on its data pipe a fork of the original stream. Pushing events on the fork produces an output like this:

```
Pushing nop
Output: nop
Pushing open
Pushing read
Pushing close
Output: open
Output: read
Output: close
```

Notice how, after pushing an `open` event, the output of the filter is buffered until a `close` is seen, after which all the buffered events are output.

There is much more to be said about monitoring in general, and LTL in particular. Although somewhat clumsy, the expression of LTL properties can be a powerful means of verifying complex ordering constraints on streams of events. The reader is referred to the appendix for more references on this topic.

## Java Widgets

Up until now, none of the examples we have shown involve interaction with a user. The sample programs get their data from a fixed source, such as a text file or a predefined `QueueSource`. In the same way, apart from the basic `Print` processor, there is little in the way of displaying information to the user. The *Widgets* palette fills some of these gaps, by allowing widgets of the Java <!--\index{Swing (library)} Swing-->Swing<!--/i--> graphical user interface (<!--\index{GUI} GUI-->GUI<!--/i-->) to be used as processors, and interact with other such objects in a chain.

In a nutshell, building a GUI in Java involves creating what are called *components*, such as windows (`JFrame`), buttons (`JButton`), sliders (`JSlider`), and defining the placement and properties of these various elements. Some of these components are sensitive to user input and other actions, and generate various kinds of objects called *events*: for example, pressing a button generates an instance of an <!--\index{ActionEvent@\texttt{ActionEvent}} \texttt{ActionEvent}-->`ActionEvent`<!--/i--> containing information about the click (the position of the mouse, a reference to the button that was clicked, etc.). Similarly, moving the cursor of a slider generates an instance of a `ChangeEvent`.

In order for a program to react to user input, one must *register* an object implementing the `EventListener` interface (or one of its descendents). Hence, to react to a click on some `JButton` instance `b`, one would call `b.addActionListener(a)`, where `a` is an arbitrary object that implements the `ActionListener` interface. Such an object must have a method called `actionPerformed`, which receives an `ActionEvent` as its argument. It is up to the code of this method to perform the actions required by the program for this specific button click.

You may notice that the terminology used by the Swing library is very close to some core BeepBeep concepts. GUI components generate *events* at various moments in the execution of a program, depending on the interaction with the user. It would be natural to see such components as `Source`s, and to try and connect them to other BeepBeep processors. This is precisely the purpose of the *Widgets* palette, which provides an object called <!--\index{ListenerSource@\texttt{ListenerSource}} \texttt{ListenerSource}-->`ListenerSource`<!--/i--> allowing the user to turn a Swing UI component into a BeepBeep event source. 

As an example, let us create a window containing a text label and a slider widget, using simple Swing objects:

``` java
JFrame frame = new JFrame("My Widget Frame");
frame.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
JPanel panel = new JPanel();
panel.setLayout(new BoxLayout(panel, BoxLayout.PAGE_AXIS));
JSlider slider = new JSlider(JSlider.HORIZONTAL, 0, 100, 30);
slider.setMajorTickSpacing(20);
slider.setPaintTicks(true);
slider.setPaintLabels(true);
JLabel slider_label = new JLabel("Value", JLabel.CENTER);
slider_label.setAlignmentX(Component.CENTER_ALIGNMENT);
panel.add(slider_label);
panel.add(slider);
panel.setBorder(BorderFactory.createEmptyBorder(10,10,10,10));
frame.add(panel);
frame.pack();
frame.setVisible(true);
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/widgets/ListenerSourceExample.java#L39)


The window should look like the following screenshot:

![A simple window with a text label and a slider widget.](SliderWindow-frame.png)

We would now like the slider to act as a BeepBeep `Source`, and send an event every time the slider's position is changed. To this end, we register a new `ListenerSource` object as a <!--\index{ChangeListener@\texttt{ChangeListener}} \texttt{ChangeListener}-->`ChangeListener`<!--/i--> on `slider`, as follows:

``` java
ListenerSource ls = new ListenerSource();
slider.addChangeListener(ls);
Print print = new Print();
Connector.connect(ls, print);
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/widgets/ListenerSourceExample.java#L58)


Once the `ListenerSource` is created and associated with a Swing component, it can be piped to other BeepBeep processors just like any other source. In this example, the source is simply connected to a `Print` processor, so that the events produced by the slider can be seen at the console. Once this program is started, a text line like the following should be printed at the console every time the slider is moved:

```
javax.swing.event.ChangeEvent[source=javax.swing.JSlider[,10,25,276x42,...
```

As one can see, the events generated by the slider are instances of Swing's <!--\index{ChangeEvent@\texttt{ChangeEvent}} \texttt{ChangeEvent}-->`ChangeEvent`<!--/i--> class. Each event contains lots of information, which could be queried using the event's accessor methods. However, most of the time, one is interested in the widget's *value*. The Widgets palette provides a BeepBeep `Function` object that extracts such value from a `ChangeEvent`. Therefore, the previous example could be modified as follows:

``` java
ListenerSource ls = new ListenerSource();
slider.addChangeListener(ls);
ApplyFunction gwv = new ApplyFunction(GetWidgetValue.instance);
Connector.connect(ls, gwv);
Print print = new Print();
Connector.connect(gwv, print);
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/widgets/GetValueSlider.java#L60)


A new function called `GetWidgetValue` has been inserted between `ls` and `print`. This time, moving the slider produces a stream of numbers that are printed at the console:

```
30,28,26,25,22,...
```

In the same way that widgets can be used as event sources, some of them can also be used as sinks. The <!--\index{WidgetSink@\texttt{WidgetSink}} \texttt{WidgetSink}-->`WidgetSink`<!--/i--> is a 0:1 processor that is instantiated by giving it a Swing widget. When it receives an event, it sets the widget's state according to the event's content. This may mean different things, according to the widget and the event's type. For example, if the widget is a text label (`JLabel`) and the input event is a number or a string, the `WidgetSink` will use the event to set the label's text. However, if the event is an instance of Swing's `ImageIcon`, the sink will use it to set the label's background.

In the case of a slider, the `WidgetSink` expects a numerical value, and uses it to change the slider's position. Using the same `JFrame` as the previous example, we can therefore write a piece of code like the following:

``` java
WidgetSink ws = new WidgetSink(slider);
Pushable p = ws.getPushableInput();
for (int i = 10; i <= 100; i+= 10)
{
  p.push(i);
  Thread.sleep(1000);
}
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/widgets/SetValueSlider.java#L54)


Notice how, this time, the program pushes events into the `WidgetSink` associated to the slider. By running this program, you should see the slider jumping from value 10 to 100, in increments of 10, every second.

The palette also includes another `Function` object, called <!--\index{ToImageIcon@\texttt{ToImageIcon}} \texttt{ToImageIcon}-->`ToImageIcon`<!--/i-->, which converts an array of bytes into a Swing `ImageIcon` object. It can be useful to take the output of a processor that produces an image (such as a PNG or JPEG bitmap), and to display it inside a Swing component. The next section will show an example that uses this function to display a plot and dynamically update it.

## Plots

One interesting purpose of processing event streams is to produce visualizations of their content --that is, to derive <!--\index{plots} plots-->plots<!--/i--> from data extracted from events. BeepBeep's `plots` palette provides a few processors to easily generate dynamic plots.

Internally, the palette makes use of the [MTNP](https://github.com/liflab/mtnp) library (MTNP stands for "Manipulate Tables N'Plots"), which itself relies on either [GnuPlot](https://gnuplot.info) or [GRAL](http://trac.erichseifert.de/gral/) to generate the <!--\index{MTNP} plots-->plots<!--/i-->. The technique can be summarized as follows:

1. Event streams are used to update the contents of a structure called a **table**.
2. The contents of this table can be processed by applying a series of **transformations**.
3. The resulting table is given as the source for a **plot** object.
4. The plot is asked to produce a picture from the contents of the table.

Let us start with the table. This data structure is represented by the <!--\index{Table@\texttt{Table}} \texttt{Table}-->`Table`<!--/i--> class of the MTNP library. A table is simply a collection of *entries*, with each entry containing a fixed number of key-value pairs. An entry therefore corresponds to a "line" of a table, and each key corresponds to one of its "columns". 

A table can be created from the contents of event streams with the use of BeepBeep's <!--\index{UpdateTable@\texttt{UpdateTable}} \texttt{UpdateTable}-->`UpdateTable`<!--/i--> processor. This processor exists in two flavours: <!--\index{UpdateTableStream@\texttt{UpdateTableStream}} \texttt{UpdateTableStream}-->`UpdateTableStream`<!--/i--> takes multiple input streams, one for the value of each column; <!--\index{UpdateTableArray@\texttt{UpdateTableArray}} \texttt{UpdateTableArray}-->`UpdateTableArray`<!--/i--> takes a single stream, which must be made of arrays of values or `TableEntry` objects. Both processors perform the same action: they update an underlying `Table` object, adding one new entry to the table for each event front they receive.

The following code sample illustrates the operation of `UpdateTableStream`:

``` java
QueueSource src1 = new QueueSource().setEvents(1, 2, 3, 4, 5);
QueueSource src2 = new QueueSource().setEvents(2, 3, 5, 7, 4);
UpdateTable table = new UpdateTableStream("x", "y");
Connector.connect(src1, OUTPUT, table, TOP);
Connector.connect(src2, OUTPUT, table, BOTTOM);
Pump pump = new Pump();
Print print = new Print().setSeparator("\n");
Connector.connect(table, pump, print);
pump.turn(4);
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/plots/UpdateTableStreamExample.java#L42)


Two sources of numbers are created and are piped into an `UpdateTableStream` processor. This processor is instantiated by giving two strings to its constructor. These strings correspond to the names of the columns in the table; the number of strings also determines the input arity of the processor. The first input pipe will receive values for column "x", while the second input pipe will receive values for column "y". A pump and a print processor are connected to the output of the table updater.

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

The next part of the process is to draw plots from the content of a table. This is the job of the <!--\index{DrawPlot@\texttt{DrawPlot}} \texttt{DrawPlot}-->`DrawPlot`<!--/i--> processor. This processor is instantiated by being given an empty `Plot` object from the MTNP library. When it receives a `Table` from its input pipe, it passes it to the plot, and calls the plot's `render` method to create an image from it. Therefore, the output events of `DrawPlot` are *pictures* --or more precisely, arrays of bytes corresponding to a bitmap in some image format (PNG by default).

As a more elaborate example, take a look at the following program.

``` java
QueueSource one = new QueueSource().setEvents(1);
Pump pump = new Pump(1000);
RandomTwoD random = new RandomTwoD();
Connector.connect(one, pump, random);
UpdateTable table = new UpdateTableStream("x", "y");
Connector.connect(random, TOP, table, TOP);
Connector.connect(random, BOTTOM, table, BOTTOM);
DrawPlot draw = new DrawPlot(new Scatterplot());
Connector.connect(table, draw);
BitmapJFrame window = new BitmapJFrame();
Connector.connect(draw, window);
window.start();
System.out.println("Displaying plot. Press Ctrl+C "
    + "or close the window to end.");
Thread th = new Thread(pump);
th.start();
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/plots/CumulativeScatterplot.java#L53)


A stream of (x,y) pairs is first created, with x an incrementing integer, and y a randomly selected number. This is done through a special-purpose processor that is called `RandomTwoD`. It actually is a `GroupProcessor` that internally forks an input of stream of numbers. The first fork is left as is and becomes the first output stream. The second fork is sent through a `RandomMutator` (which converts any input into a random integer) and becomes the second output stream. The resulting x-stream and y-stream are then pushed into an `UpdateTableStream` processor. This creates a processor with two input streams, one for the "x" values, and the other for the "y" values. Each pair of values from the x and y streams is used to append a new line to the (initially empty) table. The two outputs of the random processor are then connected to these two inputs.

The next step is to create a plot out of the table's content. The `DrawPlot` processor receives a Table and sends it to a `Plot` object from the MTNP library. In the current case, we want to create a <!--\index{plots!scatterplot@scatterplot} scatterplot-->scatterplot<!--/i--> from the table's contents; therefore, we pass an empty `Scatterplot` object. As previously mentioned, each event coming out of the `DrawPlot` processor is an array of bytes corresponding to a bitmap image. To display that image, we use yet another special processor called <!--\index{BitmapJFrame@\texttt{BitmapJFrame}} \texttt{BitmapJFrame}-->`BitmapJFrame`<!--/i-->. This processor is a sink that manages a `JFrame` window; when it receives an input event (i.e. an array of bytes), it turns that array into an image and displays it inside the window.

Graphically, this chain of processors can be illustrated as follows:

![Producing a scatterplot from a source of random values.](CumulativeScatterplot.png)

This drawing introduces a few new boxes. The one at the far right is the `BitmapJFrame`; its input pipe is coloured in light green, which represents byte arrays. The box at its left is the `DrawPlot` processor. This processor is depicted with an icon indicating the type of plot that must be produced (here, a two-dimensional scatterplot). Still more to the left is the `TableUpdateStream` processor. Next to each of its input pipes, a label indicates the name of the column that will be populated by values from that stream. The output pipe of this processor is coloured in dark blue, representing `Table` objects.

A window containing a plot, whose contents are updated once every second (due to the action of an intermediate `Pump` object) will appear by running this program.The window should look like this one:

![The window produced by the `BitmapJFrame` processor.](window-plot.png)
                                               
Each new table entry increments the value of *x* by one; the value of *y* is randomly chosen. The end result is a dynamic plot created from event streams; the whole chain, from source to the actual bitmaps being displayed, amounts to only 12 lines of code. Obviously, sending the images into a bland `JFrame` is only done for the sake of providing an example. In a real-world situation, one would be more likely to divert the stream of byte arrays somewhere else, such as a file, or as a component of the user interface of some other software.

Besides scatterplots, any other plot type supported by the MTNP library can be sent to `DrawPlot`'s constructor. It includes histograms, pie charts, heat maps, and so on. The only important point is that each plot is expected to receive tables structured in a particular way; for instance, a heat map requires a table with three columns, corresponding to the *x*-coordinate, *y*-coordinate, and "temperature" value, in this specific order. The upstream processor chain is responsible to produce a `Table` object with the appropriate structure.

Plots can also be customized by applying modifications to the `Plot` object sent to `DrawPlot`. For example, to set a custom title, one simply has to send an instance of `Scatterplot` whose title has been changed using its `setTitle` method:

``` java
Scatterplot plot = new Scatterplot();
plot.setTitle("Some title");
DrawPlot dp = new DrawPlot(plot);
```

Since the `plots` palette is a simple wrapper around MTNP objects, the reader is referred to this library's online documentation for complete details about plots, tables, and table transformations.

## Signal Processing

The input of a processor chain may be a stream of numerical values obtained from physical measurements, such as temperature or power sensors. In those cases, it may be desirable to transform this "raw" signal into a higher-level stream of values, on which some preliminary clean-up has been performed. The *Signal* palette provides processors suitable for some basic signal processing tasks, such as finding peaks, plateaus, etc.

To illustrate the operation of *Signal*'s various processors, we shall first generate a stream of values representing a "signal". To this end, we use the following processor chain ([⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/signal/FakeSignal.java)):

![Producing a numerical signal that varies with time.](FakeSignal.png)

This example is one of the firsts using the <!--\index{VariableStutter@\texttt{VariableStutter}} \texttt{VariableStutter}-->`VariableStutter`<!--/i--> processor. In the previous processor chain, it is represented by the box connected into the `Fork`. Its first input (top) is a stream of values, while its second input (bottom) indicates how many times each value should be repeated in the output. With the numbers contained in the two sources, the processor is expected to output the value 0 five times, followed by the value 10 five times, and so on.

This stream is then forked in two copies. The topmost path should be familiar to the reader, and creates a simple counter producing the output values 1, 2, 3, ...; these values will act as a clock tick "T". The bottom path cumulates the values of the forked input stream. This will produce an output signal "V" whose values move up and down upon each clock tick, from a relative amount defined by the number in the input stream.

This chain of seven processors gives us a crude way of producing a numerical signal whose behaviour is somewhat controlled by the contents of the two `QueueSource`s. In our example, in order to better see the end result, the pairs of values from "T" and "V" are sent into an <!--\index{UpdateTableStream@\texttt{UpdateTableStream}} \texttt{UpdateTableStream}-->`UpdateTableStream`<!--/i--> processor, transformed into a plot and displayed in a window. Running this program should show a plot like the following:

![Plotting the numerical signal produced by the previous chain of processors.](plot-envelope.png)

As an exercise, try changing the contents of the two sources to see the effect they have on the resulting plot.

We shall use this simple "signal generator" to illustrate the operation of various processors of the *Signal* palette. To simplify both the code and the diagrams, we shall put the previous processors into two <!--\index{GroupProcessor@\texttt{GroupProcessor}} \texttt{GroupProcessor}s-->`GroupProcessor`s<!--/i-->: the first half (up to the vertical dotted line) into a group called `GenerateSignal`, and the second into a group called `PlotSignal`. Using these two groups, the processor chain shown in the last diagram can be simplified into the following one:

![Producing a numerical signal that varies with time (grouped version).](FakeSignalGrouped.png)

As one can see, the source box is parameterized by the contents of the two input queues, while the sink box is parameterized by the number and the names of each stream of numbers it receives. We use the "delta" letter in the source, since the two input queues of this processor encode a discrete form of the first derivative of the input signal to generate. The reader is encouraged to have a look at the code of `GenerateSignal` ([⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/signal/GenerateSignal.java)) and `PlotSignal` ([⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/signal/PlotSignal.java)), in the examples repository, to better understand how these two groups are implemented.

A first useful processor of the *Signal* palette is used to find <!--\index{peak (signal)} \textbf{peaks}-->**peaks**<!--/i--> in an input stream. A peak is informally defined as an abrupt increase in the values of the signal over a short number of values (or *samples*). One possible way of looking for a peak is to use a sliding window of a few samples, and to identify local maxima in this window. The <!--\index{PeakFinderLocalMaximum@\texttt{PeakFinderLocalMaximum}} \texttt{PeakFinderLocalMaximum}-->`PeakFinderLocalMaximum`<!--/i--> processor does exactly that. Consider the following code snippet:

``` java
GenerateSignal gs = new GenerateSignal(
    new Object[] {0, 20, -10, 0, -7, 0},
    new Object[] {5, 5, 3, 5, 5, 5});
Fork fork = new Fork(2);
Connector.connect(gs, 1, fork, 0);
PeakFinderLocalMaximum peak = new PeakFinderLocalMaximum(5);
Connector.connect(fork, 1, peak, 0);
PlotSignal ps = new PlotSignal("T", "V", "P");
Connector.connect(gs, 0, ps, 0);
Connector.connect(fork, 0, ps, 1);
Connector.connect(peak, 0, ps, 2);
ps.start();
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/signal/PeakExample.java#L28)


Using the graphical conventions we just established, this chain of processors can be represented as in the following diagram:

![Finding peaks in a numerical signal.](PeakFinderExample.png)

In this program, the "signal" stream produced by the delta box is forked in two parts. One of them goes directly to the `PlotSignal` processor as before. The other is first passed through the `PeakFinderLocalMaximum` processor. This processor is parameterized by the length of the window, which, in this case, is of five events. This processed signal is also fed to the `PlotSignal` box, and given the name *P*. Therefore, the resulting plot will be made of *two* lines: one joining points from the pairs of numbers (*T*,*V*), and another joining points from the pairs (*T*,*P*). This makes it possible to visualize the effect of the `PeakFinderLocalMaximum` processor on the same plot as the original signal. The result should be a graph like the following:

![The original signal (V) and the detected local maxima (P).](peak-signal.png)

As one can see, the processor outputs the value 0 if the current input event is not considered as a peak; otherwise, it outputs the height of that peak. The definition of what constitutes a peak varies, depending on the underlying algorithm that is being used; in the present case, any local maximum that exits the sliding window is considered as a peak. The current version of the *Signal* palette also provides another processor, the <!--\index{PeakFinderTravelRise@\texttt{PeakFinderTravelRise}} \texttt{PeakFinderTravelRise}-->`PeakFinderTravelRise`<!--/i-->, which uses a different algorithm for detecting peaks.

The <!--\index{PlateauFinder@\texttt{PlateauFinder}} \texttt{PlateauFinder}-->`PlateauFinder`<!--/i--> processor identifies "plateaux" in an input signal; a *plateau* is a sequence of successive values that lie within the same (narrow) range. In the previous program, we can replace the peak processor with `PlateauFinder` and plot the results again ([⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/signal/PlateauExample.java)). This will produce the following plot:

![The original signal (V) and the detected plateaux (P).](plateau-signal.png)

We can observe that the processor outputs the value 0 when no plateau is detected; otherwise, it outputs the height of the plateau at the position of the event that corresponds to the start of a plateau. Obviously, for this processor to detect a plateau, a delay in the output is required: the start of a plateau can only be ascertained until a few events later, when enough values in the same interval have been observed. This interval is called the *window width*, and it can be configured by passing this width to the object's constructor.

Let us now change our input signal by changing its envelope and adding some random noise to its values. The processor chain to generate the signal is modified to look as follows:

![Generating a signal and adding some noise.](GenerateSignalNoise.png)

The main difference lies in the presence of a new fork on the bottom branch. The output signal from the `VariableStutter` processor is forked one more time; on the first path (top), the signal is sent into a processor called <!--\index{Randomize@\texttt{Randomize}} \texttt{Randomize}-->`Randomize`<!--/i-->; this processor turns any input event into a floating-point number, which is randomly selected from an predefined interval. In the current example, the interval is from -2 to 2, as indicated by the two numbers on the processor's box. This stream of random numbers is then added to the original signal (second path). This will result in a "jagged" output signal, with the amount of variation being parameterized by the interval set on `Randomize`. The code for this modified signal generator can be found in the example repository ([⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/signal/GenerateSignalNoise.java)).

Equipped with this new signal generator, we can illustrate a few more processors from the *Signal* palette. The first is called  <!--\index{Threshold@\texttt{Threshold}} \texttt{Threshold}-->`Threshold`<!--/i-->. Its task is to flatten to zero any input number whose absolute value lies below a predefined threshold, and to let the other numbers through. An example program showing the use of this processor produces the following plot ([⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/signal/ThresholdExample.java)):

![The original signal (V) and the signal after the application of the `Threshold` processor (P).](threshold-signal.png)

Here, the threshold has been set to 4, meaning that all values lying between -4 and 4 will be turned into 0 in the output signal. Notice how this has for effect of partly "de-noising" the input, by removing the small signal variations around the x-axis.

Like `PlateauFinder`, the <!--\index{Persist@\texttt{Persist}} \texttt{Persist}-->`Persist`<!--/i--> processor also operates on a window of width *k*; it returns the maximum value of the window. This has for effect of "persisting" high values in a signal for some time after they occur, in a way similar to some graphic equalizers used in music software. The examples repository contains a program that illustrates the use of `Persist`; it produces a plot like the following ([⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/signal/PersistExample.java)):

![The original signal (V) and the signal after the application of the `Persist` processor (P).](persist-signal.png)

As one can see, the high values in a window remain in the output for a number of events after they occur, when no higher value is observed in the sliding window.

The last processor contained in the *Signal* palette is called <!--\index{Limit@\texttt{Limit}} \texttt{Limit}-->`Limit`<!--/i-->. Instead of preserving high values, as is the case for `Persist`, this processor rather restricts the amount of non-zero events that can be output in a certain interval of time. The processor is instantitated with a window width *k*; when it receives a non-zero event, it outputs it, but will then turn into 0 the next *k*-1 events, regardless of whether they are zero or not. This is shown by the following plot, which applies the `Limit` processor to an input signal with a window width of 4 ([⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/signal/LimitExample.java)):

![The original signal (V) and the signal after the application of the `Limit` processor (P).](limit-signal.png)

The *Signal* palette is still under development; it currently only provides basic processors for manipulating raw streams of numerical values. In particular, all the processors contained in the palette operate on the *time* domain; the addition of processors working on the *frequency* domain (such as Fourier transforms) is planned in future development steps. Nevertheless, the next chapter will show an example of an actual use case that uses processors from the *Signal* palette in its current state.

## Networking

In the last chapter, we saw the `HttpGet` processor that fetches a character string remotely through an <!--\index{HTTP} HTTP-->HTTP<!--/i--> GET request. The `http` palette provides additional processors that making it possible to push and pull events across a network using HTTP. By splitting a processor chain on two machines and having both ends use HTTP to send and receive events, we are achieving what amounts to a rudimentary form of <!--\index{distributed computing} \textbf{distributed computing}-->**distributed computing**<!--/i-->.

In line with BeepBeep's general design principles, these functionalities are accessible through just a few lines of code. More precisely, send and receive operations are taken care of by two "gateway" processors, respectively called the <!--\index{HttpUpstreamGateway@\texttt{HttpUpstreamGateway}} \texttt{HttpUpstreamGateway}-->`HttpUpstreamGateway`<!--/i--> and the <!--\index{HttpDownstreamGateway@\texttt{HttpDownstreamGateway}} \texttt{HttpDownstreamGateway}-->`HttpDownstreamGateway`<!--/i-->.

The `HttpUpstreamGateway` is a sink processor that works in push mode only. It receives character strings, and is instructed to send them over the network as the payload of an HTTP request directed to a specific address. Thus, when instantiating the gateway, we must specify the URL where the request is expected to be sent.

The [`HttpDownstreamGateway`](http://liflab.github.io/beepbeep-3/javadoc/#) works in reverse. It continually listens for incoming HTTP requests on a specific TCP port; when a request matches the URL that was specified to its constructor, its contents are pushed to its output pipe in the form of a character string.

The following program shows a simple use of these two gateways.

``` java
HttpUpstreamGateway up_gateway =
    new HttpUpstreamGateway("http://localhost:12144/push");
HttpDownstreamGateway dn_gateway =
    new HttpDownstreamGateway(12144, "/push", Method.POST);
Print print = new Print();
Connector.connect(dn_gateway, print);
up_gateway.start();
dn_gateway.start();
Pushable p = up_gateway.getPushableInput();
p.push("foo");
Thread.sleep(1000);
p.push("bar");
up_gateway.stop();
dn_gateway.stop();
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/network/httppush/PushLocalSimple.java#L43)


First, an upstream gateway is created, and is asked to send requests at a specific URL on the local machine (`localhost`) on TCP port 12144 (this number is chosen arbitrarily; any unused port number would work). Additionally, a "page" is specified where the gateway should push to; in this case, it is named `push`, but this could be any character string. The same thing is done with a downstream gateway, which is instructed to listen to port 12144, watch for URLs with the string `/push` (this is the same page name that was given to the upstream gateway), and to answer only to HTTP requests that use method POST. This gateway is connected to a `Print` processor to show what it receives on the console.

Both upstream and downstream gateways must be started in order to work; method `start` takes care of initializing the objects required for the network connection. Ideally, the gateways should also be stopped at the end of the program. Other than that, they work like any normal source and sink. Strings are pushed to `up_gateway`; after the call to push, the standard output should display the contents of that string.

So far, it seems that events were merely pushed and printed at the console. What actually happened is a bit more complex: note how the upstream and the downstream gateways have never been linked using a call to `connect`. Rather, an HTTP request was used to pass the strings around. Therefore, this program is structured as if there were two "machines" running in parallel; Machine A pushes strings through HTTP requests, and Machine B receives and prints them. This could be illustrated as follows:

![Using gateways to send events through HTTP.](PushLocalSimple.png)

It just happens that in this simple program, the HTTP requests are sent to `localhost`; therefore, they never leave the computer. However, the whole process would be identical if the character strings were sent over an actual network: `localhost` would simply be replaced by the IP address of some other computer.

### Serialization

Sending character strings over a network is an arguably simple task. Very often, the events that are exchanged between processors are more complex: what if a set, a list, or some other complex object having member fields needs to be transmitted? The HTTP gateways always expect character strings, both for sending and for receiving.

A first solution would be to create a custom `Function` object that takes care of converting the object we want to send into a character string, and another one to do the process in reverse, and transform a string back into an object with identical contents. This process is called <!--\index{serialization} \textbf{serialization}-->**serialization**<!--/i-->. However, doing so manually suggests that for every different type of object, a different pair of functions must be created to convert them to and from strings. Moreover, this process can soon become complicated. Take the following class:

``` java
public class CompoundObject
{
	int a;
	String b;
	CompoundObject c;
}
```

This class has for member fields an integer, a string and yet another instance of `CompoundObject`. Converting such an object into a character string requires adding delimiters to separate the int and String fields, and yet more delimiters to represent the contents of the inner `CompoundObject` --and so on recursively.

Luckily, *serialization libraries* can automate part of the serialization process. BeepBeep has a palette called `serialization` whose purpose is to provide a few functions to serialize generic objects; under the hood, it uses the <!--\index{Azrael (library)} Azrael-->Azrael<!--/i--> serialization library. The palette defines two main `Function` objects:

- The <!--\index{JsonSerializeString@\texttt{JsonSerializeString}} \texttt{JsonSerializeString}-->`JsonSerializeString`<!--/i--> function converts an object into a character string in the <!--\index{JSON} \textbf{JSON}-->**JSON**<!--/i--> format.
- The <!--\index{JsonDeserializeString@\texttt{JsonDeserializeString}} \texttt{JsonDeserializeString}-->`JsonDeserializeString`<!--/i--> function works in reverse: it takes a JSON string and recreates an object from its contents.

These two functions can be sent to an `ApplyFunction` processor, and be used as a preprocessing step before and after passing strings to the HTTP gateways.

Let us add a constructor and a `toString` method to our `CompoundObject` class:

``` java
public CompoundObject(int a, String b, CompoundObject c)
{
    super();
    this.a = a;
    this.b = b;
    this.c = c;
}
@Override
public String toString()
{
    return "a = " + a + ", b = " + b + ", c = (" + c + ")";
}
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/network/CompoundObject.java#L36)


Now, consider the following code example, which is a slightly modified version of the first program:

``` java
ApplyFunction serialize = new ApplyFunction(new JsonSerializeString());
HttpUpstreamGateway up_gateway =
    new HttpUpstreamGateway("http://localhost:12144/push");
HttpDownstreamGateway dn_gateway =
    new HttpDownstreamGateway(12144, "/push", Method.POST);
ApplyFunction deserialize = new ApplyFunction(
        new JsonDeserializeString<CompoundObject>(
            CompoundObject.class));
Print print = new Print();
Connector.connect(serialize, up_gateway);
Connector.connect(dn_gateway, deserialize);
Connector.connect(deserialize, print);
up_gateway.start();
dn_gateway.start();
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/network/httppush/PushLocalSerialize.java#L47)


The main difference is that a processor applying `JsonSerializeString` has been inserted before the upstream gateway, and another processor applying `JsonDeserializeString` has been inserted after the downstream gateway; the rest is identical. The serialization/deserialization functions must be passed the class of the objects to be manipulated. Here, we decide to use instances of `CompoundObject`s, as defined earlier. Graphically, our processor chain becomes:

![Serializing objects before using HTTP gateways.](PushLocal.png)

Note the pictogram used to illustrate the serialization processor: the picture represents an event that is "packed" into a box with a bar code, representing its serialized form. The deserialization processor conversely represents an event that is "unpacked" from a box with a bar code. Although these processors are actually plain `ApplyFunction` processors, we represent them with these special pictograms to improve the legibility of the drawings.

We can now push `CompoundObject`s through the serializer, as is shown in the following instructions:

``` java
Pushable p = serialize.getPushableInput();
p.push(new CompoundObject(0, "foo", null));
Thread.sleep(1000);
p.push(new CompoundObject(0, "foo", new CompoundObject(6, "z", null)));
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/network/httppush/PushLocalSerialize.java#L121)


The expected output of the program should be:

```
a=0, b=foo, c=(null)
a=0, b=foo, c=(a=6, b=z, c=(null))
```

It is not very surprising, but one must remember all the tasks happening in the background:

- The object was converted into a JSON string.
- The string was sent over the network through an HTTP request...
- Converted back into a `CompoundObject` identical to the original...
- And pushed downstream to be handled by the rest of the processors as usual.

The entire process requires about 10 lines of code only.

### All Together Now: Distributed Twin Primes

As we mentioned earlier, the use of HTTP gateways provides a simple way to distribute computation over multiple computers. As a matter of fact, any chain of processors can be "split" into parts, with the loose ends attached to upstream and downstream gateways.

As a slightly more involved example, let us compute <!--\index{twin primes} twin primes-->twin primes<!--/i--> by splitting the process across two machines over a network. Twin primes are pairs of numbers *p* and *p*+2 such that both are prime. For instance, (3,5), (11,13) and (17,19) are three such pairs. The [twin prime conjecture](https://en.wikipedia.org/wiki/Twin_prime) asserts that there exists an infinity of such pairs.

The program will be composed of two machines, called A and B. Machine A will be programmed to check if each odd number 3, 5, 7, etc. is prime. If so, it will send the number *n* to Machine B, which will then check if *n*+2 is prime. If it is so, Machine B will print the values of *n* and *n*+2. Checking if a number is prime is an operation that becomes very long for large integers (especially with the algorithm we use here). By verifying *n* and *n*+2 on two separate machines, the whole processor chain can actually run two primality checks at the same time.

Since computations will be done over very large numbers, the program will use Java's <!--\index{BigInteger@\texttt{BigInteger}} \texttt{BigInteger}-->`BigInteger`<!--/i--> class instead of the usual `int`s or `long`s. Furthermore, it is assumed that there exists a function object called `IsPrime`, whose purpose is to check whether a big integer is a prime number. (The code for `IsPrime` can be found in BeepBeep's code example repository.) Let us start with the program for Machine A.

``` java
String push_url = "http://localhost:12312/bigprime";
QueueSource source = new QueueSource().addEvent(new BigInteger("2"));
Pump pump = new Pump(500);
Connector.connect(source, pump);
Cumulate counter = new Cumulate(
    new CumulativeFunction<BigInteger>(BigIntegerAdd.instance));
Connector.connect(pump, counter);
Fork fork1 = new Fork(2);
Connector.connect(counter, fork1);
ApplyFunction prime_check = new ApplyFunction(IsPrime.instance);
Connector.connect(fork1, LEFT, prime_check, INPUT);
Filter filter = new Filter();
Connector.connect(fork1, RIGHT, filter, LEFT);
Connector.connect(prime_check, OUTPUT, filter, RIGHT);
Fork fork2 = new Fork(2);
Connector.connect(filter, fork2);
Print print = new Print();
Connector.connect(fork2, LEFT, print, INPUT);
ApplyFunction int_to_string =
    new ApplyFunction(BigIntegerToString.instance);
HttpUpstreamGateway up_gateway = new HttpUpstreamGateway(push_url);
Connector.connect(fork2, RIGHT, int_to_string, INPUT);
Connector.connect(int_to_string, up_gateway);
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/network/httppush/twinprimes/TwinPrimesA.java#L50)


First, the URL where prime numbers will be pushed downstream is specified. The first processor is a source that will push the BigInteger "2" repeatedly.  The second processor is a simple counter. It is fed with the BigInteger "2" repeatedly, and it returns the cumulative sum of those "2" as its output. Since the start value of BigIntegerAdd is one, the resulting sequence is made of all odd numbers. The events output from the counter are duplicated along two paths. Along the first path, the numbers are checked for primality. Along the second path, a filter uses the primality verdict as the filtering condition. What results from the filter are only prime numbers. The output of the filter is then forked, so that what results from it can be printed. `BigInteger`s are converted to strings, and pushed across the network to Machine B using the `HttpUpstreamGateway`.

Graphically, the chain of processors for Machine A can be represented as follows:

![The chain of processors for Machine A.](MachineA.png)

Let us now move to Machine B. Only the processor chain is shown below:

![The chain of processors for Machine B.](MachineB.png)

An `HttpDownstreamGateway` is first created to receive strings from Machine A. The next step is to convert the string received from the gateway back into a `BigInteger`. This number is then incremented by 2 using the addition function for `BigInteger`s. The rest of the chain is similar to Machine A: a filter is used to only let prime numbers through, and these numbers are then printed at the console.

All in all, in this example less than 50 lines of code were written. This results in a distributed, streaming algorithm for finding twin prime numbers. Note that this chain of processors is only meant to illustrate a possible use of the HTTP gateways. As such, it is not a very efficient way to find twin primes: when *n* and *n*+2 are both prime, three primality checks will be conducted: Machine A will first discover that *n* is prime, which will trigger Machine B to check if *n*+2 also is. However, since Machine A checks all odd numbers, it will also check for *n*+2 in its next computation step.

As a side note, one can see that Machine B's program depends on the numbers sent by Machine A. Therefore, if Machine A is stopped and restarted, Machine B will restart the sequence of twin primes from the beginning.

## JSON and XML Parsing

We have already seen how BeepBeep can process input streams such as CSV text files, and break each line of these files into a structured object called a *tuple*. Other BeepBeep palettes can also process input data in a variety of other formats. In this section, we elaborate on two such formats, called JSON and XML.

### JSON Parsing

The serialization example in the previous section alluded to a particular way of formatting information using a notation called <!--\index{JSON} \textbf{JSON}-->**JSON**<!--/i-->. This acronym stands for *JavaScript Object Notation*, as it was first used in the JavaScript programming language to represent "semi-structured" data. A JSON object is a textual document such as this:

``` json
{
  "a" : 0,
  "b" : [1, 2, 3],
  "c" : {
    "d" : true,
    "e" : [
      {"f": "foo"},
      {"f": "bar"}
    ]
  }
}
```

The top-level object is delimited by the outermost pair of braces. It is an associative map between keys (the character strings "a", "b", ...) and values (on the right-hand side of the colon). A value can be:

- a primitive type such as a number (the value of "a"), a Boolean (the value of "d") or a character string (the value of "f");
- a list of primitive types (the value of "b") or of other JSON objects (the value of "e"); lists are denoted by square brackets;
- another JSON object (the value of "c").

As you can see, this notation allows an arbitrary nesting of objects within lists or other objects, which makes it both easy to read and quite versatile. An increasing number of applications uses this lightweight format to exchange, and sometimes even store data. We also learned in the previous section that JSON is one of the formats used by the Azrael library to serialize the contents of a Java object.

A complete tutorial on JSON is out of the scope of this section. However, it is interesting to know that a BeepBeep palette exists to parse and query JSON objects. The parsing is done with a function called <!--\index{ParseJson@\texttt{ParseJson}} \texttt{ParseJson}-->`ParseJson`<!--/i-->: it receives a character string as input, and produces an instance of an object called <!--\index{JsonElement@\texttt{JsonElement}} \texttt{JsonElement}-->`JsonElement`<!--/i--> as its output. It is invoked like any other BeepBeep `Function`, as in the following code example:

``` java
ParseJson parse = ParseJson.instance;
Object[] out = new Object[1];
parse.evaluate(new Object[]{
    "{\"a\" : 123, \"b\" : false, \"c\" : [4,5,6]}"}, out);
JsonElement j = (JsonElement) out[0];
System.out.println(j);
parse.evaluate(new Object[]{
"{\"a\" : "}, out);
System.out.println(out[0].getClass());
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/json/Parsing.java#L34)


We do not illustrate this program, but you can find the symbol used for this function in the glossary at the end of this book. The output of this program is:

```
{"a":123,"b":false,"c":[4,5,6]}
class ca.uqac.lif.json.JsonNull
```

If the parsing fails, such as when the input string is not properly formatted, the function outputs a special `JsonElement` called `JsonNull`, as can be observed in the second line of output.

`JsonElement` is actually an umbrella class to designate a generic JSON object. In reality, the object returned by the parsing function will belong to one of the descendents of this class, namely:

- `JsonMap` if the parsed string corresponds to an associative map.
- `JsonList` if the parsed string corresponds to a list.
- `JsonString`, `JsonNumber`, or `JsonBoolean` if the string parses to one of the primitive types.

The contents of these objects can also be queried. For example, the following code extracts elements from the object `j` obtained previously, which is actually an instance of `JsonMap`:

``` java
JsonMap map = (JsonMap) j;
JsonNumber n = (JsonNumber) map.get("a");
System.out.println(n.numberValue());
JsonList l = (JsonList) map.get("c");
System.out.println(l.get(1));
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/json/Parsing.java#L46)


The second line of code extracts the value corresponding to key "a" in the map; this value is a `JsonNumber` whose value is printed at the console (`123`). The fourth line of code extracts the value corresponding to key "c" in the map; this is a `JsonList`, in which we `get` the second element (i.e., the element at position 1) and print it at the console (`5`).

JSON objects can be easily queried using these methods. However, suppose we receive a stream of JSON objects, of which we want to extract the value corresponding to some key (say, "a") and perform further processing on it. This task should be done by an `ApplyFunction` processor --except that `get` is a *method* of a class, not an instance of a BeepBeep `Function`. Thankfully, the JSON palette also provides a second object, called <!--\index{JPathFunction@\texttt{JPathFunction}} \texttt{JPathFunction}-->`JPathFunction`<!--/i-->. An instance of this function is created by giving it the name of an element to fetch in a JSON object. When it is called on a `JsonElement`, it returns the value corresponding to the given key (or `JsonNull` if the key cannot be found). This function can be passed to an `ApplyFunction` processor, and hence JSON extraction can be applied to a stream of JSON elements. The following code example illustrates this:

``` java
Object[] out = new Object[1];
ParseJson.instance.evaluate(new Object[]{
    "{\"a\" : 123, \"b\" : false, \"c\" : [4,5,6]}"}, out);
JsonElement j = (JsonElement) out[0];
JPathFunction f1 = new JPathFunction("a");
f1.evaluate(new Object[]{j}, out);
System.out.println(out[0]);
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/json/JPathExample.java#L32)


A string is first parsed into a JSON element. A `JPathFunction` is then created, and instructed to fetch the value of a key named "a". When passed the element `j`, this function returns the `JsonNumber` 123, as expected.

If the field to fetch is nested within another `JsonElement`, it is not necessary to make calls to multiple `JPathFunction`s in succession. As its name implies, the function can accept a *path* expression instead of a single argument. This path is a string that represents a specific traversal inside a JSON element. For example, the expression `c` refers to the path that fetches the value corresponding to key "c". In the present case, this value is a list; therefore, the path `c[1]` refers to the path that fetches the second value in the list corresponding to the key "c". This is what is done in the following code example:

``` java
JPathFunction f2 = new JPathFunction("c[1]");
f2.evaluate(new Object[]{j}, out);
System.out.println(out[0]);
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/json/JPathExample.java#L42)


By convention, a period is used to designate a value inside a `JsonMap`, while brackets with a number designate a position inside a `JsonList`. Hence, the path `c.e[0].f` would lead to the value "bar" in the JSON document shown at the beginning of this chapter.

### XML Parsing

**XML** parsing and processing works in the same way. As you probably know, <!--\index{XML} XML-->XML<!--/i--> (the *eXtensible Markup Language*) is another popular notation for storing and exchanging data. An XML document is made of a set of nested "tags" and looks like this:

``` xml
<doc>
  <a>
    <b>1</b>
    <c>10</c>
  </a>
  <a>
    <b>2</b>
    <c>15</c>
  </a>
  <d>123</d>
  <e>
    <f>foo</f>
    <f>bar</f>
  </e>
</doc>
```

Each tag is enclosed between angle brackets; an *element* is the portion of a document delimited by an opening tag and its corresponding closing tag (these tags have a slash before their name). BeepBeep's XML palette provides a function called <!--\index{ParseXml@\texttt{ParseXml}} \texttt{ParseXml}-->`ParseXml`<!--/i--> that does the same thing for XML than `ParseJson` does for JSON: it converts a character string into an instance of an object, this time called <!--\index{XmlElement@\texttt{XmlElement}} \texttt{XmlElement}-->`XmlElement`<!--/i-->, as shown in the following code example:

``` java
ParseXml parse = ParseXml.instance;
Object[] out = new Object[1];
parse.evaluate(new Object[]{
  "<doc><a>123</a><b>foo</b></doc>"}, out);
XmlElement x = (XmlElement) out[0];
System.out.println(x);
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/xml/Parsing.java#L32)


The objects returned by the parsing function each have a name, some text (optionally), and a list of children tags (which may be empty). These various fields can be queried as follows:

``` java
List<XmlElement> ch = (List<XmlElement>) x.getChildren();
XmlElement e = ch.get(1);
System.out.println(e.getName() + ", " + e.getTextElement());
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/xml/Parsing.java#L44)


The last line of code produces the output `b, foo`.

Parts of an element can also be extracted using a `Function` object similar to `JPathFunction`. It is called `XPathFunction`, and it, too, performs a traversal in a document to retrieve some parts of it. However, since XML documents are structured differently from JSON, the syntax for writing paths and the actual output of the function are not identical. In its simplest form, a path is a list of tag names separated by slashes. In the XML document shown earlier, evaluating the path `doc/d` would return the `XmlElement` named `d`, which contains the number `1`.

However, there may be multiple elements of the same name; for example, the path `doc/a/e/f` corresponds to two elements: `<f>foo</f>` and `<f>bar</f>`. This is why the evaluation of an XPath expression always returns a *collection*, even when the path corresponds to only one element. The behaviour of the `XPathFunction` is illustrated in the following code example:

``` java
Object[] out = new Object[1];
ParseXml.instance.evaluate(new Object[]{
    "<doc>\n"
        + "<a><b>1</b><c>10</c></a>\n"
        + "<a><b>2</b><c>15</c></a>\n"
        + "<d>123</d>\n"
        + "</doc>"}, out);
XmlElement x = (XmlElement) out[0];
System.out.println(
    new XPathFunction("doc/d/text()").getValue(x));
System.out.println(
    new XPathFunction("doc/a/b").getValue(x));
System.out.println(
    new XPathFunction("doc/a[b=2]/c").getValue(x));
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/xml/XPathExample.java#L32)


In this excerpt, a few shortcuts are taken: since `XPathFunction` is a descendent of <!--\index{UnaryFunction@\texttt{UnaryFunction}} \texttt{UnaryFunction}-->`UnaryFunction`<!--/i-->, is has an additional method called <!--\index{UnaryFunction@\texttt{UnaryFunction}!getValue@\texttt{getValue}} \texttt{getValue}-->`getValue()`<!--/i-->`getValue()`<!--/i--> that does away with the usual input/output arrays, and makes for a shorter program. The output of the program is:

```
[123]
[<b>1</b>, <b>2</b>]
[<c>15</c>]
```

The result of the first path is straightforward; however, note the use of `text()` at the end of the path. This is an instruction that extracts the textual content inside the last element. Hence, instead of returning `<d>123</d>` the expression simply returns `123`. It is important to know that `123` is not a `String` object; since the result of an XPath expression is always a collection of `XmlElement`s, the value is encased in a special descendant of this class, called `TextElement`. The textual value that this element contains can be queried using method `toString()`.

The meaning of the second path expression (`doc/a/b`) should be interpreted as: "get all the elements named `<b>` that are inside an element named `<a>`, itself inside an element named `<doc>`". There are indeed two such elements in the input document, but note that the two `<b>`'s do not need to have the same parent `<a>`.

Finally, the third path expression introduces a special notation called a *predicate*, written inside brackets. A predicate is an additional condition on an element, which must be true for this element to be considered in the path. In this example, the condition is that element `a` must have a child called `b` whose textual contents is the value `2`. Therefore, the path expression can be interpreted as: "get all the elements named `<c>` that are inside an element named `<a>` which has a child `<b>` containing value 2, and which is inside an element named `<doc>`. There is indeed a single element satisfying this condition in the document, which is `<c>15</c>`.

Again, the purpose of this section is not to provide an in-depth reference on XML or XPath, which turns out to be a full-fledged query language for XML documents (BeepBeep's palette supports only the basic functionalities of XPath). A last remark must be made on the fact that predicates can contain references to values fetched from a <!--\index{processor!context} \texttt{Context}-->`Context`<!--/i--> object. The name of a context key is prefixed by a dollar sign. This is exemplified by the following code:

``` java
FunctionTree d = new FunctionTree(
    new Bags.ApplyToAll(Numbers.numberCast),
    new XPathFunction("doc/a/b/text()"));
FunctionTree f = new FunctionTree(Numbers.isLessThan,
    new ContextVariable("z"),
    new FunctionTree(Numbers.numberCast,
        new FunctionTree(Bags.anyElement,
            new XPathFunction("doc/a[b=$z]/c/text()"))));
ForAll fa = new ForAll("z", d, f);
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/xml/ContextExample.java#L37)


We first create a function *d* that extracts elements according to the XPath expression `doc/a/b/text()`; this produces a set of `TextElement`s. We then call the `Bags` function <!--\index{Bags@\texttt{Bags}!ApplyToAll@\texttt{ApplyToAll}} \texttt{ApplyToAll}-->`ApplyToAll`<!--/i-->, which is instructed to cast the elements of the set into `Number`s (by applying <!--\index{Numbers@\texttt{Numbers}!NumberCast@\texttt{NumberCast}} \texttt{NumberCast}-->`NumberCast`<!--/i--> on each of them). The end result is that *d* takes as input an XML document, and returns (as numbers) the set of all values found inside a `<b>` tag.

The second line of code creates another function *f*, which checks that the value of a context variable called *x* is less than another expression on the right-hand side. This expression evaluates the XPath expression `doc/a[b=$z]/c/text()`; note the presence of `$z` in the predicate, which is expected to be replaced by the value of *z* in the current context at the moment the function is evaluated. As before, this expression returns a set of `TextElement`s.

Let us assume that the input documents always have a single `<c>` element inside an `<a>`. Therefore, the result of the expression will always be a *singleton*: a set with exactly one element. We can take this element out of the set by applying the `Bags` function <!--\index{Bags@\texttt{Bags}!AnyElement@\texttt{AnyElement}} \texttt{AnyElement}-->`AnyElement`<!--/i-->, which picks an arbitrary element of a collection. The element is then cast into a number; this is the value that is compared to *x* in the topmost `IsLessThan` function.

Finally, we put functions *d* and *f* inside a <!--\index{ForAll@\texttt{ForAll}} \texttt{ForAll}-->`ForAll`<!--/i--> quantifier. Graphically, this can be represented in the following figure; the parts of the image that correspond to functions *d* and *f* have been identified.

![Using an XPath expression inside a quantifier.](ContextExample.png)

Given an XML document *x* as input, the quantifier:

- Evaluates function *d* on this document; in this case, it produces a set of numbers corresponding to all the values inside a `<b>` tag;
- for each number *n*, it creates a new copy of *f*, associates the value *n* to a context key called *z*, and evaluates *f*(*x*);
- it finally computes the logical conjunction of all the returned values.

Informally, object `fa` evaluates the condition: "inside a document, the value of every `<b>` tag is less than the value of the `<c>` tag that is located under the same `<a>` parent". We can try this function on a simple document:

``` java
FunctionTree d = new FunctionTree(
    new Bags.ApplyToAll(Numbers.numberCast),
    new XPathFunction("doc/a/b/text()"));
FunctionTree f = new FunctionTree(Numbers.isLessThan,
    new ContextVariable("z"),
    new FunctionTree(Numbers.numberCast,
        new FunctionTree(Bags.anyElement,
            new XPathFunction("doc/a[b=$z]/c/text()"))));
ForAll fa = new ForAll("z", d, f);
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/xml/ContextExample.java#L37)


The result produced by `fa` is `true`, as expected. As an exercise, try replacing `2` by `20` in the second `<b>` tag; you shall see that the quantifier returns the value `false`.

This last example was slightly more involved. However, it gives a foretaste of the wide range of capabilities that become available when one starts mixing objects from multiple palettes. The next chapter shall push the envelope even further on this respect.


## Exercises

1. Create a processor chain that takes as input a stream of numbers. Create a scatterplot that shows two lines:
- A first line of (*x*,*y*) points where *x* is a counter that increments by 1 on each new point, and *y* is the value of the input stream at position *x*.
- A second line of (*x*,*y*) points which is the "smoothed" version of the original. Smoothing can be performed by taking the average of the values at position *x*-1, *x* and *x*+1. As an extra, make it so that the amount of smoothing can be parameterized by a number *n*, indicating how many events behind and ahead of the current one are included in the average.

2. Modify the second Moore machine example so that the machine outputs the *cumulative* number of times `hasNext()` has been received when `next` is the current input event, and nothing the rest of the time.

3. Create a processor chain whose input events are sets of strings. The chain should return `true` if an event has at least one string of the same length as another one in the previous event, and `false` otherwise.

4. Modify the first example in the *Networking* section, so that the upstream and downstream gateways are in two separate programs. Run the programs of Machine A and Machine B on two different computers. What do you need to change for the communication to succeed?

5. Modify the twin primes example: instead of Machine A pushing numbers to Machine B, make it so that Machine B pulls numbers from Machine A.

<!-- :wrap=soft: -->