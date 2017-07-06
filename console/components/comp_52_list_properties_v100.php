<div id="mainBody">
	<h2 class="h2_1" style="text-align:right;">Featured Properties </h2>
	<input type="button" name="Button" value="View on Website.." onClick="window.open('../featured_all.php');" />
	<form name="form1" action="list_properties.php" method="get">
	  <input name="ID" type="hidden" id="ID" />
	  <table class="data1" cellpadding="0" cellspacing="0">
	    <thead>
	      <tr>
	        <th>Sort</th>
		    <th>&nbsp;</th>
		    <th>On Page </th>
		    <th>Pictures</th>
		    <th>Order</th>
		    <th>Web</th>
		    <th>Address</th>
		    <th>City</th>
		    <th>Title</th>
		    <th>Property Description </th>
		    <th>Price</th>
		  </tr>
        </thead>
	    <?php
	if($a=q("SELECT * FROM re1_properties ORDER BY Priority ASC", O_ARRAY)){
		foreach($a as $v){
			foreach($v as $n=>$w)if($n!=='Description')$v[$n]=htmlentities($w);
			extract($v);
			?><tr <?php echo $Properties_ID==$ID ? 'style="background-color:THISTLE;"' : (!$Active ? 'style=background-color:#CCC;"' : '')?>>
	      <td valign="top"><label>
	        <input type="image" name="Priority" src="../images/i/red up-down toggle.jpg" onClick="setID(<?php echo $ID?>)" />
          </label></td>
		    <td valign="top" nowrap="nowrap"><a href="../console/resources/bais_01_exe.php?mode=deleteFeaturedProperty&amp;ID=<?php echo $ID?>" title="Delete selected property" target="w2" onClick="if(!confirm('This will permanently delete this property. Continue?'))return false;"><img src="../images/i/del2.gif" alt="" border="0" /></a>&nbsp;&nbsp;<a title="Edit selected property" onClick="return ow(this.href,'l1_property','600,600');" href="focus_properties.php?Properties_ID=<?php echo $ID?>&amp;cbFunction=refreshList"><img src="../images/i/edit2.gif" width="15" height="18" border="0"></a></td>
		    <td  nowrap="nowrap" valign="top"><?php echo str_replace(',',',<br />',$ShowCategory)?>&nbsp;</td>
		    <td  nowrap="nowrap" valign="top">
			    <?php
				$files=0;
				if(is_dir('../images/slides/'.$Handle)){
					if($fp=opendir('../images/slides/'.$Handle)){
						while(false!==($file=readdir($fp))){
							if(!preg_match('/(gif|jpg|png)$/i',$file))continue;
							$files++;
						}
					}
				}
				?>[<a href="../admin/file_explorer/?folder=slides/<?php echo $Handle?>" title="View Images" onClick="return ow(this.href,'l1_propertyimages','800,800');"><?php echo $files . ' pictures';?>]</a><?php
				
				?>			</td>
		    <td valign="top">[<a title="sort order for pictures" onClick="return ow(this.href,'l1_sort','700,700');" href="focus_properties_order.php?Properties_ID=<?php echo $ID?>">order</a>]</td>
		    <td valign="top" <?php 
			if($b=q("SELECT * FROM re1_properties_domain WHERE ID=$ID", O_ROW)){
				echo 'style="background-color:lightgreen;"';
			}else{
			}
			?>><a title="configure site domain" onClick="return ow(this.href,'l1_property','800,700');" href="focus_featured_properties_domain.php?Properties_ID=<?php echo $ID?>&amp;cbFunction=refreshList">
			    <?php
			echo $b?'Yes':'No';
			?>
		    </a></td>
		    <td valign="top"><?php echo $Address?></td>
		    <td valign="top" nowrap="nowrap"><?php echo $City?></td>
		    <td valign="top"><?php echo $PropertyName ?></td>
		    <td valign="top"><?php
			$a=explode(' ',strip_tags($Description));
			for($i=0;$i<=25;$i++){
				echo ' '.$a[$i];
			}
			if(count($a)>25)echo '...';
			?></td>
		    <td align="right" valign="top"><?php echo number_format($Price,2);?></td>
			  </tr><?php
		}
	}
	
	?>
	    <tr>
	      <td>&nbsp;</td>
		  <td>&nbsp;</td>
		  <td colspan="9"><a title="Add new featured property" onClick="return ow(this.href,'l1_property','600,600');" href="focus_properties.php?cbFunction=refreshList"><img src="../images/i/add_32x32.gif" alt="" width="32" height="32" />&nbsp;New Property </a></td>
	  </tr>
      </table>
    </form>
	<p>&nbsp;</p>
	<br />
	<br />
	<br />
	<br />
	<br />
</div>