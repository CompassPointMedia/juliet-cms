<?php
$_POST['Composition']=='template'){
		if($_POST['TemplateMethod']=='url'){
			if(!($buff=implode('',file($_POST['TemplateLocationURL']
//----------------------------- get and display editable regions -------------------------
//match editable region
$templateType='Dreamweaver 4.0';
if(preg_match('/<!--\s*#'.'BeginEditable\s+"([^"]+)"\s*-->/i',$str)){
	$templateType='Dreamweaver 4.0';
	// DW 4.0
	$start='/<!--\s*#'.'BeginEditable\s+"([^"]+)"\s*-->/i';
	$stop='/<!--\s*#'.'EndEditable\s*-->/i';
}else if($templateType=='Dreamweaver 6.0+'){

}else if($templateType=='XML Region'){
	//idea here is the tag name, e.g. div|span|p, containing the attribute, e.g. name= or var= etc.

}

$buff=$str;
while(true){
	//here we toggle through and get the editable regions - much more reliable than regex
	$exp=(!$exp || $exp==$stop ? $start : $stop);
	if(preg_match($exp,$buff,$m)){
		$from=strstr($buff,$m[0]);
		$buff=substr($from, strlen($m[0])-strlen($from));
		if($exp==$start){
			//parse the name of the region
			$name=$m[1];
			//buffer the right string for later
			$buff2=$buff;
		}else{
			//must be stop
			$body=substr($buff2, 0, strlen($buff2) - strlen($buff) - strlen($m[0]));
			//keys are lowercase by convention
			$regions[strtolower($name)]=$body;
		}
	}else{
		break;
	}
	$i++;
	if($i>100){
		//notify admin loop failed
		break;
	}
}
if($regions){
	foreach($regions as $editableName=>$body){
		//fill with the existing SESSION, Db, or HTML in that precedence
		$_SESSION['mail'][$cc]['templates'][$Profiles_ID]['r'][$editableName]=$body;
		$_SESSION['mail'][$cc]['templates'][$Profiles_ID]['rName'][]=$editableName;
	}
}
//-------------------------------------------------------------


if(!$string=@file($TemplateLocationURL)){
	exit("The network template you selected, $TemplateLocationURL, is not available at this time");
}
$string=implode('',$string);
$regexDW40 = '/<!-- #'.'BeginEditable "NAME" -->(.|\s)*?<!-- #'.'EndEditable -->/i';
if(is_array($regions)){
	foreach($regions as $n=>$v){
		$str=str_replace('NAME',$n,$regexDW40);
		$string=preg_replace($str,stripslashes($v),$string);
	}
}
$js='<script>function document.onkeypress(){
	if(event.keyCode==27)window.close();
}
</script>';
print($js.$string);
?>