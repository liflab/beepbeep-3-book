<?php

// The directory where the sources files are
$input_directory = "pre-markdown";

// The directory where the destination files are to be written
$output_directory = "markdown";

// The location of the local copy of BeepBeep's example repository. This
// path is relative the the location of the script.
$source_location = "../examples/Source/src/";

$code_examples = 0;
$code_lines = 0;
list($code_examples, $code_lines) = stats_code();
list($num_figures, $num_exercises) = stats_exercises_and_figures();

echo <<<EOD
- **$code_examples** different code examples, for a total of $code_lines lines of Java
- **$num_figures** colour illustrations
- **$num_exercises** exercises across all chapters

EOD;

// Iterate recursively over files in the source folder
//fill_stats("markdown", ".md");
//fill_stats("latex", "tex"); // No dot!

/*function fill_stats($folder, $extension)
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
}*/

function stats_code()
{
	global $input_directory, $source_location;
	$file_list = array();
	$it = new RecursiveDirectoryIterator($input_directory);
	foreach (new RecursiveIteratorIterator($it) as $file)
	{
		if (substr($file, strlen($file) - 3, 3) !== ".md")
		{
			continue;
		}
		$original_contents = file_get_contents($file);
		preg_match_all("/\\{@snip. (.*?)\\}/ms", $original_contents, $matches);
		foreach ($matches[1] as $filename)
		{
			if (!in_array($filename, $file_list))
			{
				$file_list[] = $filename;
			}
		}
	}
	$argument = implode(" ", $file_list);
	$return_val = array();
	exec("./call-cloc.sh $argument", $return_val);
	foreach ($return_val as $line)
	{
		if (substr($line, 0, 4) === "Java")
		{
			$parts = explode(" ", $line);
			$num_lines = $parts[count($parts) - 1];
			break;
		}
	}
	return array(count($file_list), $num_lines);
}

function stats_exercises_and_figures()
{
	global $input_directory;
	$file_list = array();
	$it = new RecursiveDirectoryIterator($input_directory);
	$num_ex = 0;
	$num_fig = 0;
	foreach (new RecursiveIteratorIterator($it) as $file)
	{
		if (substr($file, strlen($file) - 3, 3) !== ".md")
		{
			continue;
		}
		// Count pictures, except in glossary
		$in_dict = (strpos($file, "dictionary") !== false);
		$original_contents = file_get_contents($file);
		$in_ex = false;
		foreach (explode("\n", $original_contents) as $line)
		{
			if (!$in_dict && preg_match("/^\\{@img/", $line))
				$num_fig++;
			if ($in_ex && preg_match("/^\\d+\\. /", $line))
				$num_ex++;
			if (!$in_ex && preg_match("/^## Exercises/", $line))
				$in_ex = true;
		}
	}
	return array($num_fig, $num_ex);
}

?>