<?php
/*
2010-5-14
	Parker	
	Changed "View" Button to actually link to something, can change the blog/articles focus page with $featuredArticlePage. Can also now handle "pretty urls"*/
	if(!$articleQuery)$articleQuery='SELECT * FROM cms1_articles ORDER BY Priority ASC';
	if(!$articleFormAction)$articleFormAction='list_articles.php';
	if(!$articleAddNewLink)$articleAddNewLink='focus_articles.php';
	if(!$articleDeleteLink)$articleDeleteLink='resources/bais_01_exe.php';
if(!$refreshComponentOnly){
	?><script language="javascript" type="text/javascript">
	function setID(id){
		g("ID").value=id;
	}
	</script>
	<h2 class="h2_1" style="text-align:right;">Content Managment - Featured Articles</h2>
	<style>
	.data1 td, .data1 th{
		padding:3px 5px 1px 8px;
	}
	table.data1{
		border-collapse:collapse;
		background-color:white;
	}
	.data1 td{
		border:1px solid #CCC;
	}
	</style>
	<?php
	}
?>
<form action="<?php echo $articleFormAction?>" method="get" name="form1" id="form1">
	<input name="ID" type="hidden" id="ID" />
	<div style="background-color:#FFF;border:1px dashed #333;padding:15px;"> Article in light green is the lead (featured) article on the home page<br />
		Articles in gray are inactive<br />
	</div>
	<table class="data1" cellpadding="0" cellspacing="0">
		<thead>
			<tr>
				<th><img src="../images/i/red up-down toggle.jpg" alt="" width="18" height="18" /></th>
				<th>&nbsp;</th>
				<th>Private</th>
				<th>Category</th>
				<th>Title</th>
				<th>Sub-Title</th>
				<th>Summary</th>
				<th>Body </th>
				<th>Featured Image </th>
				<th>View</th>
			</tr>
		</thead>
		<?php
		if($a=q($articleQuery, O_ARRAY, C_MASTER)){
			foreach($a as $v){
				foreach($v as $n=>$w)if($n!=='Body')$v[$n]=htmlentities($w);
				extract($v);
				?>
		<tr id="r_<?php echo $ID?>" <?php echo $LeadArticle ? 'style="background-color:LIGHTGREEN;"' : ($Properties_ID==$ID ? 'style="background-color:THISTLE;"' : (!$Active ? 'style=background-color:#CCC;"' : ''))?>>
			<td><label>
				<input type="image" name="Priority" src="../images/i/red up-down toggle.jpg" onclick="setID(<?php echo $ID?>)" />
			</label></td>
			<td nowrap="nowrap">
				<a href="<?php echo $articleDeleteLink?>?mode=deleteFeaturedArticle&ID=<?php echo $ID?>" title="Delete selected article" target="w2" onclick="if(!confirm('This will permanently delete this article. Continue?'))return false;"><img src="../images/i/del2.gif" alt="" border="0" /></a>
				&nbsp;&nbsp;
				<a title="Edit selected article" href="<?php echo $articleAddNewLink?>?ID=<?php echo $ID?>" onclick="return ow(this.href,'l1_articles','700,600');"><img src="../images/i/edit2.gif" width="15" height="18" border="0" /></a></td>
			<td><?php echo $Private?'Yes':'No'?></td>
			<td><?php echo $Category?></td>
			<td><?php echo $Title?></td>
			<td><?php echo $SubTitle?></td>
			<td><?php echo $Summary ? $Summary : '(none)' ?></td>
			<td><?php $a=explode(' ',strip_tags($Body,'<i><b><em>'));
			for($i=0;$i<=35;$i++){
				echo ' '.$a[$i];
			}
			if(count($a)>35)echo '...';
			?></td>
			<td><?php echo $FeaturedImage ? 'Yes':'No';?></td>
			<td><input type="button" name="Button" value="View" onclick="window.open('<?php if(!$KeywordsTitle){echo $featuredArticlePage?>?Articles_ID=<?php echo $ID?><?php }else{ echo '/'.str_replace(' ','-',$KeywordsTitle); } ?>','l0_articles');"/></td>
		</tr>
		<?php
			}
		}
		
		?>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td colspan="7"><a title="Add new article" href="<?php echo $articleAddNewLink?> " onclick="return ow(this.href,'l1_articles','700,600');"><img src="../images/i/add_32x32.gif" alt="" width="32" height="32" />&nbsp;New Article </a></td>
		</tr>
	</table>
</form>