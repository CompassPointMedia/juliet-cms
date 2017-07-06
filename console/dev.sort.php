<?php
$localSys['scriptID']='gen_access1';
$localSys['scriptVersion']=4.00;

require('systeam/php/config.php');
$qx['defCnxMethod']=C_MASTER;
$a=q("SELECT ID, FirstName, LastName, Email FROM addr_contacts WHERE 1 ORDER BY RAND()", O_ARRAY);

require($FUNCTION_ROOT.'/function_array_subkey_sort_v300.php');


?><table>
<?php
foreach($a as $n=>$v){
	?><tr><td style="background-color:#ccc;"><?php echo $n;?></td><td><?php echo implode('</td><td>',$v);?></td></tr><?php
}
?></table><?php

echo '<br><br>';

$a=subkey_sort($a,array('LastName','FirstName','ID'));

?><table>
<?php
foreach($a as $n=>$v){
	?><tr><td style="background-color:#ccc;"><?php echo $n;?></td><td><?php echo implode('</td><td>',$v);?></td></tr><?php
}
?></table><?php

exit;


exit;

$databases=q("SHOW DATABASES", O_ARRAY, C_SUPER);
foreach($databases as $v){
	$db=$v['Database'];
	$qx['useRemediation']=false;
	ob_start();
	q("ALTER TABLE $db.`addr_contacts` CHANGE `Email` `Email` CHAR( 85 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL", ERR_ECHO, C_SUPER);
	$err=ob_get_contents();
	ob_end_clean();
	if(!$err)prn('ok on '.$db);
}
exit;
prn($databases,1);



//http://www.planet-source-code.com/vb/scripts/ShowCode.asp?txtCodeId=1646&lngWId=8
function asc2bin($in){
	$out = '';
	for($i = 0, $len = strlen($in); $i < $len; $i++)$out .= sprintf("%08b",ord($in{$i}));
	return $out;
}
//use this function to convert binary back to readable ascii text
function bin2asc($in){
	$out = '';
	for($i = 0, $len = strlen($in); $i < $len; $i += 8)$out .= chr(bindec(substr($in,$i,8)));
	return $out; 
}
foreach($a as $n=>$v){
	$w=asc2bin(strtolower($v['LastName']));
	$max=max(strlen($w),$max);
	$ref[$n]=$w;
}
foreach($ref as $n=>$v){
	$ref[$n]=str_pad($v,$max,'0',STR_PAD_LEFT).$n.'';
}
$pad=strlen(max(array_keys($a)));
foreach($a as $n=>$v){
	$rf[$n]=strtolower($n==7 ? 'anders-n':$v['LastName']).'-'.str_pad($n,$pad,'0',STR_PAD_LEFT);
}
prn($rf);
asort($rf);
prn($rf);
exit;

foreach($ref as $n=>$v){
	$b[$n]=$a[$n];
}
prn($b);
?>