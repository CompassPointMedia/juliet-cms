<?php
/* name=Sample Component; description=Created after 2 components on 2012-03-14; */

//description of what the component file does here

/*
2012-05-15:
	pulled over import and export functionality from slidenav_v120 - sweet!
2012-04-15:
	building this:
		1. choose one $Parameters method
2012-03-14:

*/



$handle='nameOrFunctionOfComponentFile';
//2012-03-10: pull parameters for this component file - note that this is in gen_nodes_settings.Settings vs. gen_templates_blocks.Parameters
if($Parameters=q("SELECT Settings FROM gen_nodes_settings WHERE Nodes_ID='".($_thisnode_ ? $_thisnode_ : $thisnode) ."'", O_VALUE)){
	$Parameters=unserialize(base64_decode($Parameters));
	if($Parameters[$handle])$pJ['componentFiles'][$handle]=$Parameters[$handle];
	/* nodes include: forms; data; format.  forms is unused right now, and data[default] means "across all pages" and is the only part developed */
}else{
	unset($pJ['componentFiles'][$handle]);
}
/* or this.. */
if($Parameters=q("SELECT Parameters FROM gen_templates_blocks WHERE Templates_ID=$Templates_ID AND Name='$pJCurrentContentRegion'", O_VALUE)){
	$pJ['componentFiles'][$handle]=unserialize(base64_decode($Parameters));
	/* nodes include: forms; data; format.  forms is unused right now, and data[default] means "across all pages" and is the only part developed */
}else{
	unset($pJ['componentFiles'][$handle]);
}
/* or this.. */
if($Parameters=q("SELECT Options FROM cmsb_sections WHERE ID='$thissection'", O_VALUE)){
	$Parameters=unserialize(base64_decode($Parameters));
	if($Parameters[$handle])$pJ['componentFiles'][$handle]=$Parameters[$handle];
}else{
	unset($pJ['componentFiles'][$handle]);
}



//default variables
if(!$doSomething)$doSomething=pJ_getdata('doSomething',true);

//default CSS
if($thisComponentAdditionalCSS)$pJLocalCSS[$handle]=$thisComponentAdditionalCSS;

//for local css links in head of document
if(false)$pJLocalCSSLinks[$handle]='/site-local/somefile.css';

for($__i__=1; $__i__<=1; $__i__++){ //---------------- begin i break loop ---------------

if($mode=='componentEditor'){
	//be sure and fulfill null checkbox fields
	/*
	2012-03-12: this is universal code which should be updated on ALL components.  The objective is that 
	
	*/
	if($submode=='export')ob_start();
	if($thissection){
		/* ----------  stored in cmsb_sections -------------- */
		!is_array($pJ['componentFiles'][$handle]) ? $pJ['componentFiles'][$handle]=array() : '';
		//now integrate the form post
		$pJ['componentFiles'][$handle]['data'][$formNode]=stripslashes_deep($_POST[$formNode]);
		$Parameters[$handle]['data'][$formNode]=$pJ['componentFiles'][$handle]['data'][$formNode];
	
		//unlike gen_templates_blocks, place as part of a larger array
		if(q("SELECT * FROM cmsb_sections WHERE Section='$thissection'", O_ROW)){
			//OK
		}else{
			q("INSERT INTO cmsb_sections SET Section='$thissection', EditDate=NOW()");
		}
		q("UPDATE cmsb_sections SET Options='".base64_encode(serialize($Parameters))."' WHERE Section='$thissection'");
		prn($qr);
	}else if($_thisnode_){
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
		$Parameters=$pJ['componentFiles'][$handle];
		q("UPDATE gen_templates_blocks SET Parameters='".base64_encode(serialize($Parameters))."' WHERE Templates_ID='$Templates_ID' AND Name='$pJCurrentContentRegion'");
		prn($qr);
	}
	if($submode=='import'){
		if($ImportMerge){
			$a=unserialize(base64_decode($ImportString));
			$ImportString=base64_encode(serialize(array_merge_accurate($Parameters,$a)));
		}else{
			//no action
		}
		switch(true){
			case strlen($thissection):
				q("UPDATE cmsb_sections SET Options='".$ImportString."' WHERE Section='$thissection'");
			break;
			case strlen($_thisnode_):
				q("UPDATE gen_nodes_settings SET Settings='".$ImportString."' WHERE Nodes_ID='$_thisnode_'");
			break;
			default:
				q("UPDATE gen_templates_blocks SET Parameters='".$ImportString."' WHERE Templates_ID='$Templates_ID' AND Name='$pJCurrentContentRegion'");
		}
		?><script language="javascript" type="text/javascript">
		alert('Your settings have been successfully imported.  Juliet will now reload this page');
		var l=window.parent.location+'';
		window.parent.location=l;
		</script><?php
	}
	if($submode=='export'){
		ob_end_clean();
		$str='-- Juliet version '.$pJVersion.', file '.end(explode('/',__FILE__)).'; exported '.date('n/j/Y \a\t g:iA').' - to re-import, paste the code below into the desired component ----'."\n";
		attach_download('', $str.base64_encode(serialize($Parameters)), str_replace('.php','',end(explode('/',__FILE__))).'_'.date('Y-m-d_his').'.txt');
	}


	break;
}else if($formNode=='default' /* ok this is something many component files will contain */){
	?><p>Default Settings Form Here</p><?php
	break;
}else if($formNode=='additional'){
	?><p>Additional Settings Form Here</p><?php
	break;
}


//------------- sample region $sampleBlock ---------
ob_start();

/* output here */

	//sample calls
	pJ_call_edit(array(
		'level'=>ADMIN_MODE_DESIGNER,
		'location'=>'JULIET_COMPONENT_ROOT',
		'file'=>end(explode('/',__FILE__)),
		'thisnode'=>$thisnode,
	));

	pJ_call_edit(array(
		'formNode'=>'folders',
		'level'=>ADMIN_MODE_DESIGNER,
		'thisnode'=>$thisnode,
		'location'=>'JULIET_COMPONENT_ROOT',
		'file'=>end(explode('/',__FILE__)),
		'parameters'=>array(
			'slide'=>$thisslide,
		),
	));

	if($adminMode>=ADMIN_MODE_DESIGNER){
		pJ_call_edit(array(
			'formNode'=>'layout',
			'file'=>end(explode('/',__FILE__)),
			'location'=>'JULIET_COMPONENT_ROOT',
			
		));
	}



$sampleBlock=ob_get_contents();
ob_end_clean();

//------------- sample region $sampleBlock_2 ---------
ob_start();

/* output here */


$sampleBlock_2=ob_get_contents();
ob_end_clean();






}//---------------- end i break loop ---------------
?>