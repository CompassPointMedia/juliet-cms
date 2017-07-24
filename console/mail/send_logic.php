<?php
/*
the goal is to post the send to an iframe, and have the status tab reflect all.  
status tab should do . .. ... .... repeating
should be a refresh animated next to the processing batch
can't send out a batch while one is running
must be able to cancel, cxling will NOT allow you to resume
each send will have info about how many people opened, and how many replied



*/

if(/** test mode **/){
	//see if records available
	
	if(!records){
		//alert user
		
		exit;
	
	}else{
		//send the test batch out
		//switch to Status tab
		
		
		/**
		the batch will have no record made in the logs, but will have the progress bar run, and will have text in the status log show (realemail@test.com) sam-git@samuelfullman.com, etc..
		
		
		**/
		//increment the testmodeCount var and hidden field
		
		//[alert the user]
		
		
	}


}else{


//-----------------------------------------------------------------------
//real deal

if(/** any errors **/){
	//notify user
	
	exit;
}
if(/** this is an actual run and batch mode has NOT been run **/){
	//alert user
	
	exit;
}

//get records, are there any?

//we need the count of rows

//we could use the number of VALID emails that are present but this might require a pre-runthrough

foreach($record as $v){
	/**
	records will always be presented as an array
	we will also have system vars available based on the number of newsletters they've received (and we need a way to override this), what # they are to receive the email, last time they've received and email (from thisEntity), (from thisProfile) - see other notes I have here
	a row can be sent several times for multi email cols, so system needs to have CURRENT_EMAIL()
	records are presented without respect to the validity of the email column(s), we handle that here
	
	**/
	$i++;
	if($i==1){
		/** here we deal with
			1. mail_profiles_batches entry insertid()
			2. getting the headers from any text files or csv files?
			3. see if the email has compilation coding
			4. 
		**/
		
	}
	//merge env vars into the template coding
	
	//send email out
	
	//log, whether desired or not
	
	//also log against addr_contacts or finan_clients
	
	//

}
//-----------------------------------------------------------------------

}


?>