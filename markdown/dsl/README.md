Design a query language
=======================

In this chapter, we shall explore a unique feature of BeepBeep, which is the possibility to create custom **query languages**. Rather than instantiate and pipe processors directly through Java code, a query language allows a user to create processor chains by writing expressions using a custom syntax, effectively enabling the creation of <!--\index{domain-specific language} \textbf{domain-specific languages}-->**domain-specific Languages**<!--/i--> (DSLs).

## The Turing tarpit of a single language {#single}

As we already mentioned at the very beginning of this book, many other event stream processing engines provide the user with their own query language. In most of these systems, the syntax for these languages is borrowed from SQL, and many stream processing operations can be accomplished by writing statements such as `SELECT`. In the field of runtime verification, the majority of tools rather use variants of languages closer to mathematical logic or finite-state machines.

The main problem with all these systems is that they force you to use them through their query language exclusively. Contrary to BeepBeep, you seldom have a direct access to the underlying objects that perform the computations. Most importantly, as each of these systems aim to be versatile and applicable to a wide variety of problems, their query language becomes extremely complex: every possible operation on streams has to be written as an expression of the single query language they provide. A typical symptom of this, in some CEP systems, is the presence  of tentacular `SELECT` statements with a dozen optional clauses attempting to cover every possible case. Runtime verification tools fare no better on this respect, and complex nested logical expressions of multiple lines regularly show up in research papers about them. In all cases, the legibility of the resulting expressions suffers a lot. Although there is almost always a way to twist a problem so that it can fit inside any system's language *in theory*, in practice many such expressions are often plain unusable. This can arguably fall into the category of what computer scientist Alan Perlis has described as a "Turing tarpit":

> Beware of the Turing tar-pit in which everything is possible
> but nothing of interest is easy.

In contrast, BeepBeep was designed based on the observation that no single language could accommodate every conceivable problem on streams --at least in a simple and intuitive way. Rather that try to design a "one-size-fits-all" language, and falling victim to the same problem as other systems, BeepBeep provides no built-in query language at all. Rather it offers users the possibility to easily create their own query languages, using the syntax they wish, and including only the features they need.

## Defining a grammar

A special palette called `dsl` allows the user to design query languages for various purposes. Under the hood, `dsl` uses <!--\index{Bullwinkle parser} Bullwinkle-->Bullwinkle<!--/i-->, a parser for languages that operates through recursive descent with backtracking. Typical [parser generators](http://en.wikipedia.org/wiki/Parser_generator) such as ANTLR, <!--\index{YACC} Yacc-->Yacc<!--/i--> or <!--\index{Bison (parser)} Bison-->Bison<!--/i--> take a <!--\index{grammar} grammar-->grammar<!--/i--> as input and produce code for a parser specific to that grammar, which must then be compiled to be used. On the contrary, Bullwinkle reads the definition of a grammar at *runtime* and can parse strings on the spot.

The first step in creating a language is therefore to define its **grammar**, i.e. the concrete rules that define how valid expressions can be created. This can be done in two ways. The first way is by parsing a character string (taken from a file or created directly) that contains the grammar declaration. As a simple example, here is a grammar for defining simple arithmetical expressions:

    <exp> := <add> | <sub> | <mul> | <div> | - <exp> | <num>;
    <add> := <num> + <num> | ( <exp> + <exp> );
    <sub> := <num> - <num> | ( <exp> - <exp> );
    <mul> := <num> × <num> | ( <exp> × <exp> );
    <div> := <num> ÷ <num> | ( <exp> ÷ <exp> );
    <num> := ^[0-9]+;

The definition of the grammar must follow a well-known notation called [Backus-Naur Form](http://en.wikipedia.org/wiki/Backus-Naur_form) (<!--\index{Backus-Naur Form (BNF)} BNF-->BNF<!--/i-->)). In this notation, the grammar is defined as a series of **rules** (one rule per line). The part of the rule at the left of the `:=` character contains exactly one **non-terminal symbol**. The right-hand side of the rule contains one or more **cases**, separated by the pipe (`|`) character. Each case is a sequence made of literals (character strings to be interpreted literally) and non-terminal symbols. The first non-terminal appearing in the grammar has a special meaning, and is called the **start symbol**.

Taken together, the rules define a set of expressions called *valid* expressions. An expression is valid if there exists a way to begin at the start symbol, and successively apply rules from the grammar to ultimately produce that expression. Consider for example this very simple grammar:

    <S> := foo <X> | bar <Y>;
    <X> := 0 | 1 <Y>;
    <Y> := 2 | 3;

According to this grammar, the expression `foo 1 2` is valid, since it is possible to begin at the start symbol `S` and apply rules to obtain the expression:

1. We transform `<S>` into `foo <X>` according to the first case of rule 1.
2. We transform `<X>` into `1 <Y>` according to the second case of rule 2; our expression becomes `foo 1 <Y>`.
3. We transform `<Y>` into `2` according to the first case of rule 3; our expression becomes `foo 1 2`.

On the contrary, the expression `bar 0` is not valid, as there is no possible way to apply the rules in the grammar to transform `<S>` into that expression.

To define a grammar from a set of BNF rules, a few conventions must be followed:

- Non-terminal symbols are enclosed in `<` and `>` and their names must not contain spaces.
- Rules are defined with `:=` and cases are separated by the pipe character.
- A rule can span multiple lines (any whitespace character after the first one is ignored, as in e.g. HTML) and must end by a semicolon.
- Terminal symbols are defined by typing them directly in a rule, or through regular expressions and begin with the `^` (hat) character. The example above   shows both cases: the `+` symbol is typed directly into the rules, while the   terminal symbol `<num>` is defined with a regex. **Look out:**
  - If a space needs to be used in the regular expression, it must be
    declared by using the regex sequence `\s`, and *not* by putting a space.
  - Beware not to put an extra space before the ending semicolon, or that
    space will count as part of the regex
  - Caveat emptor: a few corner cases are not covered at the moment, such as a regex that would contain a semicolon.
- The left-hand side symbol of the first rule found is assumed to be the start symbol. This can be overridden by calling method `setStartSymbol()` on an   instance of the parser.
- Whitespace acts as a token separator, so there is no need to declare terminal   tokens separately. This means that the rule `<num> + <num>` matches any string   with a number, the symbol +, and another number, separated by any number of   spaces, including none. This also means that writing `1+2` defines a *single*   token that matches only the string "1+2". When declaring rules, tokens *must* be separated by a space. Writing `(<exp>)` is illegal and will throw an   exception; one must write `( <exp> )` (note the spaces). However, since   whitespace is ignored when parsing, this rule would still match the string
  "(1+1)".

Some symbols or sequences of symbols, such as `:=`, `|`, `<`, `>` and `;`, have a special meaning and cannot be used directly inside terminal symbols (note that this limitation applies only when parsing a grammar from a text file). However, these symbols can be included by *escaping* them, i.e. replacing them with their UTF-8 hex code.

- `|` can be replaced by `\u007c`
- `<` can be replaced by `\u003c`
- `<` can be replaced by `\u003e`
- `;` can be replaced by `\u003b`
- `:=` can be replaced by `\u003a\u003d`

The characters should appear as is (i.e. unescaped) in the string to parse.

For Bullwinkle to work, the grammar must be [LL(k)](http://en.wikipedia.org/wiki/LL_parser). Roughly, this means that it must not contain a production rules of the form `<S> := <S> something`. Trying to parse such a rule by recursive descent causes an infinite recursion (which will throw a `ParseException` when the maximum recursion depth is reached).

### Building the rules manually

A second way of defining a grammar consists of assembling rules by creating instances of objects programmatically. Roughly:

- A `BnfRule` contains a left-hand side that must be a `NonTerminalToken`, and a right-hand side containing multiple cases that are added through method `addAlternative()`.
- Each case is itself a `TokenString`, formed of multiple `TerminalToken`s and `NonTerminalToken`s which can be `add`ed. Terminal tokens include `NumberTerminalToken`, `StringTerminalToken` and `RegexTerminalToken`.
- `BnfRule`s are `add`ed to an instance of the `BnfParser`.

Once a grammar has been loaded into an instance of `BnfParser`, the `parse()` method is used to parse a given string and produce a parse tree (or null if the string does not parse). This parse tree can then be explored in two ways:

1. In a manner similar to the DOM, by calling the `getChildren()` method of an instance of a `ParseNode` to get the list of its children (and so on, recursively);
2. Through the [Visitor design pattern](http://en.wikipedia.org/wiki/Visitor_pattern). In that case, one creates a class that implements the `ParseNodeVisitor` interface, and passes this visitor to the `ParseNode`'s `acceptPostfix()` or `acceptPrefix()` method, depending on the desired mode of traversal.

Many times, the goal of parsing an expression is to create some "object" out of the resulting parse tree. The `ParseTreeObjectBuilder` class in Bullwinkle simplifies the task of creating such objects.

Suppose for example that you created objects to represent simple arithmetical expressions: there is one class for `Add`, another for `Sub`(traction), another for plain `Num`bers, etc. (See the `Examples` folder in the sources, where such classes are indeed shown in `ArithExp.java`.) You can create and nest such objects programmatically, for example to represent 10+(6-4):

    ArithExp a = new Add(new Num(10), new Sub(new Num(6), new Num(4));

Suppose you created a simple grammar to represent such expressions in "forward" Polish notation, such as this:

    <exp> := <add> | <sub> | <num>;
    <add> := + <exp> <exp>;
    <sub> := - <exp> <exp>;
    <num> := ^[0-9]+;

Using such a grammar, the previous expression would be written as `+ 10 - 6 4`. You would like to be able to instantiate `ArithExp` objects from expressions following this syntax.

The `ParseTreeObjectBuilder` makes such a task simple. It performs a *postfix* traversal of a parse tree and maintains a stack of arbitrary objects. When visiting a parse node that corresponds to a non-terminal token, such as &lt;foo&gt;, it looks for a method that handles this symbol. This is done by adding an annotation `@Builds` to the method, as follows:

``` java
@Builds(rule="<foo>")
public void myMethod(Stack<Object> stack) { ...
```

The object builder calls this method, and passes it the current contents
of the object stack. It is up to this method to pop and push objects
from that stack, in order to recursively create the desired object at the
end. For example, in the grammar above, the code to handle token `add`
would look like:

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