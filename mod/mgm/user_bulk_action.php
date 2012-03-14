<?php //$Id: user_bulk_message.php,v 1.2.2.1 2007/11/13 09:02:12 skodak Exp $
require_once('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/message/lib.php');
require_once($CFG->dirroot.'/admin/user/user_message_form.php');
require_once('mgm_forms.php');

require_login();
require_capability('mod/mgm:aprobe', get_context_instance(CONTEXT_SYSTEM));

$msg     = optional_param('msg', '', PARAM_CLEAN);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$action = required_param('action', PARAM_INT);

admin_externalpage_setup('userbulkmgm');
require_capability('moodle/site:readallmessages', get_context_instance(CONTEXT_SYSTEM));

$return = $CFG->wwwroot.'/mod/mgm/user_bulk.php';

if (empty($SESSION->bulk_users)) {
    redirect($return);
}
if ($action=='1'){
	if (empty($CFG->messaging)) {
	    error("Messaging is disabled on this site");
	}
  if (! isset($SESSION->bulk_filters['edicionid']) || $SESSION->bulk_filters['edicionid']<=0){
   	error('Debe seleccionar una edición!', $return);
  }
	//TODO: add support for large number of users

	if ($confirm and !empty($msg) and confirm_sesskey()) {
	    $in = implode(',', $SESSION->bulk_users);
	    if ($rs = get_recordset_select('user', "id IN ($in)")) {
	        while ($user = rs_fetch_next_record($rs)) {
	        	  $user->emailstop=0;
	        	  $tmp_msg=$msg;
	        	  $tmp_msg=mgm_parse_msg($tmp_msg, $user->id, $SESSION->bulk_filters['edicionid']);
	            message_post_message($USER, $user, $tmp_msg, FORMAT_HTML, 'direct');

	        }
	    }
	    notice('!Los mensajes han sido enviados!', $return);
	}
	// disable html editor if not enabled in preferences
	if (!get_user_preferences('message_usehtmleditor', 0)) {
	    $CFG->htmleditor = '';
	}

	$msgform = new user_message_form('user_bulk_action.php?action=1');

	if ($msgform->is_cancelled()) {
	    redirect($return);

	} else if ($formdata = $msgform->get_data(false)) {
	    $options = new object();
	    $options->para     = false;
	    $options->newlines = true;
	    $options->smiley   = false;

	    $msg = format_text($formdata->messagebody, $formdata->format, $options);

	    $in = implode(',', $SESSION->bulk_users);
	    $userlist = get_records_select_menu('user', "id IN ($in)", 'fullname', 'id,'.sql_fullname().' AS fullname');
	    $usernames = implode(', ', $userlist);
	    $optionsyes = array();
	    $optionsyes['confirm'] = 1;
	    $optionsyes['sesskey'] = sesskey();
	    $optionsyes['msg']     = $msg;
	    admin_externalpage_print_header();
	    print_heading(get_string('confirmation', 'admin'));
	    print_box($msg, 'boxwidthnarrow boxaligncenter generalbox', 'preview');
	    notice_yesno(get_string('confirmmessage', 'bulkusers', $usernames), 'user_bulk_action.php?action=1', 'user_bulk.php', $optionsyes, NULL, 'post', 'get');
	    admin_externalpage_print_footer();
	    die;
	}

	admin_externalpage_print_header();
	$msgform->display();
	print '<div><a><b>Comonides disponibles para el alumno:</b> #nombre, #apellidos, #email, #cc, #usuario </a></div>';
	print '<div><a><b>Comonides disponibles para el centro:</b> #dgenerica, #despecifica, #cp, #direccion, #localidad, #provincia, #pais, #telefono  </a></div>';
	print '<div><a><b>Comonides disponibles para el curso:</b>  #curso  </a></div>';
	admin_externalpage_print_footer();
}else if ($action=='2'){
	$letterform = new user_letter_form('user_bulk_action.php?action=2');
  if (! isset($SESSION->bulk_filters['edicionid']) || $SESSION->bulk_filters['edicionid']<=0){
   	error('Debe seleccionar una edición!',$return);
  }
	if ($letterform->is_cancelled()) {
	    redirect($return);
	} else if ($data = $letterform->get_data(false)) {
		require_once($CFG->dirroot.'/mod/mgm/reports/letter.class.php');
		$pdffile = new LETTERPDF();
    $pdffile->opCabecera($data->pagetitle,$data->pagehead1,$data->pagehead2);
    $pdffile->opFooter($data->pagefoot1,$data->pagefoot2);
    $filename='letters.pdf';
    $downloadfilename = clean_filename($filename);
    $in = implode(',', $SESSION->bulk_users);
    foreach(array_keys($SESSION->bulk_users) as $userid){
	    $letter_tmp=new stdClass();
	    $letter_tmp->letterhead=$data->letterhead;
	    $letter_tmp->letterbody=$data->letterbody;
	    $letter_tmp->letterfoot=$data->letterfoot;
	    $letter_tmp=mgm_parse_letter($letter_tmp, $userid, $SESSION->bulk_filters['edicionid']);
	    if ($letter_tmp){
	      	$pdffile->AddLetter($letter_tmp->letterhead, $letter_tmp->letterbody, $letter_tmp->letterfoot);
	    }
    }
		ob_get_clean ();//vaciar buffer antes de descargar el fichero
    $pdffile->Output($name=$downloadfilename,$dest='D');
    unset($SESSION->bulk_users);
    unset($SESSION->bulk_filters);
	  die;
	}

	admin_externalpage_print_header();
	$letterform->display();
	print '<div><a><b>Comonides disponibles para el alumno:</b> #nombre, #apellidos, #email, #cc, #usuario  </a></div>';
	print '<div><a><b>Comonides disponibles para el centro:</b> #dgenerica, #despecifica, #cp, #direccion, #localidad, #provincia, #pais, #telefono  </a></div>';
	print '<div><a><b>Comonides disponibles para el curso:</b>  #curso  </a></div>';
	admin_externalpage_print_footer();
}
?>