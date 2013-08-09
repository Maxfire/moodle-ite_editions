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


class plugin_dgenerica extends plugin_base{

	function init(){
		$this->form = false;
		$this->unique = true;
		$this->fullname = get_string('dgenerica','mgm');
		$this->reporttypes = array('dgenerica','sql');
	}

	function summary($data){
		return get_string('filterdgenerica_summary','mgm');
	}

	function execute($finalelements, $data){

		$filter_dgenerica = optional_param('filter_dgenerica', '', PARAM_TEXT);
		if(!$filter_dgenerica)
			return $finalelements;

		if($this->report->type != 'sql'){
				return array($filter_dgenerica);
		}
		else{
			if(preg_match("/%%FILTER_DGENERICA:([^%]+)%%/i",$finalelements,
    $output)){
				$replace = ' AND '.$output[1]." = '".$filter_dgenerica."'";
				return str_replace('%%FILTER_DGENERICA:'.$output[1].'%%',$replace,$finalelements);
			}
		}
		return $finalelements;
	}

	function print_filter(&$mform){
		global $CFG, $DB;

		$filter_dgenerica = optional_param('filter_dgenerica', '',PARAM_TEXT);

		$reportclassname = 'report_'.$this->report->type;
		$reportclass = new $reportclassname($this->report);

		if($this->report->type != 'sql'){
			$components = cr_unserialize($this->report->components);
			$conditions = $components['conditions'];

			$dgenericalist = $reportclass->elements_by_conditions($conditions);
		}
		else{
			$sql="select distinct dgenerica from {edicion_centro} order by dgenerica";
			$dgenericalist = array_keys($DB->get_records_sql($sql));
		}

		$dgenericaoptions = array();
		$dgenericaoptions[0] = get_string('choose');

		if(!empty($dgenericalist)){

			foreach($dgenericalist as $dg){
				if ($dg != '')
					$dgenericaoptions[$dg] = format_string($dg);
			}
		}
		$mform->addElement('select', 'filter_dgenerica', get_string('dgenerica', 'mgm'), $dgenericaoptions);
		$mform->setType('filter_dgenerica', PARAM_TEXT);

	}

}

?>