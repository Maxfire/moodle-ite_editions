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
 * Paypal enrolment plugin.
 *
 * This plugin allows you to set up paid courses.
 *
 * @package    enrol
 * @subpackage mgm
 * @copyright  2013 Jesus Jaen { jesus.jaen@open-phoeix.com }
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG -> dirroot . '/mod/mgm/locallib.php');

// function mgm_enrol_get_course_name_edition($edition, $courseid){
// 	global $SESSION;
// 	if (!isset($SESSION))
// }
function mgm_enrol_get_user_preinscription_data($userline, $edition, $criteria, $courseenrolid=false) {
	global $CFG, $DB, $SESSION;	
	$site = get_site();
	$user = $userline -> user;
	$especs = explode("\n",ltrim($user -> especialidades,"\n"));
	$userespecs = '<select name="especialidades" readonly="">';
	foreach($especs as $espec) {
		$userespecs .= '<option name="' . $espec . '">' . mgm_translate_especialidad($espec) . '</option>';
	}
	$userespecs .= '</select>';
	$courses = '<select name="courses" readonly="">';
	$values = explode(',', $userline -> value);

	foreach($values as $courseid) {//Comprobar rendimiento y optimizar
 		$ncourse = $SESSION->mgm_courses_edition[$courseid];
 		$courses .= '<option name="' . $courseid . '">' . $ncourse -> fullname . '</option>';
	}
	$courses .= '</select>';
	$check = '<input type="checkbox" name="users[' . $userline -> userid . ']" />';
	$colors='';
	$state='<input type="hidden" name="state[' . $userline -> userid . ']" value="1" />';
	
	//Comprobar centro privado (rojo)
	if($user->cctipo == MGM_PRIVATE_CENTER || $user->cctipo == -1) {
		$colors = '<span style="color: red;">(*) </span>';
		$check = '<input type="checkbox" name="users[' . $userline -> userid . ']" checked="false" />';
		$state='<input type="hidden" name="state[' . $userline -> userid . ']" value="2" />';
	}
	//Comprobar si el usuario ha certificado el curso (Amarillo) No necesario, se comprueba en el formulario de inscripcion

	//Comprobar comunidad autonoma (Naranja)
	if(property_exists($criteria, 'excomunidades') ) {
		$provincia = str_split($user->cc, 2);
		if(mgm_prov_in_cas($provincia, $criteria->excomunidades)) {
			$colors = $colors. '<span style="color: orange;">(*)</span> ';
			$check = '<input type="checkbox" name="users[' . $userline -> userid . ']" checked="false" />';
			$state='<input type="hidden" name="state[' . $userline -> userid . ']" value="4" />';
		}
	}
	$name=$colors.'<a href="../../user/view.php?id=' . $userline -> userid . '&amp;course=' . $site -> id . '">' . $user -> firstname . '</a>';
	$tmpdata = array($check,
			$name.$state,
			$user->lastname,
			date("d/m/Y H:i\"s", $userline -> timemodified),
			($user -> cc) ? $user -> cc : '',
			($user -> cc) ? mgm_get_ccaa($user -> cc) : '' ,
			$userespecs,
			$courses,
			$userline->prioridad);
	return $tmpdata;
}

function mgm_enrol_get_courses_options($editionid){
	global $DB;
	$course_options =array();
	
	$types = sprintf('%d, %d, %d', MGM_CRITERIA_OPCION1, MGM_CRITERIA_OPCION2, MGM_CRITERIA_ESPECIALIDAD);
	$sql = "SELECT id, course, type, value FROM {edicion_criterios} 
			WHERE edicion = ? and type IN ($types) 
			ORDER BY course, type";
	if ( $records = $DB->get_records_sql($sql, array($editionid)) ){
		foreach ($records as $record){
			if (! isset($course_options[$record->course])){
				$course = new stdClass();
				$course->prioridad1_index = 0;
				$course->prioridad2_index = 0;
				$course_options[$record->course] = $course;				
			}
			switch ($record->type){
				case MGM_CRITERIA_OPCION1:
					$course_options[$record->course]->prioridad1_type = $record->value;
					break;
				case MGM_CRITERIA_OPCION2:
					$course_options[$record->course]->prioridad2_type = $record->value;
					break;
				case MGM_CRITERIA_ESPECIALIDAD:
					if ( !isset($course_options[$record->course]->especialidades) ){
						$course_options[$record->course]->especialidades = array();
					}
					$course_options[$record->course]->especialidades[$record->value] = $record->value;
					break;			
			}
		}
	}
	return $course_options;
}

function mgm_enrol_get_prioridad_user($user, $option_course){	
	$ret = 0;
	switch ($option_course->prioridad1_type){
		case 'centros':
			(mgm_is_cc_on_mec($user->cc)) ? $ret=1: $ret=0;
			break;
		case 'especialidades':
			$uesps = explode("\n", $user->especialidades);
			foreach ($uesps as $esp){
				if ( isset($option_course->especialidades[$esp])){
					$ret = 1;
					$break;
				}
			}
			break;			
	}
	if ($ret == 0 ){
		switch ($option_course->prioridad2_type){
			case 'centros':
				(mgm_is_cc_on_mec($user->cc)) ? $ret=2: $ret=0;
				break;
			case 'especialidades':
				$uesps = explode("\n", $user->especialidades);
				foreach ($uesps as $esp){
					if ( isset($option_course->especialidades[$esp])){
						$ret = 2;
						break;
					}
				}
				break;
		}		
	}
	return $ret;
}

# n tiene que ser mayor o igual que 1
function mgm_enrol_get_option_n($editionid, $n, $course_options=NULL, $courses_completes=NULL){
	global $DB, $OUTPUT;
	//  $m=memory_get_usage()/1024/1024;
	//  echo $OUTPUT->notification("memoria antes: $m");
	$courses = array();
	if (! isset($courses_options)){
		$courses_options = mgm_enrol_get_courses_options($editionid);
	}	
	$sql = "SELECT ep.userid, ep.timemodified, ep.value, u.firstname, u.lastname, eu.cc, eu.especialidades, ec.tipo 
			FROM {edicion_preinscripcion} ep
				LEFT JOIN {user} u ON (u.id = ep.userid) 
				LEFT JOIN {edicion_user} eu ON (eu.userid = u.id)
				LEFT JOIN {edicion_centro} ec ON (ec.codigo = eu.cc)
    		WHERE ep.edicionid = ? AND ep.userid NOT IN
    			(SELECT userid FROM {edicion_inscripcion}
    			WHERE ep.edicionid = ?)
    		ORDER BY ep.timemodified ASC";
			
	if($preinscripcion = $DB->get_recordset_sql($sql, array($editionid, $editionid))) {
		#Obtiene los datos de los solicitantes por fecha de peticion de los cursos		
		foreach ($preinscripcion as $data){						
			$options = explode(",", $data->value);			
			if (count($options) >= $n){
				$courseid = $options[$n-1];
				# Si el cursos ya tiene asignadas todas las plazas, continua con el siguiente usuario
				if ( isset($courses_completes) && isset($courses_completes[$courseid]) ) {
					continue;
				}
				if (! isset($courses[$courseid])){
					$courses[$courseid] = array();
				}
				$user = new stdClass();
				$user->firstname = $data->firstname;
				$user->lastname = $data->lastname;
				$user->especialidades = $data->especialidades;
				$user->cc = $data->cc;
				$user->cctipo = $data->tipo;
				$user->id = $data->userid;
				
				$userline = new stdClass();
				$userline->option = $n;
				$userline->userid = $data->userid;
				$userline->timemodified = $data->timemodified;
				$userline->courseid = $courseid;
				$userline->value = $data->value;
				if (isset($course_options[$courseid])){
					$userline->prioridad = mgm_enrol_get_prioridad_user($user, $course_options[$courseid]);
				}else{
					$userline->prioridad = 0;
				}				
				$userline->user = $user;
				
				#Set array $courses with priorities				
				#Gestion de prioridades
				switch ($userline->prioridad){
					case 0:  # Por orden alfabetico
						$courses[$courseid][] = $userline;
						break;
					case 1: # Prioridad 1 adelantan a prioridades 0 y 2
						array_splice( $courses[$courseid], $course_options[$courseid]->prioridad1_index, 0, array($userline) );
						$course_options[$courseid]->prioridad1_index++;
						$course_options[$courseid]->prioridad2_index++;
						break;
					case 2: # Prioridades 2 adelantan a prioridades 0
						array_splice( $courses[$courseid], $course_options[$courseid]->prioridad2_index, 0, array($userline) );
						$course_options[$courseid]->prioridad2_index++;
						break;					
				}				
			}			
		}
	}
	// $m=memory_get_usage()/1024/1024;
	//  echo $OUTPUT->notification("memoria despues: $m");	
	return $courses;
}

function mgm_enrol_set_preinscripcion_data($edition){
	global $OUTPUT, $SESSION;
	//Por rendimiento 
	if ( isset($SESSION->mgm_enrol_m2)){
		unset($SESSION->mgm_enrol_m2);
	}	
	// $m=memory_get_usage()/1024/1024;
	// echo $OUTPUT->notification("memoria inicio main: $m");
	
	mgm_set_courses_edition_cache($edition->id);  // por rendimiento  	
	$option = 1;	
	$finaldata = array();
	$asigned_users = array(); # Usuarios ya asignados en algun curso actualmente
	$asigned_courses = array();# Numero de usuarios asignados en cada curso actualme
	$courses_completes = array();# Conjunto de cursos con tadas las plazas completadas
	$course_options = mgm_enrol_get_courses_options($edition->id); #Opciones de cada curso
	
	// Iterar por todas las opciones comenzando por la primera.
	while($option <= $edition->numberc){
		#echo $OUTPUT->notification("Analizando Opción: $option");
		
		#Obtener todos los solicitantes de la opcion n-esima ordenados por prioridades y fecha (en un array de cursos)
 		$courses = mgm_enrol_get_option_n($edition->id, $option, $course_options, $courses_completes); 		
		foreach ($courses as $courseid=>$course_data){
			//echo $OUTPUT->notification("Curso: $courseid");			
			if (! isset($finaldata[$courseid])){
				$finaldata[$courseid] = array();
			}
			if (!isset($asigned_courses[$courseid])){
				$asigned_courses[$courseid]=0;
			}
			#Establecer el indice de cada a partir del que se insertaran los usuarios para la opcion N
			if (isset($course_options[$courseid])){
				$course_options[$courseid]->prioridad1_index = $asigned_courses[$courseid];
				$course_options[$courseid]->prioridad2_index = $asigned_courses[$courseid];
			}
			
			$criteria = mgm_get_edition_course_criteria($edition -> id, $courseid);
			#Si el curso ya está completo continuar con el siguiente curso sin hacer nada en este 
			if ( $criteria-> plazas!=0 && $asigned_courses[$courseid] >= $criteria-> plazas ){
		#		echo $OUTPUT->notification("El curso $courseid ya tiene asignadas todas sus plazas");
				unset($courses[$courseid]); // por rendimiento				
				continue;
			}
			#Iterar sobre todas las solicitudes del curso
			foreach ($course_data as $userline){			
// 				if ( $criteria-> plazas!=0 && $asigned_courses[$courseid] >= $criteria-> plazas ){
// 					echo $OUTPUT->notification("El curso $courseid ya tiene asignadas todas sus plazas");
// 					continue;
// 				}
				# Si el usuario está ya asignado en otro curso, seguir con el siguiente usuario
				if ( array_key_exists ( $userline->userid , $asigned_users) ) {
		#			echo $OUTPUT->notification("Usuario $userline->userid ya esta admitido en otro curso");
					continue;
				}
								
				#Obtener el listado de usuarios para la tabla del curso.
				$data = mgm_enrol_get_user_preinscription_data($userline, $edition, $criteria, $courseid);				
				
				#Marcar los usuarios correspondientes para ser matriculados
 				$arr = explode('"', $data[0]);
 				$userid = $arr[3];
 				if(count($arr) >= 6 && $arr[5]=="false"){
 					// Este usuario es descartado por algún motivo
					$data[0] = '<input type="checkbox" name="' . $userid . '" />';					
				}else{
					$check = '<input type="checkbox" name="' . $userid . '" checked="true"/>';
					if($criteria -> plazas > $asigned_courses[$courseid] || $criteria -> plazas == 0) {
						#Estos usuarios tienen asignada plaza
						$data[0] = $check;
						$asigned_courses[$courseid]++;
						$asigned_users[$userline->userid]=$courseid;
					}
				}
				$pos = count($finaldata[$courseid]);
				array_unshift($data, $pos + 1);
				$finaldata[$courseid][] = $data;
			}
		#	echo $OUTPUT->notification("Curso $courseid  -- Plazas asignadas: $asigned_courses[$courseid]");
			if($criteria -> plazas <= $asigned_courses[$courseid] && $criteria -> plazas != 0) {
				$courses_completes[$courseid] = $courseid;
			}
			unset($courses[$courseid]);  //para liberar un poco de memoria
		}	
		$option++;
	}
	
	mgm_unset_courses_edition_cache();  //por rendimiento
		
	$SESSION->mgm_enrol_m2 = $finaldata;
// 	$m=memory_get_usage()/1024/1024;
// 	echo $OUTPUT->notification("memoria final main: $m");
}
