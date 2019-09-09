<?php
/* name=Sample Component; description=Created after 2 components on 2012-03-14; */

//description of what the component file does here

/*
2012-03-14:

*/



$handle='footer';
if($Parameters=q("SELECT Parameters FROM gen_templates_blocks WHERE Templates_ID=$Templates_ID AND Name='$pJCurrentContentRegion'", O_VALUE)){
	$pJ['componentFiles'][$handle]=unserialize(base64_decode($Parameters));
	/* nodes include: forms; data; format.  forms is unused right now, and data[default] means "across all pages" and is the only part developed */
}
//default variables
if(empty($doSomething))$doSomething=pJ_getdata('doSomething',true);

//default CSS
if(!empty($thisComponentAdditionalCSS))$pJLocalCSS[$handle]=$thisComponentAdditionalCSS;

//for local css links in head of document
if(false)$pJLocalCSSLinks[$handle]='/site-local/somefile.css';

for($__i__=1; $__i__<=1; $__i__++){ //---------------- begin i break loop ---------------

if(!empty($mode) && $mode=='componentEditor'){
	//be sure and fulfill null checkbox fields
	/*
	2012-03-12: this is universal code which should be updated on ALL components.  The objective is that 
	
	*/
	if($_thisnode_){
		/* ----------  this is a single-page, cross-block settings update ---------------  */
		!is_array($pJ['componentFiles'][$handle]) ? $pJ['componentFiles'][$handle]=array() : '';
		//now integrate the form post
		$pJ['componentFiles'][$handle]['data'][$formNode]=stripslashes_deep($_POST[$formNode]);
		$Parameters[$handle]['data'][$formNode]=$pJ['componentFiles'][$handle]['data'][$formNode];
	
		//unlike gen_templates_blocks, place as part of a larger array
		if(q("SELECT * FROM gen_nodes_settings WHERE Nodes_ID='$_thisnode_'", O_ROW)){
			//OK
		}else{
			q("INSERT INTO gen_ncdes_settings SET Nodes_ID='$_thisnode_', EditDate=NOW()");
		}
		q("UPDATE gen_nodes_settings SET Settings='".base64_encode(serialize($Parameters))."' WHERE Nodes_ID='$_thisnode_'");
		prn($qr);
	}else{
		/* ----------  this is a cross-page, single-block settings update ---------------  */
		if($Parameters=q("SELECT Parameters FROM gen_templates_blocks WHERE Templates_ID=$Templates_ID AND Name='$pJCurrentContentRegion'", O_VALUE)){
			$a=unserialize(base64_decode($Parameters));
		}else{
			$a=array();
		}
		!is_array($pJ['componentFiles'][$handle]) ? $pJ['componentFiles'][$handle]=array() : '';
		foreach($a as $n=>$v){
			$pJ['componentFiles'][$handle][$n]=$v;
		}
		//now integrate the form post
		$pJ['componentFiles'][$handle]['data'][$formNode]=stripslashes_deep($_POST[$formNode]);
		q("UPDATE gen_templates_blocks SET Parameters='".base64_encode(serialize($pJ['componentFiles'][$handle]))."' WHERE Templates_ID='$Templates_ID' AND Name='$pJCurrentContentRegion'");
		prn($qr);
	}
	break;
}else if(!empty($formNode) && $formNode=='default' /* ok this is something many component files will contain */){
	?><p>Default Settings Form Here</p><?php
	break;
}else if(!empty($formNode) && $formNode=='additional'){
	?><p>Additional Settings Form Here</p><?php
	break;
}


//------------- sample region $sampleBlock ---------
ob_start();

/* output here */

	//sample calls
    $f = __FILE__;
    $f = (explode('/', $f));
    $f = end($f);
	pJ_call_edit(array(
		'level'=>ADMIN_MODE_DESIGNER,
		'location'=>'JULIET_COMPONENT_ROOT',
		'file'=>$f,
		'thisnode'=>$thisnode,
	));

	pJ_call_edit(array(
		'formNode'=>'folders',
		'level'=>ADMIN_MODE_DESIGNER,
		'thisnode'=>$thisnode,
		'location'=>'JULIET_COMPONENT_ROOT',
		'file'=>$f,
		'parameters'=>array(
			'slide'=>!empty($thisslide) ? $thisslide : '',
		),
	));

	if($adminMode>=ADMIN_MODE_DESIGNER){
		pJ_call_edit(array(
			'formNode'=>'layout',
			'file'=>$f,
			'location'=>'JULIET_COMPONENT_ROOT',
			
		));
	}
require($COMPONENT_ROOT.'/footerCtrl_v120.php');

$footer=ob_get_contents();
ob_end_clean();

echo $footer;





}//---------------- end i break loop ---------------
