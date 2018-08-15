<?php

/**
 * Performs a few post-processing operations on the LaTeX files before
 * compiling them
 */

// Convert foreword into an unnumbered chapter
$s = file_get_contents("latex/chapters/foreword/README.tex");
$s = str_replace("\\chapter", "\\chapter*", $s);
file_put_contents("latex/chapters/foreword/README.tex", $s);

// Touch-up to dictionary
$s = file_get_contents("latex/chapters/dictionary/README.tex");
$s = str_replace("information.", "information.\n\n\\begin{center}\\rule{0.5\\linewidth}{\\linethickness}\\end{center}\n\n\\begin{multicols}{2}\\small", $s);
$s .= "\n\\end{multicols}\n";
$s = preg_replace("/(\\\\paragraph.*?)\\n\\n/ms", "\\1 ", $s);
$s = preg_replace("/\\@palette (.*?)@/ms", "\\bbpalette{\\1}", $s);
$s = preg_replace("/\\\\begin\\{figure\\}.*?(\\\\includegraphics[^\\}]*)\\}.*?\\\\caption.*?\\\\end\\{figure\\}/ms", "\\begin{center}\n\\1}\n\\end{center}", $s);
file_put_contents("latex/chapters/dictionary/README.tex", $s);

// Remove dictionary and drawing guide from body.tex, as these two files are
// manually included by book.tex
$s = file_get_contents("latex/chapters/body.tex");
$s = preg_replace("/^\\\\subimport.*dictionary.*$/m", "", $s);
$s = preg_replace("/^\\\\subimport.*drawing.*$/m", "", $s);
file_put_contents("latex/chapters/body.tex", $s);
?>