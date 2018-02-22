#! /bin/bash
php code-processing.php
java -jar gitbook-pandoc.jar -s markdown -d latex -p chapters
pushd latex
pdflatex book
popd
