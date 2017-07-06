<?php 
/*
1-28-11
	Made this new file in order to be able to see weekly tables of hours (So on submit it will refresh this page and update the table as well)
*/

//identify this script/GUI
$localSys['scriptGroup']='';
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['pageType']='Properties Window';

require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php'); 
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');

$qx['defCnxMethod']=C_MASTER;
$qx['useRemediation']=true;
if(!$ID)$ID=0;
$ToExtract=q("SELECT * FROM finan_hours WHERE ID='$ID'",O_ROW);
extract($ToExtract);
$StartTimeDay=date('m-d-Y',strtotime($StartTime));
$StartTimeHour=date('g:iA',strtotime($StartTime));
$EndTimeHour=date('g:iA',strtotime($EndTime));

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Untitled Document</title>
<link rel="stylesheet" type="text/css" href="/Library/css/undohtml3.css" />
<link rel="stylesheet" href="/console/rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="../Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">
body{
	background-color:#CCC;
	}
.objectWrapper{
	padding:0px 20px;
	}
td{
	border:1px solid black;
}
thead{
	border:1px solid black;
}
</style>

<script language="JavaScript" type="text/javascript" src="../Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="../Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="../Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="../Library/js/loader_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="../Library/js/contextmenus_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="../Library/js/dataobjects_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="../Library/js/jquery.js"></script>
<script type="text/javascript" src="../Library/fck6/fckeditor.js"></script>
<script language="JavaScript" type="text/javascript">
/* periwinkle coding 2.1 */
var thispage='<?php echo $thispage?>';
var thisfolder='<?php echo $thisfolder?>';
var browser='<?php echo $browser?>';
var ctime='<?php echo $ctime?>';
var PHPSESSID='<?php echo $PHPSESSID?>';
//for nav feature
var count='<?php echo $nullCount?>';
var ab='<?php echo $nullAbs?>';
var isEscapable=1;
var isDeletable=1;
var isModal=1;
var talks=1; //whether this page broadcasts overall state changes which other pages listen for
var listens=0;
</script>
</head>

<body>
<button name="previous" id="previous" onclick="window.location='hours2.php?ID=<?php echo $ID-1?>';">Previous</button>
<button name="submit" id="submit" onclick="">Submit</button>
<button name="next" id="next" onclick="window.location='hours2.php?ID=<?php echo $ID+1?>';">Next</button><br />
<?php 
	require('components/comp_40_hoursform_v101.php');
?>
</body>
</html>
