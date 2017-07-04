<?php
if(!$refreshComponentOnly){
	?><style type="text/css">
	#usemod{
		}
	#usemod td{
		padding:0px;
		text-align:left;
		}
	.usemodaction{
		border:1px solid #ccc;
		padding:15px;
		border-radius:12px;
		margin-bottom:10px;
		}
	.logoutMessage{
		color:darkblue;
		}
	.errorMessage{
		color:darkred;
		}
	.bottom{
		vertical-align:bottom;
		}
	</style>
	<script language="javascript" type="text/javascript">
	$(document).ready(function(){
		g('UN').focus();
	});
	</script><?php
}
?><div class="errorMessage"><?php
if($errMessage){
	switch(true){
		case $loginCode<0:
			mail($developerEmail,'Database error','File: '.__FILE__.', line: '.__LINE__.get_globals(),$usemod['headerError']);
			?>An abnormal error occured during login.  An administrator has been notified; please try again later<?php
		break;
		case strlen($usemod['loginCodeMessage'][$loginCode]):
			echo $usemod['loginCodeMessage'][$loginCode];
		break;
		default:
			?>Your signin was not correct. Please try again.<?php
	}
}else echo '&nbsp;';
?></div>
<?php 
if($logoutMessage){
	?><div class="logoutMessage"><?php echo $logoutMessage?></div><?php 
}
?>
<div id="usemod">
<form action="<?php echo $thispage;?>" method="post" name="form1" id="form1" class="formLayout1">
<div id="signin" class="fl usemodaction">
<?php
cgi_message_manager('loginheader',$CMSMessage);
?>
	<table>
		<tr>
			<td class="bottom"><?php 
			if($n=$usemod['loginUserNameLabel']){
				echo $n;
			}else{
				$handle=strtolower($usemod['loginUserNameTreatment']);
				?>Enter your <?php echo $handle=='username' || $handle=='both' ? 'user name' : ''?> <?php echo $handle=='both'?'or' : ''?> <?php echo $handle=='email' || $handle=='both'?'email address':''?>:<?php
			}
			?></td>
			<td><input name="UN" type="text" id="UN" value="<?php echo htmlentities($UN)?>" size="12" /></td>
		</tr>
		<tr>
			<td class="bottom">Password:</td>
			<td><input name="PW" type="password" value="" size="12" /></td>
		</tr>
	</table>
	<br />
	<input type="submit" name="Submit" value="Sign In" />
	<br />
	<a href="/cgi/forgotpassword?CMSMessage=<?php echo $CMSMessage;?>&src=<?php echo $src?>">Unable to sign in?</a>
	<input name="src" type="hidden" id="src" value="<?php echo $src;?>" />
    <input name="CMSMessage" type="hidden" id="CMSMessage" value="<?php echo $CMSMessage;?>" />
	<?php
	cgi_message_manager('loginfooter',$CMSMessage);
	?>
</div>
<div id="signup" class="fl usemodaction">
	<?php cgi_message_manager('newaccountheader',$CMSMessage);?>
	<input type="button" name="Button" value="Create One Now" onclick="window.location='usemod?CMSMessage=<?php echo urlencode($CMSMessage);?>&src='+escape('<?php echo $src?>');"/>
</div>
<div class="cb"></div>
</form>
</div>