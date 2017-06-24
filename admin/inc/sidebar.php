
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
						$BASE_FILE=BASE_FILE;
						if(BASE_FILE == 'log.php') {
							$BASE_FILE='driver.php';
							
						}
						if( is_array( $data ) ){
							return in_array( $BASE_FILE, $data ) ? "active" : "";
						}
						else{ 
							return $BASE_FILE == $data ? "active" : "";
						}
					}
					
					$firstpage = '';
					
					$sidebarSections = $gnrl->getSections();
					
					$parentSectionArr = array();
					
					foreach( $sidebarSections as $rowSidebar ){
						
						$tempSidebarRow = $rowSidebar;
						
						$childSectionArr = array();
						
						if( count( $rowSidebar['childs'] ) ){
							
							foreach( $rowSidebar['childs'] as $tempSidebarRow ){
								
								if( isset( $_SESSION['page_access']['pages'][ $tempSidebarRow['v_key'] ] ) ){
									
									$tempStr = '<li class="'.put_active( $tempSidebarRow['v_name'] ).'" >';
									$tempStr .= '<a href="'.$tempSidebarRow['v_name'].'">';
									$tempStr .= '<i class="fa '.( $tempSidebarRow['v_icon'] ? $tempSidebarRow['v_icon'] : 'fa-angle-right' ).'"></i>';
									$tempStr .= '<span>'.$tempSidebarRow['v_title'].'</span>';
									$tempStr .= '</a>';
									$tempStr .= '</li>';
									$childSectionArr[] = $tempStr;
									
									if( $firstpage == '' ){
										$firstpage = $tempSidebarRow['v_name'];
									}
									
									
								}
								
							}
							
							if( count( $childSectionArr ) ){
							
								$parentSidebarSTR = '<li>';
								$parentSidebarSTR .= '<a href="javascript:;" >';
								$parentSidebarSTR .= '<i class="fa '.( $rowSidebar['v_icon'] ? $rowSidebar['v_icon'] : 'fa-cogs' ).'"></i>';
								$parentSidebarSTR .= '<span>'.$rowSidebar['v_title'].'</span>';
								$parentSidebarSTR .= '</a>';
								$parentSidebarSTR .= '<ul class="sub-menu" style="display:none;">';
								$parentSidebarSTR .= implode( '', $childSectionArr );
								$parentSidebarSTR .= '</ul>';
								$parentSidebarSTR .= '</li>';
								
							}
							
						}
						else{
							
							$parentSidebarSTR = '<li class="'.put_active( $tempSidebarRow['v_name'] ).'" >';
							$parentSidebarSTR .= '<a href="'.$tempSidebarRow['v_name'].'" >';
							$parentSidebarSTR .= '<i class="fa '.( $tempSidebarRow['v_icon'] ? $tempSidebarRow['v_icon'] : 'fa-cogs' ).'"></i>';
							$parentSidebarSTR .= '<span>'.$tempSidebarRow['v_title'].'</span>';
							$parentSidebarSTR .= '</a>';
							$parentSidebarSTR .= '</li>';
							
							if( $firstpage == '' ){
								$firstpage = $tempSidebarRow['v_name'];
							}
							
						}
						
						$parentSectionArr[] = $parentSidebarSTR;
						
					}
					
					if( $_SESSION['redirect_link'] == 'firstpage' && $firstpage ){
						$_SESSION['redirect_link'] = '';
						$gnrl->redirectTo( $firstpage );
					}
					
					echo implode( '', $parentSectionArr );
					
					?>
					
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

