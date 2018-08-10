<?php

// Load dictionary file
$s = file_get_contents("latex/chapters/dictionary/README.tex");
$s = str_replace("information.", "information.\n\n\\begin{center}\\rule{0.5\\linewidth}{\\linethickness}\\end{center}\n\n\\begin{multicols}{2}\\small", $s);
$s .= "\n\\end{multicols}\n";
$s = preg_replace("/\\\\begin\\{figure\\}.*?(\\\\includegraphics.*?)\\\\caption.*?\\\\end\\{figure\\}/ms", "{\\begin{center}\n\\1\n\\end{center}}", $s);
$s = preg_replace("/(\\\\paragraph.*?)\\n\\n/ms", "\\1 ", $s);
file_put_contents("latex/chapters/dictionary/README.tex", $s);
?>                                                                   