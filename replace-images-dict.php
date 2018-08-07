<?php

$input_directory = "latex/"; // trailing slash!
$source_location = "../core/Core/src/";
$it = new RecursiveDirectoryIterator($input_directory."chapters/dictionary");
foreach (new RecursiveIteratorIterator($it) as $file)
{
  $file = str_replace("\\", "/", $file);
  if (substr($file, strlen($file) - 4, 4) !== ".tex")
    continue;
  $original_contents = file_get_contents($file);
  $new_contents = replace_images($original_contents);
  $new_contents = format_latex($new_contents);
  file_put_contents($file, $new_contents);
}

function format_latex($s)
{
  $s = preg_replace("/\\\\begin\\{figure\\}.*?(\\\\includegraphics.*?)\\\\caption.*?\\\\end\\{figure\\}/ms", "{\\centering\n\\1\n}", $s);
  $s = preg_replace("/(\\\\paragraph.*?)\\n\\n/ms", "\\1 ", $s);
  $s = preg_replace("/\\\\chapter\\{(.*?)\\}\\n\n/ms", "\\chapter{\\1}\n\n\\begin{multicols}{2}\n\n", $s);
  $s .= "\n\n\\end{multicols}";
  return $s;
}

function replace_images($s)
{
  global $input_directory;
  preg_match_all("/\\includegraphics(.*?)\\{(.*?)\\}/", $s, $matches, PREG_SET_ORDER);
  foreach ($matches as $match)
  {
    $img_filename = $match[2];
    if (strpos($img_filename, "dictionary") === false)
      continue;
    $pdf_filename = str_replace(".png", ".pdf", $match[2]);
    echo $pdf_filename."\n";
    if (!file_exists($input_directory.$pdf_filename))
      continue;
    echo "Replacing with PDF\n";
    $s = str_replace("\\includegraphics".$match[1]."{".$match[2]."}", "\\includegraphics".$match[1]."{".$pdf_filename."}", $s);
  }
  return $s;
}
?>