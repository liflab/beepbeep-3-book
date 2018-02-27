Advanced features
=================

## File operations {#files}

## Using `stdin` and `stdout` {#std}

## Sets and bags {#sets}

## Lists {#lists}

## Context {#context}

Each processor instance is also associated with a **context**. A context is a persistent and modifiable map that associates names to arbitrary objects. When a processor is duplicated, its context is duplicated as well. If a processor requires the evaluation of a function, the current context of the processor is passed to the function. Hence the function's arguments may contain references to names of context elements, which are replaced with their concrete values before evaluation. Basic processors, such as those described in this section, do not use context. However, some special processors defined in extensions to BeepBeep's core (the Moore machine and the first-order quantifiers, among others) manipulate their {@link jdc:ca.uqac.lif.cep.Context} object.

## Exercises {#ex-advanced}

<!-- :wrap=soft: -->