<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Your Record Has Been Modified</title>
<style type="text/css">
body{
	margin:10px 18px;
	font-family:Arial, Helvetica, San-serif;
	font-size:13px;
}
</style>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>
<body>
<?php if($usemod['EmailLogo'] && !$nonlinkedTextOnly){ ?>
<div id="imgHeader"><img src="<?php echo $usemod['EmailLogo'];?>" alt="<?php echo $adminCompany?>" /></div>
<?php }else if($usemod['EmailHeader']){  echo $usemod['EmailHeader']; } ?>
<p>Thank you, <?php echo $_SESSION['firstName'] . ' ' . $_SESSION['lastName']?>, your <?php echo $adminCompany?> record has been modified.<br />
  <br />
  For future reference you may modify your record again at:<br />
  <a href="<?php echo $usemod['usemodURLRoot']?>/modify.php"><?php echo $usemod['usemodURLRoot'].'/modify.php'?></a>
</p>
</body>
</html>