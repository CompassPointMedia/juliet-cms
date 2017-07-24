<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/properties_04_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Ticket Management </title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" href="/console/rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">
body, td{
	font-size:14px;
	}
.optionBox{
	margin-top:15px;
	border:1px solid #ccc;
	border-radius:15px;
	padding:15px;
	}
</style>
<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/loader_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/contextmenus_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/dataobjects_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/jquery.js"></script>
<script type="text/javascript" src="../Library/ckeditor_3.4/ckeditor.js"></script>
<script language="JavaScript" type="text/javascript">
/* periwinkle coding 2.1 */
var thispage='buy-now.ticket.focus.php';
var thisfolder='console';
var browser='Moz';
var ctime='1385598780';
var PHPSESSID='764b8de9672c8575bacf6777a2fb5586';
//for nav feature
var count='4';
var ab='4';
var isEscapable=1;
var isDeletable=1;
var isModal=1;
var talks=1; //whether this page broadcasts overall state changes which other pages listen for
var listens=0;
var UserName='dtester';

AddOnkeypressCommand("PropKeyPress(e)");
//var customDeleteHandler='deleteItem()';
$(document).ready(function(){
	$('#OK').click(function(){ g('subsubmode').value='updateTicket'; });
	$('#updateApplication').click(function(){ g('subsubmode').value='updateApplication'; });
	$('#updateRefcheck').click(function(){ g('subsubmode').value='updateRefcheck'; });
	$('#finalizeRefcheck').click(function(){ g('subsubmode').value='finalizeRefcheck'; });	
	$('#addCall').click(function(){ g('subsubmode').value='addCall'; });	
});
</script>

<!-- InstanceEndEditable -->
</head>

<body id="properties">
<form id="form1" name="form1" target="w2" method="post" action="/console/resources/bais_01_exe.php" onsubmit="return beginSubmit();" enctype="multipart/form-data">
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->

<div id="btns140" class="fr"><input id="Previous" type="button" name="Submit" value="Previous" class="navButton_A" onclick="focus_nav(-1, 'update', 0, 0, 'nav_query_add()');"  >
<input id="Next" type="button" name="Submit" value="Next" class="navButton_A" onclick="focus_nav(1,'update',0,0, 'nav_query_add()');" ><input name="ID" type="hidden" id="ID" value="4" />
<input name="navVer" type="hidden" id="navVer" value="1.43" />
<input name="navObject" type="hidden" id="navObject" value="Tickets_ID" />
<input name="nav" type="hidden" id="nav" />
<input name="navMode" type="hidden" id="navMode" value="" />
<input name="count" type="hidden" id="count" value="4" />
<input name="abs" type="hidden" id="abs" value="4" />

<input type="hidden" name="location" id="location" value="JULIET_COMPONENT_ROOT" />
<input type="hidden" name="file" id="file" value="buy-now.php" />
<input type="hidden" name="mode" id="mode" value="componentControls" />
<input name="submode" type="hidden" id="submode" value="staffActions" />
<input name="subsubmode" type="hidden" id="subsubmode" />


<input id="OK" type="submit" name="Submit2" value="OK" class="navButton_A" />
<!-- end navbuttons 1.43 --></div>

<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->

Name: <strong>Tester, David</strong>
<br />
Ticket #<strong>WT-4-7</strong><br />
<div id="attributes" class="fr">
Package type: <strong>Executive</strong><br />
Follow up type: <strong>Intensive - Call First</strong><br />
</div>
<strong>Employer or Contact Name</strong>: Oscar Fullman<br />
<br />
<div class="fl"><strong>Contact Info</strong>:</div>
<div class="fl">
Huntsville Penitentiary<br />123 Main St. #4<br />
Huntsville, AL&nbsp;&nbsp;33018</div>
<div class="cb0"></div>

	<style type="text/css">
		#tabWrap{
		position:relative;
		margin-top:35px;
		}
	#tabWrap a:hover{
		text-decoration:none;
				}
	.tabon, .taboff{
		float:left;
		margin-right:5px;
		background-color:#fff;
		border-left:1px solid #444;
		border-right:1px solid #444;
		border-top:1px solid #444;
		-moz-border-radius: 4px 4px 0px 0px;
		border-radius: 4px 4px 0px 0px;
		cursor:pointer;
		}
	.tabon{
		padding:3px 5px 8px 5px;
		margin-top:5px;
		border-bottom:1px solid white;
		}
	.taboff{
		padding:3px 5px;
		margin-top:10px;
		}
	.lowerline{
		border-top:1px solid #444;
		clear:both;
		margin-top:-1px;
		background-color:#99CCFF;
		}
	.tabRaise{
		position:absolute;
		top:-33px;
		left:15px;
		}
	.tabSectionStyleIII{
		padding:15px;
		border-left:1px solid #444;
		border-right:1px solid #444;
		border-bottom:1px solid #444;
		margin-bottom:10px;
		min-height:250px;
		}
		</style>
	<script language="javascript" type="text/javascript">
		$(document).ready(function(){
		$('.tabRaise a').click(function(){
			if($(this).hasClass('current'))return false;
			if($('#overallWrap').find(":animated").length>0)return false;
			var newList=$(this).attr('href').substring(1);
			//get id of current layer
			var currentList=$('.tabRaise .current').attr('href').substring(1);
			//fade out current layer
			$('#'+currentList).fadeOut(200, function(){
				//set cookie early in case of failure
				sCookie('tenhanced_default',newList);
				//fade in clicked layer
				$('#'+newList).fadeIn(200, function(){
					// Remove highlighting - Add to just-clicked tab
					$('#tab_'+currentList).removeClass('tabon');
					$('#tab_'+currentList).addClass('taboff');
					$('#tab_'+newList).addClass('tabon');
					$('#tab_'+newList).removeClass('taboff');
	
					$('#tab_'+currentList+' a').removeClass('current');
					$('#tab_'+newList+' a').addClass('current');
									});
			});
			return false;
		});
	});
		</script>
	<div id="overallWrap">	<div id="tabWrap">
		<div class="lowerline"> </div>
		<div class="tabRaise">
							<div id="tab_History" class="tabon"><a href="#History"   class="current" >History</a></div>
								<div id="tab_Calls" class="taboff"><a href="#Calls"  >Call Log/Actions</a></div>
								<div id="tab_Application" class="taboff"><a href="#Application"  >Application</a></div>
								<div id="tab_refcheckResponse" class="taboff"><a href="#refcheckResponse"  >Ref.Check Response</a></div>
								<div id="tab_Documents" class="taboff"><a href="#Documents"  >Documents</a></div>
								<div id="tab_Help" class="taboff"><a href="#Help"  >Help</a></div>
						</div>	</div>
<div id="layerWrap">
<div id="History" class="tabSectionStyleIII" style="display:block;">
<div>
<p>History is a list of all actions and status changes for this ticket.  To log a call click on Call Log/Actions and enter the information regarding the call.  Be sure and indicate whether it is an incoming or outgoing call</p>
<table class="yat"><thead>
<tr>
<th>Date</th>
<th>Action</th>
<th>&nbsp;</th>
<th>by..</th>
<th>Comments</th>
<th>&nbsp;</th>
</tr></thead><tbody>
<tr>
		<td>11/24 @10:24AM</td>
		<td>call</td>
		<td><img title="outgoing call" src="/images/i/call-outgoing.png" width="20" /></td>
		<td>system</td>
		<td>this was his personal voicemail, sounded like a cell phone</td>
		<td></td>
		</tr><tr>
		<td>11/27 @9:34AM</td>
		<td>contact request to staff</td>
		<td></td>
		<td>cpm210</td>
		<td>added by buy-now.php line 2076</td>
		<td></td>
		</tr></tbody></table>
</div>
</div>

<div id="Calls" class="tabSectionStyleIII" style="display:none;">
<div>
  <div class="optionBox">
<strong>New Call</strong><br />
Call happened at <input type="text" name="callTime" id="callTime" onchange="dChge(this);" /> <input type="button" name="button" value="Now.." onclick="g('callTime').value=new Date();" /><br />
<label><input type="radio" name="CallIncoming" value="0" checked onchange="dChge(this);" /> Outgoing</label>
&nbsp;&nbsp;
<label><input type="radio" name="CallIncoming" value="1" onchange="dChge(this);" /> Incoming</label><br />
Status of call: <select name="CallAnswered" id="CallAnswered" onchange="dChge(this);">
<option value="">&lt;Select..&gt;</option>
<option value="0">disc. or wrong number</option><option value="1">no answer</option><option value="2">left message voicemail</option><option value="4">left message verbally</option><option value="8">spoke to authorized party</option><option value="16">spoke to targeted person</option></select>
<br />
Spoke to: <input type="text" name="Recipient" id="Recipient" value="" onchange="dChge(this);" /><br />
Call notes:<br />
<textarea name="Comments" id="Comments" rows="3" cols="55" onchange="dChge(this);"></textarea>
<input type="submit" name="addCall" id="addCall" value="Add Call" />
</div>
</div>
</div>

<div id="Application" class="tabSectionStyleIII" style="display:none;">
<div>
<input type="submit" name="updateApplication" id="updateApplication" value="Update Application Data" />	<p class="red">NOTE: fields with an asterisk (<span style="font-family:Georgia, 'Times New Roman', Times, serif;font-size:151%;">*</span>) are required <input type="hidden" name="data[formid]" id="data[formid]" onchange="dChge(this)" value="buy-now.requestform.php" /></p>
	<p>Name:<input type="text" name="FirstName" id="FirstName" onchange="dChge(this)" value="David" /> <input type="text" name="LastName" id="LastName" onchange="dChge(this)" value="Tester" /><span class="rqd">*</span><br />
	DOB:<input type="text" name="BirthDate" id="BirthDate" onchange="dChge(this)" value="" /> &nbsp;&nbsp; SSN:<input type="text" name="SocSecurityNumber" id="SocSecurityNumber" onchange="dChge(this)" value="" /><br />
	Address:<input type="text" name="HomeAddress" id="HomeAddress" onchange="dChge(this)" value="123 Main St. #D" /> <br />
	City:<input type="text" name="HomeCity" id="HomeCity" onchange="dChge(this)" value="Delgado" /> &nbsp; State:<select name="HomeState" id="HomeState" onchange="dChge(this)">
<option value="">&lt;Select..&gt;</option>
<option value="AL" >Alabama</option>
<option value="AK" >Alaska</option>
<option value="AB" >Alberta</option>
<option value="AS" >American Somoa</option>
<option value="AZ" >Arizona</option>
<option value="AR" >Arkansas</option>
<option value="AE" >Armed Forces Europe</option>
<option value="AP" >Armed Forces Pacific</option>
<option value="AA" >Armed Forces the Americas</option>
<option value="BC" >British Columbia</option>
<option value="CA" >California</option>
<option value="CO" >Colorado</option>
<option value="CT" >Connecticut</option>
<option value="DE" >Delaware</option>
<option value="DC" >District of Columbia</option>
<option value="FM" >Federated States of Micronesia</option>
<option value="FL" >Florida</option>
<option value="GA" >Georgia</option>
<option value="GU" >Guam</option>
<option value="HI" >Hawaii</option>
<option value="ID" >Idaho</option>
<option value="IL" >Illinois</option>
<option value="IN" >Indiana</option>
<option value="IA" >Iowa</option>
<option value="KS" >Kansas</option>
<option value="KY" >Kentucky</option>
<option value="LA" >Louisiana</option>
<option value="ME" >Maine</option>
<option value="MB" >Manitoba</option>
<option value="MH" >Marshall Islands</option>
<option value="MD" >Maryland</option>
<option value="MA" >Massachusetts</option>
<option value="MI" >Michigan</option>
<option value="MN" >Minnesota</option>
<option value="MS" >Mississippi</option>
<option value="MO" >Missouri</option>
<option value="MT" >Montana</option>
<option value="NE" >Nebraska</option>
<option value="NV" >Nevada</option>
<option value="NB" >New Brunswick</option>
<option value="NH" >New Hampshire</option>
<option value="NJ" >New Jersey</option>
<option value="NM" >New Mexico</option>
<option value="NY" >New York</option>
<option value="NL" >Newfoundland and Labrador</option>
<option value="NC" >North Carolina</option>
<option value="ND" >North Dakota</option>
<option value="NT" >Northwest Territories</option>
<option value="NS" >Nova Scotia</option>
<option value="NU" >Nunavut</option>
<option value="OH" >Ohio</option>
<option value="OK" >Oklahoma</option>
<option value="ON" >Ontario</option>
<option value="OR" >Oregon</option>
<option value="UN" >Outside US/Canada</option>
<option value="PW" >Palau</option>
<option value="PA" >Pennsylvania</option>
<option value="PE" >Prince Edward Island</option>
<option value="PR" >Puerto Rico</option>
<option value="QC" >Quebec</option>
<option value="RI" >Rhode Island</option>
<option value="SK" >Saskatchewan</option>
<option value="SC" >South Carolina</option>
<option value="SD" >South Dakota</option>
<option value="TN" >Tennessee</option>
<option value="TX" selected>Texas</option>
<option value="UT" >Utah</option>
<option value="VT" >Vermont</option>
<option value="VI" >Virgin Islands</option>
<option value="VA" >Virginia</option>
<option value="WA" >Washington</option>
<option value="WV" >West Virginia</option>
<option value="WI" >Wisconsin</option>
<option value="WY" >Wyoming</option>
<option value="YT" >Yukon Territory</option>
</select> &nbsp; Zip:<input type="text" name="HomeZip" id="HomeZip" onchange="dChge(this)" value="78101" /><br />
	Phone:<input type="text" name="HomeMobile" id="HomeMobile" onchange="dChge(this)" value="512-754-0004" />  &nbsp; E-mail Address:<input type="text" name="Email" id="Email" onchange="dChge(this)" value="sf04@relatebase.com" /><br />
		<h3>Employer Data</h3>
	Previous Employer Name:<input type="text" name="data[previousEmployerName]" id="data[previousEmployerName]" onchange="dChge(this)" value="Huntsville Penitentiary" /> <span class="gray">(The full company name)</span><br />
	Previous Employer Address:<input type="text" name="data[previousEmployerAddress]" id="data[previousEmployerAddress]" onchange="dChge(this)" value="123 Main St. #4" /><br />
	City: <input type="text" name="data[previousEmployerCity]" id="data[previousEmployerCity]" onchange="dChge(this)" value="Huntsville" />&nbsp;&nbsp;State:<select name="data[previousEmployerState]" id="data[previousEmployerState]" onchange="dChge(this)">
<option value="">&lt;Select..&gt;</option>
<option value="AL" selected>Alabama</option>
<option value="AK" >Alaska</option>
<option value="AB" >Alberta</option>
<option value="AS" >American Somoa</option>
<option value="AZ" >Arizona</option>
<option value="AR" >Arkansas</option>
<option value="AE" >Armed Forces Europe</option>
<option value="AP" >Armed Forces Pacific</option>
<option value="AA" >Armed Forces the Americas</option>
<option value="BC" >British Columbia</option>
<option value="CA" >California</option>
<option value="CO" >Colorado</option>
<option value="CT" >Connecticut</option>
<option value="DE" >Delaware</option>
<option value="DC" >District of Columbia</option>
<option value="FM" >Federated States of Micronesia</option>
<option value="FL" >Florida</option>
<option value="GA" >Georgia</option>
<option value="GU" >Guam</option>
<option value="HI" >Hawaii</option>
<option value="ID" >Idaho</option>
<option value="IL" >Illinois</option>
<option value="IN" >Indiana</option>
<option value="IA" >Iowa</option>
<option value="KS" >Kansas</option>
<option value="KY" >Kentucky</option>
<option value="LA" >Louisiana</option>
<option value="ME" >Maine</option>
<option value="MB" >Manitoba</option>
<option value="MH" >Marshall Islands</option>
<option value="MD" >Maryland</option>
<option value="MA" >Massachusetts</option>
<option value="MI" >Michigan</option>
<option value="MN" >Minnesota</option>
<option value="MS" >Mississippi</option>
<option value="MO" >Missouri</option>
<option value="MT" >Montana</option>
<option value="NE" >Nebraska</option>
<option value="NV" >Nevada</option>
<option value="NB" >New Brunswick</option>
<option value="NH" >New Hampshire</option>
<option value="NJ" >New Jersey</option>
<option value="NM" >New Mexico</option>
<option value="NY" >New York</option>
<option value="NL" >Newfoundland and Labrador</option>
<option value="NC" >North Carolina</option>
<option value="ND" >North Dakota</option>
<option value="NT" >Northwest Territories</option>
<option value="NS" >Nova Scotia</option>
<option value="NU" >Nunavut</option>
<option value="OH" >Ohio</option>
<option value="OK" >Oklahoma</option>
<option value="ON" >Ontario</option>
<option value="OR" >Oregon</option>
<option value="UN" >Outside US/Canada</option>
<option value="PW" >Palau</option>
<option value="PA" >Pennsylvania</option>
<option value="PE" >Prince Edward Island</option>
<option value="PR" >Puerto Rico</option>
<option value="QC" >Quebec</option>
<option value="RI" >Rhode Island</option>
<option value="SK" >Saskatchewan</option>
<option value="SC" >South Carolina</option>
<option value="SD" >South Dakota</option>
<option value="TN" >Tennessee</option>
<option value="TX" >Texas</option>
<option value="UT" >Utah</option>
<option value="VT" >Vermont</option>
<option value="VI" >Virgin Islands</option>
<option value="VA" >Virginia</option>
<option value="WA" >Washington</option>
<option value="WV" >West Virginia</option>
<option value="WI" >Wisconsin</option>
<option value="WY" >Wyoming</option>
<option value="YT" >Yukon Territory</option>
</select>	&nbsp; Zip:<input type="text" name="data[previousEmployerZip]" id="data[previousEmployerZip]" onchange="dChge(this)" value="33018" size="5" /> <br />
	Company Telephone Number:<input type="text" name="data[companyPhone]" id="data[companyPhone]" onchange="dChge(this)" value="512-878-0004" /> &nbsp;&nbsp; Company FAX Number:<input type="text" name="data[companyFax]" id="data[companyFax]" onchange="dChge(this)" value="" /><span class="gray">(optional)</span><br />
	Company E-mail Address:<input type="text" name="data[companyEmail]" id="data[companyEmail]" onchange="dChge(this)" value="sf23@relatebase.com" /><br />
	Name of Immediate Supervisor:<input type="text" name="data[supervisorName]" id="data[supervisorName]" onchange="dChge(this)" value="Oscar Fullman" /><br />
	Supervisor&rsquo;s Phone Number:<input type="text" name="data[supervisorPhone]" id="data[supervisorPhone]" onchange="dChge(this)" value="same" /><br />
Supervisor&rsquo;s E-mail Address:<input type="text" name="data[supervisorEmail]" id="data[supervisorEmail]" onchange="dChge(this)" value="sf23@relatebase.com" size="35" /><br />
	
	<p><strong>Under what conditions did you leave this company</strong>? <span class="gray">(be detailed)</span><br />
	<textarea name="data[conditionsForLeaving]" id="data[conditionsForLeaving]" onchange="dChge(this)" rows="4" cols="50"></textarea><br />
	<br />
	<strong>Describe your position</strong>:<br />
	<textarea name="data[yourPosition]" id="data[yourPosition]" onchange="dChge(this)" rows="3" cols="50"></textarea><br />
	<br />
	How long were you with the company?  <input type="text" name="data[employStart]" id="data[employStart]" onchange="dChge(this)" value="" size="5" /> to <input type="text" name="data[employEnd]" id="data[employEnd]" onchange="dChge(this)" value="" size="5" /></p>
<p>Briefly list up to 3 accomplishments you believe this contact might or should cite when we contact them:<br />
	  <textarea name="data[yourCiteAccomplishments]" id="data[yourCiteAccomplishments]" onchange="dChge(this)" rows="3" cols="50"></textarea><br />
    <br />
	On a scale of 1-10, how likely is this employer or reference/likely to speak positively of you?<br /> 
  <input type="text" name="data[yourEstimate]" id="data[yourEstimate]" onchange="dChge(this)" value="" size="3" />&nbsp;  <span class="gray">(Where 1=likely extremely negative and 10=likely extremely positive)</span></p>
	
		
	<h3>Scheduling</h3>
	<p>When would you like us to start the call or emailing process?<br />
	  Date: <input type="text" name="data[processStart]" id="data[processStart]" onchange="dChge(this)" value="11/26/2013" size="12" /> and Time: <input type="text" name="data[processStartTime]" id="data[processStartTime]" onchange="dChge(this)" value="9:05AM" size="12" /><br />
<span class="gray">(This is when the email will be sent or the call will be made)</span></p>
	
	<h3>CLIENT RELEASE:</h3>
	<p>
	<span class="gray">(You must enter your first and last name twice to confirm your request)</span><br />
	I, <input type="text" name="data[fullName]" id="data[fullName]" onchange="dChge(this)" value="David Tester" />, Born on <input type="text" name="data[mmdd]" id="data[mmdd]" onchange="dChge(this)" value="1/4" size="3" />, 19<input type="text" name="data[yy]" id="data[yy]" onchange="dChge(this)" value="81" size="3" />, do hereby allow and permit RV, LLC and any of its executives or representatives to conduct a reference inquiry with any firm, company or corporation which I have been associated with or employed by as listed in my application, which I freely completed and signed electronically.  I do not hold anyone in this inquiry responsible or liable for the release of accurate, valid and truthful information concerning my conduct, whether positive or negative, at the firm being inquired upon on my behalf by RV, LLC. and reserve the right to seek remedies for anyone providing false or misleading information concerning this inquiry.  I have the express right and privilege to review any and all information gathered by RV, LLC.</p>
	<p>Signed this day, November 27th, 2013.</p>
	<p><input type="text" name="data[signature]" id="data[signature]" onchange="dChge(this)" value="David Tester" size="50" /><br />
	RV, LLC Client </p></div>
</div>

<div id="refcheckResponse" class="tabSectionStyleIII" style="display:none;">
<div>

<input type="submit" name="updateRefcheck" id="updateRefcheck" value="Update Reference Check" />
	&nbsp;&nbsp;
	<input type="submit" name="finalizeRefcheck" id="finalizeRefcheck" value="Finalize Reference Check" />
	<div id="generalform">
	<h1>Executive Reference Check (52 Points) </h1>
	<p>We would appreciate  your honest evaluation of this person on the questions below.   Therefore, please complete this short reference inquiry concerning  this previous employee <strong>David Tester</strong> who was  previously employed with your company. </strong> </p>
	<p><strong>David Tester  has signed the following  release:</strong></p>
	<div class="signature">
	<p>I, <strong>David Tester</strong>, Born on <strong>1/4</strong>, 19 <strong>81</strong>, do  hereby allow and permit RV, LLC and any of its executives or  employees to conduct a background inquiry with any firm, company or  corporation which I have been associated with or employed by as  listed in the client application, which I freely completed and signed  electronically.  I do not hold anyone in this inquiry responsible or  liable for the release of accurate, valid and truthful information  concerning my conduct, whether positive or negative, at the firm  being inquired upon on my behalf by RV, LLC.  I have the express  right and privilege to review any and all information gathered by RV,  LLC.</p>
	<p>Signed this day, 11/26/2013.</p>
	<p><strong>David Tester</strong></p>
	<p>RV,  LLC Client </p>
	</div>
	<h2> EMPLOYEE INFORMATION</h2>
	<p> 1. Dates of Verifiable Employment:<br />
	  FROM:<input type="text" name="refcheck[datefrom]" id="refcheck[datefrom]" onchange="dChge(this)" value="" size="6" /><br />
  TO: <input type="text" name="refcheck[dateto]" id="refcheck[dateto]" onchange="dChge(this)" value="" size="6" /></p>
	<h2> JOB ASSIGNMENT</h2>
	<p> 2. Department/Section/Division Employee was assigned to: <input type="text" name="refcheck[divisionassignedto]" id="refcheck[divisionassignedto]" onchange="dChge(this)" value="" size="6" /><br />
	  3. Was Employee promoted to this position or was he hired for this position? <input type="text" name="refcheck[promotedorhired]" id="refcheck[promotedorhired]" onchange="dChge(this)" value="" size="6" /><br />
	  4. If promoted, what position did he/she hold before the promotion? <input type="text" name="refcheck[positionheldbefore]" id="refcheck[positionheldbefore]" onchange="dChge(this)" value="" size="6" /><br />
	  5. What was the date of the promotion? <input type="text" name="refcheck[dateofpromotion]" id="refcheck[dateofpromotion]" onchange="dChge(this)" value="" size="6" /></p>
	<h2> SEPARATION</h2>
	<p> 6. When the Employee left the company, did Employee Resign? <input type="text" name="refcheck[didemployeeresign]" id="refcheck[didemployeeresign]" onchange="dChge(this)" value="" size="6" /><br />
	  7. Was he/she asked to resign in lieu of involuntary separation? <input type="text" name="refcheck[askedtoresign]" id="refcheck[askedtoresign]" onchange="dChge(this)" value="" size="6" /><br />
	  8. What was the issue(s) causing the separation?<br />
	  <textarea name="refcheck[issueforseperation]" id="refcheck[issueforseperation]" onchange="dChge(this)" cols="30" rows="3"></textarea><br />
	  9. Was Employee removed from his/her position? <input type="text" name="refcheck[wasemployeeremoved]" id="refcheck[wasemployeeremoved]" onchange="dChge(this)" value="" size="6" /><br />
	  10. What was the issue(s) causing the removal?<br />
	  <textarea name="refcheck[issueforremoval]" id="refcheck[issueforremoval]" onchange="dChge(this)" cols="30" rows="3"></textarea><br />
	  11. Did the Employee appeal to Human Resources/higher entity within the company? <input type="text" name="refcheck[appealtoHR]" id="refcheck[appealtoHR]" onchange="dChge(this)" value="" size="6" /><br />
	  12. What was the outcome of the appeal?<br />
	  <textarea name="refcheck[outcomeofappeal]" id="refcheck[outcomeofappeal]" onchange="dChge(this)" cols="30" rows="3"></textarea></p>
	<h2> ATTENDANCE</h2>
	<p> 13. Was Employee ever tardy/late for work? <input type="text" name="refcheck[tardyorlate]" id="refcheck[tardyorlate]" onchange="dChge(this)" value="" size="6" /><br />
	  14. Did Employee have any unexcused absences? <input type="text" name="refcheck[unexcusedabsences]" id="refcheck[unexcusedabsences]" onchange="dChge(this)" value="" size="6" /><br />
	  15. How many unexcused absences did the Employee have (on average) per year? <input type="text" name="refcheck[numberofunexcusesdabsences]" id="refcheck[numberofunexcusesdabsences]" onchange="dChge(this)" value="" size="6" /></p>
	<h2> COWORKER RELATIONS</h2>
	<p> 16. Did the Employee have any known issues/conflicts with other personnel within the company? <input type="text" name="refcheck[knownconflicts]" id="refcheck[knownconflicts]" onchange="dChge(this)" value="" size="6" /><br />
	  17. If so, what was the nature of the conflict(s)?<br />
	  <textarea name="refcheck[natureofconflic]" id="refcheck[natureofconflic]" onchange="dChge(this)" cols="30" rows="3"></textarea><br />
	  18. Was there any allegation of racism on the part of the Employee? <input type="text" name="refcheck[racism]" id="refcheck[racism]" onchange="dChge(this)" value="" size="6" /><br />
	  19. If so, what were the circumstances?<br />
	  <textarea name="refcheck[circumstances]" id="refcheck[circumstances]" onchange="dChge(this)" cols="20" rows="2"></textarea><br />
	  20. How many total complaints did this Employee receive from fellow   coworkers during his/her tenure with the company? <input type="text" name="refcheck[totalcomplaints]" id="refcheck[totalcomplaints]" onchange="dChge(this)" value="" size="6" /></p>
	<h2> DRESS &amp; APPEARANCE</h2>
	<p> 21. Did the Employee ever fail to meet the minimum standards for dress and appearance? <input type="text" name="refcheck[standardofappearance]" id="refcheck[standardofappearance]" onchange="dChge(this)" value="" size="6" /><br />
	  22. If not, what were the issues?<br />
	  <textarea name="refcheck[dressissues]" id="refcheck[dressissues]" onchange="dChge(this)" cols="20" rows="2"></textarea></p>
	<h2> COMPANY VEHICLE</h2>
	<p> 23. Was Employee provided a company vehicle/assigned company vehicle to   operate for company business? <input type="text" name="refcheck[companyvehicle]" id="refcheck[companyvehicle]" onchange="dChge(this)" value="" size="6" /><br />
	  24. Did Employee practice safe operation of said vehicle while operating it? <input type="text" name="refcheck[carsafety]" id="refcheck[carsafety]" onchange="dChge(this)" value="" size="6" /><br />
	  25. Was Employee ever involved in a motor vehicle crash/accident with   any company vehicle he/she operated? <input type="text" name="refcheck[motoraccident]" id="refcheck[motoraccident]" onchange="dChge(this)" value="" size="6" /></p>
	<h2> LEADERSHIP QUALITIES</h2>
	<p> 33. Did Employee exhibit good leadership skills and talents? <input type="text" name="refcheck[leadshership]" id="refcheck[leadshership]" onchange="dChge(this)" value="" size="3" /><br />
	  34. Was the Employee a &ldquo;leader&rdquo; or a &ldquo;manager&rdquo; to those under him/her? <input type="text" name="refcheck[leader]" id="refcheck[leader]" onchange="dChge(this)" value="" size="3" /><br />
	  35. Did subordinates respond positively to Employee? <input type="text" name="refcheck[subordinates]" id="refcheck[subordinates]" onchange="dChge(this)" value="" response="" size="3" /><br />
	  36. Was Employee able to adequately and effectively motivate his/her subordinates? <input type="text" name="refcheck[motivatesubordinates]" id="refcheck[motivatesubordinates]" onchange="dChge(this)" value="" size="3" /><br />
	  37. Did Employee take a proactive measures in successfully dealing with   conflicts between subordinates? <input type="text" name="refcheck[conflictability]" id="refcheck[conflictability]" onchange="dChge(this)" value="" size="3" /><br />
	  38. Was Employee able to effectively manage time? <input type="text" name="refcheck[timemanagement]" id="refcheck[timemanagement]" onchange="dChge(this)" value="" size="3" /><br />
	  39. Could Employee handle more than one task at a time? <input type="text" name="refcheck[multitask]" id="refcheck[multitask]" onchange="dChge(this)" value="" size="3" /><br />
	  40. Did Employee take constructive criticism from both his superiors as   well as subordinates? <input type="text" name="refcheck[constructivecriticism]" id="refcheck[constructivecriticism]" onchange="dChge(this)" value="" size="3" /></p>
	<h2> GOALS &amp; INITIATIVES</h2>
	<p> 41. On a scale from 1 to 10 with 10 being the most positive number, how   would you rate the overall performance level of the Employee?<br />
	  <input type="text" name="refcheck[overallperformance]" id="refcheck[overallperformance]" onchange="dChge(this)" value="" size="1" /><br />
	  42. On a scale from 1 to 10 with 10 being the most acceptable number,   how would you rate the overall attendance record of the Employee?<br />
	  <input type="text" name="refcheck[overallattendance]" id="refcheck[overallattendance]" onchange="dChge(this)" value="" size="1" /><br />
	  43. On a scale from 1 to 10 with 10 being the strongest number, how   would you rate the overall attitude of the Employee?   <input type="text" name="refcheck[overallattitude]" id="refcheck[overallattitude]" onchange="dChge(this)" value="" size="1" /><br />
	  44. On a scale from 1 to 10 with 10 being the most complimentary   number, how would you rate the overall character level of the Employee?<br />
	  <input type="text" name="refcheck[character]" id="refcheck[character]" onchange="dChge(this)" value="" size="1" /><br />
	  45. With 10 being the most suitable number, how would you rate the   Employee&rsquo;s overall ability to work positively with other workers during   his/her tenure?<br />
	  <input type="text" name="refcheck[workerpositivity]" id="refcheck[workerpositivity]" onchange="dChge(this)" value="" size="1" /><br />
	  46. On a scale from 1 to 10 with 10 being the most strongest number,   how would you rate the overall personality level of the Employee?<br />
	  <input type="text" name="refcheck[personality]" id="refcheck[personality]" onchange="dChge(this)" value="" size="1" /><br />
	  47. With 10 being the most satisfactory number, how would you rate the Employee&rsquo;s overall appearance during his/her tenure?<br />
	  <input type="text" name="refcheck[appearance]" id="refcheck[appearance]" onchange="dChge(this)" value="" size="1" /><br />
	  48. On a scale from 1 to 10 with 10 being the most acceptable number, how would your rate the Employee&rsquo;s trustworthiness?<br />
	  <input type="text" name="refcheck[trustworthiness]" id="refcheck[trustworthiness]" onchange="dChge(this)" value="" size="1" /><br />
	  49. Would you recommend your company rehire this Employee? <input type="text" name="refcheck[recommend]" id="refcheck[recommend]" onchange="dChge(this)" value="" size="1" /><br />
	  50. Why not?<br />
	  <textarea name="refcheck[yesornorecommend]" id="refcheck[yesornorecommend]" onchange="dChge(this)" cols="20" rows="2"></textarea><br />
	  51. Would someone else in the company recommend rehire for this Employee? <input type="text" name="refcheck[rehire]" id="refcheck[rehire]" onchange="dChge(this)" value="" size="1" /><br />
  52. Would you recommend another company hire this Employee? <input type="text" name="refcheck[recommendtoanothercompany]" id="refcheck[recommendtoanothercompany]" onchange="dChge(this)" value="" you="" for="" your="" assistance="" in="" inquiry="" of="" company="" representative="" completing="" questionnaire:="" [input:companyrep="" />  </p>
	<p>Date: <input type="text" name="refcheck[date]" id="refcheck[date]" onchange="dChge(this)" value="" size="10" />  </p>
	<p>&nbsp;</p>
	<p class="gray">The purpose of this inquiry by Reliant  Verification, LLC (RV, LLC)  is to obtain information on the former employee listed on this  release.  The information obtained by RV, LLC   will not be used for any other purpose than stated herein and all  information is held in strict confidence by our firm.   Our clients  have the option of reviewing the information your firm, its Human  Resource Department, any supervisor, coworkers, friends or other  individual may provide to this firm. </p>
</div></div>
</div>

<div id="Documents" class="tabSectionStyleIII" style="display:none;">
<div>
documents associated with this ticket will be located here
</div>
</div>

<div id="Help" class="tabSectionStyleIII" style="display:none;">
<div>
<pre>Array
(
  [identity] =&gt; Guest
  [createDate] =&gt; 2013-10-09 17:02:04
  [editDate] =&gt; 2013-10-29 12:37:34
  [loginTime] =&gt; 2013-11-27 17:18:47
  [sessionIP] =&gt; 24.155.110.33
  [cnx] =&gt; Array
    (
      [cpm210] =&gt; Array
        (
          [acct] =&gt; cpm210
          [identity] =&gt; Guest
          [primaryKeyField] =&gt; ID
          [primaryKeyValue] =&gt; 5
          [companyTableName] =&gt; finan_clients
          [companyPrimaryKeyField] =&gt; ID
          [companyPrimaryKeyValues] =&gt; Array
            (
              [6] =&gt; primary
            )
          [defaultClients_ID] =&gt; 6
          [hostName] =&gt; localhost
          [status] =&gt; 50
          [localStatusField] =&gt; RBStatus
          [localStatus] =&gt; 50
          [idLevel] =&gt; 0
          [accesses] =&gt; Array
            (
              [1] =&gt; db admin
              [3] =&gt; admin
            )
        )
    )
  [pJ] =&gt; Array
    (
      [componentsRegisteredSession] =&gt; 1385598347
      [componentFiles] =&gt; Array
        (
          [verification] =&gt; Array
            (
              [data] =&gt; Array
                (
                  [default] =&gt; Array
                    (
                      [verifyCSS] =&gt; #consoleMenu{
	margin-top:5px;
	}
#consoleMenu li{
	background-color:#99B27F;
	border-radius:5px;
	padding:5px 7px;
	margin-right:7px;
	}
#consoleMenu li a{
	text-decoration:none;
	color:#000;
	}
#consoleMenu li a:hover{
	color:#fff;
	}
#step1-position{
	width:650px;
	background-color:#DAE2D5;
	border-radius:15px;
	padding:20px 30px;
	}
#step1-position a{
	color:#FF33FF;
	}
.position{
	border-bottom:1px solid #444;
	padding-bottom:10px;
	margin-bottom:30px;
	}
.last{
	border-bottom:none;
	}
.leftOption{
	width:60%;
	}
.leftOption p{
	font-size:12px;
	line-height:normal;
	}
.button {	
	margin-top:75px
	
	}
.price{
	font-family:Georgia, &quot;Times New Roman&quot;, Times, serif;
	font-size:179%;
	color:darkgreen;
	}
.note{
	background-color:cornsilk;
	border-radius:10px;
	border:2px solid #99BD0C;
	padding:7px 12px;
	width:50%;
	margin:7px 0px;
	}
.verify{
	}
.rapid{
	}
.typeSection{
	margin-bottom:15px;
	border:1px solid #ccc;
	border-radius:10px;
	padding:10px 20px;
	}
                    )
                )
            )
        )
    )
)
</pre><pre>Array
(
  [data] =&gt; Array
    (
      [formid] =&gt; buy-now.requestform.php
      [previousEmployerName] =&gt; Huntsville Penitentiary
      [previousEmployerAddress] =&gt; 123 Main St. #4
      [previousEmployerCity] =&gt; Huntsville
      [previousEmployerState] =&gt; AL
      [previousEmployerZip] =&gt; 33018
      [companyPhone] =&gt; 512-878-0004
      [companyFax] =&gt; 
      [companyEmail] =&gt; sf23@relatebase.com
      [supervisorName] =&gt; Oscar Fullman
      [supervisorPhone] =&gt; same
      [supervisorEmail] =&gt; sf23@relatebase.com
      [conditionsForLeaving] =&gt; 
      [yourPosition] =&gt; 
      [employStart] =&gt; 
      [employEnd] =&gt; 
      [yourCiteAccomplishments] =&gt; 
      [yourEstimate] =&gt; 
      [processStart] =&gt; 11/26/2013
      [processStartTime] =&gt; 9:05AM
      [fullName] =&gt; David Tester
      [mmdd] =&gt; 1/4
      [yy] =&gt; 81
      [signature] =&gt; David Tester
    )
  [level] =&gt; 5
  [verif] =&gt; 3
  [ID] =&gt; 14
  [reference] =&gt; 1
)
</pre></div>
</div>
</div></div>
<!-- InstanceEndEditable --></div>
<div id="footer"><!-- InstanceBeginEditable name="footer" -->
&nbsp;
<!-- InstanceEndEditable --></div>
</form>
<div id="showTester" title="Javascript Tester" onClick="g('tester').style.display='block';">&nbsp;</div>
<div id="tester" >
	<a href="#" onClick="g('ctrlSection').style.display='block';return false;">Show Control Section</a><br />
	<textarea name="test" cols="65" rows="4" id="test">clear_form();</textarea><br />
	<input type="button" name="button" value="Test" onClick="jsEval(g('test').value);"><br />
	<textarea id="result" name="result" cols="65" rows="3" ></textarea>
</div>
<div id="ctrlSection" style="display:none">
	<iframe name="w1" src="/Library/js/blank.htm"></iframe>
	<iframe name="w2" src="/Library/js/blank.htm"></iframe>
	<iframe name="w3" src="/Library/js/blank.htm"></iframe>
	<iframe name="w4" src="/Library/js/blank.htm"></iframe>
</div>
</body>
<!-- InstanceEnd --></html>