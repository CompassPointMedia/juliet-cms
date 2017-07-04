<?php
/* name=Sample Forms Page and News; description=Created 2/25/2012 - first use of a form for a specific page (or maybe not).  Then next step is to have docs on how EXACTLY to create these; */


if($submode=='RSVP_2012-03-02'){

}
prn('hello1');
$block=$primaryBlockName;
ob_start();


prn('hello2');

$$block=ob_get_contents();
ob_end_clean();
?>
