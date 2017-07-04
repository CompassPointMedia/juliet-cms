<!-- form:<?php echo end(explode('/',__FILE__));?> --><?php
if(!$refreshComponentOnly){
	?><style type="text/css">
	.formhead{
		border-bottom:1px dotted #333;
		margin:10px 0px;
		}
	.required{
		color:darkred;
		font-weight:bold;
		font-size:larger;
		}
	#proxyInfo{
		border:1px dashed darkred;
		padding:15px 10px;
		margin:20px;
		}
	</style>
		<script language="javascript" type="text/javascript">
	function beginSubmit(){
		if(g('Email').value!==g('nullEmail').value){
			alert('You must enter your email in twice; your email entries do not match');
			return false;
		}
	}
	</script><?php
} 
function requiredfield($field){
	global $usemod;
	if(@in_array(strtolower($field),$usemod['requiredFields'])){
		?><span class="required">*</span><?php
	}
}

$_f_action=($usemod['formAction'] ? $usemod['formAction'] : '/index_01_exe.php');
$_f_target=($usemod['formTarget'] ? $usemod['formTarget'] : 'w2');
$_f_id=($usemod['formID'] ? $usemod['formID'] : 'form1');

?>
<form method="post" action="<?php echo $_f_action;?>" name="form1" target="<?php echo $_f_target;?>" id="<?php echo $_f_id?>" class="formLayout1" onSubmit="return beginSubmit();">
<?php
//proxy record
if($usemod['proxyInsertAllow'] && (
	($usemod['proxyLoginPresentationMethod']==3) ||
	($usemod['proxyLoginPresentationMethod']==2 && $adminMode) ||
	($usemod['proxyLoginPresentationMethod']==1 && $proxy)
	)){
	?><div id="proxyInfo">
		<h3>Proxy Record</h3>
		<p>You must enter your username and password to create a proxy record.   </p>
		<label>
		<input name="proxy" type="checkbox" id="proxy" value="1" onchange="dChge(this);" <?php echo $proxy || !isset($proxy)?'checked':''?> /> 
		Proxy record creation</label><br />
		your username: 
		<input name="UN" type="text" id="UN" value="<?php echo h($UN);?>" onchange="dChge(this);" />
		<br />
		your password: 
		<input name="PW" type="password" id="PW" onchange="dChge(this);" />
		<br />
		<label><input name="suppressEmail" type="checkbox" id="suppressEmail" value="1" onchange="dChge(this);" <?php echo $suppressEmail || !isset($suppressEmail)?'checked':''?> /> Do not send confirmation/welcome emails</label>
	</div><?php
}
if(isset($Category) || $umContactCategory){
	?><input type="hidden" name="Category" id="Category" value="<?php echo isset($Category)?$Category:$umContactCategory;?>" /><?php
}

//2013-07-24: show pre-form fields
echo $preFormFields;
?>
	<table border="0" cellspacing="0" cellpadding="1">
	<?php if($usemod['showCompanyField']){ ?>
	<tr>
		<td><strong>Company</strong><?php requiredfield('Company');?></td>
		<td><input name="Company" type="text" id="Company" onchange="dChge(this);" value="<?php echo h($Company)?>" size="45" /></td>
	</tr>
	<?php } ?>
	<?php if(false){ ?>
	<tr>
		<td>Your Title:
	    <?php requiredfield('Title');?></td>
		<td><select name="Title" id="Title" onchange="dChge(this);">
		<option value="">&lt;Select..&gt;</option>
		<?php
		$haveTitle=false;
		foreach($normalTitles as $v){
			?><option value="<?php echo $v?>" <?php if(strtolower(str_replace('.','',$Title))==$v){ $haveTitle=true; echo 'selected'; }?>><?php echo $v?></option><?php
		}
		if(strlen($Title) && !$haveTitle){ ?><option value="<?php echo h($Title)?>" selected><?php echo h($Title)?></option><?php }
		?>
		</select></td>
	</tr>
	<?php } ?>
	<tr>
		<td>First Name:<?php requiredfield('FirstName')?></td>
		<td><input name="FirstName" type="text" id="FirstName" value="<?php echo $FirstName?>" onchange="dChge(this);" size="25" /></td>
	</tr>
	<?php if($usemod['showMiddleName']){?>
	<tr>
		<td>Middle Name:</td>
		<td><input name="MiddleName" type="text" id="MiddleName" value="<?php echo $MiddleName ?>" onchange="dChge(this);"  size="25"/></td>
	</tr>
	<?php }?>
	<tr>
		<td>Last Name:
		  <?php requiredfield('LastName');?></td>
		<td><input name="LastName" type="text" id="LastName" value="<?php echo $LastName?>" onchange="dChge(this);" size="25" /></td>
	</tr>
	<tr>
		<td>Your Email:
	    <?php requiredfield('Email');?></td>
		<td><input name="Email" type="text" id="Email" value="<?php echo $Email?>" onchange="dChge(this);" size="40"/></td>
	</tr>
	<?php if($mode=='insert'){ ?>
	<tr>
		<td>Re-type Email:
	    <?php requiredfield('Email');?></td>
		<td><input name="nullEmail" type="text" id="nullEmail" value="" onchange="dChge(this);" size="40"/></td>
	</tr>
	<?php } ?>
	<?php 
	if(!$usemod['autoGenerateUsername']){
		if($mode=='insert'){ ?>
		<tr>
			<td>Unique User Name:
		    <?php requiredfield('UserName')?></td>
			<td><input name="UserName" type="text" id="UserName" value="<?php echo $UserName?>" maxlength="18" />
				<span class="gray">(4-18 chars., letters and numbers only)</span></td>
		</tr>
	<?php }else{ ?>
	<tr>
		<td>User Name:<?php requiredfield('UserName')?></td>
		<td><strong><?php echo $UserName?></strong></td>
	</tr>
	<?php
		}
	}
	if(!$usemod['autoGeneratePassword'] && $mode=='insert'){
		//access to password this way only for inserts
		?>
		<tr>
			<td>Password:<?php requiredfield('Password')?></td>
			<td><input name="Password" type="password" id="Password" value="<?php echo $Password?>" onchange="dChge(this);" /></td>
		</tr>
		<tr>
			<td>Re-type Password:<?php requiredfield('Password')?></td>
			<td><input name="nullPassword" type="password" id="nullPassword" value="<?php echo $nullPassword?>" /></td>
		</tr>
		<?php
	}
	
	if(!$hideContactInformation){ //----------------------------------------------
	?>
	<tr>
		<td colspan="2"><br />
			<br />
			<h2 class="formhead">Contact Information</h2>
			<?php if($mode=='insert' && false){
				?><div>
				<label>
				<input name="alsoMyAddress" type="checkbox" id="alsoMyAddress" value="1" />
					This is also my home/home office address</label>
				</div>
			<?php } ?>		</td>
	</tr>
	<tr>
		<td>Address:<?php requiredfield('HomeAddress')?></td>
		<td><input name="HomeAddress" type="text" id="HomeAddress" onchange="dChge(this);" value="<?php echo $GLOBALS['HomeAddress'];?>" size="35" /></td>
	  </tr>
	<tr>
		<td>City:<?php requiredfield('HomeCity')?></td>
		<td><input name="HomeCity" type="text" id="HomeCity" value="<?php echo $GLOBALS['HomeCity'];?>" onchange="dChge(this);" /></td>
	  </tr>
	<tr>
		<td>State or Region:<?php requiredfield('HomeState')?></td>
		<td>
		<select name="HomeState" id="HomeState" onChange="dChge(this);" style="width:175px;">
			<option value="" class="ghost"> &lt;Select..&gt; </option>
			<?php
			$gotState=false;
			$states=q("SELECT st_code, st_name FROM aux_states",O_COL_ASSOC, $public_cnx);
			foreach($states as $n=>$v){
				?><option value="<?php echo $n?>" <?php
				if($GLOBALS['HomeState']==$n){
					$gotState=true;
					echo 'selected';
				}
				?>><?php echo h($v)?></option><?php
			}
			if(!$gotState && $State!=''){
				?><option value="<?php echo h($GLOBALS['HomeState'])?>" selected><?php echo $GLOBALS['HomeState'];?></option><?php
			}
		?>
		</select>		</td>
	</tr>
	<tr>
		<td>Zip:<?php requiredfield('HomeZip')?></td>
		<td><input name="HomeZip" type="text" id="HomeZip" value="<?php echo $GLOBALS['HomeZip'];?>" onchange="dChge(this);" /></td>
	</tr>
	<?php if($usemod['showCountry']){?>
	<tr>
		<td>Country:<?php requiredfield('HomeCountry')?></td>
		<td>
		<select name="HomeCountry" id="HomeCountry" onChange="dChge(this);" style="width:175px;">
			<option value="" class="ghost"> &lt;Select..&gt; </option>
			<?php
			$gotCountry=false;
			$countries=q("SELECT ct_code, ct_name FROM aux_countries",O_COL_ASSOC, $public_cnx);
			foreach($countries as $n=>$v){
				?><option value="<?php echo $n?>" <?php
				if($GLOBALS['HomeCountry']==$n){
					$gotCountry=true;
					echo 'selected';
				}
				?>><?php echo h($v)?></option><?php
			}
			if(!$gotCountry && $GLOBALS['HomeCountry']!=''){
				?><option value="<?php echo h($GLOBALS['HomeCountry'])?>" selected><?php echo $GLOBALS['HomeCountry']?></option><?php
			}
		?>
		</select></td>
	</tr>
	<?php }?>
	<tr>
		<td>Phone:<?php requiredfield('HomePhone')?></td>
		<td><input name="HomePhone" type="text" id="HomePhone" value="<?php echo $GLOBALS['HomePhone']?>" onchange="dChge(this);" /></td>
	  </tr>
	<?php if(!$showFax){ ?>
	<tr>
		<td>Fax:<?php requiredfield('Fax')?></td>
		<td><input name="HomeFax" type="text" id="HomeFax" value="<?php echo $GLOBALS['HomeFax']?>" onchange="dChge(this);" /></td>
	</tr>
	<?php } ?>
	<tr>
		<td>Mobile Phone:<?php requiredfield('MobilePhone')?></td>
		<td><input name="HomeMobile" type="text" id="HomeMobile" value="<?php echo $HomeMobile?>" onchange="dChge(this);" /></td>
	</tr>
	<?php } //----------------------------- end hideContactInformation ---------------------------- ?>
	<?php if($usemod['showNewsLetterPreferences']){ ?>
	<tr>
		<td colspan="2">
			<?php if(false && $mode=='insert'){ ?>
			<label><input name="TOU_OK" type="checkbox" id="TOU_OK" value="1" /> <strong> I acknowledge that I have read and will comply with the <a href="<?php echo $usemod['TermsOfUseURL'] ? $usemod['TermsOfUseURL'] : 'termsofuse.php'; ?>" title="Site Terms of Use" onclick="return ow(this.href,'l1_tou','500,700');">Terms of Use</a> for this site</strong></label>
			
			<input name="nonPassedFieldNames[]" type="hidden" id="nonPassedFieldNames[]" value="NewsletterOK" />
			<?php } ?>
			<!--
			&nbsp;with this frequency:<br />
			<select name="NewsletterFrequency" id="NewsletterFrequency">
				<option value="1" <?php echo $NewsletterFrequency==1 ? 'selected' : ''?>>Only when one of these interests comes up</option>
				<option value="2" <?php echo $NewsletterFrequency==2 ? 'selected' : ''?>>About once a week</option>
				<option value="3" <?php echo $NewsletterFrequency==3 ? 'selected' : ''?>>About twice a month</option>
				<option value="4" <?php echo $NewsletterFrequency==4 ? 'selected' : ''?>>Once a month, no more</option>
				<option value="5" <?php echo $NewsletterFrequency==5 ? 'selected' : ''?>>Rarely</option>
			</select>
			-->			</td>
	</tr>
	<?php }?>
	<?php if($usemod['showNotes']){ ?>
	<tr>
		<td colspan="100%" valign="top">Notes or comments for staff:<?php requiredfield('Notes')?><br />
		<textarea name="Notes" cols="35" rows="5" id="Notes" onchange="dChge(this);"><?php echo $Notes?></textarea>		</td>
	</tr>
	<?php }?>
	<?php	
	if($whsle){
	?>
	<tr>
	  <td colspan="2">
		<fieldset id="whsleInformation">
			<legend><strong><?php echo ($umResellerWord);?> License Information</strong></legend>
			Current Status:
			<?php
			switch(true){
				case $mode=='insert':
					?><strong style="color:DARKRED;">APPLYING</strong><?php
				break;
				case $WholesaleAccess==WHSLE_NO:
					?>
					<strong style="color:DARKRED;">NO WHOLESALE ACCESS</strong>&nbsp;&nbsp;
					<input name="WholesaleReapply" type="checkbox" id="WholesaleReapply" value="1" />
					<?php
				break;
				case $WholesaleAccess==WHSLE_REJECTED:
					?>
					<strong style="color:DARKRED;">APPLICATION DECLINED</strong>
					<?php
				break;
				case $WholesaleAccess==WHSLE_PENDING:
					?>
					<strong style="color:GOLD;">APPLICATION PENDING</strong>
					<?php
				break;
				case $WholesaleAccess==WHSLE_APPROVED:
					?>
					<strong style="color:DARKBLUE;">APPLICATION APPROVED</strong>
					<?php
				break;
				default:
					mail(
						$developerEmail,
						'Unknown case',
						get_globals(),
						$usemod['fromHdrBugs']
					);
			
			}
			?>
			<table cellpadding="3" cellspacing="0">
				<tr>
					<td><?php echo ($umResellerWord);?> Number or ID</td>
					<td><input name="WholesaleAccess" type="hidden" id="WholesaleAccess" value="<?php echo $WholesaleAccess;?>" />
							<input name="WholesaleNumber" type="text" id="WholesaleNumber" value="<?php echo $mode=='insert'?'not given':$WholesaleNumber?>" onchange="dChge(this);"></td>
				</tr>
				<tr>
					<td>Location or State of Number/ID</td>
					<td>
					<select name="WholesaleState" id="WholesaleState" onChange="dChge(this);" style="width:225px;">
						<option value="" class="ghost"> &lt;Select..&gt; </option>
						<?php
						$gotState=false;
						if($mode=='insert')$WholesaleState='TX';
						if(!$states)$states=q("SELECT st_code, st_name FROM aux_states",O_COL_ASSOC, $public_cnx);
						foreach($states as $n=>$v){
							?><option value="<?php echo $n?>" <?php
							if($WholesaleState==$n){
								$gotState=true;
								echo 'selected';
							}
							?>><?php echo h($v)?></option><?php
						}
						if(!$gotState && $WholesaleState!=''){
							?><option value="<?php echo h($WholesaleState)?>" selected><?php echo $WholesaleState?></option><?php
						}
					?>
					</select> 					</td>
				</tr>
				<tr>
					<td valign="top" colspan="100%"><?php echo ($umResellerWord);?> Notes or Instructions for <?php echo $siteName?><br />

					
					<textarea name="WholesaleNotes" cols="45" rows="3" id="WholesaleNotes" onchange="dChge(this);"><?php echo $WholesaleNotes?></textarea></td>
				</tr>
			</table>
	    </fieldset>
	  </td>
	</tr>
	<?php }?>
	<tr>
		<td colspan="100%">
	    Other notes or instructions:<br />
	    <textarea name="Notes" cols="35" rows="4" id="Notes" onchange="dChge(this);"><?php echo h($Notes);?></textarea>
		</td>
	</tr>
	<tr>
		<td height="28" colspan="2"><?php
			$label=(
				$usemodFormButtonLabel ? $usemodFormButtonLabel :
				($mode=='update' ? 
				($umUpdateButtonText ? $umUpdateButtonText : 'Update '.($whsle ? ' '.$umResellerWord:'').' Information') : 
				($umInsertButtonText ? $umInsertButtonText : 'Create Account'))
			);
			?>
			<input class="cgiButton_A" name="SubmitMain" type="submit" id="SubmitMain" value="<?php echo $label;?>" />
			&nbsp;&nbsp;<span id="submitStatus">&nbsp;</span> </td>
	  </tr>
</table>
<?php 
if($n=$usemod['formHiddenFields']){
	//2013-07-24: allow for custom hidden fields - complete rewrite may allow for posting to another mode or component
	echo $n;
}else{
	?>
	<input name="comboMode" type="hidden" id="comboMode" value="insertUpdate" />
	<input name="mode" type="hidden" id="mode" value="<?php echo $mode?>" />
	<input name="src" type="hidden" id="src" value="<?php 
	if($src){
		echo $src;
	}else if($mode=='insert'){
		echo $usemod['postInsertRedirect'] ? $usemod['postInsertRedirect'] : '/cgi/addresult';
	}
	?>" />
	<input name="EEATOverride" type="hidden" id="EEATOverride" value="<?php echo $EEATOverride?>" />
	<?php
}

//2013-07-24: post form fields
echo $postFormFields;

//jasperandwendy
ob_start();
$res=rand(7,24);
$_a=rand(2,$res-2);
$_b=$res-$_a;
$_q=sqrt($_a)/pow($_b, .3333);
$_r=rand(1,1000000);
?>
<span style="color:darkred;">Verify you are a human being to prevent spam!</span><br />
<span style="font-size:larger;font-family:Georgia, 'Times New Roman', Times, serif;">
<input type="hidden" name="_q" value="<?php echo $_q;?>" />
<input type="hidden" name="_r" value="<?php echo $_r;?>" />
<?php echo $_a . ' + '.$_b.' = ';?><input type="text" size="3" name="_res[<?php echo $_r;?>]" id="_res" value="" />
</span><br />
<?php
$out=(ob_get_contents());
ob_end_clean();
echo $out;

if($usemod['proxyInsertAllow']){
	?><br /><br /><br /><?php
	parse_str($QUERY_STRING,$b);
	$str='';
	if($proxy){
		$b['proxy']=0;
		if(count($b))foreach($b as $n=>$v)$str.=$n.'='.urlencode($v).'&';
		$str=rtrim($str,'&');
		?><a href="usemod<?php echo $str?'?'.$str:'';?>">Remove Proxy Login</a><?php
	}else{
		$qs=preg_replace('/&*proxy=[01]/','',$QUERY_STRING);
		$str=$qs.(strlen($qs)?'&':'').'proxy=1';
		?><a href="usemod<?php echo $str?'?'.$str:'';?>">Do Proxy Login</a><?php
	}
}
?>
</form>