<?php
/* ---------------------------------------------------------------- */
//self-contained email settings - follows a standard
//array $a needs to be present
$systemEmail['content_disposition']='html';
$systemEmail['to']=$Email;
$systemEmail['subject']='Your '.strtolower($usemod['memberWord']).' application has been approved';
$systemEmail['from']=$usemod['replytoEmail'];

/* ---------------------------------------------------------------- */

ob_start();
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<style type="text/css">
body{
	padding:10px 20px;
	font-family:Arial, Helvetica, sans-serif;
	font-size:13px;
	}
#header{
	font-family:Georgia, "Times New Roman", Times, serif;
	}
.comment{
	background-color:oldlace;
	border:1px solid #333;
	padding:10px;
	}
.fr{
	float:right;
	margin:0px 0px 5px 10px;
	}
.fl{
	float:left;
	margin:0px 10px 5px 0px;
	}
.cb{
	clear:both;
	height:0px;
	}
</style>
<title><?php echo $emailSubj ? $emailSubj : $systemEmail['subject']?></title>
</head>

<body>
<?php
if($string=$usemod['EmailHeader']){
	echo $string;
}else{
	?><div id="header">
		<h2>
		<a href="<?php echo 'http://'.$_SERVER['HTTP_HOST']?>/?ref=email" title="<?php echo $adminCompany.' home page'?>">
		<?php 
		if(file_exists($_SERVER['DOCUMENT_ROOT'].'/'.$usemod['EmailLogo'])){
			?><img src="http://<?php echo $_SERVER['HTTP_HOST'];?>/<?php echo $usemod['EmailLogo']?>" alt="logo" /><?php
		}
		?>
		<?php echo $adminCompany;?>
		</a>
		</h2>
		<?php
		//get the office
		if($usemod['companyAddress'])echo $usemod['companyAddress'] . '<br />';
		if($usemod['companyCity'] || $usemod['companyState'] || $usemod['companyZip']){
			echo $usemod['companyCity'] . ($usemod['companyCity'] && $usemod['companyState'] ? ', '.$usemod['companyState']:'').'  '.$usemod['companyZip'];
		}
		if($usemod['companyPhone'])echo $usemod['companyPhone'].' (p)<br />';
		if($usemod['companyPhoneTollFree'])echo $usemod['companyPhoneTollFree'].' (tf)<br />';
		if($usemod['companyFax'])echo $usemod['companyFax'].' (f)<br />';
		?>
	</div><?php
}
?>

Dear <?php echo stripslashes($FirstName . ' ' . $LastName);?>:<br />
<br />
Thank you for creating a <?php echo strtolower($usemod['memberWord'])?> account with <?php echo $adminCompany;?>.  Your application for a <?php echo strtolower($usemod['memberWord'])?> account has been accepted and you may sign in and begin using our site immediately. If you have any questions don't hesitate to contact us by email at <?php echo $adminEmail?$adminEmail:'info@'.preg_replace('/^www\./i','',$_SERVER['HTTP_HOST']);?>.
<br />
<br />

We are pleased to welcome you to <?php echo $adminCompany?>.
<br />
<br />
To sign in click here:<br />
<a href="<?php echo ($usemod['usemodURLRoot']?$usemod['usemodURLRoot']:'http://'.$_SERVER['HTTP_HOST']).'/login?src=switchboard';?>"><?php echo ($usemod['usemodURLRoot']?$usemod['usemodURLRoot']:'http://'.$_SERVER['HTTP_HOST']).'/login?src=switchboard';?></a>
<br /> 
<br />

Your username: <?php echo $UserName?>
<?php if(strlen($Password)){ ?>
<br />
Your password: <?php echo stripslashes($Password);?><?php } ?>
<br />
<br />
To log in automatically and change your information follow this link:<br />
<?php echo ($usemod['usemodURLRoot']?$usemod['usemodURLRoot']:'http://'.$_SERVER['HTTP_HOST']).'/login?UN='.$UserName.'&authKey='.md5($MASTER_PASSWORD.$PasswordMD5).'&src='.urlencode('switchboard');?>
<br />
<br />
Sincerely,
<?php echo $adminCompany?> Staff<br />
<br />
<?php
if($usemod['techSupportEmail']){
	?>Tech Support: <a href="mailto:<?php echo $usemod['techSupportEmail']?>.com"><?php echo $usemod['techSupportEmail']?></a><br />
	<?php
}
?>

[<em><?php $stat=stat(__FILE__);
echo str_replace('.php','',end(explode('/',__FILE__))) . ' - last revised '.date('F jS \a\t g:iA',$stat['mtime']);?></em>]<br />

<br />
</body>
</html><?php
$out=ob_get_contents();
ob_end_clean();
$out=preg_replace('/src="\//i','src="'.($usemod['secureProtocolPresent'] ? 'https':'http').'://'.$_SERVER['HTTP_HOST'].'/',$out);
$out=preg_replace('/src="(\.\.\/)+/i','src="'.($usemod['secureProtocolPresent'] ? 'https':'http').'://'.$_SERVER['HTTP_HOST'].'/',$out);
echo $out;
?>