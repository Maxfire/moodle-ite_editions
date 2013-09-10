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
require_once($CFG->libdir.'/formslib.php');


class enrol_mgm_form extends moodleform {

    // Form definition
    function definition() {
    	global $CFG, $USER, $NIVELES_EDUCATIVOS, $CUERPOS_DOCENTES, $PAISES, $PROVINCIAS;
        $mform =& $this->_form;
        $course = $this->_customdata->course;
        $edition = $this->_customdata->edition;
		$strrequired=get_string('required');
        ###Informacion de usuario a rellenar/Validar
        $mform->addElement('header', 'usuario', get_string('user'));

        $tiposid = array(
          'N' => 'N NIF',
          'P' => 'P PASAPORTE',
          'T'  => 'T TARJETA DE RESIDENCIA'
        );
        $mform->addElement('select', 'tipoid', get_string('tipoid','mgm'), $tiposid);
        $mform->addRule('tipoid', $strrequired, 'required', null);

        $mform->addElement('text', 'dni', get_string('dni', 'mgm'), array('size' => '9'));
        $mform->addRule('dni', $strrequired, 'required', null);

        $mform->addElement('text', 'firstname', get_string('firstname'), 'maxlength="100" size="30"');
        $mform->addRule('firstname', $strrequired, 'required', null, 'client');
    	$mform->setType('firstname', PARAM_NOTAGS);

    	$mform->addElement('static', 'apellidoswarn', get_string('important', 'mgm'),get_string('surname2', 'mgm'));
        $mform->addElement('text', 'lastname',  'Apellidos',  'maxlength="100" size="30"');
        $mform->addRule('lastname', $strrequired, 'required', null, 'client');
    	$mform->setType('lastname', PARAM_NOTAGS);

		$mform->addElement('text', 'email', get_string('email'), 'maxlength="100" size="30"');
        $mform->addRule('email', $strrequired, 'required', null, 'client');

        $mform->addElement('text', 'phone1', get_string('phone'), 'maxlength="20" size="25"');
        $mform->addRule('phone1', $strrequired, 'required', null, 'client');
    	$mform->setType('phone1', PARAM_CLEAN);

    	$mform->addElement('text', 'address', get_string('address'), 'maxlength="70" size="25"');
		$mform->addRule('address', $strrequired, 'required', null, 'client');
    	$mform->setType('address', PARAM_MULTILANG);

    	$objs = array();
        $objs[] =& $mform->createElement('select', 'codcuerpodocente', get_string('codcuerpodocente','mgm'), $CUERPOS_DOCENTES);
        $objs[] =& $mform->createElement('submit', 'selcuerpodocente', 'Filtrar especialidades');
        $mform->addGroup($objs, 'cuerpodocentegroup',get_string('codcuerpodocente','mgm') , array(' '), false);
        $mform->addRule('cuerpodocentegroup', get_string('required'), 'required', null);

        $mform->addElement('select', 'codniveleducativo', get_string('codniveleducativo','mgm'), $NIVELES_EDUCATIVOS);
        $mform->addRule('codniveleducativo', $strrequired, 'required', null);

        $sexos = array(
          'H' => 'H Hombre',
          'M' => 'M Mujer'
        );
        $mform->addElement('select', 'sexo', get_string('sexo','mgm'), $sexos);
        $mform->addRule('sexo', $strrequired, 'required', null);

        $achoices = array();
        $aespecs = & $this->_customdata->aespecs;        

        if (is_array($aespecs)) {
            $achoices += $aespecs;
        }
//         if (is_array($sespecs)) {
//             $schoices += $sespecs;
//         }
		$mform->addElement('static', 'especialidadeswarn', get_string('note', 'mgm'). ':', get_string('multiselectespec', 'mgm'));
        $especs[0] = & $mform->addElement('select', 'especialidades', get_string('especialidades', 'mgm'), $achoices, array('size'=>"10"));
        $especs[0]->setMultiple(true);


        ###Informacion de Centro a validar /rellenar
        $mform->addElement('header', 'centro', get_string('centro', 'mgm'));

        $mform->addElement('text', 'cc', get_string('cc', 'mgm'), array('size'=>'30'));
        //$mform->setHelpButton('cc', array('cc', get_string('cc', 'mgm'), 'mgm'));
        $mform->addHelpButton('cc', 'cc', 'enrol_mgm');
        $mform->addRule('cc', $strrequired, 'required', null);

        $mform->addElement('text', 'codpostal', get_string('codpostal','mgm'), array('size' => '5'));
        $mform->addRule('codpostal', $strrequired, 'required', null);

        $mform->addElement('select', 'codprovincia', get_string('codprovincia','mgm'), $PROVINCIAS);
        $mform->addRule('codprovincia', $strrequired, 'required', null);

        $mform->addElement('select', 'codpais', get_string('codpais','mgm'),$PAISES);
        $mform->addRule('codpais', $strrequired, 'required', null);
        $mform->addElement('static', 'notafin', get_string('note', 'mgm'). ':', get_string('ccnote', 'mgm'));


        $renderer = & $mform->defaultRenderer();
        $tpl = '<label class="qflabel" style="vertical-align:top;">{label}</label> {element}';
        $renderer->setGroupElementTemplate($tpl, 'coursesgrp');


        ####Informacion de matriculacion
         $mform->addElement('header', 'matriculacion', get_string('edicioncursos', 'mgm'));

         $mform->addElement('hidden', 'courseid', $course->id);
         $mform->setType('courseid', PARAM_INT);
         $mform->addElement('hidden', 'editionid', $edition->id);
         $mform->setType('editionid', PARAM_INT);

//          $mform->addElement('hidden', 'edition', $editionid);
//          $mform->setType('edition', PARAM_INT);

         $mform->addElement('hidden', 'options', count($this->_customdata->choices));
         $mform->setType('options', PARAM_INT);
		
        foreach ($this->_customdata->choices as $k=>$v) {
        	
            $tmpnum = $k+1;
            if ($tmpnum <= $edition->numberc){
            	$mform->addElement('select', 'option['.$k.']', get_string('opcion', 'mgm').' '.$tmpnum, $v);
            }
            
        }

        $this->add_action_buttons(false, get_string('savechanges'));

    }
    
	function validation($data, $files) {
		global $CFG, $USER, $DB;
		require_once ($CFG->dirroot . "/mod/mgm/locallib.php");
		$errors = parent::validation ( $data, $files );
		$data = ( object ) $data;
		// $user = get_record('user', 'id', $usernew->id);
		// validate cc
		$ret = MGM_DATA_NO_ERROR;
		$newdata = $data;
		$newdata->cc = mgm_check_user_cc ( $data->cc, $ret, $data->tipoid );
		if ($ret == MGM_DATA_CC_ERROR) {
			$errors ['cc'] = get_string ( 'cc_no_error', 'mgm' );
			return $errors;
		}
		// validate dni/passporte/tarjeta de residencia
		//if ($data->tipoid == 'N') {
			$newdata->dni = mgm_check_user_dni ( $USER->id, $data->dni, $ret );
			if ($ret == MGM_DATA_DNI_ERROR) {
				$errors ['dni'] = get_string ( 'dnimulti', 'mgm' );
				return $errors;
			} else if ($ret == MGM_DATA_DNI_INVALID) {
				$errors ['dni'] = get_string ( 'dninotvalid', 'mgm' );
				return $errors;
			}
		//}
		//No change dni validation
		if ($userdb = mgm_get_user_extend ( $USER->id )) {
			if (isset ( $userdb->dni ) && $userdb->dni != '' && $userdb->dni != $data->dni) {
				$errors ['dni'] = get_string ( 'nochangedni', 'mgm' );
				return $errors;
			}
		}
		// validate depends:
		$courses = array ();
		$edition = $this->_customdata->edition;
		if ($edition) {
			foreach ( $data->option as $k => $option ) {
				if ($course = $DB->get_record ( 'course', array ('id' => $option) )) {
					if (! mgm_check_course_dependencies ( $edition, $course, $USER, $data->dni )) {
						$errors ['option[' . $k . ']'] = get_string ( 'nodependencias', 'mgm' );
					}
					$courses [$k] = $option;
				}
			}
			// validate cert history
			$ch = mgm_check_cert_history ( $USER->id, $courses, $data->dni );
			if (! $ch [0]) { // alguno de los cursos esta certificado para el dni del usuario
				foreach ( $data->option as $k => $option ) {
					if ($ch [2] == $option) {
						$errors ['option[' . $k . ']'] = $ch [1];
					}
				}
			}
		}
		return $errors;
	}

    function definition_after_data() {
    	        // if language does not exist, use site default lang
//        if ($langsel = $mform->getElementValue('lang')) {
//            $lang = reset($langsel);
//            // missing _utf8 in language, add it before further processing. MDL-11829 MDL-16845
//            if (strpos($lang, '_utf8') === false) {
//                $lang = $lang . '_utf8';
//                $lang_el =& $mform->getElement('lang');
//                $lang_el->setValue($lang);
//            }
//            // check lang exists
//            if (!file_exists($CFG->dataroot.'/lang/'.$lang) and
//              !file_exists($CFG->dirroot .'/lang/'.$lang)) {
//                $lang_el =& $mform->getElement('lang');
//                $lang_el->setValue($CFG->lang);
//            }
//        }



    }
}



class enrol_mgm_ro_form extends moodleform {

    // Form definition
    function definition() {
        $mform =& $this->_form;
        $course = $this->_customdata['course'];
        $edition = $this->_customdata['edition'];

        $mform->addElement('header', 'general', get_string('edicioncursos', 'mgm').' '.$this->_customdata['date']);

        $mform->addElement('hidden', 'id', $course->id);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'edition', $edition->id);
        $mform->setType('edition', PARAM_INT);

        $mform->addElement('hidden', 'options', count($this->_customdata['choices']));
        $mform->setType('options', PARAM_INT);

        foreach ($this->_customdata['choices'] as $k=>$v) {
            $tmpnum = $k+1;
            $mform->addElement('select', 'option['.$k.']', get_string('opcion', 'mgm').' '.$tmpnum, $v, array('disabled'=>''));
        }
    }
}








