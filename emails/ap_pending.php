Dear <?php echo $adminCompany?> Staff,

An application is pending by <?php echo $FirstName . ' ' . $LastName . ($Company ? ' of '.$Company : '');?>.  They need to verify their email address first (<?php echo $Email?>).  However, if they do not do so, you can verify it for them by this link:


<?php $href= 'http://'.$_SERVER['HTTP_HOST'].'/cgi/login?sessionid='.$GLOBALS['PHPSESSID'].'&UN='.$UserName.'&EnrollmentAuthToken='.$EnrollmentAuthToken;
echo $href;
?>



Or you may do the following:
1. Go to www.<?php echo $_SERVER['HTTP_HOST'];?>.com/console/
2. Select Clients > List Clients
3. Locate and open this client's record
4. Click the Contacts tab
5. Check "Has been verified" below their email and make any other needed changes in their record
6. Click "OK"


Have a nice day,
<?php echo $adminCompany?> Automated Response
[<?php $a=explode('/',__FILE__); echo str_replace('.php','',$a[count($a)-1]);?>]
