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


class plugin_ccdocente extends plugin_base{

	function init(){
		$this->form = false;
		$this->unique = true;
		$this->fullname = get_string('ccdocente','mgm');
		$this->reporttypes = array('sql');
	}

	function summary($data){
		return get_string('filterccdocente_summary','mgm');
	}

	function execute($finalelements, $data){
		$filter_ccdocente = optional_param('filter_ccdocente', "-1");
		if($filter_ccdocente=="-1")
			return $finalelements;

		if($this->report->type != 'sql'){
				return array($filter_ccdocente);
		}
		else{
			if(preg_match("/%%FILTER_CCDOCENTE:([^%]+)%%/i",$finalelements,
    $output)){
				$replace = ' AND '.$output[1]." = '".$filter_ccdocente."'";
				return str_replace('%%FILTER_CCDOCENTE:'.$output[1].'%%',$replace,$finalelements);
			}
		}
		return $finalelements;
	}

	function print_filter(&$mform){
		global $CFG, $CUERPOS_DOCENTES;

		$filter_ccdocente = optional_param('filter_ccdocente');

		$reportclassname = 'report_'.$this->report->type;
		$reportclass = new $reportclassname($this->report);

		if($this->report->type != 'sql'){
			$components = cr_unserialize($this->report->components);
			$conditions = $components['conditions'];

			$ccdocentelist = $reportclass->elements_by_conditions($conditions);
		}
		else{
			require_once($CFG->dirroot.'/mod/mgm/locallib.php');
			$ccdocentelist = $CUERPOS_DOCENTES;
		}

		$ccdocenteoptions = array();
		$ccdocenteoptions[-1] = get_string('choose');

		if(!empty($ccdocentelist)){

			foreach($ccdocentelist as $index=>$ccd){
				$ccdocenteoptions[$index] = format_string($ccd);
			}
		}
		$mform->addElement('select', 'filter_ccdocente', get_string('ccdocente','mgm'), $ccdocenteoptions);
		$mform->setType('filter_ccdocente', PARAM_TEXT);

	}

}

?>