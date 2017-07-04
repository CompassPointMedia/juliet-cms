<?php
/* name=Social Networking Widget; description=first version */
$SNWHideAddThisCoding=true; 
$SNWTwitterInvite= $siteName.' - details here:';
$SNWGallery='km'; 
$SNWGallerySize=32;
$SNWLinks=array('FaceBook','Twitter','Digg','Stumbleupon','Delicious');
$SNWBackground=true;
require($_SERVER['DOCUMENT_ROOT'].'/components/SNW_widget_v101.php');
echo $topCtrlRSSFeedLink;
?>