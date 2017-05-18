<?php 
include('includes/configuration.php');
$gnrl->check_login();
// $gnrl->isPageAccess(BASE_FILE);
// _P($_REQUEST);
// exit;
   
    extract( $_POST );
    $page_title = "Settle  Wallets";
    $page = "settle_wallets";
    $page2 = "driver_wallets";
    $table = 'tbl_wallet';
    $table2 = 'tbl_user';
    $table3 = 'tbl_wallet_transaction';
    $title2 = 'settle driver Wallet';
    // $v_role ='user';
    $script = ( isset( $_REQUEST['script'] ) && ( $_REQUEST['script'] == 'add' || $_REQUEST['script'] == 'edit'  ) ) ? $_REQUEST['script'] : "";
    
    ## Insert Record in database starts
    if(isset($_REQUEST['submit_btn']) && $_REQUEST['submit_btn']=='Submit'){
      
        $i_user_id=$_REQUEST['id'];
        if($receivable_amount > 0){
            $v_type="company_paid";
            $v_action="minus";
            $f_amount = (-1 * $paid_amount);
            $f_payable= ($receivable_amount - $paid_amount);
            $f_running_balance = $receivable_amount - $paid_amount;

        }elseif ($receivable_amount < 0) {
            $v_type="company_received";
            $f_amount = $paid_amount;
            $v_action="plus";
            $f_payable= (-1 * ($receivable_amount + $paid_amount));
            $f_running_balance = $receivable_amount + $paid_amount;
        }else{
            $v_type="";
            $v_action="";
        }
        $ins = array(
            'i_user_id'  => $i_user_id,
            'v_type' =>$v_type,
            'v_action'  => $v_action,
            'f_amount'=> $f_amount,
            'f_payable'=> $f_payable,
            'f_running_balance'=> $f_running_balance,
            'd_added' => date('Y-m-d H:i:s'),
        );
        
        $id = $dclass->insert( $table3, $ins );
        if($id > 0){

            ##Sum of all transaction 
            $ssql = "SELECT SUM(f_amount) as TOTAL from ".$table3." where i_user_id = ".$i_user_id." ";
            $restepm = $dclass->query($ssql);
            $row = $dclass->fetchResults($restepm);
            $row = $row[0];
            
            ## update the wallet
            if($receivable_amount > 0){
                $str_sql="f_amount + (".$f_amount.")";
            }
            if($receivable_amount < 0){
                $str_sql="f_amount + (".$f_amount.")";
            }
            $ssql2="UPDATE ".$table." SET f_amount = ".$str_sql." WHERE i_user_id = ".$i_user_id." ";
           
            $restepm2 = $dclass->update_sql($ssql2);
            
            $gnrl->redirectTo($page2.".php?succ=1&msg=add_settle");
        }
    }

    ## Delete Record from the database starts
    // if(isset($_REQUEST['a']) && $_REQUEST['a']==3) {
    //     if(isset($_REQUEST['id']) && $_REQUEST['id']!="") {
    //         $id = $_REQUEST['id'];
    //         if($_REQUEST['chkaction'] == 'delete') {
    //             if($gnrl->checkAction('delete') == '1'){
    //                 $dclass->delete( $table ," id = '".$id."'");
    //                 $gnrl->redirectTo($page.".php?succ=1&msg=del");
    //             }else{
    //                 $gnrl->redirectTo($page.".php?succ=0&msg=not_auth");
    //             }
    //         }
    //         // make records active
    //         else if($_REQUEST['chkaction'] == 'active'){
    //             if($gnrl->checkAction('edit') == '1'){
    //                 $ins = array('e_status'=>'active');
    //                 $dclass->update( $table, $ins, " id = '".$id."'");
    //                 $gnrl->redirectTo($page.".php?succ=1&msg=multiact");
    //             }else{
    //                 $gnrl->redirectTo($page.".php?succ=0&msg=not_auth");
    //             }
    //         }
    //         // make records inactive
    //         else if($_REQUEST['chkaction'] == 'inactive'){
    //             if($gnrl->checkAction('edit') == '1'){
    //                 $ins = array( 'e_status' => 'inactive' );
    //                 $dclass->update( $table, $ins, " id = '".$id."'");
    //                 $gnrl->redirectTo($page.".php?succ=1&msg=multiinact");
    //             }else{
    //                 $gnrl->redirectTo($page.".php?succ=0&msg=not_auth");
    //             }
    //         }
    //         // make records active
    //         else if($_REQUEST['chkaction'] == 'delete_image'){
    //             $ins = array('v_image'=>'');
    //             $dclass->update($table,$ins," id='$id'");
    //             $gnrl->redirectTo($page.".php?succ=1&msg=multiact");
    //         }
            
    //     }   
    // }
    
    ## Edit Process
    if(isset($_REQUEST['a']) && $_REQUEST['a']==2) {
        if(isset($_REQUEST['id']) && $_REQUEST['id']!="") {

            $id = $_REQUEST['id'];
            $ssql = "SELECT w.*,
                            u.v_name as driver_name
                          FROM ".$table." as w
                         LEFT JOIN ".$table2." 
                        as u ON w.i_user_id = u.id
                         WHERE true AND w.i_user_id=".$id." ";

            $restepm = $dclass->query($ssql);
            $row = $dclass->fetchResults($restepm);
            $row = $row[0];
            extract( $row );
             
        }
    }

    

?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include('_css.php');?>
</head>

<!-- Fixed navbar -->
<?php include('inc/header.php');?>
<div id="cl-wrapper" class="fixed-menu">
    <?php include('inc/sidebar.php'); ?>
    <div class="container-fluid" id="pcont">
        <?php include('all_page_head.php'); ?>

        <div class="cl-mcont">
            <?php include('all_alert_msg.php'); ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="block-flat">
                        <div class="header">
                            <h3>
                                <?php echo ucwords($title2); ?>
                                <div class="pull-right">
                                    Total Amount = <mark><?php echo $f_amount; ?></mark> 
                                </div>
                            </h3>
                        </div>
                        
                            <form role="form" action="#" method="post" parsley-validate novalidate enctype="multipart/form-data" >
                                <div class="row">
                                    <div class="col-md-6 ">
                                        <div class="content">
                                            <div class="form-group">
                                                <label>Driver Name</label>
                                                <input type="text" class="form-control" id="v_name" name="v_name" value="<?php echo $driver_name; ?>" readOnly="" />
                                            </div>
                                            <div class="form-group">
                                                <label>Receivable Amount</label>
                                                <input type="text" class="form-control" id="receivable_amount" name="receivable_amount" value="<?php echo $f_amount; ?>" required />
                                            </div>
                                            <div class="form-group">
                                                <label>Paid Amount</label>
                                                <input type="text" class="form-control" id="paid_amount" name="paid_amount" value="" required />
                                            </div>
                                            <div class="form-group">
                                                <button class="btn btn-primary" type="submit" name="submit_btn" value="<?php echo ( $script == 'edit' ) ? 'Update' : 'Submit'; ?>"><?php echo ( $script == 'edit' ) ? 'Update' : 'Submit'; ?></button>
                                                <a href="<?php echo $page2?>.php"><button class="btn fright" type="button" name="submit_btn">Cancel</button></a> 
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('_scripts.php');?>
<script type="text/javascript">
    function searchDriver(slug,val){
        window.document.location.href=window.location.pathname+'?'+slug+'='+val;
    }
</script>
<?php include('jsfunctions/jsfunctions.php');?>
</body>
</html>
