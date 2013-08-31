<?php;

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

require_once '../../../config.php';
require_once $CFG->libdir.'/gradelib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/grader/lib.php';
require_once $CFG->dirroot.'/mod/mgm/locallib.php';




$courseid = required_param('id', PARAM_INT);
$userid   = optional_param('userid', $USER->id, PARAM_INT);
$groupid = optional_param('group', 0, PARAM_INT);
/// basic access checks
if (!$course = $DB->get_record('course', array('id'=> $courseid))) {
    print_error('nocourseid');
}
require_login($course);

$context = context_course::instance($course->id);
require_capability('gradereport/acta:view', $context);
//$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/grade/report/acta/index.php', array('id'=>$courseid)));

if (empty($userid)) {
    require_capability('moodle/grade:viewall', $context);

} else {
    if (!$DB->get_record('user', array('id'=> $userid, 'deleted'=> 0)) or isguestuser($userid)) {
        print_error('invaliduserid');
    }
}
$sql = 'select id from {groups} where id in ( select groupid from {groups_members} where userid = ? and courseid = ? )'; 
if(!$groups = $DB->get_records_sql($sql, array($userid, $courseid))) {
	$groups='';
}else{
	$groups='&filter_groups=('. implode(",", array_keys($groups)).')';
}

if (!$coursemgm = $DB->get_record('edicion_course', array('courseid'=> $courseid))) {
    print_error('nocoursemgm', 'mgm', $CFG->wwwroot.'/course/view.php?id='.$courseid);
}else{
	if ($coursemgm->fechafin>time()){
		print_error('coursenotended','mgm', $CFG->wwwroot.'/course/view.php?id='.$courseid);
	}
}

$access = false;
if (has_capability('moodle/grade:viewall', $context)) {
    //ok - can view all course grades
    $access = true;

} else if ($userid == $USER->id and has_capability('moodle/grade:view', $context) and $course->showgrades) {
    //ok - can view own grades
    $access = true;

} else if (has_capability('moodle/grade:viewall', get_context_instance(CONTEXT_USER, $userid)) and $course->showgrades) {
    // ok - can view grades of this user- parent most probably
    $access = true;
}

if (!$access) {
    // no access to grades!
	print_error('nopermissiontoviewgrades', 'error',  $CFG->wwwroot.'/course/view.php?id='.$courseid);    
}

/// return tracking object
$gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'acta', 'courseid'=>$courseid, 'userid'=>$userid));

/// last selected report session tracking
if (!isset($USER->grade_last_report)) {
    $USER->grade_last_report = array();
}
$USER->grade_last_report[$course->id] = 'grade';


//first make sure we have proper final grades - this must be done before constructing of the grade tree
grade_regrade_final_grades($courseid);

$reporttype = 'Acta';
$reportid=0;
if ($reporttype){
	$reports=mgm_get_reports();
	foreach($reports as $report){
		  if ( $report->name == $reporttype){
		  	$reportid=$report->value;
		  }
	}
}
if ($reportid ==0){
	print_error('noreport', 'mgm', $CFG->wwwroot.'/course/view.php?id='.$courseid);
}
//Establecer permisos para acceso a informe de actas:
global $SESSION;
$MGMINF = new stdClass();
$MGMINF->courseid=$courseid;
$MGMINF->userid=$userid;
$MGMINF->active=1;
$SESSION->MGMINF=$MGMINF;



if (has_capability('moodle/grade:viewall', $context)) { //Teachers will see all student reports
		if ($groups == ''){//admin
			if ($groupid){
				$groups="&filter_groups=($groupid)";
				$params='?id='.$reportid . '&filter_courses=' . $courseid . $groups. '&report_name=' . $reporttype . '&download=true&format=pdf&admin=true';
		  		redirect("$CFG->wwwroot".'/blocks/configurable_reports/viewreport.php'. $params);
			}
      		print_grade_page_head($courseid, 'report', 'acta');      		
      		groups_print_course_menu($course, $gpr->get_return_url('index.php?id='.$courseid, array('userid'=>0)));
      		
      		echo $OUTPUT->footer();
      		
		}else{//tutor del curso
		  $params='?id='.$reportid . '&filter_courses=' . $courseid . $groups. '&report_name=' . $reporttype . '&download=true&format=pdf';
		  redirect("$CFG->wwwroot".'/blocks/configurable_reports/viewreport.php'. $params);
		}

} else { //Students can not see act
    print_error('noaction', 'mgm', $CFG->wwwroot.'/course/view.php?id='.$courseid);
}
//echo $OUTPUT->footer();