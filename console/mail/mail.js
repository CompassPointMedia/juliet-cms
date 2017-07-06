var detectChange=0;

//these need dynamic declaration
var CMSGroupSelCount=0; //needs to be declared dynamically for saved profiles
var selectedCMSGroups=new Array();
function grpOptions(o){
	//reset status on each click - then rebuild
	CMSGroupSelCount=0;
	selectedCMSGroups=new Array();
	var string='';
	for (var i=0; i<o.options.length; i++){
   	if(!o.options[i].selected) continue;
		if(o.options[i].value=='') continue;
		if(o.options[i].value=='{RB_ADDNEW}'){
			o.options[i].selected=false;
			alert('Adding a new group is not available from the Mailer Profile Window.  Cancel or Save this window, go to groups, and add a new group there');
			continue;
			newWindow('../groups/groups.php?Profiles_ID='+Profiles_ID+'&cb=1016','l2_groups','700,550');
			continue;
		}
		CMSGroupSelCount++;
		selectedCMSGroups[o.options[i].value]=o.options[i].innerHTML;
	}
	if(selectedCMSGroups.length==0){
		g('openCMSGroup').disabled=true;
	}else if(selectedCMSGroups.length==1){
		g('openCMSGroup').disabled=false;
	}else{
		g('openCMSGroup').disabled=false;
		//now build the available groups to focus on
		g('selectedCMSGroupsMenu').innerHTML='';
		for(f in selectedCMSGroups){
			string+='<div style="width:350px;" class="menuitems" command="group_open('+f+')" status="Open the selected group">'+selectedCMSGroups[f]+'</div>';
		}
		g('selectedCMSGroupsMenu').innerHTML=string;
	}
}
function group_open(Groups_ID){
	ow('../groups/groups.php?Groups_ID='+Groups_ID,'l2_groups','700,550');
}

//-----------------------------
//function DoPlainText:
//added by Yahav on 10/09/2004
//this function applies Plain Text mode, i.e. disables Composition and Template panels.
//-----------------------------
function DoPlainText(){
	var objPanel;
	
	//Composition:
	objPanel = g("fldSetCompose");
	SetDeepProperty(objPanel, "disabled", true);
	
	//Template:
	objPanel = g("mailComp_template");
	SetDeepProperty(objPanel, "disabled", true);
	
	//also, set Blank Email option:
	SetRadByValue(document.form1.Composition, "blank");
}

//-----------------------------
//function DoHtmlMail:
//added by Yahav on 10/09/2004
//this function applies Html mode, i.e. enables Composition and Template panels.
//-----------------------------
function DoHtmlMail(){
	var objPanel;
	
	//Composition:
	objPanel = g("fldSetCompose");
	SetDeepProperty(objPanel, "disabled", false);
	
	//Template:
	objPanel = g("mailComp_template");
	
	//maybe user selected Blank Email? if so, do not enable Template:
	if (GetSelRadValue(form1.Composition) != "blank")
		SetDeepProperty(objPanel, "disabled", false);
}

//-----------------------------
//function DoBlankEmail:
//added by Yahav on 10/09/2004
//this function applies Blank Email mode, i.e. disables Template Options panel.
//-----------------------------
function DoBlankEmail(){
	var objPanel;
	objPanel = g("mailComp_template");
	SetDeepProperty(objPanel, "disabled", true);
}

//-----------------------------
//function DoTemplateMail:
//added by Yahav on 10/09/2004
//this function applies Template Email mode, i.e. enables Template Options panel.
//-----------------------------
function DoTemplateMail(){
	var objPanel;
	objPanel = g("mailComp_template");
	SetDeepProperty(objPanel, "disabled", false);
}

//-----------------------------
//function DoUrlTemplate:
//added by Yahav on 10/09/2004
//this function auto selects From Url radio button, if user put any url
//-----------------------------
function DoUrlTemplate(){
	var strValue=document.form1.TemplateLocationURL.value;
	if (strValue.length > 0)
		SetRadByValue(document.form1.TemplateMethod, "url");
}

//-----------------------------
//function SetDeepProperty:
//added by Yahav on 10/09/2004
//this function sets given property to the control and all its children
//-----------------------------
function SetDeepProperty(objControl, strProperty, strValue){
	eval("objControl."+strProperty+" = "+strValue+";");
	for (var i=0; i<objControl.all.length; i++)
		eval("objControl.all[i]."+strProperty+" = "+strValue+";");
}
//-----------------------------
//function GetSelRadValue:
//added by Yahav on 10/09/2004
//this function returns the value of Checked button in radio buttons group.
//-----------------------------
function GetSelRadValue(objRadGroup){
	for (var i=0; i<objRadGroup.length; i++){
		if (objRadGroup[i].checked) return objRadGroup[i].value;
	}
	return "";
}

//-----------------------------
//function SetRadByValue:
//added by Yahav on 10/09/2004
//this function find button with given value in given group and set it as Checked.
//-----------------------------
function SetRadByValue(objRadGroup, strValue){
	for (var i=0; i<objRadGroup.length; i++){
		if (objRadGroup[i].value == strValue){
			objRadGroup[i].checked = true;
			return true;
		}
	}
	return false;
}
function textType(x){

}