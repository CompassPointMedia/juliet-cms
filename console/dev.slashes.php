<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Untitled Document</title>
</head>

<body>
<form action="dev.slashes.php?r=single'double&quot;back1\back2\\backsingle\'backdouble\&quot;" method="post">

<input type="text" name="string" />
<input type="submit" name="submit" value="submit" />


</form>
</body>
</html>
<?php
if(!empty($_REQUEST)){
	echo '<pre>';
	print_r($GLOBALS);
	print_r($_POST);
}
$str='hello dolly';
for($i=0;$i<strlen($str);$i++){
	echo $str{$i};
	echo $str[$i];
}


?>