<?php

// The directory where the destination files are to be written
$output_directory = "markdown";

$code_examples = "50";
$code_lines = "5,225";
$num_figures = 10;
$num_exercises = 20;

// Iterate recursively over files in the source folder
fill_stats("markdown", ".md");
fill_stats("latex", "tex"); // No dot!

function fill_stats($folder, $extension)
{
	global $code_examples, $code_lines, $num_figures, $num_exercises;
	$it = new RecursiveDirectoryIterator($folder);
	foreach (new RecursiveIteratorIterator($it) as $file)
	{
		if (substr($file, strlen($file) - 3, 3) !== $extension)
		{
			continue;
		}
		$original_contents = file_get_contents($file);
		$original_contents = str_replace("@codeexamples", $code_examples, $original_contents);
		$original_contents = str_replace("@codelines", $code_lines, $original_contents);
		$original_contents = str_replace("@figures", $num_figures, $original_contents);
		$original_contents = str_replace("@exercises", $num_exercises, $original_contents);
		file_put_contents($file	 , $original_contents);
	}
}
?>