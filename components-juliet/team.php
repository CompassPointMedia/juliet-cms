<?php
/* name=JLS Stylist Team; In the coming conflict, the only hope to save the world is the Jose Luis Salon Stylist Team (Insert dramatic Music here) */
/*
Changelog:
2013-08-01: whoo hoo! a new component	



todo
test people with spaces in their names - images and thispage 'ery
get inactive people filtered, and those who don't have or get focus
	
*/
$handle='team';
$componentVersion=2.0;
$tabVersion=3;
$Levels = array(
	'Master Stylist','Senior Stylist','Stylist','Junior Stylist','Skin Therapist'
);
$Positions=array(
	'Salon Owner','Salon Manager','Concierge','Marketing Manager',
);
function team_objects($options){
	/*2013-09-07 12:22AM
	predicated on reviews being in gen_objects
	
	also handles videos
	
	options
	-------
	mode=delete|insert (no update)
	refresh
	
	*/
	global $qr,$qx,$fromHdrBugs,$developerEmail,$fl,$ln;
	extract($_REQUEST);
	//able to override
	extract($options);
	if(!$disposition)$disposition='Review';

	//from multiple forms
	@extract($GLOBALS[$disposition]);
		
	if($submode=='deleteObject'){
		$refresh=true;
		q("DELETE o.* FROM addr_contacts c JOIN gen_objects o ON c.UserName='$UserName' AND c.ID=o.Objects_ID AND o.ParentObject='addr_contacts' WHERE o.ID=$Objects_ID");
		?><script language="javascript" type="text/javascript">
		try{
		window.parent.g('<?php echo strtolower(substr($disposition,0,1));?>_<?php echo $Objects_ID;?>').style.display='none';
		}catch(e){}
		</script><?php
	}else if($submode=='insertObject'){
		if(!strlen($CreateDate) || strtotime($CreateDate)===false)error_alert('Enter a proper date for the '.strtolower($disposition));
		if(!$Description)error_alert(
			$disposition=='Review'?'Enter a name of the person reviewing':'Enter the embed code for the video'
		);
		if(strlen($RecordSettings)<5)error_alert('Enter the content or description for the '.strtolower($disposition));
		$Objects_ID=q("SELECT ID FROM addr_contacts WHERE UserName='$UserName'", O_VALUE);
		$new=q("INSERT INTO gen_objects SET 
		Objects_ID=$Objects_ID, 
		ParentObject='addr_contacts',
		Rlx='$disposition', 
		Description='$Description',
		Settings='$RecordSettings',
		CreateDate='".date('Y-m-d H:i:s',strtotime($CreateDate))."',
		Creator='".sun()."'",O_INSERTID);
		prn($qr);
	}else{
		?><script language="javascript" type="text/javascript">
		$(document).ready(function(){
			$('#Submit<?php echo $disposition;?>').click(function(){
				g('submode').value='insertObject';
				g('disposition').value='<?php echo $disposition;?>';
				g('form1').submit();
			});
		});
		</script><?php
	}
	?>
	<div id="<?php echo $disposition;?>s">
	<h2><?php echo $disposition;?>s</h2>
	<table id="thisTable" class="yat">
	<thead><tr>
	<th>&nbsp;</th>
	<th>Date</th>
	<?php if($disposition=='Review'){ ?>
	<th>Name</th>
	<th>Review</th>
	<?php }else{ ?>
	<th>Embed Code</th>
	<th>Description</th>
	<?php } ?>
	</tr></thead>
	<tbody><?php
	if($a=q("SELECT o.ID, o.Description AS Name, o.Settings AS Review, o.CreateDate, o.Creator FROM addr_contacts c JOIN gen_objects o ON c.ID=o.Objects_ID AND ParentObject='addr_contacts' WHERE c.UserName='$UserName' AND o.Rlx='$disposition' ORDER BY o.Priority,o.CreateDate DESC", O_ARRAY)){
		$i=0;
		foreach($a as $n=>$v){
			$i++;
			?><tr id="<?php echo strtolower(substr($disposition,0,1));?>_<?php echo $v['ID'];?>" class="<?php echo (!fmod($i,2)?'alt':'').' '.($v['ID']==$new?'new':'');?>">
				<td>[<a href="/index_01_exe.php?location=JULIET_COMPONENT_ROOT&file=<?php echo end(explode('/',__FILE__));?>&mode=componentEditor&submode=deleteObject&disposition=<?php echo $disposition;?>&UserName=<?php echo $UserName;?>&Objects_ID=<?php echo $v['ID'];?>" onclick="return confirm('Are you sure you want to delete this <?php echo strtolower($disposition);?>?');" target="w2">x</a>]</td>
				<td><?php echo str_replace(' @12:00AM','',date('n/j/Y @g:iA',strtotime($v['CreateDate'])));?></td>
				<td title="<?php echo h($v['Name']);?>"><?php 
				if($disposition=='Review'){
					echo $v['Name'];
				}else{
					?><span style="font-family:'Courier New', Courier, monospace;"><?php
					echo h(substr($v['Name'],0,25)).'...';
					?></span><?php
				}
				?></td>
				<td><?php echo $v['Review'];?></td>
			</tr><?php
		}
	}else{
		?><tr>
		<td colspan="100%"><em class="gray">No <?php echo strtolower($disposition);?>s found for this stylist</em></td>
		</tr><?php
	}
	?><tr><td colspan="100%">Add a new <?php echo strtolower($disposition);?>:<br />
	Date: <input name="<?php echo $disposition;?>[CreateDate]" type="text" id="CreateDate" /><br />
	<?php if($disposition=='Review'){ ?>
	Name: <input name="<?php echo $disposition;?>[Description]" type="text" id="Description" /><br />
	<?php }else{ ?>
	Embed Code:<br />
	<textarea name="<?php echo $disposition;?>[Description]" cols="35" rows="3" id="Description"></textarea><br />
	<?php } ?>
	Review: <br />
	<textarea name="<?php echo $disposition;?>[RecordSettings]" cols="45" rows="3" id="RecordSettings"></textarea>
	<br />
	<?php 
	global $dispositionSet;
	if(!$dispositionSet){
		$dispositionSet=true;
		?><input type="hidden" name="disposition" id="disposition" /><?php
	}
	?>
	<input type="submit" name="Submit" id="Submit<?php echo $disposition;?>" value="Submit <?php echo $disposition;?>" />
	<br />
	</td></tr>
	</tbody>
	</table>
	</div><?php
	if($refresh){
		?><script language="javascript" type="text/javascript">
		try{
		window.parent.g('<?php echo $disposition;?>s').innerHTML=document.getElementById('<?php echo $disposition;?>s').innerHTML;
		}catch(e){}
		</script><?php
	}
}

//2012-03-10: pull parameters for this component file - note that this is in gen_nodes_settings.Settings vs. gen_templates_blocks.Parameters
if($Parameters=q("SELECT Settings FROM gen_nodes_settings WHERE Nodes_ID='".($_thisnode_ ? $_thisnode_ : $thisnode) ."'", O_VALUE)){
	$Parameters=unserialize(base64_decode($Parameters));
	if($Parameters[$handle])$pJ['componentFiles'][$handle]=$Parameters[$handle];
	/* nodes include: forms; data; format.  forms is unused right now, and data[default] means "across all pages" and is the only part developed */
}

//example default variables
$categoryPath=pJ_getdata('categoryPath','category');

//default CSS
get_contents_enhanced('start'); ?>
<?php if(false){ ?><style type="text/css">
<?php } ?>
#team{
	}
.titles a, .positions a{
	color:#000;
	}
#salon-owner{
	height:125px;
	}
#concierge{
	height:160px;
	}
.stylists{
	padding-left:50px;
	min-height:120px;
	background-repeat:no-repeat;
	}
#master-stylist{
	background-image:url("/images/titles/masterstylist.png");
	}
#senior-stylist{
	background-image:url("/images/titles/seniorstylist.png");
	}
#stylist{
	background-image:url("/images/titles/stylist.png");
	}
#junior-stylist{
	background-image:url("/images/titles/juniorstylist.png");
	}
#skin-therapist{
	background-image:url("/images/titles/skintherapist.png");
	}
#salon-owner{
	background-image:url("/images/titles/salonowner.png");
	}
#salon-manager{
	background-image:url("/images/titles/salonmanager.png");
	}
#concierge{
	background-image:url("/images/titles/concierge.png");
	}
#marketing-manager{
	background-image:url("/images/titles/marketing.png");
	}
.lineabove td{
	border-top:1px solid #000;
	}
<?php if(false){ ?>
</style><?php } ?>
<?php
$n=get_contents_enhanced('noecho,cxlnextbuffer');
$pJLocalCSS[$handle.'-baseCSS']=
$teamDefaultCSS=pJ_getdata('teamDefaultCSS',$n);


for($__i__=1; $__i__<=1; $__i__++){ //---------------- begin i break loop ---------------
if($formNode && $mode!=='componentEditor' && $mode!=='componentControls'){
	?><script language="javascript" type="text/javascript">
	try{
	$(document).ready(function(){
		$('#insertStaff').click(function(){
			if(!g('UserName').value.match(/^[a-zA-Z0-9]+$/)){ alert('Enter a user name from the contacts list'); return false; }
			g('submode').value='insertStaff';
			g('form1').submit();
		});
	});
	}catch(e){}
	</script><?php
}

if($mode=='componentControls'){

}
if($mode=='componentEditor'){
	//be sure and fulfill null checkbox fields
	/*
	2012-03-12: this is universal code which should be updated on ALL components.  The objective is that 
	
	*/
	if($submode=='insertObject' || $submode=='deleteObject'){
		team_objects(array(
			'submode'=>$submode,
			'UserName'=>$UserName,
			'refresh'=>true,
		));
		eOK();
	}else if($submode=='insertStaff'){
		if(!($a=q("SELECT * FROM addr_contacts c LEFT JOIN finan_ClientsContacts cc ON c.ID=cc.Contacts_ID AND cc.Type='Primary' LEFT JOIN finan_clients cl ON cc.Clients_ID=cl.ID WHERE c.UserName='$UserName'", O_ARRAY)))error_alert('Staff user name does not exist.  Click Add New Staff first, then enter the username of the new staff member');
		if(count($a)>1){
		
		}
		$a=$a[1];
		if($b=q("SELECT * FROM addr_contacts_JLSStaff WHERE UserName='$UserName'", O_ROW))error_alert('That person is already added as staff!');
		q("INSERT INTO addr_contacts_JLSStaff SET UserName=LCASE('$UserName')");
		
		?><script language="javascript" type="text/javascript">
		//send them over to that location
		window.parent.location='/_juliet_.editor.php?handle=team&location=JULIET_COMPONENT_ROOT&file=team.php&formNode=member:<?php echo strtolower($UserName);?>';
		</script><?php
	}else if($submode=='updateStaff'){
		q("UPDATE addr_contacts_JLSStaff SET 
		Active='$Active',
		ShowWork='$ShowWork',
		Level='$Level',
		Position='$Position',
		ExperienceFrom='$ExperienceFrom',
		ShortBio='$ShortBio',
		LongBio='$LongBio'
		WHERE UserName='$UserName'");
		error_alert('Record updated OK');
	}else if($submode=='deleteStaff'){
		q("DELETE FROM addr_contacts_JLSStaff WHERE UserName='$UserName'");
		?><script language="javascript" type="text/javascript">
		window.parent.g('r_<?php echo strtolower($UserName);?>').style.display='none';
		</script><?php
	}

	if($_thisnode_){
		/* ----------  this is a single-page, cross-block settings update ---------------  */
		!is_array($pJ['componentFiles'][$handle]) ? $pJ['componentFiles'][$handle]=array() : '';
		//now integrate the form post
		$pJ['componentFiles'][$handle]['data'][$formNode]=stripslashes_deep($_POST[$formNode]);
		$Parameters[$handle]['data'][$formNode]=$pJ['componentFiles'][$handle]['data'][$formNode];
	
		//unlike gen_templates_blocks, place as part of a larger array
		if($a=q("SELECT * FROM gen_nodes_settings WHERE Nodes_ID='$_thisnode_'", O_ROW)){
			//OK
		}else{
			q("INSERT INTO gen_ncdes_settings SET Nodes_ID='$_thisnode_', EditDate=NOW()");
		}
		q("UPDATE gen_nodes_settings SET Settings='".base64_encode(serialize($Parameters))."' WHERE Nodes_ID='$_thisnode_'");
		prn($qr);
	}else{
		error_alert(__LINE__);
		/* ----------  this is a cross-page, single-block settings update ---------------  */
	}
	break;
}else if($formNode=='default' /* ok this is something many component files will contain */){
	if(!$_thisnode_){
		//create a virtual page for now
		mail($developerEmail, 'Warning in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='in products.php, a virtual node was created to handle calls from pages like /products/JTI-Tools  or  /products/main, where we need some place to store settings'),$fromHdrBugs);
		if($_thisnode_=q("SELECT ID FROM gen_nodes WHERE SystemName='{generic_ecommerce_placeholder}'", O_VALUE)){
			//OK
		}else{
			$_thisnode_=q("INSERT INTO gen_nodes SET Active=8, SystemName='{generic_ecommerce_placeholder}', Name='Generic Settings', Type='Object', CreateDate=NOW(), EditDate=NOW()",O_INSERTID);
			q("INSERT INTO gen_nodes_settings SET Nodes_ID=$_thisnode_");
		}
	}
	$nodeGroup=q("SELECT * FROM gen_nodes WHERE ID=$_thisnode_", O_ROW);
	?><h3 style="font-weight:400;color:black;">Page Group: <strong style="color:darkgreen;"><?php echo $nodeGroup['Name'];?></strong></h3>
	
	<?php ob_start();?>
	<h3>Staff List</h3>
	<p class="gray">In order to show on the JLS site, a staff member must be set up here</p>
	<table id="staff" class="yat">
	<thead>
	<tr>
		<th></th>
		<th title="Active Staff">&nbsp;</th>
		<th title="Active Staff">Act.</th>
		<th>Name</th>
		<th>Level</th>
		<th>Position</th>
		<th>Reviews</th>
	</tr>
	</thead>
	<tbody>
	<?php
	if($a=q("SELECT s.*, c.ID, c.FirstName, c.MiddleName, c.LastName, c.Email FROM addr_contacts_JLSStaff s JOIN addr_contacts c ON s.UserName=c.UserName WHERE 1 ORDER BY IF(s.Level='',2,1), s.Level, c.LastName, c.FirstName", O_ARRAY)){
		$i=0;
		foreach($a as $v){
			$j++;
			?><tr id="r_<?php echo strtolower($v['UserName']);?>" class="<?php echo !fmod($j,2)?'alt':''?><?php if($buffer!==$v['Level'])echo ' lineabove';?>">
				<td>[<a href="/_juliet_.editor.php?handle=team&location=JULIET_COMPONENT_ROOT&file=team.php&formNode=member:<?php echo $v['UserName'];?>">edit</a>]</td>
				<td>[<a target="w2" href="/_juliet_.editor.php?handle=team&location=JULIET_COMPONENT_ROOT&file=team.php&mode=componentEditor&submode=deleteStaff&UserName=<?php echo $v['UserName'];?>" onclick="if(!confirm('This will remove this person from the staff list.  Continue?'))return false;">del</a>]</td>
				<td><?php if($v['Active']){ echo '<img src="/images/i/yes.gif" width="20" height="14" alt="yes" />'; }else{ echo '&nbsp;'; }?></td>
				<td><?php if($v['Email']){?><a href="mailto:<?php echo $v['Email'];?>" title="click to email"><?php } ?><?php echo $v['LastName'].', '.$v['FirstName'];?><?php if($v['Email']){?></a><?php } ?></td>
				<td><?php echo $v['Level'];?></td>
				<td><?php echo $v['Position'];?></td>
				<td><?php echo $v['Reviews'];?></td>
				<td><em class="gray">(not developed)</em></td>
			</tr><?php
			$buffer=$v['Level'];
		}
	}else{
		?><tr>
		<td colspan="101"><em class="gray">No staff records found</em></td>
		</tr><?php
	}
	?>
	</tbody>
	</table>
	<p><br />
	<input name="UserName" type="text" id="UserName" />
	<input name="insertStaff" type="submit" id="insertStaff" value="Add Username" />
	<br />
	<br />
	<a href="/console/contacts.php" onclick="return ow(this.href,'l1_contacts','850,500',true);">Add new contact</a> </p>
	<p>    
	<?php

	
	
	get_contents_tabsection('staff');?>
	  
	  </p>
	<div>styling</div>

	<?php get_contents_tabsection('styling');?>

	<div>javascript</div>
	
	<?php get_contents_tabsection('javascript');?>

	<div>php</div>

	<?php get_contents_tabsection('php');?>


	<?php require($JULIET_COMPONENT_ROOT.'/products._help_.php');?>
	
	<?php
	get_contents_tabsection('help');
	tabs_enhanced(
		array(
			'staff'=>array(
				'label'=>'Staff List',
			),
			'styling'=>array(
				'label'=>'CSS Styles',
			),
			'javascript'=>array(
				'label'=>'JavaScript',
			),
			'php'=>array(
				'label'=>'PHP Coding',
			),
			'help'=>array(
				'label'=>'Help',
			),
		) 
	);
	
	break;
}else if(preg_match('/^member:/',$formNode)){
	//record
	$a=q("SELECT c.ID, c.FirstName, c.MiddleName, c.LastName, c.Email, c.HomeAddress, c.HomeCity, c.HomeState, c.HomeZip, s.* FROM addr_contacts c JOIN addr_contacts_JLSStaff s ON c.UserName=s.UserName WHERE c.UserName='".end(explode(':',$formNode))."'", O_ROW);
	extract($a);
	
	
	ob_start();
	?>
	<div>
	<script language="javascript" type="text/javascript">
	$(document).ready(function(){
		$('#Submit').click(function(){
			g('submode').value='updateStaff';
		});
	});
	</script>
	<h3><a href="_juliet_.editor.php?_thisnode_=19&handle=team&location=JULIET_COMPONENT_ROOT&file=team.php&formNode=default" title="Go back to staff list">Staff</a> > <?php echo $a['FirstName'] . ' '. $a['LastName'];?></h3>
	<input type="hidden" name="UserName" value="<?php echo end(explode(':',$formNode));?>" />
	<input type="hidden" name="Active" value="0" />
	<label><input name="Active" type="checkbox" id="Active" value="1" <?php echo $Active==1 || !isset($Active)?'checked':''?> />
	Active staff - show on website</label><br />
	<input type="hidden" name="ShowWork" value="0" />
	<label><input name="ShowWork" type="checkbox" id="ShowWork" value="1" <?php echo $ShowWork || !isset($ShowWork)?'checked':''?> /> Show work and link to work on site</label><br />
	Experience Level: 
	<select name="Level" id="Level">
	<option value="">&lt;select..&gt;</option>
	<?php
	foreach($Levels as $v){
		?><option value="<?php echo h($v);?>" <?php if(strtolower($Level)==strtolower($v))echo 'selected';?>><?php echo $v;?></option><?php
	}
	?>
	</select>
	<br />
	Experience year: 
	<select name="ExperienceFrom" id="ExperienceFrom">
	<option value="">&lt;select..&gt;</option>
	<?php
	$y=date('Y');
	for($i=0; $i<=50; $i++){
		?><option value="<?php echo $y-$i;?>" <?php echo $y-$i==$ExperienceFrom?'selected':''?>><?php echo $y-$i;?></option><?php
	}
	?>
	</select>
	<em class="gray">(use the year that will subtract from this year and = total experience)</em> <br />
	<br />
	<br />
	Position: <select name="Position" id="Position">
	<option value="" class="gray" style="font-style:italic;">(none)</option>
	<?php
	foreach($Positions as $v){
		?><option value="<?php echo h($v);?>" <?php echo strtolower($v)==strtolower($Position)?'selected':''?>><?php echo h($v);?></option><?php
	}
	?>
	</select>
	<br />
	Short Bio:<br />
	<textarea name="ShortBio" cols="60" rows="3" id="ShortBio"><?php echo h($ShortBio);?></textarea>
	<br />
	<br />
	Longer Bio:<br />
    <textarea name="LongBio" cols="60" rows="6" id="LongBio"><?php echo h($LongBio);?></textarea>
	</div>
	<?php
	get_contents_tabsection('staff');
	?>
	Hair Gallery
	<?php
	get_contents_tabsection('pictures');
	team_objects(array(
		'UserName'=>$UserName,
	));
	get_contents_tabsection('reviews');
	team_objects(array(
		'UserName'=>$UserName,
		'disposition'=>'Video',
	));
	get_contents_tabsection('videos');
	tabs_enhanced(
		array(
			'staff'=>array(
				'label'=>'Staff Info: '.$FirstName. ' '. $LastName,
			),
			'pictures'=>array(
				'label'=>'Pictures',
			),
			'reviews'=>array(
				'label'=>'Reviews',
			),
			'videos'=>array(
				'label'=>'Videos',
			),
			'help'=>array(
				'label'=>'Help',
			),
		) 
	);
}


//regions have common purposing, this makes code below easier
if($thispage=='team'){

}else if($thisfolder=='team' && $thissubfolder==''){
	$pJBodyClass[]='focus-stylist';
	//refA: get the stylist record
	$record=q("select c.*, s.* FROM addr_contacts c JOIN addr_contacts_JLSStaff s ON c.UserName=s.UserName WHERE
replace(CONCAT(c.firstname,'-',c.lastname),' ','-')='$thispage'", O_ROW);
	$headRegionTitle='Stylist '.$record['FirstName'] . ' ' . $record['LastName'].' - Jose Luis Salon, Austin TX';
	//list of stylists
	$stylistImgs=get_file_assets($_SERVER['DOCUMENT_ROOT'].'/images/stylists',array(
		'positiveFilters'=>'(png|jpg)$',
	));
	//now get all other images via a database query.  This is gonna be complex... 2013-08-12
	
	if(!function_exists('get_image'))require($FUNCTION_ROOT.'/function_get_image_v230.php');
	if(false && 'image is joined to their record in ObjectsTree'){
		//do tree_image here
	}else if($a=get_image($record['FirstName'].$record['LastName'],$stylistImgs,array(
		'return'=>'array',
	))){
		//join into the database
		$img=tree_image(array(
			'src'=>'/images/stylists/'.$a['name'],
			'disposition'=>'216x216',
			'boxMethod'=>2,
			'alt'=>'Photo of '.$record['FirstName'].' '.$record['LastName'],
			'return'=>'var',
		));
	}else{
		//notify admin no picture showing
		
		//blank image
		$img='<img src="/images/i/spacer.gif" width="216" height="216" alt="Picture of Stylist" />';
	}
	//list of word-names
	$stylistNameImgs=get_file_assets($_SERVER['DOCUMENT_ROOT'].'/images/stylists/name-images',array(
		'positiveFilters'=>'(png|jpg)$',
	));
}


ob_start();
//call to edit
echo pJ_call_edit(array(
	'level'=>ADMIN_MODE_DESIGNER,
	'location'=>'JULIET_COMPONENT_ROOT',
	'file'=>end(explode('/',__FILE__)),
	'thisnode'=>($thisnode?$thisnode:NULL),
	'label'=>'Edit team members',
));
?>
<span id="team" class="<?php echo $pJDerivedThispage ? $pJDerivedThispage : $thispage?>"><?php
//------------- Luke here is where the center block goes -------------------------

if($thispage=='team'){
	CMSB('leadslide');
	?>
	<table class="titles" width="100%" cellpadding="4">
	  <tr>
		<td id="master-stylist" class="stylists"><?php
		
		if($a=q("SELECT
		c.FirstName, c.LastName, s.ExperienceFrom, c.UserName, s.ShowWork
		FROM addr_contacts c JOIN addr_contacts_JLSStaff s ON c.UserName=s.UserName WHERE Level='Master Stylist' AND s.Active=1", O_ARRAY)){
			$y=date('Y');
			foreach($a as $v){
				$name= strtolower(preg_replace('/[^-a-z]/i','',$v['FirstName'].'-'.$v['LastName']));
				if($v['ShowWork']){
					?><a href="/team/<?php echo $name;?>" title="click to see the work of <?php echo $v['FirstName'] . ' ' . $v['LastName'];?>"><?php
				}
				echo $v['FirstName'] . ' ' .$v['LastName'];
				if($y - $v['ExperienceFrom'] >= 25){ echo '*'; }
				echo "</a>";
				?><br /><?php
			}
		}
		
		
		?></td>
		<td id="senior-stylist" class="stylists"><?php
		
		if($a=q("SELECT
		c.FirstName, c.LastName, s.ExperienceFrom, c.UserName, s.ShowWork
		FROM addr_contacts c JOIN addr_contacts_JLSStaff s ON c.UserName=s.UserName WHERE Level='Senior Stylist' AND s.Active=1", O_ARRAY)){
			$y=date('Y');
			foreach($a as $v){
				$name= strtolower(preg_replace('/[^-a-z]/i','',$v['FirstName'].'-'.$v['LastName']));
				if($v['ShowWork']){
					?><a href="/team/<?php echo $name;?>" title="click to see the work of <?php echo $v['FirstName'] . ' ' . $v['LastName'];?>"><?php
				}
				echo $v['FirstName'] . ' ' .$v['LastName'];
				if($y - $v['ExperienceFrom'] >= 25){ echo '*'; }
				if($v['ShowWork']){
					?></a><?php
				}
				?><br /><?php
			}
		}
		
		
		?></td>
		<td id="stylist" class="stylists"><?php
		
		if($a=q("SELECT
		c.FirstName, c.LastName, s.ExperienceFrom, c.UserName, s.ShowWork
		FROM addr_contacts c JOIN addr_contacts_JLSStaff s ON c.UserName=s.UserName WHERE Level='Stylist' AND s.Active=1", O_ARRAY)){
			$y=date('Y');
			foreach($a as $v){
				$name= strtolower(preg_replace('/[^-a-z]/i','',$v['FirstName'].'-'.$v['LastName']));
				if($v['ShowWork']){
					?><a href="/team/<?php echo $name;?>" title="click to see the work of <?php echo $v['FirstName'] . ' ' . $v['LastName'];?>"><?php
				}
				echo $v['FirstName'] . ' ' .$v['LastName'];
				if($y - $v['ExperienceFrom'] >= 25){ echo '*'; }
				echo "</a>";
				?><br /><?php
			}
		}
		
		
		?></td>
	  </tr>
	  <tr>
		<td id="junior-stylist" class="stylists"><?php
		if($a=q("SELECT
		c.FirstName, c.LastName, s.ExperienceFrom, c.UserName, s.ShowWork
		FROM addr_contacts c JOIN addr_contacts_JLSStaff s ON c.UserName=s.UserName WHERE Level='Junior Stylist' AND s.Active=1", O_ARRAY)){
			$y=date('Y');
			foreach($a as $v){
					$name= strtolower(preg_replace('/[^-a-z]/i','',$v['FirstName'].'-'.$v['LastName']));
					if($v['ShowWork']){
					?><a href="/team/<?php echo $name;?>" title="click to see the work of <?php echo $v['FirstName'] . ' ' . $v['LastName'];?>"><?php
				}
				echo $v['FirstName'] . ' ' .$v['LastName'];
				if($y - $v['ExperienceFrom'] >= 25){ echo '*'; }
				echo "</a>";
				?><br /><?php
			}
		}
		?></td>
		<td id="skin-therapist" class="stylists"><?php
		
		if($a=q("SELECT
		c.FirstName, c.LastName, s.ExperienceFrom, c.UserName, s.ShowWork
		FROM addr_contacts c JOIN addr_contacts_JLSStaff s ON c.UserName=s.UserName WHERE Level='Skin Therapist' AND s.Active=1", O_ARRAY)){
			$y=date('Y');
			foreach($a as $v){
					$name= strtolower(preg_replace('/[^-a-z]/i','',$v['FirstName'].'-'.$v['LastName']));
					if($v['ShowWork']){
					?><a href="/team/<?php echo $name;?>" title="click to see the work of <?php echo $v['FirstName'] . ' ' . $v['LastName'];?>"><?php
				}
				echo $v['FirstName'] . ' ' .$v['LastName'];
				if($y - $v['ExperienceFrom'] >= 25){ echo '*'; }
				echo "</a>";
				?><br /><?php
			}
		}
		?></td>
		<td>&nbsp;</td>
	  </tr>
	</table>
	<br />
	<br />
	<img src="/images/assets/team_dottedline-01.png" />
	<br />
	<br />

	<table class="positions" width="100%" cellpadding="4">
		<tr>
			<td id="salon-owner" class="stylists">
				<?php
				if($a=q("SELECT
				c.FirstName, c.LastName, c.UserName, s.ShowWork
				FROM addr_contacts c JOIN addr_contacts_JLSStaff s ON c.UserName=s.UserName WHERE Position='Salon Owner' AND s.Active=1", O_ARRAY)){
					foreach($a as $v){
						echo $v['FirstName'] . ' ' .$v['LastName'];
						?><br /><?php
					}
				}
				?>
			</td>
			<td id="salon-manager" class="stylists">
				<?php
				if($a=q("SELECT
				c.FirstName, c.LastName, c.UserName, s.ShowWork
				FROM addr_contacts c JOIN addr_contacts_JLSStaff s ON c.UserName=s.UserName WHERE Position='Salon Manager' AND s.Active=1", O_ARRAY)){
					foreach($a as $v){
						echo $v['FirstName'] . ' ' .$v['LastName'];
						?><br /><?php
					}
				}
				?>
			</td>
		</tr>
		<tr>
			<td id="concierge" class="stylists">
				<?php
				if($a=q("SELECT
				c.FirstName, c.LastName, c.UserName, s.ShowWork
				FROM addr_contacts c JOIN addr_contacts_JLSStaff s ON c.UserName=s.UserName WHERE Position='Concierge' AND s.Active=1", O_ARRAY)){
					foreach($a as $v){
						echo $v['FirstName'] . ' ' .$v['LastName'];
						?><br /><?php
					}
				}
				?>
			</td>
			<td id="marketing-manager" class="stylists">
				<?php
				if($a=q("SELECT
				c.FirstName, c.LastName, c.UserName, s.ShowWork
				FROM addr_contacts c JOIN addr_contacts_JLSStaff s ON c.UserName=s.UserName WHERE Position='Marketing Manager' AND s.Active=1", O_ARRAY)){
					foreach($a as $v){
						echo $v['FirstName'] . ' ' .$v['LastName'];
						?><br /><?php
					}
				}
				?>
			</td>
		</tr>
	</table>
	<?php
}else if($thisfolder=='team' && $thissubfolder==''){
	if($list=q("SELECT
		c.Rlx AS Category,
		f.Description AS Folder,
		t.Tree_ID,
		_t.Name
		FROM 
		gen_objects p 
		JOIN gen_objects c ON c.ParentObject='gen_objects' AND c.Objects_ID=p.ID
		JOIN gen_objects f ON f.ParentObject='gen_objects' AND f.Objects_ID=c.ID 
		JOIN relatebase_ObjectsTree t ON t.Objects_ID=f.ID
		JOIN relatebase_tree _t ON t.Tree_ID=_t.ID
		WHERE p.ParentObject='addr_contacts' AND p.Objects_ID='".$record['ID']."'
		ORDER BY c.Priority, 
		IF(c.Rlx='Hair',1, IF(c.Rlx='Celebrity',2, IF(c.Rlx='Runway',3,4))),
		f.Priority,
		f.Description,
		t.Priority", O_ARRAY)){
		/*
		?><table><?php
		*/
		$i=0;
		$gallery=array();
		foreach($list as $v){
			$gallery[$v['Category']][$v['Folder']][$v['Tree_ID']]=$v['Name'];
			$i++;
			/*
			if($i==1){
				?><thead><tr><th><?php
				echo implode('</th><th>',array_keys($v));
				?></th></tr></thead><?php
			}
			?><tr><td><?php
			echo implode('</td><td>',$v);
			?></td></tr><?php
			*/
		}
		/*
		?></table><?php
		*/
		//now we output the slides
		$colorboxFrame='<div style="display: none;" id="cboxOverlay"></div><div class="" id="colorbox" style="padding-bottom: 42px; padding-right: 42px; display: none;"><div style="" id="cboxWrapper"><div style=""><div style="float: left;" id="cboxTopLeft"></div><div style="float: left;" id="cboxTopCenter"></div><div style="float: left;" id="cboxTopRight"></div></div><div style="clear: left;"><div style="float: left;" id="cboxMiddleLeft"></div><div style="float: left;" id="cboxContent"><div class="" style="width: 0px; height: 0px; overflow: hidden;" id="cboxLoadedContent"></div><div class="" style="" id="cboxLoadingOverlay"></div><div class="" style="" id="cboxLoadingGraphic"></div><div class="" style="" id="cboxTitle"></div><div class="" style="" id="cboxCurrent"></div><div class="" style="" id="cboxNext"></div><div class="" style="" id="cboxPrevious"></div><div class="" style="" id="cboxSlideshow"></div><div class="" style="" id="cboxClose"></div></div><div style="float: left;" id="cboxMiddleRight"></div></div><div style="clear: left;"><div style="float: left;" id="cboxBottomLeft"></div><div style="float: left;" id="cboxBottomCenter"></div><div style="float: left;" id="cboxBottomRight"></div></div></div><div style="position: absolute; width: 9999px; visibility: hidden; display: none;"></div></div>';
		?>
		<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
		<style type="text/css">
		.showit{
			float:left;
			margin-right:11px;
			margin-bottom:11px;
			}
		.bioWrap{
			padding:8px 4px;
			}
		.handles h3{
			font-style:italic;
			font-size:109%;
			margin-top:5px;
			color:#555;
			}
		
		
		</style>
		<script src="/Library/js/jquery.js" type="text/javascript"></script>
		<script type="text/javascript">jQuery.noConflict();</script>
		<link type="text/css" media="screen" rel="stylesheet" href="/Library/css/colorbox.css" />
		<script type="text/javascript" src="/Library/js/jquery.colorbox.js"></script>
		<?php echo $colorboxFrame;?>

		<table cellpadding="0" cellspacing="0"><?php
		foreach($gallery as $category=>$frames){
			?><tr><td class="tac" style="width:45px;"><img src="/images/i-local/<?php echo strtolower($category);?>.png" alt="<?php echo $category;?>" /></td>
			<td>
			<?php
			foreach($frames as $folder=>$pictures){
				$root=current($pictures);
				$key=preg_replace('/[^A-Za-z0-9]+/','',$folder);
				reset($pictures);
				$category=strtolower($category);
				$str='/images/stylists/'.$record['UserName'].'/'.$category.'/'.$folder.'/'.$root;
				echo "\n\n";
				ob_start();
				?>
				<div class="showit">
				<a class="cboxElement" href="<?php echo $str;?>" rel="lightbox[<?php echo $key;?>]"><?php 
				echo tree_image(array(
					'src'=>$str,
					'boxMethod'=>2,
					'disposition'=>'114x114',
					'alt'=>preg_replace('/\.(jpg|gif|png)$/i','',$root),
					'return'=>'var',
				));
				?></a>
				<?php if(count($pictures)>1){ $i=0; ?>
				<ul id="GalleryThList" style="display:none">
				<?php foreach($pictures as $Tree_ID=>$v){ $i++; if($i==1)continue; ?>
				<li><span id="galleryItem">
				<div class="GalleryGImg"><a class="cboxElement" href="/images/stylists/<?php echo $record['UserName'].'/'.$category.'/'.$folder.'/'.$v;?>" rel="lightbox[<?php echo h($key);?>]" title="<?php echo h($folder);?>"><?php
				tree_image(array(
					'src'=>$Tree_ID,
					'alt'=>$folder,
					'boxMethod'=>2,
					'disposition'=>'114x114',
					'alt'=>$v,
				));
				?></a></div>
				</span>
				<div id="galleryComment"><?php echo $folder;?></div>
				</li>
				<?php } ?>
				</ul>
				<?php } ?>
				</div><?php
				$out=ob_get_contents();
				ob_end_clean();
				echo str_replace("\t\t\t\t",'',$out);
			}
			?></td></tr><?php
		}
		?></table><?php		
	}
}else if($thissubfolder){
	if(!($objects=q("SELECT o.* FROM addr_contacts c JOIN gen_objects o ON c.ID=o.Objects_ID AND o.ParentObject='addr_contacts' AND Rlx='".($thispage=='stylist-reviews'?'Review':'Video')."' WHERE c.UserName='$thissubfolder' ORDER BY o.Priority, o.CreateDate DESC", O_ARRAY))){
		exit(header('Location: /'));
	}
	//same stuff as with the stylist - merge into function
	?><style type="text/css">
	/* Testimonial Insets - created 2010-05-19 by Sam - if you use .fl or .fr declare *before* the .kudos class 
	<div id="k1" class="kudos">
		<div class="kTitle"></div>
		<div class="kBody">I was very happy with the service offered; I would recommend them to anyone</div>
		<div class="kAuthor">- John Mason, Bel TX</div>
	</div>
	*/ 
	.kudos{
		/* default width */
		width:175px;
		
		border:1px dotted cornsilk;
		padding:25px 5px 5px 25px; /* pad top and left */
		background-image:url("/images/i/misc/q-l-ffffff.png");
		background-repeat:no-repeat;
		background-position:5px 5px;
		}
	.kudos .kBody{
		margin:0px;
		background-image:url("/images/i/misc/q-r-ffffff.png");
		background-repeat:no-repeat;
		background-position:bottom right;
		/* optional 
		text-align:justify; */
		padding:0px 20px 20px 0px; /*pad bottom and right */
		}
	.kudos .kAuthor{
		/* optional: bring the author text within the quote 
		margin-top:-19px;*/
		font-style:italic;
		}	
	</style><?php
	foreach($objects as $v){
		if($thispage=='stylist-reviews'){
			?><div id="k<?php echo $v['ID'];?>" class="kudos">
			<div class="kTitle"></div>
			<div class="kBody"><?php echo $v['Settings'];?></div>
			<div class="kAuthor" style="padding-bottom:15px;"><!-- <?php echo $v['CreateDate'];?> --><?php echo $v['Description'];?></div>
	        </div><?php
		}else{
			?><div>
			<?php
			echo $v['Description'];
			?><p>
			<?php echo $v['Settings'];?>
			</p>
			</div><?php
		}
		?>
		</div><?php
	}

}


//-------------------------- here is where it ends -------------------------------
?></span><?php
$mainRegionCenterContent=ob_get_contents();
ob_end_clean();




ob_start();
if($thispage=='team'){
	CMSB('mainStylistOverview');
}else if($thisfolder=='team'){
	//stylist page - record from refA
	echo $img;
	?><div class="nameImg"><?php
	if($a=get_image($record['FirstName'].$record['LastName'],$stylistNameImgs)){
		$a=current($a);
		tree_image(array(
			'src'=>'/images/stylists/name-images/'.$a['name'],
			'alt'=>$record['FirstName'].' '.$record['LastName'],
		));
		
	}else{
		echo $record['FirstName']. ' ' . $record['LastName'];
	}
	?></div>
	<div class="handles"><?php
	if($n=$record['Level']){
		?><h3 class="stylistLevel"><?php echo $n;?></h3><?php
	}
	if($thispage=='jose-buitron'){
		?><h3 class="stylistLevel">Owner</h3>
		<h3 class="stylistLevel">Celebrity Stylist</h3><?php
	}
	?></div><?php
	if($pr=q("SELECT COUNT(*) FROM addr_contacts c JOIN gen_objects o ON c.ID=o.Objects_ID AND o.ParentObject='addr_contacts' AND o.Rlx='Video' WHERE c.UserName='$thispage'", O_VALUE)){
		?><a href="/team/<?php echo $thispage?>/meet-the-stylist" title="Meet the stylist videos"><img src="/images/i-local/meetthestylist.png" /></a><br /><br /><?php
	}
	if($sr=q("SELECT COUNT(*) FROM addr_contacts c JOIN gen_objects o ON c.ID=o.Objects_ID AND o.ParentObject='addr_contacts' AND o.Rlx='Review' WHERE c.UserName='$thispage'", O_VALUE)){
		?><a href="/team/<?php echo $thispage?>/stylist-reviews" title="Stylist reviews"><img src="/images/i-local/stylistreviews.png" /></a><br /><?php
	}
	unset($buffer);
	if($records=q("SELECT c.FirstName, c.LastName, c.UserName, s.* FROM addr_contacts c JOIN addr_contacts_JLSStaff s ON c.UserName=s.UserName WHERE
		s.Level IN('Master Stylist','Senior Stylist','Stylist','Junior Stylist') AND s.ShowWork>0 AND s.Active>0 ORDER BY
		IF(Level='Master Stylist',1,
		IF(Level='Senior Stylist',2,
		IF(Level='Stylist',3,
		IF(Level='Junior Stylist',4,5))))", O_ARRAY)){
		foreach($records as $v){
			//get the next
			if($startNext && !$next)$next=$v;
			//eval match
			if(strtolower($v['FirstName'])==strtolower($record['FirstName']) && strtolower($v['FirstName'])==strtolower($record['FirstName'])){
				//get prev as last record
				if(isset($buffer))$prev=$buffer;
				$startNext=true;
			}
			$buffer=$v;
		}
		if($prev){
			?><br />
			<a href="/team/<?php echo strtolower(str_replace(' ','-',$prev['FirstName'].' '.$prev['LastName']));?>" title="Click to see the previous stylist"><img src="/images/i-local/previous.png" /></a><?php
		}
		if($next){
			?><br />
			<a href="/team/<?php echo strtolower(str_replace(' ','-',$next['FirstName'].' '.$next['LastName']));?>" title="Click to see the next stylist"><img src="/images/i-local/next.png" /></a><?php
		}
	}
}

$mainRegionLeftContent=ob_get_contents();
ob_end_clean();

if($thisfolder=='team' && $thissubfolder==''){
	ob_start();
	//stylist page - record from refA
	
	//here I output the bio
	?><div class="bioWrap"><?php
	echo $record['LongBio'];
	?></div><?php
	
	$promoContent=ob_get_contents();
	ob_end_clean();
}






}//---------------- end i break loop ---------------
?>