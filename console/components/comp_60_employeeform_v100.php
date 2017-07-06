First Name : <input type="text" name="FirstName" id="FirstName" class="c1" onchange="dChge(this);" value="<?php echo h($FirstName)?>"/><br />
Middle Name : <input type="text" name="MiddleName" id="MiddleName" class="c1" onchange="dChge(this);" value="<?php echo h($MiddleName)?>"/><br />
Last Name : <input type="text" name="LastName" id="LastName" class="c1" onchange="dChge(this);"  value="<?php echo h($LastName)?>"/><br />
Email : <input type="text" name="Email" id="Email" class="c1" onchange="dChge(this);" value="<?php echo h($Email)?>"/> <br />
Social Security # : <input maxlength="9" type="text" name="SocialSecurity" id="SocialSecurity" class="c1" onchange="dChge(this);" value="<?php echo h($SocialSecurity)?>"/><br />
UserName : <input type="text" name="UserName" id="UserName" class="c1" onchange="dChge(this);" value="<?php echo h($UserName)?>"/><br />
<?php
if($mode==$insertMode){
	?>
	<input type="hidden" value="<?php date ();?>" name="CreateDate" id="CreateDate" class="c1"/>
	<input type="hidden" value="Admin" name="Creator" id="Creator" class="c1" />
	Password : <input type="password" name="Password" id="Password" class="c1" onchange="dChge(this);" value="<?php echo h($Password)?>"/><br />
	<?php
} else if ($mode==$updateMode){
	?>
	Hours This Week :
	<?php
	$EmployeeHours=q("SELECT SUM(TotalHours) as Hours FROM finan_hours WHERE Contacts_ID='".$Employees_ID."' AND Week='".date('W')."'",O_VALUE);
	echo $EmployeeHours;
	?>
	<input type="button" onclick="ow('/console/hours.php','','700,700',true);" value="Add More Hours" /><br />
	<?php
}
?>
Hourly Rate : $<input type="text" name="PayAmount" id="PayAmount" class="c1" onchange="dChge(this);" value="<?php echo h($PayAmount)?>"/><br />
