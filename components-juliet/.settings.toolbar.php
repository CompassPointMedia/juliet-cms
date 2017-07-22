<?php
/* 
todo:
----------------------
2013-03-22
	* a color for the top settings ed
	* mouse-over = flash like a regular menu - lose the + sign and also title the controls
	(i.e. have a title attribute in the pJ_call_edit() function)
	* need to have a list of all queries, turn on or off debugging of SQL where this populates automatically in a box - page stats
	* how many people have visited this page?
	
	THEN, this needs to be a true component hooking into the core of Juliet, installed perhaps in the NULL block as above everything (that's a hook/block location)

2013-03-22: this next few weeks in Juliet will determine how well it develops for the next several years

2011-09-17: this was pulled from relatebase_05_generic.php.  Juliet is starting to look really nice! here is some code that was in the template however is no longer needed for the popup but the LOGIC of 'common' is something I will be using:
	$_pJ_a=array_key_deep($templateDefinedBlocks,$pJCurrentContentRegion);
	if($_pJ_a[0])$_pJ_a=$_pJ_a[0];
	if($adminMode==ADMIN_MODE_DESIGNER){
		if($_pJ_a['disposition']=='common'){
			?><a title="See settings for this region" onClick="return ow(this.href,'l1_regions','500,550');" href="/_juliet_.settings.php?block=<?php echo $pJCurrentContentRegion?>&_thispage=<?php echo $thispage?>&_thisfolder=<?php echo $_thisfolder?>&_thisnode=<?php echo $_thisnode?>"><img src="/images/i/design.jpg" width="16" height="17" alt="design" /></a><?php
		}
	}
*/
?><style type="text/css">
.indented{
	margin-left:20px;
	color:mintcream;
	}
.hierarchy{
	/*width:180px;*/
	position:absolute;
	top:25px;
	left:0px;
	padding:2px 7px;
	background-color:rgba(0,0,0,.60);
	border:1px solid gold;
	}
.hierarchy a{
	color:white;
	}
.hierarchy a:hover{
	color:lightgreen;
	}
#hierarchyCtrl,#componentCtrl,#otherCtrl{
	position:relative;
	float:left;
	margin-right:15px;
	}
#componentCtrlMenu{
	width:180px;
	}
<?php if($adminMode){ ?>
html{
	margin-top:27px;
	}
<?php } ?>
#d0{
	top:0px;
	position: fixed;
	color:white;
	padding:2px 75px 2px 25px;
	background-color:rgba(0,0,0,0.60);
	z-index:1000;
	}
#d0 a{
	color:white;
	}
#d0 .handle{
	font-weight:bold;
	}
#d0 .handleLinks{
	margin-left:15px;
	}
#toolbarEditPage{
	width:200px;
	float:right;
	padding:0px 20px;
	margin-left:15px;
	}
#toolbarMode{
	float:right;
	padding:0px 20px;
	margin-left:15px;
	}

.full{
	width:100%;
	}
.partial{
	float:left;
	}
.frx{
	float:right;
	padding-left:10px;
	}

</style>
<script language="javascript" type="text/javascript">
function focusRegion(o,n){
	var d=o.innerHTML;
	if(n==1){
		if(g(d))g(d).style.outline='1px dashed darkred';
	}else if(n==2){
		if(g(d))g(d).style.outline='none';
	}
	if(n!=1 && n!=2){
		ow('/_juliet_.settings.php?block='+d+'&_thispage=<?php echo $thispage?>&_thisfolder=<?php echo $thisfolder?>&_thisnode=<?php echo $thisnode?>','l1_blockregions','861,550');
	}
	return false;
}
function toggleMenu(o){
	var n=gCookie(o.id);
	if(!n)n='block';
	o.nextSibling.style.display=(n=='block'?'none':'block');
	o.firstChild.src='/images/i/plusminus-'+(n=='block'?'plus':'minus')+'.gif';
	sCookie(o.id,(n=='block'?'none':'block'));
	return false;
}
var setAdminModeBuffer;
function toggleSetAdminMode(n){
	if(n=='0' && !confirm('Leave site editor mode completely?')){
		g('setAdminMode').value=<?php echo $adminMode;?>;
		return false;
	}
	var l=window.location+'';
	l=l.split('?');
	if(l[1]){
		l[1]=l[1].replace(/&*setAdminMode=[12]/,'');
		if(l[1])l[1]+='&';
		l[1]+='setAdminMode='+n;
	}else{
		l[1]='setAdminMode='+n;
	}
	window.location=l[0]+'?'+l[1];
}
function toggleEditLinks(){
	var s;
	s=(g('toggleEditLinks_').innerHTML=='Hide Edit Links'?0:1);
	sCookie('pJHEL',(s==1?0:1));
	g('toggleEditLinks_').innerHTML=(s?'Hide Edit Links':'Show Edit Links');
	if(a=document.getElementsByClassName('_editLink_1')){
		for(var i in a){
			try{ a[i].style.display=(s?'inline':'none'); }catch(e){ }
		}
	}
	<?php if($adminMode>=ADMIN_MODE_DESIGNER){ ?>
	if(a=document.getElementsByClassName('_editLink_2')){
		for(var i in a){
			try{ a[i].style.display=(s?'inline':'none'); }catch(e){ }
		}
	}
	<?php } ?>
}
function toggleToolbar(o){
	var d=g('d0').className;
	g('toggleEditLinks').style.display=(d=='partial'?'block':'none');
	g('toolbarEditPage').style.display=(d=='partial'?'block':'none');
	g('toolbarMode').style.display=(d=='partial'?'block':'none');
	g('hierarchyCtrl').style.display=(d=='partial'?'block':'none');
	g('d0').className=(d=='partial'?'full':'partial');
	o.innerHTML=(d=='partial'?'less..':'Juliet..');
}
</script>
<div id="d0" class="full">
	<div style="float:left;cursor:pointer;" onclick="toggleToolbar(this);">less..</div>
	<?php ob_start();?>
	<div id="hierarchyCtrl">
		<a id="hierarchyMgr" href="#" onclick="return toggleMenu(this)"><img src="/images/i/plusminus-<?php echo $_COOKIE['hierarchyMgr']=='none' ? 'plus' : 'minus'?>.gif" /> Template blocks</a><div class="hierarchy" style="display:<?php echo $_COOKIE['hierarchyMgr']?$_COOKIE['hierarchyMgr']:'block';?>">
			<?php
			recurse_array($templateDefinedBlocks,1,$options=array('type'=>'menu'));
			?>
		</div>
	</div>
	<?php 
	$pJTemplateBlocksTool=ob_get_contents();
	ob_end_clean();
	?>
	<span id="<?php echo md5($pJTemplateBlocksTool);?>"> </span>


	<div id="componentCtrl">
		<a id="componentsMgr" href="#" onclick="return toggleMenu(this)"><img src="/images/i/plusminus-<?php echo $_COOKIE['componentsMgr']=='none' ? 'plus' : 'minus'?>.gif" /> Components</a><div id="componentCtrlMenu" class="hierarchy" style="display:<?php echo $_COOKIE['componentsMgr']?$_COOKIE['componentsMgr']:'block';?>">
		
		</div>	
	</div>
	<div id="otherCtrl">
		<a id="otherMgr" href="#" onclick="return toggleMenu(this)"><img src="/images/i/plusminus-<?php echo $_COOKIE['componentsMgr']=='none' ? 'plus' : 'minus'?>.gif" /> Settings</a><div id="componentCtrlMenu" class="hierarchy" style="display:<?php echo $_COOKIE['componentsMgr']?$_COOKIE['componentsMgr']:'block';?>">
		<a href="/_juliet_.settings.php?mode=pageManager&_thispage=<?php echo $thispage;?><?php if($thisfolder)echo '&_thisfolder='.$thisfolder;?>&_thisnode=<?php echo $thisnode;?>" onclick="return ow(this.href,'l1_blockregions','861,550');">Page manager</a><br />
		<a href="/_juliet_.settings.php?mode=stylesheetManager&_thispage=<?php echo $thispage;?><?php if($thisfolder)echo '&_thisfolder='.$thisfolder;?>&_thisnode=<?php echo $thisnode;?>" onclick="return ow(this.href,'l1_blockregions','861,550');">Additional Styles</a><br />
		<a href="/_juliet_.settings.php?mode=codingManager&_thispage=<?php echo $thispage;?><?php if($thisfolder)echo '&_thisfolder='.$thisfolder;?>&_thisnode=<?php echo $thisnode;?>" onclick="return ow(this.href,'l1_blockregions','861,550');">PHP Coding</a><br />
		</div>	
	</div>
	<div id="toggleEditLinks" class="fl">
	[<a id="toggleEditLinks_" href="javascript:toggleEditLinks();"><?php echo $_COOKIE['pJHEL']==1?'Show Edit Links':'Hide Edit Links';?></a>]<?php
	//initial condition
	if($_COOKIE['pJHEL']){
		?><style type="text/css"> ._editLink_{ display:none; } ._editLink_1{ display:none; } ._editLink_2{ display:none; } </style><?php
	}else if($adminMode<ADMIN_MODE_DESIGNER){
		?><style type="text/css"> ._editLink_2{ display:none; } </style><?php
	}
	?>
	</div>
	<div id="toolbarEditPage">
	<?php
	if($thisnode){ 
		?>
		<a href="http://<?php echo $_SERVER['SERVER_NAME'];?>/console/rsc_pagemanager_focus.php?Nodes_ID=<?php echo $thisnode?>" title="Edit this page with the page manager in console" onClick="return ow(this.href,'l1_pagemanager','701,702');">Edit Page</a>
		<?php 
	}else if(!$thisfolder && !$thissubfolder && $thispage){
		?>
		<a href="http://www.<?php $a=explode('.',$_SERVER['SERVER_NAME']);echo $a[count($a)-2].'.'.$a[count($a)-1];?>/console/rsc_pagemanager_focus.php?Name=<?php echo $thispage?>&Title=<?php echo $thispage;?>" title="Add this page with the page manager in console" onclick="return ow(this.href,'l1_pagemanager','701,702');"><img src="/images/i/findicons.com-makenewpage.png" width="18" height="18" alt="new page" /> Add Page (<?php echo $thispage;?>)</a>
		<?php
	}
	?>
	</div>
	<div id="toolbarMode">
		Current mode:
		<select id="setAdminMode" name="setAdminMode" class="minimal" style="background-color:#C1D2D2;" onchange="toggleSetAdminMode(this.value);">
		<option value="0" style="font-style:italic; color:#444;">(sign out)</option>
		<option value="1" <?php echo $adminMode==1?'selected':''?>>content only</option>
		<option value="2" <?php echo $adminMode==2?'selected':''?>>layout/themes</option>
		</select>
	</div>
</div>