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



//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
//includes
require('../../admin/general_00_includes.php');
require('mail_00_includes.php');
$qx['defCnxMethod']=C_DEFAULT;


//connection changes, globals must be on
require('../../systeam/php/auth_v200.php');
switch (true){
	case $mode=='setAdvanced':
		//set required fields & other advanced settings
		if(!isset($Profiles_ID)){
			?><script defer>alert('No profile ID passed in setting advanced features');</script><?php
			exit;
		}
		$_SESSION['mail'][$acct]['templates'][$Profiles_ID]['advanced']['RequiredFields']=stripslashes($_POST['RequiredFields']);
		?><script defer>
		try{
			window.parent.opener.form1.ImportHeaders.checked=true;
			window.parent.close();
		}
		catch(e){
		}
		</script><?php
		exit;
	break;
	case $mode=='composeEmail':
		if(strlen($Profiles_ID) && $Profiles_ID!==0){
			//we have the value
		}else{
			if(!$SessionToken){
				?><script>alert('Malformed querystring request; need either a Profiles_ID value or SessionToken');</script><?php
				exit('Malformed querystring request; need either a Profiles_ID value or SessionToken');
			}
			$temp=quasi_resource_generic($acct, 'relatebase_mail_profiles', $SessionToken);
			if(q("SELECT ResourceType FROM $acct.relatebase_mail_profiles WHERE ID='$temp'", O_VALUE)=='1'){
				//this is from a saved document which has not been refreshed - virtual Profiles_ID
				$Profiles_ID=$temp;
			}else{
				//we set a quasi variable to be safe
				$quasiProfiles_ID=$temp;
				#for exe we also allow actual Profiles_ID to be set here
				$Profiles_ID=$temp;
			}
		}
		if($Profiles_ID!==0){
			//2006-07-16: this field was moved to comp window vs. main and will eventually be hidden for templates or VFS files
			q("UPDATE relatebase_mail_profiles SET
			HTMLOrText='$HTMLOrText'
			WHERE ID='$Profiles_ID'");
		}
		foreach($regions as $name=>$region){
			$_SESSION['mail'][$acct]['templates'][$Profiles_ID]['r'][strtolower($name)]=stripslashes($region);
			$rName[]=strtolower($name);
			//save in DB only if we have a profile ID
			if($Profiles_ID==0)continue;
			if($varid=q("SELECT ID FROM relatebase_mail_profiles_vars WHERE Profiles_ID='$Profiles_ID' AND  Name='EditableArea' AND Ky='$name'", O_VALUE)){
				q("UPDATE relatebase_mail_profiles_vars SET Val='$region' WHERE ID=$varid");
			}else{
				q("INSERT INTO relatebase_mail_profiles_vars SET 
				CreateDate='$dateStamp',
				EditDate='$timeStamp',
				Profiles_ID='$Profiles_ID',
				Name='EditableArea',
				Ky='$name',
				Notes='mail_profile_01_exe.php line ".__LINE__."',
				Val='$region'");
			}
			$fl=__FILE__; $ln=__LINE__-11;
			prn($qr);
		}
		//re-declare the current regions
		unset($_SESSION['mail'][$acct]['templates'][$Profiles_ID]['rName']);
		$_SESSION['mail'][$acct]['templates'][$Profiles_ID]['rName']=$rName;
		
		//update the type - html or text
		$_SESSION['mail'][$acct]['templates'][$Profiles_ID]['HTMLOrText']=$HTMLOrText;
		
		//update subject line
		$_SESSION['mail'][$acct]['templates'][$Profiles_ID]['subj']=stripslashes($Subject);
		if($Profiles_ID!==0){
			if($varid=q("SELECT ID FROM relatebase_mail_profiles_vars WHERE Profiles_ID='$Profiles_ID' AND  Name='Subject'", O_VALUE)){
				q("UPDATE relatebase_mail_profiles_vars SET Val='$Subject' WHERE ID=$varid");
			}else{
				q("INSERT INTO relatebase_mail_profiles_vars SET 
				CreateDate='$dateStamp',
				EditDate='$timeStamp',
				Profiles_ID='$Profiles_ID',
				Name='Subject',
				Notes='mail_profile_01_exe.php line ".__LINE__."',
				Val='$Subject'");
			}
			$fl=__FILE__; $ln=__LINE__-11;
			prn($qr);
		}
		
		//set the compile time for reference
		$_SESSION['mail'][$acct]['templates'][$Profiles_ID]['compileTime']=strtotime($dateStamp);
		
		?><script>
		//close the parent window, eventually put some status text in wpo
		try{
			window.parent.detectChange=0;
			window.parent.g('CompositionFinished').disabled=true;
		} catch(e){ }
		try{
			window.parent.opener.detectChange=1;
		} catch(e){ }
		window.parent.close();
		try{
			window.parent.opener.focus();
		}catch(e){  }
		</script><?php
	break;
	case $mode=='checkURL':
		eval( '$out=`curl -I '.$TemplateLocationURL.'`;' );
		prn($out);
		if(preg_match('/404 Not Found/i',$out)){
			?><script>
			alert('File cannot be found')
			</script><?php
			exit;
		}else{
			$str=implode('',file($TemplateLocationURL));
		}
		if(preg_match('/<'.'!-- #BeginEditable/i',$str) || preg_match('/<'.'!-- TemplateBeginEditable/i',$str)){
			?><script>alert('This is a valid URL to a template');</script><?php
		}else{
			?><script>alert('This is NOT a valid URL');</script><?php
		}
		exit;
	break;
	case $mode=='uploadfile':
		//file is saved as /home/rbase/private_files/relatebase_accts/acctname/tmp_mailprofile001.txt
		if(is_uploaded_file($_FILES['importfile']['tmp_name'])){
			//upload the file and insert the record
			echo $uploadOK = move_uploaded_file($_FILES['importfile']['tmp_name'],$VOS_ROOT."/".$_SESSION[currentConnection]."/tmp_mailprofile".$Profiles_ID.".txt");
			
			//we should check for file format as well, based on the specifications they gave
		}
		if($uploadOK){
			?><script>
			//change status of upload
			window.parent.g('statusMessage').innerHTML='';
			//re-enable OK button
			window.parent.g('ctrlOK').disabled=false;
			//set value of window.parent.opener -- File Currently Selected:
			window.parent.opener.g('fileCurrentlySelected').innerHTML='<?php echo $_FILES["importfile"]["name"]?>';
			window.parent.opener.g('FilePresent').value=1;
			//close window
			alert('Import File uploaded OK');
			window.parent.close();
			</script><?php
			exit;
		}else{
			?><script>
			//problem
			alert('File not uploaded correctly');
			</script><?php
			exit;
		}
	break;
	case $mode=='deleteProfile':
		q("DELETE FROM relatebase_mail_profiles WHERE ID = '$Profiles_ID'",C_DEFAULT);
		q("DELETE FROM relatebase_mail_profiles_vars WHERE Profiles_ID = '$Profiles_ID'",C_DEFAULT);
		if($batchIDs=q("SELECT ID FROM relatebase_mail_batches WHERE Profiles_ID='$Profiles_ID'", O_COL)){
			q("DELETE FROM relatebase_mail_batches WHERE Profiles_ID='$Profiles_ID'",C_DEFAULT);
			q("DELETE FROM relatebase_mail_batches_logs WHERE Batches_ID IN(" . implode(',',$batchIDs) . ")");
		}
	break;
}
?>