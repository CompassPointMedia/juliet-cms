<?php
ob_start();
print_r($GLOBALS);
$out=ob_get_contents();
ob_end_clean();

mail('reroute@compasspoint-sw.com','404 called',$out,'From: bugreports@relatebase.com');
?>
Page not developed

https://docs.google.com/spreadsheets/d/19uDu3SdZNn8qXVW5OgvKo0iPD9wG6dz7GcQkZ63X6Bs/edit#gid=2047174303

https://docs.google.com/spreadsheets/d/19uDu3SdZNn8qXVW5OgvKo0iPD9wG6dz7GcQkZ63X6Bs/edit#gid=16642873