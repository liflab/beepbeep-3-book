<?php

$input_directory = "pre-markdown";
$gitbook_output_directory = "markdown/chapters";
$output_directory = "latex/chapters";
$source_location = "../examples/Source/src/";
$inkscape_command = "inkscape";

$it = new RecursiveDirectoryIterator($input_directory);
foreach (new RecursiveIteratorIterator($it) as $file)
{
  $file = str_replace("\\", "/", $file);
  $out_filename = $output_directory.substr($file, strlen($input_directory), strlen($file) - strlen($input_directory));
  $gitbook_out_filename = $gitbook_output_directory.substr($file, strlen($input_directory), strlen($file) - strlen($input_directory));
  if (substr($file, strlen($file) - 3, 3) !== ".md")
    continue;
  $out_filename = str_replace(".md", ".tex", $out_filename);
  $original_contents = file_get_contents($file);
  $new_contents = replace_images($original_contents, dirname($gitbook_out_filename)."/", dirname($out_filename)."/", $out_filename);
  file_put_contents($out_filename, $new_contents);
}

function replace_images($s, $gitbook_out_dir, $out_dir, $out_filename)
{
  global $source_location, $inkscape_command;
  $latex_contents = file_get_contents($out_filename);
  preg_match_all("/\\{@img (.*?)\\}\\{(.*?)\\}\\{(.*?)\\}/", $s, $matches, PREG_SET_ORDER);
  foreach($matches as $match)
  {
    $filename = $source_location.$match[1];
    // A PNG image is mentioned in the Markdown file
    $svg_filename = str_replace(".png", ".svg", $filename);
    // Is there an SVG file with the same name?
    if (!file_exists($svg_filename))
      continue;
    $basename = get_basename($filename);
    // Convert SVG to PDF and put in the `latex` folder
    $out_pdf_filename = $out_dir.str_replace(".png", ".pdf", $basename);
    if (!file_exists($out_pdf_filename))
    {
      $command = $inkscape_command." -z --file=$svg_filename --export-pdf=$out_pdf_filename\n";
      echo "Converting $svg_filename to $out_pdf_filename\n";
      exec($command);
    }
    $latex_contents = preg_replace("/\\\\includegraphics\\{(.*?)".preg_quote($basename)."\\}/", "\\scalebox{".$match[3]."}{\\includegraphics{\\1".str_replace(".png", ".pdf", $basename)."}}", $latex_contents);
    // Convert SVG to PNG and put in the `markdown` folder
    $out_png_filename = $basename;
    if (!file_exists($out_png_filename))
    {
      $command = $inkscape_command." -z --file=$svg_filename --export-dpi 64 --export-png=$out_png_filename\n";
      echo "Converting $svg_filename to $out_png_filename\n";
      exec($command);
    }
  }
  return $latex_contents;
}

function get_basename($filename)
{
	$parts = explode("/", $filename);
	return $parts[count($parts)-1];
}
?>