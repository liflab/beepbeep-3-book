#! /bin/bash

# --------------------------------------------------------------------
# Complete loop for generating both the GitBook version and the LaTeX
# version of the book.
#
# Usage: crunch.sh [--incremental]
# --------------------------------------------------------------------

# Process pre-markdown and write to markdown
php pre-processing.php $1

# Convert Markdown into LaTeX
java -jar gitbook-pandoc.jar -s markdown -d latex -p chapters $1

# Convert SVG images into both PNG (for GitBook) and PDF (for LaTeX)
php convert-images.php

# Copy "Palette" images (these are not handled automatically)
cp pre-markdown/dictionary/Palette*.png markdown/dictionary/
cp pre-markdown/dictionary/Palette*.pdf latex/chapters/dictionary/

# Fill statistics
php fill-stats.php

# Perform a few hacks on LaTeX output before compiling
php post-processing-latex.php

# Perform a few hacks on Markdown output before compiling
# This is done after LaTeX on purpose; keep it there
php post-processing-markdown.php

# Compile LaTeX. The script itself will exit with the same return code
# as the call to LaTeX (used to discover if compilation failed).
pushd latex
pdflatex -interaction=batchmode book
ret_code=$?
popd
exit $ret_code