<?php
session_start();
$localSys[scriptGroup]='mailer';
$localSys[scriptID]='MPM';
$localSys[scriptVersion]='1.0';
$localSys[modules]='ALL';//only mail module can access this page
$localSys[accessLevel]='User';
$localSys[pageType]='Properties Window';
$localSys[rootLocation]='/client/admin';
$localSys[rootFileName]='mail_profile.php';
$localSys[acctSwitchable]='0';

require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
//includes
require('../../admin/general_00_includes.php');
require('mail_00_includes.php');
$qx['defCnxMethod']=C_DEFAULT;


//connection changes, globals must be on
require('../../systeam/php/auth_v200.php');
!strlen($Profiles_ID)?$Profiles_ID=0:'';

/*************
sample form post
Array
(
    [RecipientMethod] => import
    [Views_ID] => 
    [ViewEmialColumns] => 
    [OverrideViewFilters] => 
    ['ComplexQuery'] =>
    [recipientMethods_status] => import
    [ManualList] => 
    [ImportType] => csv
    [EmailColumns] => Column 1
    [filePresent] => 1
    [mode] => sendbatch
    --- [HTMLOrText] => 1 -- now this is in session from composer
    [Composition] => template
    [Files_ID] => 
    [TemplateMethod] => url
    [TemplateLocationURL] => http://www.relatebase.com/Templates/mail/mail_sample.dwt
    [select] => dw40
    [FromName] => Samuel Fullman
    [FromEmail] => sam-git@compasspointmedia.com
    [ReplyToName] => (optional)
    [ReplyToEmail] => (optional)
    [BounceEmail] => (optional)
    [DefaultBatchName] => Campaign 001
    [BatchRecordEmail] => 
    [BatchRecordComment] => 
    [RecordVersion] => 
    [LastUsageTime] => 
    [nullmailProfile_status] => emailRecords
)
****************/

//prn($_POST);
//presumes no flaws in data

//first we do the work of determining recipient data source, max line, record count, etc.
switch($RecipientMethod){
	case 'group':
		unset($ids, $groups);
		$ids=array();
		$groups=array();
		//------------------------------------------------------------------
		function get_group_members_ids($group){
			global $ids, $groups;
			if(!trim($group)) return;
			if($a=q("SELECT
				a.ID AS Contacts_ID, b.ID AS Groups_ID, a.Email, b.Name
				FROM
				addr_u
				LEFT JOIN addr_contacts a ON u=1 AND a.Active=1
				LEFT JOIN addr_groups b ON u=2 AND b.Active=1
				LEFT JOIN addr_ContactsGroups c ON a.ID = c.Contacts_ID AND c.Groups_ID=$group
				LEFT JOIN addr_GroupsGroups d ON b.ID = d.Child_groups_ID AND d.Parent_groups_id=$group
				WHERE u <3 AND (c.Groups_ID=$group OR d.Parent_groups_id = $group)", O_ARRAY)){
				foreach($a as $v){
					if($v['Contacts_ID']){
						if(!in_array($v['Contacts_ID'], $ids)) $ids[]=$v['Contacts_ID'];
					}else if($v['Groups_ID']){
						if(!in_array($v['Groups_ID'], $groups)) get_group_members_ids($v['Groups_ID']);
					}
				}
			}
		}
		//------------------------------------------------------------------
		foreach($Groups_ID as $group){
			if(!trim($group)) return;
			if(!in_array($group, $groups)) get_group_members_ids($group);
		}
		//2006-07-16: NOTE this is a slow query
		if(count($ids) && $groupQueryArray=q("SELECT Email, Email2, addr_contacts.* FROM addr_contacts WHERE ID IN(".implode(',',$ids).") ORDER BY LastName, FirstName", O_ARRAY)){
			$rowCount=count($groupQueryArray);
		}else{
			//no records
			$rowCount=0;
		}
		$_emailColumns=array(0,1);
	break;
	case 'import':
		//get the email columns
		$str=preg_replace('/Column(\s|-)/i','',trim($EmailColumns));
		$a=explode(',',$str);
		foreach($a as $v){
			//set to zero-based
			$_emailColumns[]=trim($v)-1;
		}
		switch($ImportType){
			case 'auto':
				//here we must auto-determine the type of file
				#$ImportType=tab | csv | xls | qbkscust, iow we change string value
								
			case 'tab':
			
			case 'csv':
				$fp=@fopen("$VOS_ROOT/$acct/tmp_mailprofile".$Profiles_ID.".txt",'r');

				//get the file into a string
				$temp=@file("$VOS_ROOT/$acct/tmp_mailprofile".$Profiles_ID.".txt");
				$rowCount=count($temp)-($ImportHeaders?1:0); //we don't count first header data
				if(is_array($temp)){
					foreach($temp as $v){
						if(strlen($v)>$maxLine)$maxLine=strlen($v);
					}
					$maxLine+=3;
				}
				//destroy file array from memory
				unset($temp);

				if($rowCount<1){
					//no records present in file, how'd we get this far
					?><script>alert('Undetermined error, no records in imported file');</script><?php
					exit;
				}
			case 'xls':
			break;
		}
	break;
	case 'view':
		?><script>alert('View method not developed')</script><?php
		exit;
	break;
	case 'complex':
		//get the email columns
		$str=preg_replace('/Column(\s|-)/i','',trim($EmailColumns));
		$a=explode(',',$str);
		foreach($a as $v){
			//set to zero-based
			$_emailColumns[]=trim($v)-1;
		}
		//get the rowCount
		$result=q(stripslashes($ComplexQuery));
		if(!($rowCount=mysqli_num_rows($result))){
			exit("<script defer>alert('No records found for the SQL (Structured Query Language) Query')</script>");
		}
	break;
	case 'manual':
		//get data in array
		$buffer=preg_split("/[\n\r]+/",trim($ManualList));
		unset($ManualList);
		$ManualList=$buffer;
		foreach($ManualList as $n=>$v){
			$ManualList[$n]=trim($v);
		}
		//we don't need maxLine for a manual list
		$rowCount=count($ManualList);
	break;
}

//---------------------SECTION TWO: Loop through the records -----------------------------
/***
at this point we need $_emailColumns to have at least one element matching a key in $rd

todo: prge match on email below
***/

//convert HTMLOrText parameter -- 'plain' is a relic from enhanced_mail()

//prn($_SESSION['mail'][$acct]['templates'][$Profiles_ID],1);
if(isset($_SESSION['mail'][$acct]['templates'][$Profiles_ID]['HTMLOrText'])){
	$HTMLOrText=$_SESSION['mail'][$acct]['templates'][$Profiles_ID]['HTMLOrText'];
}else if($Composition=='template'){
	$HTMLOrText='1';
}else{
	$HTMLOrText='0';
}
$HTMLOrText=='1' ? $sendType='html' : $sendType='plain';

if($mode!=='previewbatch')echo "SENDING EMAIL BATCH ..<br />
NOTICE! Do not close this window or the send process will be terminated.  Depending on the size of emails (and esp. file attachments), sending can take up to 1.5 seconds per email.  When the process is completed this page will report the stop time<br />
<br />";

if($mode!=='previewbatch')echo "<pre>";
if($mode!=='previewbatch'){
	$startTime=date('Y-m-d H:i:s');
	echo '<br />started at ' . $startTime . '<br />';
}


/*---------------------------- BATCH ENTRY ------------------------------
2004-07-19: At this point be enter into relatebase_mail_batches and mail_batches_logs

If you uncheck this option, a batch report will not be stored in your system.  If there is a problem during transmission, you will not be able to resume your batch in mid-stream.  Also, you will not be able to track email responses with the RelateBase mail system.  Continue?

Please note that a report will still be sent to so@aol.com, however the report will not include the emails the batch was actually sent to.

------------------------------------------------------------------------*/
if($BatchRecord || $BatchRecordEmail){
	//get root batch name
	if($DefaultBatchName && $DefaultBatchNameAutoinc){
		//that's the root
		$root=stripslashes($DefaultBatchName);
	}else if(trim($BatchName)){
		$root=stripslashes($BatchName);
	}else{
		//we use the name of the profile
		$root=substr(stripslashes($Name),0,40);
	}
	if(!trim($root))$root=rand_alpha(8);
	//function sql_autoinc_text($table, $field, $root, $where='', $pad=0, $cnx=''){

	$BatchName=sql_autoinc_text('relatebase_mail_batches','Name',$root, "Profiles_ID".($Profiles_ID==0?" IS NULL":"=$Profiles_ID"), 2);
	
	//generate a batch number (receipt)
	$sql="SELECT COUNT(*) AS Ct from relatebase_mail_batches";
	$fl=__FILE__;$ln=__LINE__ +1;
	$btchCt=q($sql);
	$rd=mysqli_fetch_array($btchCt);
	$seq=str_pad($rd['Ct'], 5, "0", STR_PAD_LEFT);
	$receipt=date('y-m-d h:i ').$seq.'-'.$acct;
	
	//depending on method, we set null values
	$iComplexQuery='NULL';
	$iViews_ID='NULL';
	switch($RecipientMethod){
		case 'view':
		$iViews_ID=$Views_ID;
		break;
		case 'complex':
		$iComplexQuery="'".$ComplexQuery."'";
		break;
	}
	//get file name
	if($Composition=="template" && $TemplateMethod=="file" && $Files_ID){
		$fl=__FILE__;$ln=__LINE__ +1;
		$a=q("SELECT FROM relatebase_files WHERE Files_ID='$Files_ID'", O_ROW);
		//prn($qr);
		$FileName= addslashes($a['LocalPath'].'/'. $a['LocalFileName']);
	}
	
	//insert the batch record
	$fl=__FILE__;$ln=__LINE__ +1;
	$id=q("INSERT INTO relatebase_mail_batches SET
	CreateDate = '$dateStamp',
	Creator = '".($cu?$cu:$acct)."',
	EditDate = '$timeStamp',
	Profiles_ID = ".($Profiles_ID==0?"NULL":$Profiles_ID).",
	Name = '$BatchName',
	BatchNumber = '$receipt',
	RecipientSource = '$RecipientMethod',
	Views_ID = $iViews_ID,
	ComplexQuery = $iComplexQuery,
	HTMOrText = '$HTMLOrText',
	Composition = '$Composition',
	TemplateLocationURL = '".($Composition=="template" && $TemplateMethod=="url"?$TemplateLocationURL:'')."',
	Files_ID = ".($Composition=="template" && $TemplateMethod=="file" && strlen($Files_ID)?$Files_ID:'NULL').",
	FileName = '".$FileName."',
	Subject = '".addslashes($_SESSION['mail'][$acct]['templates'][$Profiles_ID][subj])."',
	Body = NULL, /** we insert this later **/
	FromName = '$FromName',
	FromEmail = '$FromEmail',
	ReplyToName = '$ReplyToName',
	ReplyToEmail = '$ReplyToEmail',
	Importance = '$Importance',
	AttachedVCard = '$AttachVCard',
	ReturnReceipt = '$ReturnReceipt',
	StartTime = '$dateStamp', /** stop time to be entered later **/
	BounceEmail = '$BounceEmail',
	BatchRecordEmail = '$BatchRecordEmail',
	BatchNotes = '$BatchRecordComment',
	RecordVersion = '1.0',
	MailerVersion  = '1.0'", O_INSERTID);
	/*** add always preview ***/
	//prn($qr);
	
	//last usage time in session and db
	if($Profiles_ID>0){
		$_SESSION['mail'][$acct]['templates'][$Profiles_ID]['lastUsageTime']=$dateStamp;
		$sql="UPDATE relatebase_mail_profiles SET LastUsageTime = '$dateStamp' WHERE ID='$Profiles_ID'";
		$fl=__FILE__;$ln=__LINE__ +1;
		q($sql);
		//prn($qr);
	}
}
//get file attachments declared
if(trim($AttachmentList)){
	$attachments=explode(',',$AttachmentList);
	foreach($attachments as $attachment){
		if(!trim($attachment))continue;
		$sql="SELECT LocalFileName, VOSFileName FROM relatebase_files WHERE ID='$attachment'";
		$fl=__FILE__;$ln=__LINE__ +1;
		$result=mysqli_query($db_cnx, $sql) or sql_handle_exception($fl,$ln);
		if(!mysqli_num_rows($result)){
			//the RBVOS record has been deleted
			//send alert to RB Staff
			$fileErr[$attachment]='An attachment ID was passed in a mail profile post but no record was located.  The file was most likely deleted between proof and send but highly unlikely!';
			mail($errReportEmail1,'Attachment ID in MPM passed, but not present in VOS',
			"Account: $acct
			Time ".date('Y-m-d H:i:s')."
			Mail Profile: $Profiles_ID
			User: $cu", "From: bugreports@relatebase.com");
			continue;
		}
		$rdAttach=mysqli_fetch_array($result);
		if(!file_exists("$VOS_ROOT/$acct/".$rdAttach[VOSFileName])){
			//VOS and files are not in synch
			$fileErr[$attachment]='An attachment ID was passed in a mail profile post and the record was present; however the actual file does not exist in the account folder.  Check for file and VOS folder presence and proper permissions';
			mail($errReportEmail1,'Attachment ID in MPM passed, record present, but file not present in VOS folder',
			"Account: $acct
			Time ".date('Y-m-d H:i:s')."
			Mail Profile: $Profiles_ID
			User: $cu", "From: bugreports@relatebase.com");
			continue;
		}
		$fileArrayAll[]="$VOS_ROOT/$acct/".$rdAttach[VOSFileName];
		$fileArrayNameAll["$VOS_ROOT/$acct/".$rdAttach[VOSFileName]] = $rdAttach[LocalFileName];
	}
}
/*$fileArrayAll[]="/home/cpm052/public_html/resources/pdf/Order-Form-New-Pricing-Effective-May-1st-2008.pdf";
$fileArrayNameAll["/home/cpm052/public_html/resources/pdf/Order-Form-New-Pricing-Effective-May-1st-2008.pdf"]="Order-Form-New-Pricing-Effective-May-1st-2008.pdf";
*/

//for now all recipients get all attachments
$fileArray=$fileArrayAll;
$fileArrayName=$fileArrayNameAll;
$emailSentList=array();
$i=0;
//assume logic compliation needed initially
$bodyCompilationNeeded=true;
$subjectCompilationNeeded=true;

//row index set to 1, only used though if mode=previewbatch
if(!$rowIdx)$rowIdx=1;
error_alert($RecipientMethod);
while($rd=get_recipient_data_row($RecipientMethod)){
	$i++;
	//we allow max 20 seconds per iteration or something is wrong
	set_time_limit(20);
	

	//DEVNOTES 2004-07-12: The fieldlist can be declared from the first dataset
	/**-------------
	This is not really well-developed and needs to be addressed when we get to security of fields (using a view as a recipient source
	
	------------------------------------------------------------------**/
	if($i==1){
		foreach($rd as $n=>$v){
			$fieldList[]=$n;
		}
		if($RecipientMethod=='complex'){
			//not sure what action is required
		
		}else if($RecipientMethod=='import' && $ImportHeaders){
			//need to translate the array emailCols to text header names
			foreach($_emailColumns as $v){
				$ec2[]=$headers[$v];
			}
			unset($_emailColumns);
			$_emailColumns=$ec2;
			//we skip the header row
			//if($RecipientMethod!=='complex')continue;
		}else if($RecipientMethod=='manual'){
			$_emailColumns[]=0;
		}
	}
	//now see if there are any email columns available
	unset($emails);
	$emails=array();
	foreach($_emailColumns as $v){
		//the function converts a header like First Name to FirstName
		$key=preg_replace('/\s*/','',$v);
		if(preg_match_all('/[_a-zA-Z0-9-]+(\.([_a-zA-Z0-9-])*)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)+/i',$rd[$key],$a)){	
			for($j=0;$j<count($a[0]);$j++){
				$x=$emails[]=$a[0][$j];
			}
		}else{
			//bad emails
		}
	}
	//now we get the uncompiled message subject and body
	if($i==1){
		$emailSubject=get_email_subject($Profiles_ID);
		$emailBody=get_email_body($Profiles_ID);
		$sql="UPDATE relatebase_mail_batches SET Body='".addslashes($emailBody)."' WHERE ID = '$id'";
		$fl=__FILE__;$ln=__LINE__ +1;
		$result=mysqli_query($db_cnx, $sql) or sql_handle_exception($fl,$ln);
	}

	//If this happens we need to report this row was skipped in batch report
	if(!count($emails)){
		if($mode!=='previewbatch')echo "Record $i skipped due to missing or invalid email<br />";
		continue;
	}
	/**------------------ DEVNOTES 2004-07-12 ---------------------------
	We really need to develop a fieldlist
	
	--------------------------------------------------------------------**/
	//compile the body and subject
	if($bodyCompilationNeeded){
		$logic_algorithm_i1['logicPresent']=false;
		$thisEmailBody=logic_algorithm_i1($emailBody);
		if($logic_algorithm_i1['logicPresent']==false) $bodyCompilationNeeded=false;
		$thisEmailBody = '?'.'>' .$thisEmailBody . '<'.'?php ';
		ob_start(); 
		eval($thisEmailBody); 
		$thisEmailBody = ob_get_contents(); 
		ob_end_clean();
	}else{
		$thisEmailBody=$emailBody;
	}
	if($subjectCompilationNeeded){
		$logic_algorithm_i1['logicPresent']=false;
		$thisEmailSubject=logic_algorithm_i1($emailSubject);
		if($logic_algorithm_i1['logicPresent']==false){
			/*** ----------------- DEVNOTES 2004-07-12 -----------------------------
			IF no compilation needed, we could send this out BCC once that feature can be made to work, which would cut down on server time tremendously...

			*** ---------------------------------------------------------------- ***/
			$subjectCompilationNeeded=false;
		}
		$thisEmailSubject = '?'.'>' .$thisEmailSubject . '<'.'?php ';
		ob_start(); 
		eval($thisEmailSubject); 
		$thisEmailSubject = ob_get_contents(); 
		ob_end_clean();
	}else{
		$thisEmailSubject=$emailSubject;
	}
	
	
	//----------- 2004-09-30: Show preview for the target row if previewing the batch --------------
	//rows before or after the rowIdx are excluded
	if($mode=='previewbatch'){
		if($i<$rowIdx){
			continue;
		}else if($i>$rowIdx){
			break;
		}
		$from=from_email($FromName,$FromEmail);
		//generate the control form

		echo '<script>function document.onkeypress(){if(event.keyCode==27){window.close();}}</script>';
		?>
		<form style="margin:0;" name="form1" method="post" action="">
		<div style="padding: 3 0 3 3; background-color:mintcream; border-bottom:1px solid #000000">
		<input type="button" name="Submit" value="Previous" <?php if($rowIdx==1)echo 'disabled';?> onClick="window.opener.d.rowIdx.value='<?php echo $rowIdx-1?>';window.opener.form1.mode.value='previewbatch';window.opener.form1.submit();return false;">&nbsp;&nbsp;&nbsp;		
		<input type="button" name="Submit2" value="Close" onClick="window.close();">&nbsp;&nbsp;&nbsp;		
		<input type="button" name="Submit3" value="Next" onClick="window.opener.d.rowIdx.value='<?php echo $rowIdx+1?>';window.opener.form1.mode.value='previewbatch';window.opener.form1.submit();return false;"><br />
		From: <?php echo htmlentities($from)?><br />
		To: <?php echo implode(', ',$emails);?><br />
		Subject: <?php echo $thisEmailSubject?><br />
		Row number: <?php echo $rowIdx?>&nbsp;&nbsp; Email Size: <?php echo number_format(strlen($thisEmailBody)/1024,2).'KB'?>&nbsp;&nbsp;
		<?php echo '<script>'?>
		var op=new Array();
		op['block']='none';
		op['none']='block';
		<?php echo '</script>'?>
		<a href='#' onClick="g('environment').style.display=op[g('environment').style.display];return false">Environment</a>
		<div id="environment" style="display:none; padding:5;"><?php prn($rd)?></div>
		</div></form>
		<title><?php echo 'Subject Line: '.$thisEmailSubject ?></title>
		<?php
		if($TextOrHTML){
			echo $thisEmailBody;
		}else{
			$thisEmailBodyText=nl2br($thisEmailBody);
			$thisEmailBodyText=str_replace("\t",'&nbsp;&nbsp;&nbsp;&nbsp;',$thisEmailBodyText);
			?><div style="font-family:Courier New, Monospace;font-size:13px;padding:0 3"><?php
			echo $thisEmailBodyText;
			?></div><?php
		}
	}



	//-------------------------------------------------------------------------------------
	
	
	/********
	2004-09-28: I regret I integrated things in so tightly here; the display (preview) is the main area needing work, see notes below
	********/
	foreach($emails as $v){
		//send the mail out
		//name
		$from=from_email($FromName,$FromEmail);
		//handle replyToEmail
		if(preg_match('/[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)+/',$ReplyToEmail)){
			if(preg_match('/"/',$ReplyToName) && $ReplyToName !=='(optional)'){
				$x='"'.str_replace('"','\"',$ReplyToName) . '"';
			}else{
				$x=$ReplyToName;
			}
			$replyTo='Reply-To: '.$x.'<'.$ReplyToEmail.'>';
		}
		$preHeaders=$replyTo;
		
		//we bounce the emails back to the sender, not the server, to minimize server load
		$bounce=($BounceEmail?$BounceEmail:$FromEmail);
		if($mode!=='previewbatch'){
			//2004-10-23 handle batch recovery
			if($CrossCheckBatch && $CrossCheckBatchNumber){
				$sql="SELECT Email FROM relatebase_mail_batches_logs WHERE Profiles_ID = '$Profiles_ID' AND Email = '$v' AND Batches_ID='$CrossCheckBatchNumber'";
				$resultCrossCheck=mysqli_query($db_cnx, $sql) or die(mysqli_error($db_cnx));
				if(mysqli_num_rows($resultCrossCheck)){
					echo "[Batch Recovery Mode]-already sent to $v<br />";
					continue;
				}
			}

			$sendCount++;
			#####($acct=='cpm018'?'sam-git@compasspointmedia.com':$v)
			//Added 2004-12-14: Test Email mode
			if($TestMode){
				if($sendCount<$TestEmailStart)continue;
				if($sendCount>$TestEmailBatch)break;
				$v=$TestEmail;
			}
			enhanced_mail(/*$v*/ 'sam-git@compasspointmedia.com', stripslashes($thisEmailSubject), $thisEmailBody, stripslashes($from), $sendType, (count($fileArray)?$fileArray:''), ($Importance==1?1:0), $preHeaders, '', '', ($bounce?$bounce:'') );
			
			//log emails used to send batches twice
			if(!in_array($v,$emailSentList)){
				$emailSentList[]=$v;
			}else{
				$duplicateEmails[$v]++;
			}
			$totalSize+=strlen($thisEmailBody)/1024;
			//log the event in db and in session
			$sql="SELECT Email FROM relatebase_mail_batches_logs WHERE
			Profiles_ID='$Profiles_ID' AND
			Batches_ID='$id' AND
			Email = '$v'";
			$result=mysqli_query($db_cnx, $sql) or sql_handle_exception($fl,$ln);
			if(!mysqli_num_rows($result)){
				//enter record	
				$sql="INSERT INTO relatebase_mail_batches_logs SET 
				Profiles_ID=$Profiles_ID,
				Batches_ID='$id',
				Email='$v',
				SentTime='".date('Y-m-d H:i:s')."'";
				$fl=__FILE__;$ln=__LINE__ +1;
				$result=mysqli_query($db_cnx, $sql) or sql_handle_exception($fl,$ln);
			}else{
				//this is a duplicate email being sent, we haven't set this up for entry.  When recoving a failed batch, suppose the batch would have sent 3 emails to the email address x, but only 2 were sent.  Unfortunately on the level of complexity we have, the third email will NOT be sent out.
			}
			
		}
		if($mode!=='previewbatch')echo 'Record '.$i.': '.$v . '<br />';
		flush();
	}
}

if($mode!=='previewbatch'){
	$stopTime=date('Y-m-d H:i:s');
	$sql="UPDATE relatebase_mail_batches SET StopTime='$stopTime' WHERE ID = '$id'";
	$fl=__FILE__;$ln=__LINE__ +1;
	$result=mysqli_query($db_cnx, $sql) or sql_handle_exception($fl,$ln);
	echo '<br />finished at ' . $stopTime;
}
ob_start();
include('mail_profile_08_batchreport.php');
$x=ob_get_contents();
ob_end_clean();
if($mode!=='previewbatch'){
	enhanced_mail($BatchRecordEmail, 'Batch Report', $x, 'batchreports@relatebase.com', 'HTML');
	echo "<br />";
	echo "Batch report mailed to ".$BatchRecordEmail;
}
?>