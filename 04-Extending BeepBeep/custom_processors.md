Create your own processor
=========================

[Home](index.html) &gt; [Extending BeepBeep](extend.html)

What if none of the processors suits your needs? If you use Java, BeepBeep allows you to create your own, which you can then compose with existing processors. You can do so using **no more than 4 lines of boilerplate code**. (If you're willing to write about 10 more lines, you can also [integrate your processor into the ESQL language](/extend.html).)

The simplest way to do so is to extend the SingleProcessor class, which takes care of most of the "plumbing" related to event management: connecting inputs and outputs, looking after event queues, etc. All you have left to do is to:

- Define how many input traces your processor needs, and how many output traces it produces. These two values are called the input and output arity the processor, respectively.
- Write the actual computation that should occur, i.e. what output event(s) to produce (if any), given an input event.

The minimal working example for a custom processor is made of six lines of code:

<pre><code>
import ca.uqac.lif.cep.*;

public class MyProcessor extends SingleProcessor {

  public MyProcessor() {
	super(0, 0);
  }

  public Queue&lt;Object[]&gt; compute(Object[] inputs) {
	return null;
  }
}
</code></pre>

This results in a processor that accepts no inputs, and produces no output. To make things more interesting, we will study a couple of examples.

## <a name="example1">Example 1: string length</a>

As a first example, let us write a processor that receives character strings as its input events, and that computes the length of each string. The input arity of this processor is therefore 1 (it receives one string at a time), and its output arity is 1 (it outputs a number). Specifying the input and output arity is done through the call to super() in the processor's constructor: the first argument is the input arity, and the second argument is the output arity.

The actual functionality of our processor will be written in the body of method compute(). This method is called whenever an input event is available, and a new output event is required. Its argument is an array of Java Objects; the size of that array is that of the input arity we declared for this processor (in our case: 1). Computing the length amounts to extracting the first (and only) event of array inputs, casting it to a String, and getting its length. The end result is this:

<pre><code>
import ca.uqac.lif.cep.*;

public class StringLength extends SingleProcessor {

  public StringLength() {
	super(1, 1);
  }

  public Queue&lt;Object[]&gt; compute(Object[] inputs) {
	int length = ((String) inputs[0]).length();
	return Processor.wrapObject(length);
  }
}
</code></pre>

Note that the compute() method must return a queue of arrays of objects. If the processor is of arity n, it must put an event into each of its n output traces. It may also decide to output more than one such n-uplet for a single input event, and these events are accumulated into a queue --hence the slightly odd return type. However, if our processor outputs a single element, the tedious process of creating an array of size 1, putting the elemetn in the array, creating a queue, putting the array into the queue and returning the queue is encapsulated in the convenience method Processor.wrapObject(), which does exactly that.

That's it. From then on, you can instantiate StringLength, connect it to the output of any other processor that produces strings, and pipe its result to the input of any other processor that accepts numbers.

## <a name="example2">Example 2: Euclidean distance</a>

This second example will show an example of a processor that takes as input two traces. The events of each trace are instances of the user-defined class Point:

<pre><code>
public class Point {
  public float x;
  public float y;
}
</code></pre>

We will write a processor that takes one event (i.e. one Point) from each input trace, and return the Euclidean distance between these two points.

<pre><code>
import ca.uqac.lif.cep.*;

public class EuclideanDistance extends SingleProcessor {

  public StringLength() {
	super(2, 1);
  }

  public Queue&lt;Object[]&gt; compute(Object[] inputs) {
	Point p1 = (Point) inputs[0];
	Point p2 = (Point) inputs[1];
	float distance = Math.sqrt(Math.pow(p2.x - p1.x, 2) + Math.pow(p2.y - p1.y, 2));
	return Processor.wrapObject(distance);
  }
}
</code></pre>

## <a name="example3">Example 3: separating a point</a>

This processor takes as input a single trace of Points (see example above), and sends the x and y component of that point as events of two output traces. It is an example of processor with an output arity of 2.

<pre><code>
import ca.uqac.lif.cep.*;

public class SplitPoint extends SingleProcessor {

  public SplitPoint() {
	super(1, 2);
  }

  public Queue&lt;Object[]&gt; compute(Object[] inputs) {
	Point p = (Point) inputs[0];
	float[] output_event = new float[2];
	float[0] = p.x;
	float[1] = p.y;
	return Processor.wrapVector(output_event);
  }
}
</code></pre>

Note that we use wrapVector(), rather than wrapObject(), as the result we are producing is already an array of size 2. Method wrapVector() simply puts that array into a new empty queue. Note also that it is an error to produce an array whose size is not equal to the processor's output arity.

## <a name="example4">Example 4: threshold</a>

So far, all processors we designed return one output event for every input event (or pair of events) they receive. This needs not be the case. The following processor outputs an event if its value is greater than 0, and no event at all otherwise.

<pre><code>
import ca.uqac.lif.cep.*;

public class OutIfPositive extends SingleProcessor {

public OutIfPositive() {
	super(1, 1);
  }

  public Queue&lt;Object[]&gt; compute(Object[] inputs) {
	Number n = (Number) inputs[0];
	if (n.floatValue() &gt; 0)
	  return Processor.wrapObject(inputs[0]);
	else
	  return null;
  }
}
</code></pre>

The way to indicate that a processor does not produce any output for an input is to return null. Note that this should not be confused with the output arity of the processor.

## <a name="example5">Example 5: stuttering</a>

Conversely, a processor does not need to output only one event for each input event. For example, the following processor repeats an input event as many times as its numerical value: if the event is the value 3, it is repeated 3 times in the output.

<pre><code>
import ca.uqac.lif.cep.*;

public class Stuttering extends SingleProcessor {
 
  public Stuttering() {
	super(1, 1);
  }

  public Queue&lt;Object[]&gt; compute(Object[] inputs) {
	Number n = (Number) inputs[0];
	Queue&lt;Object[]&gt; queue = new LinkedList&lt;Object[]&gt;();
	for (int i = 0; i &lt; n.intValue(); i++) {
	  queue.add(inputs);
	}
	return queue;
  }
}
</code></pre>

## <a name="example6">Example 6: a processor with memory</a>

So far, all our processors are memoryless: they keep no information about past events when making their computation. It is also possible to create "memoryful" processors. As an example, let's create a processor that outputs the maximum between the current event and the previous one. That is, given the following input trace:

5, 1, 2, 3, 6, 4, ...

the processor should output:

(nothing), 5, 2, 3, 6, 6, ...

Notice how, after receiving the first event, the processor should not return anything yet, as it needs two events before saying something. Here's a possible implementation:

<pre><code>
import ca.uqac.lif.cep.*;

public class MyMax extends SingleProcessor {

  Number last = null;

  public MyMax() {
    super(1, 1);
  }

  public Queue&lt;Object[]&gt; compute(Object[] inputs) {
    Number current = (Number) inputs[0];
    Number output;
    if (last != null) {
      output = Math.max(last, current);
      last = current;
      return Processor.wrapObject(output);
    }
    else {
      last = current;
      return null;
    }
  }
}
</code>
</pre>

<!-- :wrap=soft: -->
---
slug: custom
...
