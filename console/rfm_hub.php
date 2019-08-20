<?php 
/*
NOTES this page:

*/

//identify this script/GUI
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['componentID']='main';


//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/rbrfm_01.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo h($adminCompany);?> Admin Suite</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" type="text/css" href="rbrfm_admin.css" />
<link rel="stylesheet" type="text/css" href="/Library/css/DHTML/dynamic_04_i1.css" />
<script src="/Library/js/global_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/common_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/forms_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/loader_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/contextmenus_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/console/console.js" language="javascript" type="text/javascript"></script>
<script language="JavaScript" type="text/javascript">
/* periwinkle coding 2.1 */
var thispage='<?php echo $thispage?>';
var thisfolder='<?php echo $thisfolder?>';
var ctime='<?php echo $ctime?>';
var PHPSESSID='<?php echo $PHPSESSID?>';
//for nav feature
var count='<?php echo $nullCount?>';
var ab='<?php echo $nullAbs?>';
var isEscapable=0;
var isDeletable=0;
var isModal=0;
var talks=0; //whether this page broadcasts overall state changes which other pages listen for
var listens=0;


</script>
<!-- InstanceEndEditable -->
</head>

<body>
<div id="mainContainer">
	<!-- InstanceBeginEditable name="admin_top" --><!-- #BeginLibraryItem "/Library/rbrfm_adminmenu_basic_01.lbi" --><?php
require($_SERVER['DOCUMENT_ROOT'].'/console/rbrfm_adminmenu_basic_02.php');
?><!-- #EndLibraryItem --><!-- InstanceEndEditable -->
	<!-- InstanceBeginEditable name="top_region" --><!-- InstanceEndEditable -->
	<div id="leftInset">
	<!-- InstanceBeginEditable name="left_inset" --><!-- InstanceEndEditable -->
	</div>
	<div id="mainBody">
	<!-- InstanceBeginEditable name="main_body" -->
	
    <h3>Accounting Hub</h3>
    <p>this is a on-the-fly list of accounting-related features as they are developed. Most are being developed using the systementry system for list and focus views of data</p>
    <p>[may not work correctly; somewhat old] <a href="rfm_coa.php" onclick="return ow(this.href,'l1_coa','700,800');">chart of accounts</a>  </p>
	
	<?php
	//clients profile
	if(!($Clients_Tables_ID=q("SELECT ID FROM system_tables WHERE SystemName='finan_clients'", O_VALUE))){
		$Clients_Tables_ID=q("INSERT INTO system_tables SET SystemName='finan_clients',
		Name='Clients',
		KeyField='ID',
		Description='Clients available for billing',
		Type='table',
		EditDate=NOW()", O_INSERTID);
	}
	if(!($Clients_Profiles_ID_=q("SELECT ID FROM system_profiles WHERE Tables_ID='$Clients_Tables_ID' AND Type='Data View' AND Identifier='default'", O_VALUE))){
		$Clients_Profiles_ID_=q("INSERT INTO system_profiles SET
		Tables_ID=$Clients_Tables_ID,
		Identifier='default',
		Type='Data View',
		Category='Financial Mgmt.',
		Name='Clients',
		Description='',
		CreateDate=NOW(),
		Creator='".sun()."',
		EditDate=NOW()", O_INSERTID);
	}


	//items profile
	if(!($Items_Tables_ID=q("SELECT ID FROM system_tables WHERE SystemName='finan_items'", O_VALUE))){
		$Items_Tables_ID=q("INSERT INTO system_tables SET SystemName='finan_items',
		Name='Items and Products',
		KeyField='ID',
		Description='Items for Invoices and Cash Sales',
		Type='table',
		EditDate=NOW()", O_INSERTID);
	}
	if(!($Items_Profiles_ID_=q("SELECT ID FROM system_profiles WHERE Tables_ID='$Items_Tables_ID' AND Type='Data View' AND Identifier='default'", O_VALUE))){
		$Items_Profiles_ID_=q("INSERT INTO system_profiles SET
		Tables_ID=$Items_Tables_ID,
		Identifier='default',
		Type='Data View',
		Category='E-commerce Mgmt.',
		Name='Items for Invoices and Cash Sales',
		Description='',
		Settings='YToyOntzOjEwOiJkYXRhb2JqZWN0IjthOjExOntpOjA7YjoxO3M6NzoiZGF0YXNldCI7czo1OiJpdGVtcyI7czoxMjoiZGF0YXNldEdyb3VwIjtzOjU6Iml0ZW1zIjtzOjE2OiJkYXRhc2V0Q29tcG9uZW50IjtzOjk6Iml0ZW1zTGlzdCI7czoxMjoiZGF0YXNldFF1ZXJ5IjtzOjU2OiJTRUxFQ1QgKiBGUk9NIGZpbmFuX2l0ZW1zIFdIRVJFIFJlc291cmNlVHlwZSBJUyBOT1QgTlVMTCI7czoyMjoiZGF0YXNldFF1ZXJ5VmFsaWRhdGlvbiI7czozMjoiYmQ0MTk5MTJjM2MzYjk4MzRhYmY3OTRhNTRkOGNkOGYiO3M6MTE6ImRhdGFzZXRGaWxlIjtzOjQxOiJjb21wXzEwMDBfc3lzdGVtZW50cnlfZGF0YW9iamVjdF92MTAwLnBocCI7czozMDoiZGF0YXNldEZvY3VzVmlld0RldmljZUZ1bmN0aW9uIjtzOjE4OiJzeXN0ZW1fZW50cnlfZm9jdXMiO3M6NzoiY29sdW1ucyI7YTozOTp7czoxMDoiQ3JlYXRlRGF0ZSI7YToxOntzOjY6ImhlYWRlciI7czoxMToiQ3JlYXRlIERhdGUiO31zOjY6IkFjdGl2ZSI7YToxOntzOjY6ImhlYWRlciI7czo2OiJBY3RpdmUiO31zOjEwOiJFeHBvcnREYXRlIjthOjE6e3M6NjoiaGVhZGVyIjtzOjExOiJFeHBvcnQgRGF0ZSI7fXM6MTQ6IlF1YW50aXR5T25IYW5kIjthOjE6e3M6NjoiaGVhZGVyIjtzOjE2OiJRdWFudGl0eSBPbiBIYW5kIjt9czozOiJTS1UiO2E6MTp7czo2OiJoZWFkZXIiO3M6MzoiU0tVIjt9czozOiJVUEMiO2E6MTp7czo2OiJoZWFkZXIiO3M6MzoiVVBDIjt9czo0OiJOYW1lIjthOjE6e3M6NjoiaGVhZGVyIjtzOjQ6Ik5hbWUiO31zOjExOiJEZXNjcmlwdGlvbiI7YToxOntzOjY6ImhlYWRlciI7czoxMToiRGVzY3JpcHRpb24iO31zOjQ6IlR5cGUiO2E6MTp7czo2OiJoZWFkZXIiO3M6NDoiVHlwZSI7fXM6OToiVW5pdFByaWNlIjthOjE6e3M6NjoiaGVhZGVyIjtzOjEwOiJVbml0IFByaWNlIjt9czoxMDoiVW5pdFByaWNlMiI7YToxOntzOjY6ImhlYWRlciI7czoxMToiVW5pdCBQcmljZTIiO31zOjEwOiJVbml0UHJpY2UzIjthOjE6e3M6NjoiaGVhZGVyIjtzOjExOiJVbml0IFByaWNlMyI7fXM6MTA6IlVuaXRQcmljZTQiO2E6MTp7czo2OiJoZWFkZXIiO3M6MTE6IlVuaXQgUHJpY2U0Ijt9czo4OiJDYXRlZ29yeSI7YToxOntzOjY6ImhlYWRlciI7czo4OiJDYXRlZ29yeSI7fXM6MTE6IlN1YkNhdGVnb3J5IjthOjE6e3M6NjoiaGVhZGVyIjtzOjEyOiJTdWIgQ2F0ZWdvcnkiO31zOjEyOiJNYW51ZmFjdHVyZXIiO2E6MTp7czo2OiJoZWFkZXIiO3M6MTI6Ik1hbnVmYWN0dXJlciI7fXM6MTY6Ik1hbnVmYWN0dXJlcnNfSUQiO2E6MTp7czo2OiJoZWFkZXIiO3M6MTY6Ik1hbnVmYWN0dXJlcnNfSUQiO31zOjE2OiJTaG9ydERlc2NyaXB0aW9uIjthOjE6e3M6NjoiaGVhZGVyIjtzOjE3OiJTaG9ydCBEZXNjcmlwdGlvbiI7fXM6MTU6IkxvbmdEZXNjcmlwdGlvbiI7YToxOntzOjY6ImhlYWRlciI7czoxNjoiTG9uZyBEZXNjcmlwdGlvbiI7fXM6MTQ6Ildob2xlc2FsZVByaWNlIjthOjE6e3M6NjoiaGVhZGVyIjtzOjE1OiJXaG9sZXNhbGUgUHJpY2UiO31zOjU6IlByaWNlIjthOjE6e3M6NjoiaGVhZGVyIjtzOjU6IlByaWNlIjt9czo5OiJMaXN0UHJpY2UiO2E6MTp7czo2OiJoZWFkZXIiO3M6MTA6Ikxpc3QgUHJpY2UiO31zOjc6IlRheGFibGUiO2E6MTp7czo2OiJoZWFkZXIiO3M6NzoiVGF4YWJsZSI7fXM6ODoiVGF4YWJsZTIiO2E6MTp7czo2OiJoZWFkZXIiO3M6ODoiVGF4YWJsZTIiO31zOjg6IlRheGFibGUzIjthOjE6e3M6NjoiaGVhZGVyIjtzOjg6IlRheGFibGUzIjt9czo4OiJUYXhhYmxlNCI7YToxOntzOjY6ImhlYWRlciI7czo4OiJUYXhhYmxlNCI7fXM6MTM6IkltYWdlRmlsZU5hbWUiO2E6MTp7czo2OiJoZWFkZXIiO3M6MTU6IkltYWdlIEZpbGUgTmFtZSI7fXM6ODoiSXRlbXNfSUQiO2E6MTp7czo2OiJoZWFkZXIiO3M6ODoiSXRlbXNfSUQiO31zOjY6Ikxlbmd0aCI7YToxOntzOjY6ImhlYWRlciI7czo2OiJMZW5ndGgiO31zOjU6IldpZHRoIjthOjE6e3M6NjoiaGVhZGVyIjtzOjU6IldpZHRoIjt9czo1OiJEZXB0aCI7YToxOntzOjY6ImhlYWRlciI7czo1OiJEZXB0aCI7fXM6NjoiV2VpZ2h0IjthOjE6e3M6NjoiaGVhZGVyIjtzOjY6IldlaWdodCI7fXM6MTA6IlByb2R1Y3RVUkwiO2E6MTp7czo2OiJoZWFkZXIiO3M6MTE6IlByb2R1Y3QgVVJMIjt9czo4OiJJbWFnZVVSTCI7YToxOntzOjY6ImhlYWRlciI7czo5OiJJbWFnZSBVUkwiO31zOjg6IktleXdvcmRzIjthOjE6e3M6NjoiaGVhZGVyIjtzOjg6IktleXdvcmRzIjt9czoxMToiQWNjb3VudHNfSUQiO2E6MTp7czo2OiJoZWFkZXIiO3M6MTE6IkFjY291bnRzX0lEIjt9czoxMToiR3JvdXBMZWFkZXIiO2E6MTp7czo2OiJoZWFkZXIiO3M6MTI6Ikdyb3VwIExlYWRlciI7fXM6NToiTW9kZWwiO2E6MTp7czo2OiJoZWFkZXIiO3M6NToiTW9kZWwiO31zOjEzOiJQdXJjaGFzZVByaWNlIjthOjE6e3M6NjoiaGVhZGVyIjtzOjE0OiJQdXJjaGFzZSBQcmljZSI7fX1zOjE4OiJkYXRhc2V0QWN0aXZlVXNhZ2UiO2I6MTtzOjI0OiJkYXRhc2V0QWN0aXZlSGlkZUNvbnRyb2wiO2I6MDt9czo1OiJfcmF3XyI7czoyNzgxOiInZGF0YW9iamVjdCc9PmFycmF5KA0KCS8qIGFkZGVkIGJ5IHJvb3Rfc3lzdGVtZW50cnlfbGlzdC5waHAgYXQgMTIvMTYvMjAxMiBhdCAxMDo0M1BNIG9uIGluaXRpYWwgY2FsbC11cCAqLw0KCSdkYXRhc2V0QXV0b0J1aWxkJz4nMS4wLjAxJywNCgknZGF0YXNldCc9PidpdGVtcycsDQoJJ2RhdGFzZXRHcm91cCc9PidpdGVtcycsDQoJJ2RhdGFzZXRDb21wb25lbnQnPT4naXRlbXNMaXN0JywNCgknZGF0YXNldFF1ZXJ5Jz0+J1NFTEVDVCAqIEZST00gZmluYW5faXRlbXMgV0hFUkUgUmVzb3VyY2VUeXBlIElTIE5PVCBOVUxMJywNCgknZGF0YXNldFF1ZXJ5VmFsaWRhdGlvbic9PidiZDQxOTkxMmMzYzNiOTgzNGFiZjc5NGE1NGQ4Y2Q4ZicsDQoJJ2RhdGFzZXRGaWxlJz0+J2NvbXBfMTAwMF9zeXN0ZW1lbnRyeV9kYXRhb2JqZWN0X3YxMDAucGhwJywNCgknZGF0YXNldEZvY3VzVmlld0RldmljZUZ1bmN0aW9uJz0+J3N5c3RlbV9lbnRyeV9mb2N1cycsDQoJJ2NvbHVtbnMnPT5hcnJheSgNCgkJJ0NyZWF0ZURhdGUnPT5hcnJheSgNCgkJCSdoZWFkZXInPT4nQ3JlYXRlIERhdGUnLA0KCQkpLA0KCQknQWN0aXZlJz0+YXJyYXkoDQoJCQknaGVhZGVyJz0+J0FjdGl2ZScsDQoJCSksDQoJCSdFeHBvcnREYXRlJz0+YXJyYXkoDQoJCQknaGVhZGVyJz0+J0V4cG9ydCBEYXRlJywNCgkJKSwNCgkJJ1F1YW50aXR5T25IYW5kJz0+YXJyYXkoDQoJCQknaGVhZGVyJz0+J1F1YW50aXR5IE9uIEhhbmQnLA0KCQkpLA0KCQknU0tVJz0+YXJyYXkoDQoJCQknaGVhZGVyJz0+J1NLVScsDQoJCSksDQoJCSdVUEMnPT5hcnJheSgNCgkJCSdoZWFkZXInPT4nVVBDJywNCgkJKSwNCgkJJ05hbWUnPT5hcnJheSgNCgkJCSdoZWFkZXInPT4nTmFtZScsDQoJCSksDQoJCSdEZXNjcmlwdGlvbic9PmFycmF5KA0KCQkJJ2hlYWRlcic9PidEZXNjcmlwdGlvbicsDQoJCSksDQoJCSdUeXBlJz0+YXJyYXkoDQoJCQknaGVhZGVyJz0+J1R5cGUnLA0KCQkpLA0KCQknVW5pdFByaWNlJz0+YXJyYXkoDQoJCQknaGVhZGVyJz0+J1VuaXQgUHJpY2UnLA0KCQkpLA0KCQknVW5pdFByaWNlMic9PmFycmF5KA0KCQkJJ2hlYWRlcic9PidVbml0IFByaWNlMicsDQoJCSksDQoJCSdVbml0UHJpY2UzJz0+YXJyYXkoDQoJCQknaGVhZGVyJz0+J1VuaXQgUHJpY2UzJywNCgkJKSwNCgkJJ1VuaXRQcmljZTQnPT5hcnJheSgNCgkJCSdoZWFkZXInPT4nVW5pdCBQcmljZTQnLA0KCQkpLA0KCQknQ2F0ZWdvcnknPT5hcnJheSgNCgkJCSdoZWFkZXInPT4nQ2F0ZWdvcnknLA0KCQkpLA0KCQknU3ViQ2F0ZWdvcnknPT5hcnJheSgNCgkJCSdoZWFkZXInPT4nU3ViIENhdGVnb3J5JywNCgkJKSwNCgkJJ01hbnVmYWN0dXJlcic9PmFycmF5KA0KCQkJJ2hlYWRlcic9PidNYW51ZmFjdHVyZXInLA0KCQkpLA0KCQknTWFudWZhY3R1cmVyc19JRCc9PmFycmF5KA0KCQkJJ2hlYWRlcic9PidNYW51ZmFjdHVyZXJzX0lEJywNCgkJKSwNCgkJJ1Nob3J0RGVzY3JpcHRpb24nPT5hcnJheSgNCgkJCSdoZWFkZXInPT4nU2hvcnQgRGVzY3JpcHRpb24nLA0KCQkpLA0KCQknTG9uZ0Rlc2NyaXB0aW9uJz0+YXJyYXkoDQoJCQknaGVhZGVyJz0+J0xvbmcgRGVzY3JpcHRpb24nLA0KCQkpLA0KCQknV2hvbGVzYWxlUHJpY2UnPT5hcnJheSgNCgkJCSdoZWFkZXInPT4nV2hvbGVzYWxlIFByaWNlJywNCgkJKSwNCgkJJ1ByaWNlJz0+YXJyYXkoDQoJCQknaGVhZGVyJz0+J1ByaWNlJywNCgkJKSwNCgkJJ0xpc3RQcmljZSc9PmFycmF5KA0KCQkJJ2hlYWRlcic9PidMaXN0IFByaWNlJywNCgkJKSwNCgkJJ1RheGFibGUnPT5hcnJheSgNCgkJCSdoZWFkZXInPT4nVGF4YWJsZScsDQoJCSksDQoJCSdUYXhhYmxlMic9PmFycmF5KA0KCQkJJ2hlYWRlcic9PidUYXhhYmxlMicsDQoJCSksDQoJCSdUYXhhYmxlMyc9PmFycmF5KA0KCQkJJ2hlYWRlcic9PidUYXhhYmxlMycsDQoJCSksDQoJCSdUYXhhYmxlNCc9PmFycmF5KA0KCQkJJ2hlYWRlcic9PidUYXhhYmxlNCcsDQoJCSksDQoJCSdJbWFnZUZpbGVOYW1lJz0+YXJyYXkoDQoJCQknaGVhZGVyJz0+J0ltYWdlIEZpbGUgTmFtZScsDQoJCSksDQoJCSdJdGVtc19JRCc9PmFycmF5KA0KCQkJJ2hlYWRlcic9PidJdGVtc19JRCcsDQoJCSksDQoJCSdMZW5ndGgnPT5hcnJheSgNCgkJCSdoZWFkZXInPT4nTGVuZ3RoJywNCgkJKSwNCgkJJ1dpZHRoJz0+YXJyYXkoDQoJCQknaGVhZGVyJz0+J1dpZHRoJywNCgkJKSwNCgkJJ0RlcHRoJz0+YXJyYXkoDQoJCQknaGVhZGVyJz0+J0RlcHRoJywNCgkJKSwNCgkJJ1dlaWdodCc9PmFycmF5KA0KCQkJJ2hlYWRlcic9PidXZWlnaHQnLA0KCQkpLA0KCQknUHJvZHVjdFVSTCc9PmFycmF5KA0KCQkJJ2hlYWRlcic9PidQcm9kdWN0IFVSTCcsDQoJCSksDQoJCSdJbWFnZVVSTCc9PmFycmF5KA0KCQkJJ2hlYWRlcic9PidJbWFnZSBVUkwnLA0KCQkpLA0KCQknS2V5d29yZHMnPT5hcnJheSgNCgkJCSdoZWFkZXInPT4nS2V5d29yZHMnLA0KCQkpLA0KCQknQWNjb3VudHNfSUQnPT5hcnJheSgNCgkJCSdoZWFkZXInPT4nQWNjb3VudHNfSUQnLA0KCQkpLA0KCQknR3JvdXBMZWFkZXInPT5hcnJheSgNCgkJCSdoZWFkZXInPT4nR3JvdXAgTGVhZGVyJywNCgkJKSwNCgkJJ01vZGVsJz0+YXJyYXkoDQoJCQknaGVhZGVyJz0+J01vZGVsJywNCgkJKSwNCgkJJ1B1cmNoYXNlUHJpY2UnPT5hcnJheSgNCgkJCSdoZWFkZXInPT4nUHVyY2hhc2UgUHJpY2UnLA0KCQkpLA0KCSksDQoJJ2RhdGFzZXRBY3RpdmVVc2FnZSc9PnRydWUsDQoJJ2RhdGFzZXRBY3RpdmVIaWRlQ29udHJvbCc9PmZhbHNlLA0KKSwiO30=',
		CreateDate=NOW(),
		Creator='".sun()."',
		EditDate=NOW()", O_INSERTID);
	}



	if(!($Tables_ID=q("SELECT ID FROM system_tables WHERE SystemName='finan_headers'", O_VALUE))){
		$Tables_ID=q("INSERT INTO system_tables SET SystemName='finan_headers',
		Name='Invoices and Cash Sales',
		KeyField='ID',
		Description='this is a combination invoice/cash sale/check/deposit wrapper',
		Type='table',
		EditDate=NOW()", O_INSERTID);
	}
	if(!($_Profiles_ID_=q("SELECT ID FROM system_profiles WHERE Tables_ID='$Tables_ID' AND Type='Data View' AND Identifier='default'", O_VALUE))){
		$_Profiles_ID_=q("INSERT INTO system_profiles SET
		Tables_ID=$Tables_ID,
		Identifier='default',
		Type='Data View',
		Category='Financial Mgmt.',
		Name='_headers and _transaction table default',
		Description='This is an \"alpha\" basic view/profile for managing invoices and cash sales.  It will eventually be deprecated',
		Settings='YTo4OntzOjk6InN1Yl90YWJsZSI7YToxMzp7czo2OiJhY3RpdmUiO2I6MTtzOjc6InZlcnNpb24iO2Q6MTtzOjU6InRpdGxlIjtzOjU6Ikl0ZW1zIjtzOjEyOiJpbnN0cnVjdGlvbnMiO3M6NDQ6IkVudGVyIGF0IGxlYXN0IG9uZSBpdGVtIGZvciB0aGlzIHRyYW5zYWN0aW9uIjtzOjEwOiJjdXN0b21fY3NzIjtzOjQ1OiIuc3ViVGFibGUgaW5wdXRbdHlwZT10ZXh0XXtib3JkZXItd2lkdGg6MXB4O30iO3M6NToidGFibGUiO3M6MTg6ImZpbmFuX3RyYW5zYWN0aW9ucyI7czo5OiJleGNsdXNpb24iO3M6MzE6InJvb3QuQWNjb3VudHNfSUQhPWEuQWNjb3VudHNfSUQiO3M6MjU6InBvc3RfcHJvY2Vzc2luZ19jb21wb25lbnQiO3M6MzA6ImNvbXBfcHBjXzAxX2xpbmVpdGVtc192MTAwLnBocCI7czo4OiJmaWVsZHNldCI7czo3NjoiYS5JRCwgYS5JdGVtc19JRCwgYS5TS1UsIGEuUXVhbnRpdHksIGEuRGVzY3JpcHRpb24sIGEuVW5pdFByaWNlLCBhLkV4dGVuc2lvbiI7czoxMjoib3JkZXJfY2xhdXNlIjtzOjEyOiJhLklkeCwgYS5TS1UiO3M6MTA6ImJsYW5rX3Jvd3MiO2k6MTA7czo3OiJoZWFkaW5nIjtiOjE7czo3OiJjb2x1bW5zIjthOjU6e3M6ODoiUXVhbnRpdHkiO2E6Mjp7czoxMDoiYXR0cmlidXRlcyI7YToyOntzOjQ6InNpemUiO2k6MztzOjU6ImNsYXNzIjtzOjM6InRhciI7fXM6NzoiaGVhZGluZyI7czo0OiJRdHkuIjt9czozOiJTS1UiO2E6MTp7czoxMDoiYXR0cmlidXRlcyI7YToxOntzOjQ6InNpemUiO2k6MTA7fX1zOjExOiJEZXNjcmlwdGlvbiI7YToxOntzOjEwOiJhdHRyaWJ1dGVzIjthOjI6e3M6NDoic2l6ZSI7aToyMjtzOjU6ImNsYXNzIjtzOjM6InRhciI7fX1zOjk6IlVuaXRQcmljZSI7YToyOntzOjEwOiJhdHRyaWJ1dGVzIjthOjI6e3M6NDoic2l6ZSI7aTo1O3M6NToiY2xhc3MiO3M6MzoidGFyIjt9czo3OiJoZWFkaW5nIjtzOjU6IlByaWNlIjt9czo5OiJFeHRlbnNpb24iO2E6Mjp7czoxMDoiYXR0cmlidXRlcyI7YToyOntzOjQ6InNpemUiO2k6NTtzOjU6ImNsYXNzIjtzOjM6InRhciI7fXM6NzoiaGVhZGluZyI7czo0OiJFeHQuIjt9fX1zOjEwOiJjb2xsZWN0aW9uIjthOjE6e3M6MTM6ImFycmF5X3dyYXBwZXIiO3M6MDoiIjt9czo3OiJjb2x1bW5zIjthOjExOntzOjEyOiJyZXNvdXJjZXR5cGUiO2E6MTp7czo1OiJmbGFncyI7YToxOntzOjQ6InR5cGUiO3M6NDoibm9uZSI7fX1zOjEyOiJoZWFkZXJudW1iZXIiO2E6MTp7czo3OiJkZWZhdWx0IjtzOjI3OiJwaHA6OmxpYl9uZXh0SGVhZGVyTnVtYmVyKCkiO31zOjEzOiJyZXNvdXJjZXRva2VuIjthOjE6e3M6NToiZmxhZ3MiO2E6MTp7czo0OiJ0eXBlIjtzOjQ6Im5vbmUiO319czoxMDoic2Vzc2lvbmtleSI7YToxOntzOjU6ImZsYWdzIjthOjE6e3M6NDoidHlwZSI7czo0OiJub25lIjt9fXM6ODoiZXhwb3J0ZXIiO2E6MTp7czo1OiJmbGFncyI7YToxOntzOjQ6InR5cGUiO3M6NDoibm9uZSI7fX1zOjEwOiJleHBvcnR0aW1lIjthOjE6e3M6NToiZmxhZ3MiO2E6MTp7czo0OiJ0eXBlIjtzOjQ6Im5vbmUiO319czoxMjoidG9iZWV4cG9ydGVkIjthOjE6e3M6NToiZmxhZ3MiO2E6MTp7czo0OiJ0eXBlIjtzOjQ6Im5vbmUiO319czoxMDoiQ2xpZW50c19JRCI7YToxOntzOjE1OiJyZWxhdGlvbnNfbGFiZWwiO3M6MTA6IkNsaWVudE5hbWUiO31zOjExOiJDb250YWN0c19JRCI7YToyOntzOjE1OiJyZWxhdGlvbnNfbGFiZWwiO3M6MzE6IkNPTkNBVChMYXN0TmFtZSwnLCAnLEZpcnN0TmFtZSkiO3M6NToiZmxhZ3MiO2E6MTp7czo0OiJ0eXBlIjtzOjQ6Im5vbmUiO319czoxMToiQWNjb3VudHNfSUQiO2E6MTp7czoxNToicmVsYXRpb25zX2xhYmVsIjtzOjQ6Ik5hbWUiO31zOjM6Inh4eCI7YToyOntzOjEwOiJhdHRyaWJ1dGVzIjthOjA6e31zOjU6ImZsYWdzIjthOjc6e3M6MTM6ImFycmF5X3dyYXBwZXIiO3M6MDoiIjtzOjExOiJidWlsZF9hcnJheSI7YjowO3M6NDoidHlwZSI7czo0OiJub25lIjtzOjg6InJlbGF0aW9uIjthOjA6e31zOjg6ImRpc3RpbmN0IjtzOjA6IiI7czo3OiJjb3VudGVyIjtzOjA6IiI7czoyMDoiZG9fbm90X2NvbnZlcnRfdmFsdWUiO2I6MDt9fX1zOjEwOiJkYXRhb2JqZWN0IjthOjExOntpOjA7YjoxO3M6NzoiZGF0YXNldCI7czo3OiJoZWFkZXJzIjtzOjEyOiJkYXRhc2V0R3JvdXAiO3M6NzoiaGVhZGVycyI7czoxNjoiZGF0YXNldENvbXBvbmVudCI7czoxMToiaGVhZGVyc0xpc3QiO3M6MTI6ImRhdGFzZXRUYWJsZSI7czoyODoiX3ZfZmluYW5faW52b2ljZXNfY2FzaF9zYWxlcyI7czoxODoiZGF0YXNldFRhYmxlSXNWaWV3IjtiOjE7czoyMjoiZGF0YXNldFF1ZXJ5VmFsaWRhdGlvbiI7czozMjoiYmQ0MTk5MTJjM2MzYjk4MzRhYmY3OTRhNTRkOGNkOGYiO3M6MTE6ImRhdGFzZXRGaWxlIjtzOjQxOiJjb21wXzEwMDBfc3lzdGVtZW50cnlfZGF0YW9iamVjdF92MTAwLnBocCI7czozMDoiZGF0YXNldEZvY3VzVmlld0RldmljZUZ1bmN0aW9uIjtzOjE4OiJzeXN0ZW1fZW50cnlfZm9jdXMiO3M6MTg6ImRhdGFzZXRBY3RpdmVVc2FnZSI7YjowO3M6MjQ6ImRhdGFzZXRBY3RpdmVIaWRlQ29udHJvbCI7YjoxO31zOjI5OiJwb3N0X3Byb2Nlc3NpbmdfY29tcG9uZW50X2VuZCI7czozNDoic3lzdGVtZW50cnlfZmluYW5faW52b2ljZXNfZXh0LnBocCI7czoxMjoiSFRNTF9pbnNlcnRzIjthOjE6e3M6MTI6ImJlZm9yZV90YWJsZSI7czo2NzoiPGlucHV0IHR5cGU9ImJ1dHRvbiIgaWQ9InByaW50VHJhbnNhY3Rpb24iIHZhbHVlPSJQcmludCBUaGlzLi4uIiAvPiI7fXM6ODoic3VibW9kZXMiO2E6MTp7czoxNjoicHJpbnRUcmFuc2FjdGlvbiI7czo0MDoic3lzdGVtZW50cnlfZmluYW5fdHJhbnNhY3Rpb25zX3ByaW50LnBocCI7fXM6NToiX3Jhd18iO3M6NDcyMToiJ3N1Yl90YWJsZSc9PmFycmF5KA0KCSdhY3RpdmUnPT50cnVlLCAvKiBldmVudHVhbGx5IHRoaXMgbmVlZHMgdG8gYmUgY2FsY3VsYXRlZCAtIGRlcGVuZHMgb24gcGFyZW50ICovDQoJLyogLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0NCgl0aGluZ3MgdG8gYWRkOg0KDQoJTk9URVM6DQoJcG9zdF9wcm9jZXNzaW5nX2NvbXBvbmVudCB3aWxsIGNvbnRhaW4gYWxsIHRoZSBlcnJvciBjaGVja2luZyBhbmQgcmVxdWlyZWQgZmllbGRzIGF0IHRoaXMgcG9pbnQuICBCdXQgd2UgY2VydGFpbmx5IG5lZWQgY2xpZW50LXNpZGUgZXJyb3IgY2hlY2tpbmcgYWxzbw0KCXBwYyBtdXN0IGFsc28gYmUgc3RvcmVkIGluIGEgYnJvdGhlciBmb2xkZXIgbmFtZWQgY29tcG9uZW50cw0KCWZvcmVpZ25fa2V5PVtkZWZhdWx0OmF1dG9dDQoJLmNvbHVtbnNbSURdIC0gcHJpbWFyeSBrZXkgY29sdW1uIHdpbGwgYmUgY3JlYXRlZCBhdXRvbWF0aWNhbGx5DQoJLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0gKi8NCgkndmVyc2lvbic9PjEuMCwNCg0KCSd0aXRsZSc9PidJdGVtcycsDQoJJ2luc3RydWN0aW9ucyc9PidFbnRlciBhdCBsZWFzdCBvbmUgaXRlbSBmb3IgdGhpcyB0cmFuc2FjdGlvbicsDQoJJ2N1c3RvbV9jc3MnPT4nLnN1YlRhYmxlIGlucHV0W3R5cGU9dGV4dF17Ym9yZGVyLXdpZHRoOjFweDt9JywNCgkndGFibGUnPT4nZmluYW5fdHJhbnNhY3Rpb25zJywgLyogc3RyaW5nLCBvciBhcnJheSwgd2lsbCBiZSBhc3NpZ25lZCBhLGIsLi4gKi8NCgknZXhjbHVzaW9uJz0+J3Jvb3QuQWNjb3VudHNfSUQhPWEuQWNjb3VudHNfSUQnLA0KCSdwb3N0X3Byb2Nlc3NpbmdfY29tcG9uZW50Jz0+J2NvbXBfcHBjXzAxX2xpbmVpdGVtc192MTAwLnBocCcsDQoJJ2ZpZWxkc2V0Jz0+J2EuSUQsIGEuSXRlbXNfSUQsIGEuU0tVLCBhLlF1YW50aXR5LCBhLkRlc2NyaXB0aW9uLCBhLlVuaXRQcmljZSwgYS5FeHRlbnNpb24nLCAvKiBkZWZhdWx0IGEuKiAqLw0KCSdvcmRlcl9jbGF1c2UnPT4nYS5JZHgsIGEuU0tVJywNCgknYmxhbmtfcm93cyc9PjEwLA0KCSdoZWFkaW5nJz0+dHJ1ZSwgLyogaGVhZGluZ3MgdG8gdGFibGUgKi8NCgknY29sdW1ucyc9PmFycmF5KA0KCQknUXVhbnRpdHknPT5hcnJheSgNCgkJCSdhdHRyaWJ1dGVzJz0+YXJyYXkoDQoJCQkJJ3NpemUnPT4zLA0KCQkJCSdjbGFzcyc9Pid0YXInLA0KCQkJKSwNCgkJCSdoZWFkaW5nJz0+J1F0eS4nLA0KCQkpLA0KCQknU0tVJz0+YXJyYXkoDQoJCQknYXR0cmlidXRlcyc9PmFycmF5KA0KCQkJCSdzaXplJz0+MTAsDQoJCQkpLA0KCQkpLA0KCQknRGVzY3JpcHRpb24nPT5hcnJheSgNCgkJCSdhdHRyaWJ1dGVzJz0+YXJyYXkoDQoJCQkJJ3NpemUnPT4yMiwNCgkJCQknY2xhc3MnPT4ndGFyJywNCgkJCSksDQoJCSksDQoJCSdVbml0UHJpY2UnPT5hcnJheSgNCgkJCSdhdHRyaWJ1dGVzJz0+YXJyYXkoDQoJCQkJJ3NpemUnPT41LA0KCQkJCSdjbGFzcyc9Pid0YXInLA0KCQkJKSwNCgkJCSdoZWFkaW5nJz0+J1ByaWNlJywNCgkJKSwNCgkJJ0V4dGVuc2lvbic9PmFycmF5KA0KCQkJJ2F0dHJpYnV0ZXMnPT5hcnJheSgNCgkJCQknc2l6ZSc9PjUsDQoJCQkJJ2NsYXNzJz0+J3RhcicsDQoJCQkpLA0KCQkJJ2hlYWRpbmcnPT4nRXh0LicsDQoJCSksDQoJKSwNCiksDQovKiAtLSB0aGlzIHdhcyBwcmVzZW50IGFscmVhZHkgYmVmb3JlIDIwMTItMTItMDk7IGhhdmUgbm8gcmVhc29uIG5vdCB0byBzdGljayB3aXRoIHRoaXMgcmlnaHQgbm93IC0tICovDQonY29sbGVjdGlvbicgPT4gYXJyYXkgKA0KCSdhcnJheV93cmFwcGVyJyA9PiAnJywNCiksDQonY29sdW1ucycgPT4gYXJyYXkgKA0KCSdyZXNvdXJjZXR5cGUnID0+IGFycmF5ICgNCgkJJ2ZsYWdzJyA9PiBhcnJheSAoDQoJCQkndHlwZScgPT4gJ25vbmUnLA0KCQkpLA0KCSksDQoJJ2hlYWRlcm51bWJlcic9PmFycmF5KCdkZWZhdWx0Jz0+J3BocDo6bGliX25leHRIZWFkZXJOdW1iZXIoKScsKSwNCgkncmVzb3VyY2V0b2tlbicgPT4gYXJyYXkoJ2ZsYWdzJyA9PmFycmF5ICggJ3R5cGUnID0+ICdub25lJywpLCksDQoJJ3Nlc3Npb25rZXknID0+IGFycmF5KCdmbGFncycgPT5hcnJheSAoICd0eXBlJyA9PiAnbm9uZScsKSwpLA0KCSdleHBvcnRlcicgPT4gYXJyYXkoJ2ZsYWdzJyA9PmFycmF5ICggJ3R5cGUnID0+ICdub25lJywpLCksDQoJJ2V4cG9ydHRpbWUnID0+IGFycmF5KCdmbGFncycgPT5hcnJheSAoICd0eXBlJyA9PiAnbm9uZScsKSwpLA0KCSd0b2JlZXhwb3J0ZWQnID0+IGFycmF5KCdmbGFncycgPT5hcnJheSAoICd0eXBlJyA9PiAnbm9uZScsKSwpLA0KCSdDbGllbnRzX0lEJz0+YXJyYXkoDQoJCSdyZWxhdGlvbnNfbGFiZWwnPT4nQ2xpZW50TmFtZScsDQoJKSwNCgknQ29udGFjdHNfSUQnPT5hcnJheSgNCgkJJ3JlbGF0aW9uc19sYWJlbCc9PidDT05DQVQoTGFzdE5hbWUsXCcsIFwnLEZpcnN0TmFtZSknLA0KCQknZmxhZ3MnID0+IGFycmF5ICgNCgkJCSd0eXBlJyA9PiAnbm9uZScsDQoJCSksDQoJKSwNCgknQWNjb3VudHNfSUQnPT5hcnJheSgNCgkJJ3JlbGF0aW9uc19sYWJlbCc9PidOYW1lJywNCgkpLA0KCSd4eHgnID0+IGFycmF5ICgNCgkJJ2F0dHJpYnV0ZXMnID0+IGFycmF5ICgpLA0KCQknZmxhZ3MnID0+IGFycmF5ICgNCgkJCSdhcnJheV93cmFwcGVyJyA9PiAnJywNCgkJCSdidWlsZF9hcnJheScgPT4gZmFsc2UsDQoJCQkndHlwZScgPT4gJ25vbmUnLA0KCQkJJ3JlbGF0aW9uJyA9PiBhcnJheSAoKSwNCgkJCSdkaXN0aW5jdCcgPT4gJycsDQoJCQknY291bnRlcicgPT4gJycsDQoJCQknZG9fbm90X2NvbnZlcnRfdmFsdWUnID0+IGZhbHNlLA0KCQkpLA0KCSksDQopLA0KJ2RhdGFvYmplY3QnPT5hcnJheSgNCgkvKiBhZGRlZCBieSByb290X3N5c3RlbWVudHJ5X2xpc3QucGhwIGF0IDEyLzE2LzIwMTIgYXQgMTA6NDhQTSBvbiBpbml0aWFsIGNhbGwtdXAgKi8NCgknZGF0YXNldEF1dG9CdWlsZCc+JzEuMC4wMScsDQoJJ2RhdGFzZXQnPT4naGVhZGVycycsDQoJJ2RhdGFzZXRHcm91cCc9PidoZWFkZXJzJywNCgknZGF0YXNldENvbXBvbmVudCc9PidoZWFkZXJzTGlzdCcsDQoJJ2RhdGFzZXRUYWJsZSc9Pidfdl9maW5hbl9pbnZvaWNlc19jYXNoX3NhbGVzJywNCgknZGF0YXNldFRhYmxlSXNWaWV3Jz0+dHJ1ZSwNCgkvKg0KCSdkYXRhc2V0UXVlcnknPT4nU0VMRUNUICogRlJPTSBfdl9maW5hbl9pbnZvaWNlc19jYXNoX3NhbGVzIFdIRVJFIDEnLA0KCSovDQoJJ2RhdGFzZXRRdWVyeVZhbGlkYXRpb24nPT4nYmQ0MTk5MTJjM2MzYjk4MzRhYmY3OTRhNTRkOGNkOGYnLA0KCSdkYXRhc2V0RmlsZSc9Pidjb21wXzEwMDBfc3lzdGVtZW50cnlfZGF0YW9iamVjdF92MTAwLnBocCcsDQoJJ2RhdGFzZXRGb2N1c1ZpZXdEZXZpY2VGdW5jdGlvbic9PidzeXN0ZW1fZW50cnlfZm9jdXMnLA0KCS8qDQoJMjAxMi0xMi0xODoNCgl3aGVuIEkgY29tbWVudCBvdXQgdGhlIGNvbHVtbnMgbm9kZSwgdGhlIHN5c3RlbSB1c2VzIHRoZSBFWFBMQUlOIHRhYmxlIHF1ZXJ5IGFzIGNvbHVtbnMuICBJdCBkb2VzIGEgcHJldHR5IGdvb2Qgam9iIG9mIHJpZ2h0LWp1c3RpZnlpbmcgbnVtYmVycyBhbmQgY29udmVydGluZyBkYXRlcywgYXMgd2VsbCBhcyBwYXJzaW5nIG91dCBsYWJlbHMuICBEZWNsYXJlZCBjb2x1bW5zIGxvc2VzIGEgbG90IG9mIHRoaXMgYW5kIEkgbmVlZCB0byBicmluZyBpdCBvdmVyIHNvIGV4cGxpY2l0IGNvbHVtbnMgaGFuZGxlcyB0aGUgaGFyZGVyIHN0dWZmLg0KCSdjb2x1bW5zJz0+YXJyYXkoDQoJCSdIZWFkZXJUeXBlJz0+YXJyYXkoDQoJCQknaGVhZGVyJz0+J0hlYWRlciBUeXBlJywNCgkJKSwNCgkJJ0hlYWRlclN0YXR1cyc9PmFycmF5KA0KCQkJJ2hlYWRlcic9PidIZWFkZXIgU3RhdHVzJywNCgkJKSwNCgkJJ0hlYWRlckRhdGUnPT5hcnJheSgNCgkJCSdoZWFkZXInPT4nSGVhZGVyIERhdGUnLA0KCQkpLA0KCQknSGVhZGVyTnVtYmVyJz0+YXJyYXkoDQoJCQknaGVhZGVyJz0+J0hlYWRlciBOdW1iZXInLA0KCQkpLA0KCQknQ2xpZW50c19JRCc9PmFycmF5KA0KCQkJJ2hlYWRlcic9PidDbGllbnRzX0lEJywNCgkJKSwNCgkJJ0NvbnRhY3RzX0lEJz0+YXJyYXkoDQoJCQknaGVhZGVyJz0+J0NvbnRhY3RzX0lEJywNCgkJKSwNCgkJJ0FjY291bnRzX0lEJz0+YXJyYXkoDQoJCQknaGVhZGVyJz0+J0FjY291bnRzX0lEJywNCgkJKSwNCgkJJ0NsYXNzZXNfSUQnPT5hcnJheSgNCgkJCSdoZWFkZXInPT4nQ2xhc3Nlc19JRCcsDQoJCSksDQoJCSdOb3Rlcyc9PmFycmF5KA0KCQkJJ2hlYWRlcic9PidOb3RlcycsDQoJCSksDQoJCSdDcmVhdGVEYXRlJz0+YXJyYXkoDQoJCQknaGVhZGVyJz0+J0NyZWF0ZSBEYXRlJywNCgkJKSwNCgkpLA0KCSovDQoJJ2RhdGFzZXRBY3RpdmVVc2FnZSc9PmZhbHNlLA0KCSdkYXRhc2V0QWN0aXZlSGlkZUNvbnRyb2wnPT50cnVlLA0KKSwNCidwb3N0X3Byb2Nlc3NpbmdfY29tcG9uZW50X2VuZCc9PidzeXN0ZW1lbnRyeV9maW5hbl9pbnZvaWNlc19leHQucGhwJywNCidIVE1MX2luc2VydHMnPT5hcnJheSgNCgknYmVmb3JlX3RhYmxlJz0+JzxpbnB1dCB0eXBlPSJidXR0b24iIGlkPSJwcmludFRyYW5zYWN0aW9uIiB2YWx1ZT0iUHJpbnQgVGhpcy4uLiIgLz4nLA0KKSwNCidzdWJtb2Rlcyc9PmFycmF5KA0KCSdwcmludFRyYW5zYWN0aW9uJz0+J3N5c3RlbWVudHJ5X2ZpbmFuX3RyYW5zYWN0aW9uc19wcmludC5waHAnLA0KKSwiO30=',
		CreateDate=NOW(),
		Creator='".sun()."',
		EditDate=NOW()", O_INSERTID);
	}
	if(!($Type_AR=q("SELECT ID FROM finan_accounts_types WHERE Name='Accounts Receivable' AND Category='Asset'", O_VALUE))){
		$Type_AR=q("INSERT INTO finan_accounts_types SET CreateDate=NOW(), Creator='".sun()."', Name='Accounts Receivable', Category='Asset'", O_INSERTID);
	}
	if(!($Type_UDF=q("SELECT ID FROM finan_accounts_types WHERE Name='Other Current Asset' AND Category='Asset'", O_VALUE))){
		$Type_UDF=q("INSERT INTO finan_accounts_types SET CreateDate=NOW(), Creator='".sun()."', Name='Other Current Asset', Category='Asset'", O_INSERTID);
	}
	if(!($AR_Accounts_ID=q("SELECT ID FROM finan_accounts WHERE Name='Accounts Receivable'",O_VALUE))){
		$AR_Accounts_ID=q("INSERT INTO finan_accounts SET CreateDate=NOW(), Creator='".sun()."', 
		Name='Accounts Receivable', Types_ID='$Type_AR', Description='From online purchases, orders waiting for payments', Notes='Added automatically by rfm_hub.php line ".__LINE__."'", O_INSERTID);
	}
	if(!($UDF_Accounts_ID=q("SELECT ID FROM finan_accounts WHERE Name='Undeposited Funds'",O_VALUE))){
		$UDF_Accounts_ID=q("INSERT INTO finan_accounts SET CreateDate=NOW(), Creator='".sun()."', 
		Name='Undeposited Funds', Types_ID='$Type_UDF', Description='Cash and checks on hand not yet deposited', Notes='Added automatically by rfm_hub.php line ".__LINE__."'", O_INSERTID);
	}
	?>
	<p><a href="root_systementry_list.php?_Profiles_ID_=<?php echo $Items_Profiles_ID_;?>">List of billing items</a></p>
	<p><a href="root_systementry_list.php?_Profiles_ID_=<?php echo $Clients_Profiles_ID_;?>">List of Clients</a> 
	(you need to add the finan_ClientsContacts and addr_contacts entries separately) </p>
	<p><a href="root_systementry_list.php?_Profiles_ID_=<?php echo $_Profiles_ID_;?>">List of invoices and cash sales</a>
	&nbsp;&nbsp;&nbsp;
	<a href="rfm_export.php" onclick="return ow(this.href,'l1_export','700,800',true);"><strong>Invoice/Cash Sale Exporter</strong> </a></p>
    <p>
	<a href="systementry.php?_Profiles_ID_=<?php echo $_Profiles_ID_;?>&Accounts_ID=<?php echo $AR_Accounts_ID;?>&HeaderType=Invoice" onclick="return ow(this.href,'l1_transaction','700,800',true);">Add new invoice</a> 
	or 
	<a href="systementry.php?_Profiles_ID_=<?php echo $_Profiles_ID_;?>&Accounts_ID=<?php echo $UDF_Accounts_ID;?>&HeaderType=Cash+Sale" onclick="return ow(this.href,'l1_transaction','700,800',true);">Add new cash sale</a>	</p>
    <p><a href="rfm_payments.php" onclick="return ow(this.href,'l1_payments','850,650',true);">Receive a payment</a></p>
    <p>Make a deposit</p>
    <p>Enter a check</p>
    <p>Report Engine <strong>*</strong>      <br />
      <br />
      <a href="document_library.php" onclick="return ow(this.href,'l1_documents','800,700');"><strong>Document Library</strong></a> </p>
	<!-- InstanceEndEditable -->
	<div class="cbsm"> </div>
  </div>
	<div id="footer">
	<!-- InstanceBeginEditable name="footer" --><!-- #BeginLibraryItem "/Library/rbrfm_footer.lbi" -->&copy;2008-<?php echo date('Y');?> RelateBase Services Inc. - 
<a href="/" target="_blank" title="View index page of your website">view site</a> | 
<a href="http://www.compasspoint-sw.com/mediawiki/index.php?title=RelateBase_Ecommerce_Console:RBRFM:Public_Documentation" target="helpme">WIKI</a><!-- #EndLibraryItem --><!-- InstanceEndEditable -->
	</div>
</div>

<div id="showTester" title="Javascript Tester" onClick="g('tester').style.display='block';">&nbsp;</div>
<div id="tester" >
	<a href="#" onClick="g('ctrlSection').style.display=op[g('ctrlSection').style.display]; return false;">iframes</a><br />
	<textarea name="test" cols="65" rows="4" id="test">clear_form();</textarea><br />
	<input type="button" name="button" value="Test" onClick="jsEval(g('test').value);"><br />
	<textarea id="result" name="result" cols="65" rows="3" ></textarea>
</div>
<?php if(!$hideCtrlSection){ ?>
<div id="ctrlSection" style="display:<?php echo $testModeC ? 'block':'none'?>">
	<iframe name="w1" src="/Library/js/blank.htm"></iframe>
	<iframe name="w2" src="/Library/js/blank.htm"></iframe>
	<iframe name="w3" src="/Library/js/blank.htm"></iframe>
	<iframe name="w4" src="/Library/js/blank.htm"></iframe>
</div>
<?php } ?>
</body>
<!-- InstanceEnd --></html><?php
page_end();
?>