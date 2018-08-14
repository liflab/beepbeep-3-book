<?php

/**
 * Performs a few post-processing operations on the Markdown files
 */

// Touch-up to dictionary
$s = file_get_contents("markdown/dictionary/README.md");
$s = preg_replace("/@palette (.*?)@/ms", "![\\1](Palette-\\1.png)", $s);
file_put_contents("markdown/dictionary/README.md", $s);
?>