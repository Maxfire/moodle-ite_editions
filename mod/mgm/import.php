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
 * @copyright  2011 Jesús Jaén Díaz <jesus.jaen@open-phoenix.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot."/lib/filelib.php");
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot."/mod/mgm/locallib.php");
require_once($CFG->dirroot."/mod/mgm/mgm_forms.php");
require_once($CFG->dirroot."/lib/adodb/adodb.inc.php");
require_once($CFG->libdir.'/adminlib.php');

//admin_externalpage_setup('importdata', mgm_update_edition_button());
require_login();
$systemcontext = context_system::instance();
require_capability('mod/mgm:aprobe', $systemcontext);
$PAGE->set_url('/mod/mgm/import.php');
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('admin');

$strtitle = get_string('importdata','mgm');
$tempdir = $CFG->dataroot."/temp/";

$mform = new edicion_form("$CFG->wwwroot".'/mod/mgm/import.php');
$mform->addFileField();

if ($data = $mform->get_data(false)) {
    if 	(!empty($data->cancel)){
    	unset($_POST);
    	redirect("$CFG->wwwroot".'/index.php');
    }
    else if (!empty($data->next)) {
    	$name = $mform->get_new_filename('userfile');
    	$filename = $tempdir . $name;
    	$save= $mform->save_file( 'userfile', $tempdir . $name, true);
    	if ($save) {    		
    		echo 'fichero' . $filename . ' guardado';    		
    	}
    	$edicion = $data->edition;
    	if (isset($edicion) and $edicion != 0 and $save){
    		$idata=new ImportData($filename);
    		$tables = $idata->setDataHistory();
    		echo "Tablas: " . $tables;
			die();

    	}else{
    		print_error('invalidedition', 'mgm', "$CFG->wwwroot".'/mod/mgm/import.php');
    	}

    }else{
	    // reset the form selection
    	unset($_POST);
    }
}else{
 	unset($_POST);
}

	//do output	
	echo $OUTPUT->header();
	echo $OUTPUT->heading($strtitle);	
	echo $OUTPUT->box_start('boxaligncenter');
	$mform->display();
	echo $OUTPUT->box_end();
	echo $OUTPUT->footer();  	
  	#redirect("$CFG->wwwroot".'/mod/mgm/import.php?filename=repuestamdb_externo.mdb');