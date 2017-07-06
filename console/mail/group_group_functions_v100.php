<?php
/**
2006-07-25: this allows me to use group information for the mailer

in_group('BIF Members')	returns 1 if the contact is in that group
group_param('Bif members', 'Title')	e.g. returns their title in the group Bif members
in_any_group('Title', 'Administrator', 'Admin')	e.g. returns name of the first group found if, in this group, their title is administrator or admin (can have additional params)
**/
function in_group($thisGroup){
	//this function simply returns 1 if contact is in this group
	global $rd;
	if(!strlen($thisGroup) || !$rd['_GroupsList'])return;
	foreach($rd['_GroupsList'] as $group=>$fields){
		if(strtolower($group)==strtolower($thisGroup)) return 1;
	}
}
function group_param($thisGroup, $thisField){
	//this function returns the value of a field in a specific group, e.g. for George W. Bush, group_param('US Citizens', 'Title')='President';
	global $rd;
	if(!strlen($thisGroup) || !strlen($thisField) || !$rd['_GroupsList'])return;
	foreach($rd['_GroupsList'] as $group=>$fields){
		if(strtolower($thisGroup) !== strtolower($group))continue;
		foreach($fields as $field=>$value)	if(strtolower($thisField)==strtolower($field)) return $value;
	}
}
function group_value(){
	//this function returns 1 if in the specified group the value of the field is any of the given values, e.g. group_value('BIF Members', 'PreferredTypes', 'NIV', 'KJV', 'NAS') returns 1 if the contact is in the specified group and the field is any of the last 3 values
	global $rd;
	if(!$rd['_GroupsList'] || func_num_args()<3) return;
	//get the parameter and possible values
	$a=func_get_args();
	$thisGroup=$a[0];
	$thisField=$a[1];
	for($i=2; $i<count($a); $i++) $possible[]=$a[$i];
	foreach($rd['_GroupsList'] as $group=>$fields){
		if(strtolower($thisGroup)!==strtolower($group))continue;
		foreach($fields as $field=>$value){ //field -> value
			if(strtolower($thisField)!==strtolower($field)) continue;
			foreach($possible as $p){
				if(strtolower($p)==strtolower($value))return 1;
			}
		}
	}
}
function in_any_group(){
	//this function returns the name of the first group in which it was found, if, in that group, the specified field is of any of the values listed, e.g. in_any_group('Title','Admin','Administrator') will return the name of the first group in which the contact's title is either Admin or Administrator 
	global $rd;
	if(!$rd['_GroupsList'] || func_num_args()<2) return;
	//get the parameter and possible values
	$a=func_get_args();
	$thisField=$a[0];
	for($i=1; $i<count($a); $i++) $possible[]=$a[$i];
	foreach($rd['_GroupsList'] as $group=>$fields){
		foreach($fields as $field=>$value){ //field -> value
			if(strtolower($thisField)!==strtolower($field)) continue;
			foreach($possible as $p){
				if(strtolower($p)==strtolower($value))return $group;
			}
		}
	}
}


?>