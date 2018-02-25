Extending BeepBeep
==================

BeepBeep was designed from the start to be easily extensible. As was discussed earlier, it consists of only a small core of built-in processors and functions. The rest of its functionalities are implemented through custom processors and grammar extensions, grouped in packages called *palettes*.

This modular organization has three advantages. First, palettes are a flexible and generic way to extend the engine to various application domains, in ways unforeseen by its original designers. Second, they make the engine's core (and each palette individually) relatively small and self-contained, easing the development and debugging process. Finally, it is hoped that BeepBeep's palette architecture, combined with its simple extension mechanisms, will help third-party users contribute to the BeepBeep ecosystem by developing and distributing extensions suited to their own needs.

## <a name="context">Context</a>

Each processor instance is also associated with a **context**. A context is a persistent and modifiable map that associates names to arbitrary objects. When a processor is duplicated, its context is duplicated as well. If a processor requires the evaluation of a function, the current context of the processor is passed to the function. Hence the function's arguments may contain references to names of context elements, which are replaced with their concrete values before evaluation. Basic processors, such as those described in this section, do not use context. However, some special processors defined in extensions to BeepBeep's core (the Moore machine and the first-order quantifiers, among others) manipulate their [jdc:ca.uqac.lif.cep.Context](http://liflab.github.io/beepbeep-3/javadoc/ca/uqac/lif/cep/Context.html) object.