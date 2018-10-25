<?php

/**
 * Performs a few post-processing operations on the LaTeX files before
 * compiling them
 */

// Convert foreword into an unnumbered chapter
$s = file_get_contents("latex/chapters/foreword/README.tex");
$s = str_replace("\\chapter", "\\chapter*", $s);
$s = str_replace("\\section", "\\section*", $s);
$s = str_replace("LaTeX", "\\LaTeX{}", $s);
$s = str_replace("Sylvain Hallé", "\n\\vskip 20pt\n{\\raggedleft\nSylvain Hallé", $s);
$s = str_replace("/2018", "/2018\\\\\n}\n", $s);
// Change footer to "Foreword"
$s = "\\setcounter{tocdepth}{0}\n".$s;
$s = "\\pagestyle{foreword}\n\\thispagestyle{foreword}\n".$s;
file_put_contents("latex/chapters/foreword/README.tex", $s);

// Re-change footer to normal in next chapter
$s = file_get_contents("latex/chapters/README.tex");
$s = "\\setcounter{tocdepth}{1}\n".$s;
$s = "\\pagestyle{normal}\n\\thispagestyle{normal}\n".$s;
file_put_contents("latex/chapters/README.tex", $s);

// Touch-up to dictionary
$s = file_get_contents("latex/chapters/dictionary/README.tex");
$s = str_replace("information.", "information.\n\n\\begin{center}\\rule{0.5\\linewidth}{\\linethickness}\\end{center}\n\n\\begin{multicols}{2}\\small", $s);
$s .= "\n\\end{multicols}\n";
$s = preg_replace("/(\\\\paragraph.*?)\\n\\n/ms", "\\1 ", $s);
$s = preg_replace("/\\@palette (.*?)@/ms", "\\bbpalette{\\1}", $s);
$s = preg_replace("/\\\\begin\\{figure\\}.*?(\\\\scalebox.*?\\\\includegraphics[^\\}]*)\\}\\}.*?\\\\caption.*?\\\\end\\{figure\\}/ms", "\\begin{center}\n\\1}}\n\\end{center}", $s);
file_put_contents("latex/chapters/dictionary/README.tex", $s);

// Hack to table in use case section
file_put_contents("latex/chapters/flyby.inc.tex", <<<EOD
\\begin{center}
\\begin{tabular}{lll}
\\hline
\\textbf{Planet} & \\textbf{Date} & \\textbf{Days after 1/1/77} \\\\
\\hline
Jupiter & July 9, 1979 & 918 \\\\
Saturn & August 25, 1981 & 1,696 \\\\
Neptune & August 25, 1989 & 4,618 \\\\
\\hline
\\end{tabular}
\\end{center}
EOD
);
$s = file_get_contents("latex/chapters/use-cases/README.tex");
$s = preg_replace("/\\\\begin\\{longtable\\}.*?\\\\end\\{longtable\\}/ms", "\\input{flyby.inc.tex}", $s);
file_put_contents("latex/chapters/use-cases/README.tex", $s);

// Remove foreword, dictionary and drawing guide from body.tex, as these files are
// manually included by book.tex
$s = file_get_contents("latex/chapters/body.tex");
$s = preg_replace("/^\\\\subimport.*foreword.*$/m", "", $s);
$s = preg_replace("/^\\\\subimport.*dictionary.*$/m", "", $s);
$s = preg_replace("/^\\\\subimport.*drawing.*$/m", "", $s);
$s = preg_replace("/^\\\\subimport.*reading.*$/m", "", $s);
file_put_contents("latex/chapters/body.tex", $s);
?>