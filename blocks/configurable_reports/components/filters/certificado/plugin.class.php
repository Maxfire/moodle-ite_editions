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

/** Configurable Reports
  * A Moodle block for creating customizable reports
  * @package blocks
  * @author: Juan leyva <http://www.twitter.com/jleyvadelgado>
  * @date: 2009
  */

require_once($CFG->dirroot.'/blocks/configurable_reports/plugin.class.php');


class plugin_certificado extends plugin_base{

	function init(){
		$this->form = false;
		$this->unique = true;
		$this->fullname = get_string('certificado','mgm');
		$this->reporttypes = array('sql');
	}

	function summary($data){
		return get_string('filtercertificado_summary','mgm');
	}

	function execute($finalelements, $data){

		$filter_certificado = optional_param('filter_certificado');
		if(!$filter_certificado)
			return $finalelements;

		if($this->report->type != 'sql'){
				return array($filter_certificado);
		}
		else{
			if(preg_match("/%%FILTER_CERTIFICADO:([^%]+)%%/i",$finalelements,
    $output)){
    	  if ($filter_certificado=='2')
					$replace = ' AND '.$output[1]." = 2";
				else
					$replace = ' AND ('.$output[1]." != 2 or ".$output[1]." is null )";
				return str_replace('%%FILTER_CERTIFICADO:'.$output[1].'%%',$replace,$finalelements);
			}
		}
		return $finalelements;
	}

	function print_filter(&$mform){
		global $CFG;

		$filter_certificado = optional_param('filter_certificado');

		$reportclassname = 'report_'.$this->report->type;
		$reportclass = new $reportclassname($this->report);

		if($this->report->type != 'sql'){
			$components = cr_unserialize($this->report->components);
			$conditions = $components['conditions'];

			$certificadolist = $reportclass->elements_by_conditions($conditions);
		}
		else{
			$certificadolist = array('SI', 'NO');
		}

		$certificadooptions = array(0=>get_string('choose'), 1=>'No', 2=>'Si');

		$mform->addElement('select', 'filter_certificado', get_string('certificado','mgm'), $certificadooptions);
		$mform->setType('filter_certificado', PARAM_INT);

	}

}

?>