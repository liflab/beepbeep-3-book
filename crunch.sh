#! /bin/bash
php code-processing.php
java -jar gitbook-pandoc.jar -s markdown -d latex -p chapters
php replace-images.php
pushd latex
pdflatex book
popd
