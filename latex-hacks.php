<?php
$LATEX_FOLDER = "latex/chapters/";

$hacks = array(
  "README.tex" => array(
    array("The end result is a situation similar to the next figure", "The end result is a situation similar to Figure 1.1"),
    array("This is what is illustrated in the next figure.", "This is what is illustrated in Figure 1.2."),
  ),
  "basic/README.tex" => array(
    array("\texttt{doubler}. Graphically, this can be represented as follows:", "\texttt{doubler}. Graphically, this can be represented as in Figure 2.3."),
    array("It can be depicted as follows:", "It can be depicted as in Figure 2.11."),
  ),
  "core/README.tex" => array(
    array("illustrated by the following diagram:", "illustrated by the diagram in Figure 3.8."),
    array("The following example shows how a source of numbers", "The following example (Figure 3.13) shows how a source of numbers"),
    array("this section with. This can be illustrated as follows:", "this section with. This can be illustrated as in Figure 3.18."),
    array("as is shown in the example below:", "as is shown in Figure 3.19."),
    array("as in the following chain:", "as in the chain shown in Figure 3.21."),
    array("This is perfectly possible, as the picture below shows.", "This is perfectly possible, as Figure 3.25 shows."),
    // To "fix" the mysterious bug of issue #31 on GitHub
    array("/\\\\scalebox\\{.6\\}\\{\\\\includegraphics\\{chapters\\/core\\/WindowAverage.pdf\\}\\}/", "\scalebox{.45}{\includegraphics{chapters/core/WindowAverage.pdf}}")
  ),
  "advanced/README.tex" => array(
    array("This can be illustrated as such:", "This is illustrated by Figure 4.1."),
    array("Graphically, this can be illustrated as follows (note", "Graphically, this can be illustrated as in Figure 4.2 (note"),
    array("The sequence of method calls is summarized in the next figure.", "The sequence of method calls is summarized in Figure 4.12"),
    array("The sequence of method calls that occurs is illustrated in the following figure.", "The sequence of method calls that occurs is illustrated in the Figure 4.13"),
  ),
  "palettes/README.tex" => array(
    array("would be the following", "would be like the diagram in Figure 5.5"),
    array("The window should look like this one:", "The window should look like Figure 5.16."),
    array("this chain of processors can be represented as in the following diagram:", "this chain of processors can be represented as in Figure 5.20."),
    array("The processor chain to generate the signal is modified to look as follows:", "The processor chain to generate the signal is modified to look as in Figure 5.23."),
    array("This is shown by the following plot, which applies the", "This is shown by Figure 5.26, which applies the")
  ),
  "use-cases/README.tex" => array(
    array("It can be represented as in the following diagram:", "It can be represented as in Figure 6.3."),
    array(", in the next diagram.", ", in Figure 6.4."),
    array("The previous figure shows the chain", "Figure 6.5 shows the chain"),
    array("Moore machine, shown in the next diagram", "Moore machine, shown in Figure 6.15"),
    array("This corresponds to the following diagram:", "This corresponds to the diagram in Figure 6.16."),
    array("; it can be implemented as in the following diagram:", "; it can be implemented as in the diagram shown in Figure 6.21.")
  ),
  "dsl/README.tex" => array(
    array("This process can also be illustrated graphically, as in the following picture.", "This process can also be illustrated graphically, as in Figure 8.2."),
    array("This whole process can be represented as follows:", "This whole process can be represented as in Figure 8.7."),
    array("processors. Graphically, this can be represented as follows:", "processors. Graphically, this can be represented as in Figure 8.13."),
    array("depending on the size of the list:", "depending on the size of the list (see Figure 8.14)."),
    array("simply builds this whole chain:", "simply builds this whole chain (see Figure 8.15)."),
  )
);

foreach ($hacks as $filename => $replacements)
{
	$full_filename = $LATEX_FOLDER.$filename;
	$s = file_get_contents($full_filename);
	foreach ($replacements as $rep)
	{
		$quote = true;
		$from = $rep[0];
		$to = $rep[1];
		if ($from[0] == "/")
		{
			$quote = false;
		}
		if ($quote)
		{
			$from = "/".preg_quote($from)."/";
		}
		//if (!preg_match($from, $s))
		//{
		//	echo "$filename: no match for $from\n";
		//}
		$s = preg_replace($from, $to, $s);
	}
	file_put_contents($full_filename, $s);
}
?>