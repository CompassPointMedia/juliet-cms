<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Welcome to <?php echo $adminCompany?>
</title>
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
<p>Welcome to <?php echo $adminCompany?>! This email confirms your application
			has been received.
<br /><br />
You are approved and can now begin to access our great <?php echo $usemod['wholesaleToken'] ? strtolower($usemod['memberWord']):''?> account features.
<br /><br />
To sign in and change your information in the future, you may go to:</p>
<p><a href="<?php echo $usemod['usemodURLRoot'].'/login?src=add_modify.php'?>"><?php echo $usemod['usemodURLRoot'].'/login?src=add_modify.php'?></a></p>
<p>Your username is: <?php echo $_POST['UserName']?><br />
  Your password is: <?php echo stripslashes($plainTextPassword)?><br />
  <br />
  Thank you,<br />
<?php echo $adminCompany?> Staff</p>
</body>
</html>