<?php #archive.php

$page_title = "Archives";
include('./includes/sessionstart.php');
include('./includes/allsessionvariables.php');
include ('./includes/header.html');
include ('./includes/sidebar.html');

echo '<div id="archives">'."\n";

echo "<span class=\"date\"><h1>$page_title</h1></span>\n";

function getDirectoryList ($directory) {
	// create an array to hold directory list
	$results = array();
	// create a handler for the directory
	$handler = opendir($directory);
	// open directory and walk through the filenames
	while ($file = readdir($handler)) {
		// if file isn't this directory or its parent, add it to the results
		if ($file != "." && $file != "..") {
			$results[] = $file;
			}
		}
	// tidy up: close the handler
	closedir($handler);
	// done!
	return $results;
	}
	
$directory = './archives';
$files = getDirectoryList($directory);
arsort($files);

echo '<div class="wrapper">'."\n";
echo '<div class="archives">'."\n";

$thisyear = date('Y');
$lastyear = $thisyear-1;

$years = Array();
$max = 6;

foreach ($files as $name){
	$linktext = basename($name, ".pdf");	
	list($y,$m) = explode("-",$linktext);
    $years[$y][$m][] = array(0=>$name, $linktext);
	}

$countyears = count($years);
	
foreach ($years as $y=>$month){
	if ($y == $thisyear){
		echo '<div class="thisyear"'."\n";
		if ($countyears > 1){
			echo 'style="float:left;"';
			}
		echo '>';
		echo "<div class=\"yr1\"><h3>$y</h3></div>\n";
		$count = count($month);
		$i = 0;
		if ($count <= $max){
			foreach ($month as $m=>$file){
				if ($i < $max) {
					$mon = date('F',mktime(0,0,0,$m));
					echo "<div class=\"month\">\n<a href=\"#\" class=\"toggle1\" tabindex=\"0\">$mon</a>
						<a href=\"#\" class=\"toggle2\">$mon</a><br/>\n";
					echo '<div id="menu2">'."\n";
					foreach ($file as $f){
						echo "<a target=\"_blank\" href=\"$directory/$f[0]\">$f[1]</a><br/>\n";
						}
					echo '</div>'."\n".'</div>'."\n";
					$i++;
					}
				}
			}
		else {
			echo '<div class="moncol1">'."\n";
			foreach ($month as $m=>$file){
				if ($i < $max) {
					$mon = date('F',mktime(0,0,0,$m));
					echo "<div class=\"month\">\n<a href=\"#\" class=\"toggle1\" tabindex=\"0\">$mon</a>
						<a href=\"#\" class=\"toggle2\">$mon</a><br/>\n";
					echo '<div id="menu2">'."\n";
					foreach ($file as $f){
						echo "<a target=\"_blank\" href=\"$directory/$f[0]\">$f[1]</a><br/>\n";
						}
					echo '</div>'."\n".'</div>'."\n";
					$i++;
					}
				}
			echo '</div>'."\n".'<div class="moncol2">'."\n";
			$i = 0;
			foreach ($month as $m=>$file){
				if ($i < $max) {
					$i++;
					}
				else {
					$mon = date('F',mktime(0,0,0,$m));
					echo "<div class=\"month\"><a href=\"#\" class=\"toggle1\" tabindex=\"0\">$mon</a>
						<a href=\"#\" class=\"toggle2\">$mon</a><br/>\n";
					echo '<div id="menu2">'."\n";
					foreach ($file as $f){
						echo "<a target=\"_blank\" href=\"$directory/$f[0]\">$f[1]</a><br/>\n";
						}
					echo '</div>'."\n".'</div>'."\n";
					$i++;
					}
				}
			echo '</div>'."\n";
			}
		}
	}
	
echo '</div>'."\n";

echo '<div class="spacer"></div>'."\n";

$years = Array();

foreach ($files as $name){
	$linktext = basename($name, ".pdf");	
	list($y,$m) = explode("-",$linktext);
    $years[$y][$m][] = array(0=>$name, $linktext);
	}

foreach ($years as $y=>$month){
	if ($y == $lastyear){
		echo '<div class="lastyear">'."\n";
		echo "<div class=\"yr2\"><h3>$y</h3></div>\n";
		$count = count($month);
		$i = 0;
		if ($count <= $max){
			foreach ($month as $m=>$file){
				if ($i < $max) {
					$mon = date('F',mktime(0,0,0,$m));
					echo "<div class=\"month\">\n<a href=\"#\" class=\"toggle1\" tabindex=\"0\">$mon</a>\n
						<a href=\"#\" class=\"toggle2\">$mon</a><br/>\n";
					echo '<div id="menu2">'."\n";
					foreach ($file as $f){
						echo "<a target=\"_blank\" href=\"$directory/$f[0]\">$f[1]</a><br/>\n";
						}
					echo '</div>'."\n".'</div>'."\n";
					$i++;
					}
				}
			}
		else {
			echo '<div class="moncol1">'."\n";
			foreach ($month as $m=>$file){
				if ($i < $max) {
					$mon = date('F',mktime(0,0,0,$m));
					echo "<div class=\"month\">\n<a href=\"#\" class=\"toggle1\" tabindex=\"0\">$mon</a>\n
						<a href=\"#\" class=\"toggle2\">$mon</a><br/>\n";
					echo '<div id="menu2">'."\n";
					foreach ($file as $f){
						echo "<a target=\"_blank\" href=\"$directory/$f[0]\">$f[1]</a><br/>\n";
						}
					echo '</div>'."\n".'</div>'."\n";
					$i++;
					}
				}
			echo '</div>'."\n".'<div class="moncol2">'."\n";
			$i = 0;
			foreach ($month as $m=>$file){
				if ($i < $max) {
					$i++;
					}
				else {
					$mon = date('F',mktime(0,0,0,$m));
					echo "<div class=\"month\">\n<a href=\"#\" class=\"toggle1\" tabindex=\"0\">$mon</a>\n
						<a href=\"#\" class=\"toggle2\">$mon</a><br/>\n";
					echo '<div id="menu2">'."\n";
					foreach ($file as $f){
						echo "<a target=\"_blank\" href=\"$directory/$f[0]\">$f[1]</a><br/>\n";
						}
					echo '</div>'."\n".'</div>'."\n";
					$i++;
					}
				}
			echo '</div>'."\n";
			}
			echo '</div>'."\n";
		}
	}
echo '</div>'."\n".'</div>'."\n".'</div>'."\n";
include ('./includes/footer.html');
?>