Extend ESQL
===========

You know that by creating your own processor, you can pipe it to any other existing processor, provided that its input and output events are of compatible types. It is also possible to extend the grammar of the ESQL language, so that you can also use your processor directly in ESQL queries. All this in less than 10 lines of code.

As an example, let us consider the following processor, which repeats every input event n times, where n is a parameter decided when the processor is instantiated. Its implementation is as follows:

```java
import ca.uqac.lif.cep.*;

public class Repeater extends SingleProcessor {

  private final int numReps;

  public Repeater(int n) {
    super(1, 1);
    this.numReps = n;
  }

  public Queue<Object[]> compute(Object[] inputs) {
    Queue<Object[]> queue = new LinkedList<Object[]>();
    for (int i = 0; i < this.numReps; i++) {
      queue.add(inputs);
    }
    return queue;
  }
}
```

## Step 1: Defining a new grammar rule

We would like to be able to use this processor in ESQL queries. The first step is to decide what syntax one shall use to invoke the processor. In order to work, the processor requires two things: the output of another processor, and the number of times it should repeat each event. Therefore, an intuitive syntax for the processor could be:

```
REPEAT (some processor) n TIMES
```

In this syntax, some processor refers to any other ESQL expression that builds a processor, and n is a number. The result of this expression is itself another object of type processor.

If you are familiar with EBNF grammars, what follows should be easy. We must first tell the ESQL interpreter to add to its grammar a new case for the parsing of the &lt;processor&gt; rule. This rule should correspond to the parsing of our new, Repeater processor. This is done as follows:

```java
Interpreter my_int = new Interpreter();
my_int.addCaseToRule("&lt;processor&gt;", "&lt;repeater&gt;");
```

At this point, the interpreter knows that &lt;processor&gt; can be parsed as a &lt;repeater&gt;, but it has no idea what this case corresponds to. We must tell the interpreter what is the parsing pattern for &lt;repeater&gt;, using method addRule():

```java
my_int.addRule("<repeater>", "REPEAT ( <processor> ) <number> TIMES");
```

The first argument is the name of the new rule we with to add (i.e. the left-hand side of the BNF rule), and the second argument is the right-hand side, or parsing pattern, corresponding to that rule. This parsing pattern is made of:

- The literal REPEAT
- An opening parenthesis
- The &lt;processor&gt; rule name. This means that what follows the parenthesis should follow the syntax of the &lt;processor&gt; rule.
- A closing parenthesis
- The <number> rule name. This means that what follows the parenthesis should follow the syntax of the <number> rule (which corresponds to any character string with the format of a number).
- The literal TIMES

That's it. From now on, the interpreter will correctly parse an expression involving that syntax, and know that it represents an element of type &lt;processor&gt;. This means that such an expression can be written anywhere a &lt;processor&gt; is accepted; for example in the following:

```
SELECT a FROM (
  REPEAT (THE TUPLES OF FILE "foo.csv") 3 TIMES
)
```

## <a name="build">Step 2: build a processor form an expression</a>

The previous step allows the interpreter to know that REPEAT xxx n TIMES corresponds to a processor, but it doesn't know what processor it actually is, or how to instantiate it from the expression. We must therefore tell the processor what object class corresponds to the parsing of such an expression. This is done through the addAssociation() method.

<pre><code>my_int.addAssociation("&lt;repeater&gt;", "Repeater");
</code>
</pre>

The method tells the interpreter that encountering the &lt;repeater&gt; rule will result in the instantiation of a Java object of the class Repeater. This second argument should be the fully qualified name of the class. That is, if Repeater is located in package my.package, then one should write my.package.Repeater in the call to addAssociation().

Upon parsing the &lt;repeater&gt; rule, the interpreter will look for a method called build() in the corresponding class. We must therefore provide such a method in Repeater. Its signature should be the following (it it's different, or absent, the interpreter will complain):

<pre><code>public static void build(Stack&lt;Object&gt; stack) {

}
</code>
</pre>

The task of the `build()` method is to consume elements of the stack to build a new instance of the object to create, and to put that new object back on the stack so that other objects can consume it during their own construction. The contents of the stack correspond to the grammar rule we gave the interpreter in the very beginning. In our case, this means that the stack contains, from top to bottom:

- A String object containing the word "TIMES"
- A Number object corresponding to the actual value of n that was written in the expression
- A String object containing a closing paremthesis
- An object of class `ca.uqac.lif.cep.Processor`, which is the processor resulting from the parsing of the inner expression
- A String object containing an opening paremthesis
- A String object containing the word "REPEAT"

Creating a new instance of Repeater is therefore straightforward. One simply has to `pop()` the stack to fetch the value of n and the Processor object to use as input, and discard all "useless" keywords (it is important to remove them from the stack, though, otherwise other objects accessing the stack afterwards will be confused by what they find there). One can then instantiate a new Repeater, pipe the input into it (using `Connector.connect()`), and put the resulting object on the stack.

<pre><code>public static void build(Stack&lt;Object&gt; stack) {
  stack.pop(); // TIMES
  Number n = (Number) stack.pop();
  stack.pop(); // )
  Processor p = (Processor) stack.pop();
  stack.pop(); // (
  stack.pop(); // REPEAT
  Repeater r = new Repeater(n.intValue());
  Connector.connect(p, r);
  stack.push(r);
}
</code>
</pre>

All done! As you can see, adding a new processor to the ESQL grammar took us 3 lines of code to extend the grammar, and another 9 lines for building it from the parse stack. (Try doing that with another software!)

## <a name="extension">Creating a grammar extension</a>

If you write a lot of extensions to the syntax (great! please tell us), it might be a good idea to "package" these extensions so that you don't have to manually put them into the interpreter every time. BeepBeep provides a class that allows you to do this easily, called `GrammarExtension`. The boilerplate code for an extension is this:

<pre><code>public class MyGrammar extends GrammarExtension {

  public MyGrammar() {
    super(MyGrammar.class);
  }
}
</code>
</pre>

As a matter of fact, you have nothing else to write. However, two text files should be present in the same folder as your extension class:

- The first, called `eml.bnf`, should contain all the new BNF rules that should be added to the interpreter
- The second, called `associations.txt`, should contain the list of all associations between grammar rules and objects (what you pass to the interpreter's `addAssociation()` method)

For our grammar extension, here's what would be the contents of `eml.bnf`:

<pre><code>&lt;processor&gt; := &lt;repeater&gt; ;
&lt;repeater&gt;  := REPEAT ( &lt;processor&gt; ) &lt;number&gt; TIMES ;
</code>
</pre>

and the contents of associations.txt:

<pre><code>&lt;repeater&gt;,my.package.Repeater
</code>
</pre>

From then on, you can add this extension to the interpreter by calling:

<pre><code>my_int.addExtension(MyExtension.class);
</code>
</pre>

<!-- :wrap=soft: -->
---
slug: extend-esql
section-slug: doc
lang: en
...
