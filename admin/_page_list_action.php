
	<?php
	$listActions = array();
	if( $gnrl->isAllow('edit') ){
		$listActions['edit'] = '<li><a href="'.$page.'.php?a=2&script=edit&id='.$row['id'].'">Edit</a></li>';
		$listActions['active'] = '<li><a href="'.$page.'.php?a=3&amp;chkaction=active&amp;id='.$row['id'].'">Active</a></li>';
		$listActions['inactive'] = '<li><a href="'.$page.'.php?a=3&amp;chkaction=inactive&amp;id='.$row['id'].'">Inactive</a></li>';
	}
	if( $gnrl->isAllow('delete') ){
		$listActions['delete'] = '<li><a href="javascript:;" onclick="confirm_delete(\''.$page.'\',\''.$row['id'].'\');">Delete</a></li>';
	}
	if( $gnrl->isAllow('view') ){
		
	}
	
	if( BASE_FILE == 'languages.php' ){
		unset( $listActions['delete'] );
	}
	
	
	?>
	
	
	<td class="text-right" >
		<?php
		if( count( $listActions ) ){
			?>
			<div class="btn-group">
				<button class="btn btn-default btn-xs" type="button">Actions</button>
				<button data-toggle="dropdown" class="btn btn-xs btn-primary dropdown-toggle" type="button">
					<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
				</button>
				<ul role="menu" class="dropdown-menu pull-right">
					<?php echo implode( '', $listActions ); ?>
				</ul>
			</div> <?php 
		}
		else{
			echo 'No Actions';
		}?>
	</td>