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
 * @package    mod
 * @subpackage mgm
 * @copyright  2010 - 2011 Oscar Campos <oscar.campos@open-phoenix.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot."/mod/mgm/locallib.php");
require_once($CFG->dirroot.'/user/filters/lib.php');

require_login();

require_capability('mod/mgm:aprobe', get_context_instance(CONTEXT_SYSTEM));

$id = optional_param('id', 0, PARAM_INT);    // Edition id
$page = optional_param('page', 0, PARAM_INT);  
$recordsperpage = optional_param('recordsperpage', 30, PARAM_INT);
$search = optional_param('search', '', PARAM_RAW);

if (!$site = get_site()) {
    error('Site isn\'t defined!');
}

// Strings
$strmgm            = get_string('mgm', 'mgm');
$stredicion        = get_string('edicion', 'mgm');
$strediciones      = get_string('ediciones', 'mgm');
$stralumnos        = get_string('alumnos', 'mgm');
$stredicionesmgm   = get_string('reviewnotaprobed', 'mgm');
$strplazas         = get_string('plazas', 'mgm');
$strfechainicio    = get_string('fechainicio', 'mgm');
$strfechafin       = get_string('fechafin', 'mgm');
$straddedicion     = get_string('addedicion', 'mgm');
$stradministration = get_string('administration');
$strdescription    = get_string('description');
$strcourses        = get_string('courses');
$strout            = get_string('out', 'mgm');
$stryes            = get_string('yes');
$strno             = get_string('no');


// Numalumnos
$totalalumnos = 0;

// Editions
$editions = get_records('edicion');

// Print the page and form
$strgroups = get_string('groups');
$strparticipants = get_string('participants');
$stradduserstogroup = get_string('adduserstogroup', 'group');
$strusergroupmembership = get_string('usergroupmembership', 'group');


// Navigation links
$navlinks = array();
$navlinks[] = array('name' => $stradministration, 'link' => '', 'type' => 'misc');
$navlinks[] = array('name' => $strediciones, 'link' => 'review.php', 'type' => 'misc');
$navlinks[] = array('name' => $stredicionesmgm, 'link' => '', 'type' => 'activity');
$navigation = build_navigation($navlinks);

print_header($site->shortname.': '.$strmgm, $stredicionesmgm, build_navigation($navlinks),
             '', '', true);

if ($id) {
    $alumnos = array();    
          
    $data = mgm_get_usuarios_no_inscritos($id, $search, $page, $recordsperpage);
    $totalalumnos = $data['userscount'];
    foreach($data['users'] as $alumno) {
        $record = new object();
        $record->id = $alumno->userid;
        $record->nombre = $alumno->firstname.' '.$alumno->lastname;
        $record->correo = $alumno->email;     
        $record->cursos = mgm_courses_from_user_choices($alumno->value);
                
        foreach($record->cursos as $curso) {
            $criteria = mgm_get_edition_course_criteria($id, $curso->id);
            $curso->plazas = $criteria->plazas;
        }
        
        $record->dni = ($alumno->dni) ? $alumno->dni : '00000000H';
        $record->cc = ($alumno->cc) ? $alumno->cc : 0;
        $record->especialidades = ($alumno->especialidades) ? explode("\n", $alumno->especialidades) : array();
        
        $record->fecha = $alumno->timemodified;
        
        $cc_type = mgm_get_cc_type($record->cc, true);        
        $record->real_cc_type = $cc_type;                                        
        if ($cc_type == MGM_PUBLIC_CENTER) {
            $record->cc_type = get_string('cc_public', 'mgm');
        } else if ($cc_type == MGM_MIXIN_CENTER) {
            $record->cc_type = get_string('cc_mixin', 'mgm');
        } else if ($cc_type == MGM_PRIVATE_CENTER) {
            $record->cc_type = get_string('cc_private', 'mgm');
        } else {         
            $record->cc_type = $cc_type;
        }        
        $alumnos[] = $record;
    }   

    // Table data
    foreach($alumnos as $alumno) {            
        // Especialidades
        $especs = $alumno->especialidades;
        $userespecs = '<select name="especialidades" readonly="">';
        foreach ($especs as $espec) {
            $userespecs .= '<option name="'.$espec.'">'.mgm_translate_especialidad($espec).'</option>';
        }
        $userespecs .= '</select>';

        // Courses
        $courses = '<select name="courses" readonly="">';
        foreach($alumno->cursos as $course) {
            $courses .= '<option name="'.$course->id.'">'.$course->fullname.'</option>';
        }
        $courses .= '</select>';
        
        if ($alumno->real_cc_type == MGM_PRIVATE_CENTER || $alumno->real_cc_type == -1) {
            $name = '<span style="color: red;">(*)</span> '.'<a href="../../user/view.php?id='.$alumno->id.'&amp;course='.$site->id.'">'.$alumno->nombre.'</a>';    
        } else {
            $name = '<a href="../../user/view.php?id='.$alumno->id.'&amp;course='.$site->id.'">'.$alumno->nombre.'</a>';
        }
        
        $alumnostable->data[] = array(
            $name,
            '<a href="mailto:'.$alumno->correo.'">'.$alumno->correo.'</a>',
            $alumno->dni,
            $alumno->cc,
            $alumno->cc_type,
            (empty($alumno->especialidades)) ? get_string('sinespecialidades', 'mgm') : $userespecs,
            $courses,
            date("d/m/Y H:i\"s", $alumno->fecha),
        );
    }

    // Table header
    $alumnostable->head = array(get_string('name'), get_string('configsectionmail', 'admin'), get_string('dni', 'mgm'), get_string('cc', 'mgm'), get_string('cc_type', 'mgm'), get_string('especialidades', 'mgm'), get_string('courses'), get_string('date'));
    $alumnostable->align = array('left', 'left', 'left', 'left', 'left', 'left', 'left', 'left');
} else {
    if (isset($editions) && is_array($editions)) {
        foreach($editions as $edition) {
            // Check if user can see the edition.
            if (!mgm_can_do_view()) {
                continue;
            }

            $editiontable->data[] = array(
            	'<a title="'.$edition->description.'" href="review.php?id='.$edition->id.'">'.$edition->name.'</a>',
                date('d/m/Y', $edition->inicio),
                date('d/m/Y', $edition->fin),
                mgm_count_courses($edition),
                mgm_get_edition_plazas($edition),
                mgm_get_edition_out($edition)
            );
        }
    }

    // Table header
    $editiontable->head  = array($stredicion, $strfechainicio, $strfechafin, $strcourses, $strplazas, $strout);
    $editiontable->align = array('left', 'left', 'left', 'center', 'center', 'center');
}

// Output the page

if (isset($editiontable)) {
    print_heading($strediciones);    
    print_table($editiontable);    
} else {        
    print_heading($stralumnos." (".$totalalumnos.")");
    echo '<form action="">
            <label for="busca">Buscar por nombre o email: <br /></label>
            <input type="text" name="search" id="busca" />
            <input type="submit" value="Buscar" />
            <input type="hidden" name="id" value="'.$id.'" />
          </form><br />';
    print_paging_bar($totalalumnos, $page, $recordsperpage, "?id=".$id."&amp;recordsperpage=".$recordsperpage."&amp;search=".$search."&amp;");
    print_table($alumnostable);        
    print_paging_bar($totalalumnos, $page, $recordsperpage, "?id=".$id."&amp;recordsperpage=".$recordsperpage."&amp;search=".$search."&amp;");
}

print_footer();