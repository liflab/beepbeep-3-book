Drawing Guide
=============

As you may have realized, in many cases the best way of understading a chain of processors is to represent it graphically.

A straightforward way of showing processor chains would be to depict processors as shapes, perhaps with a text label indicating what they do, and to use straight lines and arrows to illustrate their interlinking. Indeed, this is what is often done to represent composition in other systems, such as this example using Apache Storm Trident:

{@img Trident.png}{The composition of "spouts" and "bolts" in Apache Storm Trident.}{.6}

However, we found early on that BeepBeep processor chains illustrated in such a way are neither particularly intuitive (all processor chains look alike until you start reading what's in the boxes) nor very pleasing to the eye (after all, we can produce color and graphics on computer monitors since at least thirty years). Therefore, we decided to develop a more colorful, intuitive, yet standardized way of drawing chains of processors.

In this appendix, we describe the basic "rules" defining how to draw pipes, processors and functions; these conventions have been followed throughout this book, in all the online documentation, as well as in all presentations about BeepBeep we made at scientific conferences in the past couple of years.

You don't need to be an artist to create your own processor chains. Using a vector drawing program such as [Inkscape](https://inkscape.org) (freely available for all operating systems), it is easy to copy-paste the symbols from this book and include them in other drawings. A PDF document containing multiple pages of predefined symbols can also be obtained from the BeepBeep GitHub repository (look for a file called `Drawing Guide`).

## Pipes

The **head** of a pipe should indicate whether it is an input or an output pipe. This is done with the inward-pointing *red* triangle (for input) and the outward-pointing *green* triangle (for output).

{@img Pipes.png}{Input and output pipes.}{.6}

In longer pipes, only the main body is longer; the head is not stretched.

{@img PipesLong.png}{A longer pipe.}{.6}

The <!--\index{pipe!colour coding} \textbf{colour}-->**colour**<!--/i--> of a pipe should indicate the type of the events it contains. For the sake of consistency, we try to use the same colour for the same type across a diagram. At the very least, frequent event types should have the same colour across a common set of examples. Here are the colours that have been used for frequent event types in this book:

{@img Colors.png}{Colour coding for pipes}{.6}

Pipe segments should be either
vertical or  horizontal. Orientation
changes are done through
rounded right-angle turns.

{@img Corner.png}{A pipe turing at a 90-degree angle.}{.6}

Pipe segments can be joined by vertically or horizontally centering them, overlapping them slightly, and using the *Path/Union* command in Inkscape.

## Processors

Processors are represented by (square) boxes with input/output pipes around them. A symbol in the center of the box represents the processor's specific functionality. The colour of the input/output pipes should match the type of the corresponding input/output stream.

{@img Processor.png}{A generic processor box.}{.6}

For a processor that takes parameters, these parameters should be placed across one of the unused sides of the processor's box.

{@img ProcessorParams.png}{Processors taking parameters.}{.6}

For processors that have as parameter another processor chain or a function tree, a link to that object is drawn with a circle and a line.

{@img ProcessorProc.png}{Processor taking another processor as a parameter.}{.6}

The "cloud" can be replaced by a rectangle for better legibility.

## Functions

Atomic functions are represented by rounded rectangles. When possible, their colour should have a similar shade to that of the input or output type of their arguments.

{@img Functions.png}{Functions.}{.6}

Function *trees* (i.e. composition of multiple atomic functions) are drawn as trees. For functions that need arguments from multiple input streams, the position of the stream is explicitly written in a lozenge. Stream numbers start at 1.

{@img FunctionTree.png}{A function tree.}{.6}

The edges can be collapsed if the resulting drawing is legible enough.

{@img FunctionTreeCollapsed.png}{A collapsed function tree.}{.6}

<!-- :wrap=soft: -->