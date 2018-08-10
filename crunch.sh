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

# Compile LaTeX
pushd latex
pdflatex -interaction=batchmode book
popd
