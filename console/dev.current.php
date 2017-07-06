<?php
$names=array('cat','dog','mouse');
		foreach($names as $n=>$v){
			unset($names[$n]);
			break;
		}
print_r($names);
?>