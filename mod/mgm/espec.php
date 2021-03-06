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
 * @copyright  2010 - 2011 Oscar Campos <oscar.campos@open-phoenix.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot."/mod/mgm/locallib.php");
require_once($CFG->dirroot."/mod/mgm/mgm_forms.php");

require_login();

require_capability('mod/mgm:createedicion', get_context_instance(CONTEXT_SYSTEM));

$strediciones     = get_string('ediciones', 'mgm');
$strespecs        = get_string('especdata', 'mgm');

$navlinks = array();
$navlinks[] = array('name' => $strediciones, 'type' => 'misc');
$navlinks[] = array('name' => $strespecs, 'type' => 'misc');

$navigation = build_navigation($navlinks);

print_simple_box_start('center');

print_header("", get_string('especdata', 'mgm'), $navigation);

if(mgm_especs_exists()) {

    echo "<center><h1>Especialidades encontradas en la base de datos</h1></center>";
		$mform = new espec_form("$CFG->wwwroot".'/mod/mgm/espec.php');
		if ($data = $mform->get_data(false)) {
    	if 	(!empty($data->cancel)){
    			unset($_POST);
    			redirect("$CFG->wwwroot".'/index.php');
    	}
    	else if (!empty($data->update)) {
    		 if (mgm_update_especs()){
    		 		echo "<center><h1>Especialidades actualizadas!</h1></center>";
    		 		redirect("$CFG->wwwroot".'/index.php');
    		 }else{
    		 		echo "<center><h1>No se ha podido actualizar las especialidades!</h1></center>";
    		 		redirect("$CFG->wwwroot".'/index.php');
    		 }

    	}
		}
		$mform->display();
} else {
    mgm_create_especs();
    echo "<center><h1>Se han creado las especialidades en la base de datos. Recargue.</h1></center>";
}

print_simple_box_end();

