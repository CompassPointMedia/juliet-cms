<?php
//2013-12-19
if(!defined('mailstatus_sent'))define('mailstatus_sent',1);
if(!defined('mailstatus_apparentlyreceived'))define('mailstatus_apparentlyreceived',2);
if(!defined('mailstatus_returnreceipt'))define('mailstatus_returnreceipt',4);
if(!defined('mailstatus_repliedto'))define('mailstatus_repliedto',8);
if(!defined('mailstatus_viewed'))define('mailstatus_viewed',16);

//specialty functions
if(!function_exists('get_recipient_data_row'))
	require($FUNCTION_ROOT.'/function_get_recipient_data_row_v100.php');
if(!function_exists('logic_algorithm_i1'))
	require($FUNCTION_ROOT.'/function_logic_algorithm_v200.php');
if(!function_exists('sql_in'))
	require($FUNCTION_ROOT.'/function_sql_in_v100.php');
if(!function_exists('mail_merge_logic_i1'))
	require($FUNCTION_ROOT.'/function_mail_merge_logic_i1_v200.php');
if(!function_exists('string_analyzer_i1'))
	require($FUNCTION_ROOT.'/function_string_analyzer_i1.php');

if(!function_exists('get_email_subject')){
	//2011-05-22: simplified this to the form post
	function get_email_subject($profileID=''){
		return stripslashes($GLOBALS['Subject']);
	}
}
if(!function_exists('get_email_body')){
	//2011-05-22: simplified this to a single content region
	//2004-08-07: this function should re-wrap the editable regions with <!-- #BeginEditabl.. etc. for storage
	function get_email_body($profileID='', $options=array()){
		global $Profiles_ID;
		$id=(strlen($profileID)?$profileID:$Profiles_ID);
		if(true){
			if($GLOBALS['HTMLOrText']==1){
				if($GLOBALS['UseTrackingImage']){
					$u='http://'.$GLOBALS['HTTP_HOST'];
					$u.='/m.php?mode=verifyValidAddress&setStatus='.mailstatus_apparentlyreceived.'&BatchesContacts_ID={BatchesContacts_ID}&Email={CurrentEmailSent}';
				}
				$str='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>HTML Email</title>
<style type="text/css">
@import url("'.$u.'");
h1,h2,h3{
	font-family:Georgia, "Times New Roman", Times, serif;
	}
h1{
	font-size:170%;
	}
h2{
	font-size:140%;
	}
h3{
	font-size:119%;
	}
body{
	padding:10px 20px;
	font-family:Arial, Helvetica, sans-serif;
	}
body, td{
	font-size:14px;
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
</head>

<body>';
				$str.=stripslashes($GLOBALS['Content']);
				if($GLOBALS['UseTrackingImage']){
					$str.='<img src="'.($u.'&disposition=img').'" width="1" height="1" /></body></html>';
				}
				return $str;
			}else{
				return stripslashes($GLOBALS['Content']);
			}
		}else if($GLOBALS['Composition']=='blank'){
			//assume blank email desired -- if radio's are disabled, then Composition is not set
			$string = $_SESSION['mail'][$acct]['templates'][$id]['r']['_blank_email'];
		}else if($GLOBALS['Composition']=='template'){
			if($GLOBALS['TemplateMethod']=='url'){
				$string=implode('',file($GLOBALS['TemplateLocationURL']));
				//match editable region
		
				// DW 4.0
				$regexDW40 = '/<!-- (#|Template)'.'BeginEditable (name=)*"[^"]*" -->(.|\s)*?<!-- (#|Template)'.'EndEditable -->/i';
				$regexDW40a = '/<!-- (#|Template)'.'BeginEditable (name=)*"';
				$regexDW40b = '" -->(.|\s)*?<!-- (#|Template)'.'EndEditable -->/i';
				// XML type tag named editable
				$regexXML = '/<editable\s+[^>]*name\s*=\s*(("[^"]+")|(\'[^\']+\'))[^>]*>(.|\s)*?<\/editable>/i';
				ob_start();
				$a=(count($GLOBALS['regions']) ? $GLOBALS['regions'] : $_SESSION['mail'][$acct]['templates'][$id]['r']);
				foreach($a as $n=>$v){
					$string=preg_replace($regexDW40a.$n.$regexDW40b,$v,$string);
				}
				ob_end_clean();
			}else if($GLOBALS[TemplateMethod]=='file'){
			
			}
		}
		return $string;
	}
}
if(!function_exists('rand_alpha')){
	function rand_alpha($size=8){
		for($i=1;$i<=$size;$i++)$str.=chr(97+rand(0,25));
		return $str;
	}
}
if(!function_exists('get_group_members_ids')){
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
}
if(!function_exists('from_email')){
	function from_email($FromName,$FromEmail){
		if(trim($FromName)){
			if(preg_match('/"/',$FromName)){
				$x='"'.str_replace('"','\"',$FromName) . '"';
			}else{
				$x=$FromName;
			}
			$from=$x.'<'.$FromEmail.'>';
		}else if(trim($FromEmail)){
			$from=$FromEmail;
		}else{
			exit('No From email or name passed (name is optional)');
		}
		return $from;
	}		
}
