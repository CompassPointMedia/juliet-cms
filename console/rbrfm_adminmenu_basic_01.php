<?php
//See the DHTML Menu notes in console/docs.txt
//moved from the library item 2010-08-24: saves space
ob_start();

?><div id="header">
<h2 class="main" style="font-weight:400;"><a href="../console/home.php" title="Site Administration Home Page"><?php
if(strlen($adminLogo) && file_exists($_SERVER['DOCUMENT_ROOT'].'/images/assets/'.$adminLogo)){
	?><img src="/images/assets/<?php echo $adminLogo?>" alt="logo"  border="0" align="absbottom" /><?php 
}
?>&nbsp; <?php echo $adminCompany?> Site Administration 1.0</a></h2>
<div id="menuWrap1">
	<div id="dmsmenu2_QzolNUNVc2VycyU1Q1NhbXVlbCUyMEZ1bGxtYW4lNUNEb2N1bWVudHMlNUNDb21wYXNzJTIwUG9pbnQlMjBNZWRpYSU1Q0hvc3RlZCUyMEFjY291bnRzJTVDREhUTUwlMjBNZW51cyU1Q3JicmZtX2FkbWlubWVudV9iYXNpY18wMS5kbXM:"> <img id='TrackPath_m2' src='../menu-files/empty.gif' width='1' height='1' style='display:none' alt='' />
			<script type="text/javascript" src="../menu-files/menu_m2_scr.js">
		</script>
			<table id="m2mainSXMenu2" cellspacing="1" cellpadding="4"  style=";width:">
				<tr style="text-align:left">
					<td onMouseOver="chgBg(m2,'m2tlm0',3);exM(m2,'m2mn1','m2tlm0',event)" onMouseOut="chgBg(m2,'m2tlm0',0);coM(m2,'m2mn1')" id="m2tlm0" style="background-color:#cccccc;" class="m2mit" ><a id="m2tlm0a" class="m2CL0" href="javascript:void(0);" >Home<img width="8" height="8" src="../menu-files/menu_m2iad.gif" style="vertical-align:middle;border-style:none" alt="" /></a></td>
					<td onMouseOver="chgBg(m2,'m2tlm1',3);exM(m2,'m2mn2','m2tlm1',event)" onMouseOut="chgBg(m2,'m2tlm1',0);coM(m2,'m2mn2')" id="m2tlm1" style="background-color:#cccccc;" class="m2mit" ><a id="m2tlm1a" class="m2CL0" href="javascript:void(0);" >Items<img width="8" height="8" src="../menu-files/menu_m2iad.gif" style="vertical-align:middle;border-style:none" alt="" /></a></td>
					<td onMouseOver="chgBg(m2,'m2tlm2',3);exM(m2,'m2mn3','m2tlm2',event)" onMouseOut="chgBg(m2,'m2tlm2',0);coM(m2,'m2mn3')" id="m2tlm2" style="background-color:#cccccc;" class="m2mit" ><a id="m2tlm2a" class="m2CL0" href="javascript:void(0);" >Members<img width="8" height="8" src="../menu-files/menu_m2iad.gif" style="vertical-align:middle;border-style:none" alt="" /></a></td>
					<td onMouseOver="chgBg(m2,'m2tlm3',3);exM(m2,'m2mn4','m2tlm3',event)" onMouseOut="chgBg(m2,'m2tlm3',0);coM(m2,'m2mn4')" id="m2tlm3" style="background-color:#cccccc;" class="m2mit" ><a id="m2tlm3a" class="m2CL0" href="javascript:void(0);" >Orders<img width="8" height="8" src="../menu-files/menu_m2iad.gif" style="vertical-align:middle;border-style:none" alt="" /></a></td>
					<td onMouseOver="chgBg(m2,'m2tlm4',3);exM(m2,'m2mn5','m2tlm4',event)" onMouseOut="chgBg(m2,'m2tlm4',0);coM(m2,'m2mn5')" id="m2tlm4" style="background-color:#cccccc;" class="m2mit" ><a id="m2tlm4a" class="m2CL0" href="javascript:void(0);" >Clothing Product Options<img width="8" height="8" src="../menu-files/menu_m2iad.gif" style="vertical-align:middle;border-style:none" alt="" /></a></td>
					<td onMouseOver="chgBg(m2,'m2tlm5',3);exM(m2,'m2mn6','m2tlm5',event)" onMouseOut="chgBg(m2,'m2tlm5',0);coM(m2,'m2mn6')" id="m2tlm5" style="background-color:#cccccc;" class="m2mit" ><a id="m2tlm5a" class="m2CL0" href="javascript:void(0);" >Classifieds<img width="8" height="8" src="../menu-files/menu_m2iad.gif" style="vertical-align:middle;border-style:none" alt="" /></a></td>
					<td onMouseOver="chgBg(m2,'m2tlm6',3);exM(m2,'m2mn7','m2tlm6',event)" onMouseOut="chgBg(m2,'m2tlm6',0);coM(m2,'m2mn7')" id="m2tlm6" style="background-color:#cccccc;" class="m2mit" ><a id="m2tlm6a" class="m2CL0" href="javascript:void(0);" >Content<img width="8" height="8" src="../menu-files/menu_m2iad.gif" style="vertical-align:middle;border-style:none" alt="" /></a></td>
					<td onMouseOver="chgBg(m2,'m2tlm7',3);exM(m2,'m2mn8','m2tlm7',event)" onMouseOut="chgBg(m2,'m2tlm7',0);coM(m2,'m2mn8')" id="m2tlm7" style="background-color:#cccccc;" class="m2mit" ><a id="m2tlm7a" class="m2CL0" href="javascript:void(0);" >Events<img width="8" height="8" src="../menu-files/menu_m2iad.gif" style="vertical-align:middle;border-style:none" alt="" /></a></td>
					<td onMouseOver="chgBg(m2,'m2tlm8',3);exM(m2,'none','',event)" onMouseOut="chgBg(m2,'m2tlm8',0,1)" id="m2tlm8" onMouseDown="f58('m2tlm8a')" style="background-color:#cccccc;" class="m2mit" ><a id="m2tlm8a" class="m2CL0" href="/console/statistics.php" >Statistics</a></td>
					<td onMouseOver="chgBg(m2,'m2tlm9',3);exM(m2,'m2mn9','m2tlm9',event)" onMouseOut="chgBg(m2,'m2tlm9',0);coM(m2,'m2mn9')" id="m2tlm9" style="background-color:#cccccc;" class="m2mit" ><a id="m2tlm9a" class="m2CL0" href="javascript:void(0);" >Images<img width="8" height="8" src="../menu-files/menu_m2iad.gif" style="vertical-align:middle;border-style:none" alt="" /></a></td>
					<td onMouseOver="chgBg(m2,'m2tlm10',3);exM(m2,'m2mn10','m2tlm10',event)" onMouseOut="chgBg(m2,'m2tlm10',0);coM(m2,'m2mn10')" id="m2tlm10" style="background-color:#cccccc;" class="m2mit" ><a id="m2tlm10a" class="m2CL0" href="javascript:void(0);" >Help<img width="8" height="8" src="../menu-files/menu_m2iad.gif" style="vertical-align:middle;border-style:none" alt="" /></a></td>
				</tr>
			</table>
	</div>
</div>
<?php if(!$_SESSION['admin']['identity'] && false){ ?><script language="javascript" type="text/javascript"> m1mn1[10]='Log In'; </script><?php } ?>
<?php if($prependAbsDHTMLMenuPath){ ?><script language="javascript" type="text/javascript">prepend_menu('<?php echo $prependAbsDHTMLMenuPath?>');</script><?php } ?>
</div><?php
$out=ob_get_contents();
ob_end_clean();
$out=str_replace('../menu-files','menu-files',$out);
//specific coding for this DHTML menu - these options must match the DHTML Menu
$DHTMLMainOptions=array();
$DHTMLMainOptions[1]='Home';
$DHTMLMainOptions[2]='Items';
$DHTMLMainOptions[3]='Customers';
$DHTMLMainOptions[4]='Orders';
$DHTMLMainOptions[5]='Classifieds';
$DHTMLMainOptions[6]='Clothing Product Options';
$DHTMLMainOptions[7]='Content';
$DHTMLMainOptions[8]='Events';
$DHTMLMainOptions[9]='Statistics';
$DHTMLMainOptions[10]='Images';
$DHTMLMainOptions[11]='Help';

//explode the table
$out=explode("\n",$out);
$str=''; $i=0;
foreach($out as $v){
	if(preg_match('/<td/i',trim($v))){
		if(!$placeholder){
			$placeholder=md5(time());
			$str.="\n".$placeholder."\n";
		}
		$i++;
		$options[$i]=$v;
	}else{
		$str.=$v."\n";
	}
}
$normallyExcludedOptions=array(
	'Classifieds',
	'Clothing Product Options'
);
foreach($options as $n=>$v){ //<-
	//exclude specialty options
	preg_match('/>([^<]+)</',$v,$a);
	if(@in_array($a[1],$normallyExcludedOptions) && !$includeExcludedOption[$a[1]]){
		unset($options[$n]);
		continue;
	}

	if(@in_array($DHTMLMainOptions[$n], $excludeDHTMLMainOptions)){
		unset($options[$n]);
		continue;
	}
	if($nameDHTMLMenuOption[$n])$options[$n]=str_replace('>'.$DHTMLMainOptions[$n].'<', '>'.$nameDHTMLMenuOption[$n].'<', $options[$n]);
}
$out=str_replace($placeholder,implode('',$options),$str);
if(!$hideHeader)echo $out;
if($allowClose){
	?><div id="closeWindow"><input type="button" name="OK" value=" Close " onClick="window.close();" /></div><?php
}
?>