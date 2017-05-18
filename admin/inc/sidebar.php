<?php 
	## GET ALl Page FROM SECTION TABLE
	$all_pages= $dclass->select('*','tbl_sections','ORDER BY i_order');
	// _P($all_pages);

	## GET ADMIN PAGES FROM ADMIN TABLE
	$admin_page_key = $gnrl->getAdminPagesArray();
	
?>
<div class="cl-sidebar" data-position="right" data-step="1" data-intro="<strong>Fixed Sidebar</strong> <br/> It adjust to your needs." >
	<div class="cl-toggle"><i class="fa fa-bars"></i></div>
	<div class="cl-navblock">
		<div class="menu-space" style="position:fixed;" >
			<div class="content">
				<div class="side-user">
					<div class="info">
						<a href="#"><?php echo ucwords( AUNAME );?></a>
						<img src="images/state_online.png" alt="Status" class="fleft" style="margin:2px 2px 0 0;" />
						<span class="fleft">Online</span>
						<span class="fleft">
							<a class="fleft" href="javascript:;" onClick="doLogout();" ><span class="fleft">&nbsp;&nbsp;( Logout )</span></a>
						</span>
						<div class="clear"></div>
						<span class="fleft">
							<a class="fleft" target="_blank" href="<?php echo SITE_URL;?>" ><span class="fleft">View Site</span></a>
						</span>
					</div>
				</div>
				<ul class="cl-vnavigation">
					<?php 
					function put_active( $data = '' ){
						if( is_array( $data ) ){
							echo in_array( BASE_FILE, $data ) ? "active" : "";
						}
						else{ 
							echo BASE_FILE == $data ? "active" : "";
						}
					}
					?>
					<?php 
						foreach ($all_pages as $a_key => $a_value) {?>
							<?php 
								if($a_value['is_parent'] == '1'){ 
									$child_page= $dclass->select('*','tbl_sections','AND is_parent = 0 AND i_parent_id = '.$a_value['id'].' ORDER BY i_order ');
									
									$isProcess = 0;
									if(count($child_page) && !empty($child_page)){
										foreach ($child_page as $c_key => $c_value) { 
											if( in_array($c_value['v_key'],$admin_page_key) ){
												$isProcess = 1;
											}
										}
									}
									// _P($isProcess);
									if( !$isProcess ){
										continue;
									}
									?>
									<li>
				     					<a href="javascript:;"><i class="fa <?php echo $a_value['v_icon']?$a_value['v_icon']:'fa-cogs';  ?>"></i> <span><?php echo $a_value['v_title']; ?></span></a>
										<?php 
											if(count($child_page) && !empty($child_page)){ ?>
												<ul class="sub-menu" style="display:none;">
													<?php 
														foreach ($child_page as $c_key => $c_value) { 
															// _P($c_value['v_key']);
															if( in_array($c_value['v_key'],$admin_page_key) ){ ?>

																<li class="<?php put_active($c_value['v_name']);?>" >
																	<a href="<?php echo $c_value['v_name']; ?>"><i class="fa <?php echo $c_value['v_icon']?$c_value['v_icon']:'fa-cogs'; ?>"></i><span><?php echo $c_value['v_title']; ?></span></a>
																</li>
															
															<?php } ?>
													<?php } ?>
												</ul>
											<?php } ?>
				                    </li>
								<?php 
								}elseif($a_value['is_parent'] == '2'){
									if( in_array($a_value['v_key'],$admin_page_key) ){ ?>

									<li class="<?php put_active($a_value['v_name']);?>" >
										<a href="<?php echo $a_value['v_name']; ?>"><i class="fa <?php echo $a_value['v_icon']?$a_value['v_icon']:'fa-cogs'; ?>"></i><span><?php echo $a_value['v_title']; ?></span></a>
									</li>
									
									<?php } 
								}
							} ?>
							<li class="" ><a href="javascript:;" onClick="doLogout();" ><i class="fa fa-power-off"></i><span>Logout</span></a></li>
							<li class="" ><a href="#"><i class=""></i><span>&nbsp;</span></a></li>
					</ul>
			</div>
		</div>
		<div class="text-right collapse-button" style="padding:7px 9px;">
			<!-- <button id="sidebar-collapse" class="btn btn-default" style=""><i style="color:#fff;" class="fa fa-angle-left"></i></button> -->
		</div>
	</div>
    <div style="padding:7px 9px;" class="text-right collapse-button">
      <button style="" class="btn btn-default" id="sidebar-collapse"><i class="fa fa-angle-left" style="color:#fff;"></i></button>
    </div>
</div>

