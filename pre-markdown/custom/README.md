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

### Partial Evaluation

We have seen in Chapter 4 that functions can also be partially evaluated. As an example, let us create a function that calculates the area of a triangle based on the length of its three sides, by using <!--\index{Heron's formula} Heron's formula-->Heron's formula<!--/i-->: if *A* is the area of the triangle, and *a*, *b*, and *c* are the lengths of its sides, then *A*² = *s*(*s*-*a*)(*s*-*b*)(*s*-*c*), where *s* is the *semiperimeter*, or half of the triangle's perimeter. Writing method `evaluate` is relatively straightforward:

{@snips customprocessors/TriangleArea.java}{public void evaluate}

However, a few shortcuts can be made when evaluating this function. For example, as soon as one of the sides is 0, the shape cannot be a triangle, and we can set the area to 0. To implement this functionality, a `Function` object must override a method called <!--\index{Function@\texttt{Function}!evaluatePartial@\texttt{evaluatePartial}} \texttt{evaluatePartial}-->`evaluatePartial`<!--/i-->, as follows:

{@snips customprocessors/TriangleArea.java}{public boolean evaluatePartial}

The signature of this method contains an array of input arguments, an array of output values, and a `Context` object (which may be null). Since this method is called during partial evaluation, any of the elements of the `inputs` array may be null. Therefore the method checks, for each of the three elements of the array, whether it is non-null, and if so, whether it is equal to zero. If this is the case, value 0 is put into the `outputs` array, and the method returns `true`. This is meant to indicate that the function was successfully evaluated and produced an output value.

If none of the elements is equal to zero, the method then checks if all the elements are non-null; if so, the method calls `evaluate` to compute its output value using the formula. Finally, when none of these conditions apply, the method returns `false`, indicating that no output value could be computed.

Let us now try partial evaluation using various combinations of input arguments, as in the following program:

{@snipm customprocessors/TriangleArea.java}{/}

The first case corresponds to regular evaluation; the method returns `true` and puts the area 6 in the `out` array. The second case corresponds to partial evaluation, but where no output value can be computed; consequently, the method returns `false`. Finally, the third case also corresponds to partial evaluation, but where an output value can be produced. Therefore, the output of this program is:

```
b: true, 6.0
b: false, null
b: true, 0
```

## Create your Own Processor

In the same way as for functions, BeepBeep allows you to create new `Processor` objects, which can then be composed with existing processors. What is more, you start to do so with no more than a few lines of boilerplate codeq.

### As a `GroupProcessor`

As for functions, a first way to create a new processor is to define a class that extends `GroupProcessor`, and to put into that class' constructor the instructions that build the desired chain of processors.

Suppose we want to create a processor that counts events. A simple way to do so is to create a `GroupProcessor` like this one:

``` java
GroupProcessor g = new GroupProcessor(1, 1);
{
	TurnInto one = new TurnInto(1);
	Cumulate sum = new Cumulate(
	  new CumulativeFunction<Number>(Numbers.addition));
	Connector.connect(one, sum);
	g.associateInput(0, one, 0);
	g.associateOutput(0, sum, 0);
	g.addProcessors(one, sum);
}
```

However, if we want to use this processor at multiple locations, we will again have to copy-paste this code everywhere we need a new instance of the counter. A better way is to create a new class that extends `GroupProcessor`:

{@snips customprocessors/CounterGroup.java}{public class}

From then on, it is possible to write `new CounterGroup()` to get a fresh instance of this processor.

### As a Descendant of `Processor`

Using a group works only if your custom processor can be expressed by piping other existing processors. If this is not the case, you have to resort to extending one of BeepBeep's `Processor` descendents. The most generic way to do so is to extend the `Processor` class directly. This class defines many functionalities that the user does not need to implement:

- methods <!--\index{Processor@\texttt{Processor}!getInputArity@\texttt{getInputArity}} \texttt{getInputArity}-->`getInputArity`<!--/i--> and <!--\index{Processor@\texttt{Processor}!getOutputArity@\texttt{getOutputArity}} \texttt{getOutputArity}-->`getOutputArity`<!--/i--> declare the input and output arity of the processor
- based on these arities, the `Processor` class takes care of creating the appropriate number of input and output queues for storing events
- method <!--\index{Processor@\texttt{Processor}!setPullableInput@\texttt{setPullableInput}} \texttt{setPullableInput}-->`setPullableInput`<!--/i--> associates one of the the processor's input pipes to the `Pullable` object of an upstream processor
- method <!--\index{Processor@\texttt{Processor}!setPushableOutput@\texttt{setPushableOutput}} \texttt{setPushableOutput}-->`setPushableOutput`<!--/i--> associates one of the the processor's output pipes to the `Pushable` object of a downstream processor
- methods <!--\index{Processor@\texttt{Processor}!getContext@\texttt{getContext}} \texttt{getContext}-->`getContext`<!--/i--> and <!--\index{Processor@\texttt{Processor}!setContext@\texttt{setContext}} \texttt{setContext}-->`setContext`<!--/i--> handle the interaction with the processor's internal `Context` object
- finally, the `Processor` class also handles the unique ID given to each instance, which can be queried with <!--\index{Processor@\texttt{Processor}!getId@\texttt{getId}} \texttt{getId}-->`getId`<!--/i-->.

These methods correspond to the very basic functionalities of a BeepBeep processor. As the reader may observe, almost none of these methods need to be called by an end-user creating processor chains (as a matter of fact, none of the code examples we have seen so far use these methods, except for `getId`). They are mostly used by the `Connector` utility class, which, as we have seen, is responsible for piping processor objects together. Many of these methods are declared `final`, which means that their behaviour cannot be changed by descendents of this class. However, since `Processor` itself is abstract, a number of important methods are left to the user to be implemented:

- <!--\index{Processor@\texttt{Processor}!duplicate@\texttt{duplicate}} \texttt{duplicate}-->`duplicate`<!--/i--> must create a copy of the current processor object
- <!--\index{Processor@\texttt{Processor}!getPushableInput@\texttt{getPushableInput}} \texttt{getPushableInput}-->`getPushableInput`<!--/i--> must provide an instance of an object implementing the `Pushable` interface to feed input values when the processor is used in push mode
- <!--\index{Processor@\texttt{Processor}!getPullableOutput@\texttt{getPullableOutput}} \texttt{getPullableOutput}-->`getPullableOutput`<!--/i--> must provide an instance of an object implementing the `Pullable` interface to fetch output values when the processor is used in pull mode

All the event handling functionalities must, of course, be implemented by the user. Typically, this means that the `Pullable` and `Pushable` objects keep a reference to the underlying processor they are associated with; calls to `pull` or `push` trigger some computation inside the processor and manipulate events in the input and output queues. For synchronous processing (which corresponds to almost every processor found in this book), this task is tedious, especially for processors with an input arity greater than 1. For example, a call to `push` may not trigger the computation of an output event if a complete input front cannot be consumed; in push mode, one must also carefully implement the subtle behaviour of the `pull` and `pullSoft` methods, and so on. We do not recommend users to extend this class directly, except perhaps in very specific situations.

Thankfully, BeepBeep provides a descendent of `Processor` that takes care of even more functionalities for the user; this class is called <!--\index{SynchronousProcessor@\texttt{SynchronousProcessor}} \texttt{SynchronousProcessor}-->`SynchronousProcessor`<!--/i-->.  This class defines its own `Pushable` and `Pullable` objects, and therefore, already implements the `getPushableInput` and `getPullableInput` methods. All the user has left to do is to:

- decide the input and output arity of the processor; this is done by passing these two numbers to `SynchronousProcessor`'s constructor, typically in a call to `super()` in the new class's constructor
- write the actual computation that should occur when a *complete* input front becomes available, i.e. what output event(s) to produce (if any), given an input event; this is done by implementing a method called <!--\index{SynchronousProcessor@\texttt{SynchronousProcessor}!compute@\texttt{compute}} \texttt{compute}-->`compute`<!--/i-->.
- optionally, override the methods `duplicate` (to allow the creation of copies of the processor), as well as  <!--\index{Processor@\texttt{Processor}!getInputTypesFor@\texttt{getInputTypesFor}} \texttt{getInputTypesFor}-->`getInputTypesFor`<!--/i--> and <!--\index{Processor@\texttt{Processor}!getOutputTypeFor@\texttt{getOutputTypeFor}} \texttt{getOutputTypeFor}-->`getOutputTypeFor`<!--/i--> (to declare the input and output type for each of the processor's pipes)

Using `SynchronousProcessor`, the minimal working example for a custom processor is made of six lines of code:

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

### A Simple 1:1 Processor

As a first example, let us write a processor that receives character strings as its input events, and that computes the length of each string (we know that BeepBeep's `Size` function already does this, but let us ignore it for the purpose of this example). The input arity of this processor is therefore 1 (it receives one string at a time), and its output arity is 1 (it outputs a number). Specifying the input and output arity is done through the call to `super()` in the processor's constructor: the first argument is the input arity, and the second argument is the output arity.

The actual functionality of the processor is written in the body of method `compute`. This method is called whenever an input event is available, and a new output event is required. Its first argument is an array of Java objects; the size of that array is that of the input arity we declared for this processor (in our case: 1). Computing the length amounts to extracting the first (and only) event of array inputs, casting it to a String, and getting its length. The end result is this:

{@snips customprocessors/StringLength.java}{public class}

Note that the `compute` method has a second argument, which is a queue of object arrays. If the processor is of arity *n*, it must create an event front of size *n*, which means putting an event into each of its *n* output queues. It may also decide to output more than one such *n*-uplet for a single input event, and these events are accumulated into a queue --hence the slightly odd object type. The method puts into the `outputs` queue an array of `Object`s with a single element, which, in this case, is an integer corresponding to the input string's length.

The return type of method `compute` is a `boolean`. This value is used to signal to a `Pullable` object whether the processor is expected to produce more events in the future. A processor should return `false` only if it is absolutely sure that no more events will be produced in the future; in all other situations, it must return `true`. Examples of processors whose `compute` method returns `false` are processors that read from a file; when the end of the file is reached, they return false to indicate that no more new events are expected. Except in very special situations such as these, a processor should return `true`.

The other method that needs to be implemented is `duplicate`; it works in the same way as for functions, and in general only consists of returning a new instance of the class. However, the reader should notice that for processors, `duplicate` takes a Boolean argument, <!--\index{processor!duplication} called-->called<!--/i--> `with_state`. If this argument is set to `true`, the processor should not simply create a new copy of itself; it must also transfer its current *state* to the new object. Typically, this means that if the processor has member fields that determine its behaviour, these member fields must be set to the same values in the newly created copy. This is not the case in this simple example, since the processor we create has no member field at all. Therefore, method `duplicate` simply ignores the argument and returns a new instance of `StringLength`. From then on, a user can instantiate `StringLength`, connect it to the output of any other processor that produces strings, and pipe its result to the input of any other processor that accepts numbers.

Optionally, a processor can declare its input and output types, as is the case for function objects. Therefore, one can override the methods `getInputTypesFor` and `getOutputType`. In the present case, the type of the first (and only) input stream is `String`, while the type of the first (and only) output stream is `Number`. This leads to the methods shown in the previous code snippet. If a processor does not override these methods, by default the `Processor` class returns a special type called <!--\index{Variant@\texttt{Variant}} \texttt{Variant}-->`Variant`<!--/i-->. The occurrence of such a type in an input or output pipe disables the type checking step that the `Connector` class normally performs before connecting two processors together.

### Greater Input and Output Arity

This second example will show a processor that takes as input two traces. The events of each trace are instances of a simple user-defined class called `Point`, defined as follows:

``` java
public class Point {
  public float x;
  public float y;
}
```

We will write a processor that takes one event (i.e. one Point) from each input trace, and return the Euclidean distance between these two points.

{@snips customprocessors/EuclideanDistance.java}{public class}

As the reader can see, the `compute` method now expects the `input` array to contain *two* elements, and these two elements are cast as instances of `Point`. In addition, the `duplicate` method introduces a small variant: rather than returning a new copy of the processor, it returns itself (`this`). This behaviour makes sense in the case of a <!--\index{singleton} \textbf{singleton}-->**singleton**<!--/i--> --that is, an object which exists in a single copy across an entire program. In such a case, a good practice is to reduce the visibility of the class' constructor (to prevent users from calling it and creating new instances), and to provide instead a static reference to a single instance of the object. This is the goal of the `instance` static field.

As we have seen in earlier chapters, many BeepBeep objects (especially functions) are singletons. For example, the utility classes `Booleans` and `Numbers` provide static references to a few general-purpose objects, such as `Booleans.and` or `Numbers.addition`. Singleton processors are less common, but this example shows that it is possible to implement them in a clean way.

As we know, a processor is not limited to producing a single output stream. In this example, we show how to implement a processor with an output arity of two. This processor takes as input a single trace of `Point`s (see the example above), and sends the *x* and *y* component of that point as events of two output streams.

{@snips customprocessors/SplitPoint.java}{public class}

This time, the processor adds to the output queue an array of size 2. One must remember that it is an error to add an array whose size is not equal to the processor's output arity. Although this may not be detected immediately, such an incorrect behaviour is likely to create exceptions at some point in the execution of the program.

### Non-Uniform Processors

So far, all processors we designed return one output event for every input event (or pair of events) they receive. (As a matter of fact, it would have been easier to implement them as `Function`s that we could have passed to an `ApplyFunction` processor.) In BeepBeep's terminology, these processors are called **uniform** (or more precisely, 1-uniform). However, this needs not be the case. The following processor outputs an event if its value is greater than 0, and no event at all otherwise.

{@snips customprocessors/OutIfPositive.java}{public class}

The way to indicate that a processor does not produce any output for an input is to simply add nothing to the output queue. Note that this should not be confused with returning `false`, which signifies that the processor will never output any event in the future.

Conversely, a processor does not need to output only one event for each input event. For example, the following processor repeats an input event as many times as its numerical value: if the event is the value 3, it is repeated 3 times in the output. Method `compute` of this processor would look like the following:

{@snips customprocessors/Stuttering.java}{public boolean compute}

This example shows why `compute`'s `outputs` argument is a *queue* of arrays of objects. In this class, a call to `compute` may result in more than one event being output. If `compute` could output only one event at a time, our processor would need to buffer the events to output somewhere, and draw events from that buffer on subsequent calls to `compute`. Fortunately, the `SynchronousProcessor` class handles this in a transparent manner. Therefore, `compute` can put as many events as one wishes in the output queue, and `SynchronousProcessor` takes care of releasing them one by one through its `Pullable` object.

This example puts in light an interesting feature of the `SynchronousProcessor` class. Notice how we inserted a `println` statement in the first line of method `compute`. This allows us to track the moments where method `compute` is being called by BeepBeep. Consider the following program:

{@snipm customprocessors/Stuttering.java}{/}

This program calls `pull` on an instance of the `Stuttering` processor four times. The first call to `pull` triggers a call to `compute` on `s` in the background; this explains the first two lines printed at the console:

```
Call to compute
Call to pull: 1
```

The second call to `pull` results in another call to `compute` on `s`, producing the value 2, and printing the next two lines:

```
Call to compute
Call to pull: 2
```

However, the third call to `pull` directly outputs the value 2, without triggering a call to `compute`; therefore, the next line to be printed is:

```
Call to pull: 2
```

This may seem surprising, but can easily be explained. The previous call to `pull` made `s` receive the input event 2. As a result, the call to `compute` put into the `outputs` queue *two* object arrays, each containing the number 2. The first object array was immediately retrieved and returned as the result of the call to `pull`, while the contents of the second object array was put into the processor's output queue, waiting for the next call to `pull`. Consequently, upon the next call to `pull`, there was no need to call `compute`, since an output event was already waiting in the processor's output queue, ready to be retrieved.

Therefore, when designing a new `SynchronousProcessor`, one must keep in mind that calls to `push` (resp. `pull`) on a processor's `Pushable` (resp. `Pullable`) do not always correspond to a call to `compute`, depending on the current contents of the processor's input and output queues.

### Stateful Processors

So far, all our processors are "memoryless": they keep no information about past events when making their computation. It is also possible to create "memoryful" processors. As an example, let us create a processor called `MyMax`, which outputs the maximum between the current event and the previous one. That is, given the following input trace:

    5, 1, 2, 3, 6, 4, ...

the processor should output:

    (nothing), 5, 2, 3, 6, 6, ...

Notice how, after receiving the first event, the processor should not return anything yet, as it needs two events before being able to output something. A possible implementation could be the following:

{@snips customprocessors/MyMax.java}{public class}

This processor is the first in this chapter to have a member field, called `last`. When the processor is instantiated, `last` is set to `null`. Each call to `compute` compares the value of `last` with the current event (if `last` is not null), and then sets the value of `last` to the current event. Therefore, the member field `last` acts as a form of "memory": for a given input event, the processor will produce a different output depending on the contents of this field --which itself depends on the previous event given to the processor.

The presence of a member field changes the way of implementing method `duplicate`. Remember that a processor has the option of being copied *along with its state*, by setting the value of argument `with_state` to `true`. Therefore, the code for `duplicate` must take into account this additional possibility. Notice how a new instance of `MyMax`, called `mm`, is created; if the duplication is stateful, an extra step is taken to copy the current value of `last` into `mm`. This has for effect of putting `mm` into the same state as the current object.

The implementation of `duplicate` is probably the most delicate part in the creation of a new stateful `SynchronousProcessor`. Failing to create a faithful copy of the original object (for example, by failing to transfer the values of all the appropriate member fields) may result in unforseen and hard-to-debug behaviours. As an example, let us go back to the `Stuttering` processor we created previously. Consider the following program:

{@snipm customprocessors/StatefulDuplication.java}{/}

```
Call to compute
Call to pull on p1: 2
```

Let us now make a stateful duplicate of `s1`, connect it to a new event source, and call `pull` once:

{@snipm customprocessors/StatefulDuplication.java}{\*}

The program prints:

```
Call to compute
Call to pull on p2: 3
```

However, this is not the expected output. As we have seen in a previous example, the next event that should be output by processor `s1` is the second instance of number 2. Processor `s2` should be a *stateful* copy of `s1`, and hence, produce the same output event. Instead, the call to `pull` on `s2` resulted in `s2` pulling number 3 from `src2` and sending it to its output. The reason for this strange behaviour is the fact that, when `s1` was duplicated, the contents of its input and output queues was not transferred to `s2`. It turns out that the events present in these queues, most of the time, are also part of a processor's state.

Technically, a descendent of `SynchronousProcessor` does not have a direct access to these queues. Rather than copying their contents manually in every implementation of `duplicate`, a shortcut consists of calling a method called <!--\index{Processor@\texttt{Processor}!duplicateInto@\texttt{duplicateInto}} \texttt{duplicateInto}-->`duplicateInto`<!--/i-->, provided by the `Processor` class. This method receives as an argument the target copy of the `Processor`; it is responsible for copying the contents of the input and output queues into this object. We can hence create a new version of `Stutter`, called `StutteringCopy`, containing a "corrected" version of method `duplicate`. The method now looks as follows:

{@snips customprocessors/StutteringCopy.java}{public StutteringCopy duplicate}

We can now write a new version of our program, which uses the `StutteringCopy` object in place of `Stuttering`:

{@snipm customprocessors/StatefulDuplication.java}{!}

This time, the program prints, as expected:

```
Call to compute
Call to pull on p1: 2
Call to pull on p2: 2
```

- - -

In this chapter, we have seen how BeepBeep's functionalities can be extended by letting users invent their own `Processor` and `Function` objects. All of BeepBeep's palettes are created in this way: a palette is just a pre-compiled JAR bundle of classes that depend on BeepBeep's core (and possibly other external libraries). We strongly encourage the reader to experiment with creating new processors specfic to the use cases they may encounter. It is hoped that BeepBeep's palette architecture, combined with its simple extension mechanisms, will help third-party users contribute to the BeepBeep ecosystem by developing and distributing extensions suited to their own needs.

<!-- :wrap=soft: -->
