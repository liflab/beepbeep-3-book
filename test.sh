#! /bin/bash
php code-processing.php
php generate-dictionary.php
java -jar gitbook-pandoc.jar -s markdown -d latex -p chapters
php replace-images.php
php replace-images-dict.php
pushd latex
pdflatex -interaction=batchmode book
popd
