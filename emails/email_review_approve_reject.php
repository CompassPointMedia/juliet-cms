<?php
/* ---------------------------------------------------------------- */
//self-contained email settings - follows a standard
//array $record needs to be present
$systemEmail['content_disposition']='html';
if(!$systemEmail['to'])
	$systemEmail['to']=$usemod['adminEmail'];
if(!$systemEmail['subject'])
	$systemEmail['subject']='New application for '.$adminCompany.' account';
if(!$systemEmail['from'])
	$systemEmail['from']=$usemod['replytoEmail'];

/* ---------------------------------------------------------------- */

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title><?php echo $adminCompany?> : Review and Approve/Reject Account</title>
</head>

<body>
<?php if($usemod['EmailLogo'] && !$nonlinkedTextOnly){ ?>
<div id="imgHeader"><img src="<?php echo $usemod['EmailLogo'];?>" alt="<?php echo $adminCompany?>" /></div>
<?php }else if($usemod['EmailHeader']){  echo $usemod['EmailHeader']; } ?>

<p>Dear <?php echo $adminCompany?> Administrator,</p>

<p><?php echo $record['FirstName'] . ' ' . $record['LastName']. ($record['Company']?' of '.$record['Company']:'');?> has applied for an account with <?php echo $adminCompany?>.  Their key information is found at the bottom of this email.  Please review and call them as necessary.</p>

<p>To approve their account: <a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/cgi/tools?mode=approveWholesale&suppressPrintEnv=1&UN=<?php echo $record['UserName'];?>&authKey=<?php echo md5($MASTER_PASSWORD.$record['PasswordMD5']);?>">Click here</a></p>
<br />

<p>To reject their account with a rejection email: <a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/cgi/tools?mode=rejectWholesaleNotify&suppressPrintEnv=1&UN=<?php echo $record['UserName'];?>&authKey=<?php echo md5($MASTER_PASSWORD.$record['PasswordMD5']);?>">Click here</a></p>
<br />

<p>To reject their account without a rejection email (silently): <a href="http://<?php echo $_SERVER['HTTP_HOST'];?>/cgi/tools?mode=rejectWholesale&suppressPrintEnv=1&UN=<?php echo $record['UserName'];?>&authKey=<?php echo md5($MASTER_PASSWORD.$record['PasswordMD5']);?>">Click here</a></p>
<br />

<h2>Applicant Information</h2>
	<p>Company: <?php echo $record['Company']?><br />
	Name: <?php echo $record['FirstName'] . ' ' . $record['LastName']?><br />
	Address: <?php echo $record['BusAddress']?><br />
	City: <?php echo $record['BusCity']?><br />
	State: <?php echo $record['BusState']?><br />
	Zip: <?php echo $record['BusZip']?><br />
	Email: <a href="mailto:<?php echo $record['Email']?>"><?php echo $record['Email']?></a><br />
	Mobile: <?php echo $record['HomeMobile']?><br />
	Phone: <?php echo $record['BusPhone']?><br />
	Fax: <?php echo $record['BusFax']?><br />
	Website: <?php echo $record['Website']?><br />
	Comments: <?php echo $record['Notes']?><br />
	<br />
	<br />
	Have a nice day,<br />
	<?php echo $adminCompany?> Automated Response<br />
	[<?php $a=explode('/',__FILE__); echo str_replace('.php','',$a[count($a)-1]);?>]</p>
	</p>
</body>
</html>
