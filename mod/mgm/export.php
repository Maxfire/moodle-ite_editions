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
 * @copyright  2011 Pedro Peña Pérez <pedro.pena@open-phoenix.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot."/lib/filelib.php");
require_once($CFG->dirroot."/mod/mgm/locallib.php");
require_once($CFG->dirroot."/mod/mgm/mgm_forms.php");

require_login();
require_capability('mod/mgm:aprobe', get_context_instance(CONTEXT_SYSTEM));

$strtitle = get_string('exportdata','mgm');

require_once($CFG->libdir.'/adminlib.php');

$tempdir = $CFG->dataroot."/temp/";
$filename = optional_param('filename');
$generated = optional_param('generated');

$mform = new edicion_form("$CFG->wwwroot".'/mod/mgm/export.php');

if ($filename && file_exists($tempdir.$filename)) {
  $lifetime = 0;
  send_file($tempdir.$filename, "export.zip", $lifetime, 0, false, true);
}
else if ($generated) {
	$ed = optional_param('edicion', 0);
	if (! $edicion=get_record('edicion', 'id', $ed)){
		error('Edicion no valida',"$CFG->wwwroot".'/mod/mgm/import.php');
	}else{
		admin_externalpage_setup('edicionesmgmt', mgm_update_edition_button());
  	admin_externalpage_print_header();
  	print_heading($strtitle);
  	print_simple_box_start('center');
  	$emision = new EmisionDatos($edicion);
  	$validacion = $emision->Validar();
  	$afichero = $emision->aFichero( $tempdir );

  	foreach (array_merge($validacion->incidencias, $afichero->incidencias) as $incidencia)
    	echo $incidencia;

	  echo get_string('file_export_link', 'mgm', $afichero);
  	print_simple_box_end();
  	admin_externalpage_print_footer();
	}
}
else if ($data = $mform->get_data(false)){
		if 	(!empty($data->cancel)){
    	unset($_POST);
    	redirect("$CFG->wwwroot".'/index.php');
    }
    else if (!empty($data->next)) {
    	$edicion=$data->edition;
    	if (! isset($edicion) or $edicion == 0){
    		error('Edicion no valida',"$CFG->wwwroot".'/mod/mgm/export.php');
    	}
    }
	//Do output
  admin_externalpage_setup('edicionesmgmt', mgm_update_edition_button());
  admin_externalpage_print_header();
  print_heading($strtitle);
  print_simple_box_start('center');
  print get_string('exportpleasewait', 'mgm');
  print_simple_box_end();
	admin_externalpage_print_footer();
	redirect("$CFG->wwwroot".'/mod/mgm/export.php?generated=1&edicion='.$edicion);
}
else {
  admin_externalpage_setup('edicionesmgmt', mgm_update_edition_button());
  admin_externalpage_print_header();
  print_heading($strtitle);
  print_simple_box_start('center');
  $mform->display();
  print_simple_box_end();
	admin_externalpage_print_footer();

}


?>