<?php
/* name=Basic Page Creator; description=uses folder and file to create a placeholder php page, created 2012-06-28; */

$handle='pagecreator';
//2012-03-10: pull parameters for this component file - note that this is in gen_nodes_settings.Settings vs. gen_templates_blocks.Parameters
if($Parameters=q("SELECT Settings FROM gen_nodes_settings WHERE Nodes_ID='".($_thisnode_ ? $_thisnode_ : $thisnode) ."'", O_VALUE)){
	$Parameters=unserialize(base64_decode($Parameters));
	if($Parameters[$handle])$pJ['componentFiles'][$handle]=$Parameters[$handle];
	/* nodes include: forms; data; format.  forms is unused right now, and data[default] means "across all pages" and is the only part developed */
}else{
	unset($pJ['componentFiles'][$handle]);
}

for($__i__=1; $__i__<=1; $__i__++){ //---------------- begin i break loop ---------------

if($mode=='componentEditor'){
	//be sure and fulfill null checkbox fields

	if($submode=='export')ob_start();
    if($submode == 'updateFile'){
        if(empty($editlocation) || empty($editfile)) error_alert('Missing values for $editlocation or $editfile');
        if(!file_exists($$editlocation.'/'.$editfile)) error_alert('File does not exist: '.$editlocation.'/'.$editfile);
        if(!is_writable($$editlocation.'/'.$editfile)) error_alert('That file is not writable for me ('.trim(`whoami`).')!  Check permissions for the folder(s) and file: '.$$editlocation.'/'.$editfile);

        $path = $$editlocation.'/'.$editfile;


        $lines = file($path);
        $inSection = false;
        $newFile = [];
        $position = '';
        $name = '';
        foreach($lines as $line){
            if(preg_match('/\/\/-{3,} (begin|end) ([ a-z0-9-]+) -{3,}/i', $line, $m)){
                $position = $m[1];
                $name = $m[2];
            }
            if($position == 'begin' && isset($sections[$name])){
                // write the start of the line as-is
                $newFile[] = $line;

                //rewrite the PHP break-out
                $newFile[] = '?>'."\n";

                //add the new content
                $newFile[] = rtrim( stripslashes($sections[$name]) ) . "\n";

                $position = '';
                $inSection = true;
                continue;
            }
            if($position == 'end'){
                //write the PHP break-in
                $newFile[] = '<?php '."\n";

                //rewrite the end of line as-is
                $newFile[] = $line;

                $position = '';
                $inSection = false;
                continue;
            }

            if($inSection) continue;
            $newFile[] = $line;
        }
        $working = trim( implode('',$newFile) ) . "\n";
        prn('------------------');
        prn($working);
        if($working){

            $backup = preg_replace('/\.php$/', '.bk'.date('YmdHis').'.php', $path);
            $fp = fopen($backup, 'w');
            fwrite($fp, implode('', $lines));
            fclose($fp);

            try{
                $fp = fopen($path, 'w');
                fwrite($fp, $working);
                fclose($fp);
            }catch(Exception $e){
                mail($developerEmail, 'Error in page creator', 'In '.__FILE__.' line '.__LINE__, 'From: errors@'.$adminDomain);
            }

        }

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

    prn($$editlocation.'/'.$editfile);

    //get file
    $c=file($$editlocation.'/'.$editfile);

    ob_start();
    highlight_string(implode('',$c));
    $c_highlighted = ob_get_contents();
    ob_end_clean();

    $sections = [];
    $state = '';
    $section = '';
    foreach ($c as $line){

        if($delimiter = preg_match('/^\/\/[-]{3,} (begin|end) ([ a-z0-9]+) [-]{3,}$/', trim($line), $m)){
            $state = $m[1];
            $section = $m[2];
            continue;
        }
        // this is necessary to handle PHP break-out lines below
        if(!trim($line)) continue;
        if($state == 'begin'){
            $sections[$section][] = $line;
        }
    }
    if(!empty($sections)){
        foreach($sections as $section => $lines){
            if(preg_match('/^\s*\?>/i', $lines[0], $m)){
                $lines[0] = substr($lines[0], strlen($m[0]));
            }
            if(preg_match('/<\?php\s*$/i', $lines[count($lines)-1], $m)){
                $lines[count($lines)-1] = substr($lines[count($lines)-1], 0, strlen($lines[count($lines)-1]) - strlen($m[0]));
            }
            //Clean up
            if(!trim($lines[count($lines)-1])) unset($lines[count($lines)-1]);
            if(!trim($lines[0])) unset($lines[0]);
            ?><h3><?php echo $section;?></h3>
            <p class="gray">(You are in HTML!  If you want PHP you must add PHP tags)</p>
            <textarea name="sections[<?php echo $section;?>]" rows="7" cols="85%"><?php echo htmlspecialchars(implode('', $lines));?></textarea>
            <br /><br />
            <?php
        }
    }
    ?>
    <input type="hidden" name="editlocation" value="<?php echo $editlocation;?>" />
    <input type="hidden" name="editfile" value="<?php echo $editfile;?>" />

    <script language="javascript" type="text/javascript">
		$(document).ready(function(){
			g('submode').value='updateFile';
		});
    </script><?php

	break;
}else if($formNode=='additional'){
	?><p>Additional Settings Form Here</p><?php
	break;
}





$str=$acct.($thisfolder?'.'.$thisfolder:'').($thissubfolder?'.'.$thissubfolder:'').($thispage?'.'.$thispage:'').'.php';
if(!is_dir($PAGE_ROOT) && !mkdir($PAGE_ROOT))error_alert('Unable to create folder "/pages" for the page creator');
if(!file_exists($PAGE_ROOT.'/'.$str)){
	$code='<?php
    /*
    This is a code block created account.file=$str on $date by pagecreator.php.  It is designed to be a vertical component for this page only and regardless of QS parameters.
    */
    //------------- mainRegionCenterContent ---------
    ob_start();
    //edit call
    pJ_call_edit(array(
        \'level\'=>ADMIN_MODE_DESIGNER,
        \'location\'=>\'JULIET_COMPONENT_ROOT\',
        \'file\'=>\'pagecreator.php\',
        \'thisnode\'=>$thisnode,
        \'parameters\'=>array(
            \'editlocation\'=>\'PAGE_ROOT\',
            \'editfile\'=>end(explode(\'/\',__FILE__)),
        ),
    ));
    // Define as many page blocks as you want to here:
    ?>
    <?php
    if($adminMode){
        //--------------- begin administrator content ---------------
        ?>
        <h2>The Juliet system has just created a content file</h2>
        <p>This file named <?php echo $str;?> is found in the /pages directory - please edit this page accordingly (and remove this message you\'ll see as well, found on line <?php echo __LINE__;?>)</p>
        <?php
        //--------------- end administrator content ---------------
    }
    
    //--------------- begin public content ---------------
    ?>
    
    <h2>Juliet CMS</h2>
    
    <?php
    //--------------- end public content ---------------
    
    // This id mainRegionCenterContent
    $mainRegionCenterContent=ob_get_contents();
    ob_end_clean();
    ?>';
	$code=str_replace('$str',$str,$code);
	$code=str_replace('$date',date('F jS Y \a\t g:iA'),$code);
	// 2017-07-15 SF: bug - this is not working in prod even though permissions seem right to me
    try{
        $fp=fopen($PAGE_ROOT.'/'.$str,'w');
        fwrite($fp,$code,strlen($code));
        fclose($fp);
    }catch(Exception $e){
        mail($developerEmail, 'Error in page creator', 'In '.__FILE__.' line '.__LINE__, 'From: errors@'.$_SERVER['SERVER_NAME']);
    }
}

try{
    // 2017-07-15 SF: bug - this is not working in prod even though permissions seem right to me
    // using include() vs require
    include($PAGE_ROOT . '/' . $str);
}catch(Exception $e){
    mail($developerEmail, 'Error in page creator', 'In '.__FILE__.' line '.__LINE__, 'From: errors@'.$_SERVER['SERVER_NAME']);
}




}//---------------- end i break loop ---------------
