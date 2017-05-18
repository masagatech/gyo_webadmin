<div class="md-modal colored-header custom-width md-effect-9" id="form-primary" style="width:80% !important;" >
		<div class="md-content">
			<div class="modal-header">
				<h3>Keywords for Email Templates</h3>
				<button type="button" class="close md-close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body form" style="max-height:300px; overflow:auto;" >
				<div class="row" style="margin-top:0;" >
					<div class="form-group col-md-12 no-margin">
						<div class="content">
							<div class="row" >
								<?php
								$email_keywords = $gnrl->email_keywords();
								if( count( $email_keywords ) ){
									foreach( $email_keywords as $kKw => $vKw ){
										?>
										<div class="form-group col-md-3" >
											<label><?php echo $vKw; ?></label>
											<input type="text" class="form-control" value="<?php echo $kKw;?>" readonly >
										</div>
										<?php
									}
								} ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default btn-flat md-close" data-dismiss="modal">Cancel</button>
			</div>
		</div>
	</div>
	<div class="md-overlay"></div>