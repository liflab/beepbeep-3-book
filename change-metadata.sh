#! /bin/bash

# Changes the "Creator" and "Producer" fields in the generated PDF file;
# otherwise, arXiv detects that it has been produced with LaTeX and
# forbids the upload of a PDF file (it wants to compile from the sources).
#
# It requires the libimage-exiftool-perl package under Ubuntu.

exiftool -Creator="foo" -Producer="foo" -overwrite_original latex/book.pdf
