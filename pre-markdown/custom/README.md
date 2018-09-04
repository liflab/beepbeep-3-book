Extending BeepBeep
==================

BeepBeep was designed from the start to be easily extensible. As was discussed earlier, it consists of only a small core of built-in processors and functions. The rest of its functionalities are implemented through custom processors and grammar extensions, grouped in packages called *palettes*.

However, it may very well be possible that none of the processors or functions in existing palettes are appropriate for a particular problem. Fortunately, BeepBeep provides easy means of creating your own objects, by simply extending some of the classes provided by the core library. In this chapter, we shall see through multiple examples how custom functions and processors can be created, often in just a few lines of code.

## Creating Custom Functions

Let us start with the simple case of functions. A custom function is any object that inherits from the base class {@link ca:uqac.lif.cep.functions.Function Function}. There are two main ways to create new function classes:

- By extending `FunctionTree` and composing existing `Function` objects
- By extending `Function` or one of its more specific descendents, such as `UnaryFunction` or `BinaryFunction`. In such a case, the function can made of arbitrary Java code.

### As a Function Tree

A first way of creating a function is to create a new class that extends {@link jdc:ca.uqac.lif.cep.functions FunctionTree}. The constructor to this class has to call `FunctionTree`'s constructor, and to build the appropriate function tree right there. Recall the function tree we created in Chapter 3, which computed the function *f*(*x*,*y*,*z*) = (*x*+*y*)×*z*. We used to build this function tree as follows:

``` java
FunctionTree tree = new FunctionTree(Numbers.multiplication,
				new FunctionTree(Numbers.addition, 
						StreamVariable.X, StreamVariable.Y),
				StreamVariable.Z);
```

However, if we want to reuse this function in various programs, we need to copy-paste this instruction multiple times --with all the problems associated with copy-pasting. A better practice would be to create a `CustomFunctionTree` that encapsulates the creation of the function inside its constructor. This can be done by creating a new class that extends `FunctionTree`, like this:

{@snipm functions/custom/CustomFunctionTree.java}{/}

Note how the first `new FunctionTree` in the original code has been replaced by a call to `super`, the constructor of the parent `FunctionTree` class. From then on, `CustomFunctionTree` can be used anywhere like the original function tree, for example like this:

``` java
Function f = new CustomFunctionTree();
ApplyFunction af = new ApplyFunction(f);
...
```

An interesting advantage is that, should you wish to change the actual function that is computed, you only need to make the modification at a single location.

### As a New Object

The previous technique only works for custom functions that can actually be expressed in terms of existing functions. When this is not possible, we must resort to creating a new, full-fledged `Function` class from scratch, composed of arbitrary Java code. It turns out that this is not very hard.

The most generic way of doing so is to directly extend the abstract class `Function`, and to implement all the mandatory methods. There are six of them:

- Method `evaluate` is responsible for doing the actual computation; it receives an array of input arguments, and writes to an array of output arguments.
- Method `getInputArity` and `getOutputArity` declare the function's input and output arity, respectively. They must each return a single integer number.
- Method `getInputTypesFor` is used to specify the type of the function's input arguments. Method `getOutputTypeFor` does the same thing for the function's output values.
- Method `duplicate` must return a new instance (a "clone") of the function.

As a simple example, let us write a new `Function` that multiplies a number by two. We start by creating an empty class that extends `Function`:

``` java
public class CustomDouble extends Function
{
}
```

A few methods are easy to implement. The case of <!--\index{Function@\texttt{Function}!getInputArity@\texttt{getInputArity}} \texttt{getInputArity}-->`getInputArity`<!--/i--> and <!--\index{Function@\texttt{Function}!getOutputArity@\texttt{getOutputArity}} \texttt{getOutputArity}-->`getOutputArity`<!--/i--> can be solved quickly: our function is expected to receive one argument, and to produce one output value; hence both methods should return 1. The <!--\index{Function@\texttt{Function}!duplicate@\texttt{duplicate}} \texttt{duplicate}-->`duplicate`<!--/i--> method is also straightfoward: we simply need to return a new instance of `CustomDouble`. This yields the following code:

{@snipm functions/custom/CustomDouble.java}{!}

The next method to implement is <!--\index{Function@\texttt{Function}!evaluate@\texttt{evaluate}} \texttt{evaluate}-->`evaluate`<!--/i-->, which receives an `inputs` array and an `outputs` array. Since our function declares an input arity of 1, `inputs` should contain a single element; moreover, this element should be an instance of `Number`. Similarly, we expect `outputs` to be an array of size 1. The method produces its return value by writing to the `outputs` array. The code for `evaluate` could therefore look like this:

{@snips functions/custom/CustomDouble.java}{public void evaluate}

Method <!--\index{Function@\texttt{Function}!getInputTypesFor@\texttt{getInputTypesFor}} \texttt{getInputTypesFor}-->`getInputTypesFor`<!--/i--> allows other objects to ask the function about the type of its arguments. It receives as arguments a set *s* of classes and an index *i*; its task is to add to *s* the `Class` object corresponding to the expected type of the *i*-th argument of the function (as usual, indices start at 0). This results in the following code:

{@snips functions/custom/CustomDouble.java}{public void getInputTypesFor}

Note that we check if *i* is 0; if so, we add the class `Number` to *s*, otherwise we add nothing. This is because `CustomDouble` has only one argument; it does not make sense to declare a type for indices higher than 0. It is important to note that this method can add more than one class to the set. For example, a function could accept either sets or lists as its arguments; in such a case, method `getInputTypesFor` would add both `List.class` and `Set.class` to the set.

The principle for <!--\index{Function@\texttt{Function}!getOutputTypeFor@\texttt{getOutputTypeFor}} \texttt{getOutputTypeFor}-->`getOutputTypeFor`<!--/i--> is similar; the slight difference is that the method must *return* a `Class` object:

{@snips functions/custom/CustomDouble.java}{public Class}

We again check for the index, and only return a type for *i*=0.

The purpose of the input/output arity/type methods is to declare what is called the function's *signature*. The <!--\index{Connector@\texttt{Connector}} \texttt{Connector}-->`Connector`<!--/i--> object we use to create processor chains calls these methods to make sure that functions and processors are piped correctly. It is therefore important to properly declare the arity and types for each custom object we create, in order to avoid exceptions being thrown when calling `connect()` with these objects.

We now have a complete new `Function` object that can be used like any other. For example:

``` java
Function f = new CustomDouble();
ApplyFunction af = new ApplyFunction(f);
```

As you might expect, a `Function` may have more than one input or output argument, and these arguments do not need to be of the same type. To illustrate this, let us create a new function `CutString` that takes two arguments: a string *s* and a number *n*. Its purpose is to cut *s* after *n* characters and return the result. A possible implementation would be:

{@snips functions/custom/CutString.java}{public class CutString}

### Unary and Binary Functions

Extending `Function` directly results in lots of "boilerplate" code. If your intended function is 1:1 or 2:1 (that is, it has an input arity of 1 or 2, and an output arity of 1), a shorter way to create a new `Function` object is to create a new class that extends either {@link jdc:ca.uqac.lif.cep.functions.UnaryFunction UnaryFunction} or {@link jdc:ca.uqac.lif.cep.functions.BinaryFunction BinaryFunction}. These classes take care of most of the housekeeping associated to functions, and require you to simply implement a method called `getValue()`, responsible for computing the output, given some input(s). In this method, you can write whatever Java code you want.

As an example, let us rewrite our `CustomDouble` function; it is a 1:1 function, so we will create a class that extends <!--\index{UnaryFunction@\texttt{UnaryFunction}} \texttt{UnaryFunction}-->`UnaryFunction`<!--/i-->. It turns out this new object now only requires five lines of code:

{@snips functions/custom/UnaryDouble.java}{public class UnaryDouble}

As you can see, you must also declare the input and output type for the function; here, the function accepts a `Number` and returns a `Number`. These types must also be reflected in the function's constructor, where you must call the superclass constructor and pass it a `Class` instance of each input and output argument.

Method  <!--\index{UnaryFunction@\texttt{UnaryFunction}!getValue@\texttt{getValue}} \texttt{getValue()}-->`getValue()`<!--/i--> is where the output of the function is computed for the input. Since the function is unary and declares its single input argument as a number, the method has a single `Number` argument. Similarly, since the function declares its output to also be a number, the return type of this method is `Number`.

Function `CutString` could also be simplified by defining it as a descendent of <!--\index{BinaryFunction@\texttt{BinaryFunction}} \texttt{BinaryFunction}-->`BinaryFunction`<!--/i-->:

{@snips functions/custom/BinaryCutString.java}{public class BinaryCutString}

This time, the class has three type arguments: the first two represent the type of the first and second argument, and the last rerpesents the type of the return value. Otherwise, method `getValue` works according to similar principles as `UnaryFunction`.

## Create your Own Processor

In the same way as for functions, BeepBeep allows you to create new `Processor` objects, which can then be composed with existing processors. What is more, you start to do so with no more than a few lines of boilerplate codeq.

### As a `GroupProcessor`

As for functions, a first way to create a new processor is to define a class that extends `GroupProcessor`, and to put into that class' constructor the instructions that build the desired chain of processors.

Suppose we want to create a processor that counts events. A simple way to do so is to create a `GroupProcessor` like this one:

``` java
GroupProcessor g = new GroupProcessor(1, 1);
{
	TurnInto one = new TurnInto(1);
	Cumulate sum = new Cumulate(new CumulativeFunction<Number>(Numbers.addition));
	Connector.connect(one, sum);
	g.associateInput(0, one, 0);
	g.associateOutput(0, sum, 0);
	g.addProcessors(one, sum);
}
```

However, if we want to use this processor at multiple locations, we will again have to copy-paste this code everywhere we need a new instance of the counter. A better way is to create a new class that extends `GroupProcessor`:

{@snips customprocessors/CounterGroup.java}{public class}

From then on, it is possible to write `new CounterGroup()` to get a fresh instance of this processor.

### As a `SynchronousProcessor`

Using a group works only if your custom processor can be expressed by piping other existing processors. If this is not the case, you have to resort to extending one of BeepBeep's `Processor` descendents.

The simplest way to do so is to extend the <!--\index{SynchronousProcessor@\texttt{SynchronousProcessor}} \texttt{SynchronousProcessor}-->`SynchronousProcessor`<!--/i--> class, which takes care of most of the "plumbing" related to event management: connecting inputs and outputs, looking after event queues, etc. All you have left to do is to:

- Define how many input pipes your processor needs, and how many output streams it produces. As we know, these two values are called the input and output <!--\index{processor!arity} \emph{arity}-->*arity*<!--/i--> of the processor, respectively.
- Write the actual computation that should occur, i.e. what output event(s) to produce (if any), given an input event.

The minimal working example for a custom processor is made of six lines of code:

``` java
import ca.uqac.lif.cep.*;

public class MyProcessor extends SynchronousProcessor {

  public MyProcessor() {
	super(0, 0);
  }

  public boolean compute(Object[] inputs, Queue<Object[]> outputs) {
	return true;
  }
}
```

This results in a processor that accepts no inputs, and produces no output. To make things more interesting, we will study a couple of examples.

### Example 1: String Length

As a first example, let us write a processor that receives character strings as its input events, and that computes the length of each string. The input arity of this processor is therefore 1 (it receives one string at a time), and its output arity is 1 (it outputs a number). Specifying the input and output arity is done through the call to `super()` in the processor's constructor: the first argument is the input arity, and the second argument is the output arity.

The actual functionality of our processor will be written in the body of method <!--\index{SynchronousProcessor@\texttt{SynchronousProcessor}!compute@\texttt{compute}} \texttt{compute()}-->`compute()`<!--/i-->. This method is called whenever an input event is available, and a new output event is required. Its first argument is an array of Java objects; the size of that array is that of the input arity we declared for this processor (in our case: 1).  Computing the length amounts to extracting the first (and only) event of array inputs, casting it to a String, and getting its length. The end result is this:

{@snips customprocessors/StringLength.java}{public class}

Note that the compute() method has a second argument, which is a queue of arrays of objects. If the processor is of arity *n*, it must create an event front of size *n*, i.e. put an event into each of its *n* output traces. It may also decide to output more than one such *n*-uplet for a single input event, and these events are accumulated into a queue --hence the slightly odd object type.

The return type of method `compute` is a `boolean`. This value is used to signal to a `Pullable` object whether the processor is expected to produce more events in the future. A processor should return `false` only if it is absolutely sure that no more events will be produced in the future; in all other situations, it must return `true`. Examples of processors whose `compute` method returns `false` are processors that read from a file; when the end of the file is reached, they return false to indicate that no more new events are expected. Except in very special situations such as these, a processor should return `true`.

The other method that we need to implement is `duplicate`; it works in the same way as for functions, and in general only consists of returning a new instance of our class.

That's it. From then on, you can instantiate `StringLength`, connect it to the output of any other processor that produces strings, and pipe its result to the input of any other processor that accepts numbers.

### Example 2: Euclidean Distance

This second example will show an example of a processor that takes as input two traces. The events of each trace are instances of the user-defined class Point:

``` java
public class Point {
  public float x;
  public float y;
}
```

We will write a processor that takes one event (i.e. one Point) from each input trace, and return the Euclidean distance between these two points.

``` java
import ca.uqac.lif.cep.*;

public class EuclideanDistance extends SynchronousProcessor {

  public StringLength() {
	super(2, 1);
  }

  public Queue<Object[]> compute(Object[] inputs) {
	Point p1 = (Point) inputs[0];
	Point p2 = (Point) inputs[1];
	float distance = Math.sqrt(Math.pow(p2.x - p1.x, 2) + Math.pow(p2.y - p1.y, 2));
	return Processor.wrapObject(distance);
  }
}
```

### Example 3: Separating a Point

This processor takes as input a single trace of Points (see example above), and sends the x and y component of that point as events of two output traces. It is an example of processor with an output arity of 2.

``` java
import ca.uqac.lif.cep.*;

public class SplitPoint extends SynchronousProcessor {

  public SplitPoint() {
	super(1, 2);
  }

  public Queue<Object[]> compute(Object[] inputs) {
	Point p = (Point) inputs[0];
	float[] output_event = new float[2];
	float[0] = p.x;
	float[1] = p.y;
	return Processor.wrapVector(output_event);
  }
}
```

Note that we use wrapVector(), rather than wrapObject(), as the result we are producing is already an array of size 2. Method wrapVector() simply puts that array into a new empty queue. Note also that it is an error to produce an array whose size is not equal to the processor's output arity.

### Example 4: Threshold

So far, all processors we designed return one output event for every input event (or pair of events) they receive. (As a matter of fact, it would have been easier to implement them as `Function`s that we could have passed to an `ApplyFunction` processor.) This needs not be the case. The following processor outputs an event if its value is greater than 0, and no event at all otherwise.

{@snips customprocessors/OutIfPositive.java}{public class}

The way to indicate that a processor does not produce any output for an input is to return null. Note that this should not be confused with the output arity of the processor.

### Example 5: Stuttering

Conversely, a processor does not need to output only one event for each input event. For example, the following processor repeats an input event as many times as its numerical value: if the event is the value 3, it is repeated 3 times in the output.

{@snips customprocessors/Stuttering.java}{public class}

This example shows why `compute`'s `outputs` argument is a *queue* of arrays of objects. In this class, a call to `compute` may result in more than one event being output. If `compute` could output only one event at a time, our processor would need to buffer the events to output somewhere, and draw events from that buffer on subsequent calls to `compute`. Fortunately, the `SynchronousProcessor` class handles this in a transparent manner. Therefore, `compute` can put as many events as you wish in the output queue, and `SynchronousProcessor` takes care of releasing them one by one through its `Pullable` object.


### Example 6: a Processor with Memory

So far, all our processors are memoryless: they keep no information about past events when making their computation. It is also possible to create "memoryful" processors. As an example, let's create a processor that outputs the maximum between the current event and the previous one. That is, given the following input trace:

    5, 1, 2, 3, 6, 4, ...

the processor should output:

    (nothing), 5, 2, 3, 6, 6, ...

Notice how, after receiving the first event, the processor should not return anything yet, as it needs two events before saying something. Here's a possible implementation:

{@snips customprocessors/MyMax.java}{public class}

<!-- :wrap=soft: -->
