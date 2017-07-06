<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Forgot your password</title>
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
<?php
if(!$usemod['passwordResetTimeout'] || $usemod['passwordResetTimeout']>72)$usemod['passwordResetTimeout']=24;
$t=time();
$tAuthKey=md5($MASTER_PASSWORD.$a[1]['PasswordMD5'].$t);
$url=($usemod['usemodURLRoot'] ? $usemod['usemodURLRoot'] : 'http://'.$_SERVER['HTTP_HOST'].'/cgi').'/resetpassword?UN='.$a[1]['UserName'].'&t='.$t.'&tAuthKey='.$tAuthKey.'&src='.urlencode($src);
?><strong>You have requested to reset your password</strong>.  For security purposes this link will only be valid for <?php echo $usemod['passwordResetTimeout'];?> hours.  Please go to:
<br />
<a href="<?php echo $url;?>"><?php echo $url;?></a>
<br />
<br />
Sincerely,<br />
<?php echo $adminCompany?>
Staff 
</body>
</html>