<?php
/* Created 2010-04-13 by Samuel with Parker helping; generic footer control */
if(!$footerCtrlURL)$footerCtrlURL='http://www.compasspointmedia.com/admin_help.php?ref='.$MASTER_DATABASE;

if(!$footerCtrlLabelEditor)$footerCtrlLabelEditor='Site Editor';
if(!$footerCtrlLabelConsole)$footerCtrlLabelConsole='Admin Console';
if(!$footerCtrlLabelAdminHelp)$footerCtrlLabelAdminHelp='Admin Help';
if(!$footerCtrlLabelSiteMap)$footerCtrlLabelSiteMap='[Site Map]';
 
 
?>
<div id="footerCtrls">
<?php
if(!$hideSiteEditorLink){
	if($siteEditorLinkType=='cgi'){
		$link=(stristr($_SERVER['SERVER_NAME'],'relatebase-rfm.com') ? '/~'.$MASTER_DATABASE : '').'/cgi/login.php?'.($adminMode ? 'logout=1&' : '').'src='.urlencode($_SERVER['REQUEST_URI']);
	}else if($siteEditorLinkType=='console'){
		$link=(stristr($_SERVER['SERVER_NAME'],'relatebase-rfm.com') ? '/~'.$MASTER_DATABASE : '').'/console/admin.php?'.($adminMode ? 'logout=1&' : '').'src='.urlencode($_SERVER['REQUEST_URI']);
	}else{
		//basic login method
		$link=(stristr($_SERVER['SERVER_NAME'],'relatebase-rfm.com') ? '/~'.$MASTER_DATABASE : '').'/admin.php?'.($adminMode ? 'logout=1&' : '').'src='.urlencode($_SERVER['REQUEST_URI']);
	}
	?>
	<span class="editor">[<a href="<?php echo $link?>" title="<?php echo $siteName?> real-time site editor"><?php echo $adminMode?'Leave ':''?><?php echo $footerCtrlLabelEditor ?></a>]</span>
<?php } ?>
<?php if(!$hideConsoleLink){ ?>
	<span class="console">[<a href="/console/" title="<?php echo $siteName?> administrative console"><?php echo $footerCtrlLabelConsole?></a>]</span>
<?php } ?>
<?php if(!$hideAdminHelp){ ?>
	<span class="console">[<a href="<?php echo $footerCtrlURL?>" title="<?php echo $siteName?> administrative console"><?php echo $footerCtrlLabelAdminHelp?></a>]</span>
<?php } ?>
<?php if(!$hideSiteMap){?>
	<span class="siteMap"><a href="../<?php echo strtolower(implode("-",explode(" ",$companyName)))?>-site-map.php" title="The site map of <?php echo $companyName?>"><?php echo $footerCtrlLabelSiteMap?></a></span>	
<?php }?>

</div>