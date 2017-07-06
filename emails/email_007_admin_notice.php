<?php
/* ---------------------------------------------------------------- */
//self-contained email settings - follows a standard
//array $a needs to be present
$emailTo=$developerEmail;
$systemEmail['content_disposition']='html';
$systemEmail['subject']='New Account Notification';
$systemEmail['from']=$usemod['replytoEmail'];


/* ---------------------------------------------------------------- */

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>New Account Notification</title>
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
<br />
Hello<?php echo $adminFirstName ?' '.$adminFirstName:''?>,<br />
<br />
<p>A new user, <?php echo stripslashes($FirstName . ' ' . $LastName);?><?php echo $Company ? ' of '.stripslashes($Company):'';?>, has signed up for a personal account.</p>

<p>Their email addresss is <a href="mailto:<?php echo $Email?>"><?php echo $Email?></a> <?php
if($x=$sender['BusPhone'])echo '<br />Work phone: '. $x;
if($x=$sender['HomePhone'])echo '<br />Home phone: '. $x;
if($x=$sender['HomeMobile'])echo '<br />Mobile: '. $x;
?>
</p>

<p><br />
Have a Nice Day,<br />
<?php echo $adminCompany?> Membership System </p>
</body>
</html>