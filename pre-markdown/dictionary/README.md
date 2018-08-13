# Glossary

This book has introduced many concepts revolving around the concept of event streams. In particular, the BeepBeep library provides a little "zoo" of dozens of `Processor` and `Function` objects. In this appendix, you will find a non-exhaustive list of the various objects and notions that have been discussed. Processors and objects with a standardized graphical representation are also accompanied by the corresponding picture.

For more technical information about each of these objects, the reader is referred to the online API documentation, which is provides in-depth and up-to-date information.

#### Arity

For a `Processor` object, refers to the number of pipes it has. The *input arity* is the number of input streams accepted by the processor, and the *output arity* is the number of output streams it produces.

For a `Function` object, refers to the number of arguments it accepts or the number of values it produces.

#### `Bags`

A container class for functions and processors applying to generic collections, i.e. "bags" of objects.

#### `BinaryFunction`

A `Function` object having exactly two input arguments, and producing exactly one output value.

#### `BlackHole`

A special type of `Sink` that discards everything it receives. It is represented graphically as follows:

{@img images/tmf/BlackHole.png}{BlackHole}{.6}

#### `Booleans`

A container class for `Function` objects related to Boolean values. For example, the static reference `Boolean.and` refers to the `Function` computing the logical conjunction of two Booleans.

#### `Call`

A `Processor` object calling an external command upon receiving an event, and returning the output of that command as its output stream.

#### Closed (chain)

A property of a chain of processors, when either all its downstream processors are `Sink`s, or all its upstream processors are `Source`s. A chain of processors that is not closed will generally throw Java exceptions when events pass through it.


#### `Connector`

A utility class that provides a number of convenient methods for connecting the outputs of processors to the inputs of other processors. Methods provided by the `Connector` class are called `connect()` and have various signatures. When called with exactly two `Processor` arguments, `connect` assigns each output pipe of the first processor to the input pipe at the same position on the second processor.

#### `Constant`

A `Function` object that takes no input argument, and returns a single output value. Constants are used in `FunctionTree`s to refer to fixed values. A `Constant` instance can be created out of any Java object, and returns this object as its value.

#### `Context`

An associative (key-value) map used by `Processor` objects to store persistent data. Each processor has its own `Context` object. When a processor is cloned, the context of the original is copied into the clone. In addition, all operations on a `Context` object are synchronized.

#### `ContextVariable`

A `Function` object that acts as a placeholder for the value associated to a key in a the `Context` of a `Processor`. When a `ContextVariable` occurs inside the `FunctionTree` assigned to an `Evaluate` processor, it queries that processor's `Context` object to get the current value associated to the key.

#### `CountDecimate`

`Processor` that returns every *n*-th input event (starting with the first). The value *n* is called the **decimation interval**. However, a mode can be specified in order to output the *n*-th input event if it is the last event of the trace and it has not been output already. It is represented graphically as:

{@img images/tmf/CountDecimate.png}{CountDecimate}{.6}

#### `Cumulate`

`Processor` that creates a cumulative processor out of a cumulative function. This is simply an instance of `ApplyFunction` whose function is of a specific type (a
`CumulativeFunction`). It is represented graphically as:

{@img images/functions/Cumulate.png}{Cumulate}{.6}

#### `CumulativeFunction`

A special type of `Function` with memory.

#### `Demultiplex`

`Processor` object that converts a sequence of *n* consecutive events into an event that is a vector of size *n*, with the element at position "0" in the vector corresponding to the first event of the window. This effectively works as a time demultiplexer.

#### `Deserialize`

A `Processor` that takes structured character strings as its inputs, and turns each of them into Java objects with the corresponding content. It is represented graphically as follows:

{@img images/other/Deserialize.png}{Deserialize}{.6}

*Deserialization* can be used to restore the state of objects previously saved on a persistent medium, or to receive non-primitive data types over a communication medium such as a network. The opposite operation is called *serialization*.

#### `Equals`

A `Function` that checks for the equality between two objects. It is represented graphically as follows:

{@img images/functions/Equals.png}{Equals}{.6}

#### `Filter`

Discards events from an input trace based on a selection criterion. The
processor takes as input two events simultaneously; it outputs the first if
the second is true.

Graphically, this processor is represented as:

{@img images/tmf/Filter.png}{Filter}{.6}


 

#### FindPattern

Extracts chunks of an input stream based on a regular expression.

#### `Fork`

A `Processor` that duplicates a single input stream into two or more output streams. A `Fork` is used when the contents of the same stream must be processed by multiple processors in parallel. It is represented graphically as:

{@img images/tmf/Fork.png}{Fork}{.6}

#### `Freeze`

A `Processor` that repeatedly outputs the first event it has received. `Freeze` works a bit like `PullConstant`; however, while <code>Constant</code> is given the event to output, <code>Freeze</code> waits for a first event, outputs it, and then outputs that event whenever some new input comes in.

#### Front

Given *n* streams, the front at position *k* is the tuple made of the event at the *k*-th position in every stream. BeepBeep `Processor`s that have an input *arity* greater than 1 handle events one front at a time; this is called *synchronous processing*.

#### `Function`

A computation unit that receives one or more *arguments*, and produces one or more output *values*. Along with `Processor`, this is one of BeepBeep's fundamental classes. Contrary to processors, functions are *stateless* (or history-independent): the same inputs must always produce the same outputs.

Functions are represented graphically as rounded rectangles, with a pictogram describing the computation they perform, such as this:

{@img images/functions/Function.png}{Function}{.6}

A function with an input arity of *m* and an output arity of *n* is often referred to as an *m*:*n* function.

#### `FunctionTree`

A `Function` object representing the composition of multiple functions together to form a "compound" function. A function tree has a *root*, which consists of an *m*:*n* function. This function is connected to *n* children, which can be functions or function trees themselves. The drawing below depicts a function tree that composes multiplication and addition to form a more complex function of two arguments.

{@img images/functions/FunctionTree.png}{FunctionTree}{.6}

#### `GroupProcessor`

A `Processor` that encapsulates a chain of processors as if it were a single object. It is represented as follows:

{@img images/tmf/GroupProcessor.png}{GroupProcessor}{.6}

To create a `GroupProcessor`, one must first instantiate and connect the processors to be encapsulated. Each processor must then be added to the group through a method called `add`. Finally, the endpoints of the chain must be associated to the inputs and outputs of the group. From then on, the processor can be moved around, connected and duplicated as if it were a single processor.

In a graphical representation of a `GroupProcessor`, the processor chain inside the group can be drawn for illustrative purposes.

#### HttpGet

Reads chunks of data from an URL, using an HTTP request. These chunks are
returned as events in the form of strings.


 

#### `IdentityFunction`

Function that returns its input for its output. It is represented as follows:

{@img images/functions/IdentityFunction.png}{IdentityFunction}{.6}

The actual colour of the oval depends on the type of events that the function relays.

#### `IfThenElse`

Function that acts as an if-then-else. If its first input is true, it returns
its second input; otherwise it returns its third input. It is represented as follows:

{@img images/functions/IfThenElse.png}{IfThenElse}{.6}

#### `Insert`

Inserts an event a certain number of times before letting the input events
through. This processor can be used to shift events of an input trace
forward, padding the beginning of the trace with some dummy element.


 

#### NthElement

Function that returns the n-th element of an ordered collection (array or
list).

#### `Pack`

A `Processor` that accumulates events from a first input pipe, and sends them in a burst into a list based on the Boolean value received on its second input pipe. A value of `true` triggers the output of a list, while a value of `false` accumulates the event into the existing list. This processor is represented graphically as follows:

{@img images/util/Pack.png}{Pack}{.6}

The oppositve of `Pack` in `Unpack`.

#### `Passthrough`

A `Processor` object that lets every input event through as its output. This processor can be used as a placeholder when a piece of code needs to be passed a `Processor` object, but that in some cases, no processing on the events is necessary. Graphically, this processor is represented as:

{@img images/tmf/Passthrough.png}{Passthrough}{.6}


 

#### Prefix

Returns the first <i>n</i> input events and discards the following ones.


 

#### `Print`

A `Processor` that sends its input events to a Java `PrintStream` (such as the standard output). This processor takes whatever event it receives (i.e. any Java `Object`), calls its {@link Object#toString() toString()} method, and pushes the
resulting output to a print stream. Graphically, it is represented as:

{@img images/cli/Print.png}{Print}{.6}

#### `Processor`

A processing unit that receives zero or more input streams, and produces zero or more output streams. The `Processor` is the fundamental class where all stream computation occurs. All of BeepBeep's processors are descendants of this class. A processor is depicted graphically as a "box", with "pipes" representing its
input and output streams.

{@img images/Processor-generic.png}{Processor}{.6}

This class itself is abstract; nevertheless, it provides important methods for handling input/output event queues, connecting processors together, etc. However, if you write your own processor, you will most likely want to inherit from its child, `SingleProcessor`, which does some more work for you.

The `Processor` class does not assume anything about the type of events
being input or output. All its input and output queues are therefore declared
as containing instances of `Object`, Java's most generic type.

#### `Pullable`

Queries events on one of a processor's outputs. For a processor with an
output arity <i>n</i>, there exists *n* distinct pullables, namely one for
each output trace. Every pullable works roughly like a classical `Iterator`:
it is possible to check whether new output events are available, and get one
new output event. However, contrarily to iterators, `Pullable`s have two versions of each method: a *soft* and a *hard* version. The opposite of `Pullable`s are `Pushable`s --objects that allow users to feed input events to processors. Graphically, a `Pullable` is represented by a pipe connected to a processor, with an outward pointing triangle:

{@img images/Pullable.png}{Pullable}{.6}

#### Pull mode

One of the two operating modes of a chain of processors. In pull mode, a user or an application obtains references to the `Pullable` objects of the downstream processors of the chain, and calls their `pull()` method to ask for new output events. When using a chain of processors in pull mode, the chain must be closed at its inputs. The opposite mode is called *push mode*.

#### `Pump`

Processor that repeatedly pulls its input, and pushes the resulting events to its output. The `Pump` is a way to bridge an upstream part of a processor chain that works in *pull* mode, to a downstream part that operates in *push* mode.

Graphically, this processor is represented as:

{@img images/tmf/Pump.png}{Pump}{.6}

The repeated pulling of events from its input is started by calling this
processor's `#start()` method. In the background, this will instantiate
a new thread, which will endlessly call <tt>pull()</tt> on whatever input is
connected to the pump, and then call <tt>push()</tt> on whatever input is
connected to it.

The opposite of the `Pump` is the `Tank`.

#### `Pushable`

An object that gives events to some of a processor's input. Interface `Pushable` is the opposite of `Pullable`: rather than querying events form a processor's output (i.e. "pulling"), it gives events to a processor's input. This has for effect of triggering the processor's computation and "pushing" results (if any) to the processor's output. If a processor is of input arity *n*, there exist *n* distinct
`Pullable`s: one for each input pipe. Graphically, a `Pushable` is represented by a pipe connected to a processor, with an inward pointing triangle:

{@img images/Pushable.png}{Pushable}{.6}

#### Push mode

One of the two operating modes of a chain of processors. In push mode, a user or an application obtains references to the `Pushable` objects of the upstream processors of the chain, and calls their `push()` method to feed new input events. When using a chain of processors in push mode, the chain must be closed at its outputs. The opposite mode is called *pull mode*.

#### `QueueSink`

A `Sink` that accumulates events into queues, one for each input pipe. It is represented graphically as:

{@img images/tmf/QueueSink.png}{QueueSink}{.6}

#### `QueueSource`

A `Source` whose input is a queue of objects. One gives the `QueueSource` a list of events, and that source sends these events as its input one by one. When reaching the end of the list, the source returns to the beginning and keeps feeding events from the list endlessly. The `QueueSource` is represented graphically as:

{@img images/tmf/QueueSource.png}{QueueSource}{.6}

#### `ReadLines`

Source that reads text lines from a Java `InputStream`. It is represented graphically as:

{@img images/io/ReadLines.png}{ReadLines}{.6}

#### ReadStringStream

Extracts character strings from a Java `InputStream`.

#### `Serialize`

A `Processor` that takes arbitrary objects as its inputs, and turns each of them into a structured character string depicting their content. It is represented graphically as follows:

{@img images/other/Serialize.png}{Serialize}{.6}

*Serialization* can be used to store the state of objects on a persistent medium, or to transmit non-primitive data types over a communication medium such as a network. The opposite operation is called *deserialization*.

#### `StreamReader`

A `Source` that reads chunks of bytes from a Java `InputStream`.  It is represented graphically as follows:

{@img images/io/StreamReader.png}{StreamReader}{.6}

#### `SingleProcessor`

A `Processor` that performs a computation on input events to produce output events. This is the direct descendant of `Processor`, and probably the one you'll want to inherit from when creating your own processors. While `Processor` takes care of input and output queues, `SingleProcessor` also implements `Pullable`s and `Pushable`s. These take care of collecting input events, waiting until one new event is received from all input traces before triggering the computation, pulling and buffering events from all outputs when either of the `Pullable`s is being called, etc.

The only thing that is left undefined is what to do when new input events have been received from all input traces. This is the task of abstract method `compute()`, which descendants of this class must implement.

#### `Sink`

A `Processor` with an output *arity* of zero. It is used to close processor chains in *push mode*.

#### `Slice`

TODO

#### `Source`

A `Processor` with an input *arity* of zero. It is used to close processor chains in *pull mode*.

#### `StreamVariable`

A `Function` standing for the *i*-th stream given as input to a processor. A `StreamVariable` can be given as an argument to a `FunctionTree`. It is represented as follows:

{@img images/functions/StreamVariable.png}{StreamVariable}{.6}

The number inside the diamond represents the stream number. By convention, stream numbers start at 1 in drawings.

#### `Tank`

A `Processor` that accumulates pushed events into a queue until they are pulled. The Tank is a way to bridge an upstream part of a processor chain that works in *push* mode, to a downstream part that operates in *pull* mode.

Graphically, this processor is represented as:

{@img images/tmf/Tank.png}{Tank}{.6}

The opposite of the tank is the `Pump`.

#### `TimeDecimate`

A `Processor` which, after returning an input event, discards all others for the next *n* seconds. This processor therefore acts as a rate limiter. It is represented as:

{@img images/tmf/TimeDecimate.png}{TimeDecimate}{.6}

Note that this processor uses `System.currentTimeMillis()` as its clock. Moreover, a mode can be specified in order to output the last input event of the trace if it has not been output already.

#### `Trim`

A `Processor` that discards the first *n* events of its input stream, and outputs the remaining ones as is. It is represented as:

{@img images/tmf/Trim.png}{Trim}{.6}

#### `Tuple`

A special type of event defined by BeepBeep's *Tuple* palette, which consists of an associative map between keys and values. Contrary to tuples in relational databases, where values must be scalar (i.e. strings or numbers), the tuples in BeepBeep can have arbitrary Java objects as values (including other tuples).

#### `TupleFeeder`

A `Processor` that converts lines of text into `Tuple`s. It is represented as:

{@img images/other/TupleFeeder.png}{TupleFeeder}{.6}

#### `TurnInto`

A `Processor` that turns any input event into a predefined object. It is represented graphically as:

{@img images/functions/TurnInto.png}{TurnInto}{.6}

#### `UnaryFunction`

A `Function` object that has an input and output *arity* of exactly 1.

#### Uniform (processor)

A `Processor` that produces the same number of output fronts for every input front it receives. Occasionally, the number of output fronts produced is explicitly mentioned: a *k*-uniform processor produces exactly *k* output fronts for every input front.

#### `Unpack`

A `Processor` that unpacks a list of objects by outputting its contents as separate events. This processor is represented graphically as follows: 

{@img images/util/Unpack.png}{Unpack}{.6}

The opposite of `Unpack` is `Pack`.

#### `Window`

Simulates the application of a "sliding window" to a trace. It is represented graphically as:

{@img images/tmf/Window.png}{Window}{.6}

The processor takes as arguments another processor P and a window width *n*. It returns the result of P after processing events 0 to *n*-1... - Then the result of (a new instance of P) that processes events 1 to *n*, and so on.


<!-- :wrap=soft: -->