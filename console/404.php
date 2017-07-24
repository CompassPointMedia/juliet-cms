<?php
ob_start();
print_r($GLOBALS);
$out=ob_get_contents();
ob_end_clean();

mail('reroute@compasspoint-sw.com','404 called',$out,'From: bugreports@relatebase.com');
?>
Page not developed