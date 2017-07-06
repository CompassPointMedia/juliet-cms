<?php

if(preg_match('/[a-f0-9]{32}/i',$Location) && $creator=$_SESSION['special']['datasets'][$Location]){
	//quasi dc present	


}else if(@$lines=file($Location)){
	//get settings including 
	$datasetFetchSettings=true;
	$i=0;
	foreach($lines as $n=>$v){
		$i++;
		if(preg_match('/dataset_generic_precoding_v|dataset_component_v/',$v) && !strstr($v,'datasetFetchSettings')){
			$lines[$n]='if(!$datasetFetchSettings)'.$v;
		}
		//$lines[$n]=$i.': '.$lines[$n];
	}
	eval('?>'.implode('',$lines));
}else{
	//create quasi dc
	$Location=md5(time().rand(1,100000));
}


?>