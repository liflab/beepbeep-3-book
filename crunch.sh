#! /bin/bash
php code-processing.php $1
java -jar gitbook-pandoc.jar -s markdown -d latex -p chapters $1
php replace-images.php
pushd latex
pdflatex -interaction=batchmode book
popd
