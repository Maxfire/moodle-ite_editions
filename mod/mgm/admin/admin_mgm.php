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
require_once($CFG->dirroot."/lib/filelib.php");
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot."/mod/mgm/locallib.php");
require_once($CFG->dirroot."/mod/mgm/mgm_forms.php");

require_login();
require_capability('mod/mgm:editedicion', get_context_instance(CONTEXT_SYSTEM));

$strtitle = get_string('admin');
$tempdir = $CFG->dataroot."/temp/";
$strtitle = get_string('admin_mgm', 'mgm');
$action = optional_param('action');

$filename= optional_param('filename');
$mform = new admin_form("$CFG->wwwroot".'/mod/mgm/admin/admin_mgm.php');
admin_externalpage_setup('admin_mgm', mgm_update_edition_button());
admin_externalpage_print_header();
print_heading($strtitle);
print_simple_box_start('center');

if ($data = $mform->get_data(false)) {
    if 	(!empty($data->cancel)){
    	unset($_POST);
    	redirect("$CFG->wwwroot".'/index.php');
    }
    else if (!empty($data->next)) {
    	if ($mform->save_files($tempdir)) {
    		$mform->_upload_manager->inputname='userfile';
    		$filename=$mform->get_new_filename();
    		$filename=$tempdir.$mform->get_new_filename();
    		$mform->save_files($tempdir);
    	}
    	if ($data->action=='loadchistory'){
   	 		$handle = fopen($filename, "r");
			  if( $handle ) {
			    //$fields='tipodocumento,numdocuemnto,nombre,apellido1,apellido2,idcurso,rol';
			    $fields=fgetcsv($handle, 0, ",");
			    $out='';
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
						$out=$out.$dev[1];
			    }
			    $out=$out.'<br/>Todos los registros procesados<br>';
			    fclose($handle);
			    @unlink($filename);
			  }else{
			  	$out=$out.'No se puede abrir el fichero:' . $file;
			  }

    	}else if ($data->action=='loadcolegios'){
    		$handle = fopen($filename, "r");
			  if ($handle){
			  	$fields=fgetcsv($handle, 0, ",");
			  	$out='';
					while (($reg = fgetcsv($handle, 0, ",")) !== FALSE) {
						if (count($reg)>=13){
			    		$nreg=new stdClass();
			    		$nreg->pais=str_replace("'","\'",$reg[1]);;
			    		$nreg->localidad=str_replace("'","\'",$reg[2]);;
			    		$nreg->dgenerica=str_replace("'","\'",$reg[3]);
			    		$nreg->despecifica=str_replace("'","\'",$reg[4]);;
			    		$nreg->codigo=$reg[5];
			    		$nreg->naturaleza=str_replace("'","\'",$reg[6]);;
			    		$nreg->direccion=str_replace("'","\'",$reg[7]);;
			    		$nreg->cp=$reg[9];
			    		$nreg->provincia=str_replace("'","\'",$reg[10]);;
			    		$nreg->tipo=$reg[12];
							$dev=mgm_set_centro($nreg);
							$out=$out.$dev[1];
						}else{
							$out=$out.'<br />Registro incorrecto';
						}
			    }
			    $out=$out.'<br/>Todos los registros procesados<br>';
			    fclose($handle);
			    @unlink($filename);
			  }else{
			  	$out=$out.'No se puede abrir el fichero:' . $file;
			  }
    	}
 		  else {
  			$out="Acción desconocida";
				//  redirect($CFG->dirroot.'/index.php');
			}
			print $out;
			print_simple_box_end();
			admin_externalpage_print_footer();
			unset($_POST);
			die();

    }else{
	    // reset the form selection
    	unset($_POST);
    }

}else{
 	unset($_POST);
}
$mform->display();
print_simple_box_end();
admin_externalpage_print_footer();
