<?php #sess_clean.php

/** define the directory **/
$dir = "/home/teulberg/lpl-repository.com/sess_tmp/";

/*** cycle through all files in the directory ***/
foreach (glob($dir."*") as $file) {

/*** if file is 24 hours (86400 seconds) old then delete it ***/
if (filemtime($file) < time() - 86400) {
    unlink($file);
    }
}

?>