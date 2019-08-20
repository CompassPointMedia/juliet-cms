<?php 
/*
*/
//identify this script/GUI
$localSys['scriptID']='generic';
$localSys['scriptVersion']='1.0';
$localSys['componentID']='main';


//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
$insertMode='insertCartSettings';
$updateMode='updateCartSettings';

if($a=q("SELECT
	u.Password,
	am.Settings, m.AdminSettings
	FROM rbase_userbase u, rbase_account a, rbase_AccountModules am, rbase_modules m WHERE
	u.UserName=a.AcctName AND
	a.AcctName='$acct' AND 
	a.ID=am.Account_ID AND 
	am.Modules_ID=m.ID AND 
	m.ID='$cartModuleId'", O_ARRAY, C_SUPER)){
	//kind of extra work
	if(count($a)==1){
		$a=$a[1];
	}else if(count($a)>1){
		exit('multiple modules');
	}else{
		exit('module ID#'.$cartModuleId.' not present');
	}
	$AdminSettings=unserialize(base64_decode($a['AdminSettings']));
	if(count($AdminSettings['_settings'])){
		$mode=$updateMode;
		$_settings=$AdminSettings['_settings'];
		$customTemplateString=$AdminSettings['customTemplateString'];
	}else{
		$mode=$updateMode;
		//proffer default values
		$_settings=array(
			
		);
		$customTemplateString=$AdminSettings['customTemplateString'];
	}
}

if(!function_exists('form_field_translator'))require($FUNCTION_ROOT.'/function_form_field_translator_v100.php');

if(!count($consoleEmbeddedModules)){
	mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,
	get_globals($err='we got at this page with no consoleEmbeddedModules array'),$fromHdrBugs);
	exit($err);
}else{
	foreach($consoleEmbeddedModules as $n=>$v){
		if($v['SKU']=='040'){
			if($cartModule){
				mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,
				get_globals($err='multiple cart modules for account'),$fromHdrBugs);
				exit($err);
			}else{
				$cartModule=$v;
			}
		}
	}
	if(!$cartModule){
		mail($developerEmail, 'Error file '.__FILE__.', line '.__LINE__,
		get_globals($err='no cart module for account'),$fromHdrBugs);
		exit($err);
	}
}
if(count($cartModule['moduleAdminSettings']['_settings'])){
	//OK
	extract($cartModule['moduleAdminSettings']['_settings']);
	$mode=$updateMode;
}else{
	$mode=$insertMode;
	if(file_exists($_SERVER['DOCUMENT_ROOT'].'/admin/cart_settings.php')){
		$transferringData=true;
		require($_SERVER['DOCUMENT_ROOT'].'/admin/cart_settings.php');
	}
}
$cartFlows=array(
	1=>'Basic flow',
	2=>'Standard flow',
	3=>'Extended flow'
);
$shippingTypes=array(
	1=>'No shipping',
	2=>'Fixed shipping',
	3=>'Variable shipping',
	4=>'Real-time quotes -UPS,USPS and Fedex'
);
$systemTable=array(
	1=>'addr_contacts',
	2=>'finan_accounts',
	3=>'finan_items',
	4=>'finan_header',
	5=>'finan_transactions',
	6=>'finan_billing',
	7=>'finan_invoiceorder'
);
//is this needed
$productTable= array(
	1=>'SKU',
	2=>'Name',
	3=>'Weight',
	4=>'Description',
	5=>'LongDescription'	
);
$stateList=array(
	1=>'Basic states',
	2=>'International'
);
$shippingProportionMethod=array(
	1=>'by the pound',
	2=>'by the price'
);


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="../Templates/reports_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Shopping Cart Settings</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" href="/Library/css/cssreset01.css" type="text/css" />
<link rel="stylesheet" href="rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">
.bq{
	margin-left:25px;
	border-left:1px dotted #ccc;
	padding-left:10px;
	}
</style>
<script language="JavaScript" type="text/javascript" src="/Library/js/jquery.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript">
/* periwinkle coding */
var thispage='<?php echo $thispage?>';
var thisfolder='<?php echo $thisfolder?>';
var ctime='<?php echo $ctime?>';
var PHPSESSID='<?php echo $PHPSESSID?>';
//for nav feature
var count='<?php echo $nullCount?>';
var ab='<?php echo $nullAbs?>';
</script>

<!-- following coding modified from ajaxloader.info - a long way to go to be modular -->
<script language="javascript" type="text/javascript">
</script>
<!-- InstanceEndEditable -->
</head>

<body id="report">
<?php if(!$suppressForm){ ?>
<form action="../console/resources/bais_01_exe.php" method="post" enctype="multipart/form-data" name="form1" target="w2" id="form1" onsubmit="return beginSubmit();">
<?php }?>
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->



<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->

<h1>Manage E-commerce/Cart Settings</h1>
<div class="fr">
	<input type="submit" name="Submit" value="Save Changes" />
	&nbsp;&nbsp;
	<input type="button" name="Submit2" value="Close" onclick="window.close();" />
</div>
<br />
<br />
<?php
ob_start(); //form_field_translator
if(false &&  $transferringData){
	?><div class="balloon1">
	Transferring from hard-coded file
	</div><?php
}
?>

<style type="text/css">
textarea{
	background-color:#F4EEDF;
	padding:5px;
	border:1px solid #999;
	}
fieldset{
	margin-top:15px;
	}
legend{
	font-size:119%;
	font-weight:900;
	letter-spacing:0.03em;
	}
#tabWrap{
	position:relative;
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
#lowerline{
	border-top:1px solid #444;
	clear:both;
	margin-top:-1px;
	background-color:#99CCFF;
	}
#tabRaise{
	position:absolute;
	top:-33px;
	left:15px;
	}
.tabSectionStyleII{
	padding:15px;
	border-left:1px solid #000;
	border-right:1px solid #000;
	border-bottom:1px solid #000;
	margin-bottom:10px;
	min-height:250px;
	}
#individualStatus{
	font-family:Georgia, "Times New Roman", Times, serif;
	color:darkgreen;
	font-size:larger;
	}
</style>
<script language="javascript" type="text/javascript">
if(false){
	var tabSections={1:'section1', 2:'section2',3:'section3'};
	var loadInviterComplete=false;
	function tabon(o){
		if(o.className=='tabon')return;
		for(var i in tabSections){
			g('tab'+i).className='taboff';
			g(tabSections[i]).style.display='none';
		}	
		var n=o.id.replace('tab','');
		g('tab'+n).className='tabon';
		g(tabSections[n]).style.display='block';
	}
}
</script>
<?php
ob_start(); //for tabs
?>
<p>

<fieldset>
<h3>
  <legend>Company Information</legend>
</h3>
<br />
<table>
<tr><td>Site Name:</td><td>[input:siteName ] </td></tr>
<tr><td>Company address:</td><td>[input:companyAddress ] </td></tr>
<tr><td>City:</td><td> [input:companyCity ] </td></tr>
<tr><td>State:</td><td> [input:companyState default='TX' ] </td></tr>
<tr><td>Zip:</td><td> [input:companyZip ] </td></tr>
<tr><td>Country:</td><td> [select:companyCountry options='USA' ] </td></tr>
<tr><td>Phone:</td><td> [input:companyPhone ] </td></tr>
<tr><td>Toll Free Phone:</td><td> [input:companyPhoneTollFree ] </td></tr>
<tr><td>Fax:</td><td> [input:companyFax ] </td></tr>
<tr><td>URL of the Website:</td><td> [input:siteURL ] </td></tr>
<tr><td colspan="100%"><span class="gray">example: tomsexoticplants.com (no www or http)</span> </td></tr>
<tr><td>Emails to send notifications to:</td><td> [input:adminEmail default='<?php echo $adminEmail;?>' ] </td></tr>
<tr><td>Reply-to email:</td><td> [input:replyEmail ] </td></tr>
<tr><td colspan="100%"><span class="gray">This is the email address that the order will show as coming from</span> </td></tr>
<tr><td>Minimum purchase amount (optional):</td><td> [input:minimumPurchase ] </td></tr>
<tr><td>Shopping cart page title:</td><td> [input:pageTitle ] </td></tr>
<tr><td colspan="100%"><span class="gray">example: Tom's Exotic Plant Heaven:: Rare and Exotic Plants For You!</span> </td></tr>
</table>
</fieldset>
<p>Secure Order Message/Insert:<br />
[textarea:secureOrderStatement rows=4 cols=50 ]</p>
<fieldset>
<legend>Disorganized miscellaneous items</legend>
  <p>
	Line item entry fields: [input:lineItemEntryFields ]<br />
	<span class="gray">This module adds secondary information fields after each line item on the order.  Must be found in modules/users</span><br />

	[checkbox:useLongOrderDetail label='Use long description below each line item in the order' ]	  </p><p>
	Field for long order detail: [input:longOrderDetailField default='LongDescription' ]
	</p><p>
	[checkbox:addMfrSKU label='Show manufacturer's part number if present' ]
	</p><p>
	[checkbox:allowQuantityModification label='Allow modification of quantity on line items' ]
	</p><p>
	<span class="gray">If not selected, user can only delete the line item.  Set this to not selected for items that need additional information per item such as color and other features</span>
	</p><p>
	Email logo (full path from your site root) [input:EmailLogo ]
	</p><p>
	<span class="gray">This will be a &quot;remote image&quot; in the email and may not show on some mail clients</span>
	</p><p>

	Email header (HTML):
	</p><p>
	[textarea:EmailHeader rows=5 cols=65 ]
	</p><p>
	<span class="gray">HTML only, no PHP code (will not be eval'ed)</span>
	</p><p>
	Email source file: [input:emailSource ]
	</p><p>
	<span class="gray">This must be in the emails folder if declared; contact administrator to set this up.</span>
	</p><p>&nbsp;</p>
</fieldset>
<br />
<br />
<fieldset>
<legend>Customer Sign-In Settings</legend>
  Allow sign-in/new creation: <select name="_settings[allowSignin]" id="_settings[allowSignin]" onchange="dChge(this);">
	<option value="0" <?php echo $_settings['allowSignin']==='0'?'selected':''?>>No (anonymous only, not recommended)</option>
	<option value="1" <?php echo $_settings['allowSignin']==1 || $mode==$insertMode ? 'selected':''?>>Yes</option>
	<option value="2" <?php echo $_settings['allowSignin']==2?'selected':''?>>Between shipping and payment steps</option>
	<!-- 
	<option value="4" <?php echo $_settings['allowSignin']==4?'selected':''?>>[reserved]</option> 
	-->
	<option value="8" <?php echo $_settings['allowSignin']==8?'selected':''?>>Force sign-in/new account</option>
	</select>
  <br />
  Path to sign-in application: [input:signinRoot default='<?php echo $_settings['siteURL'] ? 'http://www.'.$_settings['siteURL'].'/cgi/' : ''?>' size=40 ]<br />
	<span class="gray">(example http://www.mysite.com/cgi/ note that the www is normally included)</span>
  </p><p>
	New account page: [input:newSignupLocation default=usemod ]
	</p><p>
	Existing account sign-in page: [input:currentSigninLocation default=login ]
	</p><p>
	Sign-in message:
	</p><p>
	[textarea:customSigninMessage cols=65 rows=4 default='To speed up future orders, please {register}register{/register} or {signin}sign in{/signin}' ]
	</p><p>
	<span class="gray">Must have bbtags as follows: [signin]Sign In[/signin] and [register]Create a new account[/register] - these will become links</span>
	</p><p>
	New account message handle: [input:insertAccountFormHeader default='beforeCheckout' ]
	</p><p>
	<span class="gray">default value is 'beforeCheckout' which is recognized by the Juliet CGI system</span>
	</p><p>
	[checkbox:allowHeaderUpdate label='Provide a checkbox to update my personal information to this address in-cart' ]
	</p><p>
</fieldset>
<br />
<fieldset><legend>Tax Settings</legend>
Taxable State: [input:taxableState size=5 ]
</p>
<p> <span class="gray">Use two letters, e.g. TX, GA, HI</span> </p>
<p> Tax amount (decimal, e.g. .0825): [input:tax size=8 ] </p>
</fieldset>
<br />
<fieldset>
<legend>Content</legend>
  State list:
  <select name="_settings[stateList]" id="stateList" onchange="dChge(this);">
	<option value="">&lt;Select..&gt;</option>
	<?php
	foreach($stateList as $idx=>$list){
		?><option value="<?php $idx?>" <?php if($_settings['stateList']==$idx)echo 'selected';?>><?php echo h($list);?></option><?php
	}
	?>
  </select>
</fieldset>
</p>
<br />
<fieldset>
<legend>Product Fields</legend>
  Retail price field:&nbsp;[input:retailPriceField default='UnitPrice' ]
  </p><p>[checkbox:wholesaleLogin label='Allow wholesale login' ]
	</p>
  <p>
	Wholesale price field: [input:wholesalePriceField default='WholesalePrice' ]	  </p>
  <p>Minimum Wholesale Order Amount [input:minimumWholesaleOrder ] <br />
	<span class="gray">(optional, only applies to wholesale purchase)</span>
	</fieldset>
<br />
<br />
<h3>Miscellaneous</h3>
New customer category for orders made: [input:customerCategory default='Website Order' ]
<br />
<br />
Pre-cart evaluator module: 
<select name="_settings[preCartEvaluator]" id="_settings[preCartEvaluator]" onchange="dChge(this);">
  <option value="">&lt;Select..&gt;</option>
  <?php
$dir='/home/rbase/public_html/c/cart/en/v500/modules/users/'.$acct.'-'.$cartModuleId;
if(is_dir($dir) && $fp=opendir($dir))
while(false!==($file=readdir($fp))){
	if(!stristr($file,'.php'))continue;
	?><option value="<?php echo $file;?>" <?php echo $_settings['preCartEvaluator']==$file?'selected':''?>><?php echo str_replace('.php','',$file);?></option><?php
}
?>
</select>
<br />
<span class="gray">This component will evaluate the cart contents before the page is shown. Contact administrator if no components are seen and you need this feature.</span>
<p>
  Post SQL Entry Module: [input:postSQLEntryModule ]
  <br />
  <span class="gray">This is the module that will run after all order SQL is processed. Must be found in modules/users. Contact administrator to add or modify this file</span><br />
  <br />
  <br />
  Financial Storage Method: 
  <select name="_settings[accountingVersion]" id="_settings[accountingVersion]" onchange="dChge(this);">
	<option value="1.0">Version 1.0 - non-normalized</option>
	<option value="2.0" <?php echo $_settings['accountingVersion']==2.0?'selected':''?>>Version 2.0 - using finan_headers</option>
  </select>
</p>
<br />
<p>Additional Cart CSS:<br />
  <span class="gray">Omit the &lt;style&gt;&lt;/style&gt; tags </span><br />
[textarea:customCartCSS rows=4 cols=50 ]</p>

<?php get_contents_tabsection('main');?>

<h3>NEW Shipping Settings - effective 10/28/2012<br />
Use [select:shippingVersion options='1:previous version, 2:Version 2.0 (current version)' :noblankoption ]	  </h3>
<p><span class="gray">These are more streamlined settings and will eventually interact with shipping settings for individual products</span></p>
<p>Basic shipping method you use (this is referred to as your <strong>Shipping Scheme</strong>):<br />
<br />
<label>[radio:shippingScheme :nolabel option=10 default=10 ] <span class="big"><strong>None</strong></span>.  I don't use shipping. (For digital products for example it doesn't even apply) Don't even mention it.</label><br />
<br />
<label>[radio:shippingScheme :nolabel option=20 ] <strong>FREE</strong> shipping.  Shipping cost is built into my products. Mention this on the shopping cart (if it contains shippable items).</label>
</p>
<div class="bq">
Short message: [input:shippingAlwaysFreeShortStatement default='FREE SHIPPING!' ]<br />
Longer message:<br />
[textarea:shippingAlwaysFreeLongStatement default='You never pay shipping with us' rows=2 cols=45 ]<br />
</div>
<p>
<label>[radio:shippingScheme :nolabel option=30 ] Free above a certain price:</label>
<br />		  
<span class="gray">(do you need this to be net profit vs. gross price? Contact Compass Point Media for assistance!)</span> <br />
<div class="bq">
Free shipping price point (dollars and cents): [input:shippingFreePrice size=10 ]<br />
<span class="gray">This is the dollar value (USD) at which shipping is free</span>
<br />	
No shipping encouragement message: [input:noShippingMessage default='No shipping charges on orders over <?php echo $_settings['shippingFreePrice'] ? number_format($_settings['shippingFreePrice'],2) : '$50.00';?>' size=45 ]<br />
<span class="gray">(This is the message when free shipping is available but they have not reached that point)</span><br />
You can use the following variables in your message: <code class="code red">{priceremaining}</code> and <code class="code red">{shippingfreeprice}</code>.
<br />
Only mention this when their basket value is within [input:shippingFreeMentionPrice size=10 ] of the shipping free price (above)<br />
<span class="gray">(use either a number value or a % value such as 75%)</span></div>
<br />

  
  
<label>[radio:shippingScheme :nolabel option=40 ] I always charge for shipping. No free shipping deals at any price level</label>

<br />
<br />
<fieldset>
<legend>Shipping  Settings</legend>
Shipping Type:
<select name="_settings[shippingType]" id="_settings[shippingType]" onchange="dChge(this);">
  <option value="">&lt;Select..&gt;</option>
  <?php
	foreach($shippingTypes as $idx=>$type){
		?>
  <option value="<?php echo $idx?>" <?php if($_settings['shippingType']==$idx)echo 'selected';?>><?php echo h($type);?></option>
  <?php
	}
	?>
</select>
<br />
</p>
<p> Shipping base price: [input:shippingBasePrice size=10 ] </p>
<p> <span class="gray">This is the fixed price for fixed-price shipping, or the &quot;base price&quot; to start calculations from for other shipping types</span> </p>
<p> Shipping proportion method:
  <select name="_settings[shippingProportionMethod]" id="_settings[shippingProportionMethod]">
      <option value="" >Select..</option>
      <option value="1" <?php if($_settings['shippingProportionMethod']==1)echo 'selected';?>>by the pound</option>
      <option value="2" <?php if($_settings['shippingProportionMethod']==2)echo 'selected';?>>by the price</option>
    </select>
</p>
<p> <span class="gray">This applies for variable-price shipping only</span> </p>
<p> <strong>Shipping Price per Pound or Cost:</strong> [input:shippingPricePer size=10 ] </p>
<p> <span class="gray">If per pound, specify an amount (e.g. 2.50); if per price, specify a decimal (e.g. .07 = $7.00 per $100.00 order)</span><br />
</p>
</fieldset>
<br />
<br />

<fieldset>
<h4>
  <legend>Shipping  Page Text</legend>
</h4>
<p>Shipping Page Title (main heading): [input:shippingInfoHeader default='Shipping Information' size=30 ] </p>
<p>Shipping Page Instructions (below main heading):<br />
  [textarea:shippingIntro  rows=5 cols=45 ] </p>
</fieldset>
<p>&nbsp; </p>
<h3>Live Shipping Values</h3>
Origin zip: [input:origin_zip size=10 ]
</p>
<p> <span class="gray">This is where the order is normally being shipped from</span> </p>
<p> [checkbox:shippingAssumeResidential label='Assume residential delivery (for UPS services only)' ] </p>
<p> Median destination zip: [input:median_destination_zip size=10 ] </p>
<p> <span class="gray">Before zip is entered by customer, this is used to <u>estimate</u> real-time shipping costs</span> </p>
<p> <br />
  Available Shippers and Shipping Methods:<br />
  <select name="_settings[shippingMethods][]" size="7" multiple="multiple" id="shippingMethods">
    <option value="" >Select..</option>
    <option value="1" <?php if(@in_array(1, $_settings['shippingMethods']))echo 'selected';?>>UPS Ground</option>
    <option value="2" <?php if(@in_array(2, $_settings['shippingMethods']))echo 'selected';?>>UPS 3 Day Select</option>
    <option value="3" <?php if(@in_array(3, $_settings['shippingMethods']))echo 'selected';?>>UPS 2nd Day Air</option>
    <option value="4" <?php if(@in_array(4, $_settings['shippingMethods']))echo 'selected';?>>UPS 2nd Day Air AM</option>
    <option value="5" <?php if(@in_array(5, $_settings['shippingMethods']))echo 'selected';?>>UPS Next Day Air Saver</option>
    <option value="6" <?php if(@in_array(6, $_settings['shippingMethods']))echo 'selected';?>>UPS Next Day Air</option>
    <option value="10" <?php if(@in_array(10, $_settings['shippingMethods']))echo 'selected';?>>Fedex Second Day Air</option>
    <option value="20" <?php if(@in_array(20, $_settings['shippingMethods']))echo 'selected';?>>USPS Priority Mail</option>
  </select>
  <br />
  <span class="gray">If you select multiple shipping methods, these will be available to the customer. (If you do not see a shipper/method that you use, contact Compass Point Media)</span> <br />
  <br />
  Select the default shipping method from the list above:
  <select name="_settings[shippingDefaultMethod]" id="shippingDefaultMethod">
    <option value="" >Select..</option>
    <option value="1" <?php if($_settings['shippingDefaultMethod']==1)echo 'selected';?>>UPS Ground</option>
    <option value="2" <?php if($_settings['shippingDefaultMethod']==2)echo 'selected';?>>UPS 3 Day Select</option>
    <option value="3" <?php if($_settings['shippingDefaultMethod']==3)echo 'selected';?>>UPS 2nd Day Air</option>
    <option value="4" <?php if($_settings['shippingDefaultMethod']==4)echo 'selected';?>>UPS 2nd Day Air AM</option>
    <option value="5" <?php if($_settings['shippingDefaultMethod']==5)echo 'selected';?>>UPS Next Day Air Saver</option>
    <option value="6" <?php if($_settings['shippingDefaultMethod']==6)echo 'selected';?>>UPS Next Day Air</option>
    <option value="10" <?php if($_settings['shippingDefaultMethod']==10)echo 'selected';?>>Fedex Second Day Air</option>
    <option value="20" <?php if($_settings['shippingDefaultMethod']==20)echo 'selected';?>>USPS Priority Mail</option>
  </select>
</p>
<p> Shipping base weight (add to account for packaging): [input:shippingBaseWeight size=10 ] </p>
<p> Additional package weight: [input:additionalPackageWeight size=10 ] </p>
<p> Alternate Minimum Shipping Price: [input:shippingMinimumPrice size=10 ] </p>
<p> [checkbox:hideDeliveryNotes checked label='Hide delivery notes field (this field allows customer to indicate where the shipment should be dropped off, etc.)' ] <br />
  Custom shipping module:
  <select name="_settings[customShippingModule]" id="_settings[customShippingModule]" onchange="dChge(this);">
      <option value="">&lt;Select..&gt;</option>
      <?php
	$dir='/home/rbase/public_html/c/cart/en/v500/modules/users/'.$acct.'-'.$cartModuleId;
	if(is_dir($dir) && $fp=opendir($dir))
	while(false!==($file=readdir($fp))){
		if(!stristr($file,'.php'))continue;
		?>
    <option value="<?php echo $file;?>" <?php echo $_settings['customShippingModule']==$file?'selected':''?>><?php echo str_replace('.php','',$file);?></option>
    <?php
	}
	?>
    </select>
  <br />
    <span class="gray">If selected, this component will override all other settings and custom-calculate shipping. Contact administrator if no components are seen and you need this feature.</span></p>
<h3>      Product Packaging Rules </h3>
<p>How products are packaged in real life is a function of their size, need to be together because of similar shape or function,  general settings and even location across multiple warehouses.  Here are the available settings for packaging (grouping) products together:<br />
[select:productPackaging options='1:Package and ship each product in individual boxes, 8:Package all together unless items.Package=0 (do not package me with other items), 32:Package all together regardless of items.Package value, 128{disabled}:Use custom logic coding for packaging' :noblankoption ]      </p>


<br />
Default value of <code class="code green">Package</code> field in items table: [select:defaultPackageFieldValue options='-1:I am a digital or non-physical product, 0:Do not package me with other items, 1:OK to package me with other items' ] <br />
</p>

<?php get_contents_tabsection('shipping');?>
<p>Payment Page Title (main heading): [input:paymentInfoHeader default='Payment Information' size=30 ] </p>
<p>Payment Page Instructions (below main heading): <br />
  [textarea:paymentIntro  rows=5 cols=45 ]</p>

<p>[checkbox:allowPaypal label='Accept PayPal' ]</p>
</p>
<p> Paypal integration type: [input:PaypalIntegrationType size=20 ] </p>
<p> Paypal account email: [input:PaypalAccountEmail size=30 ] </p>
<p> Base paypal action: [input:basePaypalAction size=35 value='https://www.paypal.com/cgi-bin/webscr' ] </p>
<p> Encrypted field value: </p>
<p> [textarea:encryptedFieldValue cols=50 rows=5 ] </p>
<p> <strong>Card information storage</strong>: <br />
  [checkbox:cardInfoDoNotStore value=1 label='Do not store' ]<br />
  [checkbox:cardInfoDoNotSend value=1 label='Do not send by email' ]<br />
</p>
<div class="balloon1"> <strong>NOTE</strong>: if you uncheck both these values and the customer pays by credit card AND a gateway is not set up, then the card information will <em>still be stored</em> in the database.  If you have any questions on this contact RelateBase </div>
</p>
<p> [checkbox:acceptCreditCards label='Cart will accept credit cards' ] </p>
<p> [checkbox:requireCardFullAddress label='Require card full address' ] </p>
<p> [checkbox:requireAVSNumber label='Require CVV for purchase' ] </p>
<p> </p>
<p> Gateway Method: [input:gatewayMethod ] </p>
<p> Merchant ID: [input:merchantID ] </p>
<p> Merchant Key: [input:merchantKey ] </p>
<p> </p>
<p> <br />
  Billing Override code (optional): [input:overrideCode size=20 ] </p>
<p> <span class="gray">This code when entered by purchaser will not debit their card (treats order as an invoice vs. cash sale)</span></p>
<h3>Alternate Payment</h3>
<p>[checkbox:allowPaymentByCheck value=1 label='Allow payment by check' ]</p>
<p>Terms or conditions text for accepting checks:<br />
  [textarea:checkTerms cols=50 rows=3 ] </p>
<?php get_contents_tabsection('payment');?>
<h2>Layout Code</h2>
<p>This page is not without a few gotchas. If you recompile layout, you need to refresh this form before submitting it, or the layout as stored in the box below (prior to compiling) will again overwrite what you've compiled. </p>

<p>Site domain <span class="gray">(example: tomsexoticplants.com)</span>:<br />
<strong class="red">NO WWW AT FRONT!</strong> [input:siteDomain ]</p>
<p>Password protection token: [input:passwordProtectionToken ]	  </p>
<p><span class="gray">(Use this if the site is password protected when compiling or refreshing the cart layout)</span></p>
<p>[checkbox:customTemplate label='Create customized template from the website' ]</p>

<h4>Tools</h4>
<br />
<a onclick="return ow(this.href,'l2_cart','800,700');" href="https://www.relatebase.com/c/cart/en/v500/?acct=<?php echo $acct?>&amp;mid=<?php echo $cartModuleId?>">Click to view layout appearance</a><br />
<a onclick="return ow(this.href,'l2_cart','800,700');" href="https://www.relatebase.com/c/cart/en/v500/?acct=<?php echo $acct?>&amp;mid=<?php echo $cartModuleId?>&amp;refreshCustomTemplate=1">Click to recompile layout</a><br />
<a onclick="return ow(this.href,'l2_cart','800,700');" href="https://www.relatebase.com/c/cart/en/v500/?acct=<?php echo $acct?>&amp;mid=<?php echo $cartModuleId?>&amp;refreshCustomTemplate=1&amp;getAssetsOverride=1">Click to recompile layout AND re-acquire CSS and image assets<br />
</a>Blank regions for template: [input:blankBlocksInTemplate size=45]<br />
(separate by a comma, case sensitive) </p>
<textarea name="customTemplateString" id="customTemplateString" onchange="dChge(this);" cols="80" rows="25"><?php
echo h($customTemplateString);
?></textarea>

<?php get_contents_tabsection('layoutCode');?>

<?php ob_start(); ?>
<?php if(false){ ?><style type="text/css"><?php } ?>
body{padding:10px 20px;}
body, td{
	font-size:13px;font-family:Arial, Helvetica, sans-serif;
	}
#header{
	font-family:Georgia, "Times New Roman", Times, serif;
	}
#recipient{
	float:left;
	width:350px;
	border:1px dotted #ccc;
	padding:10px;
	}
.col{
	float:left;
	padding:0px 5px;
	margin-right:10px;
	}
#invoiceRef{
	float:left;
	margin-left:100px;
	width:150px;
	}
#invoiceRef table{
	border-collapse:collapse;
	}
#invoiceRef td{
	padding:3px 4px 1px 5px;
	border:1px dotted #ccc;
	}
#invoiceRef th{
	background-color:goldenrod;
	font-size:109%;
	font-weight:400;
	}
.vert th, .vert .mid{
	text-align:right;
	}
.vert td, .vert th{
	padding:3px 4px 1px 7px;
	}
.comment{background-color:oldlace; border:1px solid #333; padding:10px;}
<?php if(false){ ?></style><?php } ?>
<?php
$emailCSSDefault=ob_get_contents();
ob_end_clean();
if(!$_settings['emailCSS'])$_settings['emailCSS']=$emailCSSDefault;
?>
<strong>CSS for email</strong> <span class="gray">(background images can cause email to be treated as spam)</span><br />
[textarea:emailCSS cols=80 rows=25 class=tabby ]

<?php get_contents_tabsection('email');?>

<?php
//help section
?>
<h2>Help Under Development</h2>
<p>
You are seeing this page because you have the RelateBase E-Commerce &quot;Shopping Cart&quot; module installed on your site.  This page represents items you can change on the shopping cart including payment, shipping and tax options, and behavior related to how the cart handles sign-ins and more.  Contact 512-754-7927 for assistance with or questions about custom modules and components (some of these relate to provisioning subscriptions, setting up courses or tests, and other custom database coding)</p>
<p><strong>NOTE</strong>: the first tab is under development and expect to see a major upgrade in organization of the settings, along with better interactivity and explanation of each part.  </p>
<p>1/5/2012: Not able to pull the template i.e. regions showing up you don't want? Look at the new &quot;Blank Regions for Template&quot; field and submit changes before (re-)compiling the cart. Uses Juliet feature of declaring region output in query string </p>
<?php get_contents_tabsection('help');?>


<?php
tabs_enhanced(array(
	'main'=>array(
		'label'=>'Main Settings'
	),
	'layoutCode'=>array(
		'label'=>'Layout'
	),
	'shipping'=>array(
		'label'=>'Shipping'
	),
	'payment'=>array(
		'label'=>'Payment'
	),
	'email'=>array(
		'label'=>'Email'
	),
	'help'=>array(
		'label'=>'Help'
	),
));

?>





<input name="mode" type="hidden" id="mode" value="<?php echo $mode;?>" />
<?php
$form=ob_get_contents();
ob_end_clean();
echo form_field_translator($form, array(
	'arrayString'=>'_settings',
));
?>
  
<!-- InstanceEndEditable --></div>
<div id="footer"><!-- InstanceBeginEditable name="footer" -->
&nbsp;
<!-- InstanceEndEditable --></div>
<?php if(!$suppressForm){ ?>
</form>
<?php }?>
<?php if(!$hideCtrlSection){ ?>
<div id="showTester" title="Javascript Tester" onclick="g('tester').style.display='block';">&nbsp;</div>
<div id="tester" >
	<a href="#" onclick="g('ctrlSection').style.display='block';return false;">Show Control Section</a><br />
	<textarea name="test" cols="65" rows="4" id="test">clear_form();</textarea><br />
	<input type="button" name="button" value="Test" onclick="jsEval(g('test').value);"><br />
	<textarea id="result" name="result" cols="65" rows="3" ></textarea>
</div>
<div id="ctrlSection" style="display:<?php echo $testModeC ? 'block':'none'?>">
	<iframe name="w1" src="/Library/js/blank.htm"></iframe>
	<iframe name="w2" src="/Library/js/blank.htm"></iframe>
	<iframe name="w3" src="/Library/js/blank.htm"></iframe>
	<iframe name="w4" src="/Library/js/blank.htm"></iframe>
</div>
<?php } ?>
</body>
<!-- InstanceEnd --></html><?php
page_end();
?>