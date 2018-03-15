# A dictionary of BeepBeep objects

#### Bags

A container object for functions and processors applying to generic
collections, i.e. "bags" of objects.


 

#### BinaryFunction

Function of two inputs and one output.





 

#### BlackHole

A special type of `Sink` that discards everything it receives.


 

#### Booleans

A container object for Boolean functions.


 

#### Call

Processor calling an external command upon receiving an event,
and returning the output of that command as its output stream.


 

#### Connector

Provides a number of convenient methods for connecting the outputs of
processors to the inputs of other processors.

Methods provided by the `Connector` class are called
`connect()` and have various signatures. Their return value
typically consists of the <em>last</em> processor of the chain given
as an argument. This means that nested calls to `connect()`
are possible to form a complex chain of processors in one pass, e.g.
``` java
Processor p = Connector.connect(
  new QueueSource(2, 1),
  Connector.connect(new QueueSource(1, 1), new Addition(), 0, 0),
  0, 1);
```

In the previous example, the inner call to <code>connect()</code>
links output 0 of a <code>QueueSource</code> to input 0 of an
`Addition` processor; this partially-connected
`Addition` is the return value of this method. It is then used
in the outer call, where another `QueueSource` is linked
to its input 1. This fully-connected `Addition` is what is
put into variable `p`.

If you use lots of calls to `Connector.connect`, you may
consider writing:
``` java
static import Connector.connect;
```
in the beginning of your file, so you can simply write `connect`
instead of `Connector.connect` every time.


 

#### Constant

Representation of a unary constant.


 

#### Context

Associative map used by processors to store persistent data. In addition,
all operations on a `Context` object are synchronized.


 

#### ContextVariable

Placeholder for the value of a context element. A `ContextVariable`
can be given as an argument to a `FunctionTree`.


 

#### CountDecimate

Returns one input event and discards the next <i>n</i>-1. The value <i>n</i>
is called the <em>decimation interval</em>.
However, a mode can be specified in order to output the <i>n</i>-<i>i</i>th input
event if it is the last event of the trace and it has not been output already.


 

#### Cumulate

Creates a cumulative processor out of a cumulative function.
This is simply a `ApplyFunction` whose function is of
a specific type (a `CumulativeFunction`). However, it has a
special grammar that allows any binary function to be turned into
a cumulative processor.


 

#### CumulativeFunction

A function with memory.


 

#### Demultiplex

Converts a sequence of <i>n</i> consecutive events into an event
that is a vector of size <i>n</i>. This effectively
works as a time demultiplexer.



 

#### Equals

A function that checks for the equality of various data types.


 

#### Filter

Discards events from an input trace based on a selection criterion.
The processor takes as input two events simultaneously; it outputs
the first if the second is true.


 

#### FindPattern

Extracts chunks of an input stream based on a regular expression.


 

#### Fork

Duplicates an input trace into two or more output traces.


 

#### Freeze

Repeatedly outputs the first event it has received. <code>Freeze</code>
works a bit like `PullConstant`; however, while <code>Constant</code>
is given the event to output, <code>Freeze</code> waits for a first event,
outputs it, and then outputs that event whenever some new input comes in.


 

#### Function

Represents a stateless <i>m</i>-to-<i>n</i> function.


 

#### FunctionTree

A tree of n-ary functions composed together.


 

#### HttpGet

Reads chunks of data from an URL, using an HTTP request.
These chunks are returned as events in the form of strings.


 

#### IdentityFunction

Function that returns its input for its output.


 

#### IfThenElse

Function that acts as an if-then-else. If its first input is true,
it returns its second input; otherwise it returns its third input.


 

#### Insert

Inserts an event a certain number of times before letting the input
events through. This processor can be used to shift events of an input
trace forward, padding the beginning of the trace with some dummy
element.


 

#### NthElement

Function that returns the n-th element of an ordered collection
(array or list).


 

#### Prefix

Returns the first <i>n</i> input events and discards the following ones.


 

#### Print

Sends its input to a PrintStream
(such as the standard output). This processor takes whatever event it
receives (i.e. any Java <tt>Object</tt>), calls its {@link Object#toString()
toString()} method, and pushes the resulting output to a print stream.
Graphically, it is represented as:

![Processor](/doc-files/cli/Print.png)

The behaviour of this processor can be configured in a few ways. Methods
{@link #setPrefix(String) setPrefix()} and {@link #setPrefix(String) setSuffix()}
can specify the character string to be displayed before and after each
event, and method {@link #setSeparator(String) setSeparator()} defines the
symbol that is inserted between each event. Further customization of the
output can be achieved by passing to a fancier type of print stream, such
as an ANSI-aware printer.


 

#### Processor

Receives zero or more input events, and produces zero or more output
events. The processor is the fundamental class where all computation
occurs. All of BeepBeep's processors (including yours)
are descendants of this class.

A processor is depicted graphically as a "box", with "pipes" representing
its input and output streams.

![Processor](/doc-files/Processor-generic.png)

This class itself is abstract; nevertheless, it provides important
methods for handling input/output event queues, connecting processors
together, etc. However, if you write your own processor, you will
most likely want to inherit from its child, `SingleProcessor`, which
does some more work for you.

The `Processor` class does not assume anything about the type of
events being input or output. All its input and output queues are
therefore declared as containing instances of `Object`, Java's
most generic type.



 

#### Pullable

Queries events on one of a processor's outputs. For a processor with
an output arity <i>n</i>, there exists *n* distinct pullables,
namely one for each output trace. Every pullable works roughly like a
classical `Iterator`: it is possible to check whether new
output events are available, and get one new output event.

However, contrarily to iterators, `Pullable`s have two versions of
each method: a *soft* and a *hard* version.

- **Soft** methods make a single attempt at producing an
  output event. Since processors are connected in a chain, this generally
  means pulling events from the input in order to produce the output.
  However, if pulling the input produces no event, no output event can
  be produced. In such a case, `#hasNextSoft()` will return a special
  value (<code>MAYBE</code>), and `#pullSoft()` will return
  <code>null</code>. Soft methods can be seen a doing "one turn of the
  crank" on the whole chain of processors --whether or not this outputs
  something.
- **Hard** methods are actually calls to soft methods until
  an output event is produced: the "crank" is turned as long as necessary
  to produce something. This means that one call to, e.g.
  `#pull()` may consume more than one event from a processor's
  input. Therefore, calls to `#hasNext()` never return
  <code>MAYBE</code> (only <code>YES</code> or <code>NO</code>), and
  `#pull()` returns <code>null</code> only if no event will
  ever be output in the future (this occurs, for example, when pulling
  events from a file, and the end of the file has been reached).

The lifecycle of a `Pullable` object is as follows:

- One obtains a reference to one of a processor's pullables. This
  can be done explicitly, e.g. by calling
  `Processor#getPullableOutput(int)`, or implicitly, for example
  through every call to `Connector#connect(Processor...)`.
- At various moments, one calls `#hasNextSoft()` (or
  `#hasNext()` to check if events are available
- One calls `#pullSoft()` (or `#pull()` to produce the next
  available output event.

The Pullable interface extends the `Iterator` and
`Iterable` interfaces. This means that an instance of Pullable
can also be iterated over like this:
```
Pullable p = ...;
for (Object o : p) {
  // Do something
}
```
Note however that if <code>p</code> refers to a processor producing an
infinite number of events, this loop will never terminate by itself.

For the same processor, mixing calls to soft and hard methods is discouraged.
As a matter of fact, the Pullable's behaviour in such a situation is
left undefined.


 

#### Pump

Processor that repeatedly pulls its input, and pushes the resulting
events to its output. The Pump is a way to bridge an upstream part of a
processor chain that works in <em>pull</em> mode, to a downstream part
that operates in <em>push</em> mode.

Graphically, this processor is represented as:

![Pump](/doc-files/tmf/Pump.png)

The repeated pulling of events from its input is started by calling this
processor's `#start()` method. In the background, this will
instantiate a new thread, which will endlessly call <tt>pull()</tt> on
whatever input is connected to the pump, and then call <tt>push()</tt>
on whatever input is connected to it.

The opposite of the Pump is the {@link ca.uqac.lif.cep.tmf.Tank Tank}.


 

#### Pushable

Gives events to some of a processor's input. Interface `Pushable`
is the opposite of `Pullable`: rather than querying events form
a processor's output (i.e. "pulling"), it gives events to a processor's
input. This has for effect of triggering the processor's computation
and "pushing" results (if any) to the processor's output.

If a processor is of input arity *n*, there exist *n* distinct
`Pullable`s: one for each input trace.


 

#### QueueSink

Sink that accumulates events into queues.


 

#### QueueSource

Source whose input is a queue of objects. One gives the
<code>QueueSource</code> a list of events, and that source sends
these events as its input one by one. When reaching the end of
the list, the source returns to the beginning and keeps feeding
events from the list endlessly. This behaviour can be changed
with `#loop(boolean)`.


 

#### ReadLines

Source that reads text lines from a Java `InputStream`.


 

#### ReadStringStream

Extracts character strings from a Java `InputStream`.


 

#### SingleProcessor

Performs a computation on input events to produce output events.

This is the direct descendant of `Processor`, and probably the one
you'll want to inherit from when creating your own processors. While
`Processor` takes care of input and output queues,
`SingleProcessor` also implements `Pullable`s and
`Pushable`s. These take care of collecting input events, waiting
until one new event is received from all input traces before triggering
the computation, pulling and buffering events from all outputs when
either of the `Pullable`s is being called, etc.

The only thing that is left undefined is what to do
when new input events have been received from all input traces. This
is the task of abstract method {@link #compute(Object[], Queue)}, which descendants
of this class must implement.



 

#### StreamVariable

Symbol standing for the <i>i</i>-th trace given as input. A
`StreamVariable` can be given as an argument to a `FunctionTree`.


 

#### Tank

Accumulates pushed events into a queue until they are pulled.
The Tank is a way to bridge an upstream part of a
processor chain that works in <em>push</em> mode, to a downstream part
that operates in <em>pull</em> mode.

Graphically, this processor is represented as:

![Tank](/doc-files/tmf/Tank.png)

The opposite of the tank is the {@link ca.uqac.lif.tmf.Pump Pump}.


 

#### TimeDecimate

After returning an input event, discards all others for the next
*n* seconds. This processor therefore acts as a rate limiter.

Note that this processor uses <code>System.currentTimeMillis()</code>
as its clock.
Moreover, a mode can be specified in order to output the last input
event of the trace if it has not been output already.


 

#### Trim

Discards the first *n* events of the input, and outputs
the remaining ones.


 

#### TurnInto

Processor that turns any event into a predefined object.


 

#### UnaryFunction

Function of one input and one output.



 

#### Window

Simulates the application of a "sliding window" to a trace.

-The processor takes as arguments another processor &phi; and a
 window width *n*
- It returns the result of &phi; after processing events 0 to
  <i>n</i>-1...
- Then the result of (a new instance of &phi;) that processes
  events 1 to <i>n</i>-1...
- ...and so on


 

