#! /bin/bash

textidote=~/Workspaces/textidote/textidote.jar
outfile=/tmp/out.txt
java -jar $textidote --clean markdown/foreword/README.md >> $outfile
java -jar $textidote --clean markdown/README.md >> $outfile
java -jar $textidote --clean markdown/basic/README.md >> $outfile
java -jar $textidote --clean markdown/core/README.md >> $outfile
java -jar $textidote --clean markdown/advanced/README.md >> $outfile
java -jar $textidote --clean markdown/palettes/README.md >> $outfile
java -jar $textidote --clean markdown/use-cases/README.md >> $outfile
java -jar $textidote --clean markdown/custom/README.md >> $outfile
java -jar $textidote --clean markdown/dsl/README.md >> $outfile
java -jar $textidote --clean markdown/drawing/README.md >> $outfile
java -jar $textidote --clean markdown/dictionary/README.md >> $outfile
java -jar $textidote --clean markdown/reading/README.md >> $outfile