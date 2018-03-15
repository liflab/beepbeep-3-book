<?php

$beepbeep_folder = "../beepbeep-3/Core/src";

// An array of folder names in which to look for source code
$source_folders = array($beepbeep_folder);

// The folder in which to create the output markdown files
$output_folder = "pre-markdown/dictionary";

// The absolute path to the folder corresponding to {@docRoot}
//$docroot_folder = "dictionary";

$inkscape_command = "inkscape";

// The name of the table of contents in that folder
$toc = "README.md";

// Get the list of all paths
$all_paths = array();
foreach ($source_folders as $root)
{
  $all_paths[] = $root;
  $iter = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST,
    RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
  );
}
$all_paths = array($root);
foreach ($iter as $path => $dir) {
    if ($dir->isDir()) {
        $all_paths[] = $path;
    }
}

// Go in each path and check every file
$entry_names = array();
foreach ($all_paths as $path)
{
  // Copy doc files if any
  $path = str_replace("\\", "/", $path);
  if (file_exists($path."/doc-files"))
  {
    xcopy($path."/doc-files", $output_folder."/doc-files");
  }
  // Parse source files and generate Markdown
  $file_list = array_diff(scandir($path), array(".", ".."));
  foreach ($file_list as $filename)
  {
    if (!preg_match("/\\.java$/", $filename))
      continue;
    generate_doc($path."/".$filename, $entry_names);
  }
}
// Copy auxiliary files
xcopy($beepbeep_folder."/doc-files", $output_folder."/doc-files");
$names = array_keys($entry_names);
sort($names);
$dict_toc = "# A dictionary of BeepBeep objects\n\n";
foreach ($names as $name)
{
  //$dict_toc .= "* [$name](".str_replace("\\", "/", $entry_names[$name]).")\n";
  $dict_toc .= $entry_names[$name]."\n\n";
}
file_put_contents($output_folder."/".$toc, $dict_toc);

function generate_doc($filename, &$entry_names)
{
  global $output_folder, $toc;
  $file_contents = file_get_contents($filename);
  $javadoc = extract_class_javadoc($file_contents);
  if (include_in_doc($filename, $javadoc))
  {
    echo "FILENAME $filename\n";
    format_entry($filename, $javadoc, $entry_names);
  }
}

function get_markdown_path($filename)
{
  preg_match("/ca(.*?)[\\\\\\/]([^\\\\\\/]*)\\.java$/", $filename, $matches);
  return "ca".$matches[1];
}

function include_in_doc($filename, $javadoc)
{
  return strpos($javadoc, "@dictentry") !== false;
}

function extract_class_javadoc($s)
{
  $matches = array();
  if (!preg_match("/\\/\\*\\*(.*?)\\*\\//ms", $s, $matches))
    return "";
  $javadoc = $matches[1];
  $javadoc = preg_replace("/^\\s*\\*\\s/m", "", $javadoc);
  return $javadoc;
}

function format_entry($filename, $javadoc, &$entry_names)
{
  global $output_folder, $docroot_folder;
  preg_match("/[\\\\\\/]([^\\\\\\/]*)\\.java$/", $filename, $matches);
  $class_name = $matches[1];
  $out_folder = get_markdown_path($filename);
  $package_name = "ca".preg_replace("/[\\\\\\/]/", ".", $matches[1]);
  $markdown_filename = $out_folder."/".$class_name.".md";
  // Strip all metadata
  $javadoc = preg_replace("/^\\s*@.*$/m", "", $javadoc);
  // Change {@link} tags into plain text
  $javadoc = preg_replace("/\\{@link ([^\\s]*)\\}/m", "`$1`", $javadoc);
  // Replace {@docRoot} with actual path
  $javadoc = str_replace("{@docRoot}", $docroot_folder, $javadoc);
  // Replace images
  $javadoc = resolve_images($javadoc);
  $out_text = "#### $class_name\n\n";
  $out_text .= $javadoc;
  if (!file_exists($output_folder."/".$out_folder))
  {
    mkdir($output_folder."/".$out_folder, 0777, true);
  }
  //file_put_contents($output_folder."/".$markdown_filename, $out_text);
  $entry_names[$class_name] = $out_text;
}

function resolve_images($s)
{
  global $beepbeep_folder, $inkscape_command, $output_folder;
  preg_match_all("/\\[.*?\\]\\((.*?)\\)/", $s, $matches);
  foreach ($matches[1] as $match)
  {
    $png_filename = $beepbeep_folder.$match;
    $svg_filename = str_replace(".png", ".svg", $png_filename);
    $out_pdf_filename = $output_folder.str_replace(".png", ".pdf", $match);
    if (file_exists($svg_filename))
    {
      $command = $inkscape_command." -z --file=$svg_filename --export-pdf=$out_pdf_filename\n";
      exec($command);
    }
  }
  return $s;
}

/**
 * Copy a file, or recursively copy a folder and its contents
 * @author      Aidan Lister <aidan@php.net>
 * @version     1.0.1
 * @link        http://aidanlister.com/2004/04/recursively-copying-directories-in-php/
 * @param       string   $source    Source path
 * @param       string   $dest      Destination path
 * @param       int      $permissions New folder creation permissions
 * @return      bool     Returns true on success, false on failure
 */
function xcopy($source, $dest, $permissions = 0755)
{
    // Check for symlinks
    if (is_link($source)) {
        return symlink(readlink($source), $dest);
    }

    // Simple copy for a file
    if (is_file($source)) {
        return copy($source, $dest);
    }

    // Make destination directory
    if (!is_dir($dest)) {
        mkdir($dest, $permissions);
    }

    // Loop through the folder
    $dir = dir($source);
    while (false !== $entry = $dir->read()) {
        // Skip pointers
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        // Deep copy directories
        xcopy("$source/$entry", "$dest/$entry", $permissions);
    }

    // Clean up
    $dir->close();
    return true;
}
?>
