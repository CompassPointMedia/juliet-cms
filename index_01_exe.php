<?php
/*
2012-01-28: Post Repository File index_01_exe.php

Starting 1/28/2012, I am using this more for a physical file and putting the exectable coding more into the components themselves, including soon things like contacts since form-building is a module-type activity
* pulled all cgi-related nodes from here

*/
require_once($_SERVER['DOCUMENT_ROOT'].'/config.php');
$qx['defCnxMethod']=C_MASTER;

if(!$suppressPrintEnv){
	if(!empty($_GET)){
		prn('query string:');
		prn($_GET);
	}
	if(!empty($_POST)){
		prn('form post:');
		prn($_POST);
	}
}
// new shutdown coding
$assumeErrorState=true;
register_shutdown_function('iframe_shutdown');

ob_start();
$excludePageFromStats=true;

ob_start();
if($repostID){
	q("UPDATE system_poststorage SET Reposted=Reposted+1 WHERE ID=$repostID", ERR_ECHO);
}else if(count($_POST)){
	$sql="INSERT INTO system_poststorage SET UserName='".($_SESSION['admin']['userName'] ? $_SESSION['admin']['userName'] : $_SESSION['systemUserName'])."', Mode='$mode', Content='".base64_encode(serialize($_POST))."', Session='". base64_encode(serialize($_SESSION)) . "'";
	$Poststorage_ID=q($sql, ERR_ECHO);
}
$err=ob_get_contents();
ob_end_clean();
if($err)mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);

switch(true){
	/* cgi modes are now integrated into index_01_exe.php */
	case $comboMode=='insertUpdate':
	case $mode=='rejectWholesale':
	case $mode=='rejectWholesaleNotify':
	case $mode=='approveWholesale':
		require($_SERVER['DOCUMENT_ROOT'].'/components-juliet/cgi.php');
	break;
	case $mode=='addcart':
	case $mode=='addcartAPI':
		if(is_array($ID)){
			//we could do better than this
			foreach($ID as $n=>$v){
				if(!strlen($v)) error_alert('Please select all of the features for the product you selected');
			}
		}
		if(isset($SKU_SUFFIX))$SKU_SUFFIX='-'.$SKU_SUFFIX;
		if(isset($Description_PREFIX))$Description_PREFIX.=' - ';
		if(!$qty)$qty=1;
		for($i=1; $i<=$qty; $i++){
			shopping_cart($ID, 1, $options=array(
				/* courses must each be taken by individuals */
				'combineItems'=>false
			));
		}
		if(count($_SESSION['shopCart']['default']))
		foreach($_SESSION['shopCart']['default'] as $n=>$v){
			$total+=$v['Quantity'];
		}
		if($mode!=='addcartAPI'){
			?><script language="javascript" type="text/javascript">
			//change courses in cart value
			try{
			window.parent.g('orderCount').innerHTML='<?php echo $total?>';
			}catch(e){ }
			try{
			window.parent.g('added<?php echo $ID?>').style.visibility='visible';
			}catch(e){ }
			</script><?php
		}
	break;
	case $mode=='contact':
        //NOTE: pretty low security - someone could just post with the fields unset
        if(isset($_q) && isset($_r)){
            //jasperandwendy
            $r=$_POST['_res'][$_POST['_r']];
            $q=$_POST['_q'];
            for($i=2; $i<=min($r-2,22); $i++){
                if( round(sqrt($i) / pow($r - $i, .3333),4) == round($q,4))$pass=true;
            }
            if(!$pass)error_alert('You are either not a human being or you made a simple math error (which Shakespeare would find ironic, if you think about it).  We are divine and forgive you.  Check the sum of the two numbers and try again.');
        }

        if(!preg_match('/^[-_.a-z0-9]+@[-a-z0-9]+(\.[-a-z0-9]+){1,}$/i',$Email))error_alert('Enter a valid email');
		if((!$FirstName || !$LastName))	error_alert('You must enter a first and last name');

		$_POST['Submitted at']=date('m/d/Y g:iA');
		foreach($_POST as $n=>$v){
			if(preg_match('/recaptcha|mode/',$n))continue;
			$str.=$n . ': '. stripslashes($v)."\n";
		}
		$to=($adminEmail ? $adminEmail : $developerEmail);
		if($to!==$developerEmail)$to.=','.$developerEmail;
		$result1 = mail($to,'Contact form submission',str_replace("\t",'', "The following information was submitted:\n\n
		$str"),'From: '.$Email);
		//customer copy
		$result2 = mail($Email,'Contact form submission',str_replace("\t",'', "The following is your copy of the request you submitted:\n\n
		$str"),'From: '.$to);
		?><script language="javascript" type="text/javascript">
		alert('Thank you for your request.  We will be replying as soon as possible.');
		window.parent.location='/';
		</script><?php
		$assumeErrorState=false;
	break;
	case $mode=='message':
        //NOTE: pretty low security - someone could just post with the fields unset
        if(isset($_q) && isset($_r)){
            //jasperandwendy
            $r=$_POST['_res'][$_POST['_r']];
            $q=$_POST['_q'];
            for($i=2; $i<=min($r-2,22); $i++){
                if( round(sqrt($i) / pow($r - $i, .3333),4) == round($q,4))$pass=true;
            }
            if(!$pass)error_alert('You are either not a human being or you made a simple math error (which Shakespeare would find ironic, if you think about it).  We are divine and forgive you.  Check the sum of the two numbers and try again.');
        }

		if(!preg_match('/^[-_.a-z0-9]+@[-a-z0-9]+(\.[-a-z0-9]+){1,}$/i',$Email))error_alert('Enter a valid email');
		if(!$Name) error_alert('Please enter your name');

		$_POST['Submitted at']=date('m/d/Y g:iA');
		foreach($_POST as $n=>$v){
			if(preg_match('/recaptcha|mode/',$n))continue;
			$str.=$n . ': '. stripslashes($v)."\n";
		}
		$to=($adminEmail ? $adminEmail : $developerEmail);
		if($to!==$developerEmail)$to.=','.$developerEmail;
		mail($to,'Message submission',str_replace("\t",'', "The following information was submitted:\n\n
		$str"),'From: '.$Email);
		//customer copy
		mail($Email,'Message submission',str_replace("\t",'', "The following is your copy of the message you submitted:\n\n
		$str"),'From: '.$adminEmail);
		?><script language="javascript" type="text/javascript">
		alert('Thank you for your request. We will be replying as soon as possible.');
		window.parent.location='/';
		</script><?php
		$assumeErrorState=false;
	break;
	case $mode=='updateMetaTags':
		if(!$adminMode)error_alert('You can only perform this task in Administrative Access Mode');
		$thispage=$_POST['thispage'];
		$thisfolder=$_POST['thisfolder'];
		//call function twice
		metatags_i1('title');
		metatags_i1('meta');
		prn($metatags);
		extract($metatags['record']);
		//parse the query string
		if($_POST['QUERY_STRING']){
			//HOPE IT'S ENCODED RIGHT!
			$a=explode('&',$_POST['QUERY_STRING']);
			foreach($a as $v){
				$b=explode('=',$v);
				$c[$b[0]]=urldecode($b[1]);
			}
			foreach($c as $n=>$v){
				//globalize
				$$n=$v;
			}
		}
		//title first
		if(isset($MetaTitle)){
			if($TTable && $TField && $TVar1){
				if(!preg_match('/^[a-z0-9_]+$/i',$TField)){
					//no go
				}else{
					$and=(strlen($TVar2) ? " AND $TVar2='".$$TVar2."'" : '');
					if(q("SELECT * FROM $TTable WHERE $TVar1='".$$TVar1."' $and", O_ROW)){
						//update
						ob_start();
						q("UPDATE $TTable SET $TField='".$MetaTitle."' WHERE $TVar1='".$$TVar1."'", ERR_ECHO);
						$err=ob_get_contents();
						ob_end_clean();
						prn($qr);
						if($err)mail($developerEmail,'error in file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
					}else{
						//insert
						q("INSERT INTO $TTable SET $TVar1='".$$TVar1."'".($and ? ", $TVar2='".$$TVar2."'" : '').", $TField='".$MetaTitle."', CreateDate=NOW(), Creator='system'");
					}
				}	
			}else{
				if($r=q("SELECT * FROM site_metatags WHERE ThisFolder='$thisfolder' AND ThisPage='$thispage'", O_ROW)){
					q("UPDATE site_metatags SET Title='$MetaTitle', EditDate=NOW() WHERE ThisFolder='$thisfolder' AND ThisPage='$thispage'");
					prn($qr);
					if(!$qr['affected_rows'])mail($developerEmail,'error in file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
				}else{
					q("INSERT INTO site_metatags SET Title='$MetaTitle', EditDate=NOW(), ThisFolder='$thisfolder', ThisPage='$thispage'");
				}
			}
		}	

		//description
		if(isset($MetaDescription)){
			if($DTable && $DField && $DVar1){
				if(!preg_match('/^[a-z0-9_]+$/i',$DField)){
					//no go
				}else{
					$and=(strlen($DVar2) ? " AND $DVar2='".$$DVar2."'" : '');
					if(q("SELECT * FROM $DTable WHERE $DVar1='".$$DVar1."' $and", O_ROW)){
						//update
						ob_start();
						q("UPDATE $DTable SET $DField='".$MetaDescription."' WHERE $DVar1='".$$DVar1."'", ERR_ECHO);
						$err=ob_get_contents();
						ob_end_clean();
						prn($qr);
						if($err)mail($developerEmail,'error in file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
					}else{
						//insert
						q("INSERT INTO $DTable SET $DVar1='".$$DVar1."'".($and ? ", $DVar2='".$$DVar2."'" : '').", $DField='".$MetaDescription."', CreateDate=NOW(), Creator='system'");
					}
				}	
			}else{
				if($r=q("SELECT * FROM site_metatags WHERE ThisFolder='$thisfolder' AND ThisPage='$thispage'", O_ROW)){
					q("UPDATE site_metatags SET Description='$MetaDescription', EditDate=NOW() WHERE ThisFolder='$thisfolder' AND ThisPage='$thispage'");
					prn($qr);
					if(!$qr['affected_rows'])mail($developerEmail,'error in file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
				}else{
					q("INSERT INTO site_metatags SET Description='$MetaDescription', EditDate=NOW(), ThisFolder='$thisfolder', ThisPage='$thispage'");
				}
			}
		}	
		//keywords
		if(isset($MetaKeywords)){
			if($KTable && $KField && $KVar1){
				if(!preg_match('/^[a-z0-9_]+$/i',$KField)){
					//no go
				}else{
					$and=(strlen($KVar2) ? " AND $KVar2='".$$KVar2."'" : '');
					if(q("SELECT * FROM $KTable WHERE $KVar1='".$$KVar1."' $and", O_ROW)){
						//update
						ob_start();
						q("UPDATE $KTable SET $KField='".$MetaKeywords."' WHERE $KVar1='".$$KVar1."'", ERR_ECHO);
						$err=ob_get_contents();
						ob_end_clean();
						prn($qr);
						if($err)mail($developerEmail,'error in file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
					}else{
						//insert
						q("INSERT INTO $KTable SET $KVar1='".$$KVar1."'".($and ? ", $KVar2='".$$KVar2."'" : '').", $KField='".$MetaKeywords."', CreateDate=NOW(), Creator='system'");
					}
				}	
			}else{
				if($r=q("SELECT * FROM site_metatags WHERE ThisFolder='$thisfolder' AND ThisPage='$thispage'", O_ROW)){
					q("UPDATE site_metatags SET Keywords='$MetaKeywords', EditDate=NOW() WHERE ThisFolder='$thisfolder' AND ThisPage='$thispage'");
					prn($qr);
					if(!$qr['affected_rows'])mail($developerEmail,'error in file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
				}else{
					q("INSERT INTO site_metatags SET Keywords='$MetaKeywords', EditDate=NOW(), ThisFolder='$thisfolder', ThisPage='$thispage'");
				}
			}
		}
		?><script>
		window.parent.g('adminModeUpdate').disabled=false;
		</script><?php
		$assumeErrorState=false;
	break;
	case $mode=='configureMetatags':
		if(!function_exists('sql_insert_update_generic')){
			require('functions/function_sql_insert_update_generic_v100.php');
		}
		$sql=sql_insert_update_generic($MASTER_DATABASE,'site_metatags','REPLACE INTO');
		prn($sql);
		ob_start();
		q($sql, ERR_ECHO);
		$err=ob_get_contents();
		ob_end_clean();
		if($err){
			mail($developerEmail,'error in file '.__FILE__.', line '.__LINE__,get_globals(), $fromHdrBugs);
			error_alert('Abnormal error in submitting config - developer notified');
		}else{
			?><script defer="defer">
			alert('Changes made');
			//window.parent.location='<?php echo $referer;?>';
			</script><?php
		}
	break;
	case $mode=='insertLink':
	case $mode=='updateLink':
	case $mode=='deleteLink':
		if(!$adminMode)error_alert('You are out of admin mode; your session probably timed out');
		if($mode=='deleteLink'){
			q("DELETE FROM gen_links WHERE ID='$Links_ID'");
			?><script>
			try{
			window.parent.g('link<?php echo $Links_ID?>').style.display='none';
			<?php
			if($currentLink==$Links_ID){
			if($new=q("SELECT ID FROM gen_links ORDER BY Idx LIMIT 1", O_VALUE)){
				?>window.parent.location='/summary.php?get=1&links_id=<?php echo $new?>';<?php
			}else{
				?>window.parent.location='/index.php';<?php
			}
			}?>
			}catch(e){ }
			</script><?php	
			$assumeErrorState=false;
			exit;
		}
		if(!$Name)error_alert('A name is required for each link');
		$sql=sql_insert_update_generic($MASTER_DATABASE,'gen_links', ($mode=='insertLink' ? 'INSERT' : 'UPDATE'));
		$Links_ID=q($sql, O_INSERTID);
		prn($qr);
		?><script defer="defer"><?php
		if($mode=='insertLink'){
			?>
			if(confirm('Switch to new page?')){
				window.parent.opener.location='/summary.php?get=1&links_id=<?php echo $Links_ID?>';
			}
			<?php
		}
		?>
		window.parent.close();
		</script><?php
	break;
	case $mode=='editTextRegion':
		if(!$adminMode)error_alert('You are not in admin mode; please sign in again');
		if($text=q("SELECT * FROM gen_textareas WHERE Name='$Name'", O_ROW)){
			q("UPDATE gen_textareas SET Type='$Type',Name='$Name', Body='$Body' WHERE Name='$Name'");
		}else{
			q("INSERT INTO gen_textareas SET Type='$Type',Name='$Name', Body='$Body'");
		}
		$text=q("SELECT Body, Type FROM gen_textareas WHERE Name='$Name'", O_ROW);
		?><div id="textRegion-<?php echo $Name?>">
		<!-- function get_text_region(), v1.0, passed name: <?php echo $Name?> --><?php
		if($text['Type']==2){
			echo $text['Body'];
		}else if($text['Type']==1 || true){
			echo $text['Body'];
		}
		?></div>
		<script>
		window.parent.g('textRegion-<?php echo $Name?>').innerHTML=document.getElementById('textRegion-<?php echo $Name?>').innerHTML;
		window.parent.toggletr(g('toggle-<?php echo $Name?>'));
		</script><?php
	break;
	case $mode=='refreshComponent':
		$refreshComponentOnly=true;
		if(false && strstr($component,':')){
			//2013-05-13 I pulled this over but it was NOT used
			$a=explode(':',$component);
			if(md5($a[1].$MASTER_PASSWORD)!=$a[2])error_alert('Improper key passage for dynamic file component call');
			//2012-02-07: the component has self-validated; create this node on the fly
			if(strstr($a[1],'/')){
				$b=explode('/',$a[1]);
				$c=array_pop($b);
				if(count($c)==1 && strlen($GLOBALS[$c[0]])){
					$registeredComponents[$a[0]]=$GLOBALS[$c[0]].'/'.$c;
				}else{
					$registeredComponents[$a[0]]=implode('/',$b).'/'.$c;
				}
			}else{
				$registeredComponents[$a[0]]=$COMPONENT_ROOT.'/'.$a[1];
			}
			//this relates output (div id) to the component
			$component=$a[0];
		}else if(file_exists($_SERVER['DOCUMENT_ROOT'].'/components-juliet/'.$component.'.php')){
			require($_SERVER['DOCUMENT_ROOT'].'/components-juliet/'.$component.'.php');
		}else{
			mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals($err='Component does not exist.'),$fromHdrBugs);
			error_alert('The component '.$component.' needs to exist as '.$_SERVER['DOCUMENT_ROOT'].'/components-juliet/'.$component.'.php');
		}
	break;
	case $mode=='navMonthEventCalendar':
		if($mode=='fetchEventsEventCalendar')$eventDate="$year-$month-$day";
		//require('./console/components/calWidget.php');

		//coding matches parent cal
		$thispage='wedding-calendar';
		require($_SERVER['DOCUMENT_ROOT'].'/components-juliet/cpm160.scheduler.php');
		?><script language="javascript" type="text/javascript">
		window.parent.g('cal').innerHTML=document.getElementById('cal').innerHTML;
		window.parent.g('selectStatus').innerHTML=' ';
		</script><?php	
	break;
	case $mode=='RSSFeed':
		//2011-03-15: output in a fairly simple manner
		header('Content-Type: text/xml');
echo '<?xml version="1.0" encoding="utf-8"'.'?'.'>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
	<channel>';?>
		<title><?php echo $companyName.':'.$siteURL?>: Articles</title>
		<link>http://<?php echo $siteURL?></link>
		<description><?php echo $RSSFeedArticleDescription;?></description>
		<?php if(file_exists($_SERVER['DOCUMENT_ROOT'].'/images/assets/rss-logo.jpg')){ ?><image>
			<title><?php echo $companyName?>: Articles</title>
			<url>http://<?php echo $siteURL?>/images/assets/rss-logo.jpg</url>
			<link>http://<?php echo $siteURL;?></link>
		</image><?php }?>
		<?php
		if($articles=q("SELECT * FROM cms1_articles WHERE
		".($_SESSION['cnx'][$cnxKey] ? '' : 'Private=0 AND ')."
		Active=1 AND 
		Category='Article'
		ORDER BY PostDate DESC LIMIT 15", O_ARRAY))
		foreach($articles as $v){
		extract($v);
		?><item>
			<title><?php echo $Title?></title>
			<link>http://<?php echo $siteURL?>/<?php echo trim($KeywordsTitle) ? $KeywordsTitle : 'articles.php?ID='.$ID?></link>
			<pubDate><?php echo date('r',strtotime($PostDate));?></pubDate>
			<description><?php echo $Description;?></description>
			<guid>http://<?php echo $siteURL?>/articles/item<?php echo $ID?></guid>
			<category>Article</category>
		</item><?php
		}
	echo '	</channel>
</rss>';
		$suppressNormalIframeShutdownJS=true;
		$assumeErrorState=false;
		exit;
	break;
	case $mode=='SNWLink':
		require($COMPONENT_ROOT.'/SNW_widget_v101.php');
	break;
	case $mode=='forms':
		//2012-02-25 - new method, simple, what am I missing here to make form development faster and simpler?
		$refreshComponentOnly=true;
		require($_SERVER['DOCUMENT_ROOT'].'/'.$ComponentLocation);
	break;
	case $ComponentLocation:
		if(file_exists($JULIET_COMPONENT_ROOT.'/'.$acct.'.'.$ComponentLocation.'.php')){
			require($JULIET_COMPONENT_ROOT.'/'.$acct.'.'.$ComponentLocation.'.php');
		}else if(file_exists($JULIET_COMPONENT_ROOT.'/'.$ComponentLocation.'.php')){
			require($JULIET_COMPONENT_ROOT.'/'.$ComponentLocation.'.php');
		}else{
			mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals($err='Unable to locate component '.$ComponentLocation),$fromHdrBugs);
			error_alert($err);			
		}
	break;
	case $submode=='blockManager':
	case $submode=='codingManager':
	case $submode=='pageManager':
	case $submode=='stylesheetManager':
	case $submode=='jsManager':
		require('_juliet_.settings.php');
	break;
	case $mode=='componentEditor':
		$hasAdmin=false;
		if($a=$_SESSION['cnx'][$acct]['accesses'])foreach($a as $v)if(preg_match('/^(admin|db admin)$/i',$v)){
			$hasAdmin=true;
			break;
		}
		if($adminMode<ADMIN_MODE_EDITOR /*1*/ && !$hasAdmin)exit('You are logged out and cannot do this');
	case $mode=='componentControls':
		$refreshComponentOnly=true;
		//this is used in for example _juliet_.settings.php; call it directly before calling the component
		$pJModalInclusion=true;
		require($pJulietTemplate);

		require($$location.'/'.$file);

		if($mode=='componentEditor' && $refreshOpener){
			?><script language="javascript" type="text/javascript">
			//try{
			var l=window.parent.opener.location+'';
			l=l.replace(/&*r=[.0-9]+/,'');
			var r=Math.random();
			r=(r+'').replace('0.','').substring(0,4);
			l=l+(l.indexOf('?')!= -1 ? '' : '?') + '&r=' + r;
			window.parent.opener.location=l;
			//}catch(e){ }
			</script><?php
		}
		if($mode=='componentEditor'){
			?><script language="javascript" type="text/javascript">
			window.parent.detectChange=0;
			</script><?php
		}
	break;
	default:
		require($_SERVER['DOCUMENT_ROOT'] . '/functions/function_what_happened_v100.php');
		what_happened();
		mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,get_globals(),$fromHdrBugs);
		?><script language="javascript" type="text/javascript">
		alert('No mode passed, or mode <?php echo $mode?> is not recognized');
		</script><?php
}
$assumeErrorState=false;
?>