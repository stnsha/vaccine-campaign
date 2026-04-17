<?php
$dumpfile=$_GET['id'];
// We'll be outputting a dumpfile file
header('Content-type: application/xlsx');

// It will be called for downloaded
header("Content-Disposition: attachment; filename=$dumpfile");

// The PDF source is in original.pdf
readfile($dumpfile);
unlink($dumpfile);

?>
