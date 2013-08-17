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
 * Code to delete an edition
 *
 * @package    mod
 * @subpackage mgm
 * @copyright  2010 Oscar Campos <oscar.campos@open-phoenix.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');

$id = required_param('id', PARAM_INT);
$delete = optional_param('delete', '', PARAM_ALPHANUM);

require_login();

$systemcontext = context_system::instance();
$PAGE->set_url('/mod/mgm/delete.php');
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('admin');

if (!mgm_can_do_create()) {
    print_error('You do not have the permission to delete this edition.');
}

if (!$site = get_site()) {
    print_error('Site not found!');
}

$strdeleteedition = get_string('deleteedicion', 'mgm');
$stradministration = get_string('administration');
$streditions = get_string('ediciones', 'mgm');

if (!$edition = $DB->get_record('edicion', array('id'=> $id))) {
    print_error('Edition ID was incorrect (can\'t find it)');
}else{
	if ($edition->active == 1){
		print_error('No se puede eliminar una edición activa');
	}
}

$edition->shortname = $edition->name;

if (!$delete) {
    $strdeletecheck = get_string('deletecheck', '', $edition->name);
    $strdeleteeditioncheck = get_string('deleteedicioncheck', 'mgm');
    
    $PAGE->navbar->add( $stradministration);
    $PAGE->navbar->add( $streditions, new moodle_url('index.php'));
    $PAGE->navbar->add( $strdeletecheck);
    $PAGE->set_title("$site->shortname: $strdeletecheck");    
    echo $OUTPUT->header();
    echo $OUTPUT->heading($site->fullname);
    $optionsyes = array('id'=>$edition->id, 'delete'=>md5($edition->timemodified), 'sesskey'=>$USER->sesskey);
    $buttoncontinue = new single_button(new moodle_url("delete.php", $optionsyes), get_string('yes'));
    $buttoncancel   = new single_button(new moodle_url("index.php"), get_string('no'));
    $message = $strdeleteeditioncheck."<br /><br />" . format_string($edition->name);
    echo $OUTPUT->confirm($message, $buttoncontinue, $buttoncancel);    
	echo $OUTPUT->footer();
    exit;
}

if ($delete != md5($edition->timemodified)) {
    print_error('The check variable was wrong - try again');
}

if (!confirm_sesskey()) {
    print_error('confirmsesskeybad', 'error');
}

// OK checks done, delete the edition now.

add_to_log(SITEID, "edition", "delete", "view.php?id=$edition->id", "$edition->name (ID $edition->id)");

$strdeletingedition = get_string("deletingedition", "mgm", format_string($edition->name));


$PAGE->navbar->add( $stradministration);
$PAGE->navbar->add( $streditions, new moodle_url('index.php'));
$PAGE->navbar->add( $strdeletingedition);
$PAGE->set_title("$site->shortname: $strdeletingedition");
echo $OUTPUT->header();
echo $OUTPUT->heading($site->fullname);
echo $OUTPUT->heading($strdeletingedition);

mgm_delete_edition($edition);

echo $OUTPUT->heading( get_string("deletededicion", "mgm", format_string($edition->name)) );
echo $OUTPUT->continue_button("index.php");
echo $OUTPUT->footer();
