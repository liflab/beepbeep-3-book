Basic Concepts
==============

In this chapter, you will learn about the the fundamental principles for using BeepBeep through simple examples. In particular, you'll be shown the basic usage of two types of objects: processors and functions.

## Processors

The first fundamental building block of BeepBeep is an object called a **processor**. This object that takes one or more *event streams* as its input, and and returns one or more *event streams* as its output. A processor is a stateful device: for a given input, its output may depend on events received in the past. Virtually all the processing of event traces is done through the action of a processor, or a combination of multiple processors chained together to achieve the desired functionality. In terms of Java, all processors are descendents of the generic [Processor](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/Processor.html) class.

An easy way to understand processors is to think of them as "boxes" having one or more "pipes". Some of these pipes are used to feed events to the processor (input pipes), while others are used to collect events produced by the processor (output pipes). Throughout this book, we will often represent processors graphically exactly in this way, as the following figure shows. A processor object is represented by a square box, with a pictogram giving an idea of the type of computation it executes on events. On the sides of this box are one or more "pipes" representing its inputs and outputs. Input pipes are indicated with a red, inward-pointing triangle, while output pipes are represented by a green, outward-pointing triangle. 

![A graphical representation of a generic processor taking one input stream, and producing one output stream.](pipe-tuple.png)

The color of the pipes themselves will be used to denote the type of events passing through them. According to the convention in this book, a blue-green pipe represents a stream of numbers, a grey pipe contains a stream of Boolean values, etc.

The number of input and output pipes is called the (input and output) **arity** of a processor; these two numbers vary depending on the actual type of processor we are talking about. For example, the previous picture represents a processor with an input arity of 1, and an output arity of 1. Events come in by one end, while events (maybe of a different kind) come out by the other end.

## Pulling events

There are two ways to interact with a processor. The first is by getting a hold of the processor's output pipe, and by repeatedly asking for new events. The action of requesting a new output event is called **pulling**, and this mode of operation is called *pull mode*.

Let us instantiate a simple processor and pull events from it. The following code snippet shows such a thing, using a processor called [QueueSource](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/QueueSource.html).

``` java
QueueSource source = new QueueSource();
source.setEvents(1, 2, 4, 8, 16, 32);
Pullable p = source.getPullableOutput();
for (int i = 0; i < 8; i++)
{
    int x = (Integer) p.pull();
    System.out.println("The event is: " + x);
}
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/basic/QueueSourceUsage.java#L39)


`QueueSource` is a simple processor that does only one thing. When it is created, it is given a list of events; from that point on, it will endlessly output these events, one by one, looping back at the beginning of the list when it reaches the end. The first two lines of the previous snippet create a new instance of `QueueSource`, and then give the list of events it is instructed to repeat (in this case, the events are integers).

To collect events from a processor's output, one uses a special object called a [Pullable](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/Pullable.html). The third instruction takes care of obtaining an instance of `Pullable` corresponding to `QueueSource`'s output.

![A first example](QueueSourceUsage.png)

A `Pullable` can be seen as a form of iterator over an output trace. It provides a method, called [pull()](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/Pullable.html#pull()); each call to `pull()` asks the corresponding processor to produce one more output event. The loop in the previous code snippet amounts to calling `pull()` eight times. Since events handled by processors can be anything (Booleans, numbers, strings, sets, etc.), the method returns an object of the most generic type, i.e. `Object`. It is up to the user of a processor to know what precise type of event this return value can be cast into. In our case, we know that the `QueueSource` we created returns integers, and so we manually cast the output of `pull()` into objects of this type.

Since the queue source loops through its array of events, after reaching the last (32), it will restart from the beginning of its list. The expected output of this program is:

    The event is: 1
    The event is: 2
    The event is: 4
    The event is: 8
    The event is: 16
    The event is: 32
    The event is: 1
    The event is: 2

Note that `source` springs into action only upon a call to `pull()` on its `Pullable` object. That is, it computes and returns a new output event only upon request. In other words, we can see it as some kind of gearbox that does something only when we turn the crank: each turn of the "crank" triggers the production of a new output event.
    
This simple example shows the basic concepts around the use of a processor:

- An instance of a processor is first created
- To read events from its output, we must obtain an instance of a `Pullable` object from this processor
- Events can be queried by calling `pull()` on this `Pullable` object

## Piping processors {#piping}

BeepBeep provides dozens of processors, but each of them in isolation performs a simple operation. To perform more complex computations, processors can be composed (or "piped") together, by letting the output of one processor be the input of another. This piping is possible as long as the type of the first processor's output matches the type expected by the second processor's input.

Let us create a simple example of piping by building upon the previous example, as follows:

``` java
QueueSource source = new QueueSource();
source.setEvents(1, 2, 3, 4, 5, 6);
Doubler doubler = new Doubler();
Connector.connect(source, doubler);
Pullable p = doubler.getPullableOutput();
for (int i = 0; i < 8; i++)
{
    int x = (Integer) p.pull();
    System.out.println("The event is: " + x);
    UtilityMethods.pause(1000);
}
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/basic/PipingUnary.java#L43)


First, a `QueueSource` is created as before; then, an instance of another processor called `Doubler` is also created. For the sake of the example, let us simply assume that `Doubler` takes arbitrary integers as its input, multiples them by two, and returns the result as its output.

The next instruction uses the [Connector](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/Connector.html) object to pipe the two processors together. The call to method [...) connect()](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/Connector.html#connect(Processor) sets up the processors so that the output of `source` is sent directly to the input of `doubler`. We can then obtain `doubler`'s `Pullable` object, and fetch its output events like before. The output of this program will be:

    The event is: 2
    The event is: 4
    The event is: 6
    The event is: 8
    ...

As expected, each event of the output stream is the double of the one at matching position in the source's input stream.

Notice how we obtained a hold of `doubler`'s output Pullable, and made our `pull` calls on *that* object --not on `source`'s.

We mentioned earlier that processors can have more than one input "pipe", or one or more output "pipe". The following example shows it:

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
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/basic/PipingBinary.java#L41)


This time, we create *two* sources of numbers. We intend to connect these two sources of numbers to a processor called `add`, which, incidentally, has two input pipes. The interesting bit comes in the calls to [int, ca.uqac.lif.cep.Processor, int) connect()](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/Connector.html#connect(ca.uqac.lif.cep.Processor,), which now includes a few more arguments. The first call connects the output of `source1` to the *first* input of a processor called `add`. The second call connects the output of `source2` to the *second* input of `add`. The rest is done as usual: a `Pullable` is obtained from `add`, and its first few output events are printed:

	The event is: 5.0
	The event is: 8.0
	The event is: 5.0
	The event is: 9.0
	...

The previous example shows that the output of `add` seems to be the pairwise sum of events from `source1` and `source2`. Indeed, 2+3=5, 7+1=8, 1+4=5, etc. This is indeed exactly the case. When a processor has an input arity of 2 or more, it processes its inputs in batches we call **fronts**. A *front* is a set of events in identical positions in each input trace. Hence, the pair of events 2 and 3 corresponds to the front at position 0; the pair 7 and 1 corresponds to the front at position 1, and so on.

When a processor has an arity of 2 or more, the processing of its input is done *synchronously*. This means that a computation step will be performed if and only if a new event can be consumed from each input trace. More on that later.

## <a name="mismatch">When types do not match</a>

We said earlier that any processor can be piped to any other, *provided that they have matching types*. The following code example shows what happens when types do not match:

``` java
QueueSource source = new QueueSource();
source.setEvents(3);
Processor av = new ApplyFunction(Numbers.absoluteValue);
Connector.connect(source, av);
Processor neg = new ApplyFunction(Booleans.not);
Connector.connect(av, neg);
System.out.println("This line will not be reached");
```
[⚓](https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/basic/IncorrectPiping.java#L58)


The problem lies in the fact that processor `av` sends out events of type `Number` as its output, while processor `neg` expects events of type `Boolean` as its input. Since the former cannot be converted into the latter, the call to `connect()` will throw an exception similar to this one:

	Exception in thread "main" ca.uqac.lif.cep.Connector$IncompatibleTypesException:
	Cannot connect output 0 of ABS to input 0 of !: incompatible types
		at ca.uqac.lif.cep.Connector.checkForException(Connector.java:268)
		at ca.uqac.lif.cep.Connector.connect(Connector.java:123)
		at ca.uqac.lif.cep.Connector.connect(Connector.java:191)
		at queries.IncorrectPiping.main(IncorrectPiping.java:43)

Here "ABS" and "!" are the symbols defined for `av` and `neg`, respectively.

A processor can be queried for the types it accepts for input number *n* by using the [getInputType()](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/Processor.html#getInputType(int)); ditto for the type produced at output number *n* with [getOutputType()](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/Processor.html#getOutputType(int)).
		
## <a name="class">The <code>Processor</code> class</a>

Processors in BeepBeep all descend from the abstract class [Processor](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/Processor.html), which provides a few common functionalities, such as obtaining a reference to the n-th input or output, getting the type of the n-th input or output, etc.

A processor produces its output in a *streaming* fashion: this means that output events are made available progressively while the input events are consumed. In other words, a processor does not wait to read its entire input trace before starting to produce output events. However, a processor can require more than one input event to create an output event, and hence may not always output something right away.


## <a name="context">Context</a>

Each processor instance is also associated with a **context**. A context is a persistent and modifiable map that associates names to arbitrary objects. When a processor is duplicated, its context is duplicated as well. If a processor requires the evaluation of a function, the current context of the processor is passed to the function. Hence the function's arguments may contain references to names of context elements, which are replaced with their concrete values before evaluation. Basic processors, such as those described in this section, do not use context. However, some special processors defined in extensions to BeepBeep's core (the Moore machine and the first-order quantifiers, among others) manipulate their [jdc:ca.uqac.lif.cep.Context](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/Context.html) object.

<!-- :wrap=soft: -->