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

function export_report($report, $filtercourseid=false, $reportname=false, $admin=false, $filtergroupid){
	global $CFG;
  require_once($CFG->dirroot.'/mod/mgm/oppdflib.class.php');
  require_once($CFG->dirroot.'/mod/mgm/reports/acta.class.php');
  require_once($CFG->dirroot."/mod/mgm/locallib.php");
  $finalreport=$report->finalreport;
  $table = $finalreport->table;
	$matrix = array();
	$filename = 'report_'.(time()).'.pdf';

    if (!empty($table->head)) {
        $countcols = count($table->head);
        $keys=array_keys($table->head);
        $lastkey = end($keys);
        foreach ($table->head as $key => $heading) {
                $matrix[0][$key] = str_replace("\n",' ',htmlspecialchars_decode(strip_tags(nl2br($heading))));
        }
    }

    if (!empty($table->data)) {
        foreach ($table->data as $rkey => $row) {
            foreach ($row as $key => $item) {
                $matrix[$rkey + 1][$key] = str_replace("\n",' ',htmlspecialchars_decode(strip_tags(nl2br($item))));
            }
        }
    }
    $cabecera1=$report->config->name;
    $colwidth=0;
    if ($reportname=='Acta'){
    	$pdffile = new ACTAPDF();
    	$username=$report->currentuser->lastname . ', ' . $report->currentuser->firstname;
    	$coursename='--';
    	$edicionname='--';
    	$fechas='';
    	if ($filtercourseid){
    		$course=get_record('course', 'id', $filtercourseid);
    		if ($course){
    			$coursename=$course->fullname;
    			$course_extend=get_record('edicion_course', 'courseid', $filtercourseid);
    			if ($course_extend){
    				$fechas="\nFecha: " . date('d/m/Y', $course_extend->fechainicio) . " - " . date('d/m/Y', $course_extend->fechafin);
    			}
    		}
    		$edition=mgm_get_course_edition($filtercourseid);
    		if ($edition){
    			$edicionname=$edition->name;
    		}
    	}
			if ($admin){
				$groupid=trim($filtergroupid, '()');
				$roles=mgm_get_certification_roles();
				$roleid=$roles['tutor'];
				$sql="SELECT ra.userid FROM ". $CFG -> prefix . "role_assignments ra left join ". $CFG -> prefix . "groups_members gm on (ra.userid=gm.userid)
				where contextid IN (SELECT id FROM ". $CFG -> prefix . "context m where contextlevel=50 and instanceid=".$filtercourseid.")
				and groupid=".$groupid." and roleid=". $roleid ;
				if ($tutorid=get_record_sql($sql)){
					$t=get_record('user', 'id', $tutorid->userid);
					$tutor=$t->lastname . ', ' . $t->firstname;
				}else{
					$tutor='Desconocido';
				}
				#Parametrizar
				$pdffile->setAdminMsg(get_string('acta_admin_msg','mgm'));
				$pdffile->setAdminSig(get_string('acta_admin_sig','mgm'));
				$username='';

			}
			else{
       $tutor=$username;
			}
			$alumnos=$rkey+1;
			$cabecera2="Edición: " .$edicionname ."\nCurso: ". $coursename ."\nTutor/a: " . $tutor. $fechas . "   Alumnos: $alumnos";
			$pdffile->opCabecera($cabecera1, $cabecera2);
			$pdffile->SetUsername($username);
			$colwidth=array(13,70,40,35,17);
    }else{
    	$pdffile = new OPPDF();
    	$pdffile->opCabecera($cabecera1);
    }
    $downloadfilename = clean_filename($filename);
    $pdffile->SetFont('Arial','',8);
    $pdffile->AliasNbPages();
    $pdffile->AddPage();
    $pdffile->addTable($matrix, $colwidth);
    $pdffile->Output($name=$downloadfilename,$dest='D');
    exit;
}

?>