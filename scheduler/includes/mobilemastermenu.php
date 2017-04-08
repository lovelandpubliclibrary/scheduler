<?php # mobilemastermenu.php
echo '<div class="mobile"><div class="submenu mastersub">'."\n";
echo '<div class="title"><h1>Master Schedule, '.ucwords($season).' '.$year.' &ndash; '.ucwords($week_type).'</h1></div>'."\n";
echo '<div class="tab';
if ($day == 'sat'){echo ' select';}
echo "\"><a href=\"/$link/$y/$s/a/sat\">Sat</a></div>\n";
echo '<div class="tab';
if ($day == 'sun'){echo ' select';}
echo "\"><a href=\"/$link/$y/$s/$week_type/sun\">Sun</a></div>\n";
echo '<div class="tab';
if ($day == 'mon'){echo ' select';}
echo "\"><a href=\"/$link/$y/$s/$week_type/mon\">Mon</a></div>\n";
echo '<div class="tab';
if ($day == 'tue'){echo ' select';}
echo "\"><a href=\"/$link/$y/$s/$week_type/tue\">Tue</a></div>\n";
echo '<div class="tab';
if ($day == 'wed'){echo ' select';}
echo "\"><a href=\"/$link/$y/$s/$week_type/wed\">Wed</a></div>\n";
echo '<div class="tab';
if ($day == 'thu'){echo ' select';}
echo "\"><a href=\"/$link/$y/$s/$week_type/thu\">Thu</a></div>\n";
echo '<div class="tab';
if ($day == 'fri'){echo ' select';}
echo "\"><a href=\"/$link/$y/$s/$week_type/fri\">Fri</a></div>\n";
echo '</div>'."\n".'</div>'."\n";

?>