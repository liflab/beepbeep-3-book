Design a query language
=======================

In this chapter, we shall explore a unique feature of BeepBeep, which is the possibility to create custom **query languages**. Rather than instantiate and pipe processors directly through Java code, a query language allows a user to create processor chains by writing expressions using a custom syntax, effectively enabling the creation of <!--\index{domain-specific language} \textbf{domain-specific languages}-->**domain-specific Languages**<!--/i--> (DSLs).

## The Turing tarpit of a single language {#single}

As we already mentioned at the very beginning of this book, many other event stream processing engines provide the user with their own query language. In most of these systems, the syntax for these languages is borrowed from SQL, and many stream processing operations can be accomplished by writing statements such as `SELECT`. In the field of runtime verification, the majority of tools rather use variants of languages closer to mathematical logic or finite-state machines.

The main problem with all these systems is that they force you to use them through their query language exclusively. Contrary to BeepBeep, you seldom have a direct access to the underlying objects that perform the computations. Most importantly, as each of these systems aim to be versatile and applicable to a wide variety of problems, their query language becomes extremely complex: every possible operation on streams has to be written as an expression of the single query language they provide. A typical symptom of this, in some CEP systems, is the presence  of tentacular `SELECT` statements with a dozen optional clauses attempting to cover every possible case. Runtime verification tools fare no better on this respect, and complex nested logical expressions of multiple lines regularly show up in research papers about them. In all cases, the legibility of the resulting expressions suffers a lot. Although there is almost always a way to twist a problem so that it can fit inside any system's language *in theory*, in practice many such expressions are often plain unusable. This can arguably fall into the category of what computer scientist Alan Perlis has described as a "Turing tarpit":

> Beware of the Turing tar-pit in which everything is possible
> but nothing of interest is easy.

In contrast, BeepBeep was designed based on the observation that no single language could accommodate every conceivable problem on streams --at least in a simple and intuitive way. Rather that try to design a "one-size-fits-all" language, and falling victim to the same problem as other systems, BeepBeep provides no built-in query language at all. Rather it offers users the possibility to easily create their own query languages, using the syntax they wish, and including only the features they need.

The basic process of creating a DSL is as follows:

1. We first decide what expressions of the language will look like by defining what is called a *grammar*
2. We then devise a mechanism to build objects (typically `Function` and `Processor` objects) from expressions of the language

## Defining a grammar {#grammar}

A special palette called `dsl` allows the user to design query languages for various purposes. Under the hood, `dsl` uses <!--\index{Bullwinkle parser} Bullwinkle-->Bullwinkle<!--/i-->, a parser for languages that operates through recursive descent with backtracking. Typical [parser generators](http://en.wikipedia.org/wiki/Parser_generator) such as ANTLR, <!--\index{Yacc} Yacc-->Yacc<!--/i--> or <!--\index{Bison (parser)} Bison-->Bison<!--/i--> take a <!--\index{grammar} grammar-->grammar<!--/i--> as input and produce code for a parser specific to that grammar, which must then be compiled to be used. On the contrary, Bullwinkle reads the definition of a grammar at *runtime* and can parse strings on the spot.

The first step in creating a language is therefore to define its **grammar**, i.e. the concrete rules that define how valid expressions can be created. This can be done by parsing a character string (taken from a file or created directly) that contains the grammar declaration. Here is a very simple example of such a declaration:

    <exp> := <add> | <sbt> | <num> ;
    <add> := <num> + <num> ;
    <sbt> := <num> - <num> ;
    <num> := 0 | 1 | 2 ;

The definition of the grammar must follow a well-known notation called [Backus-Naur Form](http://en.wikipedia.org/wiki/Backus-Naur_form) (<!--\index{Backus-Naur Form (BNF)}BNF-->BNF<!--/i-->)). In this notation, the grammar is defined as a series of **rules** (one rule per line). The part of the rule at the left of the `:=` character contains exactly one **non-terminal symbol**. The right-hand side of the rule contains one or more **cases**, separated by the pipe (`|`) character. Each case is a sequence made of literals (character strings to be interpreted literally) and non-terminal symbols. The first non-terminal appearing in the grammar has a special meaning, and is called the **start symbol**.

Taken together, the rules define a set of expressions called *valid* expressions. In the above example, our grammar defines a simple subset of arithmetical expressions, involving only addition, subtraction, and three numbers. An expression is valid if there exists a way to begin at the start symbol, and successively apply rules from the grammar to ultimately produce that expression.

According to the grammar above, the expression `1 + 0` is valid, since it is possible to begin at the start symbol `<exp>` and apply rules to obtain the expression:

1. We transform `<exp>` into `<add>` according to the first case of rule 1.
2. We transform `<add>` into `<num> + <num>` according to the (only) case of rule 2.
3. We transform the first `<num>` into `1` according to the second case of rule 4; our expression becomes `1 + <num>`.
3. We transform the second `<num>` into `0` according to the first case of rule 4; our expression becomes `1 + 0`.

On the contrary, the expression `1 + 0 - 2` is not valid, as there is no possible way to apply the rules in the grammar to transform `<exp>` into that expression.

To define a grammar from a set of BNF rules, a few conventions must be followed. First, non-terminal symbols are enclosed in `<` and `>` and their names must not contain spaces. As we have seen, rules are defined with `:=` and cases are separated by the pipe character. A rule may span multiple lines (any whitespace character after the first one is ignored, as in e.g. HTML) and must end by a semicolon.

In our previous example, the grammar can accommodate only the numbers 0 to 2. Since Bullwinkle only accepts the terminal symbols that are explicitly written into the grammar, we would need to write as many cases for `<num>` as there are integers, which is not very practical. Fortunately, terminal symbols can also be defined through <!--\index{regular expression} \emph{regular expressions}-->*regular expressions*<!--/i-->. A regular expression (regex for short) describes a pattern of characters. Regex terminals are identified with the `^` (hat) character. For example, to indicate that any string of one or more digits is accepted, we could rewrite the rule for `<num>` as follows: 

    <num> := ^[0-9]+;

The expression `[0-9]+` is called a regex pattern; here, it designates any string of numbers. Explaining regular expressions is beyond the scope of this chapter. The reader is referred to the very large documentation on the topic available in books and online.

A BNF grammar can also be *recursive*; that is, a rule `<A>` can contain a case that involves the non-terminal `<B>`, which itself can have a case that refers to `<A>`. We can rewrite our original grammar in a slightly more complex way, such that nested operations are allowed:

    <exp> := <add> | <sbt> | <num> ;
    <add> := ( <exp> ) + ( <exp> ) ;
    <sbt> := ( <exp> ) - ( <exp> ) ;
    <num> := ^[0-9]+;

Note how the operands for `<add>` and `<sbt>` involve the non-terminal `<exp>`. Using such a grammar, an expression like `(3)+((4)-(5))` is valid. However, according to the rules, the use of parentheses is mandatory, even around single numbers. This can be relaxed by adding further cases to `<add>` and `<sbt>`, which become:

    <add> := <num> + <num> | <num> + ( <exp> ) | ( <exp> ) + <num> | ( <exp> ) + ( <exp> );
    <sbt> := <num> - <num> | <num> - ( <exp> ) | ( <exp> ) - <num> | ( <exp> ) - ( <exp> );

In this new grammar, it is now possible to write a more natural expression such as `3+(4-5)`.

The Bullwinkle parser offers many more features, which we shall not discuss here. For example, it accepts a second way of defining a grammar by assembling rules and creating instances of objects programmatically; we refer the reader to the online documentation for more detals. A final remark regarding grammars is that they must belong to a special family called [LL(k)](http://en.wikipedia.org/wiki/LL_parser). Roughly, this means that they must not contain a production rules of the form `<S> := <S> something`. Trying to parse such a rule by recursive descent (the algorithm used by Bullwinkle) causes an infinite recursion (which will throw a `ParseException` when the maximum recursion depth is reached).

From a grammar defined as above, we can create an instance of an object called a `BnfParser`. For example, suppose that the grammar for arithmetical expressions is contained in a text file called `arithmetic.bnf`. Obtaining a parser for that object can be done as follows:

{@snipm dsl/ParserExample.java}{/}

Once a grammar has been loaded into an instance of `BnfParser`, we are ready to read character strings through its `parse()` method. This is what is done the last instruction above: the string `3+(4-5)` is passed to `parse`, and the method returns an object of type `ParseNode`. This object corresponds to the root of a structure called a <!--\index{parse tree} \textbf{parse tree}-->**parse tree**<!--/i-->. The tree gives the structure of the parsed expression, and specifies how it can be derived from the start symbol using the rules defined by the grammar. The parse tree for the expression `3+(4-5)` looks like this:

{@img doc-files/dsl/tree.png}{The parse tree for the expression `3+(4-5)`.}{.6}

The leaves of this tree are literals; all the other nodes correspond to non-terminal symbols. Intuitively, a node represents the application of a rule, and the children of that node are the symbols in the specific case of the rule that was applied. For example, the root of the tree corresponds to the start symbol `<exp>`; this symbol is transformed into `<add>` by applying the first case of rule 1. The symbol `add`, in turn, is transformed into the expression `<num> + ( <exp> )` by applying the second case of rule 2 --and so on.

## Building objects from the parse tree {#objectbuilder}

As we can see, the process of parsing transforms an arbitrary character string into a structured tree. Using this tree to construct an object is much easier than trying to process a character string directly: one simply needs to traverse the parse tree, and to build the parts of the object piece by piece. This is done using an object called the  <!--\index{GrammarObjectBuilder@\texttt{GrammarObjectBuilder}} \texttt{GrammarObjectBuilder}-->`GrammarObjectBuilder`<!--/i-->.

To illustrate the principle, consider this simple grammar to represent arithmetic expressions in <!--\index{Polish notation} Polish notation-->Polish notation<!--/i-->, such as this:

    <exp> := <add> | <sbt> | <num>;
    <add> := + <exp> <exp>;
    <sbt> := - <exp> <exp>;
    <num> := ^[0-9]+;

Using such a grammar, the expression `3+(4-5)` is written as `+ 3 - 4 5`. We would like to be able to create a `FunctionTree` object from expressions following this syntax.

The first step is to create a new empty class that extends `GrammarObjectBuilder`. The constructor of this class should call a method called `setGrammar()`, and pass a string containing the BNF grammar corresponding to the language.

{@snipm dsl/ArithmeticBuilder.java}{/}

The `GrammarObjectBuilder` class defines a method called `build()`, which takes as input a character string. It first parses that string, and then performs a *postfix* traversal of the resulting parse tree, maintaining  in its memory a stack of arbitrary objects along the way. A postfix traversal means that the nodes of the tree are visited one by one; furthermore, before a parent node is visited, all its children are visited first. Hence, in the tree shown above, the first node to be visited will be the leftmost number `3`, followed by its parent `<num>`, and so on.

The `GrammarObjectBuilder` treats any terminal symbol as a character string. Therefore, when visiting a leaf of the parse tree, `GrammarObjectBuilder` puts on its stack a `String` object whose value is the contents of that specific literal. When visiting a parse node that corresponds to a non-terminal token, such as `<add>`, the builder looks for a method that handles this symbol. "Handling" a symbol generally means popping objects from the stack, creating one or more new objects, and pushing these objects back onto the stack. Therefore, to build a `FunctionTree` from an expression, our `ArithmeticBuilder` class must define methods that take care of each non-terminal symbol in the grammar we defined.

Let us start with the simplest case, that of the `<num>` symbol. When a `<num>` node is visited in the parse tree, as per the postfix traversal we described earlier, we know that the top of the stack contains a string with the number that was parsed. The task of our method is to take this string, convert it into a Java `Number` object, and then create a BeepBeep `Constant` object from this number. Therefore, we can create a method called `handleNum` that goes as follows:

{@snipm dsl/ArithmeticBuilder.java}{\*}

As you can see, this method receives as an argument the current contents of the object stack maintained by the `GrammarObjectBuilder` object. It is up to each method to pop and push objects from the stack, in order to recursively create the desired object at the end. This process can also be illustrated graphically, as in the following picture.

{@img doc-files/dsl/Rule-num.png}{A graphical representation of the stack manipulations for rule `<num>`.}{.4}

To the left-hand side of the schema, a box represents the top of the object stack when the method is called. Here, we expect the stack to contain a String object with a numerical value *n*. The right-hand side of the stack represent the content of the object stack after the method returns. Here, we can see that the String at top of the stack has been popped, and replaced by a `Constant` object with the value *n*. The stack may contain other objects below, but they are not relevant to the application of this method. For the sake of clarity, the grammar rule and case corresponding to this operation are often written next to the schema.

What remains to be done is to signal to the object builder that this method should be called whenever a `<num>` tree node is visited. This can be done by adding an <!--\index{annotation} annotation-->annotation<!--/i--> <!--\index{Builds@\texttt{\@Builds}} \texttt{pop}-->`@Builds`<!--/i--> to the method, which reads as follows:

``` java
@Builds(rule="<num>")
```

You should place this annotation just above the first line that declares the method signature. The operation of this method can also be illustrated graphically as in the following figure.

Let us now have a look at the code to handle token `add`.

{@snipm dsl/ArithmeticBuilder.java}{%}

Since the builder traverses the tree in a postfix fashion, when a parse node for `add` is visited, the object stack should already contain the `Function` objects created from its two operands. This is illustrated by the following schema:

{@img doc-files/dsl/Rule-add.png}{A graphical representation of the stack manipulations for rule `<add>`.}{.4}

As a rule, each method should pop from the stack as many objects as there are tokens in the corresponding case in the grammar. For example, the rule for `add` has three tokens, and so the method handling `<add>` pops three objects. In particular, the third line of the method pops and immediately discards an object from the stack, which corresponds to the "+" string that is present in the rule for `<add>`. Notice how, since we are operating on a stack, objects are popped in the reverse order that they appear in the corresponding rule in the grammar.

For the sake of completion, let us write a method that handles the rule for the `<sbt>` non-terminal symbol:

{@snipm dsl/ArithmeticBuilder.java}{!}

{@img doc-files/dsl/Rule-sbt.png}{A graphical representation of the stack manipulations for rule `<sbt>`.}{.4}

We are now ready to use the object builder we just created. Parsing an expression and using the resulting `Function` object can be done in a few lines, as the code below illustrates.

{@snipm dsl/ArithmeticBuilder.java}{&}

The first instruction creates a new instance of our `ArithmeticBuilder`. The second calls the `build` method on the string `+ 3 - 4 5`. Since we parameterized `ArithmeticBuilder` with the type `Function`, the return value of `build`, `f`, is correctly cast as a `Function` object. The remaining lines simply prepare a call to `evaluate` on `f` and print its return value. Our function contains no `StreamVariables`, hence it takes no argument as its input. The end result, printed at the console, is indeed the value of `3+(4-5)`:

    2.0

As a matter of fact, we have just written a simple <!--\index{calculator} calculator-->calculator<!--/i--> that can read strings in Polish notation and compute their value. This was done using BeepBeep's `Function` objects, a simple grammar and a custom-built `GrammarObjectBuilder`. This has required, so far, only 4 lines of text for the grammar, and about 20 lines of code for the interpreter. Just for fun, we can even turn our program into an interactive command line tool, as follows:

{@snipm dsl/Calculator.java}{/}

This program simply reads expressions at the console, parses and evaluates them, and prints their result until the user writes `q`:

    ? + 2 3
    5.0
    ? - 5 + 4 4
    -3.0
    ? q


## Simpler stack manipulations {#stack}

As one can see, it is possible to create builders that read expressions and create new objects with very little effort. However, the manipulation of the stack in each method remains a delicate operation. Popping one object too much, or one too many, may put the stack in an inconsistent state and have disastrous cascading effects on the build process. As a simple example, suppose we modify method `handleAdd` as follows:

{@snipm dsl/ArithmeticBuilderIncorrect.java}{%}

We simply swapped the last two calls to `pop`, meaning we now discard the second object on the stack, and try to cast the first and third as `Function` objects. Trying to run this modified program will produce a screenful of exceptions:

```
Exception in thread "main" 
	at ca.uqac.lif.bullwinkle.ParseTreeObjectBuilder.build(ParseTreeObjectBuilder.java:92)
	at ca.uqac.lif.cep.dsl.GrammarObjectBuilder.build(GrammarObjectBuilder.java:64)
	at dsl.ArithmeticBuilderIncorrect.main(ArithmeticBuilderIncorrect.java:44)
Caused by: 
	at ca.uqac.lif.bullwinkle.ParseTreeObjectBuilder.visit(ParseTreeObjectBuilder.java:161)
	at ca.uqac.lif.bullwinkle.ParseNode.postfixAccept(ParseNode.java:176)
	...
```

As a result, one has to be very careful when interacting with the object stack. However, it turns out that in many cases, a user does not need to manipulate this stack directly. Looking back at the `ArithmeticBuilder` we wrote earlier, we can realize that every method actually does the same thing:

- It pops as many objects from the stack as there are tokens in the corresponding grammar rule, in reverse from the order they appear in the rule.
- It instantiates a new object, using elements that were popped from the stack
- It puts that new object back onto the stack

It is possible to instruct the object builder to automate this repetitive process, using an additional argument to the `@Builds` annotation called <!--\index{pop@\texttt{pop} (annotation)} \texttt{pop}-->`pop`<!--/i-->. For example, the annotation for the `<num>` symbol would now read:

``` java
@Builds(rule="<num>", pop=true)
```

The use of `pop` also changes the signature of our handler method, which becomes:

{@snipm dsl/ArithmeticBuilderPop.java}{\*}

First, one should notice that the method no longer receives a stack as an argument, but rather an array of objects called `parts`. The use of `pop` instructs the builder to already pop the appropriate number of objects from the stack, based on the number of tokens in the corresponding rule of the grammar. Here, the rule for `<num>` has a single token, which is a string of digits. Therefore, the array `parts` will contain a single `String` object at index 0.

The second observation is that the method now returns something. The return value should correspond to the object that should be put back onto the stack at the end of the operation. In the case of `<num>`, the return value is a `Constant` object created by extracting a `Float` from the string received from `parts`. Notice how the original 4-line method has been simplified to a single instruction. Moreover, we no longer have to manually pop and push objects onto the stack: the object builder takes care of this outside of our handler method. This reduces the amount of work required, but also the possibility of making mistakes.

Similarly, a handler method for `<add>` would look like this:

{@snipm dsl/ArithmeticBuilderPop.java}{%}

The rule for `<add>` has three tokens. Based on that rule, the contents of `parts` will be made of three objects: the first is the "+" string, and the other two are the `Function` objects that are the operands of the addition. We know that, by the time this method is called, these two functions have already been created by the previous building steps and placed on the stack. Notice how, again, the five lines of the original method have been replaced by a single instruction.

Let us now consider a more complex grammar, this time defining arithmetic operations using the more natural *infix* notation.

    <exp> := <add> | <sbt> | <num> ;
    <add> := <num> + <num> | <num> + ( <exp> ) | ( <exp> ) + <num> | ( <exp> ) + ( <exp> );
    <sbt> := <num> - <num> | <num> - ( <exp> ) | ( <exp> ) - <num> | ( <exp> ) - ( <exp> );
    <num> := ^[0-9]+

This time, the rules for each operator must take into account whether any of their operands is a number or a compound expression. Writing an object builder for this grammar is slightly more complex. The handler methods for `<add>` and `<sbt>` now have multiple cases; these cases do not have the same number of operands, and the position of the `<exp>` operands among the tokens for each case is not always the same. Therefore, one would have to carefully pop an element, check if it is a parenthesis, and if so, take care of popping the matching parenthesis later on, and so on. This is perfectly possible, but a little tedious:

``` java
public ArithExp handleAdd(Object ... parts)
{
	Function left, right;
	int index ;
	if (parts[0] instanceof String)	{
		left = (Function) parts[1];
		index = 4;
	}
	else {
		left = (Function) parts[0];
		index = 2;
	}
	if (parts[index] instanceof String)
		right = (Function) parts[index + 1];
	else
		right = (Function) parts[index];
	return new FunctionTree(Addition.instance, left, right);
}
```

Notice how we must first check if the first object in `parts` is a string (corresponding to an opening parenthesis); if so, the first operand is located at index 1, otherwise it is at index 0. This, in turn, shifts the index of the second operand, which may or may not be surrounded by parentheses. The case where both operands are between parentheses could be illustrated as follows:

{@img doc-files/dsl/Rule-add-infix.png}{A graphical representation of the stack manipulations for rule `<add>` in infix notation.}{.4}

However, one can see that each case of the rule has exactly two non-terminal tokens, and that both are `FunctionTrees`. As a further refinement to the object builder, the <!--\index{clean@\texttt{clean} (annotation)} \texttt{clean}-->`clean`<!--/i--> annotation can remove from the arguments all the objects that match terminal symbols in the corresponding rule. Using the `clean` option in conjunction with `pop`, the code for handling `add`; becomes identical as before:

``` java
@Builds(rule="<add>", pop=true, clean=true)
public FunctionTree handleAdd(Object ... parts) {
  return new FunctionTree(Addition.instance,
	(Function) parts[0], (Function) parts[1]);
}
```

The array indices become 0 and 1, since only the two `FunctionTree` objects remain as the arguments. This results in the picture below. Notice how the non-terminal symbols `<exp>` in the rule are underlined, to emphasize the fact that they are the only symbols to be represented on the object stack at the right; the interspersed terminal tokens between these symbols are not shown.

{@img doc-files/dsl/Rule-add-infix-clean.png}{A graphical representation of the stack manipulations for rule `<add>` in infix notation, using the `clean` option.}{.4}



## Building processor chains {#procchains}

Hello.


<!-- :wrap=soft: -->