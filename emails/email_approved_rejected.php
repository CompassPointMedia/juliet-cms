<?php
/* ---------------------------------------------------------------- */
//self-contained email settings - follows a standard
//array $a needs to be present
$systemEmail['content_disposition']='html';
if(!$systemEmail['to'])
	$systemEmail['to']=$a['Email'];
if(!$systemEmail['subject'])
	$systemEmail['subject']='Your '.strtolower($usemod['memberWord']).' application has been '.($mode=='approveWholesale' ? 'approved':'declined');
if(!$systemEmail['from'])
	$systemEmail['from']=$usemod['replytoEmail'];


/* ---------------------------------------------------------------- */

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
body{
	margin:10px 18px;
	font-family:Arial, Helvetica, San-serif;
	font-size:13px;
}
</style>
<title>General <?php echo $mode=='approveWholesale' ? 'Approval' : 'Decline'?> Reply</title>
</head>

<body>
<?php if($usemod['EmailLogo'] && !$nonlinkedTextOnly){ ?>
<div id="imgHeader"><img src="<?php echo $usemod['EmailLogo'];?>" alt="<?php echo $adminCompany?>" /></div>
<?php }else if($usemod['EmailHeader']){  echo $usemod['EmailHeader']; } ?>
Dear <?php echo $a['FirstName'] . ' ' . $a['LastName']?>:<br />
<br />
<?php 
if($mode=='approveWholesale'){
	?>
	Thank you for your interest in a <?php echo strtolower($usemod['memberWord'])?> account with <?php echo $adminCompany;?>.  After review by administration, your application for a <?php echo strtolower($usemod['memberWord'])?> account has been accepted.
	<br />
	<br />
	
	We are pleased to welcome you to <?php echo $adminCompany?> and look forward to being of benefit to your corporate image through the quality of our servce.  If we can assist you in any way, please let us know.  Please also save this email for your records.
	<br />
	<br />
	
	Your username: <?php echo $a['UserName']?>
	<?php if(strlen($a['Password'])){ ?>
	<br />
	Your password: <?php echo $a['Password']?><?php } ?>
	<br />
	<br />
	To log in automatically and change your information follow this link:<br />
	<?php echo $usemod['usemodURLRoot'].'/login?UN='.$a['UserName'].'&authKey='.md5($MASTER_PASSWORD.$a['PasswordMD5']).'&src='.urlencode('switchboard');?> 
	<br />
	<br />

	<?php echo $usemod['companyAddress'] ?>	
	<br />
	<br />

	Sincerely,
	<?php echo $adminCompany?> Staff<br />
	<br />
	<br />
	<br />
	<br />
	[<?php echo str_replace('_rejected','-_____',str_replace('.php','',end(explode('/',__FILE__))));?>]
	<?php
}else{
	?>
	Thank you for your interest in a <?php echo strtolower($usemod['memberWord'])?> account with <?php echo $adminCompany;?>.  After review by administration, your application for a <?php echo strtolower($usemod['memberWord'])?> account has been declined.
	<br />
	<br />
	If you would like further information as to the reason for the decline, please contact <?php echo $adminCompany?> at this email address:<br />
	<?php echo $usemod['replytoEmail']?>
	<br />
	<br />
	Sincerely, <?php echo $adminCompany?> Staff
	<br />
	<br />
	<br />
	<br />
	<?php
}

?>
</body>
</html>