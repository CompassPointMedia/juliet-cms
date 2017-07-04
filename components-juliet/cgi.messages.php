<?php
$i=0;

//======================== MESSAGE INSTANCE ===========================
/* # -- 				DO NOT TOUCH THIS CODE!!				-- # */
/* # */ $i++;													/* # */
/* # */ ob_start();												/* # */
/* # */ //message begins here									/* # */
//---------------------------------------------------------------------
$messageAlias='newsletterSignup';
?><!-- message <?php echo $i.': '.$messageAlias;?> generated from components-juliet/cgi.messages.php line <?php echo __LINE__?> -->

<?php CMSB('newsletterSignup');?>
<?php
//---------------------------------------------------------------------
/* # -- 				DO NOT TOUCH THIS CODE!!				-- # */
/* # */ $message[$messageAlias]=ob_get_contents();				/* # */
/* # */ $message[$i]=ob_get_contents();							/* # */
/* # */ ob_end_clean();											/* # */
/* # */ if(false){ ?><br /><br /><br /><hr /><?php }			/* # */
//====================== END MESSAGE INSTANCE =========================


//======================== MESSAGE INSTANCE ===========================
/* # -- 				DO NOT TOUCH THIS CODE!!				-- # */
/* # */ $i++;													/* # */
/* # */ ob_start();												/* # */
/* # */ //message begins here									/* # */
//---------------------------------------------------------------------
$messageAlias='emailConfirmedWhslePending';
?><!-- message <?php echo $i.': '.$messageAlias;?> generated from components-juliet/cgi.messages.php line <?php echo __LINE__?> -->
<h2>Email Address Verified</h2>
<p>
Thank you, <?php echo $_SESSION['lastName'] ? $_SESSION['firstName'] . ' ' . $_SESSION['lastName'].', ' : ''; ?>your email address has been verified.  Check your inbox for a welcome email containing information on how to use your account.  PLEASE SAVE THIS EMAIL for your records.<br />
<?php if($_SESSION['cnx'][$acct]['wholesaleAccess']==4){ ?>
<br />
You can sign in now but you will not be above to view reseller prices.  Thank you, and we look forward to doing business with you and providing our products to your company.<br />
<?php } ?>
</p>
<p>
Sincerely,<br />
<?php echo $siteName?> Staff
</p>
<?php
//---------------------------------------------------------------------
/* # -- 				DO NOT TOUCH THIS CODE!!				-- # */
/* # */ $message[$messageAlias]=ob_get_contents();				/* # */
/* # */ $message[$i]=ob_get_contents();							/* # */
/* # */ ob_end_clean();											/* # */
/* # */ if(false){ ?><br /><br /><br /><hr /><?php }			/* # */
//====================== END MESSAGE INSTANCE =========================

//======================== MESSAGE INSTANCE ===========================
/* # -- 				DO NOT TOUCH THIS CODE!!				-- # */
/* # */ $i++;													/* # */
/* # */ ob_start();												/* # */
/* # */ //message begins here									/* # */
//---------------------------------------------------------------------
$messageAlias='preWeddingConsultation';
?><!-- message <?php echo $i.': '.$messageAlias;?> generated from components-juliet/cgi.messages.php line <?php echo __LINE__?> -->
<h1>Step 2: Enter Your Contact Information </h1>
<p>
Enter your name and contact information so we are prepared for your visit.  Have you already created an account with Pennington's?  In that case, <a href="/cgi/login?src=<?php echo urlencode(stripslashes($src));?>">sign in here</a>.</p>
<p>
Date of your wedding: 
  <input name="DateOfWedding" type="text" id="DateOfWedding" />
  <br />
Number of people attending the consultation: 
<input name="NumberAttendingTasting" type="text" id="NumberAttendingTasting" size="7" />
<script language="javascript" type="text/javascript">
setTimeout('appendcomments()',7000);
function appendcomments(){
	g('form1').onsubmit=function(){
		g('Notes').value +="\n";
		g('Notes').value +="date of wedding: "+g('DateOfWedding').value+"\n";
		g('Notes').value +="number attending tasting: "+g('NumberAttendingTasting').value+"\n";		
	}
}
</script>
</p>
<?php
//---------------------------------------------------------------------
/* # -- 				DO NOT TOUCH THIS CODE!!				-- # */
/* # */ $message[$messageAlias]=ob_get_contents();				/* # */
/* # */ $message[$i]=ob_get_contents();							/* # */
/* # */ ob_end_clean();											/* # */
/* # */ if(false){ ?><br /><br /><br /><hr /><?php }			/* # */
//====================== END MESSAGE INSTANCE =========================

?>