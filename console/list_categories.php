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
$hideCtrlSection=false;

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/rbrfm_01.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title><?php echo h($adminCompany);?> List Categories</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" type="text/css" href="rbrfm_admin.css" />
<link rel="stylesheet" type="text/css" href="/Library/css/DHTML/dynamic_04_i1.css" />
<link rel="stylesheet" type="text/css" href="/Library/css/DHTML/data_04_i1.css" />
<style>
/** CSS Declarations for this page **/
</style>

<script src="/Library/js/global_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/common_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/forms_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/loader_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/contextmenus_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/Library/js/dataobjects_04_i1.js" language="JavaScript" type="text/javascript"></script>
<script src="/console/console.js" language="javascript" type="text/javascript"></script>
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
var isEscapable=0;
var isDeletable=0;
var isModal=0;
var talks=0; //whether this page broadcasts overall state changes which other pages listen for
var listens=0;

<?php 
//js var user settings
js_userSettings();
?>

function toggleActiveObject(ID){
	var active=g('r_'+ID+'_active').getAttribute('active');
	g('r_'+ID+'_active').innerHTML=(active=='1' ? '<img src="../images/i/garbage2.gif" width="18" height="21" align="absbottom" />' : '&nbsp;');
	g('r_'+ID+'_active').title=('Make this member '+(active=='1' ? '':'in')+'active');
	g('r_'+ID+'_active').setAttribute('active', (active=='1'?'0':'1'));
	window.open('resources/bais_01_exe.php?mode=toggleActiveObject&node='+ID+'&table=finan_clients&current='+active, 'w2');
}
</script>

<!-- InstanceEndEditable -->
</head>

<body>
<div id="mainContainer">
	<!-- InstanceBeginEditable name="admin_top" --><!-- #BeginLibraryItem "/Library/rbrfm_adminmenu_basic_01.lbi" -->
	<?php
require($_SERVER['DOCUMENT_ROOT'].'/console/rbrfm_adminmenu_basic_02.php');
?>
	<!-- #EndLibraryItem --><!-- InstanceEndEditable -->
	<!-- InstanceBeginEditable name="top_region" --><!-- InstanceEndEditable -->
	<div id="leftInset">
	<!-- InstanceBeginEditable name="left_inset" --><!-- InstanceEndEditable -->
	</div>
	<div id="mainBody">
	<!-- InstanceBeginEditable name="main_body" -->

<style type="text/css">
.box{
	border-collapse:collapse;
	}
.box a{
	color:midnightblue;
	}
.box td{
	border:1px dotted darkred;
	padding:10px;
	}
.box img{
	padding:2px;
	border:1px solid gold;
	}
.img{
	text-align:center;
	}
.imgCtrls{
	float:left;
	width:25px;
	}
.deleteButton{
	background-color:darkred;
	color:white;
	}
.addButton{
	background-color:darkblue;
	color:white;
	}
.SKUList{
	margin-bottom:6px;
	}
.subthumb{
	float:left;border:1px dotted #666;padding:4px;max-width:150px;font-size:12px;color:#999;margin:0px 3px 2px 0px;
	}
</style>
<div class="fr">
<a href="/admin/file_explorer/?uid=category&folder=category" onclick="return ow(this.href,'l1_fex','700,700');">View Category Folder (/images/category)</a><br />
<a href="list_categories_changer.php">View Category/Subcategory Changer</a>
</div>
<h1>Categories</h1>
<p>Each time you call this page, it checks that the images/category folder is set up correctly and that references to "orphaned" images have been removed
<?php
/*
2011-08-29
do a prelim check on all images actually being present from objects

*/

//make sure the category folder exists
if(!is_dir($_SERVER['DOCUMENT_ROOT'].'/images/category') && !mkdir($_SERVER['DOCUMENT_ROOT'].'/images/category')){
	exit('unable to create category folder; an administrator has been notified');
}
if(!is_dir($_SERVER['DOCUMENT_ROOT'].'/images/subcategory') && !mkdir($_SERVER['DOCUMENT_ROOT'].'/images/subcategory')){
	exit('unable to create category folder; an administrator has been notified');
}
$catImg=get_file_assets($_SERVER['DOCUMENT_ROOT'].'/images/category');
#prn($catImg);
$subcatImg=get_file_assets($_SERVER['DOCUMENT_ROOT'].'/images/subcategory');

if(!function_exists('get_image'))require($FUNCTION_ROOT.'/function_get_image_v201.php');

if($category=q("SELECT
LCASE(i.Category) AS CATIDX,
i.Category,
COUNT(DISTINCT i.SubCategory) AS Subcategories,
c.Description,
c.Keywords,
c.Priority,
COUNT(DISTINCT iA.ID) AS Active,
COUNT(DISTINCT iB.ID) AS Inactive

FROM finan_items i LEFT JOIN finan_items_categories c ON i.Category=c.Name
LEFT JOIN finan_items iA ON i.Category=iA.Category AND iA.Active=1 AND iA.ResourceType IS NOT NULL
LEFT JOIN finan_items iB ON i.Category=iB.Category AND iB.Active=0 AND iB.ResourceType IS NOT NULL
WHERE i.ResourceType IS NOT NULL
GROUP BY i.Category
ORDER BY i.Category ASC", O_ARRAY_ASSOC)){

	//first we match up the images which are present
	foreach($category as $n=>$v){
		$get_image=get_image($n,$catImg,array('returnmethod'=>'array'));
		if($get_image['status']=='not available'){
			$category[$n]['src']='/images/i/spacer.gif';
			$category[$n]['width']=200;
			$category[$n]['height']=200;
			$category[$n]['dims']='width="200" height="200"';
			$category[$n]['status']='not available';
		}else{
			$category[$n]['src']='/images/category/'.$get_image['name'];
			$category[$n]['width']=$get_image['width'];
			$category[$n]['height']=$get_image['height'];
			if($get_image['width']>200 || $get_image['height']>200){
				$category[$n]['dims']=($get_image['width']>$get_image['height'] ? 'width="200"':'height="200"');
			}else{
				$category[$n]['dims']='width="'.$get_image['width'].'" height="'.$get_image['height'].'"';
			}
			unset($catImg[$get_image['array_key']]);
		}
	}
	if(count($catImg)){
		//append what is left
		foreach($catImg as $n=>$v){
			if(!preg_match('/\.(svg|gif|jpg|jpeg|png)$/i',$n))continue;
			$category[$n]=array(
				'orphan'=>true,
				'src'=>'/images/category/'. $v['name'],
				'width'=>$v['width'],
				'height'=>$v['height'],
				'dims'=>'width="200"',
			);
		}
	}

	?><table class="box">
	<?php
	$cols=3;
	$i=0;
	foreach($category as $n=>$v){
		$i++;
		if(!fmod($i-1,$cols)){
			?><tr><?php
		}
		?><td>
		<h3><?php echo $v['orphan'] ? $v['name'] : ($v['Category'] ? $v['Category'] : '<em>(uncategorized)</em>');?></h3>
		<div class="img">
		<div class="imgCtrls">
		<a href="javascript:alert('Currently, open the images/category folder in File Explorer and delete the picture named <?php ?>');"><div class="deleteButton">x</div></a>
		<br />
		<?php if(!$v['orphan']){ ?>
		<a href="javascript:alert('Currently, open the images/category folder in File Explorer and rename or upload a new picture named <?php ?>');"><div class="addButton">+</div></a>
		<?php } ?>
		</div>
		<img src="<?php echo $v['src'];?>" <?php echo $v['dims']?> alt="category picture" /><br />
		<span class="gray"><?php echo $v['status']=='not available' ? '<em>no picture available</em>' : ($v['width']. 'x'.$v['height']);?></span>
		</div>
		<?php
		if($v['orphan']){
			?><span class="gray">un-associated image:</span><br /><?php
			echo end(explode('/',$v['src']));
		}else{
			//3 active
			//2 active, 1 inactive
			//3 inactive
			unset($a);
			if($x=$v['Active'])$a[]=$x.' active';
			if($x=$v['Inactive'])$a[]=$x.' inactive';
			echo implode(', ',$a);
			if(count($a))echo ' product'.(array_sum($a)>1?'s':'');
			?><div class="SKUList"><?php
			if($v['Active']){
				$o=q("SELECT ID, SKU, SubCategory, Name FROM finan_items WHERE Category='".addslashes($v['Category'])."' AND Active=1 AND ResourceType IS NOT NULL ORDER BY SubCategory, SKU", O_ARRAY);
				if(count($o)){
					$j=0;
					foreach($o as $w){
						$j++;
						//orange means image old style, red means no image
						?><a href="items.php?Items_ID=<?php echo $w['ID']?>" title="<?php echo h($w['SubCategory'].':'.$w['Name']);?>" onclick="return ow(this.href,'l1_items','750,700');"><?php echo $w['SKU'];?></a><?php
						if($j<count($o))echo ', ';
						if($j==10 && count($o)>10){
							?><span title="there are also more products in this category"><?php echo count($o)-10?> more..</span><?php
							break;
						}
					}
				}
			}
		}
		
		?></div>
		<?php
		if($v['Subcategories']){
			foreach(q("SELECT SubCategory, COUNT(*) FROM finan_items WHERE Category='".addslashes($v['Category'])."' GROUP BY SubCategory ORDER BY SubCategory", O_COL_ASSOC) as $p=>$x){
				$gi=get_image($p,$subcatImg,array('returnmethod'=>'array'));
				$s=array();
				if($gi['status']=='not available'){
					$s['src']='/images/i/spacer.gif';
					$s['dims']=($gi['width']>$gi['height']?'width="40"':'height="40"');
				}else{
					$s['src']='/images/subcategory/'.$gi['name'];
					$s['dims']='width="40"';
				}
				
				?><div class="subthumb"><span title="<?php echo h($p);?>"><img src="<?php echo $s['src'];?>" <?php echo $s['dims'];?> alt="<?php echo $p;?>" /> </span><br />
				<?php echo h($p).'('.$x.')';?>
				</div><?php
			}
		}
		?>
		
		</td><?php
		if(!fmod($i,$cols)){
			?></tr><?php
		}
	}
	//close out
	if($final=$cols - fmod($i,$cols)){
		echo str_repeat('<td>&nbsp;</td>',$final).'</tr>';
	}
	?>
	</table><?php
}


?>
	

<!-- InstanceEndEditable -->
	<div class="cbsm"> </div>
	</div>
	<div id="footer">
	<!-- InstanceBeginEditable name="footer" --><!-- #BeginLibraryItem "/Library/rbrfm_footer.lbi" -->&copy;2008-<?php echo date('Y');?> RelateBase Services Inc. - 
<a href="/" target="_blank" title="View index page of your website">view site</a> | 
<a href="http://www.compasspointmedia.com/mediawiki/index.php?title=RelateBase_Ecommerce_Console:RBRFM:Public_Documentation" target="helpme">WIKI</a><!-- #EndLibraryItem --><!-- InstanceEndEditable -->
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
$out=ob_get_contents(); #end buffer to move iframes
ob_end_clean();
$ctrl=strstr($out,'<div id="ctrlSection"');
$ctrl=str_replace('</body>','',$ctrl);
$ctrl=str_replace('</html>','',$ctrl);
$out=str_replace($ctrl,'',$out);
$out.='</body></html>';
$out=str_replace($rand,$ctrl,$out);
echo($out);
page_end();
?>