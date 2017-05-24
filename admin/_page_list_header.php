<?php
if( BASE_FILE == 'languages.php' ){
	
}
?>
<div class="header">
	<h3>
		<?php echo $script ? ucfirst( $script ).' '.ucfirst( $title2 ) : 'List of '.' '.ucfirst( $title2 ); ?> 
		<?php 
		if( !$script && $gnrl->isAllow('add') ){?>
			<a href="<?php echo $page?>.php?script=add" class="fright">
				<button class="btn btn-primary" type="button">Add New</button>
			</a>
		<?php } ?>
	</h3>
</div>