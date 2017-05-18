<?php
extract($_REQUEST);
//echo '<pre>'; print_r($_REQUEST); exit;
header('Pragma: public');				// required
header('Expires: 0');					// to prevent caching
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Cache-Control: private',false);
header('Content-Type: application/force-download');
header('Content-Disposition: attachment; filename="'.basename($filename).'"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: '.filesize($filename));	// provide file size
header('Connection: close');
if (!file_exists($filename)) {
  exit("Cannot find file located at '$localPath'");
}
readfile($filename);
exit;
?>