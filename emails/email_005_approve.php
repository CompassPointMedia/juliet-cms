Dear Site Administrator:

You have received a request from <?php echo $_POST['FirstName'] . ' ' . $_POST['LastName'] . (trim($_POST['Company']) ? ' of ' . $_POST['Company'] : '')?> to approve an Instructor Account. They have provided the following information regarding their  status:

Instructor ID or Number: <?php echo ($_POST['WholesaleNumber'])?>

State of Instructor ID: <?php echo ($_POST['WholesaleState'])?>

Notes: <?php echo ($_POST['WholesaleNotes'])?>

(The rest of the form field entries are below.)


To approve this application, please use your website admin section, or click the following link:
<?php echo $usemod['usemodURLRoot'].'/retail_approval.php?mode=approveWholesale&suppressPrintEnv=1&UN='.$UserName.'&authKey='.md5($MASTER_PASSWORD.$PasswordMD5);?>


To REJECT this application with an email to the applicant click here
<?php echo $usemod['usemodURLRoot'].'/retail_approval.php?mode=rejectWholesaleNotify&suppressPrintEnv=1&UN='.$UserName.'&authKey='.md5($MASTER_PASSWORD.$PasswordMD5);?>


To reject this application with no email to the applicant click here
<?php echo $usemod['usemodURLRoot'].'/retail_approval.php?mode=rejectWholesale&suppressPrintEnv=1&UN='.$UserName.'&authKey='.md5($MASTER_PASSWORD.$PasswordMD5);?>


Form Fields Submitted:
<?php
$excludes=array(
	'wholesaleaccess',
	'wholesalenotes',
	'wholesalestate',
	'wholesalenumber',
	'submit',
	'password',
	'nullpassword',
	'combomode',
	'mode',
	'eeatoverride',
	'submitmain',
	'passwordmd5'
);
foreach($_POST as $n=>$v){
	if(!in_array(strtolower($n),$excludes)){
		if(!strlen($v))continue;
		echo $n . ': '.stripslashes($v) . "\n";
	}
}

?>

If you have any further questions, please contact your administrator or the webmaster.
[005_approve]