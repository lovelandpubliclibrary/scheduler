<?php # todaysdate.php
date_default_timezone_set('America/Denver');
$headday = date('l');
$headdom = date('j');
$headmon = date('n');
$headyear = date('Y');
echo "$headday, $headmon/$headdom/$headyear";
?>