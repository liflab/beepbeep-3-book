GitBook repository for "the BeepBeep Book"
==========================================

This is the GitHub repository that contains the sources to the book [Event Stream Processing with BeepBeep 3](https://liflab.gitbook.io/event-stream-processing-with-beepbeep-3/). It can be used to update the online contents on the GitBook site, and also to generate a nice-looking PDF copy, compiled with LaTeX.

Requirements
------------

- Java 1.6 or later
- PHP 5 or later
- [Pandoc](http://pandoc.org/) installed and callable with `pandoc` from the command line
- A local copy of the the [BeepBeep example repository](https://github.com/liflab/beepbeep-3-examples) located in a folder called `examples`, under the same parent directory as this repository

Repository structure
--------------------

The repository has three folders:

- `pre-markdown`: this is the folder to edit. Contains Markdown files organized according to the book's table of contents. The file `SUMMARY.md` contains the table of contents, and the file `README.md` contains chapter 1.
- `markdown`: the folder GitBook uses to update its website. Its contents are auto-generated from the `pre-markdown` folder, so **don't edit it manually**.
- `latex`: the folder used to compile a PDF book with LaTeX. Its contents are auto-generated from the `markdown` folder, so **don't edit it manually** (except for a few files, see below).

Markdown sources (the `pre-markdown` folder)
--------------------------------------------

- Separate chapters into folders. The main file for each chapter *must* be called `README.md`.
- Try to use a single file per chapter. Otherwise, the contents on GitBook are broken into as many pages as there are files in the chapter, instead of having a single flowing chapter.

### Figure references

Figures should be inserted **not** by using Markdown's syntax, but rather like this:

    {@img path/to/QueueSourceUsage.png}{A first example}{.6}

The path for the image is either:

- The book's root folder, OR;
- If the path starts with `doc-files`, the `Source/src` folder **in the `examples` repository**. Therefore, use `doc-files` to refer to images from the examples repo.
 
The text between the second pair of braces is the figure's caption; the last argument is the scaling factor (currently, this number is ignored, but it must still be there).

### Image files

Put images either in the `doc-files` folder of the `examples` repo, or in the same folder as the chapter that refers to it in the `pre-markdown` folder.

All processor images are generated in Inkscape, and exported as PNGs at **64 dpi**.

### Code excerpts

Excerpts from [BeepBeep's example repository](https://github.com/liflab/beepbeep-3-examples) can be auto-inserted using this syntax:

    {@snipm basic/QueueSourceUsage.java}{/}

- The path is relative to the folder `Source/src` of the `examples` repository.
- The second argument is the delimiter to look for in the source file. If "X" is the delimiter, when reading the source, a script will look for the comment line `//X`, and insert there whatever is contained between the first and second such comment lines. (This way, you can fetch more than one part of the same source file by using different delimiters.)
- These lines are found by a regex, so if you use special regex characters (such as `*`), make sure to escape them with `\`.

### Index entries

Index entries have no effect on GitBook; they only have an impact on the LaTeX (PDF) version. You insert an entry like this:

    lorem ipsum <!--\index{Doubler@\texttt{Doubler}} \texttt{Doubler}-->`Doubler`<!--/i--> bla

In LaTeX, the text that is kept is what is inside the first HTML comment. In GitBook, HTML comments are interpreted as HTML (i.e. they don't show up), so what remains is the single `Doubler` word in this case.

Generating the book
-------------------

Run `crunch.sh` to generate the contents of the `markdown` and `latex` folders.

<!-- :wrap=soft: -->