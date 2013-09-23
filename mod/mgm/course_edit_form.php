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
 * Edition course criteria form
 *
 * @package    mod
 * @subpackage mgm
 * @copyright  2010 Oscar Campos <oscar.campos@open-phoenix.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

/**
 * mgm_course_edit_form class
 * @author Oscar Campos
 */
class mgm_course_edit_form extends moodleform {

    // Form definition
    function definition() {
        global $CFG;
        global $NIVELES_EDUCATIVOS;
        global $CODIGOS_AGRUPACION;
        global $MODALIDADES;
        global $PROVINCIAS;
        global $PAISES;
        global $MATERIAS;        
        $mform =& $this->_form;
        $criteria = $this->_customdata;

        if (isset($criteria->id)) {
            // Editing an existing edition criteria
            $strsubmit = get_string('savechanges');
        } else {
            // Making a new edition
            $strsubmit = get_string('createcriteria', 'mgm');
        }

        $mform->addElement('header', 'course_extend', get_string('course_extend', 'mgm'));

        $mform->addElement('select', 'codagrupacion', get_string('codagrupacion', 'mgm'), $CODIGOS_AGRUPACION);
        $mform->addRule('codagrupacion', get_string('required'), 'required', null);
        $mform->addRule('codagrupacion', get_string('numeric', 'mgm'), 'numeric');
        $mform->setDefault('codagrupacion', 25);

        $mform->addElement('select', 'codmodalidad', get_string('codmodalidad', 'mgm'), $MODALIDADES);
        $mform->addRule('codmodalidad', get_string('required'), 'required', null);

        $mform->addElement('select', 'codprovincia', get_string('codprovincia', 'mgm'), $PROVINCIAS);
        $mform->addRule('codprovincia', get_string('required'), 'required', null);
        $mform->setDefault('codprovincia', 280);

        $mform->addElement('select', 'codpais', get_string('codpais', 'mgm'), $PAISES);
        $mform->addRule('codpais', get_string('required'), 'required', null);

        $mform->addElement('select', 'codmateria', get_string('codmateria', 'mgm'), $MATERIAS);
        $mform->addRule('codmateria', get_string('required'), 'required', null);
        $mform->setDefault('codmateria', 3801);

        $mform->addElement('select', 'codniveleducativo', get_string('codniveleducativo', 'mgm'), $NIVELES_EDUCATIVOS );
        $mform->addRule('codniveleducativo', get_string('required'), 'required', null);
        $mform->setDefault('codniveleducativo', 25);

        $mform->addElement('text', 'numhoras', get_string('numhoras', 'mgm'));
        $mform->addRule('numhoras', get_string('required'), 'required', null);
        $mform->addRule('numhoras', get_string('numeric', 'mgm'), 'numeric');

        $mform->addElement('text', 'numcreditos', get_string('numcreditos', 'mgm'));
        $mform->addRule('numcreditos', get_string('required'), 'required', null);
        $mform->addRule('numcreditos', get_string('numeric', 'mgm'), 'numeric');

        $mform->addElement('date_selector', 'fechainicio', get_string('fechainicio', 'mgm'));
        $mform->addRule('fechainicio', get_string('required'), 'required', null);

        $mform->addElement('date_selector', 'fechafin', get_string('fechafin', 'mgm'));
        $mform->addRule('fechafin', get_string('required'), 'required', null);

        $mform->addElement('text', 'localidad', get_string('localidad', 'mgm'));
        $mform->addRule('localidad', get_string('required'), 'required', null);
        $mform->setDefault('localidad', "En Red" );

        $mform->addElement('date_selector', 'fechainimodalidad', get_string('fechainimodalidad', 'mgm'));
        $mform->addRule('fechainimodalidad', get_string('required'), 'required', null);
        $mform->setDefault('fechainimodalidad', mktime(0,0,0,10,29,2011));

        $mform->addElement('text', 'tutorpayment', get_string('tutor_payment', 'mgm'));
        $mform->addRule('tutorpayment', get_string('required'), 'required', null);
        $mform->setDefault('tutorpayment', 65);

        $mform->addElement('text', 'duration', get_string('duration', 'mgm'));
        $mform->addRule('duration', get_string('required'), 'required', null);
        $mform->setDefault('duration', 2);

        $mform->addElement('text', 'prevlab', get_string('prevlab', 'mgm'));
        $mform->addRule('prevlab', get_string('required'), 'required', null);
        $mform->setDefault('prevlab', 180);

        $mform->addElement('text', 'tramo[0]', get_string('tramo', 'mgm').' 1-5');
        $mform->addRule('tramo[0]', get_string('required'), 'required', null);
        $mform->setDefault('tramo[0]', (isset($this->_customdata->tramo[0])) ? $this->_customdata->tramo[0] : 350);

        $mform->addElement('text', 'tramo[1]', get_string('tramo', 'mgm').' 6-10');
        $mform->addRule('tramo[1]', get_string('required'), 'required', null);
        $mform->setDefault('tramo[1]', (isset($this->_customdata->tramo[1])) ? $this->_customdata->tramo[1] : 450);

        $mform->addElement('text', 'tramo[2]', get_string('tramo', 'mgm').' 11-15');
        $mform->addRule('tramo[2]', get_string('required'), 'required', null);
        $mform->setDefault('tramo[2]', (isset($this->_customdata->tramo[2])) ? $this->_customdata->tramo[2] : 500);

        $mform->addElement('text', 'tramo[3]', get_string('tramo', 'mgm').' 16-20');
        $mform->addRule('tramo[3]', get_string('required'), 'required', null);
        $mform->setDefault('tramo[3]', (isset($this->_customdata->tramo[3])) ? $this->_customdata->tramo[3] : 550);

        $mform->addElement('text', 'tramo[4]', get_string('tramo', 'mgm').' 20+');
        $mform->addRule('tramo[4]', get_string('required'), 'required', null);
        $mform->setDefault('tramo[4]', (isset($this->_customdata->tramo[4])) ? $this->_customdata->tramo[4] : 600);

        $tasks = array(0 => 'Automático');
        $atasks = & $this->_customdata->tasks;
        $tasks += $atasks;


        $mform->addElement('select', 'ecuadortask', get_string('ecuadortask', 'mgm'), $tasks);
				foreach($tasks as $k=>$v){
        	if(preg_match('/^ec-/', $v)){
        		$mform->setDefault('ecuadortask', $k);
        		break;
        	}
        }

        $mform->addElement('header', 'criteria', get_string('criterios', 'mgm'));

        $mform->addElement('text', 'plazas', get_string('plazas', 'mgm'));
        $mform->addRule('plazas', get_string('required'), 'required', null);
        $mform->addRule('plazas', get_string('numeric', 'mgm'), 'numeric');

        $mform->addElement('text', 'numgroups', get_string('numgroups', 'mgm'));
        $mform->addRule('numgroups', get_string('required'), 'required', null);
        $mform->addRule('numgroups', get_string('numeric', 'mgm'), 'numeric');
        
        $modes = array('1'=> get_string('especialidades', 'mgm'),
        			   '2'=> get_string('cc', 'mgm'),
        			   '3'=> get_string('ccespecialidades', 'mgm')
        				);
        $mform->addElement('select', 'modegroup', get_string('modegroup', 'mgm'), $modes);
        $mform->addRule('modegroup', get_string('required'), 'required', null);
        $mform->setDefault('modegroup', (isset($this->_customdata->modegroup)) ? $this->_customdata->modegroup : '1');
        

        $choices = array(
            'ninguna'		 => get_string('sinprioridad', 'mgm'),
            'centros'        => get_string('prioridadcentro', 'mgm'),
            'especialidades' => get_string('prioridadespec', 'mgm')
        );

        $mform->addElement('select', 'opcion1', get_string('opcionuno', 'mgm'), $choices, 'onChange="mgm_opciones(true);"');
        $mform->addElement('select', 'opcion2', get_string('opciondos', 'mgm'), $choices, 'onChange="mgm_opciones(false);"');

        $acomunidades =& $this->_customdata->acomunidades;
        $scomunidades =& $this->_customdata->scomunidades;
		$achoicescom = array();
		$schoicescom = array();
		
		if (is_array($acomunidades)){
			$achoicescom =  $acomunidades;
		}
		if (is_array($scomunidades)){
			$schoicescom =  $scomunidades;
		}
		$comunidades = array();
		
		//$mform->addElement('header', 'comunidades', get_string('comunidades', 'mgm'));
		$objs = array();
		$objs[0] =& $mform->createElement('select', 'acomunidades', get_string('available', 'mgm'), $achoicescom, 'size="15"');
		$objs[0]->setMultiple(true);
		$objs[1] =& $mform->createElement('select', 'scomunidades', get_string('selected', 'mgm'), $schoicescom, 'size="15"');
		$objs[1]->setMultiple(true);		
		$grp =& $mform->addElement('group', 'comunidadesgrp', get_string('comunidades', 'mgm'), $objs, ' ', false);
		$mform->addHelpButton('comunidadesgrp', 'comunidades', 'mgm');
		
		$objs = array();
		$objs[] =& $mform->createElement('submit', 'addsel', get_string('comunidad_exclude', 'mgm'));
		$objs[] =& $mform->createElement('submit', 'removesel', get_string('comunidad_include', 'mgm'));
		$grp =& $mform->addElement('group', 'buttonscomgrp', get_string('comunidades_exclude', 'mgm'), $objs, array(' ', '<br />'), false);
		//$mform->addHelpButton('buttonscomgrp', 'selectedlist', 'bulkusers');
		
		
        //$mform->addElement('select', 'comunidad', get_string('comunidad_exclude', 'mgm'), $comunidades);        
        //$mform->addHelpButton('comunidades', 'comunidades', 'mgm');

        $achoices = $schoices = array();
        $aespecs = & $this->_customdata->aespecs;
        $sespecs = & $this->_customdata->sespecs;

        if (is_array($aespecs)) {
            $achoices += $aespecs;
        }

        if (is_array($sespecs)) {
            $schoices += $sespecs;
        }

        $especs = array();
        $especs[0] = & $mform->createElement('select', 'aespecs', get_string('cavailable', 'mgm'), $achoices,
        	          						 'size="15" class="mod-mgm courses-select"
        									  onfocus="getElementById(\'id_addsel\').disabled=false;
        									  getElementById(\'id_removesel\').disabled=true;
        									  getElementById(\'id_scourses\').selectedIndex=-1;"');
        $especs[0]->setMultiple(true);
        $especs[1] = & $mform->createElement('select', 'sespecs', get_string('cselected', 'mgm'), $schoices,
        									 'size="15" class="mod-mgm courses-select"
        									  onfocus="getElementById(\'id_addsel\').disabled=true;
        									  getElementById(\'id_removesel\').disabled=false;
        									  getElementById(\'id_acourses\').selectedIndex=-1;"');
        $especs[1]->setMultiple(true);

        $grp =& $mform->addElement('group', 'especsgrp', get_string('especialidades', 'mgm'), $especs, ' ', false);

        $objs = array();
        $objs[] =& $mform->createElement('submit', 'addsel', get_string('addespec', 'mgm'));
        $objs[] =& $mform->createElement('submit', 'removesel', get_string('removeespec', 'mgm'));
        $grp =& $mform->addElement('group', 'buttonsgrp', get_string('selectedespeclist', 'mgm'), $objs, array(' ', '<br />'), false);


        if (isset($criteria->edicionid)) {
            $mform->addElement('hidden', 'edicionid', 0);
            $mform->setType('edicionid', PARAM_INT);
            $mform->setDefault('edicionid', $criteria->edicionid);
        }

        if (isset($criteria->courseid)) {
            $mform->addElement('hidden', 'courseid', 0);
            $mform->setType('courseid', PARAM_INT);
            $mform->setDefault('courseid', $criteria->courseid);
        }
        
        $dpends = array();
        $dchoices = array();
        foreach ($this->_customdata->dependencias as $k => $v) {
            $dchoices[$k] = ($v->idnumber != "") ? $v->idnumber : "NO CODE"." (".$v->fullname.")";
        }
        $dpends[] =& $mform->createElement('checkbox', 'depends', '',
                           '', array('id'=>"dcheck",'onclick'=>"if(this.checked) {
                           			getElementById('id_dlist').disabled=false;
    					   	} else {
    					   		getElementById('id_dlist').disabled=true;
    					   	}") );
        if  ($this->_customdata->depends){
        	$dlistoption=array('enabled'=> "1");
        } else{
        	$dlistoption=array('disabled'=> "1");
        }
        $dpends[] =& $mform->createElement('select', 'dlist', '', $dchoices, $dlistoption);        
        $grp =& $mform->addElement('group', 'dpendsgroup', get_string('cdepend', 'mgm'), $dpends, ' ', false);
        $this->add_action_buttons(true);
    }
}
