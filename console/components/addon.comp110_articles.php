<?php
$mergeAvailableContentCategories=array(
	'scheme'=>array(
		'Article' => array( 'idx'=>2 ),
		'Bulletin' => array( /* my official grouping is "Bulletin or Announcement" */
			'idx' =>1,
		),
		'Press Release'=>array( 'active'=>0 ),
		'Blog'=>array( 'active'=>0 ),
		'Forum Post'=>array( 'active'=>0 ),
		'Video'=>array('idx'=>3),
	)
);

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






$mergeAvailableCols['Content']['embedded']['first']['scheme']=array(
	'Verified'=>array(
		'method'=>'function',
		'fieldExpressionFunction'=>'is_verified(\'EnrollmentAuthToken\')',
		'format'=>'default', /* use field attributes themselves*/
		'datatype'=>'logical', /* could be email, phone number, URL, link, popup - conflicts possible */
		'sortable'=>true, /* the default */
		'sortTitle'=>'Sort by email verified status',
		'header'=>'Verified',
		/* this called AFTER $sort and $asc present but before the query, for sort=Status */
		'orderBy'=>"IF(EnrollmentAuthToken IS NOT NULL,1,0) $asc, /* extra stuff is nice :) */ LastName $asc, FirstName $asc",
		/* ------- etc., etc., etc. -------- */
		'colorCoding'=>NULL,
		'visibility'=>COL_VISIBLE,
		'colposition'=>2.5
	)
);

$availableCols[$datasetGroup][$modApType][$modApHandle]=array(
	'version'=>1.0,
	'description'=>'This is the first array implementation of relatebase_views and _views_items',
	'scheme'=>array(
		'PostDate' =>array(
		
		),
		'Title' =>array(
		
		),
		'SubTitle' =>array(
		
		),
		'Category' =>array(
		
		),
		'SubCategory' =>array(
		
		),
		'Author' =>array(
			'method'=>'function',
			'fieldExpressionFunction'=>'get_author()',
		
		),
		'Description' =>array(
			'method'=>'function',
			'fieldExpressionFunction'=>'text_manage(',
		
		),
		'Notes' =>array(
			'method'=>'function',
			'fieldExpressionFunction'=>'RepCode',
		
		),
		'Body' =>array(
			'method'=>'function',
			'fieldExpressionFunction'=>'RepCode',
		
		),
		'Keywords' =>array(
			'method'=>'function',
			'fieldExpressionFunction'=>'RepCode',
		
		),
		'FeaturedImage' =>array(
			'method'=>'function',
			'fieldExpressionFunction'=>'RepCode',
		
		)
	)
);



$availableCols[$datasetGroup][$modApType][$modApHandle]=array(
	'version'=>1.0,
	'description'=>'This is the first array implementation of relatebase_views and _views_items',
	'scheme'=>array(
		/*list these in order they would normally appear; analogous to Tbird's list of all inbox cols available */
		'RepCode'=>array(
			'method'=>'field',
			'fieldExpressionFunction'=>'RepCode',
			'header'=>'Rep',
			'orderBy'=>'RepCode $asc, LastName $asc, FirstName $asc',
			'visibility'=>COL_VISIBLE,
			'colposition'=>1
		),
		'StatusHandle'=>array(
			'method'=>'field', /* the default */
			'fieldExpressionFunction'=>'StatusHandle', /* the default */
			'format'=>'default', /* use field attributes themselves*/
			'datatype'=>'text', /* could be email, phone number, URL, link, popup - conflicts possible */
			'sortable'=>true, /* the default */
			'sortTitle'=>'Sort by member status',
			'header'=>'Status',
			/* this called AFTER $sort and $asc present but before the query, for sort=Status */
			'orderBy'=>'StatusHandle $asc, /* extra stuff is nice :) */ LastName $asc, FirstName $asc',
			/* ------- etc., etc., etc. -------- */
			'colorCoding'=>NULL,
			'visibility'=>COL_VISIBLE,
			'colposition'=>2
		),
		'CreateDate'=>array(
			'method'=>'function',
			'fieldExpressionFunction'=>'member_cols(\'CreateDate\')',
			'sortable'=>true,
			'sortTitle'=>'Sort by record creation date',
			'header'=>'Created',
			'orderBy'=>'CreateDate $asc',
			'nowrap'=>true,
			'visibility'=>COL_VISIBLE,
			'colposition'=>3
		),
		'CompanyName'=>array(
			'header'=>'Company',
			'orderBy'=>'CompanyName $asc, LastName $asc, FirstName $asc',
			'visibility'=>COL_VISIBLE,
			'colposition'=>4
		),
		'Name'=>array(
			'method'=>'function',
			'fieldExpressionFunction'=>'h($LastName.\', \'.$FirstName)',
			'datatype'=>'name', /* not used yet :) */
			'format'=>'LNFN',
			'orderBy'=>'LastName $asc, FirstName $asc',
			'visibility'=>COL_VISIBLE,
			'colposition'=>5
		),
		'Email'=>array(
			'datatype'=>'email',
			'orderBy'=>'Email $asc, LastName $asc, FirstName $asc',
			'visibility'=>COL_VISIBLE,
			'colposition'=>6
		),
		'Phones'=>array(
			'method'=>'function',
			'fieldExpressionFunction'=>'member_cols(\'Phones\')',
			'sortable'=>false,
			'visibility'=>COL_VISIBLE,
			'colposition'=>7
		),
		'BusinessAddress'=>array(
			'method'=>'function',
			'header'=>'Bus. Address',
			'fieldExpressionFunction'=>'member_cols(\'BusinessAddress\')',
			'sortable'=>true,
			'orderBy'=>'BusinessCountry $asc, BusinessState $asc, BusinessCity $asc, BusinessAddress $asc',
			'visibility'=>COL_VISIBLE,
			'colposition'=>8
		),
		'HomeAddress'=>array(
			'method'=>'function',
			'header'=>'Home Address',
			'fieldExpressionFunction'=>'member_cols(\'HomeAddress\')',
			'sortable'=>true,
			'orderBy'=>'HomeCountry $asc, HomeState $asc, HomeCity $asc, HomeAddress $asc',
			'visibility'=>COL_VISIBLE,
			'colposition'=>9
		),
		'ClientAddress'=>array(
			'method'=>'function',
			'header'=>'Company Address',
			'fieldExpressionFunction'=>'member_cols(\'ClientAddress\')',
			'sortable'=>true,
			'orderBy'=>'ClientCountry $asc, ClientState $asc, ClientCity $asc, ClientAddress $asc',
			'visibility'=>COL_VISIBLE,
			'colposition'=>10
		)
	)
);


?>