<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod
 * @subpackage mgm
 * @copyright  2011 Oscar Campos <oscar.campos@open-phoenix.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');

$id = required_param('id', PARAM_INT);
$active = optional_param('active', '', PARAM_ALPHANUM);

require_login();

if (!mgm_can_do_edit()) {
    print_error('You do not have the permission to active this edition.');
}

if (!$site = get_site()) {
    print_error('Site not found!');
}

$stractiveedition = get_string('activaedicion', 'mgm');
$strdeactiveedition = get_string('desactivaedicion', 'mgm');
$stradministration = get_string('administration');
$streditions = get_string('ediciones', 'mgm');

$systemcontext = context_system::instance();
$PAGE->set_url('/active.php');
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('admin');


if (!$edition = $DB->get_record('edicion', array('id'=> $id))) {
    print_error('Edition ID was incorrect (can\'t find it)');
}

$edition->shortname = $edition->name;

$navlinks = array();

if (!$active) {
    $stractivecheck = get_string('activar', 'mgm');
    $strdeactivecheck = get_string('desactivar', 'mgm');
	$PAGE->navbar->add($stradministration);
	$PAGE->navbar->add($streditions, "index.php");

    if (!$edition->active) {
    	$PAGE->navbar->add($stractivecheck);        
        $strcheck = $stractivecheck;
        $stredition = $stractiveedition;
    } else {
    	$PAGE->navbar->add($strdeactivecheck);        
        $strcheck = $strdeactivecheck;
        $stredition = $strdeactiveedition;
    }
    $PAGE->set_title("$site->shortname: $strcheck");
    $PAGE->set_heading($site->fullname);    
    $buttoncontinue = new single_button(new moodle_url("active.php?id=$edition->id&amp;active=".md5($edition->timemodified)."&amp;sesskey=$USER->sesskey"), get_string('yes'));
    $buttoncancel   = new single_button(new moodle_url("index.php"), get_string('no'));
    echo $OUTPUT->header();
    echo $OUTPUT->confirm($stredition."<br /><br />" . format_string($edition->name), $buttoncontinue, $buttoncancel);
    echo $OUTPUT->footer();    
    exit;
}

if ($active != md5($edition->timemodified)) {
    print_error('The check variable was wrong - try again');
}

if (!confirm_sesskey()) {
    print_error('confirmsesskeybad', 'error');
}

$stractivingedition = get_string('activing', 'mgm', format_string($edition->name));
$strdeactivingedition = get_string('deactiving', 'mgm', format_string($edition->name));
$PAGE->navbar->add($stradministration);
$PAGE->navbar->add($streditions, "index.php");
$PAGE->navbar->add(($edition->active) ? $strdeactivingedition : $stractivingedition);
$PAGE->set_title($site->shortname.": ".($edition->active) ? $stractivingedition : $strdeactivingedition);
$PAGE->set_heading($site->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading((!$edition->active) ? $stractivingedition : $strdeactivingedition);
if ($edition->active) {
	  if ($edition->state == 'preinscripcion' || $edition->state == 'matriculacion'){
	  	print_error('No se puede desactivar una edición que está en estado de Preinscripción o Matriculación', $CFG->wwwroot.'/mod/mgm/index.php?editionedit=on');
	  }else{
    	mgm_deactive_edition($edition);
    	echo $OUTPUT->heading(get_string("deactivededicion", "mgm", format_string($edition->name)));    	
	  }
} else {
		if ($aedition=mgm_get_active_edition() and ($aedition->state=='matriculacion' || $aedition->state=='preinscripcion')){
		 	print_error('La edicion activa está en estado de Preinscripción o Matriculación', $CFG->wwwroot.'/mod/mgm/index.php?editionedit=on');
		}
    mgm_active_edition($edition);
    echo $OUTPUT->heading(get_string("activededicion", "mgm", format_string($edition->name)));
}
echo $OUTPUT->continue_button("index.php"); 
echo $OUTPUT->footer();
