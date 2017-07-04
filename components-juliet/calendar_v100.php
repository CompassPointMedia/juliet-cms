<?php
/* name=Events Calendar; description=4th component file created; */

//description of what the component file does here

/*
2012-03-15: created component file

*/



$handle='calendar';
//2012-03-10: pull parameters for this component file - note that this is in gen_nodes_settings.Settings vs. gen_templates_blocks.Parameters
if($Parameters=q("SELECT Settings FROM gen_nodes_settings WHERE Nodes_ID='".($_thisnode_ ? $_thisnode_ : $thisnode) ."'", O_VALUE)){
	$Parameters=unserialize(base64_decode($Parameters));
	if($Parameters[$handle])$pJ['componentFiles'][$handle]=$Parameters[$handle];
	/* nodes include: forms; data; format.  forms is unused right now, and data[default] means "across all pages" and is the only part developed */
}
//default variables
if(!$doSomething)$doSomething=pJ_getdata('doSomething',true);

//default CSS
//this is a new method of having default CSS- should be modified quickly as this default css is going to evolve
if(false){ ?><div style="display:none;"><style type="text/css"><?php } 
ob_start();?>
#cal{
	}
#calHeader{
	display:none;
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
#calHeader{
	padding:5px 0px 2px 12px;
	font-weight:400;
	font-size:104%;
	margin-bottom:7px;
	margin-top:10px;
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
#evt{
	margin-top:0px;
	padding-top:0px;
	}
#evt .event{
	border-bottom:1px dotted #272727;
	margin-top:20px;
	padding:15px 0px;
	}
#evt .payonline{
	float:right;
	}
#evt .desc{
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
<?php if(false){ ?></style></div><?php }
$calDefaultCalCSS=trim(ob_get_contents());
ob_end_clean();
$pJLocalCSS[$handle]=pJ_getdata('calDefaultCalCSS',$calDefaultCalCSS);

//for local css links in head of document
if(false)$pJLocalCSSLinks[$handle]='/site-local/somefile.css';


//functions needed for this component
if(!function_exists('get_contents')){
function get_contents(){
	/* 2008-06-30 - for handling output buffering 
	2009-11-29 - made an "official" function in a_f; it was in 5 files.  Only in comp_tabs v2.00 (+?) the end logic is NOT if(beginnextbuffer) then ob_start() ELSE return gcontents.out - instead the logic is if(beginnextbuffer) then ob_start(); return gcontents.out PERIOD
	HOWEVER, beginnextbuffer is never flagged in comp_tabs so I have no fear of back-compat problems
	this function will return output and can optionally start the next buffer.
	GOTCHA! since this is a function, we must ob_start() before we return the contents.  Therefore, if you store the value returned as a variable, thats great, but if you wish to echo it, you are already in the next buffer.  So you cannot do a rewrite as done in cal widget and etc.
	*/
	$cmds=array('striptabs','beginnextbuffer','trim');
	global $gcontents;
	unset($gcontents);
	if($a=func_get_args()){
		foreach($a as $v){
			if(in_array(strtolower($v),$cmds)){
				$v=strtolower($v);
				$$v=true;
			}
		}
	}
	$gcontents['out']=ob_get_contents();
	if($trim)$gcontents['out']=trim($gcontents['out'])."\n";
	ob_end_clean();
	if($striptabs)$gcontents['out']=str_replace("\t",'',$gcontents['out']);
	if($beginnextbuffer){
		ob_start();
	}else{
		return $gcontents['out'];
	}
}
}
if(!function_exists('event_write')){
function event_write($v){
	global $pageHandles,$qr,$fl,$ln,$developerEmail,$fromHdrBugs,$adminMode;
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
	?><a title="See details about this event" href="<?php echo $pageHandles['calendarFocus'];?>?Events_ID=<?php echo $ID?>"><?php echo $Name?></a></div>
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
	global $pageHandles, $adminMode, $year, $month, $qr, $qx, $fl, $ln, $developerEmail, $fromHdrBugs, $Cal_ID;
	?>
	<div class="gridDay"><?php 
	if($adminMode){
		?><a class="_editLink_1" href="/console/events.php?Cal_ID=<?php echo $Cal_ID?$Cal_ID:1?>&StartDate=<?php echo $year.'-'.$month.'-'.str_pad($day,2,'0',STR_PAD_LEFT);?>" title="Add a new event for this day" onclick="return ow(this.href,'l1_events','700,700',true);"><img src="/images/i/plusminus-plus.gif" width="11" height="11" alt="new event" /></a> <?php
	}
	echo $day?></div>
	<div class="gridEvents"><?php
	if(count($events))
	foreach($events as $n=>$v){
		?><div class="gridEvent"><a class="gridEventLink" title="<?php echo h($v['Description'])?>" href="<?php echo $pageHandles['calendarFocus']?>?Events_ID=<?php echo $v['ID']?>"><?php
		//handle text length eventually
		echo $v['Name'];
		?></a></div><?php
	}
	?></div>
	<?php
}
}


for($__i__=1; $__i__<=1; $__i__++){ //---------------- begin i break loop ---------------

if($mode=='componentEditor'){
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
}else if($formNode=='default' /* ok this is something many component files will contain */){
	?><p>Calendar Basic Settings</p>
	<textarea name="default[calDefaultCalCSS]" id="calDefaultCalCSS" onchange="dChge(this);" rows="15" cols="60" style="padding:0px 0px 0px 10px;"><?php
	echo h($calDefaultCalCSS);
	?></textarea>
	
	
	<?php
}else if($formNode=='additional'){
	?><p>Additional Settings Form Here</p><?php
}


//------------- sample region $sampleBlock ---------
$block=pJ_getdata('calContentRegion','mainRegionCenterContent');
ob_start();

pJ_call_edit(array(
	'level'=>ADMIN_MODE_DESIGNER,
	'location'=>'JULIET_COMPONENT_ROOT',
	'file'=>end(explode('/',__FILE__)),
	'thisnode'=>$thisnode,
));

if(!function_exists('js_email_encryptor'))require($FUNCTION_ROOT.'/function_js_email_encryptor_v100.php');
if($Events_ID){
	$showCalWidget=true;
	if(!($cal=q("SELECT * FROM cal_events WHERE Active=1 AND ResourceType IS NOT NULL /* public calendar */ AND ID='$Events_ID'", O_ROW))){
		header('Location: /');
		exit;
	}
	//-----------------------------------
	?>
	<div id="evt"><?php
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
	?><div class="event" style="border-bottom:1px dotted #272727;margin-top:0px; padding-top:0px;">
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
				<?php if(false && $Address){ ?>
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
	</div>
	<?php
	//-----------------------------------
}else{
	$calGridDisplayFunction='trainingCalendarGrid';
	function trainingCalendarGrid($thisDay, $events){
		global $gridEventUsage, $pageHandles, $gridEventOnclick, $gridDayUsage, $calAccesToken, $year, $month, $day, $date, $gtParent, $adminMode;
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
					?><a class="gridEventLink" title="<?php echo str_replace('"','&quot;',strip_tags($v['Description']))?>" href="<?php echo $pageHandles['calendarFocus']?>?Events_ID=<?php echo $v['ID']?>" <?php echo $gridEventOnclick?>><?php
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
	$pageHandles['calRequestURL']=$thispage;
	$pageHandles['calendarFocus']=$thispage;
	$calCustomEventsQuery='calCustomEventsQuery';
	function calCustomEventsQuery($date){
		global $calEventWhereClause;
		return q("SELECT cal_events.*
		FROM cal_events 
		WHERE $calEventWhereClause cal_events.Active=1 AND
		(
		(StartDate='$date' AND (!EndDate OR EndDate IS NULL)) OR (StartDate<='$date' AND EndDate >='$date')
		)", O_ARRAY);
	}
	$cals=q("SELECT * FROM cal_cal ORDER BY ID", O_ARRAY);
	$calPreventPastNavigation=false;
	$hideCalEventListing=true;
	$calRewrite=true;
	$gridEventOnclick='';
	
	CMSB('calIntro');
	
	//-----------------------------begin old component location ----------------------------------

	
	//1.20 improvement: allows us to make "today" be any day
	if($systemDate && ($n=strtotime($systemDate))!=-1 && ($n=strtotime($systemDate))!=false){
		$systemDate=date('Y-m-d',$n);
	}else{
		$systemDate=date('Y-m-d');
	}
	$systemDateQbks=date('m/d/Y',strtotime($systemDate));
	
	//cal
	if(!$pageHandles['calendarList'])$pageHandles['calendarList']='/Event-Calendar.php';
	if(!$pageHandles['calendarFocus'])$pageHandles['calendarFocus']='/Event-Calendar-Item.php';
	if(!$pageHandles['calRequestURL'])$pageHandles['calRequestURL']='/index_01_exe.php';
	
	//images
	if(!$calNavLeftImage)$calNavLeftImage='/images/i/arrows/2_white_left.png';
	if(!$calNavRightImage)$calNavRightImage='/images/i/arrows/2_white_right.png';
	
	if(!isset($calRewrite))$calRewrite=false;
	if(!isset($maxCalItemListings))$maxCalItemListings=5;
	if(!isset($calHeaderText))$calHeaderText='Calendar';
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
	
	
	//$calGridDisplayFunction($thisDay, $events) = cal_grid_display - can be used if desired
	
	
	if(!isset($gridDayUsage))$gridDayUsage='load';
	if(!isset($gridEventUsage))$gridEventUsage='focus';
	
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
			//header - "Calendar" for example
			?><div id="calHeader" class="chclr"><?php echo $calHeaderText?></div><?php
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
									?><a title="Click this date for a quick list of events (<?php echo count($events)?> event<?php echo count($events)>1?'s':''?>)" href="#" onclick="window.open('<?php echo $pageHandles['calRequestURL']?>?mode=fetchEventsEventCalendar&year=<?php echo $year?>&month=<?php echo $month?>&day=<?php echo $thisDay?>','w2');return false;"><?php
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
												?><a class="gridEventLink" title="<?php echo h($v['Description'])?>" href="<?php echo $pageHandles['calendarFocus']?>?Events_ID=<?php echo $v['ID']?>" <?php echo $gridEventOnclick?>><?php
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
	
				}else if(($thispage!==trim($pageHandles['calendarList'],'/')  || $overrideHideMonthQuickEvents) && $month && 
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
						<a title="Click here to view complete calendar" href="<?php echo $pageHandles['calendarList']?>"><?php echo $calEventAllLinkText;?></a>
						
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
	</div>
	<?php
	if($calRewrite)	$standardLayout=get_contents();
	//-----------------------------end old component location-----------------------------------

	echo $standardLayout;
	if($mode=='navMonthEventCalendar' || $mode=='fetchEventsEventCalendar'){
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


$$block=ob_get_contents();
ob_end_clean();







}//---------------- end i break loop ---------------
?>