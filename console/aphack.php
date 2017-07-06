<?php
/*
This is a temporary hack to get by the "Internal Server Error" I am getting on the console.  It is basically a stand-in folder for console which requires the parallel files.  I found that without the 200 OK status, external javascript files would not process
code as of 2010-06-11:
------------
header("HTTP/1.1 200 OK");
if($GLOBALS['REDIRECT_URL']){
	$QUERY_STRING=$GLOBALS['QUERY_STRING']=$GLOBALS['REDIRECT_QUERY_STRING'];
	require(preg_replace('#^/console/#','../console/',$GLOBALS['REDIRECT_URL']));
}else{
	require('index.php');
}
------------
*/
header("HTTP/1.1 299 OK", true);
if($f=$GLOBALS['REDIRECT_URL']){
	$QUERY_STRING=$GLOBALS['QUERY_STRING']=$GLOBALS['REDIRECT_QUERY_STRING'];
	parse_str($GLOBALS['REDIRECT_QUERY_STRING'], $_REQUEST);
	if(preg_match('#/$#',$f))$f.='index.php';
	@extract($_REQUEST);
	require(preg_replace('#^/console/#','../console-sym/',$f));
}else{
	require('../console-sym/index.php');
}
