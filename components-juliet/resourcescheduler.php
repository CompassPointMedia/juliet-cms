<?php
/* name=Resource Scheduler */
/*
todo:
	DONE	make sure the console system is completely new
	DONE	pasword protect /resources/fieldpics - with master UN/PW
	DONE	get users in system
	DONE	add Season Pass as a permission and give to everyone
	DONE	wro_inparty
	DONE	test some basic bookings
	DONE	show # people booked on calendar
	DONE	lock out non-season passers
	DONE	remove hunter cap
	DONE	figure a way to set schedule for a field
	DONE	figure a way to black out a day
	DONE	description old-style for now
	DONE	edited OK
	DONE	van ormy showing central
	DONE	add a field in place
	DONE	lock out bookings for over max
	
	when I go to members, redirects to login - need a MESSAGE
	remove address requirement
	
	turn edit description into a CMSB with link to THAT CELL or text field, or even better to cms_sections so that we have a history
	ability to create a "season"
	
	cannot change Resource to "Field" and make stick

	chris to open a day and edit/delete/add as admin
	members to cancel booking - include how to in email - link "click here"
	automated report for the day - to chris and hunters
	figure a way to block reserve certain slots
	QUESTION: can I make this over-arching? modular etc.
		start making settings such as how to join the season range
	way to define a "season".

	

	
Changelog:
2013-08-25: roughed out where you can book a hunt/field
2013-08-23: whoo hoo! a root level component

	
*/
$handle='resourcescheduler';
$componentVersion=1.0;
$tabVersion=3;

if(!function_exists('get_image'))require($FUNCTION_ROOT.'/function_get_image_v230.php');

//2012-03-10: pull parameters for this component file - note that this is in gen_nodes_settings.Settings vs. gen_templates_blocks.Parameters
if($Parameters=q("SELECT s.Settings FROM gen_nodes n, gen_nodes_settings s WHERE n.ID=s.Nodes_ID AND n.Name='Members' AND n.Type='Object'", O_VALUE)){
    $Parameters=unserialize(base64_decode($Parameters));
    if($Parameters[$handle])$pJ['componentFiles'][$handle]=$Parameters[$handle];
    /* nodes include: forms; data; format.  forms is unused right now, and data[default] means "across all pages" and is the only part developed */
}


//example default variables
$categoryPath=pJ_getdata('categoryPath','category');
$resourceschedulerSeasonStart=pJ_getdata('resourceschedulerSeasonStart','2015-08-01');
$resourceschedulerSeasonEnd=pJ_getdata('resourceschedulerSeasonEnd','2016-02-28');
$resourceschedulerMaxBookings=pJ_getdata('resourceschedulerMaxBookings',2);

//settings for calWidget
$calNavURL='/index_01_exe.php?mode=componentControls&submode=refreshCal&file='.end(explode('/',__FILE__)).'&location=JULIET_COMPONENT_ROOT';
$calHideEventListing=true;
$calOverrideDefaultCSS=true;
$calPrevMonthWord='Prev.';
$calNextMonthWord='Next';
$calGridDisplayFunction='booking';


// This was epic - couldn't get into January..?
function booking($thisDay,$nothing){
    global $class,$title,$calAccessToken,$year,$month,$_events,$thispage,$Cal_ID;

    if($thisDay){
        $date=$year.'-'.str_pad($month,2,'0',STR_PAD_LEFT).'-'.str_pad($thisDay,2,'0',STR_PAD_LEFT);
        $e=($_events[$date]);
    }
    if(false){ ?><table><tr><?php }
    ?><td <?php echo !is_null($thisDay)?'id="day'.$thisDay.'" ':''?> class="gridSquare<?php if(!$e)echo ' na';?><?php if(is_null($thisDay))echo ' noday'; ?>"><?php

    if(is_null($thisDay)){
        //we are in grid but outside day range
        echo '&nbsp;';
    }else{
        if($adminMode){
            ?><a href="#"><?php
        }
        ?><div class="gridDay"><?php echo $thisDay?></div><?php
        if($adminMode){
            ?></a><?php
        }
        if($e){
            /*
            if(_MorningBooked
            _AfternoonBooked
            _AllBooked
            */
            if($e['_MorningBooked'] + $e['_AfternoonBooked'] + $e['_AllBooked']){
                $morning=q("SELECT SUM(WRO_InParty) FROM cal_events WHERE Cal_ID=$Cal_ID AND StartDate='".$date."' AND StartTime='07:00' AND EndTime='12:00' GROUP BY Cal_ID", O_VALUE);
                $afternoon=q("SELECT SUM(WRO_InParty) FROM cal_events WHERE Cal_ID=$Cal_ID AND StartDate='".$date."' AND StartTime='12:00' AND EndTime='17:00' GROUP BY Cal_ID", O_VALUE);
                $all=q("SELECT SUM(WRO_InParty) FROM cal_events WHERE Cal_ID=$Cal_ID AND StartDate='".$date."' AND StartTime='07:00' AND EndTime='17:00' GROUP BY Cal_ID", O_VALUE);
                $morningDisabled=($morning + $all >= $e['WRO_Max']);
                $afternoonDisabled=($afternoon + $all >= $e['WRO_Max']);
                $allDisabled=(max($morningDisabled,$afternoonDisabled) >= $e['WRO_Max']);
            }
            /** no longer used - probably same logic as found next for excluding mornings!
            if($e['Identifier']=='2014masterson' &&
                ($e['StartDate']=='2014-09-06' || $e['StartDate']=='2014-09-07' || $e['StartDate']=='2014-09-13' || $e['StartDate']=='2014-09-14'))$morningDisabled=$allDisabled=true;
            */
            if($e['WRO_Region'] == 'South' && (
                $e['StartDate'] == '2019-09-01' || $e['StartDate'] == '2019-09-02' || $e['StartDate'] == '2019-09-07' || $e['StartDate'] == '2019-09-08'
            ))$morningDisabled = $allDisabled = true;
            ?>
            <a <?php if($morningDisabled){ ?>class="disabled" onclick="alert('This time slot is booked full or not available, sorry!');return false;"<?php } ?> href="/members/<?php echo $thispage;?>/book?time=morning&month=<?php echo $month;?>&day=<?php echo $thisDay;?>&year=<?php echo $year;?>" title="click here to book this field for the morning">Morning<?php
                if($morning)echo ' <span title="Number of people hunting this field in the morning">('.$morning.')</span>';
                ?></a><br />
            <a <?php if($allDisabled){ ?>class="disabled" onclick="alert('This time slot is booked full or not available, sorry!');return false;"<?php } ?> href="/members/<?php echo $thispage;?>/book?time=all&month=<?php echo $month;?>&day=<?php echo $thisDay;?>&year=<?php echo $year;?>" title="click here to book this field for the whole day">All Day<?php
                if($all)echo ' <span title="Number of people hunting this field all day">('.$all.')</span>';
                ?></a><br />
            <a <?php if($afternoonDisabled){ ?>class="disabled" onclick="alert('This time slot is booked full or not available, sorry!');return false;"<?php } ?> href="/members/<?php echo $thispage;?>/book?time=afternoon&month=<?php echo $month;?>&day=<?php echo $thisDay;?>&year=<?php echo $year;?>" title="click here to book this field for the afternoon">Afternoon<?php
                if($afternoon)echo ' <span title="Number of people hunting this field in the afternoon">('.$afternoon.')</span>';
                ?></a><br />
        <?php
        }else{
            ?><div class="natext" style="font-style:italic;opacity:.75;">not available</div><?php
        }
    }
    ?></td><?php
    if(false){ ?></tr></table><?php }
}
function events_range($options=array()){
    /*options:
    comparison: Identifier='field' or ID=1 for _v_cal_bookings
    */
    global $qr,$developerEmail,$fromHdrBugs,$MASTER_USERNAME,$_events,$record,$range;
    extract($options);
    //2013-08-26: note, multiple range calendars attached are an unknown
    if(!($_events=q("SELECT b.StartDate, b.* FROM _v_cal_bookings b WHERE $comparison ORDER BY StartDate", O_ARRAY_ASSOC))){
        mail($developerEmail, 'Error in '.$MASTER_USERNAME.':'.end(explode('/',__FILE__)).', line '.__LINE__,get_globals($err='cannot field field (Identifier)'),$fromHdrBugs);
        header('Location: /');
        exit;
    }
    //get a range representation
    $i=1;
    $j=0;
    $daysec=24*3600;
    foreach($_events as $StartDate=>$v){
        $j++;
        if($j>1){
            unset($_events[$StartDate]['WRO_Schedule'],$_events[$StartDate]['WRO_DaysOn'],$_events[$StartDate]['WRO_DaysOff']);
        }
        if($j==1)$record=$v;
        /*if(!$prev){
            //no previous date
            $range[$i]['start']=$StartDate;
        }else if(strtotime($StartDate) > strtotime($prev)+$daysec){
            //this date is more than one date ahead of previous date
            $range[$i]['end']=$prev;
            $range[$i+1]['start']=$StartDate;
            $i++;
        }else{
            //idle?
        }
        $prev=$StartDate;*/
    }
    //$range[$i]['end']=$StartDate;

    //// SO THIS IS WAY TOO COMPLICATED
    //// AND ITS RIDICULOUS TO THINK OF PUTTING AN ENTRY IN THE DATABASE FOR EVERY SINGLE AVAILABLE DAY FOR EVERY CALENDAR GARSH
    //// THATS WHEN WE HARDCODE IT - life is much better this way.
    // just don't ask how long it took to sort thru this tangled web to figure out how to even hardcode it
    // call me cynical

    if ($record['WRO_Region'] == 'Central'){
        // central
        $range[1]['start'] = '2015-09-01';
        $range[1]['end'] = '2015-10-25';
        $range[2]['start'] = '2015-12-18';
        $range[2]['end'] = '2016-01-01';
    } else if ($record['WRO_Region'] == 'South'){
        // south
        $range[1]['start'] = '2015-09-18';
        $range[1]['end'] = '2015-10-21';
        $range[2]['start'] = '2015-12-18';
        $range[2]['end'] = '2016-01-22';
    } else if ($record['WRO_Region'] == 'Special White Wing'){
        // special
        $range[1]['start'] = '2015-09-05';
        $range[1]['end'] = '2015-09-06';
        $range[2]['start'] = '2015-09-12';
        $range[2]['end'] = '2015-09-13';
        $range[3]['start'] = '2015-09-18';
        $range[3]['end'] = '2015-10-21';
        $range[4]['start'] = '2015-12-18';
        $range[4]['end'] = '2016-01-18';
    } else {
        // go home
        error_alert("No date range defined for calendar: " . $record['Identifier']);
    }

    foreach ($range as $r){
        $rStart = strtotime($r['start']);
        $rEnd = strtotime($r['end']);
        while($rStart <= $rEnd){
            $formattedStart = date('Y-m-d', $rStart);
            $_events[date($formattedStart)]=array(
                'StartDate'=>$rStart,
                'ID'=>$record['ID'],
                'WRO_Max'=>$record['WRO_Max'],
                'WRO_Region'=>$record['WRO_Region'],
                'Description'=>$record['Description'],
                'WRO_Category'=>$record['WRO_Category'],
                'Identifier'=>$record['Identifier'],
                'Name'=>$record['Name'],
                '_MorningBooked' => q("SELECT SUM(WRO_InParty) FROM cal_events WHERE Cal_ID='".$record['ID']."' AND StartDate='".$formattedStart."' AND StartTime='07:00' AND EndTime='12:00' GROUP BY ID",O_VALUE),
                '_AfternoonBooked' => q("SELECT SUM(WRO_InParty) FROM cal_events WHERE Cal_ID='".$record['ID']."' AND StartDate='".$formattedStart."' AND StartTime='12:00' AND EndTime='17:00' GROUP BY ID",O_VALUE),
                '_AllBooked' => q("SELECT SUM(WRO_InParty) FROM cal_events WHERE Cal_ID='".$record['ID']."' AND StartDate='".$formattedStart."' AND StartTime='07:00' AND EndTime='17:00' GROUP BY ID",O_VALUE),
            );

            $rStart+=$daysec;
        }
    }

    /*if($daysOn=trim($record['WRO_DaysOn'])){
        $daysOn=preg_split('/\s+/',$daysOn);
        foreach($daysOn as $n=>$v){
            if($b=strtotime($v))$daysOn[date('Y-m-d',$b)]=date('Y-m-d',$b);
            unset($daysOn[$n]);
        }
        if(!empty($daysOn))
        foreach($daysOn as $n){
            if(!$_events[$n]){
                $_events[$n]=array(
                    'StartDate'=>$n,
                    'ID'=>$record['ID'],
                    'WRO_Max'=>$record['WRO_Max'],
                    'WRO_Region'=>$record['WRO_Region'],
                    'Description'=>$record['Description'],
                    'WRO_Category'=>$record['WRO_Category'],
                    'Identifier'=>$record['Identifier'],
                    'Name'=>$record['Name'],
                    '_MorningBooked' => q("SELECT SUM(WRO_InParty) FROM cal_events WHERE Cal_ID='".$record['ID']."' AND StartDate='".$n."' AND StartTime='07:00' AND EndTime='12:00' GROUP BY ID",O_VALUE),
                    '_AfternoonBooked' => q("SELECT SUM(WRO_InParty) FROM cal_events WHERE Cal_ID='".$record['ID']."' AND StartDate='".$n."' AND StartTime='12:00' AND EndTime='17:00' GROUP BY ID",O_VALUE),
                    '_AllBooked' => q("SELECT SUM(WRO_InParty) FROM cal_events WHERE Cal_ID='".$record['ID']."' AND StartDate='".$n."' AND StartTime='07:00' AND EndTime='17:00' GROUP BY ID",O_VALUE),
                );
            }
        }
    }*/
    if($daysOff=trim($record['WRO_DaysOff'])){
        $daysOff=preg_split('/\s+/',$daysOff);
        foreach($daysOff as $n=>$v){
            if($b=strtotime($v))$daysOff[date('Y-m-d',$b)]=date('Y-m-d',$b);
            unset($daysOff[$n]);
        }
        if(!empty($daysOff))
            foreach($daysOff as $v)unset($_events[$v]);
    }
    //------------- now we are going to unset days not on schedule, if declared, except for DaysOn ------------------
    if(strlen($record['WRO_Schedule'])){
        $schedule=explode(',',$record['WRO_Schedule']);
        foreach($_events as $StartDate=>$v){
            if(!in_array(date('w',strtotime($StartDate)),$schedule) && !$daysOn[$StartDate])unset($_events[$StartDate]);
        }
    }
}

//default CSS
get_contents_enhanced('start'); ?>
<?php if(false){ ?><style type="text/css"><?php } ?>
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

    #newsHeader{
        padding:5px 0px 2px 12px;
        font-weight:400;
        font-size:104%;
        margin-bottom:7px;
        margin-top:10px;
    }
    #newsListing{
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
    #bookForm{
        border:1px solid #B47B29;
        padding:15px;
        border-radius:10px;
        margin:15px 0px;
    }
    <?php if(false){ ?></style><?php } ?>
<?php
$n=get_contents_enhanced('noecho,cxlnextbuffer');
$pJLocalCSS[$handle.'-baseCSS']=
$resourceschedulerCSS=pJ_getdata('resourceschedulerCSS',$n);
$resourceObjectName=pJ_getdata('resourceObjectName','Resource');



//------------- GENERAL USERS SECTION -------------
for($__j__=1; $__j__<=1; $__j__++) { //---------------- begin i break loop ---------------
if(!$_SESSION['cnx'][$acct]['primaryKeyValue'])break; //only members here
    if($mode=='componentControls') {
        //public component controls
        if ($submode == 'deleteBookingClient') {
            q("DELETE e.*
    FROM addr_contacts c JOIN finan_ClientsContacts cc ON c.ID=cc.Contacts_ID AND cc.Type='Primary' JOIN cal_events e ON cc.Clients_ID=e.Clients_ID WHERE cc.Contacts_ID='".$_SESSION['cnx'][$acct]['primaryKeyValue']."' AND e.ID='$Events_ID'");
            prn($qr);
            ?><script>window.parent.g('r_<?php echo $Events_ID;?>').style.display='none';</script><?php
            $assumeErrorState=false;
            exit;
        }
    }

    if($thispage!=='edit')break;


    ob_start();
    $data=q("SELECT
    e.StartDate AS date, e.Name as name, e.ID AS Events_ID,
    WRO_GuestName AS GuestName, WRO_GuestPhone as GuestPhone,
    cal.Identifier
    FROM addr_contacts c
    JOIN finan_ClientsContacts cc ON c.ID=cc.Contacts_ID AND cc.Type='Primary'
    JOIN cal_events e ON cc.Clients_ID=e.Clients_ID
    JOIN cal_cal cal ON e.Cal_ID=cal.ID
    WHERE cc.Contacts_ID='".$_SESSION['cnx'][$acct]['primaryKeyValue']."' ORDER BY StartDate", O_ARRAY);
    ?>
    <div>
        <br><br>
        <h2>Your Bookings</h2>

    <table>
        <thead><tr>
            <th>&nbsp;</th>
            <th>Date</th>
            <th>Field</th>
            <th>Guest</th>
            <th>Phone#</th>
        </tr></thead>
        <tbody>
        <?php
        if($data){
            foreach($data as $n=>$v){
                extract($v);
                ?><tr id="r_<?php echo $v['Events_ID'];?>">
                    <td><a href="">[<a href="/index_01_exe.php?mode=componentControls&submode=deleteBookingClient&Events_ID=<?php echo $v['Events_ID'];?>&location=JULIET_COMPONENT_ROOT&file=<?php echo end(explode('/',__FILE__));?>" target="w2" onclick="return confirm('Are you sure you want to delete this booking?');">delete</a>]</a></td>
                    <td><?php echo date('n/j/Y',strtotime($date));?></td>
                    <td><a href="/members/<?php echo $Identifier;?>"><?php echo $name;?></a></td>
                    <?php if($GuestName){ ?>
                    <td><?php echo $GuestName;?></td>
                    <td><?php echo $GuestPhone;?></td>
                    <?php }else{ ?>
                    <td colspan="2"><em >(none)</em></td>
                    <?php } ?>
                </tr><?php
            }
        }else{
            ?><td colspan="100%"><span class="gray">You do not have any booking currently. <a href="/memers">Click here to get started</a>.</span></td><?php
        }
        ?>
        </tbody>
    </table>
    </div>
    <?php
    $mainRegionCenterContent=ob_get_contents();
    ob_end_clean();
    goto compend;
} //----------- end j break loop


for($__i__=1; $__i__<=1; $__i__++){ //---------------- begin i break loop ---------------


//------------- ADMIN SECTION ($hasAdmin) ---------
//handle logout or timeout
if(!$hasAdmin && !$_SESSION['cnx'][$acct]['accesses'][6]){
	if($mode=='componentControls')error_alert('Your session has timed out.  You will need to sign in again');
	exit(header('Location: /cgi/login?src='.urlencode(($thisfolder?'/'.$thisfolder:'').($thissubfolder?'/'.$thissubfolder:'').($thispage?'/'.$thispage:'').($QUERY_STRING?'?'.$QUERY_STRING:''))));
}




if($formNode){
	?><script language="javascript" type="text/javascript">
        /*
        $(document).ready(function(){
            $('#something').click(function(){

            });
        });
        */
	</script><?php
}

if($mode=='componentControls'){
	//public component controls
	if($submode=='refreshCal'){
		if(!$_SESSION['identity'])error_alert('You are timed out. Unable to navigate calendar');
		$mode='navMonthEventCalendar';
		$calNavURL.='&calPreventFutureNavigation='.$calPreventFutureNavigation.'&calPreventPastNavigation='.$calPreventPastNavigation;
		events_range(array(
			'comparison'=>"ID='$Cal_ID'",
		));
		$thisfolder='members';
		$thissubfolder='';
		$thispage=q("SELECT Identifier FROM cal_cal WHERE ID=$Cal_ID", O_VALUE);
		require($MASTER_COMPONENT_ROOT.'/calWidget_v130.php');
		break;
	}else if($submode=='book'){
		if(!$Cal_ID)error_alert('Field data not passed');
		$record=q("SELECT * FROM cal_cal WHERE ID=$Cal_ID", O_ROW);
		if(!$_SESSION['identity'])error_alert('You are timed out. Unable to book this hunt.  Click "sign out" at top, and then sign in again!');
		if($GuestName && !$GuestPhone)error_alert('Enter a phone number for your guest');
		if(!$IAgree)error_alert('You must check the "I Agree" to confirm you and your guest will bag all trash and hunt responsibly');


		if(is_array($GuestName)){
		    $guest = []; $phone = [];
		    foreach($GuestName as $n => $v){
		        if(!trim($v)) continue;
		        if(strlen(preg_replace('/[^0-9]+/','',$GuestPhone[$n]))<10)
		            error_alert('Please enter a valid phone number (with area code) for guest #' . ($n + 1));
		        $guest[] = $v;
		        $phone[] = $GuestPhone[$n];
            }
            $GuestName = implode(', ', $guest);
            $GuestPhone = implode(', ', $phone);
        }else{
            if(trim($GuestName) && strlen(preg_replace('/[^0-9]+/','',$GuestPhone))<10)error_alert('Please enter a valid phone number (with area code) for your guest');
        }

		/*inparty only available by administrators*/
		$WRO_InParty=($hasAdmin ? $WRO_InParty : ($GuestName?2:1));

		if($hasAdmin){
			extract(q("SELECT CONCAT(c.FirstName,' ',c.LastName) AS ContactName, c.Email AS ContactEmail FROM addr_contacts c JOIN finan_ClientsContacts cc ON c.ID=cc.Contacts_ID AND cc.Type='Primary' JOIN finan_clients cl ON cc.Clients_ID=cl.ID WHERE cl.ID=$Clients_ID", O_ROW));
		}else{
			$ContactName=$_SESSION['firstName'].' '.$_SESSION['lastName'];
			$ContactEmail=$_SESSION['email'];
			$Clients_ID=$_SESSION['cnx'][$acct]['defaultClients_ID'];
		}
		//rules: 1. no concurrent booking 2. not more than 5 from today on 3. above capacity
		$StartTime=($time=='afternoon'?'12:00':'07:00');
		$EndTime=($time=='morning'?'12:00':'17:00');
        if(q("SELECT COUNT(*) FROM cal_events WHERE Clients_ID=$Clients_ID AND StartDate='$date' AND StartTime<'$EndTime' AND EndTime>'$StartTime'", O_VALUE))error_alert('You cannot book this time; you already have an overlapping booking in this or another field!', $hasAdmin);
		if(q("SELECT COUNT(*) FROM cal_events WHERE Clients_ID=$Clients_ID AND StartDate>=CURDATE()", O_VALUE)>=$resourceschedulerMaxBookings)error_alert('You cannot book more than '.$resourceschedulerMaxBookings.' slots at any given time', $hasAdmin);
		$morning=q("SELECT SUM(WRO_InParty) FROM cal_events WHERE Cal_ID=$Cal_ID AND StartDate='$date' AND EndTime='12:00:00'", O_VALUE);
		$afternoon=q("SELECT SUM(WRO_InParty) FROM cal_events WHERE Cal_ID=$Cal_ID AND StartDate='$date' AND StartTime='12:00:00'", O_VALUE);
		$all=q("SELECT SUM(WRO_InParty) FROM cal_events WHERE Cal_ID=$Cal_ID AND StartDate='$date' AND StartTime='07:00:00' AND EndTime='17:00:00'", O_VALUE);
		if($time=='morning'){
			$morning+=$WRO_InParty;
		}else if($time=='afternoon'){
			$afternoon+=$WRO_InParty;
		}else if($time=='all'){
			$all+=$WRO_InParty;
		}
		if(max($morning,$afternoon,$all,$morning+$all,$afternoon+$all)>$record['WRO_Max']) error_alert(
		    $hasAdmin ?
            "This booking put the field over the max number of hunters, but since you are an admin it has been booked anyway" :
            'You cannot book this slot for the field; it would put the field over the max number of hunters.  If you need assistance please contact Winged Republic at 512-557-2945'
		, $hasAdmin
        );

		$key=substr(md5(time().rand(1,1000000)),0,30);
		$fl = __FILE__;
		$fl = explode('/', $fl);
		$fl = end($fl);
		$sql = "INSERT INTO cal_events SET 
		Name='Booked hunt for ".addslashes($record['Name'])."',
		ContactName='".addslashes($ContactName)."',
		ContactEmail='".addslashes($ContactEmail)."',
		Clients_ID='$Clients_ID',
		StartDate='$date',
		StartTime='".($time=='afternoon'?'12:00':'07:00')."',
		EndTime='".($time=='morning'?'12:00':'17:00')."',
		CreateDate=NOW(),
		Creator='".sun()."',
		ResourceType=1,
		ResourceToken='$key',
		SessionKey='".$PHPSESSID."',
		Cal_ID=$Cal_ID,
		Description='Online Booking by ".$fl."',
		WRO_GuestName='$GuestName',
		WRO_GuestPhone='$GuestPhone',
		WRO_Confirmation='".
            'IP:'.$REMOTE_ADDR
            ."',
		WRO_InParty='".$WRO_InParty."'";
		prn($sql);
        $Events_ID=q($sql, O_INSERTID);
		prn($qr);
		
		ob_start();
		if(false){ ?><div><?php } ?>
		A booking was made for field <?php echo $record['Name'];?>
		
		Region: <?php echo $record['WRO_Region'];?>
		
		Name: <?php echo $_SESSION['firstName']. ' ' .$_SESSION['lastName'];?>
		
		Time: <?php echo $time;?>
		
		Date: <?php echo date('n/j/Y',strtotime($date));?>
		
		Guest: <?php 
		if($GuestName){
			echo stripslashes($GuestName.', '.$GuestPhone);
		}else{
			echo '(none)';
		}
		?>
		
		<?php if(false){ ?></div><?php }
		$content=str_replace("\t",'',ob_get_contents());
		ob_end_clean();
		mail('txphi470@gmail.com,emai.samuel.fullman@gmail.com','Booking made on field: '.$record['Name'],$content,'From: booking@wingedrepublic.com');
		error_alert('Good job, you successfully booked this field!',1);
		?><script language="javascript" type="text/javascript">
		window.parent.location='/members/<?php echo strtolower($record['Identifier']);?>/confirmed?key=<?php echo $key;?>';
		</script><?php
		break;
	}else if($submode=='deleteBooking'){
		if(!($event=q("SELECT * FROM cal_events WHERE ID=$Events_ID", O_ROW)))error_alert('Cannot find that event!');
		//admin or they are this person
		if(!($_SESSION['cnx'][$acct]['defaultClients_ID']==$event['Clients_ID']) && !$_SESSION['cnx'][$acct]['accesses'][3])error_alert('You are not authorized to delete this schedule item');
		q("DELETE FROM cal_events WHERE ID=$Events_ID");
		?><script language="javascript" type="text/javascript">
		try{
		window.parent.g('r_<?php echo $Events_ID;?>').style.display='none';
		}catch(e){}
		</script><?php
		error_alert('deleted OK',1);
		break;
	}
}
if($mode=='componentEditor'){
	//be sure and fulfill null checkbox fields
	/*
	2012-03-12: this is universal code which should be updated on ALL components.  The objective is that 
	
	*/
	if(preg_match('/^(insert|update|delete)Resource$/',$submode)){
		if($submode=='deleteResource'){
			//see if it has been used
			if(q("SELECT COUNT(*) FROM cal_events WHERE Cal_ID=".($Cal_ID?$Cal_ID:$ID),O_VALUE))error_alert('This '.strtolower($resourceObjectName).' has been used already. You cannot delete it without first deleting all reservations');
			//delete it
			q("DELETE FROM cal_cal WHERE ID=$ID");
			?><script language="javascript" type="text/javascript">
			try{
			window.parent.g('r_<?php echo $Cal_ID?$Cal_ID:$ID?>').style.display='none';
			window.parent.close();
			}catch(e){}
			</script><?php
		}else{
			//unique identifier
			if(!preg_match('/^[_a-z0-9]+$/i',$Identifier))error_alert('Identifier must only contain numbers, letters, and the underscore character');
			if(q("SELECT COUNT(*) FROM cal_cal WHERE Identifier='".$Identifier."'".($submode=='updateResource'?" AND ID!=$ID":''), O_VALUE))error_alert('That identifier is already in use.  Select another one');
			
			//convert days of availability
			$WRO_Schedule=trim(implode(',',$WRO_Schedule));
			if(!$WRO_Schedule)$WRO_Schedule='PHP:NULL';
						
			//query
			if($mode=='insertResource')unset($ID);
			$sql=sql_insert_update_generic($MASTER_DATABASE,'cal_cal',$submode);
			prn($sql);
			$new=q($sql,O_INSERTID);
			if($submode=='insertResource')$ID=$new;
				
			//range/season calendar(s)
			q("DELETE FROM cal_CalCal WHERE ChildCal_ID=$ID");
			foreach($seasons as $v)	q("INSERT INTO cal_CalCal SET ParentCal_ID=$v, ChildCal_ID=$ID, Rlx='Available'");
			?><script language="javascript" type="text/javascript">
			<?php if($mode=='insertResource'){ ?>
			alert('New record added OK');
			window.parent.g('ID').value=<?php echo $ID;?>;
			<?php }else{ ?>
			alert('Record updated OK');
			<?php } ?>
			</script><?php
			break;			
		}
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
			q("INSERT INTO gen_nodes_settings SET Nodes_ID='$_thisnode_', EditDate=NOW()");
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
		error_alert('fail');
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
	<div>
	Calendar Overall CSS:<br />
	<textarea name="default[resourceschedulerCSS]" cols="65" rows="8" id="calCSS" onchange="dChge(this);" class="tabby"><?php echo h($resourceschedulerCSS);?></textarea>
	
	
	</div>
	<?php get_contents_tabsection('calendar');?>
	<div><?php
	$resources=q("SELECT c.*, MIN(e.StartDate) AS MinStart, MAX(e.StartDate) AS MaxStart, COUNT(DISTINCT e.ID) AS Bookings FROM cal_cal c LEFT JOIN cal_events e ON c.ID=e.Cal_ID AND e.StartDate BETWEEN '$resourceschedulerSeasonStart' AND '$resourceschedulerSeasonEnd' WHERE WRO_Category!='{range}' GROUP BY c.ID ORDER BY c.WRO_Region, c.Name", O_ARRAY);
	?><table class="yat"><thead>
	<tr>
	<th>&nbsp;</th>
	<th>Name</th>
	<th>Region</th>
	<th>Bookings</th>
	<th>from..</th>
	<th>to..</th>
	</tr>
	</thead>
	<tbody>
	<?php
	if(count($resources)){
		foreach($resources as $n=>$v){
			?><tr>
			<td><?php 
			if($v['Active']==0)echo '<em class="gray">inactive</em>';
			?></td>
			<td><a href="/_juliet_.editor.php?location=JULIET_COMPONENT_ROOT&file=<?php echo end(explode('/',__FILE__));?>&_thisnode_=<?php echo $_thisnode_;?>&formNode=resource:<?php echo $v['Identifier'];?>"><?php echo $v['Name']?></a></td>
			<td><?php echo $v['WRO_Region'];?></td>
			<td><?php echo $v['Bookings']?$v['Bookings']:'<span class="gray">(none)</span>';?></td>
			<td><?php echo $v['MinStart']?date('D n/j/Y',strtotime($v['MinStart'])):'<em class="gray">N/A</em>';?></td>
			<td><?php echo $v['MaxStart']?date('D n/j/Y',strtotime($v['MaxStart'])):'<em class="gray">N/A</em>';?></td>
			</tr><?php
		}
	}else{
		?><tr><td colspan="100%"><span class="gray">(No fields listed)</span></td></tr><?php	
	}
	?><tr><td colspan="100%"><input type="button" value="Add new resource.." onClick="window.location='/_juliet_.editor.php?location=JULIET_COMPONENT_ROOT&file=<?php echo end(explode('/',__FILE__));?>&_thisnode_=<?php echo $_thisnode_;?>&formNode=resource:'" /></td></tr>
	</tbody></table>
	</div>
	<?php get_contents_tabsection('resources');?>
	<div><?php
	$members=q("SELECT cl.ID, c.FirstName, c.LastName, c.Email, 
	IF(c.HomeMobile,c.HomeMobile,cl.Mobile) AS HomeMobile, 
	IF(c.HomePhone,c.HomePhone,cl.Phone) AS HomePhone,
	e.ID AS Events_ID,
	e.StartDate,
	e.StartTime,
	e.EndTime,
	e.WRO_InParty,
	e.WRO_GuestName,
	e.WRO_GuestPhone,
	WRO_Region,
	cal.Name AS CalName,
	ca.Access_ID
	FROM (finan_clients cl JOIN finan_ClientsContacts cc ON cl.ID=cc.Clients_ID AND cc.Type='Primary' JOIN addr_contacts c ON cc.Contacts_ID=c.ID LEFT JOIN addr_ContactsAccess ca ON c.ID=ca.Contacts_ID) LEFT JOIN
	cal_events e ON cl.ID=e.Clients_ID LEFT JOIN cal_cal cal ON e.Cal_ID=cal.ID
	WHERE ca.Access_ID=6 OR e.ID IS NOT NULL
	ORDER BY IF(ca.Access_ID=6, 1,2), c.LastName, c.FirstName, cl.ID, e.StartDate, cal.Name, e.StartTime, e.EndTime", O_ARRAY);

	$key=md5(rand(1,1000));
	ob_start();
	echo $key;

	?><table class="yat"><thead>
	<tr>
    <th>Status</th>
	<th>Name</th>
	<th>Email</th>
	<th>Phone</th>
	<th>Cell</th>
	</tr>
	</thead>
	<tbody>
	<?php
	$i=0;
	foreach($members as $n=>$v){
		if($v['ID']==$buffer)continue;
		$i++;
		?><tr>
        <td><?php
        echo $v['Access_ID'];
            ?></td>
		<td><a href="/console/members.php?Clients_ID=<?php echo $v['ID'];?>" onclick="return ow(this.href,'l1_members','800,700');"><?php echo $v['LastName'].', '.$v['FirstName'];?></a></td>
		<td><?php echo $v['Email'];?></td>
		<td><?php echo $v['HomePhone']?$v['HomePhone']:'<em class="gray">(none)</em>';?></td>
		<td><?php echo $v['HomeMobile']?$v['HomeMobile']:'<em class="gray">(none)</em>';?></td>
		</tr><?php
		$buffer=$v['ID'];
	}
	?>
	</tbody></table><?php
	$out=ob_get_contents();
	ob_end_clean();
	echo str_replace($key,'<h3 class="nullTop">'.$i.' members</h3>',$out);
	?>
	</div>
	<?php get_contents_tabsection('members');?>
	<style type="text/css">
	.yat td.subsection{
		border-bottom:1px solid #000;
		}
	</style>
	<div><table class="yat"><thead><tr>
	<th>&nbsp;</th>
	<th>Field</th>
	<th>Date/Time</th>
	<th>Guest</th>
	<th>In Party</th>
	</tr></thead><tbody><?php
	foreach($members as $n=>$v){
		if(!$v['StartDate'])continue;
		if($buffer!==$v['ID']){
			?><tr><td class="subsection" colspan="100%">
			<a href="/console/members.php?Clients_ID=<?php echo $v['ID'];?>" onclick="return ow(this.href,'l1_members','800,700');"><?php echo $v['LastName'].', '.$v['FirstName'];?></a>
			</td></tr><?php
			$buffer=$v['ID'];
		}
		?><tr id="r_<?php echo $v['Events_ID'];?>">
		<td><a href="/index_01_exe.php?mode=componentControls&submode=deleteBooking&Events_ID=<?php echo $v['Events_ID'];?>&location=JULIET_COMPONENT_ROOT&file=<?php echo end(explode('/',__FILE__));?>" style="opacity:.50;" target="w2" onclick="return confirm('Delete this booking?');"><img align="absbottom" src="/images/i/del2.gif" alt="x" width="8" /></a></td>
		<td><?php echo $v['CalName'];?></td>
		<td><?php echo date('D n/j/Y',strtotime($v['StartDate'])). ' - '.date('g:iA',strtotime($v['StartTime'])).' to '.date('g:iA',strtotime($v['EndTime']));?></td>
		<td title="<?php echo $v['WRO_GuestPhone'];?>"><?php echo $v['WRO_GuestName'];?></td>
		<td class="tac"><?php echo $v['WRO_InParty'];?></td>
		</tr><?php
	}
	?></tbody></table>
	</div>
	<?php get_contents_tabsection('schedule');?>
	<div>automation</div>
	<?php get_contents_tabsection('automation');?>
	Name of "resource" in this application: <input name="default[Name]" type="text" id="default[Name]" value="<?php echo h($resourceObjectName);?>" size="30" onchange="dChge(this);" /><br />
	
	Beginning of current season: <input name="default[resourceschedulerSeasonStart]" type="text" id="default[resourceschedulerSeasonStart]" value="<?php echo h($resourceschedulerSeasonStart);?>" /> <span class="gray">(Used for presenting bookings for current year)</span><br>
	End of current season: <input name="default[resourceschedulerSeasonEnd]" type="text" id="default[resourceschedulerSeasonEnd]" value="<?php echo h($resourceschedulerSeasonEnd);?>" /><br>
	Maximum number of bookings per customer: <input name="default[resourceschedulerMaxBookings]" type="text" id="default[resourceschedulerMaxBookings]" value="<?php echo h($resourceschedulerMaxBookings);?>" size="3" /><br>
	
	
	
	<br>
	<?php
	get_contents_tabsection('Settings');
	require($JULIET_COMPONENT_ROOT.'/products._help_.php');?>
	<?php
	get_contents_tabsection('help');
	tabs_enhanced(
		array(
			'calendar'=>array(
				'label'=>'Calendar',
			),
			'resources'=>array(
				'label'=>'Resources',
			),
			'members'=>array(
				'label'=>'Members',
			),
			'schedule'=>array(
				'label'=>'Schedule',
			),
			'automation'=>array(
				'label'=>'Automation',
			),
			'Settings'=>array(),
			'help'=>array(
				'label'=>'Help',
			),
		) 
	);
	break;
}else if(preg_match('/^resource:/',$formNode)){
	//record
	$insertMode='insertResource';
	$updateMode='updateResource';
	$deleteMode='deleteResource';
	$mode=$updateMode;
	if(!($events=q("SELECT c.*, ct.Email, cl.Phone, e.ID AS Events_ID, e.StartDate, e.StartTime, e.EndTime, e.Name AS EventName, e.Clients_ID, e.WRO_GuestName, e.WRO_GuestPhone, e.WRO_InParty, ct.FirstName, ct.LastName, ct.HomeMobile, cc.Clients_ID, cc.Contacts_ID
		FROM cal_cal c 
		LEFT JOIN cal_events e ON c.ID=e.Cal_ID 
		LEFT JOIN finan_clients cl ON e.Clients_ID=cl.ID
		LEFT JOIN finan_ClientsContacts cc ON cl.ID=cc.Clients_ID AND cc.Type='Primary'
		LEFT JOIN addr_contacts ct ON cc.Contacts_ID=ct.ID
		WHERE c.Identifier='".end(explode(':',$formNode))."'", O_ARRAY))){
		$mode=$insertMode;
		$Identifier='_new_resource_';
	}
	@extract($events[1]);
	?><script language="javascript" type="text/javascript">
	$(document).ready(function(){
		$('#currentResource').change(function(){
			if(detectChange && !confirm('You have started to change this <?php echo $resourceObjectName;?> and will lose your changes if you switch. Continue?'))return false;
			window.location='_juliet_.editor.php?_thisnode_=<?php echo $_thisnode_;?>&handle=resourcescheduler&location=JULIET_COMPONENT_ROOT&file=<?php echo end(explode('/',__FILE__));?>&formNode=resource:'+$(this).val();
		});
		$('#submode').val('<?php echo $mode;?>');
	});
	</script>
	Current <?php echo $resourceObjectName?>:<select id="currentResource">
	<?php
	$i=0;
	foreach(q("SELECT ID, WRO_Region, Identifier, Name FROM cal_cal WHERE WRO_Category!='{range}' ORDER BY WRO_Region, Name", O_ARRAY) as $v){
		$i++;
		if($v['WRO_Region']!==$buffer){
			if($i>1)echo '</optgroup>';
			$buffer=$v['WRO_Region'];
			?><optgroup label="<?php echo h($buffer);?>"><?php
		}
		?><option value="<?php echo $v['Identifier'];?>" <?php echo $ID==$v['ID']?'selected':''?>><?php echo h($v['Name']);?></option><?php
	}
	?></optgroup>
	<option value="" <?php echo !$ID?'selected':''?>>&lt;Add new <?php echo strtolower($resourceObjectName);?>..&gt;</option>
	</select>
	&nbsp;&nbsp;<a href="/_juliet_.editor.php?location=JULIET_COMPONENT_ROOT&file=<?php echo end(explode('/',__FILE__));?>&_thisnode_=<?php echo $_thisnode_;?>&formNode=default">Return to main list..</a>
	
	<?php ob_start();?>
	<input type="hidden" name="Active" value="0" />
	<label><input type="checkbox" name="Active" value="1" <?php echo !strlen($Active) || $Active==1?'checked':''?> /> Active field for booking</label><br><br>
	Name: <input name="Name" type="text" id="Name" value="<?php echo h($Name);?>" size="30" onchange="dChge(this);" /><br />
	<div>
	<script language="javascript" type="text/javascript">
	</script>
	<input type="hidden" name="ID" id="ID" value="<?php echo $ID;?>" />
	Identifier for <?php echo $resourceObjectName;?>: <span class="gray">(goes in the URL, e.g. /members/resourcename)</span><br />
	<?php if($mode==$insertMode){ ?>
	<input name="Identifier" type="text" id="Name" value="" size="30" onchange="dChge(this);" /><br />

	<?php }else{ ?>
	<input type="hidden" name="Identifier" id="Identifier" value="<?php echo $Identifier;?>" />
	<strong style="font-size:139%;"><?php echo $Identifier;?></strong>
	<?php } ?><br />
	<!-- custom fields here, NOT part of resource -->
	Address: <input name="WRO_Address" type="text" id="WRO_Address" value="<?php echo h($WRO_Address);?>" size="30" onchange="dChge(this);" /><br />
	City: <input name="WRO_City" type="text" id="WRO_City" value="<?php echo h($WRO_City);?>" size="30" onchange="dChge(this);" /><br />
	State:
	<select name="WRO_State" id="WRO_State" onChange="dChge(this);" style="width:175px;">
	<option value="" class="ghost"> &lt;Select..&gt; </option>
	<?php
	if(!$WRO_State)$WRO_State='TX';
	$states=q("SELECT st_code, st_name FROM aux_states",O_COL_ASSOC, $public_cnx);
	foreach($states as $n=>$v){
		?><option value="<?php echo $n?>" <?php echo $WRO_State==$n?'selected':'';?>><?php echo h($v)?></option><?php
	}
	?>
	</select><br />
	Zip: <input name="WRO_Zip" type="text" id="WRO_Zip" value="<?php echo h($WRO_Zip);?>" size="7" onchange="dChge(this);" /><br />
	Instructions/Directions: <span class="gray">(HTML OK)</span><br />
	<textarea name="Description" id="Description" rows="6" cols="60" onchange="dChge(this);"><?php echo h($Description);?></textarea><br />
Google Map Link:<br />
<textarea name="WRO_MapLink" id="WRO_MapLink" rows="6" cols="60" onchange="dChge(this);"><?php echo h($WRO_MapLink);?></textarea><br />
Google Map Embed (iframe):<br />
<textarea name="WRO_MapEmbed" id="WRO_MapEmbed" rows="6" cols="60" onchange="dChge(this);"><?php echo h($WRO_MapEmbed);?></textarea><br />
	Hunter cap in field: <input name="WRO_Max" type="text" id="WRO_Max" value="<?php echo h($WRO_Max);?>" size="7" onchange="dChge(this);" />
	<br />
	<br />
	Region: 
	<select name="WRO_Region" id="WRO_Region" onchange="dChge(this);">
	  <option>&lt;Select..&gt;</option>
	  <option <?php echo $WRO_Region=='North'?'selected':''?> value="North">North</option>
	  <option <?php echo $WRO_Region=='South'?'selected':''?> value="South">South</option>
	  <option <?php echo $WRO_Region=='Central'?'selected':''?> value="Central">Central</option>
	  <option <?php echo $WRO_Region=='Special White Wing'?'selected':''?> value="Special White Wing">Special White Wing</option>
	  </select>
	<br />
	<br />
	Applicable season(s):<br />
	<select size="8" multiple="multiple" name="seasons[]" id="seasons" onchange="dChge(this);">
	<option value="" class="gray">(none)</option>
	<?php
	$seasons=q("SELECT c.ID, c.Name, cc.ChildCal_ID FROM cal_cal c LEFT JOIN cal_CalCal cc ON c.ID=cc.ParentCal_ID AND cc.ChildCal_ID='$ID' WHERE WRO_Category='{range}'", O_ARRAY);
	foreach($seasons as $w){
		?><option value="<?php echo $w['ID']?>" <?php echo $w['ChildCal_ID']?'selected':''?>><?php echo h($w['Name']);?></option><?php
	}
	?>
	</select><br />	
	</div>
	<?php
	get_contents_tabsection($resourceObjectName);
	?><div id="resourceSchedule">
	<table class="yat"><thead>
	<tr>
	<th>&nbsp;</th>
	<th>Date</th>
	<th>Time</th>
	<th>&nbsp;</th>
	<th>Customer</th>
	<th>Guest</th>
	<th>#In Party</th>
	</tr>
	</thead><tbody>
	<?php
	if($events[1]['Events_ID']){
		$events=subkey_sort($events,array(
			'StartDate','StartTime','LastName','FirstName'
		));
		foreach($events as $n=>$v){
			?><tr id="r_<?php echo $v['Events_ID'];?>">
			<td>[<a href="/console/events.php?Events_ID=<?php echo $v['Events_ID'];?>" onclick="return ow(this.href,'l1_events','800,700');">edit</a>]&nbsp;[<a href="/index_01_exe.php?mode=componentControls&submode=deleteBooking&Events_ID=<?php echo $v['Events_ID'];?>&location=JULIET_COMPONENT_ROOT&file=<?php echo end(explode('/',__FILE__));?>" target="w2" onclick="return confirm('Are you sure you want to delete this booking?');">delete</a>]</td>
			<td><?php echo date('D n/j',strtotime($v['StartDate']));?></td>
			<td><?php 
			if($v['StartTime']=='07:00:00' && $v['EndTime']=='12:00:00'){
				echo 'morning';
			}else if($v['StartTime']=='12:00:00'){
				echo 'afternoon';
			}else{
				echo 'all&nbsp;day';
			}
			?></td>
			<td><a href="mailto:<?php echo $v['Email'];?>"><img src="/images/i/mail1_30x30.gif" width="17" alt="(e)" /></a></td>
			<td><a href="/console/members.php?Clients_ID=<?php echo $v['Clients_ID'];?>" title="Click to view member info" onclick="return ow(this.href,'l1_members','800,700');"><?php echo $v['LastName'].', '.$v['FirstName'];?></a></td>
			<td title="<?php echo $v['WRO_GuestPhone']?'Guest phone #: '.$v['WRO_GuestPhone']:'';?>"><?php echo $v['WRO_GuestName'];?></td>
			<td class="tac"><?php echo $v['WRO_InParty'];?></td>
			</tr><?php
		}
	}else{
		?><tr><td colspan="101"><span class="gray">(Nothing scheduled)</span></td></tr><?php
	}
	?></tbody></table>
	</div><?php
	get_contents_tabsection('Schedule');
	?>
	Availability:<br />
	<select size="8" multiple="multiple" name="WRO_Schedule[]" type="text" id="WRO_Schedule" onchange="dChge(this);">
	<option value="" class="gray">(ALL DAYS)</option>
	<?php
	if($WRO_Schedule)$a=explode(',',$WRO_Schedule);
	$days=array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
	for($i=0;$i<=6;$i++){
		?><option value="<?php echo $i?>" <?php echo @in_array($i,$a)?'selected':''?>><?php echo $days[$i];?></option><?php
	}
	?>
	</select><br />
	<br />
	<div class="fl" style="width:175px;">
	"<strong>On</strong>" Days<br />
	<span class="gray">(these days will be available even if not on the list above; one date per line)</span><br />
	<textarea name="WRO_DaysOn" type="text" rows="14" cols="12" id="WRO_DaysOn" onchange="dChge(this);"><?php echo h($WRO_DaysOn);?></textarea>
	</div>
	<div class="fl" style="width:175px;">
	"<strong>Off</strong>" Days<br />
	<span class="gray">(these days will NOT be available even if on the list above; one date per line)</span><br />
	<textarea name="WRO_DaysOff" type="text" rows="14" cols="12" id="WRO_DaysOff" onchange="dChge(this);"><?php echo h($WRO_DaysOff);?></textarea>
	</div>
	<div class="cb0"></div>

	<?php
	get_contents_tabsection('Availability');
	tabs_enhanced(array(
		$resourceObjectName=>array(
			'label'=>$resourceObjectName.' Data',
		),
		'Schedule'=>array(
		),
		'Availability'=>array(
		),
	));
}


//-------------------------- regions have common purposing, this makes code below easier -----------------------------
if($thispage=='members'){

}else if($thisfolder=='members'){
	//we are looking at an individual field
	if($thissubfolder){
		$field=$thissubfolder;
	}else{
		$field=$thispage;
	}
	$pJBodyClass[]='wide focus-resource';
	//get the resource record
	events_range(array(
		'comparison'=>"Identifier='$field'",
	));

	//get the field picture
	$img=get_image($thispage,get_file_assets($_SERVER['DOCUMENT_ROOT'].'/images/resources/fieldpics'),array(
		'return'=>'string',
	));
}
//--------------------------------------------------------------------------------------------------------------------

ob_start();
?>
<span id="resourcescheduler" class="<?php echo $pJDerivedThispage ? $pJDerivedThispage : $thispage?>"><?php
//------------- here is where the center block goes -------------------------

if($thispage=='members'){
	?><style type="text/css">
	.zonedivider{
		border-bottom:1px solid ;
		}
	</style>
	<div class="fields"><?php
	//intro
	CMSB('bookFields');
    ?><p>
    <a href="/members/edit">Click here to manage any current bookings you have.</a>
    </p><?php

	//call to edit
	echo pJ_call_edit(array(
		'level'=>($hasAdmin?0:ADMIN_MODE_DESIGNER),
		'location'=>'JULIET_COMPONENT_ROOT',
		'file'=>end(explode('/',__FILE__)),
		'winsize'=>'900,650',
		'editLinkBase'=>'_editLinkMod_',
		'thisnode'=>q("SELECT ID FROM gen_nodes WHERE Type='Object' AND Name='Members'", O_VALUE),
		'label'=>'Edit the scheduler here',
	));

	//get fields based on a category, and also ones with 
	if($a=q("SELECT * FROM _v_cal_bookings ORDER BY ID, StartDate", O_ARRAY)){
		unset($fields);
		$maps=get_file_assets($_SERVER['DOCUMENT_ROOT'].'/images/resources/fieldpics');
		foreach($a as $n=>$v){
			$fields[$v['ID']]['identifier']=$v['Identifier'];
			$fields[$v['ID']]['active']=$v['Active'];
			$fields[$v['ID']]['name']=$v['Name'];
			$fields[$v['ID']]['region']=$v['WRO_Region'];
			$fields[$v['ID']]['city']=$v['WRO_City'];
			$fields[$v['ID']]['category']=$v['WRO_Category'];
			$fields[$v['ID']]['description']=$v['Description'];
			$fields[$v['ID']]['dates'][]=$v['StartDate'];
			$fields[$v['ID']]['max']=$v['WRO_Max'];
			$fields[$v['ID']]['maplink']=$v['WRO_MapLink'];
			$fields[$v['ID']]['mapembed']=$v['WRO_MapEmbed'];
		}
		$fields=subkey_sort($fields,array('region','city'));
		$sp=0;
		foreach($fields as $n=>$v){
			if($v['active']==0)continue;
			$sp++;
			if($sp>1){
				?><br /><br /><br /><?php
			}
			if($v['region']!==$buffer){
				$buffer=$v['region'];
				?><h1 class="zonedivider"><?php echo $buffer;?> Zone</h1><?php
			}
			?>
			<h3><?php echo $v['name']?></h3>
			<p>Region: <strong><?php echo $v['region'];?></strong></p>
			<?php if($v['city']){ ?>
			<p>City: <strong><?php echo $v['city'];?></strong></p>
			<?php } ?>
			<p><?php
			echo $v['description'];
			?></p><?php
			if($str=$v['maplink']){
				?><p><a href="<?php echo $str;?>" title="Google Map Link" target="_blank">Click here for a Google maps link (for directions)</a></p><?php
			}
			?>
			<!--
			<p>Hunter cap: <strong><?php echo $v['max'];?></strong></p>
			-->
			<input type="button" onclick="window.location='/members/<?php echo $v['identifier'];?>';" name="button" value="Step 1: Select this Field &#8595;" />
			<br />
			<?php
			if($img=get_image($v['identifier'],$maps,array(
				'return'=>'string',
				))){
				?><a href="/members/<?php echo $v['identifier'];?>" title="click the map to book <?php echo strtolower($v['category']);?> in this field"><?php
				tree_image(array(
					'src'=>'images/resources/fieldpics/'.$img,
					'boxMethod'=>4,
					'disposition'=>'500x',
                    'style'=>'max-width: 100%;',
					'alt'=>'click the map to book '.strtolower($v['category']).' in this field',
				));
				?></a><?php
				#prn($img);
			}
						
		}
	}
	
	?>
	<br />
	</div>
	<?php
}else if($thisfolder=='members' && $thissubfolder==''){
	?><div class="fr">
	<h3><a href="/members"><< Back to main field listing</a></h3>
	</div>
	<h1><?php echo $record['Name'];?></h1>
	</span>
	<h3>Step 2: Select a Date Based On Availability</h3>
	<?php
	echo pJ_call_edit(array(
		'level'=>($hasAdmin?0:ADMIN_MODE_DESIGNER),
		'location'=>'JULIET_COMPONENT_ROOT',
		'file'=>end(explode('/',__FILE__)),
		'winsize'=>'900,650',
		'editLinkBase'=>'_editLinkMod_',
		'formNode'=>'resource:'.$thispage,
		'thisnode'=>q("SELECT ID FROM gen_nodes WHERE Type='Object' AND Name='Members'", O_VALUE),
		'label'=>'Edit this '.$resourceObjectName,
	));	
	?>
	<span class="<?php echo $pJDerivedThispage ? $pJDerivedThispage : $thispage?>">
	<p>Region: <strong><?php echo $record['WRO_Region'];?></strong></p>
	<!--
	<p>Hunter cap for this field: <strong><?php echo $record['WRO_Max'];?></strong></p>
	-->
	<p>Season dates:<br />
	<?php
	//season start here
	unset($start,$end);
	foreach($range as $n=>$v){
		//2014-08-16 - filter last year out -
		if($v['start']<$resourceschedulerSeasonStart)continue;
		
		if(!$start)$start=$v['start'];
		$end=$v['end'];
		echo date('n/j/y',strtotime($v['start'])).($v['end']? ' to '.date('n/j/y',strtotime($v['end'])) : '').'<br />';
	}
	?>
	</p>
	<?php CMSB('common:calpreamble','',array(
		'commonfolder'=>'members',
	));?>
	<?php
	if($img)tree_image(array(
		'src'=>'images/resources/fieldpics/'.$img,
		'boxMethod'=>4,
		'disposition'=>'700x',
		'alt'=>$record['WRO_Category'].' field',		
	));
	//settings for calendar
	$a=explode('-',$start);
	if(!$month)$month=$a[1];
	if(!$year)$year=$a[0];
	$calPreventPastNavigation=$start;
	$calPreventFutureNavigation=$end;
	$Cal_ID=$record['ID'];
	$calNavURL.='&calPreventPastNavigation='.$calPreventPastNavigation.'&calPreventFutureNavigation='.$calPreventFutureNavigation;
	//this file is the first instance of 1.30
	require($MASTER_COMPONENT_ROOT.'/calWidget_v130.php');
}else{
	if($thispage=='book'){
		$date=$year.'-'.str_pad($month,2,'0',STR_PAD_LEFT).'-'.str_pad($day,2,'0',STR_PAD_LEFT);
		?><form target="w2" method="post" action="/index_01_exe.php">
		<div id="bookForm">
		<h1><a href="/members" title="Back to main field listing">Book a Field</a> &raquo; <a href="/members/<?php echo $thissubfolder;?>" title="click here to select a different date"><?php echo $record['WRO_Region'].' zone - '.$record['Name'];?></a> &raquo; Booking</h1>
		<input name="file" type="hidden" id="file" value="<?php echo end(explode('/',__FILE__));?>" />
		<input name="location" type="hidden" id="location" value="JULIET_COMPONENT_ROOT" />
		<input name="mode" type="hidden" id="mode" value="componentControls" />
		<input name="submode" type="hidden" id="submode" value="book" />
		<input name="time" type="hidden" id="time" value="<?php echo $time;?>" />
		<input name="date" type="hidden" id="date" value="<?php echo $date;?>" />
		<input name="Cal_ID" type="hidden" id="Cal_ID" value="<?php echo $record['ID'];?>" />
		<?php 
		if($hasAdmin){ 
            if(!$Clients_ID)$Clients_ID=$_SESSION['cnx'][$acct]['defaultClients_ID'];
            ?>Person booking for:
            <select name="Clients_ID" id="Clients_ID" onchange="dChge(this);">
            <option value="">&lt;Select..&gt;</option>
            <?php
            if($a=q("SELECT c.ID, ct.FirstName, ct.LastName, ct.MiddleName, ct.Email FROM finan_clients c JOIN finan_ClientsContacts cc ON c.ID=cc.Clients_ID AND cc.Type='Primary' JOIN addr_contacts ct ON cc.Contacts_ID=ct.ID JOIN addr_ContactsAccess ca ON ct.ID=ca.Contacts_ID WHERE ca.Access_ID=6 ORDER BY ct.LastName, ct.FirstName",O_ARRAY)){
                foreach($a as $v){
                    ?><option value="<?php echo $v['ID'];?>" <?php echo $Clients_ID==$v['ID']?'selected':''?>><?php echo $v['LastName'].', '.$v['FirstName'].($v['MiddleName']?' '.substr($v['MiddleName'],0,1):'').($v['Email']?' ('.$v['Email'].')':'');?></option><?php
                }
            }
            ?>
            </select><br />
            Number in party (including season pass holder):
            <select name="WRO_InParty" id="WRO_InParty" onchange="dChge(this);">
            <option value="">&lt;Select..&gt;</option>
            <?php
            for($i=1; $i<=50; $i++){
                ?><option value="<?php echo $i;?>" <?php echo $WRO_InParty==$i?'selected':'';?>><?php echo $i;?></option><?php
            }
            ?>
            </select>
            <?php
		}else{
		    ?>
            <p>Your name: <strong><?php echo $_SESSION['firstName'] . ' '.$_SESSION['lastName'];?></strong></p>
            <?php
		}
		?>
		<p>Booking a hunt for <strong><?php echo date('n/j/Y',strtotime($date));?></strong><br />
		Field: <strong><?php echo $record['Name'];?></strong><br />
		Booking time: <strong><?php
		if($time=='morning'){
			echo 'Morning (Sunrise to Noon)';
		}else if($time=='afternoon'){
			echo 'Afternoon (Noon to Sunset)';
		}else{
			echo 'All Day (Sunrise to Sunset latest)';
		}
        ?></strong></p>
            <p>

            <?php
            if(($corporate = q("SELECT WRO_Corporate FROM finan_clients WHERE ID =" . $_SESSION['cnx'][$acct]['defaultClients_ID'], O_VALUE, ERR_SILENT)) > 0){
                ?>
                You may bring <?php echo $corporate == 1 ? 'one guest' : $corporate . ' guests';?>.  Please enter their name<?php echo $corporate > 1 ? 's' : '';?> and phone number<?php echo $corporate > 1 ? 's' : '';?>:<br />
                <table>
                <?php for($i = 1; $i<= $corporate; $i++){
                    ?><tr>
                        <td>Name: <input type="text" name="GuestName[]" size="35" /> </td>
                        <td>Phone: <input type="text" name="GuestPhone[]" /> </td>
                    </tr><?php
                }
                ?>
                </table>
                <?php
            }else{
               ?>
                Do you have a guest? <?php echo !$hasAdmin?'(limit 1)':''?> Enter their name here:
                <input name="GuestName" type="text" id="GuestName" value="<?php echo h($GuestName);?>" size="35" />
                <br />
                Enter your guest's contact phone:
                <input name="GuestPhone" type="text" id="GuestPhone" value="<?php echo h($GuestPhone);?>" />
                <?php
            }
            ?>
		</p>
		<p>
		<input name="IAgree" type="hidden" id="IAgree" value="0" />
		<label><input name="IAgree" type="checkbox" id="IAgree" value="1" /> I (and my guest if applicable) agree to bag and take out all trash and to hunt responsibly</label>
		</p>
		<p>
		<input type="submit" name="Submit" value="Confirm" /> 
		</p>
		</div>
		</form><?php
	}else if($thispage=='confirmed'){
		$record=q("SELECT e.*, c.Name AS CalName FROM cal_events e JOIN cal_cal c ON e.Cal_ID=c.ID WHERE e.ResourceToken='$key'", O_ROW);
		?><div>
		<h1>Booking Confirmation</h1>
		<p>You have successfully booked the field <strong><?php echo $record['CalName'];?></strong> for hunting on <?php echo date('n/j/Y',strtotime($record['StartDate']));?> from <?php echo date('g:iA',strtotime($record['StartTime']));?> to <?php echo date('g:iA',strtotime($record['EndTime']));?>.  Thank you for using the online booking system!</p>
		<p>Your confirmation number is B-<?php echo $record['ID'];?></p>
		
		<h3>Book another slot</h3>
		<p>You can have up to 3 slots on-calendar at any given time.  <a href="/members">Click here to reserve another location or date</a> as long as you're not over 3</p>
		</div><?php
	}
}


//-------------------------- here is where it ends -------------------------------
?>
</span>
<?php
$mainRegionCenterContent=ob_get_contents();
ob_end_clean();






}//---------------- end i break loop ---------------
compend:
