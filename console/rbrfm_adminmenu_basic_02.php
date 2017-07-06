<?php
/*
upgrade on menu 2011-03-15: used Gioco saCar e logic for more consistent control
See the DHTML Menu notes in console/docs.txt
moved from the library item 2010-08-24: saves space

*/

ob_start();
?><div id="header">
	<?php if($consoleMethod=='usemod'){ ?>
	<div class="fr">
	<?php echo $_SESSION['firstName']. ' '.$_SESSION['lastName'];?>
	&nbsp;&nbsp;
	<a href="/console/?logout=2">sign out</a>
	</div>
	<?php }?>
	<h2 class="main" style="font-weight:400;"><a href="../console/home.php" title="Site Administration Home Page">
	<?php
	if(strlen($adminLogo) && file_exists($_SERVER['DOCUMENT_ROOT'].'/images/assets/'.$adminLogo)){
		?><img src="/images/assets/<?php echo $adminLogo?>" alt="logo"  border="0" align="absbottom" /><?php 
	}
	?>&nbsp; <?php echo $adminCompany?> Site Administration 1.0</a></h2>
	<div id="menuWrap1">

		<script language="javascript" type="text/javascript">
		/* 2010-11-01: this array works with SoThink menu creator; see function DHTMLMenu() in a_f and $DTHMLMenu array in auth_i2_v100.php */
		var _DHTMLMenuShow_=[<?php echo implode(',',DHTMLmenu())?>];
		var _DHTMLMenuIdx_=0;
		function DHMLMenuShow(){_DHTMLMenuIdx_++; return (_DHTMLMenuShow_[_DHTMLMenuIdx_-1] ? true : false);}
		</script>
		<script type="text/javascript" language="JavaScript1.2" src="/console/stm31.js"></script>
		<script type="text/javascript" language="JavaScript1.2" src="/console/stm31_output.js"></script>

	</div>
	<?php
	$out=ob_get_contents();
	ob_end_clean();
	if(!$hideHeader)echo $out;
	if($allowClose){
		?><div id="closeWindow"><input type="button" name="OK" value=" Close " onClick="window.close();" /></div><?php
	}
	?>
</div>