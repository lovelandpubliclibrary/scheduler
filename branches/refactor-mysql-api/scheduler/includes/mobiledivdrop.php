<?php #mobiledivdrop.php

foreach ($divisions as $k=>$v){
	echo '<li><a href="/scheduler/'.$k.'/daily">'.$v.'</a></li>';
	}
?>