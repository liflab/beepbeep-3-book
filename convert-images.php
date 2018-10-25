<?php
/**
* Processes each Markdown file in the `pre-markdown` folder (recursively
* into subfolders). Looks for all `img` instructions mentioning a PNG image in
* these files. For each such reference, looks for an SVG file of the same name;
* if so:
* - if a PDF file of same name does not exist in the `latex` folder, converts
*   the SVG into PDF and sends it to the `latex` folder
* - if a PNG file of same name does not exist in the `markdown` folder,
*   converts the SVG into PNG and sends it to the `markdown` folder
* - replaces the "png" extension into "pdf" in the call to `\includegraphics`
*   in the LaTeX file
* 
* (Yes, this means that the md file can refer to a PNG image that does not yet
* exist; the script converts the SVG to PNG.)
* 
* Usage: php convert-images.php
*/

// The directory where the sources files are
$input_directory = "pre-markdown";

// The directory where the Markdown files are to be written
$gitbook_output_directory = "markdown";

// The directory where the LaTeX files are to be written
$output_directory = "latex/chapters";

// The location of the local copy of BeepBeep's example repository. This
// path is relative the the location of the script.
$source_location = "../examples/Source/src/";

// The command to launch inkscape from the CLI. Change this if Inkscape is not
// in your path
$inkscape_command = "inkscape";

// Iterate recursively over files in the source folder
echo "Converting images...\n";
$it = new RecursiveDirectoryIterator($input_directory);
foreach (new RecursiveIteratorIterator($it) as $file)
{
		$file = str_replace("\\", "/", $file);
		$out_filename = $output_directory.substr($file, strlen($input_directory), strlen($file) - strlen($input_directory));
		$gitbook_out_filename = $gitbook_output_directory.substr($file, strlen($input_directory), strlen($file) - strlen($input_directory));
		if (substr($file, strlen($file) - 3, 3) !== ".md")
				continue;
		/*if (!strpos($file, "dictionary"))
		{
				// Just to debug
				continue;
		}*/
		echo " - ".$file."\n";
		$out_filename = str_replace(".md", ".tex", $out_filename);
		$original_contents = file_get_contents($file);
		$new_contents = convert_images($original_contents, dirname($gitbook_out_filename)."/", dirname($out_filename)."/", $out_filename);
		file_put_contents($out_filename, $new_contents);
}

/**
* Converts the images
*/
function convert_images($s, $gitbook_out_dir, $out_dir, $out_filename)
{
		global $source_location, $inkscape_command, $input_directory, $output_directory;
		$latex_contents = file_get_contents($out_filename);
		preg_match_all("/\\{@img (.*?)\\}\\{(.*?)\\}\\{(.*?)\\}/", $s, $matches, PREG_SET_ORDER);
		print_r($matches);
		$path_from_root = substr($out_dir, strlen($output_directory) + 1);
		foreach($matches as $match)
		{
				if (starts_with("doc-files", $match[1]))
				{
						$filename = $source_location.$match[1];
						$basename = get_basename($filename);
				}
				else
				{
						$filename = $input_directory."/".$path_from_root.$match[1];
						$basename = $path_from_root.$match[1];
				}		 
				// A PNG image is mentioned in the Markdown file
				$svg_filename = str_replace(".png", ".svg", $filename);
				
				// Is there an SVG file with the same name?
				echo "Looking for $svg_filename...\n";
				if (!file_exists($svg_filename))
				{
						// No: copy the PNG to both `markdown` and `latex` folders
						echo "not found\n";
						if (starts_with("doc-files", $match[1]))
						{
								$out_pdf_filename = $out_dir.$basename;
								$out_png_filename = $gitbook_out_dir.$basename;
						}
						else
						{
								$out_pdf_filename = $out_dir.$match[1];
								$out_png_filename = $gitbook_out_dir.$match[1];
						}
						if (!file_exists($out_pdf_filename))
						{
								echo "    Copying $filename to ".$out_pdf_filename."\n";
								copy($filename, $out_pdf_filename);
						}
						if (!file_exists($out_png_filename))
						{
								echo "    Copying $filename to ".$out_png_filename."\n";
								copy($filename, $out_png_filename);
						}
						$latex_contents = preg_replace("/\\\\includegraphics\\{(.*?)".str_replace("/", "\\/", preg_quote($basename))."\\}/", "\\scalebox{".$match[3]."}{\\includegraphics{\\1".$basename."}}", $latex_contents);
				}
				else
				{
						// Yes: convert SVG to PDF and put in the `latex` folder
						echo "found\n";
						if (starts_with("doc-files", $match[1]))
						{
								$out_pdf_filename = $out_dir.str_replace(".png", ".pdf", $basename);
								$out_png_filename = $gitbook_out_dir.$basename;
						}
						else
						{
								$out_pdf_filename = $out_dir.str_replace(".png", ".pdf", $match[1]);
								$out_png_filename = $gitbook_out_dir.$match[1];
						}
						$svg_filemtime = filemtime($svg_filename);
						if (!file_exists($out_pdf_filename) || filemtime($out_pdf_filename) < $svg_filemtime)
						{
								$command = $inkscape_command." -z --file=$svg_filename --export-pdf=$out_pdf_filename\n";
								echo "    Converting $svg_filename to $out_pdf_filename\n";
								exec($command);
						}
						echo "Replacing...\n";
						$latex_contents = preg_replace("/\\\\includegraphics\\{(.*?)".str_replace("/", "\\/", preg_quote($basename))."\\}/", "\\scalebox{".$match[3]."}{\\includegraphics{\\1".str_replace(".png", ".pdf", $basename)."}}", $latex_contents);
						// Convert SVG to PNG and put in the `markdown` folder
						if (!file_exists($out_png_filename) || filemtime($out_png_filename) < $svg_filemtime)
						{
								$command = $inkscape_command." -z --file=$svg_filename --export-dpi 64 --export-png=$out_png_filename\n";
								echo "    Converting $svg_filename to $out_png_filename\n";
								exec($command);
						}
				}
		}
		return $latex_contents;
}

/**
* Gets the basename of a file. This is everything following the last "/".
* @param $filename The filename
* @return The basename
*/
function get_basename($filename)
{
		$parts = explode("/", $filename);
		return $parts[count($parts)-1];
}

/**
* Checks if a string starts with a pattern.
* @param $pat The pattern to look for
* @param $s The string to check
* @return true if $s starts with $pat, false otherwise
*/
function starts_with($pat, $s)
{
		if (strlen($pat) > strlen($s))
		{
				return false;
		}
		return substr($s, 0, strlen($pat)) === $pat;
}

// :tabSize=2:
?>