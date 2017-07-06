	<div id="btns140" style="float:right;">
	  <!--
			Navbuttons version 1.41. Last edited 2008-01-21.
			This button set came from devteam/php/snippets
			Now used in a bunch of RelateBase interfaces and also client components. Useful for interfaces where sub-records are present and being worked on.
			-->
	  <?php if($mode==$updateMode){ ?>
	  <input type="button" name="Button2" value="Set Up Domain Name.." onclick="if(detectChange &amp;&amp; !confirm('You have changed the record; if you switch to Set Up Domain Name, you will lose these changes.  Are you sure?'))return false; window.location=('focus_featured_properties_domain.php?Properties_ID=<?php echo $$object?><?php
			//pass on any callback function
			foreach($_GET as $n=>$v){
				if(preg_match('/^cb/',$n)){
					echo '&'.$n .'='.$v;
				}
			}
			
			?>');" />
	  <?php }?>
	  <?php
			//Things to do to install this button set:
			#1. install contents of this div tag (btns140)
			#2. the coding above needs to go in the head of the document, change as needed to connect to the specific table(s) or get the resource in a different way
			#3. must declare the following vars in javascript:
			// var thispage='whatever.php';
			// var thisfolder='myfolder';
			// var count='[php:echo $nullCount]';
			// var ab='[php:echo $nullAbs]';
			#4. need js functions focus_nav() and focus_nav_cxl() in place
			?>
	  <input id="Previous" type="button" name="Submit2" value="Previous" onclick="focus_nav(-1, '<?php echo ($mode==$insertMode?'insert':'update')?>', <?php echo $mode==$insertMode?1:0?>, 0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs<=1?'disabled':''?>>
	  <?php
			//Handle display of all buttons besides the Previous button
			if($mode==$insertMode){
				if($insertType==2 /** advanced mode **/){
					//save
					?>
	  <input id="Save" type="button" name="Save" value="Save" onclick="focus_nav(0,'insert',1,2<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveInitiallyDisabled?> />
	  <?php
				}
				//save and new - common to both modes
				?>
	  <input id="SaveAndNew" type="button" name="SaveAndNew" value="Save &amp; New" onclick="focus_nav(0,'insert', 1,1<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndNewInitiallyDisabled?> />
	  <?php
				if($insertType==1 /** basic mode **/){
					//save and close
					?>
	  <input id="SaveAndClose" type="button" name="SaveAndClose" value="Save &amp; Close" onclick="focus_nav(0,'insert', 1,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $saveAndCloseInitiallyDisabled?> />
	  <?php
				}
				?>
	  <input id="CancelInsert" type="button" name="CancelInsert" value="Cancel" onclick="focus_nav_cxl('insert');" />
	  <?php
			}else{
				//OK, and appropriate [next] button
				?>
	  <input id="OK" type="button" name="ActionOK" value="OK" onclick="focus_nav(0,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" />
	  <input id="Next" type="button" name="Next" value="Next" onclick="focus_nav(1,'<?php echo $mode==$insertMode?'insert':'update'?>',0,0<?php echo $navQueryFunction ? ", '".$navQueryFunction . "'" :'';?>);" <?php echo $nullAbs>$nullCount || ($denyNextToNew && $nullAbs==$nullCount) ?'disabled':''?> />
	  <?php
			}
			// *note that we could go back to the same page the 'New Record' click appeared on, but there's major issues programmatically on whether it would shift becuase of the placement of the new record.
			// *note that the primary key field is now included here to save time
			?>
	  <input name="<?php echo $recordPKField?>" type="hidden" id="<?php echo $recordPKField?>" value="<?php echo $$object;?>" />
	  <input name="navVer" type="hidden" id="navVer" value="<?php echo $navVer?>" />
	  <input name="navObject" type="hidden" id="navObject" value="<?php echo $navObject?>" />
	  <input name="nav" type="hidden" id="nav" />
	  <input name="navMode" type="hidden" id="navMode" value="" />
	  <input name="count" type="hidden" id="count" value="<?php echo $nullCount?>" />
	  <input name="abs" type="hidden" id="abs" value="<?php echo $nullAbs?>" />
	  <input name="insertMode" type="hidden" id="insertMode" value="<?php echo $insertMode?>" />
	  <input name="updateMode" type="hidden" id="updateMode" value="<?php echo $updateMode?>" />
	  <input name="mode" type="hidden" id="mode" value="<?php echo $mode?>" />\
	  <?php
	  echo $mode;
	  ?>
	  <input name="componentID" type="hidden" id="componentID" value="<?php echo $localSys['componentID']?>" />
	  <?php
			if(count($_REQUEST)){
				foreach($_REQUEST as $n=>$v){
					if(substr($n,0,2)=='cb'){
						if(!$setCBPresent){
							$setCBPresent=true;
							?>
	  <!-- callback fields automatically generated -->
	  <?php
							echo "\n";
							?>
	  <input name="cbPresent" id="cbPresent" value="1" type="hidden" />
	  <?php
							echo "\n";
						}
						if(is_array($v)){
							foreach($v as $o=>$w){
								echo "\t\t";
								?>
	  <input name="<?php echo $n?>[<?php echo is_numeric($o)? '': $o?>]" id="<?php echo $n?>[<?php echo is_numeric($o)? '': $o?>]" type="hidden" value="<?php echo urlencode(stripslashes($w))?>" />
	  <?php
								echo "\n";
							}
						}else{
							echo "\t\t";
							?>
	  <input name="<?php echo $n?>" id="<?php echo $n?>" type="hidden" value="<?php echo urlencode(stripslashes($v))?>" />
	  <?php
							echo "\n";
						}
					}
				}
			}
			?>
	  <!-- end navbuttons 1.41 -->
	</div>
	<div id="mainBody">
		<p>Property Identifier: <strong><?php echo $Handle?></strong></p>
		<?php if($ID){ ?>
	&nbsp;&nbsp;
			<input type="button" name="Submit" value="View in Site" onClick="window.location='../featured.php?Handle=<?php echo $Handle?>';" />
		<?php } ?>
		<br />
		
		
		<?php echo '<div style="float:right;">';
			if(file_exists('../images/slides/featured/.thumbs.dbr/'.$LeadImage)){
				?><img src="<?php echo '../images/slides/featured/.thumbs.dbr/'.$LeadImage?>" />
		<?php
			
			}
		echo '</div>'?>
		
		<input name="Active" type="checkbox" id="Active" value="1" <?php echo !isset($Active) || $Active==1 ? 'checked' : ''?> onChange="mgeChge(this)" />
		Active Property<br />
		Status: 
		<label>
		<select name="Status" id="Status" onChange="mgeChge(this)">
		  <option value="">&lt;select&gt;</option>
		  <option <?php echo $Status=='For Sale'?'selected':''?> value="For Sale">For Sale</option>
		  <option <?php echo $Status=='Sold'?'selected':''?> value="Sold">Sold</option>
		  <option <?php echo $Status=='Closing/In Escrow'?'selected':''?> value="Closing/In Escrow">Closing/In Escrow</option>
		</select>
		</label>
		<br />
		Posting date: 
		<input name="PostDate" type="text" id="PostDate" value="<?php echo date('m/d/Y g:iA',$PostDate ? strtotime($PostDate) : time())?>" onChange="mgeChge(this)" />
		<br />
		<br />
		Property Name: 
		<input name="PropertyName" type="text" id="PropertyName" onChange="mgeChge(this);if(g('KeywordsTitle').value=='')g('KeywordsTitle').value=this.value.replace(/[^a-z0-9 ]+/gi,'').replace(/ {2,}/gi,' ');" value="<?php echo h($PropertyName)?>" size="55" />
		<br />
		Pretty URL/Keywords Title: 
		<input name="KeywordsTitle" type="text" id="KeywordsTitle" onChange="mgeChge(this)" value="<?php echo h($KeywordsTitle)?>" size="55" />
		<em>(spaces will be replaced by dashes)</em><br />
		Price: 
		<input name="Price" type="text" id="Price" onChange="mgeChge(this)" value="<?php echo $Price?>" size="17" />
		<br />
		Category (&quot;Standard&quot; is the long-standing featured page, &quot;Global Exclusives &quot; is the recent page used for other agents):<br />
		<select name="ShowCategory[]" size="2" id="ShowCategory[]" onChange="mgeChge(this)" multiple="multiple">
		  <?php
		$showCategories=array('Standard','Global Exclusives');
		if(!isset($ShowCategory)){
			$ShowCategory=array('Standard');
		}else $ShowCategory=explode(',',$ShowCategory);
		foreach($showCategories as $n=>$v){
			?><option value="<?php echo $v?>" <?php echo in_array($v,$ShowCategory)?'selected':''?>><?php echo $v?></option><?php
		}
		?>
		</select> 
		<br />
		Agent name: 
		<input name="AgentName" type="text" id="AgentName" value="<?php echo isset($AgentName)?$AgentName : (isset($realtorInfo['FullName'])?$realtorInfo['FullName'] : '')?>" onChange="mgeChge(this);" />
		<br />
		Agent email: 
		<input name="AgentEmail" type="text" id="AgentEmail" value="<?php echo isset($AgentEmail)?$AgentEmail : (isset($realtorInfo['Email'])?$realtorInfo['Email'] : '')?>" onChange="mgeChge(this);" />	
		<br />
		<br />
		<br />
	
		<input name="EditDescription" type="checkbox" id="EditDescription" onChange="mgeChge(this)" value="1" />
		Edit Description<br />
		<div id="xToolbar"></div>
		<script type="text/javascript">
		var sBasePath= '/Library/fck6/';
		var oFCKeditor = new FCKeditor('Description') ;
		oFCKeditor.BasePath	= sBasePath ;
		oFCKeditor.ToolbarSet = 'xTransitional' ;
		oFCKeditor.Height = 150;
		oFCKeditor.Width = '100%';
		oFCKeditor.Config[ 'ToolbarLocation' ] = 'Out:xToolbar' ;
		oFCKeditor.Value = '<?php
		//output section text
		if(!preg_match('/<(p|br|div)[^>]*>/i',$Description)){
			//clean up for use in FCK editor
			$Description=nl2br($Description);
		}
		$a=@explode("\n",$Description);
		foreach($a as $n=>$v){
			$a[$n]=trim(str_replace("'","\'",$v));
		}
		echo implode('\n',$a);
		?>';
		oFCKeditor.Create() ;
		</script>
		
		
		<br />
		<!-- Flash Movie Code (optional) <br />
		<textarea name="FlashMovieCode" onChange="mgeChge(this)" cols="45" rows="5" id="FlashMovieCode"><?php echo $FlashMovieCode?></textarea>  -->
		<br />
		Address: 
		<input name="Address" type="text" id="Address" value="<?php echo h($Address)?>" onChange="mgeChge(this)" onBlur="var reg=/[^_a-z0-9]/gi;if(g('Handle').value=='')g('Handle').value=this.value.replace(reg,'').toLowerCase();"/>
		<br />
		City: 
		<input name="City" type="text" id="City" value="<?php echo h($City)?>" onChange="mgeChge(this)" />
		<br />
		State: 
		<select name="State" id="State" onChange="mgeChge(this)">
		  <option value="">- select state -</option>
		  <?php
		$states=q("SELECT st_code, st_name FROM z_public.aux_states", O_COL_ASSOC, array('localhost','z_public','public','z_public'));
		foreach($states as $n=>$v){
			?><option value="<?php echo $n?>" <?php echo $State==$n?'selected':''?>><?php echo $v?></option><?php
		}
		?>
		</select>
		<br />
		Zip Code: 
		<input name="Zip" type="text" id="Zip" value="<?php echo $Zip?>" onChange="mgeChge(this)" />
		<br />
		<br />
	
		<fieldset>
		<legend>Property Identification</legend>
			<p>MLS ID#: 
				<input name="MLSNumber" type="text" id="MLSNumber" value="<?php echo $MLSNumber?>" onChange="mgeChge(this)" />
				<br />
			  <?php if(!$ID){ ?>
			  Identifier (must be unique): 
			  <input name="Handle" type="text" id="Handle" value="<?php echo $Handle?>" size="14" onChange="mgeChge(this)" />
			  <input type="button" name="Button" value="Check" onClick="checkHandle()" /><span id="checkHandle">&nbsp;</span>
			  <?php }else{
				?>
			  LACE Identifier: <strong><?php echo $Handle?></strong><br />
			  <input type="button" name="Button" value="Set Up Domain Name.." onClick="return ow('focus_featured_properties_domain.php?Properties_ID=<?php echo $ID?>','l1_domain','700,700');" />
	
			  <input name="Handle" type="hidden" id="Handle" value="<?php echo $Handle?>" />
			  <?php
			  } ?>
			</p>
		</fieldset>
		<p>&nbsp;</p>
		<p>
		  <input type="button" name="Submit" value="View/Manage Images.." onClick="if(<?php echo !$ID?'true':'false'?>){ alert('You must first save this new property before you add images'); return false; } return ow('../admin/file_explorer/index.php?uid=mgimgs1&folder=slides/<?php echo $Handle?>&createFolder=1','w<?php echo $Handle?>','700,500');" />
		  <br />
		  <script language="javascript" type="text/javascript">
		function showFeatured(){
			file=g('null1').value+'.'+g('null3').value;
			node=g('null2').value;
			if(g('FeaturedImage').value!==file)mgeChge(g('FeaturedImage'));
			g('Featured').innerHTML=g('null1').value;
			g('FeaturedImage').value=file;
			g('deleteFeaturedImage').style.display='block';
		}
		</script>
		  <input type="button" name="Submit" value="Featured (Lead) Image.." onClick="return ow('../admin/file_explorer/index.php?uid=mgimgs2&folder=slides/featured/&createFolder=1&disposition=selector&cbTarget=null1&cbTargetNode=null2&cbTargetExt=null3&cbFunction=showFeatured','w<?php echo $Handle?>','700,500');" />
		  <span id="Featured" style="font-weight:900;"><?php echo htmlentities($FeaturedImage);?></span>
		  <input name="FeaturedImage" type="hidden" id="FeaturedImage" value="<?php echo $FeaturedImage?>" onChange="mgeChge(this)" />
		  <span id="deleteFeaturedImage" style="display:<?php echo $FeaturedImage?'block':'none'?>"><a href="#" onClick="g('FeaturedImage').value='';g('Featured').innerHTML='&nbsp;'; g('deleteFeaturedImage').style.display='none';return false;" >remove image</a></span>
		  <input name="null1" type="hidden" id="null1" value="" />
		  <input name="null2" type="hidden" id="null2" value="" />
		  <input name="null3" type="hidden" id="null3" value="" />
		  <input name="realtorID" type="hidden" id="realtorID" value="<?php	echo $realtorInfo['Agents_ID']?>" />
		  <input name="realtorOffice" type="hidden" id="realtorOffice" value="<?php	echo $realtorInfo['Offices_ID']?>" />
		  <br />
		  <br />
		</p>
	</div>
