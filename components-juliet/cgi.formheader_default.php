<?php
/*
2013-05-27:
this is now modified to pretty much get everything in CMSB, though defaults can be handled as pJ_getvars() in the componentFile.  Note that you can add form fields to sections that are nested in forms.  The only limits then are vars and other systems in CMSB

two concepts:
message by handle
message by scenario - this is being developed throughout cgi and is based on CMSMessage
variable CMSMessage allows for a complete set of messages for formLocations X states.  States are currently simply 
insert
update

2010-10-01
this allows custom messages to be declared by identifying regions and specifying message vars, for example
usemod?insertAccountFormHeader=beforePurchase&insertAccountFormFooter=beforePurchaseFooter
see docs folder for a list of message regions
*/
if($CMSMessage){
	cgi_message_manager('formheader',$CMSMessage);
}else{
	//2013-05-27 this code is old
	if($out=$message[is_array($_REQUEST['msg']) ? $_REQUEST['msg']['msgTop'] : $_REQUEST['msg']]){
		echo $out;
	}else if($mode=='update'){
		/*
		2012-04-16: this is mostly legacy and will not be used for Juliet projects
		
		*/
		if(!$updateAccountFormHeader)$updateAccountFormHeader='updateAccountFormHeader';
		if($msg=$message[$updateAccountFormHeader]){
			echo $msg;
		}else{
			//generic message could use the following enhancements
			#mention of the account
			#mention of which fields are required
			?>
			<div id="updateAccountFormHeader">
			<h1 class="nullBottom cgiHeader">Update Your Account</h1>
			<p class="cgiGeneralContent">Make sure that all required fields are filled out</p>
			<p class="cgiGeneralContent">To change your password <a href="/cgi/resetpassword" onclick="return ow(this.href, 'l1_password','700,700');">click here</a></p>
			</div>
			<?php
			CMSB( $whsle ? 'resellerFormUpdate' : 'memberFormUpdate' );
		}
	}else{
		if(!$insertAccountFormHeader)$insertAccountFormHeader='insertAccountFormHeader';
		if($msg=$message[$insertAccountFormHeader]){
			echo $msg;
		}else{
			//generic message could use the following enhancements
			#mention of the account
			#mention of which fields are required
			?>
			<div id="updateAccountFormHeader">
			<h1 class="nullBottom cgiHeader">Create a New Account</h1>
			<p class="cgiGeneralContent">Already a <?php echo $whsle?$umResellerWord:$usemod['memberWord']?>? <a href="login?src=<?php echo $src ? urlencode(stripslashes($src)):'usemod'?>">Click here to sign in</a></p>
			</div>
			<?php
			CMSB( $whsle ? 'resellerFormIntro' : 'memberFormIntro' );
		}
	}
}
?>