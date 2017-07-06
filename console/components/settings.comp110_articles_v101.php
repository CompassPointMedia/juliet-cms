<?php
$availableContentCategories=array(
	'settings'=>array(
		'comments'=>'first use of an array like this outside of a dataset, in this case for options.  Created 2010-01-20 by Samuel',
		'allow_multiple'=>'', /* options are comma-separated and relational */
	),
	'scheme'=> array(
		'Article'=> array(
			/* ---------- this has the full set of keys -------------- */
			'idx'=>1,
			'label'=>'Article', /* only needed if differs from this key */
			'value'=>'Article', /* only needed if differs from this key */
			'title'=>'Informational or instructional article',
			'active'=>1,
			'id'=>NULL,  /* i.e. if null this is not abstracted */
		),
		'Bulletin'=> array( /* my official grouping is "Bulletin or Announcement" */
			'idx'=>2,
			'label'=>'Bulletin/Announcement',
			'title'=>'Bulletin or Announcement',
		),
		'Press Release'=> array( /* my official grouping is "New and Press Releases" */
			'idx'=>3,
			'title'=>'News or press release',
		),
		'Blog'=> array(
			'idx'=>4,
			'label'=>'Blog Entry',
			'title'=>'Blog',
		),
		'Forum Post'=> array(
			'idx'=>5,
			'title'=>'Forum Post',
		),
		'Video'=>array(
			'idx'=>6,
			'title'=>'Video',
		),
	)
);



/* ----------- usage ------------

foreach($availableContentCategories['scheme'] as $n=>$v){
	if(isset($v['active']) && !$v['active'])unset($availableContentCategories['scheme'][$n]);
}
$availableContentCategories['scheme']=subkey_sort($availableContentCategories['scheme'],'idx');

?>
<select name="Category<?php if($availableContentCategories['settings']['allow_multiple'])echo '[]'?>" id="Category<?php if($availableContentCategories['settings']['allow_multiple'])echo '[]'?>" onchange="dChge(this)">
	<option value="">&lt; Select.. &gt;</option>
	<?php
	foreach($availableContentCategories['scheme'] as $n=>$v){
		?><option title="<?php echo $v['title']?>" value="<?php echo $v['id'] ? $v['id'] : ($v['value'] ? $v['value'] : $n)?>" <?php
		
		
		?>><?php echo h($v['label'] ? $v['label'] : $n);?></option><?php
	}
	
	?>
</select>
<?php
   ------------------------------ */
?>