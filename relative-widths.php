<?php

// The maximum width of an SVG (in mm); any file larger needs to be scaled down
$MAX_WIDTH = 112;

// The default scale of figures
$DEFAULT_SCALE = 0.6;

// The possible scales for a picture; we avoid resizing each picture to an
// arbitrary number of scaling factors, for uniformity
$SCALES = array(0.6, 0.45, 0.3, 0.2, 0.1);

$EXAMPLES_FOLDER = "../examples/Source/src/";
$PRE_MARKDOWN_FOLDER = "pre-markdown/";

$markdown_file = $argv[1];

$contents = file_get_contents($markdown_file);
preg_match_all("/\\{@img (.*?)\\}/ms", $contents, $matches);
foreach ($matches[1] as $filename)
{
	$full_path = "";
	if (substr($filename, 0, 9) === "doc-files")
	{
		$full_path = $EXAMPLES_FOLDER.$filename;
	}
	else
	{
		$slash_pos = strrpos($markdown_file, "/");
		if ($slash_pos !== false && $slash_pos > 0)
		{
			$full_path = substr($markdown_file, 0, $slash_pos + 1).$filename;
		}
		else
		{
			$full_path = $PRE_MARKDOWN_FOLDER.$filename;
		}
	}
	$svg_filename = str_replace(".png", ".svg", $full_path);
	if (!file_exists($svg_filename))
		continue;
	$width = get_svg_width($svg_filename);
	$scale = $DEFAULT_SCALE;
	if ($width * $DEFAULT_SCALE > $MAX_WIDTH)
	{
		$scale = $MAX_WIDTH / $width;
	}
	$new_scale = find_closest_scale($scale, $SCALES);
	echo str_pad($filename, 50)."\t".$new_scale."\n";
}
exit(0);

function find_closest_scale($scale, $SCALES)
{
	foreach ($SCALES as $s)
	{
		if ($s <= $scale)
			return $s;
	}
}

function get_svg_width($filename)
{
	$contents = file_get_contents($filename);
	if (!preg_match("/width=\"([\\d\\.]*)mm\"/s", $contents, $matches))
	{
		return 0;
	}
	return $matches[1];
}
?>