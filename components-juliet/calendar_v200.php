<?php
/* name=Events Calendar; description=Everything from the past in one package!; */
/*
2013-04-06: Events Calendar
---------------------------------------------------
The idea behind this calendar is that other/all pages will be able to "hook into" this component and call it as an API; it's self contained and you just pass it the variables/initial conditions you need to set up output.
Data is FIRST PROCESSED into an organized array and THEN ouput is made.  The object requesting this might after all just want the array and to work on it itself.
The success of a component depends on how well you can change it either with php coding and functions (developer) or without (end user)
[update 2013-05-14: the above is still not close to ready..]
---------------------------------------------------


todo:
SAMF
	DONE	styling for you will be attending is inline, hard to read, get as setting + default
	lead to login a) no message "in order to {join} this event you need to sign in b) no link to "create an account" - do them as a side-by-side with default suggestive sales message + replacement
	DONE	have the event count showing on the console
	ability to change statuses of people
	ability to add people to the event	
	would be nice
		PERSISTENT LOGINS - and make sure I can't change my password etc. at this level
		DONE	ability to invite people - specifically, hi, auto-signin, and make the link to join/maybe/no be in the email itself
		ability to email peple attending|maybe on this event
		how do I integrate this in with facebook

TCA
	for now we go to thispage?Events_ID=
	navigating the calendar=componentControls?
	for TCA specifically
		DONE	bg color rgba
		DONE	header VISIBLE!	
		instructions at top
		3 days for Jim
		1 day for Roland and Robert
		color code the planes
		custom form for us

*/

$handle='eventsCalendar';
$version='2.0';

if(!defined('attendance_dec'))define('attendance_dec',0);
if(!defined('attendance_req'))define('attendance_req',1);
if(!defined('attendance_inv'))define('attendance_inv',2);
if(!defined('attendance_tent'))define('attendance_tent',4);
if(!defined('attendance_acc'))define('attendance_acc',8);
if(!defined('attendance_sch'))define('attendance_sch',16);
if(!defined('attendance_uns'))define('attendance_uns',32);
if(!defined('attendance_inc'))define('attendance_inc',64);
if(!defined('attendance_ok'))define('attendance_ok',128);

//let's go ahead and register the component if not done
if(!($_Components_ID_=q("SELECT ID FROM gen_components WHERE Handle='$handle' AND Version='$version'", O_VALUE))){
	mail($developerEmail, 'Warning in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='component was registered on-the-fly'),$fromHdrBugs);
	$f=explode('/',__FILE__);
	$ComponentFile=array_pop($f);
	$Location=(end($f)=='components-juliet'?'JULIET_COMPONENT_ROOT':(end($f)=='components'?'COMPONENT_ROOT':end($f)));
	$_Components_ID_=q("INSERT INTO gen_components SET Handle='$handle', Version='$version', Location='$Location', ComponentFile='$ComponentFile', CreateDate=NOW(), Creator='".sun()."'", O_INSERTID);
	prn($qr);
}

//let's get our data
if($Parameters=q("SELECT cn.Settings FROM gen_components c LEFT JOIN gen_ComponentsNodes cn ON c.ID=cn.Components_ID WHERE c.ID='$_Components_ID_' AND cn.Nodes_ID='".($_thisnode_ ? $_thisnode_ : $thisnode) ."'", O_VALUE)){
	$Parameters=unserialize(base64_decode($Parameters));
	if(!empty($Parameters))$pJ['componentFiles'][$handle]=$Parameters;
}else{
	unset($pJ['componentFiles'][$handle]);
}

//settings
if(!$calMainBlock)$calMainBlock=pJ_getdata('calMainBlock','mainRegionCenterContent');
//default CSS
if(false){ ?><div style="display:none;"><style type="text/css"><?php } 
ob_start();?>
#cal{
	}
.calHeader{
	}
#calNav{
	}
.cal10{
	border-collapse:collapse;
	width:100%;
}
.cal10 th{
	padding:6px 0px 2px 6px;
	font-size:115%;
	font-weight:400;
	/* font-family:Georgia, "Times New Roman", Times, serif; */
	}
.cal10 td{
	padding:2px 5px;
	}
.gridSquare{
	width:14%;
	height:85px;
	}
.gridDay{
	float:right;
	font-size:11px;
	}
.cnclr{
	font-size:139%;
	font-family:Georgia, "Times New Roman", Times, serif;
	}
#cal .unavailable{
	filter:alpha(opacity=40);
	-moz-opacity:.40;
	opacity:.40;
	}
#cal a:hover{
	}
#calGrid{
	}
.dot{
	}
.monthYear{
	}
/* ---- borders, colors and background colors ------- */
#cal a{
	}
.cnclr{
	}
.nhclr, .chclr{
	color:#FFF;
	background-color:#1c4879;
	}
.cal10{
	}
.cal10 th{
	background-color:tan;
	border-bottom:1px solid #000;
	}
.cal10 tbody tr{
	border-left:1px solid #000;
	border-right:1px solid #000;
	}
.cal10 td{
	border:1px dotted #777;
	}
.cal10 .bottom td{
	border-bottom:1px solid #000;
	}
.hasEvent{
	}
.today{
	background-color:darksalmon;
	}
.noday{
	background-color:#ccc;
	cursor:auto;
	}

/* individual event CSS */
.event{
	border-bottom:1px dotted #272727;
	margin-top:20px;
	padding:15px 0px;
	}
.event .payonline{
	float:right;
	}
.event .desc{
	clear:right;
	margin-top:15px;
	}
table.stats{
	border-collapse:collapse;
	margin-bottom:15px;
	}
.stats td{
	border-bottom:1px dotted #272727;
	padding:2px 15px 1px 7px;
	vertical-align:top;
	}
.rtc:hover{
	text-decoration:none;
	}
.rtc{
	font-weight:400;
	font-size:smaller;
	}
/* 2013-05-17 event interaction added */
#eventInteraction{
	border:1px solid #ccc;
	margin:7px 0px;
	padding:10px 15px;
	}
.eventWord{
	display:block;
	padding:3px;
	margin-bottom:3px;
	border-bottom:1px solid #eee;
	background-color:#3B5998;
	color:white;
	font-weight:900;
	font-size:107%;
	font-family:Tahoma,Arial;
	}
<?php if(false){ ?></style></div><?php }
$calCSS=trim(ob_get_contents());
ob_end_clean();
$calCSS=pJ_getdata('calCSS',$calCSS);
if($calCSS)$pJLocalCSS[$handle]=trim($str)."\n".trim($calCSS);
//field out
$calPreventPastNavigation=pJ_getdata($calPreventPastNavigation,false);
$gridEventOnclick=pJ_getdata($gridEventOnclick,'');
$calNavLeftImage=pJ_getdata('calNavLeftImage','/images/i/arrows/2_white_left.png');
$calNavRightImage=pJ_getdata('calNavRightImage','/images/i/arrows/2_white_right.png');
$calHeader=pJ_getdata('calHeader','Calendar');
$calIntro=pJ_getdata('calIntro',true);
$calRangeYears=pJ_getdata('calRangeYears','5');

$calCustomPHP=pJ_getdata('calCustomPHP'); //2013-04-06: custom function replacing trainingCalendarGrid


$calPage=pJ_getdata('calFocusPage','/'.($thisfolder?$thisfolder.'/':'').($thissubfolder?$thissubfolder.'/':'').$thispage);

$calGridDisplayFunction=pJ_getdata('calGridDisplayFunction','trainingCalendarGrid');
$calCustomEventsQuery='calCustomEventsQuery';
$cals=q("SELECT * FROM cal_cal ORDER BY ID", O_ARRAY);


//----------- these are antiquated; what am I going to do with it? ----------------- 
$hideCalEventListing=true;
$calRewrite=true; #I no longer do this this way - see ecommerce rewrite
if(!isset($calRewrite))$calRewrite=false;







if(!isset($maxCalItemListings))$maxCalItemListings=5;
if(!isset($calForceEventAllLink))$calForceEventAllLink=false;
if(!isset($showCalEventHeaderOnEmpty))$showCalEventHeaderOnEmpty=true;
if(!$calEventWhereClause){
	$calEventWhereClause='1 AND ';
}else{
	$calEventWhereClause=preg_replace('/\s+AND$/i','',$calEventWhereClause).' AND ';
}
if(!isset($calEventFunction))$calEventFunction='event_write'; //puts the write of the event listing into a user-defined function
if(!isset($hideEventsAllLink))$hideEventsAllLink=false;
if(!isset($calEventAllLinkText))$calEventAllLinkText='See calendar page..';
if(!isset($calEventNoEventsPresent))$calEventNoEventsPresent='';

if(!isset($calHideGridEvents))$calHideGridEvents=false;

if(!isset($calPreventPastNavigation))$calPreventPastNavigation=true;
if(!$calPreventFutureNavigation)$calPreventFutureNavigation=''; //format should be 201209 i.e. YYYYMM - default unlimited future navigation

if(!$calDayShowRange)$calDayShowRange=7; //how many days to show
if(!$calDayOffset)$calDayOffset=0; //which day to start on (default of 0=sunday)
if(!$calWeekDayLabels) $calWeekDayLabels=array('Sun', 'Mon', 'Tues', 'Wed', 'Thurs', 'Fri', 'Sat');
if(!isset($gridDayUsage))$gridDayUsage='load';
if(!isset($gridEventUsage))$gridEventUsage='focus';




//functions
if(!function_exists('event_write')){
function event_write($v){
	global $qr,$fl,$ln,$developerEmail,$fromHdrBugs,$adminMode;
	global $calPage;
	extract($v);
	//date
	?><div class="itemHdr"><?php
	if($EndDate!='0000-00-00'){
		echo date('M jS',strtotime($StartDate)). ($StartDate!=$EndDate ? ' - '.date('M jS',strtotime($EndDate)) : '');
	}else{
		echo date('l, M jS',strtotime($StartDate));
	}
	if($StartTime!='00:00:00'){
		echo '<br />';
		?><span class="time"><?php
		if($EndTime!='00:00:00'){
			echo date('g:i a',strtotime($StartTime)) . ' to '.date('g:i a',strtotime($EndTime));
		}else{
			echo '@ '.date('g:i a',strtotime($StartTime));
		}
		?></span><?php
	}
	?></div>
	<div class="itemName"><?php
	if($adminMode){
		?><a class="_editLink_1" title="Edit this event" href="/console/events.php?Events_ID=<?php echo $ID?>" onclick="return ow(this.href,'l1_events','700,700');"><img src="/images/i/plusminus-plus.gif" width="11" height="11" alt="edit" /></a>
		&nbsp;<?php
	}
	?><a title="See details about this event" href="<?php echo $calPage;?>?Events_ID=<?php echo $ID?>"><?php echo $Name?></a></div>
	<?php
	if($BriefDescription){
		?><div class="itemDesc"><?php echo $BriefDescription;?></div><?php
	}
	if($ContactName || $ContactEmail || $ContactPhone){
		?><div class="contactInfo">
		<strong>Contact:</strong> <?php
		echo $ContactName;
		if($ContactPhone){
			echo ' '.$ContactPhone;
		}
		if($ContactEmail){
			if($ContactPhone)echo '<br />';
			//encrypt email
			js_email_encryptor($ContactEmail);
		}
		?></div><?php
	}
	if($URL){
		?><div class="url"><a title="Website link" href="<?php echo $URL?>"><?php echo $URL?></a></div><?php
	}
}
}
if(!function_exists('cal_grid_display')){
function cal_grid_display($day, $events, $options=array()){
	global $adminMode, $year, $month, $qr, $qx, $fl, $ln, $developerEmail, $fromHdrBugs, $Cal_ID;
	global $calPage;
	?>
	<div class="gridDay"><?php 
	if($adminMode){
		?><a class="_editLink_1" href="/console/events.php?Cal_ID=<?php echo $Cal_ID?$Cal_ID:1?>&StartDate=<?php echo $year.'-'.$month.'-'.str_pad($day,2,'0',STR_PAD_LEFT);?>" title="Add a new event for this day" onclick="return ow(this.href,'l1_events','700,700',true);"><img src="/images/i/plusminus-plus.gif" width="11" height="11" alt="new event" /></a> <?php
	}
	echo $day?></div>
	<div class="gridEvents"><?php
	if(count($events))
	foreach($events as $n=>$v){
		?><div class="gridEvent"><a class="gridEventLink" title="<?php echo h($v['Description'])?>" href="<?php echo $calPage?>?Events_ID=<?php echo $v['ID']?>"><?php
		//handle text length eventually
		echo $v['Name'];
		?></a></div><?php
	}
	?></div>
	<?php
}
}
if(!function_exists('calCustomEventsQuery')){
function calCustomEventsQuery($date){
	global $calEventWhereClause;
	return q("SELECT cal_events.*
	FROM cal_events 
	WHERE $calEventWhereClause cal_events.Active=1 AND
	(
	(StartDate='$date' AND (!EndDate OR EndDate IS NULL)) OR (StartDate<='$date' AND EndDate >='$date')
	)", O_ARRAY);
}
}
if(!function_exists('trainingCalendarGrid')){
function trainingCalendarGrid($thisDay, $events){
	global $gridEventUsage,$gridEventOnclick, $gridDayUsage, $calAccesToken, $year, $month, $day, $date, $gtParent, $adminMode;
	global $calPage;
	//------------------ gridDay and usage ------------------
	if($adminMode){
		?><a class="gridDayLink" href="/console/events.php?StartDate=<?php echo $year.'-'.$month.'-'.$thisDay;?>" onclick="return ow(this.href,'l1_event','700,700',true);" title="Add a new event for this day"><?php
	}
	?><div class="gridDay"><?php echo $thisDay?></div><?php
	if($adminMode){
		?></a><?php
	}
	if($gridEventUsage=='focus'){
		?><div class="gridEvents"><?php
		if(count($events) && !$calHideGridEvents)
		foreach($events as $n=>$v){
			if(!$printed){
				//prn($events);
				$printed=true;
			}
			?><div class="gridEvent<?php if($v['Cal_ID'])echo ' fromCal'.$v['Cal_ID']?>"><?php
			if($adminMode){
				?><a class="_editLink_1" href="/console/events.php?Events_ID=<?php echo $v['ID'];?>" title="Edit this event" onclick="return ow(this.href,'l1_events','700,700');"><img src="/images/i/plusminus-plus.gif" width="11" height="11" alt="edit" /></a><?php
			}
			if($gridEventUsage=='focus'){
				?><a class="gridEventLink" title="<?php echo str_replace('"','&quot;',strip_tags($v['Description']))?>" href="<?php echo $calPage?>?Events_ID=<?php echo $v['ID']?>" <?php echo $gridEventOnclick?>><?php
			}
			//handle text length eventually
			if($function=$calGridEventOutputFunction && $direction=$calRegisteredFunctions[$calGridEventOutputFunction]){
				if(is_bool($direction)){
					echo $calGridEventOutputFunction($v);
				}else{
					//undeveloped; more complex call
				}
			}else{
				?>
				
				<span class="timePart"><?php echo date('g:iA',strtotime($v['StartTime']));?></span>
				<span class="titlePart"><?php echo $v['Name'];?></span>
				<?php
			}
			if($gridEventUsage=='focus'){
				?></a><?php
			}
			?></div><?php
		}
		?></div><?php
	}
}
}

for($__i__=1; $__i__<=1; $__i__++){ //---------------- begin i break loop ---------------


if($mode=='componentControls'){
	if($submode=='eventHandle'){
		/*
		2013-05-13: ok wht we'd normally do here is 
			error check data submitted
			check the action's legality
			perform the action
			update the parent page html and status variables
		HOWEVER, we have the following constraints:
			an action can only be done when logged in in this case; if not then we would redirect to the login page, with a registered message (never really developed as a component), and a redirect to perform the action. the PROBLEM with that is that we're in a "w2 hole" and I have never developed a resubmit or requery.
			the other issue is that OFTEN the same page processed would show the way we want it to if and after we just ran the db updates; so this is where mincemeat comes in.
		we have a few options here
			this script calls the login api from itself, and piggybacks on available hardware (complex) to requery (GET or POST)
			as above, develop the resubmit or requery as a global feature
		*/
		if(!$_SESSION['cnx'][$acct]){
			$r=str_replace('http://','',$HTTP_REFERER);
			$r=str_replace('https://','',$r);
			$r=preg_replace('/&*resubmit=[^&]*/','',$r);
			$r=explode('/',$r);
			unset($r[0]);
			$r='/'.implode('/',$r);
			?><script language="javascript" type="text/javascript">
			//ok here we would sessionize the submission/request in a stack, give it a key value, and call it back
			//we would also specify a message before login, or even register a message with the cgi component.  tho a "native" component, it has a stnadard protocol (in my new world) to do this.
			window.parent.location='/cgi/login?src=<?php echo urlencode($r);?>&resubmit=1';
			</script><?php
			eOK();
		}
		//error checking
		$Contacts_ID=$_SESSION['cnx'][$acct]['primaryKeyValue'];
		if(!($event=q("SELECT * FROM cal_events WHERE ID=$Events_ID", O_ROW)))error_alert('unable to find this event');
		if(!($contact=q("SELECT * FROM addr_contacts WHERE ID='$Contacts_ID'", O_ROW)))error_alert('unable to find your contact record');
		if($event['AllowEventSignup']<10)error_alert('this event does not allow for joining it');
		$entry=q("SELECT Status, Comments FROM addr_ContactsEvents WHERE Contacts_ID='$Contacts_ID' AND Events_ID=$Events_ID", O_ROW);
		if($event['AllowEventSignup']<20 && !$entry)error_alert('you must be invited to this event to join it');

		//database entries
		if($entry){
			q("UPDATE addr_ContactsEvents SET Status=".$key.", EditDate=NOW(), Editor='".$_SESSION['systemUserName']."' WHERE Contacts_ID=$Contacts_ID AND Events_ID=$Events_ID");
		}else{
			q("INSERT INTO addr_ContactsEvents SET Contacts_ID=$Contacts_ID, Events_ID=$Events_ID, Status=".$key.", Creator='".$_SESSION['systemUserName']."', CreateDate=NOW()");
		}
		prn($qr);
		//mincemeat
		mm(array(
			'sections'=>array(
				'eventInteraction'=>array(
					'method'=>'basic',
				),
			)
		));
		//this is an artificial stop to simulate all HTML-output parts of the component being sectioned
		goto placeholder_sectionstart; 
	}
}else if($mode=='componentEditor'){
	if($submode=='import'){
		$ImportString=trim($ImportString);
		if(!preg_match('/^[+a-zA-Z0-9=]+$/',$ImportString))error_alert('The string you are attempting to import does not appear to be valid.  It must be a base 64-encoded serialized array');
		$temp=unserialize(base64_decode($ImportString));
		if(empty($temp))error_alert('The string you are attempting to import does not appear to be valid.  It must be a base 64-encoded serialized array');
		if($ImportMerge){
			$a=unserialize(base64_decode($ImportString));
			$ImportString=base64_encode(serialize(array_merge_accurate($Parameters,$a)));
		}else{
			//no action
		}
		error_alert('since development of gen_ComponentsNodes, imports have been disabled');
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
	/* 2012-03-12: this is universal code which should be updated on ALL components	*/



	if($submode=='export')ob_start();
	if($_thisnode_){
		/*
		2013-04-06: this is completely new coding to go into gen_components.ComponentSettings and gen_ComponentsNodes.Settings, vs. gen_nodes_settings.Settings.  this is a much better and less conflicting storage
		*/

		//pJ.componentFiles is the var storage cabinet for all components
		!is_array($pJ['componentFiles'][$handle]) ? $pJ['componentFiles'][$handle]=array() : '';
		//now integrate the form post turtled
		$pJ['componentFiles'][$handle]['data'][$formNode]=stripslashes_deep($_POST[$formNode]);
		//we assume (2013-04-06) that the page node exists, but the join record may not
		if($a=q("SELECT * FROM gen_ComponentsNodes WHERE Components_ID=$_Components_ID_ AND Nodes_ID='".($_thisnode_ ? $_thisnode_ : $thisnode) ."'", O_ROW)){
			$Settings=unserialize(base64_decode($a['Settings']));
			prn($qr);
			prn($Settings);
		}else{
			q("INSERT INTO gen_ComponentsNodes SET Components_ID=$_Components_ID_, Nodes_ID=".($_thisnode_?$_thisnode_:$thisnode));
			$Settings=array();
			prn($qr);
		}
		$Settings['data'][$formNode]=$pJ['componentFiles'][$handle]['data'][$formNode];
		q("UPDATE gen_ComponentsNodes SET Settings='".base64_encode(serialize($Settings))."' WHERE Components_ID=$_Components_ID_ AND Nodes_ID=".($_thisnode_?$_thisnode_:$thisnode));
		break;
	}else{
		exit('unable to update component');
	}
	if($submode=='export'){
		ob_end_clean();
		$str='-- Juliet version '.$pJVersion.', file '.end(explode('/',__FILE__)).'; exported '.date('n/j/Y \a\t g:iA').' - to re-import, paste the code on the next line into the desired component ----'."\n";
		$str.=base64_encode(serialize($Parameters));
		$str.="\n--------- the following should NOT be pasted in but is an unencoded version of the above -------\n";
		ob_start();
		print_r($Parameters);
		$str.=ob_get_contents();
		ob_end_clean();
		attach_download('', $str, str_replace('.php','',end(explode('/',__FILE__))).'_'.date('Y-m-d_his').'.txt');
	}
	break;
}else if($formNode=='default'){
	$tabVersion=3;
	?><p>Calendar Basic Settings 0 heading here</p><?php
	ob_start();
	?>
	<p class="gray">This covers where the data comes from, what data shows, and contextually how it is presented</p>
	<?php
	get_contents_tabsection('datasource');
	?>
	<p class="gray">For now this one block of CSS will handle all styling for the calendar.  If other components "hook into" this we should be able to limit sections to those needed for specific insets etc.	</p>
	<h3 class="gray">Navigation buttons</h3>
	<div class="fl">
	  Left (previous):
	  <input name="default[calNavLeftImage]" type="text" id="default[calNavLeftImage]" onchange="dChge(this);" value="<?php echo $calNavLeftImage;?>" size="40" />
      <br />
	  Right (next):
	  <input name="default[calNavRightImage]" type="text" id="default[calNavRightImage]" onchange="dChge(this);" value="<?php echo $calNavRightImage;?>" size="40" />
    </div>
	<div class="fl">
	<a href="/admin/file_explorer/?uid=cal" onclick="return ow(this.href,'l1_fex','700,700');"><img src="/images/i/fex104/bootleg_ms_folder.gif" width="38" height="37" /></a>
	</div>
	<textarea name="default[calCSS]" id="calCSS" class="tabby" onchange="dChge(this);" rows="15" cols="100%" style="padding:0px 0px 0px 10px;"><?php
	echo h($calCSS);
	?></textarea>
	<?php
	get_contents_tabsection('styling');
	?>
	<p class="gray">This is the "calendar grid" classic view
	</p>
	<?php
	get_contents_tabsection('gridview');
	?>
	<p class="gray">This is the inset view of the calendar with associated settings
	</p>
	<?php
	get_contents_tabsection('insetview');
	?>
	<p class="gray">This is when a single event is focused on</p>
	<?php
	get_contents_tabsection('focusview');
	?>
	<p class="gray">Advanced settings</p>
	<p>Name of grid function: <span class="gray">(passed $thisDay and $events as array)</span></p>
	<input type="text" name="default[calGridDisplayFunction]" id="default[calGridDisplayFunction]" value="<?php echo $calGridDisplayFunction;?>" onchange="dChge(this);" />
	<p><strong>Custom PHP coding</strong>: <span class="gray">(will be eval'd, can include function calls)</span></p>
	<textarea name="default[calCustomPHP]" id="calCustomPHP" class="tabby" onchange="dChge(this);" rows="15" cols="100%" style="padding:0px 0px 0px 10px;"><?php echo h($calCustomPHP);?></textarea>
	
	<?php
	get_contents_tabsection('advanced');
	tabs_enhanced(
		array(
			'layout'=>array( 'label'=>'Layout'),
			'datasource'=>array( 'label'=>'Data Source'),
			'styling'=>array( 'label'=>'Styling'),
			'gridview'=>array( 'label'=>'Grid View'),
			'insetview'=>array( 'label'=>'Inset View'),
			'focusview'=>array( 'label'=>'Focus View'),
			'advanced'=>array( 'label'=>'Advanced'),
		) 
	);
	
	break;
}

//begin blocks
ob_start();
//edit link
echo pJ_call_edit(array(
	'level'=>ADMIN_MODE_DESIGNER,
	'location'=>'JULIET_COMPONENT_ROOT',
	'file'=>end(explode('/',__FILE__)),
	'thisnode'=>$thisnode,
	'thissection'=>$thissection,
	'label'=>'Edit Cal Settings',
));
placeholder_sectionstart:



//=========================================== old code (all) ================================
if($Events_ID){
	$showCalWidget=true;
	if(!($cal=q("SELECT * FROM cal_events WHERE Active=1 AND ResourceType IS NOT NULL /* public calendar */ AND ID='$Events_ID'", O_ROW))){
		header('Location: /');
		exit;
	}
	extract($cal);
	$logicalLink=true;
	unset($havemain);
	if($Items_ID){
		$item=q("SELECT * FROM finan_items WHERE ID=$Items_ID", O_ROW);
		$enrollments=q("SELECT SUM(Quantity) FROM finan_transactions WHERE Items_ID=$Items_ID", O_VALUE);
	}else{
		unset($item,$enrollments);
	}
	if($pics=get_file_assets('images/events/event'.$ID,'large')){
		foreach($pics as $n=>$v){
			if(preg_match('/^main/i',$n)){
				$havemain=$v;
				$havemain['full']='images/events/event'.$ID.'/'.$v['name'];
				break;
			}
		}
	}
	if($img=$havemain){
		//OK
	}else if('holder has logo'){
	
	}else if('sponsor has logo'){
	
	}
	$Cost=trim($Cost);
	$a=explode("\n",$Cost);
	$a=explode(":",$a[0]);
	$OneCost=trim($a[count($a)-1]);
	if(($Deadline!=='0000-00-00' && strtotime($Deadline)<time())
		||
		($MaxEnrollments>0 && $enrollments >=$MaxEnrollments)
		||
		!$AllowOnlinePayment
		||
		!trim($Cost)){
		//no online payment
		$pay=false;
	}else{
		$pay=true;
	}
	if(!$refreshComponentOnly){
		?><script language="javascript" type="text/javascript">
		$(document).ready(function(){
			$('#eventJoin').click(function(){
				window.open('/index_01_exe.php?location=JULIET_COMPONENT_ROOT&file=<?php echo end(explode('/',__FILE__));?>&mode=componentControls&submode=eventHandle&key=<?php echo attendance_acc;?>&Events_ID=<?php echo $Events_ID;?>','w2');
			}<?php /*run this now*/	if($action=='eventJoin')echo '()';?>);
			$('#eventMaybe').click(function(){
				window.open('/index_01_exe.php?location=JULIET_COMPONENT_ROOT&file=<?php echo end(explode('/',__FILE__));?>&mode=componentControls&submode=eventHandle&key=<?php echo attendance_tent;?>&Events_ID=<?php echo $Events_ID;?>','w2');
			}<?php /*run this now*/	if($action=='eventMaybe')echo '()';?>);
			$('#eventNo').click(function(){
				window.open('/index_01_exe.php?location=JULIET_COMPONENT_ROOT&file=<?php echo end(explode('/',__FILE__));?>&mode=componentControls&submode=eventHandle&key=<?php echo attendance_dec;?>&Events_ID=<?php echo $Events_ID;?>','w2');
			}<?php /*run this now*/	if($action=='eventNo')echo '()';?>);
			$('#eventOptions').change(function(){
				window.open('/index_01_exe.php?location=JULIET_COMPONENT_ROOT&file=<?php echo end(explode('/',__FILE__));?>&mode=componentControls&submode=eventHandle&key='+$(this).val()+'&Events_ID=<?php echo $Events_ID;?>','w2');
			});
		});
		</script><?php
	}
	?>
	<div class="event">
	<?php
	#for($i=0;$i<=255;$i++)prn(chr($i).': '.urlencode(chr($i)));
	?>
		<?php if(!$ShowOnlyDescription){ ?>
		<h1 class="title"><?php
		if($adminMode){
			?><a class="_editLink_1" title="edit this event" href="/console/events.php?cbFunction=refreshList&Events_ID=<?php echo $ID?>" onclick="return ow(this.href,'l1_events','700,700');"><img src="images/i/plusminus-plus.gif" width="11" height="11" alt="edit this event" /></a><?php
		}
		?>
		<div class="fr"><a class="rtc" href="<?php echo $thispage;?>">&laquo; Return to calendar</a></div>
		<?php echo $Name?></h1>
		<?php }?>
		<?php
	if($pay){
		?><div class="fr" style="border:1px dotted gold;padding:15px;">
		Purchase Tickets Here!<br />
		Cost: <?php
		$a=explode("\n",$Cost);
		if(count($a)==1){
			?><strong>$<?php echo number_format($OneCost,2);?></strong><br /><?php
		}else{
			$range=array();
			foreach($a as $v){
				$v=explode(':',$v);
				$range[trim($v[0])]=trim($v[1]);
			}
			?><table class="stats"><?php
			$persons=1;
			foreach($range as $n=>$v){
				?><tr>
				<td><?php
				//number of people
				#can use ticket in place of persons
				#one person
				#one person or more
				#1-3 people
				echo $n.' person'.($n>1?'s':'');				
				?></td>
				<td>$<?php
				echo number_format($v,2);
				if($n>1)echo ' ('.number_format(floor($v/$n),2).' per person)';
				?></td>
				</tr><?php
			}
			?></table><?php
		}
		?>
		<form action="index_01_exe.php" method="post" name="form2" target="w2" id="form2" style="display:inline;">
		<?php
		if($AllowMultiplePurchases){
			?>Number: 
			<select name="qty" id="qty">
			<option value=""> -- </option>
			<?php
			for($i=1;$i<=30;$i++){
				?><option value="<?php echo $i?>"><?php echo $i?></option><?php
			}
			?>
			</select><?php
		}else{
			?>
			<input name="qty" type="hidden" id="qty" value="1" />
			<?php
		}
		?><br />
		<input name="mode" type="hidden" id="mode" value="eventBuy" />
		<input name="Events_ID" type="hidden" id="Events_ID" value="<?php echo $Events_ID;?>" />
		<input name="ID" type="hidden" id="ID" value="<?php echo q("SELECT ID FROM finan_items WHERE SKU='EventItem-$Events_ID'", O_VALUE);?>" />
		<input type="submit" name="Submit" value="Purchase Now" />
		</form>
		</div><?php
	}
	if($img && $ShowOnlyDescription){
		?><div class="fl">
		<img src="<?php echo $img['full']?>" />
		</div><?php
	}
	?>
	<?php if(!$ShowOnlyDescription){ ?>
	<div class="date"><strong>When:</strong> <?php
	echo date('m/d/Y',strtotime($StartDate)) . ($EndDate!=='0000-00-00'?' through '.date('m/d/Y',strtotime($EndDate)):'');
	if($StartTime!=='00:00:00'){ 
		?><br /><?php
		echo date('g:iA',strtotime($StartTime)) . ($EndTime!=='00:00:00' ? ' - '.date('g:iA',strtotime($EndTime)):'');
	}
	if($ScheduleNotes)echo '<br /><em>'.$ScheduleNotes .'</em>';
	?></div>
	<?php } ?>
	<?php if(!$ShowOnlyDescription){ ?>
	<?php
	if($Cost>0){
		$pay=true;
		?><div class="cost"><?php
		?><strong>Cost:</strong> <?php echo number_format($OneCost,2)?></div><?php
	}else{
		$pay=false;
	}
	if($Location || $Address){
		?><div class="location"><strong>Where:</strong> <?php echo $Location?>
			<span class="addrpad">
				<?php if($Address){ ?>
				<br />
				<?php echo $Address?>
				<?php } ?>
				<?php if($City){ ?>
				<br />
				<?php echo $City . ($State ? ', '.$State:'').' '.$Zip;?>
				<?php } ?>
		  </span>
		</div><?php
	}
	/*
	//this has complexities over and above facebook in that: a) we are dealing with login/logout b) different levels of event signup c) I'm worried that invited/declined or some combo leads to an irreversible condition
	if($AllowEventSignup==20){
		if($_SESSION['cnx'][$acct]){
			if( 
				null "[join] [maybe]"
				decl "not attending" *
				req  "request pending" - but would not be an option for AllowEventSignup=20 since anyone can attend
				inv  "`you are invited to this event` [join] [maybe]"
				tent "maybe"
				acc  "attending"
				sch, uns, inc - n/a (but something like "unsat/incomplete")
				ok	"you were here"
				
		}else{
			//Join or Maybe
			
		}
	}else if($AllowEventSignup==10){
		if($_SESSION['cnx'][$acct]){
			if( 
				null "request attendance || buy tickets"
				decl "not attending" (with this status they could change things)
				req  "request pending"
				inv  "`you are invited to this event` [join] [maybe]"
				tent "maybe"
				acc  "attending"
				sch, uns, inc - n/a (but something like "unsat/incomplete")
				ok	"you were here"
			
		}else{
			//join
		}
	}
	*/






	//-------------- begin event interaction HTML ---------------
	mm();
	switch($_mm['node']){
		case 'eventInteraction': goto eventInteraction;
		case 'compend': goto compend;
	}
	eventInteraction:

	//this has complexities over and above facebook in that: a) we are dealing with login/logout b) different levels of event signup c) I'm worried that invited/declined or some combo leads to an irreversible condition
	ob_start();
	q("SELECT AllowEventSignup FROM cal_events WHERE ID=$Events_ID");
	$err=ob_get_contents();
	ob_end_clean();
	if($err){
		//make event editable by default - TIGRIS
		$defaultEventSignupValue=20;
		q("UPDATE cal_events SET AllowEventSignup='$defaultEventSignupValue'");
	}
	if($AllowEventSignup>0){
		$buttons=array(); $options=array();
		if($_SESSION['cnx'][$acct])	$a=q("SELECT e.ID, ce.Status FROM cal_events e LEFT JOIN addr_ContactsEvents ce ON e.ID=ce.Events_ID AND ce.Contacts_ID='".$_SESSION['cnx'][$acct]['primaryKeyValue']."' WHERE e.ID=$Events_ID", O_ROW);
		if($AllowEventSignup==20){
			if($_SESSION['cnx'][$acct]){
				switch(true){
					case is_null($a['Status']):
						#join:standard maybe:standard
						$buttons=array('join','maybe');
					break;
					case $a['Status']==attendance_dec:
						#not attending *[attendance_tent|attendance_acc]
						$word='Not attending';
						$options=array(attendance_tent=>'Maybe', attendance_acc=>'Will be attending');
					break;
					/*req  "request pending" - but would not be an option for AllowEventSignup=20 since anyone can attend*/
					case $a['Status']==attendance_inv:
						#`you are invited to this` join:standard maybe:standard
						$word='You are invited to this event';
						$buttons=array('join','maybe','decline');
					break;
					case $a['Status']==attendance_tent:
						#`you may be attending this` *[attendance_dec|attendance_acc]
						$word='you may be attending this event';
						$options=array(attendance_dec=>'Will not be attending', attendance_acc=>'Will be attending');
					break;
					case $a['Status']==attendance_acc:
						#`you will be attending this` *[attendance_dec|attendance_tent]
						$word='You will be attending this event';
						$options=array(attendance_dec=>'Will not be attending', attendance_tent=>'Maybe');
					break;
					/*sch, uns, inc - n/a (but something like "unsat/incomplete")*/
					case $a['Status']==attendance_ok:
						#`you were here` {comments}
						$word='You were here';
					break;
				}
			}else{
				//Join or Maybe
				#join:standard maybe:standard [no?]
				$buttons=array('join','maybe');
			}
		}else if($AllowEventSignup==10){
			if($_SESSION['cnx'][$acct]){
				switch(true){
					case is_null($a['Status']):
						#request_attendance:standard
						$buttons=array('request');
					break;
					case $a['Status']==attendance_dec:
						#not attending *[attendance_tent|attendance_acc]
						$word='Not attending';
						$options=array(attendance_tent=>'Maybe', attendance_acc=>'Attending');
					break;
					case $a['Status']==attendance_req:
						#`request pending` [cancel]
						$word='Request pending';
						$buttons=array('cancel');
					break;
					case $a['Status']==attendance_inv:
						#`you are invited to this event` join:standard maybe:standard no:standard
						$word='You are invited to this event';
						$buttons=array('join','maybe','decline');
					break;
					case $a['Status']==attendance_tent:
						#`you may be attending this` *[attendance_dec|attendance_acc]
						$word='you may be attending this event';
						$options=array(attendance_dec=>'Will not be attending', attendance_acc=>'Will be attending');
					break;
					case $a['Status']==attendance_acc:
						#`you will be attending this` *[attendance_dec|attendance_tent]
						$word='You will be attending this event';
						$options=array(attendance_dec=>'Will not be attending', attendance_tent=>'Maybe');
					break;
					/*sch, uns, inc - n/a (but something like "unsat/incomplete")*/
					case $a['Status']==attendance_ok:
						#`you were here` {comments}
						$word='You were here';
					break;
				}
			}else{
				//join - button is same but action will be different based on event allow type
				$buttons=array('join');
			}
		}
		//added this so the action would be present for/from an email invite
		if($action=='eventNo' && !in_array('decline',$buttons))$buttons[]='decline';
		//output
		?><div id="eventInteraction"><?php
		if($word){
			?><span class="eventWord"><?php echo $word;?></span><?php
		}
		?><span class="eventButtons"><?php
		foreach($buttons as $v)switch($v){
			case 'join':
				?><button id="eventJoin" class="eventButton">Join</button><?php
			break;
			case 'maybe':
				?><button id="eventMaybe" class="eventButton">Maybe</button><?php
			break;
			case 'decline':
				?><button id="eventNo" class="eventButton">No</button><?php
			break;
			case 'cancel':
				?><button id="eventCancel" class="eventButton">Cancel</button><?php
			break;
		}
		?></span><?php
		if($options){
			?><span class="eventOptions">Change to: 
			<select id="eventOptions">
			<option value="">&lt;Select..&gt;</option><?php
			foreach($options as $n=>$v){
				?><option value="<?php echo $n?>"><?php echo $v;?></option><?php
			}
			?></select>
			</span><?php
		}
		?></div><?php
	}
	mm();
	switch($_mm['node']){
		case 'eventInteraction': goto eventInteraction;
		case 'compend': goto compend;
	}
	placeholder_sectionend:

	//------------------- end event interaction ----------------------




	?>
	<?php } ?>
	<?
	$desc=preg_replace('/^<p[^>]*>/i','',trim($Description));
	$desc=preg_replace('/<\/p>$/i','',$desc);
	if(trim($desc)){
		?><div class="desc" <?php echo $ShowOnlyDescription ? 'style="clear:none;"':''?>>
		<?php echo $Description;?> 
		</div><?php
	}
	?>
	<div class="cb"> </div>
	</div>
	</div>
	<?php
	//-----------------------------------
}else{


	//1.20 improvement: allows us to make "today" be any day
	if($systemDate && ($n=strtotime($systemDate))!=-1 && ($n=strtotime($systemDate))!=false){
		$systemDate=date('Y-m-d',$n);
	}else{
		$systemDate=date('Y-m-d');
	}
	$systemDateQbks=date('m/d/Y',strtotime($systemDate));
	if($calGetRange && !isset($past))  $past  =date('Y-m-d H:i:s',strtotime(trim($systemDateQbks.'  -7 day')));
	if($calGetRange && !isset($future))$future=date('Y-m-d H:i:s',strtotime(trim($systemDateQbks.' +45 day')));
	
	if(!$day)$day=date('d',strtotime($systemDate));
	if(!$month)$month=date('m',strtotime($systemDate));
	if(!$year)$year=date('Y',strtotime($systemDate));
	//get next and previous objects starting with year
	$nextYear=$year+1;
	$prevYear=$year-1;
	$nextMonth=$month+1; if($nextMonth==13)$nextMonth=1;
	$prevMonth=$month-1; if($prevMonth== 0)$prevMonth=12;
	$nextDay=$day+1;
	if($nextDay>date('t',strtotime("$year-$month-$day")))$nextDay=1;
	//previous day not developed!
	$prevDay=$day-1;
	
	//what day of the week does this month's date start
	$dayStartPosition=date('w',strtotime("$year-$month-01 00:00:00"))+1;
	$daysInMonth=date('t',strtotime("$year-$month-01 00:00:00"));
	$rows= ceil(($dayStartPosition+$daysInMonth-1)/7);
	$cells=$rows*7;
	
	if(trim($calCustomPHP)){
		ob_start();
		eval($calCustomPHP);
		$err=ob_get_contents();
		if($err)exit('error evaluating custom PHP coding: '.$err);
		ob_end_clean();
	}
	
	if($calRewrite) ob_start();
	
	if(!$refreshComponentOnly){
		?><script language="javascript" type="text/javascript">
		if(typeof calNav=='undefined'){
			function calNav(o,y,m){
				if(o.parentNode.className.match(/unavailable/gi)){
					alert('You cannot go here on the calendar');
					return false;
				}
				return true;
			}
		}
		</script><?php
	}
	?><div id="cal"><?php
		ob_start();
		?><div id="calSection"><?php
			?><h1 class="calHeader"><?php echo $calHeader?></h1><?php
			if($calIntro)CMSB('calIntro');
			?><div id="calWidget">
				<?php ob_start();?>
				<div id="calNav" class="cnclr">
				<span id="prevMonth" class="<?php if($calPreventPastNavigation && ($year.str_pad($month,2,'0',STR_PAD_LEFT) <= date('Ym')))echo 'unavailable';?>"><a href="/<?php echo ($thisfolder ? trim($thisfolder,'/').'/':''). ($thissubfolder ? trim($thissubfolder,'/').'/':'').$thispage?>?mode=navMonthEventCalendar&Cal_ID=<?php echo $Cal_ID?$Cal_ID:1?>&year=<?php echo $month==1?$prevYear:$year?>&month=<?php echo $prevMonth?><?php echo $calNavQueryStringParams?>" title="View previous Month" target="w2" onclick="return calNav(this,<?php echo $month==1?$prevYear:$year?>,<?php echo $prevMonth?>);"><img src="<?php echo $calNavLeftImage?>" alt="previous month" align="absbottom" /></a>
				</span>
				<span class="monthYear">
				<?php echo date('F',strtotime($year.'-'.str_pad($month,2,'0',STR_PAD_LEFT).'-01'))?> <?php echo $year?>
				</span>
				<span id="nextMonth" class="<?php if($calPreventFutureNavigation > 0 && $calPreventFutureNavigation<= $year.str_pad($month,2,'0',STR_PAD_LEFT))echo 'unavailable';?>"><a href="/<?php echo ($thisfolder ? trim($thisfolder,'/').'/':''). ($thissubfolder ? trim($thissubfolder,'/').'/':'').$thispage?>?mode=navMonthEventCalendar&Cal_ID=<?php echo $Cal_ID?$Cal_ID:1?>&year=<?php echo $month==12?$nextYear:$year?>&month=<?php echo $nextMonth?><?php echo $calNavQueryStringParams?>" title="View next nonth" target="w2" onclick="return calNav(this,<?php echo $month==12?$nextYear:$year?>,<?php echo $nextMonth?>);"><img src="<?php echo $calNavRightImage?>" alt="next month" align="absbottom" /></a>
				</span>
				</div>
				<?php
				echo $calCalNav=get_contents();
				ob_start();
				?>
				<div id="calGrid"><table class="cal10" cellpadding="0" cellspacing="0"><?php
				$i=0;
				for($i=1; $i<=$cells; $i++){
					if(!(($i-1)%7)){
						if($i>6){
							$j++;
							echo '</tr>';
							echo '<tr row="'.$j.'"'.($cells-$i<=7 ? 'class="bottom"':'').'>';
						}
						if(!$calHeaderPrinted){
							$calHeaderPrinted=true;
							?><thead class="calDays"><tr>
								<?php
								for($k=1; $k<=$calDayShowRange; $k++){
									$idx= fmod($k+$calDayOffset - 1, 7);
									?><th><?php echo $calWeekDayLabels[$idx]?></th><?php
								}
								?>
							</tr>
							</thead><tr><?php
						}
					}
					$thisDay=$i-$dayStartPosition+1;
					$thisDay<1 || $thisDay>date('t',strtotime("$year-$month-$day 00:00:00"))?$thisDay=NULL:'';
			
					//Event cell here =============================================
	
					//handle styles such as background color
					$date="$year-".str_pad($month,2,'0',STR_PAD_LEFT).'-'.str_pad($thisDay,2,'0',STR_PAD_LEFT);
					$class='class="gridSquare';
					$title='title="';
					
					if(is_null($thisDay))$class.=' noday';
					if($thisDay && $calDayModulus){
						if(!$modulusSet){
							if(!$calDayModulusStart)$calDayModulusStart='01-01-'.date('Y',strtotime($systemDate));
							$modulusSet=floor(strtotime($calDayModulusStart)/(3600*24));
						}
						$modulus=floor(strtotime($date)/(3600*24));
						$modulus=$modulus-$modulusSet;
						$modulus=fmod($modulus,$calDayModulus);
						if($modulus<0)$modulus=$calDayModulus+$modulus;
						$modulus+=1;
						$class.=' mod'.$modulus;
					}
					$class.= (date('Ymd',strtotime($systemDate))==str_replace('-','',$date)?' today':'');
					if($calCustomEventsQuery){
						//handled in the function
						$events=$calCustomEventsQuery($date);
						if(count($events))$class.= ' hasEvent';
					}else if($events=q("SELECT * FROM
						cal_events WHERE $calEventWhereClause
						Active=1 AND
						(
						(StartDate='$date' AND (!EndDate OR EndDate IS NULL)) OR (StartDate<='$date' AND EndDate >='$date')
						)", O_ARRAY)){
						$class.= ' hasEvent';
						foreach($events as $n=>$v)unset($events[$n]['Description']);
						$qrs[]=array_merge($qr,$events);
					}
					$class.='"';
					$title.='"';
					//output the cell..
					if(
						/* before a left offset */
						($i-1)%7 < $calDayOffset || 
						/* after range of days */
						$calDayShowRange < ($i-1)%7
					)continue;
	
					if($calCustomGridComponent){
						//handle both gridDay and gridEvents; must take $thisDay and $events as the first two parameters passed
						require($calCustomGridComponent);
					}else{
						?><td <?php echo !is_null($thisDay)?'id="day'.$thisDay.'" ':''?> <?php echo $class?> <?php echo $title?>><?php
						if(is_null($thisDay)){
							//we are in grid but outside day range
							echo '&nbsp;';
						}else{
							if($calGridDisplayFunction){
								//handle both gridDay and gridEvents; must take $thisDay and $events as the first two parameters passed
								$calGridDisplayFunction($thisDay, $events);
	
							}else{
								//------------------ gridDay and usage ------------------
								if($adminMode){
									?><a href="/console/events.php?StartDate=<?php echo $year.'-'.$month.'-'.str_pad($thisDay,2,'0',STR_PAD_LEFT);?>" title="Add a new event for this day" onclick="return ow(this.href,'l1_events','700,700',true);"><img src="/images/i/plusminus-plus.gif" width="11" height="11" alt="new event" /></a> <?php
								}
								if($gridDayUsage=='load' && count($events)){
									?><a title="Click this date for a quick list of events (<?php echo count($events)?> event<?php echo count($events)>1?'s':''?>)" href="#" onclick="window.open('/index_01_exe.php?mode=fetchEventsEventCalendar&year=<?php echo $year?>&month=<?php echo $month?>&day=<?php echo $thisDay?>','w2');return false;"><?php
								}
								?><div class="gridDay"><?php echo $thisDay?></div><?php
								if($gridDayUsage=='load' && count($events)){
									?></a><?php
								}
								//------------------- events and usage --------------------
								if($gridEventUsage=='focus'){
									?><div class="gridEvents"><?php
									if(count($events) && !$calHideGridEvents){
										foreach($events as $n=>$v){
											?><div class="gridEvent<?php if($v['Cal_ID'])echo ' fromCal'.$v['Cal_ID']?>"><?php
											if($gridEventUsage=='focus'){
												?><a class="gridEventLink" title="<?php echo h($v['Description'])?>" href="<?php echo $calPage?>?Events_ID=<?php echo $v['ID']?>" <?php echo $gridEventOnclick?>><?php
											}
											//handle text length eventually
											if($function=$calGridEventOutputFunction && $direction=$calRegisteredFunctions[$calGridEventOutputFunction]){
												if(is_bool($direction)){
													echo $calGridEventOutputFunction($v);
												}else{
													//undeveloped; more complex call
												}
											}else{
												echo $v['Name'];
											}
											if($gridEventUsage=='focus'){
												?></a><?php
											}
											?></div><?php
										}
									}
									?></div><?php
								}
							}
						}
						?></td><?php 
					}
				}
				?></tr></table></div>
				<?php
				echo $calCalGrid=get_contents();
				?>
				
			</div><?php
			unset($events);
	
			//calendar events list below calendar layout
			if(!$hideCalEventListing){
				ob_start();
				?><div id="calEventListing"><?php
				if(isset($events)){
					//events array being passed specifically
					$calEventMethod='array';
					if(!isset($calEventHeaderText))$calEventHeaderText='EVENTS';
	
				}else if($eventSQL){
					//sql query passed
					$events=q($eventSQL, O_ARRAY);
					$calEventMethod='sql';
					if(!isset($calEventHeaderText))$calEventHeaderText='EVENTS';
	
				}else if($thispage=='index_01_exe.php' && $past && $future && $calGetRange && $events=q("SELECT * FROM 
					cal_events WHERE $calEventWhereClause
					Active=1 AND StartDate BETWEEN '$past' AND '$future' 
					ORDER BY StartDate", O_ARRAY)){
					//events touching a specific range
					$calEventMethod='range';
					if(!isset($calEventHeaderText))$calEventHeaderText='EVENTS';
					
				}else if($eventDate && $events=q("SELECT * FROM
					cal_events WHERE $calEventWhereClause
					Active=1 AND
					(
					(StartDate='$eventDate' AND EndDate='0000-00-00') OR (StartDate<='$eventDate' AND EndDate >='$eventDate' AND EndDate!='0000-00-00')
					) ORDER BY StartDate", O_ARRAY)){
					//events touching a specific date
					$calEventMethod='date';
					if(!isset($calEventHeaderText))$calEventHeaderText='EVENTS FOR '.date('m/d/Y',strtotime($eventDate));
	
				}else if($events=q("SELECT * FROM
					cal_events WHERE $calEventWhereClause
					Active=1 AND
					(
					(StartDate='".date('Y-m-d',strtotime($systemDate))."' AND EndDate='0000-00-00') OR (StartDate<='".date('Y-m-d',strtotime($systemDate))."' AND EndDate >='".date('Y-m-d',strtotime($systemDate))."' AND EndDate!='0000-00-00')
					) ORDER BY StartDate", O_ARRAY)){
					//events touching today
					$calEventMethod='today';
					if(!isset($calEventHeaderText))$calEventHeaderText='TODAY';
				/* 2013-05-12 what is this? */
				}else if(($thispage!==trim($calPage,'/')  || $overrideHideMonthQuickEvents) && $month && 
					$events=q("SELECT * FROM cal_events WHERE $calEventWhereClause
					Active=1 AND (
					StartDate>='$year-$month-01' AND StartDate <='$year-$month-".date('t',strtotime("$year-$month-01"))."'
					) ORDER BY StartDate ASC", O_ARRAY)){
					//events in this month
					$calEventMethod='month';
					if(!$eventDate)$eventDate="$year-$month-01";
					if(!isset($calEventHeaderText))$calEventHeaderText='EVENTS FOR '.strtoupper(date('F',strtotime($eventDate)));
	
				}
				if(count($events) || $showCalEventHeaderOnEmpty){
					?><h3 class="calEventHeader"><?php echo $calEventHeaderText; ?></h3><?php
				}
				if(count($events)){
					$i=0;
					foreach($events as $v){
						$i++;
						//handle event output here
						if(!$hideCalEventContainer){ 
							?><div id="calEvent<?php echo $i?>" class="calEvent evtFromCal<?php echo $v['Cal_ID']?>"><?php
						}
						if($calEventFunction){
							$calEventFunction($v);
						}else if($calEventSnippet){
							eval($calEventSnippet);
						}else{
						
						}
						if(!$hideCalEventContainer){ 
							?></div><?php
						}
						//handle break out
						if($maxCalItemListings>0 && $i>$maxCalItemListings){
							$calBreakout=true;
							break;
						}
	
					}
					if(!$hideEventsAllLink && ($calBreakout || $calForceEventAllLink)){
						//------------ view all events ---------------
						?><div id="calEvent0" class="calEventAll">
						<a title="Click here to view complete calendar" href="<?php echo $calPage?>"><?php echo $calEventAllLinkText;?></a>
						
						</div><?php
						//--------------------------------------------
					}
				}else{
					//no event text here
					echo $calEventNoEventsPresent;
				}
				if($adminMode){
					?><a class="_editLink_1" title="Edit this event" href="/console/events.php?Cal_ID=1&cbFunction=refreshList" onclick="return ow(this.href,'l1_events','700,700',true);"><img src="/images/i/plusminus-plus.gif" width="11" height="11" alt="edit" />&nbsp;
					New event</a><?php
				}
				?></div><?php
				echo $calCalEventListing=get_contents();
			}
		?></div><?php
		echo $calCalSection=get_contents();
		?>
	</div><?php
	if($calRewrite)	$standardLayout=get_contents();
	//-----------------------------end old component location-----------------------------------

	echo $standardLayout;
	if($mode=='navMonthEventCalendar' || $mode=='fetchEventsEventCalendar'){
		//2013-05-04: sick of AEG googlebots bugging me about 1957!
		if($calRangeYears){
			$a=strtotime($year.'-'.str_pad($month,2,'0',STR_PAD_LEFT).'-01');
			$b=time();
			$c=abs($a-$b)/(365*24*3600);
			if($c>$calRangeYears)error_alert('This date is outside of calendar range');
		}
		if($mode=='fetchEventsEventCalendar')$eventDate="$year-$month-$day";
		?><script language="javascript" type="text/javascript">
		<?php if(!$calCustomReplaceRegions){ ?>
		window.parent.g('cal').innerHTML=document.getElementById('cal').innerHTML;
		<?php }else{ foreach($calCustomReplaceRegions as $v){ ?>
		window.parent.g('<?php echo $v?>').innerHTML=document.getElementById('<?php echo $v?>').innerHTML;
		<?php }} ?>
		</script><?php	
		$assumeErrorState=false;
		exit;
	}
}
//=========================================== end old code   ================================



$$calMainBlock=ob_get_contents();
ob_end_clean();


}//---------------- end i break loop ---------------
compend:
?>