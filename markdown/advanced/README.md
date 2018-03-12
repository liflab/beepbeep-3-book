Advanced features
=================

The previous chapters have shown the fundamental concepts around BeepBeep and the basic processors that can be used in general use cases. In this chapter, we shall see a number of more special-purpose processors that come in BeepBeep's core that you are likely to use in one of your processor chains.

## Lists, sets and maps

Up to this point, all the examples we have seen use event streams that are one of Java's primitive types: numbers (`int`s or `float`s), `Strings` and `Booleans`. However, we have said in the very beginning that one of BeepBeep's design principles is that everything (that is, any Java object) can be used as an event. To this end, the `util` package provides functions and processors to manipulate a few common data structures, especially lists, sets and maps.

A few of these functions are grouped under the [`Bags`](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/util/Bags.html) utility class. It contains references to functions that can be used to query arbitrary <!--\index{Bags@\texttt{Bags}} collections-->collections<!--/i--> of objects.

[`Bags.getSize`](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/util/Bags/getSize.html) refers to a function <!--\index{Bags!GetSize} \texttt{GetSize}-->`GetSize`<!--/i--> that takes a Java `Collection` object for input, and returns the size of this collection. For example, if `list` is a `List` object with a few elements inside, one could use `GetSize` like any other function:

``` java
Object[] outs = new Object[1];
Bags.getSize.evaluate(new Object[]{list}, outs);
// outs[0] contains the size of list
```

[`Bags.contains`](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/util/Bags/contains.html) refers to a function <!--\index{Bags!Contains} \texttt{Contains}-->`Contains`<!--/i--> that takes as input a Java `Collection` and an object *o*, and returns a Boolean value indicating whether the collection contains *o*. Its usage can be illustrated in the following code example:

``` java
QueueSource src1 = new QueueSource();
src1.addEvent(UtilityMethods.createList(1f, 3f, 5f));
src1.addEvent(UtilityMethods.createList(4f, 2f));
src1.addEvent(UtilityMethods.createList(4f, 4f, 8f));
src1.addEvent(UtilityMethods.createList(6f, 4f));
QueueSource src2 = new QueueSource();
src2.setEvents(1);
ApplyFunction contains = new ApplyFunction(Bags.contains);
Connector.connect(src1, 0, contains, 0);
Cumulate counter = new Cumulate(new CumulativeFunction<Number>(Numbers.addition));
Connector.connect(src2, counter);
Connector.connect(counter, 0, contains, 1);
Pullable p = contains.getPullableOutput();
for (int i = 0; i < 4; i++)
{
    System.out.println(p.pull());
}
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/util/BagsContains.java#L42)


We first create a `QueueSource` as usual; note that this time, each event in the source is itself a *list* (method `createList` is a small utility method that creates a `List` object out of its arguments). We then pipe this source as the first argument of an `ApplyFunction` processor that evaluates `Bags.contains`; its second argument comes from a stream of numbers that increments by one. The end result is a stream where the *n*-th output event is the value `true` if and only if the *n*-th input list in `src1` contains the value *n*. This can be illustrated like this:

![A first event stream with a more complex data structure.](BagsContains.png)

This drawing introduces the "polka dot" pattern. The base color to represent collections (sets, lists or arrays) is pink; the dots on the pipes are used to indicate the type of the elements inside the collection (here, numbers). When the type of the elements inside the collection is not known or may vary, the pipes will be represented in flat pink without the dots. Note also the symbol used to depict the `Contains` function.

As expected, the output of the program is:

```
true
true
false
true
```

The `Bags` class also provides a function called [`ApplyToAll`](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/util/Bags/ApplyToAll.html). This function is intantiated by giving it a `Function` object *f*; given a set/list/array, <!--\index{Bags!ApplyToAll} \texttt{ApplyToAll}-->`ApplyToAll`<!--/i--> returns a *new* set/list/array whose content is the result of applying *f* to each element. This can be shown in the following example:

``` java
List<Object> list = UtilityMethods.createList(-3, 6, -1, -2);
Object[] out = new Object[1];
Function f = new Bags.ApplyToAll(Numbers.absoluteValue);
f.evaluate(new Object[]{list}, out);
System.out.println(out[0]);
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/util/BagsFunctions.java#L35)


The output of this code snippet is indeed a new list with the absolute value of the elements of the input list:

```
[3.0, 6.0, 1.0, 2.0]
```

The [`FilterElements`](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/util/Bags/FilterElements.html) function can be used to remove elements form a collection. Like `ApplyToAll`, <!--\index{Bags!FilterElements@\texttt{FilterElements}} \texttt{FilterElements}-->`FilterElements`<!--/i--> is instantiated by passing a `Function` object *f* to its constructor. This function must be 1:1 and return a Boolean value. Given a set/list/array, `FilterElements` will return a new set/list/array containing only elements for which *f* returns `true`. Using the same list as above, the following code:

``` java
Function filter = new Bags.FilterElements(Numbers.isEven);
filter.evaluate(new Object[]{list}, out);
System.out.println(out[0]);
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/util/BagsFunctions.java#L42)


will produce this output:

```
[6, -2]
```

It is also possible to take the input of multiple streams, and to create a collection out of each front of events. This can be done with the help of function <!--\index{Bags!ToList} \texttt{ToList}-->`ToList`<!--/i-->. Consider the following code example:

``` java
QueueSource src1 = new QueueSource().setEvents(3, 1, 4, 1, 6);
QueueSource src2 = new QueueSource().setEvents(2, 7, 1, 8);
QueueSource src3 = new QueueSource().setEvents(1, 1, 2, 3, 5);
ApplyFunction to_list = new ApplyFunction(
        new Bags.ToList(Number.class, Number.class, Number.class));
Connector.connect(src1, 0, to_list, 0);
Connector.connect(src2, 0, to_list, 1);
Connector.connect(src3, 0, to_list, 2);
Pullable p = to_list.getPullableOutput();
for (int i = 0; i < 4; i++)
{
    System.out.println(p.pull());
}
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/util/ToListExample.java#L47)


We first create three sources of numbers, and pipe them into an `ApplyFunction` processor that is given the `ToList` function. When instantiated, this function must be given the type (that is, the `Class` object) of each of its inputs. Here, the function is instructed to receive three arguments, and is told that all three are instances of `Number`.

Graphically, this can be illustrated as follows (note the symbol used to represent `ToList`):

![Creating lists from the input of multiple streams.](ToListExample.png)

When run, this program will take each front of events from the sources, and create a list object of size three with those three events. The output of this program is therefore:

```
[3, 2, 1]
[1, 7, 1]
[4, 1, 2]
[1, 8, 3]
```

The functions <!--\index{Bags!ToSet} \texttt{ToSet}-->`ToSet`<!--/i--> and <!--\index{Bags!ToArray} \texttt{ToArray}-->`ToArray`<!--/i--> operate in a similar way, but create respectively a `Set` object and an array instead of a list.

Finally, the `Bags` class also defines a `Processor` object called [`RunOn`](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/util/Bags/RunOn.html). When instantiated, <!--\index{Bags!RunOn@\texttt{RunOn}} \texttt{RunOn}-->`RunOn`<!--/i--> must be given a 1:1 processor P. When it receives a collection as its input, `RunOn` takes each element of the collection, pushes it into P, and collects its last output.

Consider the following code example:

``` java
QueueSource src1 = new QueueSource();
src1.addEvent(UtilityMethods.createList(1f, 3f, 5f));
src1.addEvent(UtilityMethods.createList(4f, 2f));
src1.addEvent(UtilityMethods.createList(4f, 4f, 8f));
src1.addEvent(UtilityMethods.createList(6f, 4f));
Bags.RunOn run = new Bags.RunOn(
        new Cumulate(new CumulativeFunction<Number>(Numbers.addition)));
Connector.connect(src1, run);
Pullable p = run.getPullableOutput();
for (int i = 0; i < 4; i++)
{
    System.out.println(p.pull());
}
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/util/RunOnExample.java#L49)


A `RunOn` processor is created, and is given a `Cumulate` processor that is instructed to compute the cumulative sum of a stream of events. When receiving a collection, `RunOn` pushes each element into a fresh copy `Cumulate`; the last event is collected and returned. The end result is a program that computes the sum of elements in each set:

```
9.0
6.0
16.0
10.0
```

The following picture shows how to depict the `RunOn` processor graphically. Like the other processors we have seen earlier (such as `Window` and `Slice`), `RunOn` can take any `Processor` object as an argument. However, if we want to pass a chain of processors, we must take care to encapsulate that chain inside a `GroupProcessor`.

![Applying a processor on collections of events with `RunOn`.](RunOnExample.png)

### Set-specific objects

The `util` package also provides a few functions and processors specific to some particular types of collections. The [`Sets`](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/util/Sets.html) class has a member field `Sets.isSubsetOrEqual` which refers to a function `IsSubsetOrEqual` that compares two `Set` objects. It also defines a processor [`PutInto`](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/util/Sets/PutInto.html) which receives arbitrary objects as input, and accumulates them into a set, which it returns as its output.

The following program shows the basic usage of <!--\index{Sets!PutInto@\texttt{PutInto}} \texttt{PutInto}-->`PutInto`<!--/i-->.

``` java
QueueSource src = new QueueSource().setEvents("A", "B", "C", "D");
Sets.PutInto put = new Sets.PutInto();
Connector.connect(src, put);
Pullable p = put.getPullableOutput();
Set<Object> set1, set2;
p.pull();
set1 = (Set<Object>) p.pull();
System.out.println("Set 1: " + set1);
p.pull();
set2 = (Set<Object>) p.pull();
System.out.println("Set 2: " + set2);
System.out.println("Set 1: " + set2);
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/util/PutIntoExample.java#L32)


It produces the following output:

```
Set 1: [A, B]
Set 2: [A, B, C, D]
Set 1: [A, B, C, D]
```

Note how after the second call to `pull`, the variable `set1` is a set that contains the first two events, "A" and "B". Two calls to `pull` later, variable `set2` contains, as expected, the first four events. The last call to `println` is more surprising. It reveals that `set1` now also contains the first four events! This is because the variables `set1` and `set2` actually are two references to the same object. In other words, processor `PutInto` keeps returning the same `Set`, each time with a new element added to it. We say that `PutInto` is a <!--\index{mutator processor} \textbf{mutator}-->**mutator**<!--/i--> processor: it modifies the state of the objects it returns.

If we want to have a different set for every output event, we must rather use [`PutIntoNew`](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/util/Sets/PutIntoNew.html). Upon each input event, this processor creates a new set, copies the content of the previous one, and adds the <!--\index{Sets!PutIntoNew@\texttt{PutIntoNew}}  new-->new<!--/i--> event into it. Since this processor performs a copy every time, it runs much slower than `PutInto`.

### List-specific objects

Functions and processors that work on arbitrary collections obviously also work on lists. BeepBeep provides a few more for collections that are *ordered*, such as lists and arrays. For example, [`NthElement`](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/util/NthElement.html) is a function that returns the <!--\index{NthElement@\texttt{NthElement}} element-->element<!--/i--> at the *n*-th position in an ordered collection.

The [`Lists`](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/util/Lists.html) class defines two processors that work on lists in a special way. The first is called [`Pack`](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/util/Lists/Pack.html) and has two input pipes. The first, called the *data* pipe, is a stream of arbitrary events. The second, called the *control* pipe, is a stream of Boolean values. You may remember that the `Filter` processor seen in the previous chapter had two similarly-named input pipes.

Processor <!--\index{Lists!Pack@\texttt{Pack}} \texttt{Pack}-->`Pack`<!--/i--> accumulates events received from the input pipe, as long as the corresponding event in the control pipe is the Boolean value `false`. When the value in the control pipe is `true`, `Pack` outputs the list of events accumulated so far, instantiates a new empty list, and puts the incoming event into it. Consider the following example:

``` java
QueueSource src1 = new QueueSource();
src1.setEvents(3, 1, 4, 1, 5, 9, 2, 6, 5, 3, 5);
QueueSource src2 = new QueueSource();
src2.setEvents(false, true, false, false, false, true, false, true);
Lists.Pack pack = new Lists.Pack();
Connector.connect(src1, 0, pack, 0);
Connector.connect(src2, 0, pack, 1);
Pullable p = pack.getPullableOutput();
for (int i = 0; i < 4; i++)
{
    System.out.println(p.pull());
}
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/util/PackExample.java#L48)


![Packing events into lists using the `Pack` processor.](PackExample.png)

We create a data and a control stream, connect them to a `Pack` processor and pull events from its output. The program prints:

```
[3]
[1, 4, 1, 5]
[9, 2]
[6, 5]
```

One can see how the control stream acts as a "trigger" that tells the `Pack` processor when to release a list of events.

The [`Unpack`](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/util/Lists/Unpack.html) processor is the exact opposite of `Pack`. It receives a stream of lists, and outputs the event of each list one by one:

``` java
QueueSource src1 = new QueueSource();
src1.addEvent(UtilityMethods.createList(1f, 3f, 5f));
src1.addEvent(UtilityMethods.createList(4f, 2f));
src1.addEvent(UtilityMethods.createList(4f, 4f, 8f));
src1.addEvent(UtilityMethods.createList(6f, 4f));
Lists.Unpack unpack = new Lists.Unpack();
Connector.connect(src1, 0, unpack, 0);
Pullable p = unpack.getPullableOutput();
for (int i = 0; i < 6; i++)
{
    System.out.println(p.pull());
}
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/util/UnpackExample.java#L51)


![Unpacking events from a stream of lists using the `Unpack` processor.](UnpackExample.png)

Of course, putting an <!--\index{Lists!Unpack@\texttt{Unpack}} \texttt{Unpack}-->`Unpack`<!--/i--> processor next to a `Pack` processor will recreate the original stream. This means that the following procsesor chain is equivalent to a `Passthrough` processor on the stream of numbers 3, 1, 4, ...

![Chaining a `Pack` and an `Unpack` processor.](PackUnpack.png)

Note that the end result of this program does not depend on the Boolean stream at the bottom. This control stream merely changes the way events are grouped into lists, but does not change the relative ordering of each event. Since the lists are unpacked immediately, the output from `Unpack` will always be the same.

One final remark must be made about `Unpack` when it is used in <!--\index{push mode} push mode-->push mode<!--/i-->. Consider the following simple chain:

![Using the `Unpack` processor in push mode.](UnpackPush.png)

Suppose that `p` is `Unpack`'s `Pushable` object; what do you think the following program will print?

``` java
List<Object> list = UtilityMethods.createList(1, 2, 3, 4);
System.out.println("Before first push");
p.push(list);
list = UtilityMethods.createList(5, 6, 7);
System.out.println("\nBefore second push");
p.push(list);
System.out.println("\nAfter second push");
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/util/UnpackPush.java#L54)


Let us see what happens on the first call to `push`. The `Unpack` processor is given a list of numbers. Its task is to output these numbers one by one; since we are in push mode, these numbers will hence be pushed to its output pipe and down to the `Print` processor. But how many such numbers will be pushed? Only the first one?

The answer is **all at once**. That is, on the single call to `push`, the `Unpack` processor will push all four events one after the other. The output of the program is therefore:

```
Before first push
1,2,3,4,
Before second push
5,6,7,
After second push
```

Notice how the first call to `push` on `Unpack` results in four calls to `push` on the downstream `Print` processor. We had already seen that the number of calls to `pull` may not be uniform across a processor chain; we now know that the same is true for calls to `push`. As a matter of fact, the `Pack` and `Unpack` processors are called **non-uniform**: for a single output event, the number of output events they produce is not always the same. In contrast, <!--\index{Processor!uniform} uniform-->uniform<!--/i--> processors produce the same number of output events for each input event.

We have already seen other non-uniform processors before: the `Filter`, `CountDecimate` and `Trim` processors sometimes produce zero output event from an input event. Here, we see a non-uniform processor in the opposite way: it sometimes produces more than one output event from a single input event.

### Map-specific objects

There is one last Java collection we haven't talked about: <!--\index{Map@\texttt{Map} (interface)} \texttt{Map}-->`Map`<!--/i-->. As you know, a map is a data structure that associates arbitrary *keys* to arbitrary *values*. A map can be queried for the value corresponding to a key using a method called `get()`. BeepBeep provides a [`Maps`](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/Maps.html) class that defines a few functions and processors specific to the manipulation of such <!--\index{Maps@\texttt{Maps}} maps-->maps<!--/i-->. The first one is [`Get`](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/util/Maps/Get.html), which, as you may guess, fetches a <!--\index{Maps!Get@\texttt{Get}} value-->value<!--/i--> from a map given the name of a key. A simple usage would be the following:

``` java
Map map = ...
Function get = new Maps.Get("foo");
Object[] out = new Object[1];
get.evaluate(new Object[]{map}, out);
// out[0] contains the value of key foo
```

The `Maps` class also defines a processor <!--\index{Maps!PutInto@\texttt{PutInto}} maps-->`PutInto`<!--/i--> that works in the same way as the one we have seen in `Sets` and `Lists`. It receives two input streams: the first one is made of the "keys", and the second one is made of the "values". When receiving an event front, it creates a key-value pair from the two events and uses it to update the map, which it then returns. For example, the following program:

``` java
QueueSource keys = new QueueSource().setEvents("foo", "bar", "foo", "baz");
QueueSource values = new QueueSource().setEvents(1, "abc", "def", 6);
Maps.PutInto put = new Maps.PutInto();
Connector.connect(keys, 0, put, 0);
Connector.connect(values, 0, put, 1);
Pullable p = put.getPullableOutput();
for (int i = 0; i < 4; i++)
{
    System.out.println(p.pull());
}
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/util/MapPutInto.java#L42)


...will produce the following output:

```
{foo=1}
{bar=abc, foo=1}
{bar=abc, foo=def}
{bar=abc, foo=def, baz=6}
```

Note how the map is *updated*: if a key already exists in the map, its corresponding value is replaced by the new one. Also note how types can be mixed in the map: the value for key "foo" is first a number, and is replaced later by string. A variant of `PutInto` is called <!--\index{Maps!PutIntoArray@\texttt{PutIntoArray}} maps-->`PutIntoArray`<!--/i-->, which takes a single input stream, whose events are *arrays*. The first element of the array contains the key, and the second contains the value.

One last function of interest is called [`Values`](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/util/Maps/Values.html). This function takes a map as input, and returns the collection made of all the <!--\index{Maps!Values@\texttt{Values}} values-->values<!--/i--> occurring in the key-value pairs it contains. This function performs the equivalent of the `values()` method in Java's `Map` interface.

## Pumps and tanks




## Basic input/output

So far, the data sources we used in our examples were simple, hard-coded `QueueSource`s. Obviously, the events in real-world use cases are more likely to come from somehwere else: a file, the program's standard input, or some other source. BeepBeep's `io` package provides a few functionalities for connecting processor chains to the outside world.

### Reading from a file

Consider for example a text <!--\index{file!reading from} files-->file<!--/i--> containing single numbers, each on a separate line:

    3
    1
    4
    1
    5
    9
    2.2

The [`ReadLines`](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/io/ReadLines.html) processor takes a Java <!--\index{InputStream@\texttt{InputStream}} \texttt{InputStream}-->`InputStream`<!--/i-->, and returns as its output events each text line that can be extracted from that stream. Pulling from a <!--\index{ReadLines@\texttt{ReadLines}} \texttt{ReadLines}-->`ReadLines`<!--/i--> processor is then straightfoward:

``` java
InputStream is = LineReaderExample.class.getResourceAsStream("pi.txt");
ReadLines reader = new ReadLines(is);
ApplyFunction cast = new ApplyFunction(Numbers.numberCast);
Connector.connect(reader, cast);
Pullable p = cast.getPullableOutput();
while (p.hasNext())
{
    Number n = (Number) p.next();
    System.out.println(n + "," + n.getClass().getSimpleName());
}
p.pull();
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/io/LineReaderExample.java#L35)


A few important observations must be made form this code sample. The first is that since we are reading from a file, eventually the `ReadLines` processor will reach the end of the file, and no further output event will be produced when pulled. Therefore, we must repeatedly ask the `Pullable` object whether there is a new output event available. This can be done using method <!--\index{Pullable!hasNext@\texttt{hasNext}} \texttt{hasNext}-->`hasNext()`<!--/i-->. This method returns `true` when a new event can be pulled, and `false` when the corresponding processor has no more events to produce. Therefore, in our code sample, we loop until `hasNext` returns `false`.

Note also that instead of using method `pull`, we use method <!--\index{Pullable!next@\texttt{next}} \texttt{next}-->`next()`<!--/i--> to get a new event. Methods `pull` and `next` are in fact *synonyms*: they do exactly the same thing. However, the pair of methods `hasNext`/`next` makes a `Pullable` look like a plain old Java <!--\index{Iterator@\texttt{Iterator}} \texttt{Iterator}-->`Iterator`<!--/i-->. As a matter of fact, this is precisely the case: although we did not mention it earlier, a `Pullable` does implement Java's `Iterator` interface, meaning that a `Pullable` can be used in a program wherever an `Iterator` is expected. This makes it very handy to use BeepBeep objects inside an existing program, without even being aware that they actually refer to processor chains.

The last remark is that the output events of `ReadLines` are *strings*. This means that if we want to pipe them into arithmetical functions, they must be converted into `Number` objects beforehand; forgetting to do so is a common programming mistake. A special function of utility class <!--\index{Numbers@\texttt{Numbers}} \texttt{Numbers}-->`Numbers`<!--/i-->, called [`NumberCast`](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/util/Numbers/NumberCast.html), is designed especially for that. This function takes as input any Java `Object`, and does its best to turn it into a `Number`. In particular, if the object is a `String`, it tries to parse that string into either an `int` or, if that fails, into a `float`. In our code example, we pipe the output of `reader` into an `ApplyFunction` processor that invokes this function on each event; the function is referred to by the static member field <!--\index{NumberCast@\texttt{NumberCast}} \texttt{Numbers.numberCast}-->`Numbers.numberCast`<!--/i-->.

The expected output of the program is:

    3,Integer
    1,Integer
    4,Integer
    1,Integer
    5,Integer
    9,Integer
    2.2,Float
    Exception in thread "main" java.util.NoSuchElementException
	    at ca.uqac.lif.cep.UniformProcessor$UnaryPullable.pull(UniformProcessor.java:269)
	    at io.LineReaderExample.main(LineReaderExample.java:43)

Note how the first lines of the file have been cast as an `Integer` number; the last number could not be parsed as an integer, therefore it has been cast as a `Float`.

The last printed lines show that an exception has been thrown by the program. This is caused by the very last instruction in the code, which makes one last `pull` on `p`. However, this happens right after `p.hasNext()` returns false, which has taken us out of the loop. As we have said earlier, attempting to pull an event from a `Pullable` that has no more event to produce causes such an exception to be thrown. Yet another programming mistake is to disregard the return value of `hasNext` (or not even calling it in the first place) and attempting to pull from an source that has "run dry".

The processor chain in this program can be represented as follows:

![Reading lines from a text file with `ReadLines`.](LineReaderExample.png)

This diagram introduces two new elements. First, the `ReadLines` processor is a box with a white sheet as its pictogram. As expected, the processor has one output pipe, which is painted in purple --the color that represents streams of `String` objects. Second, the processor seems to have an input pipe, but of a different shape than the ones we have seen earlier. This symbol does *not* represent a pipe, as can be confirmed by the fact that the input arity of `ReadLines` is zero. The funnel-shaped symbol rather represents a Java `InputStream` object. As we know, an `InputStream` can refer to an arbitrary source of bytes: a file, a network connection, and so on. Therefore, this symbol is intended to indicate that the line reader takes its source of bytes from some outside source --more precisely, from something that is not a BeepBeep processor. BeepBeep's square pipes cannot be connected into funnels, and vice-versa. The light-green color of the funnel indicates that the input stream provides raw bytes to the reader. The leftmost diskette symbol indicates that this particular input stream is connected to a file source.

### Reading from the standard input

As we have seen earlier, we can read lines from a source of text by passing an `InputStream` to a `ReadLines` processor. However, it is possible to read from arbitrary streams of bytes, and in particular from the special system stream called the <!--\index{standard input} \textbf{standard input}-->**standard input**<!--/i-->. The standard input is an implicit stream that every running program has; external processes can connect to this stream and send bytes that the program can then read.

In Java, the standard input can be manipulated like any `InputStream`, using the static member field `System.in`. We could pass it to a `ReadLines` processor as we have done before; however, instead of complete lines of text ending with the newline character (`\n`), let us read abitrary chunks of characters. This can be done using another processor called [`ReadStringStream`](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/io/ReadStringStream.html). The following program reads characters from the standard input and, using a `Print` processor, prints them back onto the standard output.

``` java
ReadStringStream reader = new ReadStringStream(System.in);
reader.setIsFile(false);
Pump pump = new Pump(100);
Thread pump_thread = new Thread(pump);
Connector.connect(reader, pump);
Print print = new Print();
Connector.connect(pump, print);
pump_thread.start();
while (true)
{
    Thread.sleep(10000);
}
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/io/ReadStdin.java#L114)


Since `ReadStringStream` works only in pull mode, and `Print` works only in `Push` mode, a `Pump` must be placed in between to repeatedly pull bytes from the input and push them to the output. This can be represented graphically as follows:

![Reading characters from the standard input.](ReadStdin.png)

In this picture, the leftmost processor is the [`StreamReader`](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/io/StreamReader.html). As you can see, <!--\index{StreamReader@\texttt{StreamReader}} it-->it<!--/i--> takes its input from the standard input; note how its left-hand side input has the "funnel" shape that represents system streams (and not BeepBeep pipes). A similar comment can be done for the <!--\index{Print@\texttt{Print}} \texttt{Print}-->`Print`<!--/i--> processor, which we have seen earlier. It receives input events, but as far as BeepBeep is concerned, does not produce any output events. Rather, it sends whatever it receives to the "outside world", this time through the `stdout` system stream. This is also what does the `Print` processor in examples from the previous chapters; however, the "stdout" output which was implicit in those examples is written here explicitly in the drawing. 

You can compile this program as a runnable JAR file (e.g. `read-stdin.jar`) and try it out on the command line. Suppose you type:

```
$ java -jar read-stdin.jar 
```

Nothing happens; however, if you type a few characters and press `Enter`, you should see the program reprint exactly what you typed (followed by a comma, as the `Print`} processor is instructed to insert one between each event).

Let's try something slightly more interesting. If you are at a Unix-like command prompt, you can create a [named pipe](https://en.wikipedia.org/wiki/Named_pipe). Let us create one with the name `mypipe`:

```
$ mkfifo mypipe
```

Now, let us launch `read-stdin.jar`, by redirecting `mypipe` into its standard input:

```
$ cat mypipe > java -jar read-stdin.jar
```
If you open another command prompt, you can then push characters into `mypipe`; for example using the command `echo`. Hence, if you type

```
$ echo "foo" > mypipe
```

you should see the string `foo` being immediately printed at the other command prompt. This happens because `read-stdin.jar` continuously polls its standard input for new characters, and pushes them down the processor chain whenever it receives some.


As you can see, the use of stream readers in BeepBeep, combined with system pipes on the command line, makes it possible for BeepBeep to interact with other programs from the command line, in exactly the same way Unix programs can be connected into each other.


This can be used to read a file. Instead of redirecting a named pipe to the program, one can use the `cat` command with an actual filename:

```
$ cat somefile.txt > java -jar read-stdin.jar
```

This will have for effect of reading and pushing the entire contents of `somefile.txt` into the processor chain.

### Separating the input into tokens



### Reading from an HTTP request

Using <!--\index{HTTP} HTTP-->HTTP<!--/i-->.

## Processor context

Each processor instance is also associated with a **context**. A context is a persistent and modifiable map that associates names to arbitrary objects. When a processor is duplicated, its context is duplicated as well. If a processor requires the evaluation of a function, the current context of the processor is passed to the function. Hence the function's arguments may contain references to names of context elements, which are replaced with their concrete values before evaluation. Basic processors, such as those described in this section, do not use context. However, some special processors defined in extensions to BeepBeep's core (the Moore machine and the first-order quantifiers, among others) manipulate their [`jdc:ca.uqac.lif.cep.Context`](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/Context.html) object.

## Exercises

1. Create a chain of processors that receives a stream of collections of integers, and outputs `true` for a collection if and only if it contains a number that corresponds to its size. For example, the set {1,3,6} is of size 3, and it contains the number 3, so the answer would be `true`. Do it...
  a. Using a `FunctionTree`
  b. Without using a `FunctionTree`

2. Create a chain of processors that receives three streams of numbers as its input. Its output should be a stream of *sets* of numbers. The output set at position *i* should contain the *i*-th element of each input stream, only if this element is positive. That is, if the first event of each stream is respectively -1, 3, 4, the first output set should be {3,4}.

3. Create a chain of processors that receives a stream of collections of numbers, and returns...
  a. the average of each collection
  b. the largest number of each collection

4. The `Strings` utility class in BeepBeep defines a `Function` object called `SplitString`. Use it to create a processor chain that receives a stream of arbitrary strings, and returns a stream made of each individual word, except those that start with the letter "a". For example, on the input event "this is an abridged text", the chain would produce the output events "this", "is", "text". (Hint: a simple solution involves the use of  `Unpack`.)

5. Consider a stream of letters of the alphabet. Create a processor chain that always returns the number of occurrences of the letter that has been seen most often so far. For example, on the input stream a,b,a,c,c,b,a, the processor would return 1,1,2,2,2,2,3. (Hint: a possible solution involves `Slice`, `Cumulate`, `Maps.Values` and `Numbers.max`, among others.)

<!-- :wrap=soft: -->