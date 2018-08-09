GitBook repository for "the BeepBeep Book"
==========================================

This is the GitHub repository that contains the sources to the book [Event Stream Processing with BeepBeep 3](https://liflab.gitbook.io/event-stream-processing-with-beepbeep-3/). It can be used to update the online contents on the GitBook site, and also to generate a nice-looking PDF copy, compiled with LaTeX.

Repository structure
--------------------

The repository has three folders:

- `pre-markdown`: this is the folder to edit. Contains Markdown files organized according to the book's table of contents. The file `SUMMARY.md` contains the table of contents, and the file `README.md` contains chapter 1.
- `markdown`: the folder GitBook uses to update its website. Its contents are auto-generated from the `pre-markdown` folder, so **don't edit it manually** (except for a few files, see below).
- `latex`: the folder used to compile a PDF book with LaTeX. Its contents are auto-generated from the `markdown` folder, so **don't edit it manually** (except for a few files, see below).

Markdown sources (the `pre-markdown` folder)
--------------------------------------------

Try to use a single file per chapter. Otherwise, the contents on GitBook are broken into as many pages as there are files in the chapter, instead of having a single flowing chapter.

### Figure references

Figures should be inserted **not** by using Markdown's syntax, but rather like this:

    {@img basic/QueueSourceUsage.png}{A first example}{.6}

- The path is relative to the book's root folder, not the folder where the Markdown file resides.
- The text between the second pair of braces is the figure's caption
- The last argument is the scaling factor (currently, this number is ignored, but it must still be there)

### Image files

Don't put image files in `pre-markdown`; put them in `markdown` (this is one exception where you can edit the `markdown` folder).

All processor images are generated in Inkscape, and exported as PNGs at **64 dpi**.

### Code excerpts

Excerpts from [BeepBeep's example repository](https://github.com/liflab/beepbeep-3-examples) can be auto-inserted using this syntax:

    {@snipm basic/QueueSourceUsage.java}{/}

- The example repository must be in a local folder called `examples`, in the same parent folder as this repository
- The path is relative to the folder `Source/src` in that repository.
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