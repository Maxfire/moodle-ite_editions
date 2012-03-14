<?php  //$Id: user_bulk.php,v 1.4.2.4 2008/02/05 15:22:08 poltawski Exp $

require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/mod/mgm/mgm_forms.php');

admin_externalpage_setup('userbulkmgm');
if (!isset($SESSION->bulk_users)) {
    $SESSION->bulk_users = array();
}
if (!isset($SESSION->bulk_filters)) {
    $SESSION->bulk_filters = array();
}
// create the user filter form
$ufiltering = new user_filter();
if (isset($_POST['submit_action']) ) {
    $action=$_POST['action'];
    if (isset($_POST['users'])){
    	$SESSION->bulk_users = $_POST['users'];
    }
		redirect($CFG->wwwroot.'/mod/mgm/user_bulk_action.php?action='.$action);

}
if ($data = $ufiltering->get_data(false)) {
	$ufiltering->set_filters($data);
  if (!empty($data->reset)) {
        $ufiltering->reset_filters();
        unset($_POST);
        unset($SESSION->bulk_users);
        unset($SESSION->bulk_filters);
        redirect($CFG->wwwroot.'/mod/mgm/user_bulk.php');
  }else if (!empty($data->show)){
  	admin_externalpage_print_header();
  	$ufiltering->display();
  	$users=$ufiltering->get_users();
  	print '<form action="user_bulk.php" method="POST">';
   	$ufiltering->print_users_table($users);
   	print '<center>';
   	print '<input type="hidden" value="1" name="confirm">
    	<input type="hidden" value="'.sesskey().'" name="sesskey">
    	<SELECT name="action">
		  <OPTION VALUE="0">Elegir</OPTION>
		  <OPTION VALUE="1">Enviar mensaje</OPTION>
		  <OPTION VALUE="2">Generar cartas</OPTION>
			</SELECT>
    	<input type="submit" name="submit_action" id="Submit_btn1" value="'.get_string('next').'">
    	</form>';
   	print '</center>';
   	admin_externalpage_print_footer();
   	die();
  }
}

admin_externalpage_print_header();
$ufiltering->display();
admin_externalpage_print_footer();

?>
