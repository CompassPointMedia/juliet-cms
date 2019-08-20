<?php 
//identify this script/GUI
$localSys['scriptID']='gen_access1';
$localSys['scriptVersion']='1.0';
$localSys['pageType']='Properties Window';


//2013-06-25 unified configuration files
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/config.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/resources/bais_00_includes.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/console/systeam/php/auth_i4_Usemod-Authentication_v100.php');
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/properties_04_i1.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>Quick Product Editor I</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->

<link rel="stylesheet" type="text/css" href="/Library/css/cssreset01.css" />
<link rel="stylesheet" href="/console/rbrfm_admin.css" type="text/css" />
<link rel="stylesheet" href="/Library/css/DHTML/dynamic_04_i1.css" type="text/css" />
<style type="text/css">
input[type=text]{
	border-width:1px;
	}
#loading{
	font-size:129%;
	color:#000066;
	}
.lthumb{
	border:1px solid #CCC;
	padding:2px;
	}
</style>

<script language="JavaScript" type="text/javascript" src="/Library/js/jquery.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/global_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/common_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/forms_04_i1.js"></script>
<script language="JavaScript" type="text/javascript" src="/Library/js/loader_04_i1.js"></script>
<script language="JavaScript" type="text/javascript">
/* periwinkle coding 2.1 */
var thispage='<?php echo $thispage?>';
var thisfolder='<?php echo $thisfolder?>';
var ctime='<?php echo $ctime?>';
var PHPSESSID='<?php echo $PHPSESSID?>';
//for nav feature
var count='<?php echo $nullCount?>';
var ab='<?php echo $nullAbs?>';
var isEscapable=1;
var isDeletable=1;
var isModal=1;
var talks=1; //whether this page broadcasts overall state changes which other pages listen for
var listens=0;

AddOnkeypressCommand("PropKeyPress(e)");

var saymode='';
function say(text){
	if(saymode!=='test')return;
	if(typeof this.i=='undefined')this.i=0;
	this.i++;
	$('#console').prepend('<div>'+new Date()+' ['+i+'] '+text+'</div>');
}
</script>
<script language="javascript" type="text/javascript">
//https://gist.github.com/754454
function stringify(obj) {
	var t = typeof (obj);
	if (t != "object" || obj === null) {
		// simple data type
		if (t == "string") obj = '"' + obj + '"';
		return String(obj);
	} else {
		// recurse array or object
		var n, v, json = [], arr = (obj && obj.constructor == Array);

		for (n in obj) {
			v = obj[n];
			t = typeof(v);
			if (obj.hasOwnProperty(n)) {
				if (t == "string") v = '"' + v + '"'; else if (t == "object" && v !== null) v = stringify(v);
				json.push((arr ? "" : '"' + n + '":') + String(v));
			}
		}
		return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
	}
}

<?php
/*
2012-12-02: This is the same across multiple classnames; as of now I don't know how to abstract it because of {this}
*/
$preBuildFunctionString='	
		function(optionRootElement,inputObj){
			//here is where I would live-submit the category which is sorely needed
			//called when an existing tag is selected from list - a "hot" binding
			if(typeof inputObj==\'string\'){
				var Label=inputObj;
			}else{
				var Label=inputObj.innerHTML.replace(\'<span class="highlighted">\',\'\').replace(\'</span>\',\'\');
			}
			var str=lookup_externalFile + \'?suppressPrintEnv=1&mode=listAdder&submode=updateField&table=\'+this.table+\'&field=\'+this.field+\'&string=\'+escape(Label)+\'&bindTo=\'+optionRootElement.id.replace(/[^0-9]+/g,\'\');
			$.post(str, function(data) {
				_dm_=data;
			});
			$(document).ajaxStop(function() {
				//this is asynchronous
				var OK=$.parseJSON(_dm_).OK;
				if(!OK)alert(\'Error in binding this object to the tag\');
				$(this).unbind(\'ajaxStop\');
			});
		}';
?>
var lookup_method='single'; //single|multiple, default is multiple
var autofills={
	imagetags:{
		mode:'getImageTagsByLetters',		/* where the query is located */
		label:'Name',						/* field of return */
		addNewTag:function(o){
			//----------- new coding -----------	
			$.post(lookup_externalFile + '?suppressPrintEnv=1&mode=listAdder&submode=imageTags&Name='+escape(o.value)+'&bindTo='+o.id, function(data){
				_dm_=data;
			});
	
			$(document).ajaxStop(function() {
				//this is asynchronous
				var Tags_ID=$.parseJSON(_dm_).Tags_ID;
				var Label=o.value;

				//------------ following code taken from lookup_setValue() ------------
				say('setvalue indian');
				if($(o).next().val().indexOf('|'+Tags_ID+'|')>-1){
					//what? this should not happen
				}else{
					if(lookup_method=='multiple'){
						//add the element visually - note the double key employed for multiple autofill fields
						var str='<div id="c'+('_'+o.id.replace(/[^0-9]/g,''))+'_'+Tags_ID+'" class="cancellableItem">';
						str+=Label+'<div class="cancel" onclick="lookupCancel(this)">x</div>';
						str+='</div>';
						$(o).prev().html($(o).prev().html()+str);
					}
					$(o).next().val(
						(lookup_method=='multiple' ? $(o).next().val() : '|') +Tags_ID+'|'
					);
				}
				//clear the input and focus again on it
				if(lookup_method=='multiple'){
					$(o).val('');
					$(o).focus();
				}else{
					//fill the input box with the full element name - not necessary when adding new since that IS the value
					return false;
				}
				//------------ end code ------------
				$(this).unbind('ajaxStop');
			});
		},
		preBuildFunction:function(optionRootElement,inputObj){
			//called when an existing tag is selected from list - a "hot" binding
			var Label=inputObj.innerHTML;
			Label=Label.replace('<span class="highlighted">','').replace('</span>','');
			$.post(lookup_externalFile + '?suppressPrintEnv=1&mode=listAdder&submode=imageTags&Tags_ID='+inputObj.id+'&bindTo='+optionRootElement.id, function(data) {
				_dm_=data;
			});
			$(document).ajaxStop(function() {
				//this is asynchronous
				var OK=$.parseJSON(_dm_).OK;
				if(!OK)alert('Error in binding this object to the tag');
				$(this).unbind('ajaxStop');
			});
		}
	},
	activevalue:{
		mode:'getItemActiveByLetters',
		table:'finan_items',
		field:'Active',
		label:'Name',
		addNewTag:function(o){ },
		preBuildFunction:<?php echo $preBuildFunctionString;?>
	},
	namevalue:{
		mode:'getItemNamesByLetters',
		table:'finan_items',
		field:'Name',
		label:'Name',
		addNewTag:function(o){ },
		preBuildFunction:<?php echo $preBuildFunctionString;?>
	},
	categoryvalue:{
		mode:'getItemCategoriesByLetters',
		table:'finan_items',
		field:'Category',
		label:'Name',
		addNewTag:function(o){ },
		preBuildFunction:<?php echo $preBuildFunctionString;?>
	}
}
var lookup_externalFile='resources/bais_01_exe.php';
var optionDivInnerHTML='lookup_simple';
function lookup_simple(it,letters){
	/* rewrite this */
	var str;
	for(var i in it)it[i]=$.trim(it[i]);
	eval('var reg=/(^| )('+letters+')/gi;');
	str=it['Name'].replace(reg,'<span class="highlighted">$2</span>');
	return str;
}
$(document).ready(function(e){
	$('.autofill').blur(function(e){ 
		if(this.value==autofill_current.focusValue)return;
		var c=this.className.replace(/autofill\s+/,'').split(' ');
		for(var i in c){
			if(o=autofills[c[i]])o.preBuildFunction(this,this.value);
		}
	});
});
</script>
<script type="text/javascript" src="/Library/js/lookup-1.0.js"></script>
<script language="javascript" type="text/javascript">
//rewrites
function lookupCancel(o){
	//removes element from visual list and from key receipt field
	var o=$(o).parent()[0];
	var a=o.id.split('_');
	$.post(lookup_externalFile + '?suppressPrintEnv=1&mode=listAdder&submode=imageTags&subsubmode=deleteTag&Tags_ID='+a[2]+'&bindTo='+a[1], function(data) {
		_dm_=data;
	});
	$(document).ajaxStop(function() {
		if(!$.parseJSON(_dm_).OK)alert('Error in deleting this tag from the object');
		$(this).unbind('ajaxStop');
	});
	$('#val'+a[1]).val( $('#val'+a[1]).val().replace('|'+a[2]+'|','|') );
	o.style.display='none';
	$('#val'+a[1]).focus();
}
</script>

<!-- InstanceEndEditable -->
</head>

<body id="properties">
<form id="form1" name="form1" target="w2" method="post" action="/console/resources/bais_01_exe.php" onsubmit="return beginSubmit();" enctype="multipart/form-data">
<div id="header"><!-- InstanceBeginEditable name="top_nav" -->
<div class="fr">
  <input type="button" name="Button" value="Close" onclick="window.close();" />
</div>
<!-- InstanceEndEditable --></div>
<div id="mainBody"><!-- InstanceBeginEditable name="main_body" -->
<h3>Quick Item Editor</h3>
<?php
$a=q("SELECT ID, Active, Category, SKU, Name, Description FROM finan_items WHERE Category!=''", O_ARRAY_ASSOC);
$b=q("SELECT ID, Active, Category, SKU, Name, Description FROM finan_items WHERE Category=''", O_ARRAY_ASSOC);
$a=array_merge($a,$b);
?>
  <br />
  <div id="loading"><img src="../images/i/ani/ani-fb-orange.gif" alt="loading" width="16" height="11" /> Loading items...please wait...</div>
  <table class="yat">
  <thead>
  <tr>
	  <th>&nbsp;</th>
	  <th>Active</th>
	  <th>SKU</th>
	  <th>Name</th>
	  <th>Category</th>
	  <th>Description</th>
  </tr>
  </thead>
  <tbody>
  <?php
foreach($a as $v){
	extract($v);
	?><tr>
	  <td style="padding:0px;">
	  <?php
	  if(file_exists('/home/cpm006/cj/images/stock/.thumbs.dbr/'.$SKU.'.jpg')){
	  	?><a href="#" onclick="return ow(this.firstChild.src.replace('.thumbs.dbr',''),'l1_img','700,700');"><img src="/images/stock/.thumbs.dbr/<?php echo $SKU;?>.jpg" class="lthumb" width="95" height="95" /></a><?php
	  }
	  ?>
	  </td>
	  <td><input name="Active[<?php echo $ID;?>][<?php echo md5(rand(1,1000000));?>]" type="text" class="autofill activevalue minimal" id="nodeName<?php echo $ID;?>" value="<?php echo h($Active);?>" size="1" maxlength="1" /></td>
	  <td style="padding-top:4px;"><a href="items.php?Items_ID=<?php echo $ID;?>" onclick="return ow(this.href,'l1_items','800,700');" title="view/edit this item" tabindex="-1"><?php echo $SKU;?></a></td>
	  <td>
	    <input name="Name[<?php echo $ID;?>][<?php echo md5(rand(1,1000000));?>]" type="text" id="nodeName<?php echo $ID;?>" value="<?php echo h($Name);?>" class="autofill namevalue minimal" />
	    <input name="id[<?php echo $ID;?>]" type="hidden" id="val<?php echo $ID;?>" value="|<?php echo h($Name);?>" />
	    </td>
	  <td>
	    <input name="Category[<?php echo $ID;?>][<?php echo md5(rand(1,1000000));?>]" type="text" class="autofill categoryvalue minimal" id="nodeCategory<?php echo $ID;?>" value="<?php echo h($Category);?>" size="12" />
	    <input name="id[<?php echo $ID;?>]" type="hidden" id="val<?php echo $ID;?>" value="|<?php echo h($Category);?>" />
	    </td>
	  <td><?php echo $Description?></td>
	  </tr><?php
}
?>
  </tbody>
  </table>
  <?php
?>
<script language="javascript" type="text/javascript">
g('loading').style.display='none';
</script>
<!-- InstanceEndEditable --></div><div id="footer"><!-- InstanceBeginEditable name="footer" -->
<div id="console">

</div>
<!-- InstanceEndEditable --></div>
</form>
<?php if(!$hideCtrlSection){ ?>
<div id="showTester" title="Javascript Tester" onClick="g('tester').style.display='block';">&nbsp;</div>
<div id="tester" >
	<a href="#" onClick="g('ctrlSection').style.display='block';return false;">Show Control Section</a><br />
	<textarea name="test" cols="65" rows="4" id="test">clear_form();</textarea><br />
	<input type="button" name="button" value="Test" onClick="jsEval(g('test').value);"><br />
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