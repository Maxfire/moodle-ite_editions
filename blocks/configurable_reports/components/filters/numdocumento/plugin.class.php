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


class plugin_numdocumento extends plugin_base{

	function init(){
		$this->form = false;
		$this->unique = true;
		$this->fullname = get_string('numdocumento','mgm');
		$this->reporttypes = array('sql');
	}

	function summary($data){
		return get_string('filternumdocumento_summary','mgm');
	}

	function execute($finalelements, $data){

		$filter_numdocumento = optional_param('filter_numdocumento', '', PARAM_TEXT);
		if(!$filter_numdocumento)
			return $finalelements;

		if($this->report->type != 'sql'){
				return array($filter_numdocumento);
		}
		else{
			if(preg_match("/%%FILTER_NUMDOCUMENTO:([^%]+)%%/i",$finalelements,
    $output)){
				$replace = ' AND '.$output[1]." like '%".$filter_numdocumento."%'";
				return str_replace('%%FILTER_NUMDOCUMENTO:'.$output[1].'%%',$replace,$finalelements);
			}
		}
		return $finalelements;
	}

	function print_filter(&$mform){
		global $CFG;

		$filter_numdocumento = optional_param('filter_numdocumento', '', PARAM_TEXT);

		$reportclassname = 'report_'.$this->report->type;
		$reportclass = new $reportclassname($this->report);

		if($this->report->type != 'sql'){
			$components = cr_unserialize($this->report->components);
			$conditions = $components['conditions'];

			$numdocumentolist = $reportclass->elements_by_conditions($conditions);
		}


		$mform->addElement('text', 'filter_numdocumento', get_string('numdocumento', 'mgm'));
		$mform->setType('filter_numdocumento', PARAM_TEXT);

	}

}

?>