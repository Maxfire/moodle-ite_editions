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

class plugin_tipocol extends plugin_base{

	function init(){
		$this->form = false;
		$this->unique = true;
		$this->fullname = get_string('tipocol','mgm');
		$this->reporttypes = array('sql');
	}

	function summary($data){
		return get_string('filtertipocol_summary','mgm');
	}

	function execute($finalelements, $data){

		$filter_tipocol = optional_param('filter_tipocol', 0, PARAM_INT);
		if(!$filter_tipocol)
			return $finalelements;

		if($this->report->type != 'sql'){
				return array($filter_tipocol);
		}
		else{
			if(preg_match("/%%FILTER_TIPOCOL:([^%]+)%%/i",$finalelements,
    $output)){
    	  $tipocol=(int)$filter_tipocol-1;
				$replace = ' AND '.$output[1]." = ".$tipocol;
				return str_replace('%%FILTER_TIPOCOL:'.$output[1].'%%',$replace,$finalelements);
			}
		}
		return $finalelements;
	}

	function print_filter(&$mform){
		global $CFG;

		$filter_tipocol = optional_param('filter_tipocol', 0, PARAM_INT);

		$reportclassname = 'report_'.$this->report->type;
		$reportclass = new $reportclassname($this->report);

		if($this->report->type != 'sql'){
			$components = cr_unserialize($this->report->components);
			$conditions = $components['conditions'];

			$tipocollist = $reportclass->elements_by_conditions($conditions);
		}
		else{
			$tipocollist = array(0=>'Publico', 1=>'Concertado', 2=>'Privado');
		}

		$tipocoloptions = array();
		$tipocoloptions[0] = get_string('choose');
		$tipocoloptions[1] = 'Publico';
		$tipocoloptions[2] = 'Concertado';
		$tipocoloptions[3] = 'Privado';
		$mform->addElement('select', 'filter_tipocol', get_string('tipocol', 'mgm'), $tipocoloptions);
		$mform->setType('filter_tipocol', PARAM_INT);

	}

}

?>