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

## Defining a grammar

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

## Using the parse tree

From a grammar defined as above, we can create an instance of an object called a `BnfParser`. For example, suppose that the grammar for arithmetical expressions is contained in a text file called `arithmetic.bnf`. Obtaining a parser for that object can be done as follows:

{@snipm dsl/ParserExample.java}{/}

Once a grammar has been loaded into an instance of `BnfParser`, we are ready to read character strings through its `parse()` method. This is what is done the last instruction above: the string `3+(4-5)` is passed to `parse`, and the method returns an object of type `ParseNode`. This object corresponds to the root of a structure called a **parse tree**. The tree gives the structure of the parsed expression, and specifies how it can be derived from the start symbol using the rules in the grammar. The parse tree for the expression `3+(4-5)` looks like this:

{@img doc-files/dsl/tree.png}{The parse tree for the expression `3+(4-5)`.}{.6}

The leaves of this tree are literals; all the other nodes correspond to non-terminal symbols. Intuitively, a node represents the application of a rule, and the children of that node are the symbols in the specific case of the rule that was applied. For example, the root of the tree corresponds to the start symbol `<exp>`; this symbol is transformed into `<add>` by applying the first case of rule 1. The symbol `add`, in turn, is transformed into the expression `<num> + ( <exp> )` by applying the second case of rule 2 --and so on.

As we can see, the process of parsing transforms an arbitrary character string into a structured tree. Using this tree to construct an object is much easier than trying to process a character string directly: one simply needs to traverse the parse tree, and to build the parts of the object piece by piece. This is done using an object called the  `ParseTreeObjectBuilder`.

To illustrate the principle, consider this simple grammar to represent arithmetic expressions in "forward" Polish notation, such as this:

    <exp> := <add> | <sbt> | <num>;
    <add> := + <exp> <exp>;
    <sbt> := - <exp> <exp>;
    <num> := ^[0-9]+;

Using such a grammar, the expression `3+(4-5)` is written as `+ 3 - 4 5`. We would like to be able to create a `FunctionTree` object from expressions following this syntax.

The `ParseTreeObjectBuilder` makes such a task simple. It performs a *postfix* traversal of a parse tree and maintains in its memory a stack of arbitrary objects. When visiting a parse node that corresponds to a non-terminal token, such as `<foo>`, it looks for a method that handles this symbol. This is done by adding an annotation `@Builds` to the method, as follows:

``` java
@Builds(rule="<foo>")
public void myMethod(Stack<Object> stack) { ...
```

The object builder calls this method, and passes it the current contents of the object stack. It is up to this method to pop and push objects from that stack, in order to recursively create the desired object at the end. For example, in the grammar above, the code to handle token `add` would look like:

``` java
@Builds(rule="<add>")
public void handleAdd(Stack<Object> stack) {
  ArithExp e2 = (ArithExp) stack.pop();
  ArithExp e1 = (ArithExp) stack.pop();
  stack.pop(); // To remove the "+" symbol
  stack.push(new Add(e1, e2));
}
```

Since the builder traverses the tree in a postfix fashion, when a parse node for `add` is visited, the object stack should already contain the `ArithExp` objects created from its two operands. As a rule, each method should pop from the stack as many objects as there are tokens in the corresponding case in the grammar. For example, the rule for `add`; has three tokens, and so the method handling `add` pops three objects.

As one can see, it is possible to create object builders that read expressions in just a few lines of code. This can be even further simplified using the `pop` and `clean` parameters. Instead of popping objects manually, and pushing a new object back onto the stack, one can use the `pop` parameter to ask for the object builder to already pop the appropriate number of objects from the stack. The method for `add` would then become:

``` java
@Builds(rule="<add>", pop=true, clean=true)
public ArithExp handleAdd(Object ... parts) {
  return new Add((ArithExp) parts[0], (ArithExp) parts[1]);
}
```

Notice how this time, the method's arguments is an array of objects; in that case, the array has three elements, corresponding to the three tokens of the `add`; rule. The first is the "+" symbol, and the other two are the
`ArithExp` objects created from the two sub-expressions. Similarly, instead of pushing an object to the stack, the method simply returns it; the object builder takes care of pushing it. By not accessing the contents of the stack directly, it is harder to make mistakes.

As a further refinement, the `clean` option can remove from the arguments all the objects that match terminal symbols in the corresponding rule. Consider a grammar for infix arithmetical expressions, where parentheses are optional around single numbers. This grammar would look like:

    <exp> := <add> ...
    <add> := <num> + <num> | ( <exp> ) + <num> | <num> + ( <exp> ) ...

This time, the rules for each operator must take into account whether any of their operands is a number or a compound expression. The code handling
`add`; would be more complex, as one would have to carefully pop an
element, check if it is a parenthesis, and if so, take care of popping the
matching parenthesis later on, etc. However, one can see that each case of
the rule has exactly two non-terminal tokens, and that both are `ArithExp`. Using the `clean` option in conjunction with `pop`, the code for handling `add`; becomes identical as before:

    @Builds(rule="<add>", pop=true, clean=true)
    public ArithExp handleAdd(Object ... parts) {
      return new Add((ArithExp) parts[0], (ArithExp) parts[1]);
    }

The array indices become 0 and 1, since only the two `ArithExp` objects remain as the arguments.
<!-- :wrap=soft: -->