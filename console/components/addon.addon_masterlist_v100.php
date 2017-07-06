<?php
if(!$refreshComponentOnly){
	?><style type="text/css">
	#addons{
		border:1px solid #333;
		padding:5px;
		margin:10px 0px;
		}
	#addons ul{
		}
	</style><?php
}
?>
<div id="addons" refreshparams="noparams">
	<input type="hidden" name="noparams" id="noparams" value="" />
	<h2>Add-Ons</h2>
	<?php
	if(count($moduleConfig['1.0']['addons'])){
		?><div id="addonsList"><?php
		foreach($moduleConfig['1.0']['addons'] as $n=>$v){
			?><div class="addon <?php echo $v['disabled']?'gray':''?>">
			<div class="fr">
			[<a title="Disable this add-on without removing it" href="resources/bais_01_exe.php?mode=disableAddon&ID=<?php echo $n?>" target="w2">disable</a>]
			&nbsp;&nbsp;
			[<a title="Remove this add-on completely" href="resources/bais_01_exe.php?mode=deleteAddon&ID=<?php echo $n?>" target="w2">remove</a>]			</div>
			<a href="addons.php?cbFunction=refreshComponent&amp;cbParam=fixed:addons&Addons_ID=<?php echo $n?>" title="Get add-ons to extend or enhance your console" onclick="return ow(this.href,'l1_addons','400,400');" ><?php echo $v['Name']?></a>
			<p>
			<?php echo $v['Description'];?>
			</p></div><?php
		}
		?></div><?php
	}
	?>
	<a title="Get add-ons to extend or enhance your console" onclick="return ow(this.href,'l1_addons','400,400');" href="get_addons.php?cbFunction=refreshComponent&amp;cbParam=fixed:addons">get <?php echo count($settings['addons'])>0?'more':''?> add-ons..</a>
</div>
