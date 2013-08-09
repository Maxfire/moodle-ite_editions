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
//global $CFG, $USER, $OUTPUT, $PAGE, $DB, $form, $SESSION, $_POST;
require_once('../../config.php');
require_once($CFG->dirroot."/mod/mgm/locallib.php");
require_once($CFG->dirroot."/mod/mgm/locallib.php");
$id = required_param('id', PARAM_INT);
$instance = $DB->get_record('enrol', array('id'=> $id));
if (!$instance || !$instance->courseid){
	error(get_string('wsnoinstance', 'enrol_mgm'));
}
$course = $DB->get_record('course', array('id'=> $instance->courseid));
//require_login($course);
require_login();
$context = context_course::instance($course->id, MUST_EXIST);
//require_capability('enrol/mgm:enrol', $context);
//require_capability('enrol/manual:manage', $context);
$PAGE->set_url('/enrol/mgm/enrol.php');
$PAGE->set_context($context);

if (!$edition = mgm_get_course_edition($course->id)) {
	error(get_string('noeditioncourse', 'mgm'));
}

if (!$edition->active) {
	error(get_string('noactiveedition', 'mgm'));
}
if ($edition->state != 'preinscripcion'){
	error(get_string('nopreinscriptionstate', 'mgm'));
}
if ($edition->state == 'matriculacion') {
	error(get_string('nomodifydata', 'mgm'));
}

$sql = "SELECT * FROM {edicion_inscripcion}
            	WHERE edicionid=:edition AND value=:value";
$arg = array('edition'=>$edition->id, 'value'=> $course->id );
if ($inscripcion = $DB->get_records_sql($sql, $arg) || time() > $edition->fin) {
	error(get_string('fueradeperiodo', 'mgm'));
}

if (!mgm_check_course_dependencies($edition, $course, $USER)) {
	error(get_string('nodependencias', 'mgm'));
}

$strloginto = get_string('loginto', '', $edition->name);
$strcourses = get_string('courses');

$context = get_context_instance(CONTEXT_SYSTEM);

$navlinks = array();
$navlinks[] = array('name' => $strcourses, 'link' => ".", 'type' => 'misc');
$navlinks[] = array('name' => $strloginto, 'link' => null, 'type' => 'misc');
$navigation = build_navigation($navlinks);

// if (has_capability('moodle/legacy:guest', $context, $USER->id, false)) {
// 	add_to_log($course->id, 'course', 'guest', 'view.php?id='.$course->id, getremoteaddr());
// 	return;
// }

print_header($strloginto, $course->fullname, $navigation);
echo '<br />';
echo $OUTPUT->heading($edition->name.' ('.$edition->description.')');
//print_simple_box_start('center', '80%');

echo $OUTPUT->box_start('center');
$choices = array();
if (!$options = mgm_get_edition_user_options($edition->id, $USER->id)) {
	$choices[0][0] = get_string('none');
	foreach (mgm_get_edition_courses($edition) as $course) {
		$choices[0][$course->id] = $course->fullname;
	}
} else {
	$plus = 0;
	if (mgm_count_courses($edition) > count($options)) {
		$plus = 1;
	}

	for ($i = 0; $i < count($options)+$plus; $i++) {
		foreach (mgm_get_edition_courses($edition) as $course) {
			$choices[$i][0] = get_string('none');
			$choices[$i][$course->id] = $course->fullname;
		}
	}
}
$user = $DB ->get_record('user', array('id'=> $USER->id));
$data2 = mgm_get_user_extend($USER->id);
if (array_key_exists('codcuerpodocente', $_POST)){
	$allespecs = mgm_get_all_especialidades($_POST['codcuerpodocente']);
}else{
	$allespecs = mgm_get_all_especialidades($data2->codcuerpodocente );
}
if (!empty($allespecs)) {
	$aespecs = $allespecs;
}

$data2 ->firstname=$user->firstname;
$data2 ->lastname=$user->lastname;
$data2 ->email=$user->email;
$data2 ->phone1=$user->phone1;
$data2 ->address=$user->address;
$data2->aespecs = $aespecs;
$data2->choices=$choices;
$data2->course=$course;
$data2->edition=$edition;
$data2->id=$course->id;
if ($userspec = mgm_get_user_especialidades($USER->id)){
	$data2->especialidades=array_keys($userspec);
}


// Print form
require_once($CFG->dirroot.'/enrol/mgm/enrol_form.php');
$eform = new enrol_mgm_form('enrol.php', $data2);
$eform->set_data($data2);
if ($options) {
	$data = new stdClass();
	foreach ($options as $k=>$v) {
		$prop = 'option['.$k.']';
		$data->$prop = $v;
	}
	$eform->set_data($data);
}


if ($data=$eform->get_data() ) {
	//Si data devuelve algun valor (not null) entonces los datos del formulario de entrada son correctos.
	//guardar datos de usuario
	if (isset($data->submitbutton)){
		$courses = array();
		foreach ($data->option as $k=>$option) {
			if (in_array($option, $courses) && $option > 0) {
				error(get_string('opcionesduplicadas', 'mgm'), '?id='.$course->id);
				echo $OUTPUT->box_end();
				echo $OUTPUT->footer();
				die();
			}
			$courses[$k] = $option;
		}			
		$user=new stdClass();
		$user->id=$USER->id;
		$user->firstname=$data->firstname;
		$user->lastname=$data->lastname;
		$user->email=$data->email;
		$user->phone1=$data->phone1;
		$user->address=$data->address;
		$DB->update_record('user', $user);

		//guardar datos de usuario  y centro mgm
		$mgmuser=new stdClass();
		$mgmuser->tipoid=$data->tipoid;
		$mgmuser->dni=$data->dni;
		$mgmuser->codcuerpodocente=$data->codcuerpodocente;
		$mgmuser->codniveleducativo=$data->codniveleducativo;
		$mgmuser->sexo=$data->sexo;
		//especialidades //
		if (isset($data->especialidades)){
			$mgmuser->especialidades=implode("\n", $data -> especialidades);
		}
		$mgmuser->cc=$data->cc;
		$mgmuser->codpostal=$data->codpostal;
		$mgmuser->codprovincia=$data->codprovincia;
		$mgmuser->codpais=$data->codpais;

		mgm_set_userdata2($USER->id, $mgmuser, true);
		$ch=mgm_check_cert_history($USER->id, $courses);
		if ($ch[0]){//Ningun curso ya certificado
			mgm_preinscribe_user_in_edition($edition->id, $USER->id, $courses);			
			echo $OUTPUT->confirm(get_string('preinscrito', 'mgm'),
				new moodle_url('/enrol/mgm/enrol.php', array('id'=>$course->id)), 
				new moodle_url($CFG->wwwroot.'/index.php'));
			die();
		}else{//alguno de los cursos esta certificado para el dni del usuario
			error($ch[1], '?id='.$course->id);
		}
	}
}

$eform->display();
if ($eform->is_validated()){
	print "Guardar datos formulario correcto";
}
if ($options) {
	echo "<br />";
	echo get_string('edicionwarning', 'mgm');
}
echo $OUTPUT->box_end();
echo $OUTPUT->footer();