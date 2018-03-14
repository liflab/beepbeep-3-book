<?php

$input_directory = "pre-markdown";
$output_directory = "latex/chapters";
$source_location = "../beepbeep-3-examples/Source/src/";
$inkscape_command = "inkscape";

$it = new RecursiveDirectoryIterator($input_directory);
foreach (new RecursiveIteratorIterator($it) as $file)
{
  $file = str_replace("\\", "/", $file);
  $out_filename = $output_directory.substr($file, strlen($input_directory), strlen($file) - strlen($input_directory));
  if (substr($file, strlen($file) - 3, 3) !== ".md")
    continue;
  $out_filename = str_replace(".md", ".tex", $out_filename);
  $original_contents = file_get_contents($file);
  $new_contents = replace_images($original_contents, dirname($out_filename)."/", $out_filename);
  file_put_contents($out_filename, $new_contents);
}

function replace_images($s, $out_dir, $out_filename)
{
  global $source_location, $inkscape_command;
  $latex_contents = file_get_contents($out_filename);
  preg_match_all("/\\{@img (.*?)\\}\\{(.*?)\\}\\{(.*?)\\}/", $s, $matches, PREG_SET_ORDER);
  foreach($matches as $match)
  {
    $filename = $source_location.$match[1];
    $svg_filename = str_replace(".png", ".svg", $filename);
    if (strpos($svg_filename, "dictionary") !== false)
      continue; // Don't care about these files
    if (!file_exists($svg_filename))
      continue;
    $basename = get_basename($filename);
    $out_pdf_filename = $out_dir.str_replace(".png", ".pdf", $basename);
    if (!file_exists($out_pdf_filename))
    {
      $command = $inkscape_command." -z --file=$svg_filename --export-pdf=$out_pdf_filename\n";
      echo "Converting $svg_filename to $out_pdf_filename\n";
      exec($command);
    }
    $latex_contents = preg_replace("/\\\\includegraphics\\{(.*?)".preg_quote($basename)."\\}/", "\\scalebox{".$match[3]."}{\\includegraphics{\\1".str_replace(".png", ".pdf", $basename)."}}", $latex_contents);
  }
  return $latex_contents;
}

function get_basename($filename)
{
	$parts = explode("/", $filename);
	return $parts[count($parts)-1];
}
?>