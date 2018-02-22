Basic Concepts
==============

In this chapter, you will learn about the the fundamental principles for using BeepBeep through simple examples.

## Processors

The first fundamental building block of BeepBeep is an object called a **processor**. This object that takes one or more *event streams* as its input, and and returns one or more *event streams* as its output. A processor is a stateful device: for a given input, its output may depend on events received in the past. Virtually all the processing of event traces is done through the action of a processor, or a combination of multiple processors chained together to achieve the desired functionality.

An easy way to understand processors is to think of them as "boxes" having one or more "pipes". Some of these pipes are used to feed events to the processor (input pipes), while others are used to collect events from the processor (output pipes). The number of input and output pipes is called the (input and output) **arity** of a processor; these two numbers vary depending on the actual type of processor we are talking about. For example, the following picture represents a processor with an input arity of 1, and an output arity of 1. Events come in by one end, while events (maybe of a different kind) come out by the other end.

![A 1:1 processor](pipe-tuple.png)

## <a name="example">A first example</a>

The following code snippet shows a first example of processor called {@link jdc:ca.uqac.lif.cep.QueueSource QueueSource}.

{@snipm basic/QueueSourceUsage.java}{/}

`QueueSource` is a simple processor that does only one thing. When it is created, it is given a list of events; from that point on, it will endlessly output these events, one by one, looping back at the beginning of the list when it reaches the end. The first two lines of the previous snippet create a new instance of `QueueSource`, and then give the list of events it is instructed to repeat (in this case, the events are integers).

To collect events from a processor's output, one uses a special object called a {@link jdc:ca.uqac.lif.cep.Pullable Pullable}. The third instruction takes care of obtaining an instance of `Pullable` corresponding to `QueueSource`'s output.

A `Pullable` can be seen as a form of iterator over an output trace. It provides a method, called {@link jdm:ca.uqac.lif.cep.Pullable#pull() pull()}; each call to `pull()` asks the corresponding processor to produce one more output event. The loop in the previous code snippet amounts to calling `pull()` eight times. Since events handled by processors can be anything (Booleans, numbers, strings, sets, etc.), the method returns an object of the most generic type, i.e. `Object`. It is up to the user of a processor to know what precise type of event this return value can be cast into. In our case, we know that the `QueueSource` we created returns integers, and so we manually cast the output of `pull()` into objects of this type.

Since the queue source loops through its array of events, after reaching the last (32), it will restart from the beginning of its list. The expected output of this program is:

    The event is: 1
    The event is: 2
    The event is: 4
    The event is: 8
    The event is: 16
    The event is: 32
    The event is: 1
    The event is: 2

This simple example shows the basic concepts around the use of a processor:

- An instance of a processor is first created
- To read events from its output, we must obtain an instance of a `Pullable` object from this processor
- Events can be queried by calling `pull()` on this `Pullable` object

## <a name="piping">Piping processors</a>

Processors can be composed (or "piped") together, by letting the output of one processor be the input of another. This piping is possible as long as the type of the first processor's output matches the second processor's input type. This is exemplified by the following piece of code:

{@snipm basic/PipingUnary.java}{/}

A `QueueSource` is created as before; then, an instance of another processor called `Doubler` is also created. For the sake of the example, the `Doubler` processor takes integers as input, and returns the double of each of them as its output.

The next instruction uses the {@link jdc:ca.uqac.lif.cep.Connector Connector} object to pipe the two processors together. The call to method {@link jdm:ca.uqac.lif.cep.Connector#connect(Processor ...) connect()} sets up the processors so that the output of `source` is sent directly to the input of `doubler`. We can then obtain `doubler`'s `Pullable` object, and fetch its output events like before. The output of this program will be:

    The event is: 2
    The event is: 4
    The event is: 6
    The event is: 8
    ...

We mentioned earlier that processors can have more than one input "pipe", or one or more output "pipe". The following example shows it:

{@snipm basic/PipingBinary.java}{/}

This time, we create *two* sources of numbers. We intend to connect these two sources of numbers to a processor called `add`, which, incidentally, has two input pipes. The interesting bit comes in the calls to {@link jdm:ca.uqac.lif.cep.Connector#connect(ca.uqac.lif.cep.Processor, int, ca.uqac.lif.cep.Processor, int) connect()}, which now includes a few more arguments. The first call connects the output of `source1` to the *first* input of a processor called `add`. The second call connects the output of `source2` to the *second* input of `add`. The rest is done as usual: a `Pullable` is obtained from `add`, and its first few output events are printed:

	The event is: 5.0
	The event is: 8.0
	The event is: 5.0
	The event is: 9.0
	...

The previous example shows that the output of `add` seems to be the pairwise sum of events from `source1` and `source2`. Indeed, 2+3=5, 7+1=8, 1+4=5, etc. This is indeed exactly the case. When a processor has an input arity of 2 or more, it processes its inputs in batches we call **fronts**. A *front* is a set of events in identical positions in each input trace. Hence, the pair of events 2 and 3 corresponds to the front at position 0; the pair 7 and 1 corresponds to the front at position 1, and so on.

When a processor has an arity of 2 or more, the processing of its input is done *synchronously*. This means that a computation step will be performed if and only if a new event can be consumed from each input trace. More on that later.

## <a name="mismatch">When types do not match</a>

We said earlier that any processor can be piped to any other, *provided that they have matching types*. The following code example shows what happens when types do not match:

{@snipm basic/IncorrectPiping.java}{/}

The problem lies in the fact that processor `av` sends out events of type `Number` as its output, while processor `neg` expects events of type `Boolean` as its input. Since the former cannot be converted into the latter, the call to `connect()` will throw an exception similar to this one:

	Exception in thread "main" ca.uqac.lif.cep.Connector$IncompatibleTypesException:
	Cannot connect output 0 of ABS to input 0 of !: incompatible types
		at ca.uqac.lif.cep.Connector.checkForException(Connector.java:268)
		at ca.uqac.lif.cep.Connector.connect(Connector.java:123)
		at ca.uqac.lif.cep.Connector.connect(Connector.java:191)
		at queries.IncorrectPiping.main(IncorrectPiping.java:43)

Here "ABS" and "!" are the symbols defined for `av` and `neg`, respectively.

A processor can be queried for the types it accepts for input number *n* by using the {@link jdm:ca.uqac.lif.cep.Processor#getInputType(int) getInputType()}; ditto for the type produced at output number *n* with {@link jdm:ca.uqac.lif.cep.Processor#getOutputType(int) getOutputType()}.
		
## <a name="class">The <code>Processor</code> class</a>

Processors in BeepBeep all descend from the abstract class {@link jdc:ca.uqac.lif.cep.Processor Processor}, which provides a few common functionalities, such as obtaining a reference to the n-th input or output, getting the type of the n-th input or output, etc.

A processor produces its output in a *streaming* fashion: this means that output events are made available progressively while the input events are consumed. In other words, a processor does not wait to read its entire input trace before starting to produce output events. However, a processor can require more than one input event to create an output event, and hence may not always output something right away.


## <a name="context">Context</a>

Each processor instance is also associated with a **context**. A context is a persistent and modifiable map that associates names to arbitrary objects. When a processor is duplicated, its context is duplicated as well. If a processor requires the evaluation of a function, the current context of the processor is passed to the function. Hence the function's arguments may contain references to names of context elements, which are replaced with their concrete values before evaluation. Basic processors, such as those described in this section, do not use context. However, some special processors defined in extensions to BeepBeep's core (the Moore machine and the first-order quantifiers, among others) manipulate their {@link jdc:ca.uqac.lif.cep.Context} object.

<!-- :wrap=soft: -->