<?php
if(!$refreshComponentOnly){
	?><style type="text/css">
	</style>
	<script language="javascript" type="text/javascript">
	</script><?php
}
require_once($_SERVER['DOCUMENT_ROOT'].'/console/resources/bais_00_includes_mail.php');

//these were in mail_profile_01.exe
if(trim($EmailColumns)){
	$a=explode(',',trim($EmailColumns));
	foreach($a as $v){
		$v=preg_replace('/[^0-9]+/i','',trim($v));
		if(!trim($v))continue;
		$_emailColumns[]=$v;
	}
}
if($submode=='testquery'){
	ob_start();
	$result=q(stripslashes($ComplexQuery), ERR_ECHO);
	$err=ob_get_contents();
	ob_end_clean();
	if($err){
		error_alert('Your query returned an error ('.mysqli_errno($db_cnx).'\n\nThis is the text of the error:\n'.addslashes(mysqli_error($db_cnx)));
	}else{
		if(!mysqli_num_rows($result)){
			error_alert('Query is valid, but it returns no records (empty set)');
		}else{
			$rows=mysqli_num_rows($result);
			$rd=mysqli_fetch_array($result);
			$cols=count($rd)/2;
			error_alert('Query returned a total of '.$rows.' records ('. $cols.' fields)');
		}
	}
}else if($submode=='selectEmailCols'){
	if(!count($emailCol)){
		?><script language="javascript" type="text/javascript">
		alert('No column selected; click on the checkbox above the column which contains emails');
		</script><?php
	}else{
		//buidl the string
		if(is_array($colNames)){
			foreach($emailCol as $n=>$v){
				$a[]=$colNames[$v];
			}
			$textString=implode(', ',$a);
			if(strlen($textString)>50){
				$shortTextString=substr($textString,0,50).' ..';
			}else{
				$shortTextString=$textString;
			}
		}
		$string='Column '.implode(', Column ',$emailCol);
		//set the email columns in session
		$_SESSION['mail'][$acct]['templates'][$ID]['EmailColumns']=$string;
		//javascript action only
		?><script>
		//get the text version
		//update the wpo field naming the columns
		window.parent.opener.g('emailCols').value='<?php echo $string?>';
		window.parent.opener.g('emailCols').select();
		window.parent.opener.g('emailFieldList').innerHTML='Currently selected: <?php echo $shortTextString?>';
		window.parent.opener.g('emailFieldList').title='<?php echo $textString?>';
		window.parent.opener.g('emailFieldList2').innerHTML='Currently selected: <?php echo $shortTextString?>';
		window.parent.opener.g('emailFieldList2').title='<?php echo $textString?>';
		window.parent.close();
		</script><?php
	}
	$assumeErrorState=false;
	exit;
}else if($submode=='saveprofile'){
	require_once($MASTER_COMPONENT_ROOT.'/mailer_profile_error_checking_v100.php');
	if($errLevel>=2){
		prn($err);
		?><script language="javascript" type="text/javascript"><?php
		foreach($err[2] as $v){
			echo "alert('" . $v . "')";
		}
		?></script><?php
		$assumeErrorState=false;
		exit;
	}
	//2006-07-12: Handle groups values
	if(count($Groups_ID))
	foreach($Groups_ID as $v){
		if(trim($v)) $a[]=$v;
	}
	$Groups_ID=implode(',',$a);
	if(false){
		//modify Groups_ID field to hold multiple
		q('ALTER TABLE `relatebase_mail_profiles` DROP INDEX `groups_id`', ERR_ECHO);
		q('ALTER TABLE `relatebase_mail_profiles` CHANGE `Groups_ID` `Groups_ID` TEXT NOT NULL', ERR_ECHO);
	}

	$sql="UPDATE relatebase_mail_profiles SET";
	//TemplateFileOrURL
	if($TemplateMethod=='file'){
		$TemplateFileOrURL=$Files_ID;
	}else{
		$TemplateFileOrURL=$TemplateLocationURL;
	}
	
	//LastUsageTime
	/*** no longer used
	$acceptExistingName=true;
	if($saveAsNew && !$acceptExistingName){
		$name=$_POST['Name']. ' ('. rand(10000,10000000).')';
	}else{
		$name=$_POST['Name'];
	}
	***/
	
	$sql.=" EditDate = '$timeStamp',
	Name = '$Name', 
	RecipientSource = '$RecipientSource',
	Views_ID = '$Views_ID',
	Groups_ID='$Groups_ID',
	OverrideViewFilters = '', /** undeveloped **/
	ImportType = '$ImportType',
	ImportHeaders = '$ImportHeaders',
	HTMLOrText = '$HTMLOrText', /** 1=HTML, 0=Text **/
	Composition = '$Composition',
	EditableRegion = '".($Composition=="template"?$EditableRegion:'')."',
	EditableRegionName = '', /** undeveloped **/
	TemplateMethod = '".($Composition=="template"?$TemplateMethod:'')."', /** file or url **/
	TemplateFileOrURL = '".($Composition=="template"?$TemplateFileOrURL:'')."',
	TemplateDefaultDirectory = '', /** not developed **/
	AttachmentDefaultDirectory = 0, /** not developed **/
	FromName = '$FromName',
	FromEmail = '$FromEmail',
	ReplyToName  = '".($ReplyToName!=="(optional)"?$ReplyToName:"")."',
	ReplyToEmail  = '".($ReplyToEmail!=="(optional)"?$ReplyToEmail:"")."',
	BounceEmail  = '".($BounceEmail!=="(optional)"?$BounceEmail:"")."',
	TestEmail = '$TestEmail',
	TestEmailBatch = '$TestEmailBatch',
	TestEmailStart = '$TestEmailStart',
	AlwaysPreview = '$AlwaysPreview',
	Importance  = '$Importance',
	AttachVCard  = '$AttachVCard',
	ReturnReceipt  = '$ReturnReceipt',
	DefaultBatchName  = '$DefaultBatchName',
	DefaultBatchNameAutoinc  = '$DefaultBatchNameAutoinc',
	BatchRecordEmail  = '$BatchRecordEmail',
	AttachmentString='$AttachmentString',
	ResourceType=1,
	RecordVersion  = '1.0',
	MailerVersion  = '1.0' 
	WHERE ID = $ID";
	q($sql);
	prn($qr);
	//---------------------------- enter the editable areas -----------------------
	!$Composition ? $Composition='blank':'';
	if($Composition=='blank'){
		//place a wrapper on the session, insert that as the Body var
		//2011-05-25: note that we just use the form post
		if($varid=q("SELECT ID FROM relatebase_mail_profiles_vars WHERE Profiles_ID='$ID' AND  Name='EditableArea' AND Ky='_blank_email'", O_VALUE)){
			q("UPDATE relatebase_mail_profiles_vars SET 
			Editor='".sun()."',
			Val='$Content' WHERE ID=$varid");
			prn($qr);
		}else{
			q("INSERT INTO relatebase_mail_profiles_vars SET 
			CreateDate=NOW(),
			Creator='".sun()."',
			Profiles_ID='$ID',
			Name='EditableArea',
			Ky='_blank_email',
			Notes='mail_profile_01_exe.php line ".__LINE__."',
			Val='$Content'");
			prn($qr);
		}
	}else if($Composition=='template'){
		if($TemplateMethod=='file'){
			exit('use of VOS file as template not developed');
			$string='';
		}else if($TemplateMethod=='url'){
			//one way we can detect problems, or a change in templates, is in a mismatch between session editable regions and the template -- this needs developed
			//note: it is no longer necessary to pull the template from the network since we have (if we chose to) set the editable areas in session. IT WOULD BE BETTER TO USE CURL HERE! - if we did pull it
			#$file=@file($TemplateLocationURL);
			#$string=@ implode('',$file);
			//NOTE that we could and maybe should flush out all of the editable areas not related to the current rName set
		}
		//now build existing editable regions (in session)
		if(count($_SESSION['mail'][$acct]['templates'][$ID]['rName'])>0){
			foreach($_SESSION['mail'][$acct]['templates'][$ID]['rName'] as $name){
				//insert the region into the database
				$region=$_SESSION['mail'][$acct]['templates'][$ID]['r'][$name];
				if($varid=q("SELECT ID FROM relatebase_mail_profiles_vars WHERE Profiles_ID='$ID' AND  Name='EditableArea' AND Ky='$name'", O_VALUE)){
					q("UPDATE relatebase_mail_profiles_vars SET Editor='".sun()."', Val='$region' WHERE ID=$varid");
				}else{
					q("INSERT INTO relatebase_mail_profiles_vars SET 
					CreateDate=NOW(),
					Creator='".sun()."',
					EditDate='$timeStamp',
					Profiles_ID='$ID',
					Name='EditableArea',
					Ky='$name',
					Notes='mail_profile_01_exe.php line ".__LINE__."',
					Val='$region'");
				}
				$fl=__FILE__; $ln=__LINE__-11;
				prn($qr);
			}
		}
	}
	if(false){
		//2006-09-15: relatebase_mail_profiles_vars MUST have a 4-way unique index
		$a=q("SHOW INDEXES FROM relatebase_mail_profiles_vars", O_ARRAY);
		$fourWay=0;
		foreach($a as $n=>$v){
			if($v['Key_name']=='Profiles_IDNameIdxKy'){
				$fourWay++;
				$fourWayCols[]=$v['Column_name'];
			}
		}
		if($fourWay!==4){
			mail($adminEmail,'Adjusting indexes on line '.__LINE__. ' of file mail_profile_01_exe.php for acct '.$acct,'',$fromHdrBugs);
			echo 'Adjusting indexes!';
			q("ALTER TABLE relatebase_mail_profiles_vars DROP INDEX Profiles_IDNameIdxKy", ERR_ECHO);
			q("ALTER TABLE `relatebase_mail_profiles_vars` ADD UNIQUE `Profiles_IDNameIdxKy` ( `Profiles_ID` , `Name` , `Idx` , `Ky` )", ERR_ECHO);
		}
	}
	//----------------------------
	switch($RecipientSource){
		case 'complex':
			//we save the complex query
			if($varid=q("SELECT ID FROM relatebase_mail_profiles_vars WHERE Profiles_ID='$ID' AND Name='ComplexQuery' AND (Idx='' OR Idx IS NULL) AND (Ky='' OR Ky IS NULL)", O_VALUE)){
				q("UPDATE relatebase_mail_profiles_vars SET
				Editor='".sun()."',
				Profiles_ID='$ID',
				Name='ComplexQuery',
				Idx='',
				Ky='',
				Val='$ComplexQuery',
				Notes='mail_profile_01_exe.php, line ".__LINE__."' WHERE ID='$varid'", O_INSERTID);
				prn($qr);
			}else{
				$varid=q("INSERT INTO relatebase_mail_profiles_vars SET
				CreateDate=NOW(),
				Creator='".sun()."',
				Profiles_ID='$ID',
				Name='ComplexQuery',
				Idx='',
				Ky='',
				Val='$ComplexQuery',
				Notes='mail_profile_01_exe.php, line ".__LINE__."'", O_INSERTID);
			}
			prn($qr);
		break;
		case 'manual':
			//we save the complex query
			if($varid=q("SELECT ID FROM relatebase_mail_profiles_vars WHERE Profiles_ID='$ID' AND Name='ManualList' AND (Idx='' OR Idx IS NULL) AND (Ky='' OR Ky IS NULL)", O_VALUE)){
				$varid=q("UPDATE relatebase_mail_profiles_vars SET
				Editor='".sun()."',
				Profiles_ID='$ID',
				Name='ManualList',
				Idx='',
				Ky='',
				Val='$ManualList',
				Notes='mail_profile_01_exe.php, line ".__LINE__."' WHERE ID='$varid'", O_INSERTID);
			}else{
				$varid=q("INSERT INTO relatebase_mail_profiles_vars SET
				CreateDate=NOW(),
				Creator='".sun()."',
				Profiles_ID='$ID',
				Name='ManualList',
				Idx='',
				Ky='',
				Val='$ManualList',
				Notes='mail_profile_01_exe.php, line ".__LINE__."'", O_INSERTID);
			}
			prn($qr);
		break;
	}
	//now we save the subject line
	if($varid=q("SELECT ID FROM relatebase_mail_profiles_vars WHERE Profiles_ID='$ID' AND  Name='Subject'", O_VALUE)){
		q("UPDATE relatebase_mail_profiles_vars SET Editor='".sun()."', Val='$Subject' WHERE ID=$varid");
	}else{
		q("INSERT INTO relatebase_mail_profiles_vars SET 
		CreateDate=NOW(),
		Creator='".sun()."',
		Profiles_ID='$ID',
		Name='Subject',
		Notes='mail_profile_01_exe.php line ".__LINE__."',
		Val='$Subject'");
	}
	prn($qr);
	
	//save the batch record comment
	if($varid=q("SELECT ID FROM relatebase_mail_profiles_vars WHERE Profiles_ID='$ID' AND Name='BatchRecordComment' AND (Idx='' OR Idx IS NULL) AND (Ky='' OR Ky IS NULL)", O_VALUE)){
		$varid=q("UPDATE relatebase_mail_profiles_vars SET
		Editor='".sun()."',
		Profiles_ID='$ID',
		Name='BatchRecordComment',
		Idx='',
		Ky='',
		Val='$BatchRecordComment',
		Notes='mail_profile_01_exe.php, line ".__LINE__."' WHERE ID='$varid'", O_INSERTID);
	}else{
		$varid=q("INSERT INTO relatebase_mail_profiles_vars SET
		CreateDate=NOW(),
		Creator='".sun()."',
		Profiles_ID='$ID',
		Name='BatchRecordComment',
		Idx='',
		Ky='',
		Val='$BatchRecordComment',
		Notes='mail_profile_01_exe.php, line ".__LINE__."'", O_INSERTID);
	}
	prn($qr);

	//gulp - we save the attachments
	q("DELETE FROM relatebase_mail_profiles_vars WHERE Profiles_ID='$ID' AND Name='AttachmentList'");
	if($AttachmentList){
		$i=0;
		foreach($AttachmentTree_ID as $n=>$Tree_ID){
			$i++;
			//we want to have idx=1,2,..n, Ky=tree_id, Val=logic if any or (true)
			q("INSERT INTO relatebase_mail_profiles_vars SET
			CreateDate=NOW(),
			Creator='".sun()."',
			Profiles_ID='$ID',
			Name='AttachmentList',
			Idx=$i,
			Ky='".$Tree_ID."',
			Val='".$AttachmentLogic[$n]."',
			Notes='line ".__LINE__."'");
		}
	}

	//next we declare the email columns if applicable (or delete if not)*not doen
	q("REPLACE INTO relatebase_mail_profiles_vars SET EditDate='$timeStamp',
	CreateDate=NOW(),
	Creator='".sun()."',
	Profiles_ID = '$ID',
	Name='EmailColumns',
	Idx='',
	Ky='',
	Val='$EmailColumns',
	Notes='mail_profile_01_exe.php,2004-07-19'");
	prn($qr);
	
	//2004-09-28 required fields -- note var will persist regardless of recip method
	if($x=$_SESSION['mail'][$acct]['templates'][$ID]['advanced']['RequiredFields']){
		q("REPLACE INTO relatebase_mail_profiles_vars SET EditDate='$timeStamp',
		CreateDate=NOW(),
		Creator='".sun()."',
		Profiles_ID = '$ID',
		Name='RequiredFields',
		Idx='',
		Ky='',
		Val='".
		addslashes($x)."',
		Notes='mail_profile_01_exe.php,2004-09-28'");
		prn($qr);
	}else{
		q("DELETE FROM relatebase_mail_profiles_vars WHERE Profiles_ID='$ID' AND Name='RequiredFields'");
		prn($qr);
	}
	?><script language="javascript" type="text/javascript">
	window.parent.detectChange=0;
	</script><?php
	error_alert('done');
}else if($submode=='sendbatch' || $submode=='previewbatch'){
    // 2017-12-25 - reviewed and walked through
	//we want to check for errors via an include page, then post the parent again to a new file where the process takes place
	//there are actually two levels of ec return: 1) OK to save, 2) OK to send
	require($MASTER_COMPONENT_ROOT.'/mailer_profile_error_checking_v100.php');
	//Added 2004-12-14, for test email feature
	if(!empty($testMode)){
		if(!preg_match('/^[_a-zA-Z0-9-]+(\.([_a-zA-Z0-9-])*)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)+$/i',$TestEmail)){
			?><script language="javascript" type="text/javascript">
			alert('Your test email address does not appear to be valid.\nEnter a valid email address, or remove the email, which will send the batch regularly');
			try{
			window.parent.g('mailProfile_i_emailDelivery').onclick();
			window.parent.g('TestEmail').focus();
			}catch(e){ }
			</script><?php
			$assumeErrorState=false;
			exit;
		}
		if(!is_numeric($TestEmailBatch) || $TestEmailBatch<1 || $TestEmailBatch>100){
			?><script language="javascript" type="text/javascript">
			alert('To run test emails, you must specify a valid number between 1 and 100');
			try{
			window.parent.g('mailProfile_i_emailDelivery').onclick();
			window.parent.g('TestEmailBatch').focus();
			}catch(e){ }
			</script><?php
			$assumeErrorState=false;
			exit;
		}
	}
    if($formOK){
		require($MASTER_COMPONENT_ROOT.'/mailer_profile_process_v100.php');
		eOK();
	}else{
		?><script language="javascript" type="text/javascript">
		var err='<?php 
		//this system will give them one error at a time to fix
		//we go for 1 errors first because were outputting not saving
		if($err[1]){
			foreach($err[1] as $v){
				echo str_replace('\\\\n','\\n',js_safe_01($v));
				break;
			}
		}
		if($err[2]){
			foreach($err[2] as $v){
				echo str_replace('\\\\n','\\n',js_safe_01($v));
				break;
			}
		}
		?>';
		alert(err);
		</script><?php
		eOK();
	}
	$assumeErrorState=false;
	exit;
}
