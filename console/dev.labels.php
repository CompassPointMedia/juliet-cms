<?php
$lead=4;
$recordCount=100;
$ppi=96;

//Avery 5160 example
$labelTop=.5;
$labelLeft=.19;
$labelWidth=2.63;
$labelHeight=1.00;
$labelMarginRight=.12;
$labelMarginBottom=0;
$rows=10;
$cols=3;

//following are needed to print correctly
$discrepancyTopBottom=.125; #these get subtracted
$discrepancyLeftRight=.125;
$topMargin2ndPageFix=.4; #this is weird; if you need .5, in FF that creates a blank page - so -.1 of target seems to work
if(false && 'stupid browser Chrome'){
	$widthFix=10; #screw Chrome, these are quite extreme and I doubt we'd be on layout
	$heightFix=3;
}

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Labels : {Type Here}</title>
<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<style>
body{
	margin:0px;
	padding:0px;
	}
.page{
	margin-top:<?php echo ($labelTop-$discrepancyTopBottom)*$ppi;?>px;
	margin-right:<?php echo ($labelLeft-$discrepancyLeftRight)*$ppi;?>px;
	margin-bottom:<?php echo ($labelTop-$discrepancyTopBottom)*$ppi;?>px;
	margin-left:<?php echo ($labelLeft-$discrepancyLeftRight)*$ppi;?>px;
	<?php if($labelTestMode){ ?>
	outline:1px dashed darkred;
	<?php } ?>
	}
.label{
	width:<?php echo ($labelWidth*$ppi)-($labelTestMode?2:0)-$widthFix;?>px;
	<?php if($labelTestMode){ ?>
	border:1px solid #666;
	border-radius:5px;
	<?php } ?>
	height:<?php echo ($labelHeight*$ppi)-($labelTestMode?2:0)-$heightFix;?>px;
	margin-right:<?php echo $labelMarginRight*$ppi;?>px;
	margin-bottom:<?php echo $labelMarginBottom*$ppi;?>px;
	float:left;
	}
.labelRight{ margin-right:0px; }
.labelBottom{ margin-bottom:0px; background-color:#ddd;}
.space{
	margin:10px;
	outline:1px dotted #ccc;
	}
.pageBreakBefore{
	page-break-before:always;
	margin-top:<?php echo $labelTop*$ppi;?>px;
	}
.topMargin2ndPageFix{
	width:90%;
	height:<?php echo $topMargin2ndPageFix*$ppi;?>px;
	}
</style>
</head>

<body>
<div class="page">
<?php 
$j=0;
for($i=1; $i<=($lead + $recordCount); $i++){ ?>
<?php
if($i>1 && !fmod($i-1,$rows*$cols)){
	?></div><div class="page pageBreakBefore"><?php
	if($topMargin2ndPageFix){
		?><div class="topMargin2ndPageFix"></div><?php
	}
}
?>
<div class="label<?php if(!fmod($i,$cols))echo ' labelRight'; if(!fmod(ceil($i/$cols),$rows))echo ' labelBottom';?>"><div class="space">
<?php
if($i<=$lead){
	?><span class="gray">(blank lead)</span><?php	
}else{
	$j++;
	?>
	label <?php echo $j;?>
	<?php
}
?>
</div></div>
<?php } ?>
</div>
</body>
</html>
