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
 * Edit editions settings
 *
 * @package    mod
 * @subpackage mgm
 * @copyright  2012 Jesús Jaén <jesus.jaen@open-phoenix.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->dirroot."/mod/mgm/locallib.php");
require_once($CFG->libdir.'/adminlib.php');

require_login();
require_capability('mod/mgm:editedicion', get_context_instance(CONTEXT_SYSTEM));
$strtitle = get_string('adminmgm', 'mgm');
$action = optional_param('action');    // Edition id

//admin_externalpage_setup('adminmgm', mgm_update_edition_button());
//admin_externalpage_print_header();
//print_heading($strtitle);

$filename= optional_param('filename');

if ($action == 'loadchistory') {
	print "Cargar historico de certificaciones";
	if (isset($filename)){
		$file="../data/".$filename;
	}else{
		$file="../data/cert_historico_1.csv";
	}
  $handle = fopen($file, "r");

  if( $handle ) {
    //$fields='tipodocumento,numdocuemnto,nombre,apellido1,apellido2,idcurso,rol';
    $fields=fgetcsv($handle, 0, ",");
    while (($reg = fgetcsv($handle, 0, ",")) !== FALSE) {
    	$cert_reg=new stdClass();
    	$cert_reg->userid=0;
    	$cert_reg->edicionid=0;
    	$cert_reg->courseid='0';
    	$cert_reg->roleid=0;
    	$cert_reg->numregistro='0';
    	$cert_reg->confirm=1;
    	$cert_reg->tipodocumento='N';
    	$cert_reg->numdocumento='0';

			if (isset($reg[0])){
				$cert_reg->tipodocumento=$reg[0];
			}
		  if (isset($reg[1])){
			  $cert_reg->userid=mgm_get_userid_from_dni($reg[1]);
			  $cert_reg->numdocumento=$reg[1];
		  }
			if (isset($reg[5])){
				$cert_reg->courseid=$reg[5];
			}
			$dev=mgm_set_cert_history($cert_reg);
			print $dev[1];
    }
    fclose($handle);
  }else{
  	print 'No se puede abrir el fichero:' . $file;
  }

} else {
  print "Niguna accion de Administracion indicada";
//  redirect($CFG->dirroot.'/index.php');
}


//admin_externalpage_print_footer();