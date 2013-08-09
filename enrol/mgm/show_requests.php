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
 * @package    enrol
 * @subpackage mgm
 * @copyright  2010 Oscar Campos <oscar.campos@open-phoenix.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot."/mod/mgm/locallib.php");

require_login();

if (!isloggedin() or isguestuser()) {
    error('You need to be logged into the platform!');
}

if (!$preinscripcion = $DB->get_records('edicion_preinscripcion', array('userid'=> $USER->id))) {
    error(get_string('nohaydatos', 'mgm'));
}
$context=context_user::instance($USER->id);
//context_module::instance($moduleinstance);
$PAGE->set_context($context);
$PAGE->set_url('/enrol/mgm/show_requests.php');
// $navlinks = array();
// $navlinks[] = array('name' => get_string('ediciones', 'mgm'), 'link' => ".", 'type' => 'misc');
// $navigation = build_navigation($navlinks);
$PAGE->set_heading(ucfirst($USER->lastname).', '. ucfirst($USER->firstname));
echo $OUTPUT->header();
echo '<br />';

foreach($DB->get_records('edicion', array(),'inicio desc') as $edition) {
    $choices = array();
    if (!$options = mgm_get_edition_user_options($edition->id, $USER->id)) {
        continue;
    } else {
    	echo $OUTPUT->heading($edition->name);
    	echo $OUTPUT->box_start();        
        $plus = 0;
        for ($i = 0; $i < count($options)+$plus; $i++) {
            foreach (mgm_get_edition_courses($edition) as $course) {
                $choices[$i][$course->id] = $course->fullname;
            }
        }
    }
    $date = mgm_get_preinscription_timemodified($edition->id, $USER->id);
    $date = date("d/m/Y H:i\"s", $date->timemodified);

    // Print form
    require_once($CFG->dirroot.'/enrol/mgm/enrol_form.php');
    $eform = new enrol_mgm_ro_form('enrol.php', compact('course', 'edition', 'choices', 'date'));
    if ($options) {
        $data = new stdClass();
        foreach ($options as $k=>$v) {
            $prop = 'option['.$k.']';
            $data->$prop = $v;
        }

        $eform->set_data($data);
    }

    $eform->display();

    if ($edition->state='matriculacion' && $edition->active==true){
    	  	echo get_string('provisional', 'mgm');
    }
    if ($inscripcion = mgm_get_user_inscription_by_edition($USER, $edition)) {
        $ctemp = $DB->get_record('course', array('id'=> $inscripcion->value));
        echo get_string('cconcedido', 'mgm').$ctemp->fullname;
    } else {
    		if ($adescs = mgm_get_edicion_descartes($edition->id, $USER->id)){
    			foreach ($adescs as $i=>$d){
    				$ctemp = $DB->get_record('course', array('id'=> $i));
    				if ($DB->record_exists('edicion_inscripcion', array('edicionid'=> $edition->id, 'value'=> $i, 'released'=> 1))) {
    					echo '<br />Curso: '. $ctemp->fullname.' - Motivo de descarte: '.get_string('serror_'.$d, 'mgm');
    				}
    			}
    		}else{
        	echo get_string('cconcedidono', 'mgm');
    		}
    }

	echo $OUTPUT->box_end();
    
}
echo $OUTPUT->box_start();
// print_single_button('javascript: window.print();', '', get_string('pageprint', 'mgm', 'get'));
$button = '<form><input type="button" onclick="javascript: window.print();" name="Print" value='.get_string('pageprint', 'mgm', 'get').'></form>';
print $button;
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
