<?php

$input_directory = "pre-markdown";
$output_directory = "markdown";
$javadoc_root = "http://liflab.github.io/beepbeep-3/javadoc/";
$source_location = "../beepbeep-3-examples/Source/src/";
$github_source_location = "https://github.com/liflab/beepbeep-3-examples/blob/master/Source/src/";

$it = new RecursiveDirectoryIterator($input_directory);
foreach (new RecursiveIteratorIterator($it) as $file)
{
	$out_filename = $output_directory.substr($file, strlen($input_directory), strlen($file) - strlen($input_directory));
	if (substr($file, strlen($file) - 1, 1) === ".")
	{
		if (substr($file, strlen($file) - 2, 1) !== ".")
		{
			echo "Created directory $out_filename\n";
			@mkdir(dirname($out_filename), 0755, true);
		}
		continue;
	}
	if (substr($file, strlen($file) - 3, 3) !== ".md")
	{
		copy($file, $out_filename);
		continue;
	}
	$original_contents = file_get_contents($file);
	$out_contents = insert_code_snippets($original_contents);
	$out_contents = resolve_javadoc($out_contents);
	$out_contents = resolve_images($out_contents, dirname($out_filename)."/");
	file_put_contents($out_filename, $out_contents);
}

function insert_code_snippets($s)
{
  $s = resolve_snipm($s);
  $s = resolve_snips($s);
  return $s;
}

function resolve_images($s, $out_folder)
{
	global $source_location;
	preg_match_all("/\\{@img (.*?)\\}\\{(.*?)\\}\\{(.*?)\\}/", $s, $matches, PREG_SET_ORDER);
	foreach($matches as $match)
	{
		$filename = $source_location.$match[1];
		$out_filename = $out_folder.get_basename($filename);
		copy($filename, $out_filename);
		$contents = "![".$match[2]."](".get_basename($out_filename).")";
		$s = str_replace($match[0], $contents, $s);
	}
	return $s;
}

function resolve_snipm($s, $remove_comments = true)
{
  global $source_location, $github_source_location;
  preg_match_all("/\\{@snipm (.*?)\\}\\{(.*?)\\}/", $s, $matches, PREG_SET_ORDER);
  foreach($matches as $match)
  {
    $filename = $source_location.$match[1];
    if (file_exists($filename))
    {
      $snip_content = file_get_contents($filename);
      $snip_matches = array();
      $quoted_match = str_replace("/", "\\/", $match[2]);
      $line_nb = preg_match_line("/\\/\\/\\s*".$quoted_match."/s", $snip_content);
      preg_match("/\\/\\/\\s*".$quoted_match."(.*?)\\/\\/\\s*".$quoted_match."/ms", $snip_content, $snip_matches);
      $code = fix_indentation($snip_matches[1]);
      if ($remove_comments)
      {
      	  $code = remove_comments($code);
      }
      $contents = "``` java\n".$code."```\n";
      $contents .= "[⚓](".$github_source_location.$match[1]."#L".($line_nb + 1).")\n";
      $s = str_replace($match[0], $contents, $s);
    }
    else
    {
      $s = str_replace($match[0], "<pre><code>Source code not found: $filename</code></pre>", $s);
    }
  }
  return $s;
}

/**
 * Removes the comments in a piece of code
 */
function remove_comments($s)
{
	$star_s = preg_replace("/\\/\\*.*?\\*\\//ms", "", $s);
	$lines = explode("\n", $star_s);
	$out = "";
	foreach ($lines as $line)
	{
		$new_line = rtrim(preg_replace("/\\/\\/.*$/", "", $line));
		if (!empty($new_line))
			$out .= $new_line."\n";
	}
	return $out;
}

/**
 * Works like preg_match, but returns the number of the first line of the
 * matched pattern. The pattern to find must not span multiple lines.
 */
function preg_match_line($pattern, $content)
{
  $lines = explode("\n", $content);
  for ($i = 0; $i < count($lines); $i++)
  {
    $line = $lines[$i];
    if (preg_match($pattern, $line))
    {
      return $i;
    }
  }
  return -1;
}

/**
 * Extracts a structured block from the source code. The marker defines
 * the first line of the file to include; further lines will be included
 * until the nesting level of the braces falls from 1 to 0.
 */
function resolve_snips($s)
{
  global $source_location, $github_source_location;
  preg_match_all("/\\{@snips (.*?)\\}\\{(.*?)\\}/", $s, $matches, PREG_SET_ORDER);
  foreach($matches as $match)
  {
    $filename = $source_location.$match[1];
    if (!file_exists($filename))
    {
      $s = str_replace($match[0], "<pre><code>Source code not found</code></pre>", $s);
      return $s;
    }
    $snip_content = file_get_contents($filename);
    list($structured_content, $line_nb) = extract_structured($snip_content, $match[2]);
    $contents = "<pre><code>".fix_indentation($structured_content)."</code></pre>\n";
    $contents .= "<a class=\"code-ref\" href=\"".$github_source_location.$match[1]."#L".($line_nb + 1)."\"><span>[Code on GitHub]</span></a>\n";
    $s = str_replace($match[0], $contents, $s);
  }
  return $s;
}

function extract_structured($file_contents, $marker)
{
  $lines = explode("\n", $file_contents);
  $line_nb = 0;
  for ($i = 0; $i < count($lines); $i++)
  {
    if (strpos($lines[$i], $marker) !== false)
    {
      $line_nb = $i;
      break;
    }
  }
  $out = "";
  $nesting = 0;
  for ($j = $i; $j < count($lines); $j++)
  {
    $line = $lines[$j];
    if ($j == $i)
    {
      $out .= rtrim($line)."\n";
    }
    else
    {
      for ($k = 0; $k < strlen($line); $k++)
      {
	$char = substr($line, $k, 1);
	if ($char == "{")
	  $nesting++;
	if ($char == "}")
	{
	  if ($nesting == 1)
	  {
	    // Last line to include
	    $out .= $line."\n";
	    break 2;
	  }
	  $nesting--;
	}
      }
    }
    $out .= rtrim($line)."\n";
  }
  return array($out, $line_nb);
}

/**
 * Removes from each line of s the minimum number of spaces common
 * to all lines of s
 */
function fix_indentation($s)
{
  // Replace tabs by spaces
  $s = str_replace("\t", "    ", $s);
  $lines = explode("\n", $s);
  $num_spaces = 100000; // "MAX_INT"
  $out = "";
  // We skip the first and last line
  for ($i = 1; $i < count($lines) - 1; $i++)
  {
    $line = $lines[$i];
    $sp = strlen($line) - strlen(ltrim($line));
    $num_spaces = min($num_spaces, $sp);
  }
  for ($i = 1; $i < count($lines) - 1; $i++)
  {
    $line = $lines[$i];
    $out .= substr($line, $num_spaces)."\n";
  }
  return $out;
}

/**
 * Replaces all strings of the form "jdx:something" into an URL pointing
 * the the corresponding Javadoc
 */
function resolve_javadoc($s)
{
  preg_match_all("/\\{@link\\s*(jd.:.*?)(\\s+.*?){0,1}\\}/", $s, $matches, PREG_SET_ORDER);
  foreach ($matches as $match)
  {
    $url = get_javadoc_url($match[1]);
    if (isset($match[2]) && !empty($match[2]))
    {
      $s = str_replace($match[0], "[".trim($match[2])."]($url)", $s);
    }
    else
    {
      $s = str_replace($match[0], "[".trim($match[1])."]($url)", $s);
    }
  }
  return $s;
}

/**
 * Find the Javadoc entry corresponding to a class
 */
function get_javadoc_url($string)
{
  global $javadoc_root;
  $left_part = substr($string, 0, 4);
  $right_part = substr($string, 4);
  $url = "#";
  switch ($left_part)
  {
    case "jdp:":
      // Package
      $parts = explode(".", $right_part);
      $path = implode("/", $parts);
      $url = $javadoc_root.$path."/package-summary.html";
      break;
    case "jdc:":
    case "jdi:":
      // Class or interface
      $parts = explode(".", $right_part);
      $path = implode("/", $parts);
      $url = $javadoc_root.$path.".html";
      break;
    case "jdm:":
      // Method
      $big_parts = explode("#", $right_part);
      $parts = explode(".", $big_parts[0]);
      $last_part = $parts[count($parts) - 1];
      unset($parts[count($parts) - 1]);
      $path = implode("/", $parts);
      $url = $javadoc_root.$path."/".$last_part.".html#".$big_parts[1];
      break;
  }
  return $url;
}

function get_basename($filename)
{
	$parts = explode("/", $filename);
	return $parts[count($parts)-1];
}

// :tabWidth=2:
?>