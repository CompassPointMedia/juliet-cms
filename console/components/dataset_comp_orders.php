<?php
function orders_delete($ids, $options=array()){
	/*
	2013-09-16: this is first function which is dynamically included by systementry in a required file
	*/
	extract($options);
	global $fl,$ln,$qr,$qx,$fromHdrBugs,$developerEmail;
	//what about security, who able to do so etc.
	foreach($ids as $Headers_ID){
		//filter out for payments made or deposits made
		$a=q("SELECT * FROM _v_finan_invoices_cash_sales WHERE ID=$Headers_ID", O_ROW);
		if($a['AmountApplied']<>0 || $a['AmountAppliedTo']<>0){
			error_alert('Transaction '.$a['HeaderNumber'].' was not deleted because it was referenced or paid with another transaction',1);
			global $Orders_ID;
			unset($Orders_ID[array_search($Headers_ID,$Orders_ID)]);
			if(count($ids)==1)eOK();
		}
		$ln=__LINE__; q("DELETE FROM finan_headers WHERE ID=$Headers_ID");
		prn($qr);
		$ln=__LINE__; q("DELETE FROM finan_invoices WHERE Headers_ID=$Headers_ID");
		prn($qr);
		$ln=__LINE__; q("DELETE FROM finan_payments WHERE Headers_ID=$Headers_ID");
		prn($qr);
		$ln=__LINE__; q("DELETE FROM finan_transactions WHERE Headers_ID=$Headers_ID");
		prn($qr);
	}
}

?>