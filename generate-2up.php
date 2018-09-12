%% --------------------------------------------------------------------------
%% 
%% --------------------------------------------------------------------------
\documentclass[legalpaper,landscape]{article}
\usepackage{graphicx}
\usepackage[absolute]{textpos}
\usepackage[landscape]{geometry}
\begin{document}
\setlength{\parskip}{0in}
\setlength{\TPHorizModule}{1in}
\setlength{\TPVertModule}{\TPHorizModule}
\textblockorigin{0in}{0in} % start everything near the top-left corner
\thispagestyle{empty}
\pagestyle{empty}
<?php
$TOTAL_PAGES = 328;
for ($i = 1; $i <= $TOTAL_PAGES; $i += 4)
{
	if ($i + 3 <= $TOTAL_PAGES)
	{
		echo "\\begin{textblock}{7}(0,0)\n";
		echo "\\noindent\\includegraphics[page=".($i+3)."]{book-pdfa}\n";
		echo "\\end{textblock}\n";
	}
	if ($i <= $TOTAL_PAGES)
	{
		echo "\\begin{textblock}{7}(7,0)\n";
		echo "\\noindent\\includegraphics[page=".($i)."]{book-pdfa}\n";
		echo "\\end{textblock}\n";
	}
	echo "\\phantom{W}\n";
	echo "\\newpage\n\n";
	if ($i + 1 < $TOTAL_PAGES)
	{
		echo "\\begin{textblock}{7}(0,0)\n";
		echo "\\noindent\\includegraphics[page=".($i+1)."]{book-pdfa}\n";
		echo "\\end{textblock}\n";
	}
	if ($i + 2 < $TOTAL_PAGES)
	{
		echo "\\begin{textblock}{7}(7,0)\n";
		echo "\\noindent\\includegraphics[page=".($i+2)."]{book-pdfa}\n";
		echo "\\end{textblock}\n";
	}
	echo "\\phantom{W}\n";
	echo "\\newpage\n\n";
}
?>
\end{document}