<?php
//last update 2012-01-28
$systemEmail['to']=$a['Email'];
$systemEmail['subject']='Your '.strtolower($usemod['resellerWord']).' application with '.$adminCompany;
$systemEmail['from']=$usemod['replytoEmail'];


?>
Dear <?php echo $a['FirstName'] . ' ' . $a['LastName']?>:

<?php 
ob_start();
if($mode=='approveWholesale'){
	?>
	Thank you for your interest in a <?php echo strtolower($usemod['resellerWord'])?> account with <?php echo $adminCompany;?>.  After review by administration, your application for a <?php echo strtolower($usemod['resellerWord'])?> account has been accepted.
	
	We are pleased to welcome you to <?php echo $adminCompany?> and look forward to being of service to you.
	
	Your username: <?php echo $a['UserName']?>
	
	<?php if(strlen($a['Password'])){ ?>Your password: <?php echo $a['Password']?><?php } ?>
	
	To log in automatically and change your information follow this link:
	http://www.<?php echo preg_replace('/^www\./i','',$_SERVER['HTTP_HOST']);?>/cgi/login?UN=<?php echo $a['UserName'];?>&authKey=<?php echo md5($MASTER_PASSWORD.$a['PasswordMD5']);?>&src=<?php echo urlencode('/cgi/switchboard');?>
	
	<?php echo $usemod['companyAddress'] ?>	
	
	Sincerely,
	<?php echo $adminCompany?> Staff
	
	<?php
}else{
	?>
	Thank you for your interest in a <?php echo strtolower($usemod['resellerWord'])?> account with <?php echo $adminCompany;?>.  After review by administration, your application for a <?php echo strtolower($usemod['resellerWord'])?> account has been declined.
	
	If you would like further information as to the reason for the decline, please contact <?php echo $adminCompany?> at this email address:
	<?php echo $usemod['replytoEmail']?>
	
	Sincerely, <?php echo $adminCompany?> Staff
	
	<?php
}
$out=ob_get_contents();
ob_end_clean();
echo str_replace("\t",'',$out);

?>