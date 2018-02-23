Fundamental Processors and Functions
====================================

BeepBeep is organized along a modular architecture. The main part of BeepBeep is called the *engine*, which provides the basic classes for creating processors and functions, and contains a handful of general-purpose processors for manipulating traces. The rest of BeepBeep's functionalities is dispersed across a number of *palettes*. In this chapter, we describe the basic processors and functions provided by BeepBeep's engine.

## Apply a function {#applyfunction}

A first way to create a processor is by lifting any *m*:*n* function *f* into a *m*:*n* processor. This is done by applying *f* successively to each front of input events, producing the output events. The processor responsible for this is called a {@link jdc:ca.uqac.lif.cep.functions.FunctionProcessor FunctionProcessor}.

In the following bit of code, a `FunctionProcessor` is created by applying the Boolean negation function to an input trace of Boolean values:

{@snipm Examples/src/queries/SimpleFunction.java}{SNIP}

We have already seen an example of a FunctionProcessor applying the addition function to pairs of inputs.

{@snipm Examples/src/queries/PipingBinary.java}{SNIP}

A function processor is created by applying the "+" (addition) function, represented by an oval, to the left and right inputs, producing the output. Recall that in BeepBeep, functions are first-class objects. Hence the \texttt{Addition} function can be passed as an argument when instantiating the \texttt{FunctionProcessor}. Since this function is 2:1, the resulting processor is also 2:1.

One special case of function processor is worth mentioning. The {@link ca.uqac.lif.cep.tmf.Fork Fork} is a 1:*n* processor that simply copies its input to its *n* outputs. When *n*=1, the fork is also called a *passthrough*.

## Cumulate values {#cumulate}

A variant of the function processor is the {@link ca.uqac.lif.cep.functions.CumulativeProcessor CumulativeProcessor}. Contrarily to the processors above, which are stateless, a cumulative processor is stateful. A `CumulativeProcessor` is given a binary function *f*. Intuitively, if *x* is the previous value returned by the processor, its output on the next event *y* will be *f(x,y)*. The processor requires an initial value *t* to compute its first output.

Depending on the function *f*, cumulative processors can represent many things. In the following code example, *f* is addition and 0 is the start value.

{@snipm Examples/src/queries/CumulativeSum.java}{SNIP}

The processor outputs the cumulative sum of all values received so far:

    The event is: 1
    The event is: 3
    The event is: 6
    The event is: 10
    ...

As another example, if *f* is the [three-valued logical conjunction](https://en.wikipedia.org/wiki/Three-valued_logic#Kleene_and_Priest_logics) and "?" is the start value, then the processor computes the three-valued conjunction of events received so far, and has the same semantics as the LTL3 "Globally" operator.

<!-- :wrap=soft: -->