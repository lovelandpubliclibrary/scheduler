<?php # 404.php
include('./includes/sessionstart.php');
include('./includes/allsessionvariables.php');

$page_title = 'Error';
include ('./includes/header.html');
include ('./includes/sidebar.html');

echo '<span class="date"><h1>Error</h1></span>
<div style="text-align:center;margin-top:30px;"><h3>404 - This page cannot be found.</h3></div>
<div style="margin-left:20px;">Please ensure you are logged in with the proper credentials.<br/>
<a href="/scheduler2/logout">Click here</a> to log in again.</div>';

include ('./includes/footer.html');
?>