function openCustomer(){
	//really means "new customer" - the new/edit js for customers and items is a mess
	ow('members.php','l1_member','700,700',true);
	}
function openItem(){
	//same as above :)
	ow('items.php','l1_member','700,700',true);
	}
function ta(o,act){
	switch(act){
		case 'keyup':
			o.style.height=0;
			o.style.height=o.scrollHeight+'px'
		break;
		case 'focus':
			if(o.value=='None')o.value='';
		break;
		case 'blur':
			if(o.value=='')o.value='';
		break;
	}
}
