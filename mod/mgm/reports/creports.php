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
 * @copyright  2010 - 2012 Jesús Jaén <jesus.jaen@open-phoenix.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//Constantes



require_once('../../../config.php');
require_once($CFG->dirroot."/mod/mgm/locallib.php");
require_once($CFG->dirroot."/mod/mgm/mgm_forms.php");
require_once($CFG->libdir.'/adminlib.php');

$strtitle = get_string('admin_report', 'mgm');
require_login();
require_capability('mod/mgm:createedicion', get_context_instance(CONTEXT_SYSTEM));




function mgm_creports($create=1, $update=0, $sqlfunc=1){
		$TYPE='5';
		global $USER;
		$reports=array();

		##Funciones:
		$sqlarr=array();
		##funcion para determinar la comunidad autonoma a partir del codigo de centro(cc INT)
		$sqlarr[0]='drop function if exists get_ca';
		$sqlarr[1]="create function get_ca(cc INT)
returns varchar(20)
DETERMINISTIC
CONTAINS SQL
Begin
  declare ca varchar(20);
  declare cod varchar(2);
  declare cc2 varchar(8);
  set cc2=cc;
  WHILE length(cc2) < 8 DO
    select concat('0',cc2) into cc2;
  END WHILE;
  set cod=substring(cc2, 1, 2);
  select case when cod REGEXP '04|11|14|18|21|23|29|41' then 'ANDALUCIA'
	      when cod REGEXP '22|44|50' then 'ARAGON'
	      when cod REGEXP '33' then 'ASTURIAS'
	      when cod REGEXP '07' then 'BALEARES'
	      when cod REGEXP '35|38' then 'CANARIAS'
	      when cod REGEXP '39' then 'CANTABRIA'
	      when cod REGEXP '05|09|24|34|37|40|42|47|49' then 'CASTILLA Y LEON'
	      when cod REGEXP '02|13|16|19|45' then 'CASTILLA LA MANCHA'
	      when cod REGEXP '08|17|25|43' then 'CATALUÑA'
	      when cod REGEXP '03|12|46' then 'VALENCIA'
	      when cod REGEXP '10|06' then 'EXTREMADURA'
	      when cod REGEXP '15|27|32|36' then 'GALICIA'
	      when cod REGEXP '28' then 'MADRID'
	      when cod REGEXP '30' then 'MURCIA'
	      when cod REGEXP '31' then 'NAVARRA'
	      when cod REGEXP '01|20|48' then 'EUSKADI'
	      when cod REGEXP '26' then 'RIOJA'
	      when cod REGEXP '51' then 'CEUTA'
	      when cod REGEXP '52' then 'MELILLA'
        when cod='00' then case when cc2='00000075' then 'ONCE'
				      when cc2='00000050' then 'EXTRANJERO'
				      when cc2='00000025' then 'AULAS ITINERANTES'
				      else 'INDEFINIDA'
				 end
  end into ca;
  RETURN ca;
end";
		##Funcion para obtener los cuerpos docentes a partir del codigo
		$sqlarr[2]='drop function if exists get_cd';
		$sqlarr[3]="create function get_cd(ccd varchar(4))
returns varchar(50)
DETERMINISTIC
CONTAINS SQL
Begin
  declare cd varchar(50);
  select case when ccd like '0000' then 'NO FUNCIONARIO'
              when ccd like '0590' then 'PROFESORES DE ENSEÑANZA SECUNDARIA'
	      when ccd like '0591' then 'PROF. TECNICOS FORMACION PROFESIONAL'
	      when ccd like '0592' then 'PROFESORES DE ESC. OFICIALES DE IDIOMAS'
	      when ccd like '0593' then 'CATEDRATICOS DE MUSICA Y ARTES ESCENICAS'
	      when ccd like '0594' then 'PROFESORES DE MUSICA Y ARTES ESCENICAS'
	      when ccd like '0595' then 'PROFESORES DE ARTES PLASTICAS Y DISEÑO'
	      when ccd like '0596' then 'MAESTROS DE TALLER ARTES PLAST. Y DISEÑO'
	      when ccd like '0597' then 'MAESTROS'
	      when ccd like '5407' then 'ESCALA DOCENTE GRUPO A DE LA AISS'
	      when ccd like '5423' then 'ESCALA DOCENTE GRUPO B DE LA AISS'
	      when ccd like '6470' then 'PROF.NUMER. Y PSICOL. ENS.INTEGR.'
	      when ccd like '6471' then 'PROF.MATER.TECN.-PROF.Y EDUC.E.I.'
	      when ccd like '6472' then 'PROF.PRACTICAS Y ACTIVIDADES E.I.'
	      when ccd like '7100' then 'INSPECTORES DE EDUCACION'
	      when ccd like '7110' then 'INSPECTORES AL SERVICIO ADMON. EDUCATIVA'
	      when ccd like '8100' then 'PROFESORES UNIVERSITARIOS'
	      when ccd like '8110' then 'ESCALA DE TEC.DE GESTIÓN DE U.SEVILLA'
	      when ccd like '8120' then 'ESCALA TEC. DE GESTIÓN DE U.DE CADIZ'
	      when ccd like '8121' then 'OTROS PROFESORES/OTROS PROFESIONALES'
	      when ccd like '8122' then 'OTROS FUNCIONARIOS DE LA ADMINISTRACION'
	      when ccd like '8123' then 'CATEDRÁTICOS DE UNIVERSIDAD'
	      else 'DESCONOCIDO'
         end into cd;
  RETURN cd;
end ";
		if ($sqlfunc){
	  	execute_sql_arr($sqlarr, $continue=true, $feedback=true);
		}
		##Definicion de informes
		#Acta
		$reports['Acta']=new stdClass();
		$reports['Acta']->name =get_string('Acta', 'mgm');
		$reports['Acta']->summary =get_string('Acta_summary', 'mgm');
		$reports['Acta']->type ='sql';
		$reports['Acta']->jsordering= 1;
		$reports['Acta']->visible= 1;
		$reports['Acta']->components='a:4:{s:9:"customsql";a:1:{s:6:"config";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"2097152";s:8:"querysql";s:1314:"select++%28%40rownum%3A%3D%40rownum%2B1%29+AS+Numero%2C+cmat.lastname+as+Apellidos%2C+cmat.firstname+as+Nombre%2C+cmat.dni+DNI%2C+IF+%28ccert.finalgrade%3D2.0%2C%5C%27Apto%5C%27%2C%5C%27No+apto%5C%27%29+as+Calificacion+from++%28SELECT+%40rownum%3A%3D0%29+r%2C+%0D%0A%28SELECT+DISTINCT+u.id+as+userid%2C+u.firstname%2C+u.lastname%2C+upper%28eu.dni%29+DNI%2C+c.id+as+courseid%0D%0A++FROM%0D%0A%09prefix_user++AS+u+LEFT+JOIN+prefix_edicion_user+eu+on+%28u.id%3Deu.userid%29+LEFT+JOIN+prefix_groups_members+AS+gm++on+%28eu.userid%3Dgm.userid%29+%2C%0D%0A%09prefix_edicion_course+ec+LEFT+JOIN+prefix_course+AS+c+on+%28ec.courseid%3Dc.id%29%2C%0D%0A%09prefix_role_assignments+AS+ra+INNER+JOIN+prefix_context+AS+context+ON+ra.contextid%3Dcontext.id+++%0D%0A++WHERE++context.contextlevel+%3D+50+AND+ra.roleid%3D5+AND+u.id%3Dra.userid+AND+context.instanceid%3Dc.id%0D%0A++%25%25FILTER_EDITIONS%3Aec.edicionid%25%25%0D%0A++%25%25FILTER_COURSES%3Ac.id%25%25%0D%0A++%25%25FILTER_GROUPS%3Agm.groupid%25%25%0D%0A++%0D%0A++order+by+u.lastname%2C+u.firstname%29+as+cmat%0D%0Aleft+join+%0D%0A%28SELECT+courseid%2C+finalgrade%2C+userid++FROM+prefix_grade_grades+gg%2C+prefix_grade_items+gi+where+gg.itemid%3Dgi.id+and+itemname+like+%5C%27cert%25%5C%27%29+ccert+on+%28cmat.courseid+%3Dccert.courseid+and+cmat.userid%3Dccert.userid%29";s:12:"submitbutton";s:15:"Guardar+cambios";}}s:7:"filters";a:1:{s:8:"elements";a:3:{i:0;a:5:{s:2:"id";s:15:"Y2qaEO3aztEDL5m";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:8:"editions";s:14:"pluginfullname";s:9:"Ediciones";s:7:"summary";s:97:"Este+filtro+muestra+una+lista+de+ediciones.+Solo+se+puede+seleccionar+una+edicion+al+mismo+tiempo";}i:1;a:5:{s:2:"id";s:15:"dPETvryqboTny6d";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:7:"courses";s:14:"pluginfullname";s:6:"Cursos";s:7:"summary";s:87:"Este+filtro+muestra+una+lista+de+cursos.+S%C3%B3lo+un+curso+puede+seleccionado+a+la+vez";}i:2;a:5:{s:2:"id";s:15:"XZT29ahEo3czKHO";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:6:"groups";s:14:"pluginfullname";s:6:"Grupos";s:7:"summary";s:96:"Este+filtro+muestra+una+lista+de+grupos.+S%C3%B3lo+se+puede+seleccionar+un+grupo+al+mismo+tiempo";}}}s:11:"permissions";a:2:{s:6:"config";O:6:"object":1:{s:13:"conditionexpr";s:0:"";}s:8:"elements";a:1:{i:0;a:5:{s:2:"id";s:15:"DjkzXms6I06I55P";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:9:"custommgm";s:14:"pluginfullname";s:3:"MGM";s:7:"summary";s:46:"Acceso+a+informes+controlado+por+el+modulo+MGM";}}}s:5:"calcs";a:1:{s:8:"elements";a:5:{i:0;a:5:{s:2:"id";s:15:"n94q3SS2BSWSkRU";s:8:"formdata";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"2097152";s:6:"column";s:1:"3";s:12:"submitbutton";s:7:"Agregar";}s:10:"pluginname";s:3:"sum";s:14:"pluginfullname";s:4:"Suma";s:7:"summary";s:12:"Matriculados";}i:1;a:5:{s:2:"id";s:15:"pT0FhrbZt9WhUIB";s:8:"formdata";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"2097152";s:6:"column";s:1:"4";s:12:"submitbutton";s:7:"Agregar";}s:10:"pluginname";s:3:"sum";s:14:"pluginfullname";s:4:"Suma";s:7:"summary";s:11:"Presentados";}i:2;a:5:{s:2:"id";s:15:"bTrdYqyZD9uQKCn";s:8:"formdata";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"2097152";s:6:"column";s:1:"5";s:12:"submitbutton";s:7:"Agregar";}s:10:"pluginname";s:3:"sum";s:14:"pluginfullname";s:4:"Suma";s:7:"summary";s:2:"E1";}i:3;a:5:{s:2:"id";s:15:"sTXIJ6ruGMxU9Cc";s:8:"formdata";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"2097152";s:6:"column";s:1:"6";s:12:"submitbutton";s:7:"Agregar";}s:10:"pluginname";s:3:"sum";s:14:"pluginfullname";s:4:"Suma";s:7:"summary";s:7:"Ecuador";}i:4;a:5:{s:2:"id";s:15:"svbXk9lXQjq27Qb";s:8:"formdata";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"2097152";s:6:"column";s:1:"7";s:12:"submitbutton";s:7:"Agregar";}s:10:"pluginname";s:3:"sum";s:14:"pluginfullname";s:4:"Suma";s:7:"summary";s:12:"Certificados";}}}}';
		$reports['Acta']->export='ods,xls,';
		$reports['Acta']->ownerid=$USER->id;
		$reports['Acta']->courseid=SITEID;

		##Inmformacion de ediciones
		$reports['Report001']=new stdClass();
		$reports['Report001']->name =get_string('Report001', 'mgm');
		$reports['Report001']->summary =get_string('Report001_summary', 'mgm');
		$reports['Report001']->type ='sql';
		$reports['Report001']->jsordering= 1;
		$reports['Report001']->visible= 1;
		$reports['Report001']->components='a:4:{s:9:"customsql";a:1:{s:6:"config";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"2097152";s:8:"querysql";s:2123:"select+cmat.fullname+Curso%2C+cmat.fechainicio+as+Inicio%2C+cmat.fechafin+as+Fin%2C+cmat.matriculados+as+Matriculados%2C+cpre.presentados+as+Presentados%2C+ce1.e1+as+E1%2C+cec.ec+as+Ecuador%2C+ccert.certificados+as+Certificados+from+%0D%0A++%28SELECT+c.id%2C+c.fullname%2C+date_format%28from_unixtime%28ec.fechainicio%29%2C+%5C%27%25d%2F%25m%2F%25Y%5C%27%29+as+fechainicio%2C+date_format%28from_unixtime%28ec.fechafin%29%2C+%5C%27%25d%2F%25m%2F%25Y%5C%27%29+as+fechafin+%2C+count%28ra.id%29+as+matriculados+FROM+%0D%0A%09prefix_user+AS+u%2C+prefix_edicion_course+ec+LEFT+JOIN+prefix_course+AS+c+on+ec.courseid%3Dc.id%2C+prefix_role_assignments+AS+ra+INNER+JOIN+prefix_context+AS+context+ON+ra.contextid%3Dcontext.id+++%0D%0Awhere+context.contextlevel+%3D+50+AND+ra.roleid%3D5+AND+u.id%3Dra.userid+AND+context.instanceid%3Dc.id%0D%0A%25%25FILTER_EDITIONS%3Aec.edicionid%25%25%0D%0A%25%25FILTER_CATEGORIES%3Ac.category%25%25%0D%0A%25%25FILTER_COURSES%3Ac.id%25%25%0D%0A+group+by+c.id%2C+c.fullname%29+as+cmat%0D%0A++left+join+%28SELECT+courseid%2C+count%28itemid%29+as+presentados+FROM+prefix_grade_grades+gg%2C+prefix_grade_items+gi+where+gg.itemid%3Dgi.id+and+itemname+like+%5C%27pre%25%5C%27+and+finalgrade%3D2+group+by+courseid%29+as+cpre+on+%28cmat.id%3Dcpre.courseid%29%0D%0A++left+join+%28SELECT+courseid%2C+count%28itemid%29+as+e1+FROM+prefix_grade_grades+gg%2C+prefix_grade_items+gi+where+gg.itemid%3Dgi.id+and+itemname+like+%5C%27e1%25%5C%27+and+finalgrade+%3D+2+group+by+courseid+order+by+courseid%29+as+ce1+on+%28cmat.id%3Dce1.courseid%29%0D%0Aleft+join+%28SELECT+courseid%2C+count%28itemid%29+as+ec+FROM+prefix_grade_grades+gg%2C+prefix_grade_items+gi+where+gg.itemid%3Dgi.id+and+itemname+like+%5C%27ec%25%5C%27+and+finalgrade+%3D+2+group+by+courseid+order+by+courseid%29+as+cec+on+%28cmat.id%3Dcec.courseid%29+%0D%0A++left+join+%28SELECT+courseid%2C+count%28itemid%29+as+certificados+FROM+prefix_grade_grades+gg%2C+prefix_grade_items+gi+where+gg.itemid%3Dgi.id+and+itemname+like+%5C%27certif%25%5C%27+and+finalgrade+%3D+2+group+by+courseid%29+ccert+on+%28cmat.id+%3Dccert.courseid%29%0D%0Aorder+by+cmat.fullname";s:12:"submitbutton";s:15:"Guardar+cambios";}}s:7:"filters";a:1:{s:8:"elements";a:3:{i:0;a:5:{s:2:"id";s:15:"ijr0IlRl46iaSOm";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:8:"editions";s:14:"pluginfullname";s:9:"Ediciones";s:7:"summary";s:98:"Este+filtro+muestra+una+lista+de+ediciones.+Solo+se+puede+seleccionar+una+ediciona+al+mismo+tiempo";}i:1;a:5:{s:2:"id";s:15:"5HNJ9M7wQnTRxAk";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:10:"categories";s:14:"pluginfullname";s:25:"Filtro+de+categor%C3%ADas";s:7:"summary";s:31:"Para+filtrar+por+categor%C3%ADa";}i:2;a:5:{s:2:"id";s:15:"CShdpgWcsPC7T5J";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:7:"courses";s:14:"pluginfullname";s:6:"Cursos";s:7:"summary";s:87:"Este+filtro+muestra+una+lista+de+cursos.+S%C3%B3lo+un+curso+puede+seleccionado+a+la+vez";}}}s:11:"permissions";a:1:{s:6:"config";O:6:"object":1:{s:13:"conditionexpr";s:0:"";}}s:5:"calcs";a:1:{s:8:"elements";a:5:{i:0;a:5:{s:2:"id";s:15:"n94q3SS2BSWSkRU";s:8:"formdata";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"2097152";s:6:"column";s:1:"3";s:12:"submitbutton";s:7:"Agregar";}s:10:"pluginname";s:3:"sum";s:14:"pluginfullname";s:4:"Suma";s:7:"summary";s:12:"Matriculados";}i:1;a:5:{s:2:"id";s:15:"pT0FhrbZt9WhUIB";s:8:"formdata";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"2097152";s:6:"column";s:1:"4";s:12:"submitbutton";s:7:"Agregar";}s:10:"pluginname";s:3:"sum";s:14:"pluginfullname";s:4:"Suma";s:7:"summary";s:11:"Presentados";}i:2;a:5:{s:2:"id";s:15:"bTrdYqyZD9uQKCn";s:8:"formdata";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"2097152";s:6:"column";s:1:"5";s:12:"submitbutton";s:7:"Agregar";}s:10:"pluginname";s:3:"sum";s:14:"pluginfullname";s:4:"Suma";s:7:"summary";s:2:"E1";}i:3;a:5:{s:2:"id";s:15:"sTXIJ6ruGMxU9Cc";s:8:"formdata";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"2097152";s:6:"column";s:1:"6";s:12:"submitbutton";s:7:"Agregar";}s:10:"pluginname";s:3:"sum";s:14:"pluginfullname";s:4:"Suma";s:7:"summary";s:7:"Ecuador";}i:4;a:5:{s:2:"id";s:15:"svbXk9lXQjq27Qb";s:8:"formdata";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"2097152";s:6:"column";s:1:"7";s:12:"submitbutton";s:7:"Agregar";}s:10:"pluginname";s:3:"sum";s:14:"pluginfullname";s:4:"Suma";s:7:"summary";s:12:"Certificados";}}}}';
		$reports['Report001']->export='ods,xls,pdf,';
		$reports['Report001']->ownerid=$USER->id;
		$reports['Report001']->courseid=SITEID;

		##Imformacion detallada
		$reports['Report002']=new stdClass();
		$reports['Report002']->name =get_string('Report002', 'mgm');
		$reports['Report002']->summary =get_string('Report002_summary', 'mgm');
		$reports['Report002']->type ='sql';
		$reports['Report002']->jsordering= 1;
		$reports['Report002']->visible= 1;
		$reports['Report002']->components='a:4:{s:9:"customsql";a:1:{s:6:"config";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"8388608";s:8:"querysql";s:2082:"select+c1.fullname+Curso%2C+c1.Comunidad%2C+c1.dgenerica+Dgenerica%2C+get_cd%28c1.codcuerpodocente%29+CDocente%2C+c1.codniveleducativo+CNEducativo%2C+c1.codcuerpodocente+CCDocente%2C+c1.cc+%2C+c1.codpais+Pais%2C+c1.sexo+Sexo%2C+case+when+c1.tipo%3D0+then+%5C%27Publico%5C%27+when+c1.tipo%3D1+then+%5C%27Concertado%5C%27+when+c1.tipo%3D2+then+%5C%27Privado%5C%27+end+as+Tipo%2C+case+when+ccert.finalgrade%3D2+then+%5C%27SI%5C%27+else+%5C%27NO%5C%27+end+Certificado%2C++1+as+Total%2C+c1.firstname+Nombre%2C+c1.lastname+Apellidos%2C+c1.dni+DNI%0D%0Afrom+++%0D%0A%28SELECT+u.firstname%2C+u.lastname%2C+eu.dni%2C+c.fullname%2C+c.id+as+courseid%2C+get_ca%28eu.cc%29+Comunidad%2C+eu.codniveleducativo%2C+eu.codcuerpodocente%2C+eu.sexo%2C+eu.codpais%2C+eu.cc%2C+eu.userid+as+euuserid%2C+ect.tipo%2C+ect.dgenerica%2C+u.id+as+userid%0D%0A+++++FROM+prefix_user+u+left+join++prefix_edicion_user+eu+on+%28u.id%3Deu.userid%29+left+join+prefix_edicion_centro+ect+on+ect.codigo%3Deu.cc+%2C%0D%0A+++++prefix_edicion_course+ec+LEFT+JOIN+prefix_course+AS+c+on+ec.courseid%3Dc.id%2C+prefix_role_assignments+AS+ra+INNER+JOIN+prefix_context+AS+context+ON+ra.contextid%3Dcontext.id+++%0D%0A+++++where+context.contextlevel+%3D+50+AND+ra.roleid%3D5+AND+u.id%3Dra.userid+AND+context.instanceid%3Dc.id+++++%0D%0A++%25%25FILTER_EDITIONS%3Aec.edicionid%25%25%0D%0A++%25%25FILTER_CATEGORIES%3Ac.category%25%25%0D%0A++%25%25FILTER_COURSES%3Ac.id%25%25%0D%0A++%25%25FILTER_CMAT%3Aget_ca%28eu.cc%29%25%25%0D%0A++%25%25FILTER_DGENERICA%3Aect.dgenerica%25%25%0D%0A++%25%25FILTER_TIPOCOL%3Aect.tipo%25%25%0D%0A++%25%25FILTER_CCDOCENTE%3Aeu.codcuerpodocente%25%25%0D%0A++%25%25FILTER_SEXO%3Aeu.sexo%25%25%0D%0A+++++%29+as+c1%0D%0Aleft+join+%0D%0A%28SELECT+courseid%2C+finalgrade%2C+userid++FROM+prefix_grade_grades+gg%2C+prefix_grade_items+gi+where+gg.itemid%3Dgi.id+and+itemname+like+%5C%27certif%25%5C%27%29+ccert+%0D%0Aon+%28c1.courseid+%3Dccert.courseid+and+c1.userid%3Dccert.userid%29+%0D%0AWhere%0D%0Atrue%0D%0A%25%25FILTER_CERTIFICADO%3Afinalgrade%25%25%0D%0Aorder+by++Curso%2C+Comunidad%2C+dgenerica%2C+CDocente";s:12:"submitbutton";s:15:"Guardar+cambios";}}s:7:"filters";a:1:{s:8:"elements";a:9:{i:0;a:5:{s:2:"id";s:15:"0zRhk7acfFl3JdI";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:8:"editions";s:14:"pluginfullname";s:9:"Ediciones";s:7:"summary";s:97:"Este+filtro+muestra+una+lista+de+ediciones.+Solo+se+puede+seleccionar+una+edicion+al+mismo+tiempo";}i:1;a:5:{s:2:"id";s:15:"b92vvXpIHwDLi3J";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:10:"categories";s:14:"pluginfullname";s:25:"Filtro+de+categor%C3%ADas";s:7:"summary";s:31:"Para+filtrar+por+categor%C3%ADa";}i:2;a:5:{s:2:"id";s:15:"jbkVdM3B8eP7fzg";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:7:"courses";s:14:"pluginfullname";s:6:"Cursos";s:7:"summary";s:87:"Este+filtro+muestra+una+lista+de+cursos.+S%C3%B3lo+un+curso+puede+seleccionado+a+la+vez";}i:3;a:5:{s:2:"id";s:15:"CfAAFvXYh6u6TcH";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:4:"cmat";s:14:"pluginfullname";s:23:"Comunidad+Aut%C3%B3noma";s:7:"summary";s:104:"Filtro+de+Comunidad+Aut%C3%B3noma.+S%C3%B3lo+se+puede+seleccionar+una+Comunidad+autonoma+al+mismo+tiempo";}i:4;a:5:{s:2:"id";s:15:"VTVUgA5OL7FGUVb";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:9:"dgenerica";s:14:"pluginfullname";s:31:"Denominaci%C3%B3n+Gen%C3%A9rica";s:7:"summary";s:112:"Filtro+de+Denominaci%C3%B3n+Ger%C3%A9rica+del+Centro.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:5;a:5:{s:2:"id";s:15:"8CIsZCspnYCt5uE";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:7:"tipocol";s:14:"pluginfullname";s:15:"Tipo+de+Colegio";s:7:"summary";s:127:"Filtro+de+tipo+de+centro+%28P%C3%BAblico%2C+Concertado+y+Privado%29.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:6;a:5:{s:2:"id";s:15:"A5Nxr5eZMVs8FZE";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:9:"ccdocente";s:14:"pluginfullname";s:14:"Cuerpo+Docente";s:7:"summary";s:92:"Filtro+de+tipo+de+Cuerpo+Docente.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:7;a:5:{s:2:"id";s:15:"zW37WDZwkPAta1p";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:11:"certificado";s:14:"pluginfullname";s:11:"Certificado";s:7:"summary";s:56:"Indica+si+se+ha+superado+la+tarea+de+Certificaci%C3%B3n.";}i:8;a:5:{s:2:"id";s:15:"qSJvakPBX0gvcJo";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:4:"sexo";s:14:"pluginfullname";s:4:"Sexo";s:7:"summary";s:46:"Seleccion+entre+Hombre+%28H%29+y+Mujer+%28M%29";}}}s:5:"calcs";a:1:{s:8:"elements";a:1:{i:0;a:5:{s:2:"id";s:15:"55ArKxFtPfxJNnI";s:8:"formdata";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"8388608";s:6:"column";s:2:"11";s:12:"submitbutton";s:7:"Agregar";}s:10:"pluginname";s:3:"sum";s:14:"pluginfullname";s:4:"Suma";s:7:"summary";s:5:"Total";}}}s:11:"permissions";a:2:{s:6:"config";O:6:"object":1:{s:13:"conditionexpr";s:0:"";}s:8:"elements";a:1:{i:0;a:5:{s:2:"id";s:15:"eWMj0xuLK7TMcg6";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:9:"custommgm";s:14:"pluginfullname";s:3:"MGM";s:7:"summary";s:46:"Acceso+a+informes+controlado+por+el+modulo+MGM";}}}}';
		$reports['Report002']->export='ods,xls,';
		$reports['Report002']->ownerid=$USER->id;
		$reports['Report002']->pagination=100;
		$reports['Report002']->courseid=SITEID;

		##Información Comunidad Autonoma
		$reports['Report003']=new stdClass();
		$reports['Report003']->name =get_string('Report003', 'mgm');
		$reports['Report003']->summary =get_string('Report003_summary', 'mgm');
		$reports['Report003']->type ='sql';
		$reports['Report003']->jsordering= 1;
		$reports['Report003']->visible= 1;
		$reports['Report003']->components='a:3:{s:7:"filters";a:1:{s:8:"elements";a:9:{i:0;a:5:{s:2:"id";s:15:"X2MKTWAJCx81qot";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:8:"editions";s:14:"pluginfullname";s:9:"Ediciones";s:7:"summary";s:97:"Este+filtro+muestra+una+lista+de+ediciones.+Solo+se+puede+seleccionar+una+edicion+al+mismo+tiempo";}i:1;a:5:{s:2:"id";s:15:"152vl0oVP6UCZt6";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:10:"categories";s:14:"pluginfullname";s:25:"Filtro+de+categor%C3%ADas";s:7:"summary";s:31:"Para+filtrar+por+categor%C3%ADa";}i:2;a:5:{s:2:"id";s:15:"YdbsMGrbI6QLgbA";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:7:"courses";s:14:"pluginfullname";s:6:"Cursos";s:7:"summary";s:87:"Este+filtro+muestra+una+lista+de+cursos.+S%C3%B3lo+un+curso+puede+seleccionado+a+la+vez";}i:3;a:5:{s:2:"id";s:15:"TbnFT4b7TGP2d8M";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:4:"cmat";s:14:"pluginfullname";s:23:"Comunidad+Aut%C3%B3noma";s:7:"summary";s:104:"Filtro+de+Comunidad+Aut%C3%B3noma.+S%C3%B3lo+se+puede+seleccionar+una+Comunidad+autonoma+al+mismo+tiempo";}i:4;a:5:{s:2:"id";s:15:"kKVhMyJh42uxgxj";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:9:"dgenerica";s:14:"pluginfullname";s:31:"Denominaci%C3%B3n+Gen%C3%A9rica";s:7:"summary";s:112:"Filtro+de+Denominaci%C3%B3n+Ger%C3%A9rica+del+Centro.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:5;a:5:{s:2:"id";s:15:"yPzF0l3DeuyBTar";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:7:"tipocol";s:14:"pluginfullname";s:15:"Tipo+de+Colegio";s:7:"summary";s:127:"Filtro+de+tipo+de+centro+%28P%C3%BAblico%2C+Concertado+y+Privado%29.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:6;a:5:{s:2:"id";s:15:"rfmo4Rrkt8KUdqd";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:9:"ccdocente";s:14:"pluginfullname";s:14:"Cuerpo+Docente";s:7:"summary";s:92:"Filtro+de+tipo+de+Cuerpo+Docente.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:7;a:5:{s:2:"id";s:15:"a7e7I4aHNt82dTD";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:4:"sexo";s:14:"pluginfullname";s:4:"Sexo";s:7:"summary";s:46:"Seleccion+entre+Hombre+%28H%29+y+Mujer+%28M%29";}i:8;a:5:{s:2:"id";s:15:"LKHtMSbpb0ZWlaE";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:11:"certificado";s:14:"pluginfullname";s:11:"Certificado";s:7:"summary";s:56:"Indica+si+se+ha+superado+la+tarea+de+Certificaci%C3%B3n.";}}}s:9:"customsql";a:1:{s:6:"config";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"8388608";s:8:"querysql";s:1539:"select+c1.Comunidad%2C+count%28c1.userid%29+as+Total%0D%0Afrom++%0D%0A%28SELECT+c.fullname%2C+c.id+as+courseid%2C+get_ca%28eu.cc%29+Comunidad%2C+eu.codniveleducativo%2C+eu.codcuerpodocente%2C+eu.sexo%2C+eu.codpais%2C+eu.cc%2C+eu.userid+as+euuserid%2C+ect.tipo%2C+ect.dgenerica%2C+u.id+as+userid%0D%0A+++++FROM+prefix_user+u+left+join++prefix_edicion_user+eu+on+%28u.id%3Deu.userid%29+left+join+prefix_edicion_centro+ect+on+ect.codigo%3Deu.cc+%2C%0D%0A+++++prefix_edicion_course+ec+LEFT+JOIN+prefix_course+AS+c+on+ec.courseid%3Dc.id%2C+prefix_role_assignments+AS+ra+INNER+JOIN+prefix_context+AS+context+ON+ra.contextid%3Dcontext.id+++%0D%0A+++++where+context.contextlevel+%3D+50+AND+ra.roleid%3D5+AND+u.id%3Dra.userid+AND+context.instanceid%3Dc.id%0D%0A++%25%25FILTER_EDITIONS%3Aec.edicionid%25%25%0D%0A++%25%25FILTER_CATEGORIES%3Ac.category%25%25%0D%0A++%25%25FILTER_COURSES%3Ac.id%25%25%0D%0A++%25%25FILTER_CMAT%3Aget_ca%28eu.cc%29%25%25%0D%0A++%25%25FILTER_DGENERICA%3Aect.dgenerica%25%25%0D%0A++%25%25FILTER_TIPOCOL%3Aect.tipo%25%25%0D%0A++%25%25FILTER_CCDOCENTE%3Aeu.codcuerpodocente%25%25%0D%0A++%25%25FILTER_SEXO%3Aeu.sexo%25%25%0D%0A+++++%29+as+c1%0D%0Aleft+join+%0D%0A%28SELECT+courseid%2C+finalgrade%2C+userid++FROM+prefix_grade_grades+gg%2C+prefix_grade_items+gi+where+gg.itemid%3Dgi.id+and+itemname+like+%5C%27certif%25%5C%27%29+ccert+%0D%0Aon+%28c1.courseid+%3Dccert.courseid+and+c1.userid%3Dccert.userid%29+%0D%0AWhere%0D%0Atrue%0D%0A%25%25FILTER_CERTIFICADO%3Afinalgrade%25%25%0D%0Agroup+by+Comunidad%0D%0Aorder+by++Comunidad";s:12:"submitbutton";s:15:"Guardar+cambios";}}s:5:"calcs";a:1:{s:8:"elements";a:1:{i:0;a:5:{s:2:"id";s:15:"k2fRpdAPoobV3SE";s:8:"formdata";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"8388608";s:6:"column";s:1:"1";s:12:"submitbutton";s:7:"Agregar";}s:10:"pluginname";s:3:"sum";s:14:"pluginfullname";s:4:"Suma";s:7:"summary";s:5:"Total";}}}}';
		$reports['Report003']->export='ods,xls,pdf,';
		$reports['Report003']->ownerid=$USER->id;
		$reports['Report003']->courseid=SITEID;

		##Información por sexo
		$reports['Report004']=new stdClass();
		$reports['Report004']->name =get_string('Report004', 'mgm');
		$reports['Report004']->summary =get_string('Report004_summary', 'mgm');
		$reports['Report004']->type ='sql';
		$reports['Report004']->jsordering= 1;
		$reports['Report004']->visible= 1;
		$reports['Report004']->components='a:3:{s:7:"filters";a:1:{s:8:"elements";a:9:{i:0;a:5:{s:2:"id";s:15:"X2MKTWAJCx81qot";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:8:"editions";s:14:"pluginfullname";s:9:"Ediciones";s:7:"summary";s:97:"Este+filtro+muestra+una+lista+de+ediciones.+Solo+se+puede+seleccionar+una+edicion+al+mismo+tiempo";}i:1;a:5:{s:2:"id";s:15:"152vl0oVP6UCZt6";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:10:"categories";s:14:"pluginfullname";s:25:"Filtro+de+categor%C3%ADas";s:7:"summary";s:31:"Para+filtrar+por+categor%C3%ADa";}i:2;a:5:{s:2:"id";s:15:"YdbsMGrbI6QLgbA";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:7:"courses";s:14:"pluginfullname";s:6:"Cursos";s:7:"summary";s:87:"Este+filtro+muestra+una+lista+de+cursos.+S%C3%B3lo+un+curso+puede+seleccionado+a+la+vez";}i:3;a:5:{s:2:"id";s:15:"TbnFT4b7TGP2d8M";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:4:"cmat";s:14:"pluginfullname";s:23:"Comunidad+Aut%C3%B3noma";s:7:"summary";s:104:"Filtro+de+Comunidad+Aut%C3%B3noma.+S%C3%B3lo+se+puede+seleccionar+una+Comunidad+autonoma+al+mismo+tiempo";}i:4;a:5:{s:2:"id";s:15:"kKVhMyJh42uxgxj";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:9:"dgenerica";s:14:"pluginfullname";s:31:"Denominaci%C3%B3n+Gen%C3%A9rica";s:7:"summary";s:112:"Filtro+de+Denominaci%C3%B3n+Ger%C3%A9rica+del+Centro.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:5;a:5:{s:2:"id";s:15:"yPzF0l3DeuyBTar";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:7:"tipocol";s:14:"pluginfullname";s:15:"Tipo+de+Colegio";s:7:"summary";s:127:"Filtro+de+tipo+de+centro+%28P%C3%BAblico%2C+Concertado+y+Privado%29.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:6;a:5:{s:2:"id";s:15:"rfmo4Rrkt8KUdqd";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:9:"ccdocente";s:14:"pluginfullname";s:14:"Cuerpo+Docente";s:7:"summary";s:92:"Filtro+de+tipo+de+Cuerpo+Docente.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:7;a:5:{s:2:"id";s:15:"a7e7I4aHNt82dTD";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:4:"sexo";s:14:"pluginfullname";s:4:"Sexo";s:7:"summary";s:46:"Seleccion+entre+Hombre+%28H%29+y+Mujer+%28M%29";}i:8;a:5:{s:2:"id";s:15:"LKHtMSbpb0ZWlaE";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:11:"certificado";s:14:"pluginfullname";s:11:"Certificado";s:7:"summary";s:56:"Indica+si+se+ha+superado+la+tarea+de+Certificaci%C3%B3n.";}}}s:9:"customsql";a:1:{s:6:"config";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"8388608";s:8:"querysql";s:1501:"select+case+when+sexo+like%5C%27H%5C%27+then+%5C%27Hombre%5C%27+when+sexo+like+%5C%27M%5C%27+then+%5C%27Mujer%5C%27+else+%5C%27No+establecido%5C%27+end++as+Sexo%2C+count%28c1.userid%29+as+Total%0D%0Afrom++%0D%0A%28SELECT+c.id+as+courseid%2C+eu.sexo%2C+u.id+as+userid%0D%0A+++++FROM+prefix_user+u+left+join++prefix_edicion_user+eu+on+%28u.id%3Deu.userid%29+left+join+prefix_edicion_centro+ect+on+ect.codigo%3Deu.cc+%2C%0D%0A+++++prefix_edicion_course+ec+LEFT+JOIN+prefix_course+AS+c+on+ec.courseid%3Dc.id%2C+prefix_role_assignments+AS+ra+INNER+JOIN+prefix_context+AS+context+ON+ra.contextid%3Dcontext.id+++%0D%0A+++++where+context.contextlevel+%3D+50+AND+ra.roleid%3D5+AND+u.id%3Dra.userid+AND+context.instanceid%3Dc.id%0D%0A++%25%25FILTER_EDITIONS%3Aec.edicionid%25%25%0D%0A++%25%25FILTER_CATEGORIES%3Ac.category%25%25%0D%0A++%25%25FILTER_COURSES%3Ac.id%25%25%0D%0A++%25%25FILTER_CMAT%3Aget_ca%28eu.cc%29%25%25%0D%0A++%25%25FILTER_DGENERICA%3Aect.dgenerica%25%25%0D%0A++%25%25FILTER_TIPOCOL%3Aect.tipo%25%25%0D%0A++%25%25FILTER_CCDOCENTE%3Aeu.codcuerpodocente%25%25%0D%0A++%25%25FILTER_SEXO%3Aeu.sexo%25%25%0D%0A+++++%29+as+c1%0D%0Aleft+join+%0D%0A%28SELECT+courseid%2C+finalgrade%2C+userid++FROM+prefix_grade_grades+gg%2C+prefix_grade_items+gi+where+gg.itemid%3Dgi.id+and+itemname+like+%5C%27certif%25%5C%27%29+ccert+%0D%0Aon+%28c1.courseid+%3Dccert.courseid+and+c1.userid%3Dccert.userid%29+%0D%0AWhere%0D%0Atrue%0D%0A%25%25FILTER_CERTIFICADO%3Afinalgrade%25%25%0D%0Agroup+by+Sexo%0D%0Aorder+by++Sexo";s:12:"submitbutton";s:15:"Guardar+cambios";}}s:5:"calcs";a:1:{s:8:"elements";a:1:{i:0;a:5:{s:2:"id";s:15:"k2fRpdAPoobV3SE";s:8:"formdata";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"8388608";s:6:"column";s:1:"1";s:12:"submitbutton";s:7:"Agregar";}s:10:"pluginname";s:3:"sum";s:14:"pluginfullname";s:4:"Suma";s:7:"summary";s:5:"Total";}}}}';
		$reports['Report004']->export='ods,xls,pdf,';
		$reports['Report004']->ownerid=$USER->id;
		$reports['Report004']->courseid=SITEID;

		##Informacion por tipo de centro
		$reports['Report005']=new stdClass();
		$reports['Report005']->name =get_string('Report005', 'mgm');
		$reports['Report005']->summary =get_string('Report005_summary', 'mgm');
		$reports['Report005']->type ='sql';
		$reports['Report005']->jsordering= 1;
		$reports['Report005']->visible= 1;
		$reports['Report005']->components='a:3:{s:7:"filters";a:1:{s:8:"elements";a:9:{i:0;a:5:{s:2:"id";s:15:"X2MKTWAJCx81qot";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:8:"editions";s:14:"pluginfullname";s:9:"Ediciones";s:7:"summary";s:97:"Este+filtro+muestra+una+lista+de+ediciones.+Solo+se+puede+seleccionar+una+edicion+al+mismo+tiempo";}i:1;a:5:{s:2:"id";s:15:"152vl0oVP6UCZt6";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:10:"categories";s:14:"pluginfullname";s:25:"Filtro+de+categor%C3%ADas";s:7:"summary";s:31:"Para+filtrar+por+categor%C3%ADa";}i:2;a:5:{s:2:"id";s:15:"YdbsMGrbI6QLgbA";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:7:"courses";s:14:"pluginfullname";s:6:"Cursos";s:7:"summary";s:87:"Este+filtro+muestra+una+lista+de+cursos.+S%C3%B3lo+un+curso+puede+seleccionado+a+la+vez";}i:3;a:5:{s:2:"id";s:15:"TbnFT4b7TGP2d8M";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:4:"cmat";s:14:"pluginfullname";s:23:"Comunidad+Aut%C3%B3noma";s:7:"summary";s:104:"Filtro+de+Comunidad+Aut%C3%B3noma.+S%C3%B3lo+se+puede+seleccionar+una+Comunidad+autonoma+al+mismo+tiempo";}i:4;a:5:{s:2:"id";s:15:"kKVhMyJh42uxgxj";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:9:"dgenerica";s:14:"pluginfullname";s:31:"Denominaci%C3%B3n+Gen%C3%A9rica";s:7:"summary";s:112:"Filtro+de+Denominaci%C3%B3n+Ger%C3%A9rica+del+Centro.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:5;a:5:{s:2:"id";s:15:"yPzF0l3DeuyBTar";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:7:"tipocol";s:14:"pluginfullname";s:15:"Tipo+de+Colegio";s:7:"summary";s:127:"Filtro+de+tipo+de+centro+%28P%C3%BAblico%2C+Concertado+y+Privado%29.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:6;a:5:{s:2:"id";s:15:"rfmo4Rrkt8KUdqd";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:9:"ccdocente";s:14:"pluginfullname";s:14:"Cuerpo+Docente";s:7:"summary";s:92:"Filtro+de+tipo+de+Cuerpo+Docente.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:7;a:5:{s:2:"id";s:15:"a7e7I4aHNt82dTD";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:4:"sexo";s:14:"pluginfullname";s:4:"Sexo";s:7:"summary";s:46:"Seleccion+entre+Hombre+%28H%29+y+Mujer+%28M%29";}i:8;a:5:{s:2:"id";s:15:"LKHtMSbpb0ZWlaE";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:11:"certificado";s:14:"pluginfullname";s:11:"Certificado";s:7:"summary";s:56:"Indica+si+se+ha+superado+la+tarea+de+Certificaci%C3%B3n.";}}}s:9:"customsql";a:1:{s:6:"config";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"8388608";s:8:"querysql";s:1532:"select+case+when+c1.tipo%3D0+then+%5C%27Publico%5C%27+when+c1.tipo%3D1+then+%5C%27Concertado%5C%27+when+c1.tipo%3D2+then+%5C%27Privado%5C%27+else+%5C%27Desconocido%5C%27+end+as+Tipo%2C++count%28c1.userid%29+as+Total%0D%0Afrom++%0D%0A%28SELECT+c.id+as+courseid%2C+ect.tipo+as+tipo%2C+u.id+as+userid%0D%0A+++++FROM+prefix_user+u+left+join++prefix_edicion_user+eu+on+%28u.id%3Deu.userid%29+left+join+prefix_edicion_centro+ect+on+ect.codigo%3Deu.cc+%2C%0D%0A+++++prefix_edicion_course+ec+LEFT+JOIN+prefix_course+AS+c+on+ec.courseid%3Dc.id%2C+prefix_role_assignments+AS+ra+INNER+JOIN+prefix_context+AS+context+ON+ra.contextid%3Dcontext.id+++%0D%0A+++++where+context.contextlevel+%3D+50+AND+ra.roleid%3D5+AND+u.id%3Dra.userid+AND+context.instanceid%3Dc.id%0D%0A++%25%25FILTER_EDITIONS%3Aec.edicionid%25%25%0D%0A++%25%25FILTER_CATEGORIES%3Ac.category%25%25%0D%0A++%25%25FILTER_COURSES%3Ac.id%25%25%0D%0A++%25%25FILTER_CMAT%3Aget_ca%28eu.cc%29%25%25%0D%0A++%25%25FILTER_DGENERICA%3Aect.dgenerica%25%25%0D%0A++%25%25FILTER_TIPOCOL%3Aect.tipo%25%25%0D%0A++%25%25FILTER_CCDOCENTE%3Aeu.codcuerpodocente%25%25%0D%0A++%25%25FILTER_SEXO%3Aeu.sexo%25%25%0D%0A+++++%29+as+c1%0D%0Aleft+join+%0D%0A%28SELECT+courseid%2C+finalgrade%2C+userid++FROM+prefix_grade_grades+gg%2C+prefix_grade_items+gi+where+gg.itemid%3Dgi.id+and+itemname+like+%5C%27certif%25%5C%27%29+ccert+%0D%0Aon+%28c1.courseid+%3Dccert.courseid+and+c1.userid%3Dccert.userid%29+%0D%0AWhere%0D%0Atrue%0D%0A%25%25FILTER_CERTIFICADO%3Afinalgrade%25%25%0D%0Agroup+by+Tipo%0D%0Aorder+by++Tipo";s:12:"submitbutton";s:15:"Guardar+cambios";}}s:5:"calcs";a:1:{s:8:"elements";a:1:{i:0;a:5:{s:2:"id";s:15:"k2fRpdAPoobV3SE";s:8:"formdata";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"8388608";s:6:"column";s:1:"1";s:12:"submitbutton";s:7:"Agregar";}s:10:"pluginname";s:3:"sum";s:14:"pluginfullname";s:4:"Suma";s:7:"summary";s:5:"Total";}}}}';
		$reports['Report005']->export='ods,xls,pdf,';
		$reports['Report005']->ownerid=$USER->id;
		$reports['Report005']->courseid=SITEID;

		##Información por denominación generica del centro
		$reports['Report006']=new stdClass();
		$reports['Report006']->name =get_string('Report006', 'mgm');
		$reports['Report006']->summary =get_string('Report006_summary', 'mgm');
		$reports['Report006']->type ='sql';
		$reports['Report006']->jsordering= 1;
		$reports['Report006']->visible= 1;
		$reports['Report006']->components='a:3:{s:7:"filters";a:1:{s:8:"elements";a:9:{i:0;a:5:{s:2:"id";s:15:"X2MKTWAJCx81qot";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:8:"editions";s:14:"pluginfullname";s:9:"Ediciones";s:7:"summary";s:97:"Este+filtro+muestra+una+lista+de+ediciones.+Solo+se+puede+seleccionar+una+edicion+al+mismo+tiempo";}i:1;a:5:{s:2:"id";s:15:"152vl0oVP6UCZt6";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:10:"categories";s:14:"pluginfullname";s:25:"Filtro+de+categor%C3%ADas";s:7:"summary";s:31:"Para+filtrar+por+categor%C3%ADa";}i:2;a:5:{s:2:"id";s:15:"YdbsMGrbI6QLgbA";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:7:"courses";s:14:"pluginfullname";s:6:"Cursos";s:7:"summary";s:87:"Este+filtro+muestra+una+lista+de+cursos.+S%C3%B3lo+un+curso+puede+seleccionado+a+la+vez";}i:3;a:5:{s:2:"id";s:15:"TbnFT4b7TGP2d8M";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:4:"cmat";s:14:"pluginfullname";s:23:"Comunidad+Aut%C3%B3noma";s:7:"summary";s:104:"Filtro+de+Comunidad+Aut%C3%B3noma.+S%C3%B3lo+se+puede+seleccionar+una+Comunidad+autonoma+al+mismo+tiempo";}i:4;a:5:{s:2:"id";s:15:"kKVhMyJh42uxgxj";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:9:"dgenerica";s:14:"pluginfullname";s:31:"Denominaci%C3%B3n+Gen%C3%A9rica";s:7:"summary";s:112:"Filtro+de+Denominaci%C3%B3n+Ger%C3%A9rica+del+Centro.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:5;a:5:{s:2:"id";s:15:"yPzF0l3DeuyBTar";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:7:"tipocol";s:14:"pluginfullname";s:15:"Tipo+de+Colegio";s:7:"summary";s:127:"Filtro+de+tipo+de+centro+%28P%C3%BAblico%2C+Concertado+y+Privado%29.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:6;a:5:{s:2:"id";s:15:"rfmo4Rrkt8KUdqd";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:9:"ccdocente";s:14:"pluginfullname";s:14:"Cuerpo+Docente";s:7:"summary";s:92:"Filtro+de+tipo+de+Cuerpo+Docente.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:7;a:5:{s:2:"id";s:15:"a7e7I4aHNt82dTD";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:4:"sexo";s:14:"pluginfullname";s:4:"Sexo";s:7:"summary";s:46:"Seleccion+entre+Hombre+%28H%29+y+Mujer+%28M%29";}i:8;a:5:{s:2:"id";s:15:"LKHtMSbpb0ZWlaE";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:11:"certificado";s:14:"pluginfullname";s:11:"Certificado";s:7:"summary";s:56:"Indica+si+se+ha+superado+la+tarea+de+Certificaci%C3%B3n.";}}}s:9:"customsql";a:1:{s:6:"config";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"8388608";s:8:"querysql";s:1399:"select+c1.Denominacion%2C++count%28c1.userid%29+as+Total%0D%0Afrom++%0D%0A%28SELECT+c.id+as+courseid%2C+ect.dgenerica+Denominacion%2C+u.id+as+userid%0D%0A+++++FROM+prefix_user+u+left+join++prefix_edicion_user+eu+on+%28u.id%3Deu.userid%29+left+join+prefix_edicion_centro+ect+on+ect.codigo%3Deu.cc+%2C%0D%0A+++++prefix_edicion_course+ec+LEFT+JOIN+prefix_course+AS+c+on+ec.courseid%3Dc.id%2C+prefix_role_assignments+AS+ra+INNER+JOIN+prefix_context+AS+context+ON+ra.contextid%3Dcontext.id+++%0D%0A+++++where+context.contextlevel+%3D+50+AND+ra.roleid%3D5+AND+u.id%3Dra.userid+AND+context.instanceid%3Dc.id%0D%0A++%25%25FILTER_EDITIONS%3Aec.edicionid%25%25%0D%0A++%25%25FILTER_CATEGORIES%3Ac.category%25%25%0D%0A++%25%25FILTER_COURSES%3Ac.id%25%25%0D%0A++%25%25FILTER_CMAT%3Aget_ca%28eu.cc%29%25%25%0D%0A++%25%25FILTER_DGENERICA%3Aect.dgenerica%25%25%0D%0A++%25%25FILTER_TIPOCOL%3Aect.tipo%25%25%0D%0A++%25%25FILTER_CCDOCENTE%3Aeu.codcuerpodocente%25%25%0D%0A++%25%25FILTER_SEXO%3Aeu.sexo%25%25%0D%0A+++++%29+as+c1%0D%0Aleft+join+%0D%0A%28SELECT+courseid%2C+finalgrade%2C+userid++FROM+prefix_grade_grades+gg%2C+prefix_grade_items+gi+where+gg.itemid%3Dgi.id+and+itemname+like+%5C%27certif%25%5C%27%29+ccert+%0D%0Aon+%28c1.courseid+%3Dccert.courseid+and+c1.userid%3Dccert.userid%29+%0D%0AWhere%0D%0Atrue%0D%0A%25%25FILTER_CERTIFICADO%3Afinalgrade%25%25%0D%0Agroup+by+Denominacion%0D%0Aorder+by++Denominacion";s:12:"submitbutton";s:15:"Guardar+cambios";}}s:5:"calcs";a:1:{s:8:"elements";a:1:{i:0;a:5:{s:2:"id";s:15:"k2fRpdAPoobV3SE";s:8:"formdata";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"8388608";s:6:"column";s:1:"1";s:12:"submitbutton";s:7:"Agregar";}s:10:"pluginname";s:3:"sum";s:14:"pluginfullname";s:4:"Suma";s:7:"summary";s:5:"Total";}}}}';
		$reports['Report006']->export='ods,xls,pdf';
		$reports['Report006']->ownerid=$USER->id;
		$reports['Report006']->courseid=SITEID;

		##Información por Cuerpo Docente
		$reports['Report007']=new stdClass();
		$reports['Report007']->name =get_string('Report007', 'mgm');
		$reports['Report007']->summary =get_string('Report007_summary', 'mgm');
		$reports['Report007']->type ='sql';
		$reports['Report007']->jsordering= 1;
		$reports['Report007']->visible= 1;
		$reports['Report007']->components='a:3:{s:7:"filters";a:1:{s:8:"elements";a:9:{i:0;a:5:{s:2:"id";s:15:"X2MKTWAJCx81qot";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:8:"editions";s:14:"pluginfullname";s:9:"Ediciones";s:7:"summary";s:97:"Este+filtro+muestra+una+lista+de+ediciones.+Solo+se+puede+seleccionar+una+edicion+al+mismo+tiempo";}i:1;a:5:{s:2:"id";s:15:"152vl0oVP6UCZt6";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:10:"categories";s:14:"pluginfullname";s:25:"Filtro+de+categor%C3%ADas";s:7:"summary";s:31:"Para+filtrar+por+categor%C3%ADa";}i:2;a:5:{s:2:"id";s:15:"YdbsMGrbI6QLgbA";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:7:"courses";s:14:"pluginfullname";s:6:"Cursos";s:7:"summary";s:87:"Este+filtro+muestra+una+lista+de+cursos.+S%C3%B3lo+un+curso+puede+seleccionado+a+la+vez";}i:3;a:5:{s:2:"id";s:15:"TbnFT4b7TGP2d8M";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:4:"cmat";s:14:"pluginfullname";s:23:"Comunidad+Aut%C3%B3noma";s:7:"summary";s:104:"Filtro+de+Comunidad+Aut%C3%B3noma.+S%C3%B3lo+se+puede+seleccionar+una+Comunidad+autonoma+al+mismo+tiempo";}i:4;a:5:{s:2:"id";s:15:"kKVhMyJh42uxgxj";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:9:"dgenerica";s:14:"pluginfullname";s:31:"Denominaci%C3%B3n+Gen%C3%A9rica";s:7:"summary";s:112:"Filtro+de+Denominaci%C3%B3n+Ger%C3%A9rica+del+Centro.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:5;a:5:{s:2:"id";s:15:"yPzF0l3DeuyBTar";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:7:"tipocol";s:14:"pluginfullname";s:15:"Tipo+de+Colegio";s:7:"summary";s:127:"Filtro+de+tipo+de+centro+%28P%C3%BAblico%2C+Concertado+y+Privado%29.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:6;a:5:{s:2:"id";s:15:"rfmo4Rrkt8KUdqd";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:9:"ccdocente";s:14:"pluginfullname";s:14:"Cuerpo+Docente";s:7:"summary";s:92:"Filtro+de+tipo+de+Cuerpo+Docente.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:7;a:5:{s:2:"id";s:15:"a7e7I4aHNt82dTD";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:4:"sexo";s:14:"pluginfullname";s:4:"Sexo";s:7:"summary";s:46:"Seleccion+entre+Hombre+%28H%29+y+Mujer+%28M%29";}i:8;a:5:{s:2:"id";s:15:"LKHtMSbpb0ZWlaE";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:11:"certificado";s:14:"pluginfullname";s:11:"Certificado";s:7:"summary";s:56:"Indica+si+se+ha+superado+la+tarea+de+Certificaci%C3%B3n.";}}}s:9:"customsql";a:1:{s:6:"config";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"8388608";s:8:"querysql";s:1453:"select+c1.codcuerpodocente+Codigo%2C+get_cd%28c1.codcuerpodocente%29+Cuerpo%2C++count%28c1.userid%29+as+Total%0D%0Afrom++%0D%0A%28SELECT+c.id+as+courseid%2C+eu.codcuerpodocente%2C+u.id+as+userid%0D%0A+++++FROM+prefix_user+u+left+join++prefix_edicion_user+eu+on+%28u.id%3Deu.userid%29+left+join+prefix_edicion_centro+ect+on+ect.codigo%3Deu.cc+%2C%0D%0A+++++prefix_edicion_course+ec+LEFT+JOIN+prefix_course+AS+c+on+ec.courseid%3Dc.id%2C+prefix_role_assignments+AS+ra+INNER+JOIN+prefix_context+AS+context+ON+ra.contextid%3Dcontext.id+++%0D%0A+++++where+context.contextlevel+%3D+50+AND+ra.roleid%3D5+AND+u.id%3Dra.userid+AND+context.instanceid%3Dc.id%0D%0A++%25%25FILTER_EDITIONS%3Aec.edicionid%25%25%0D%0A++%25%25FILTER_CATEGORIES%3Ac.category%25%25%0D%0A++%25%25FILTER_COURSES%3Ac.id%25%25%0D%0A++%25%25FILTER_CMAT%3Aget_ca%28eu.cc%29%25%25%0D%0A++%25%25FILTER_DGENERICA%3Aect.dgenerica%25%25%0D%0A++%25%25FILTER_TIPOCOL%3Aect.tipo%25%25%0D%0A++%25%25FILTER_CCDOCENTE%3Aeu.codcuerpodocente%25%25%0D%0A++%25%25FILTER_SEXO%3Aeu.sexo%25%25%0D%0A+++++%29+as+c1%0D%0Aleft+join+%0D%0A%28SELECT+courseid%2C+finalgrade%2C+userid++FROM+prefix_grade_grades+gg%2C+prefix_grade_items+gi+where+gg.itemid%3Dgi.id+and+itemname+like+%5C%27certif%25%5C%27%29+ccert+%0D%0Aon+%28c1.courseid+%3Dccert.courseid+and+c1.userid%3Dccert.userid%29+%0D%0AWhere%0D%0Atrue%0D%0A%25%25FILTER_CERTIFICADO%3Afinalgrade%25%25%0D%0Agroup+by+Codigo%2C+Cuerpo%0D%0Aorder+by++Codigo%2C+Cuerpo";s:12:"submitbutton";s:15:"Guardar+cambios";}}s:5:"calcs";a:1:{s:8:"elements";a:1:{i:0;a:5:{s:2:"id";s:15:"k2fRpdAPoobV3SE";s:8:"formdata";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"8388608";s:6:"column";s:1:"2";s:12:"submitbutton";s:7:"Agregar";}s:10:"pluginname";s:3:"sum";s:14:"pluginfullname";s:4:"Suma";s:7:"summary";s:5:"Total";}}}}';
		$reports['Report007']->export='ods,xls,pdf';
		$reports['Report007']->ownerid=$USER->id;
		$reports['Report007']->courseid=SITEID;

		##Información por Cursos
		$reports['Report008']=new stdClass();
		$reports['Report008']->name =get_string('Report008', 'mgm');
		$reports['Report008']->summary =get_string('Report008_summary', 'mgm');
		$reports['Report008']->type ='sql';
		$reports['Report008']->jsordering= 1;
		$reports['Report008']->visible= 1;
		$reports['Report008']->components='a:3:{s:7:"filters";a:1:{s:8:"elements";a:9:{i:0;a:5:{s:2:"id";s:15:"X2MKTWAJCx81qot";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:8:"editions";s:14:"pluginfullname";s:9:"Ediciones";s:7:"summary";s:97:"Este+filtro+muestra+una+lista+de+ediciones.+Solo+se+puede+seleccionar+una+edicion+al+mismo+tiempo";}i:1;a:5:{s:2:"id";s:15:"152vl0oVP6UCZt6";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:10:"categories";s:14:"pluginfullname";s:25:"Filtro+de+categor%C3%ADas";s:7:"summary";s:31:"Para+filtrar+por+categor%C3%ADa";}i:2;a:5:{s:2:"id";s:15:"YdbsMGrbI6QLgbA";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:7:"courses";s:14:"pluginfullname";s:6:"Cursos";s:7:"summary";s:87:"Este+filtro+muestra+una+lista+de+cursos.+S%C3%B3lo+un+curso+puede+seleccionado+a+la+vez";}i:3;a:5:{s:2:"id";s:15:"TbnFT4b7TGP2d8M";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:4:"cmat";s:14:"pluginfullname";s:23:"Comunidad+Aut%C3%B3noma";s:7:"summary";s:104:"Filtro+de+Comunidad+Aut%C3%B3noma.+S%C3%B3lo+se+puede+seleccionar+una+Comunidad+autonoma+al+mismo+tiempo";}i:4;a:5:{s:2:"id";s:15:"kKVhMyJh42uxgxj";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:9:"dgenerica";s:14:"pluginfullname";s:31:"Denominaci%C3%B3n+Gen%C3%A9rica";s:7:"summary";s:112:"Filtro+de+Denominaci%C3%B3n+Ger%C3%A9rica+del+Centro.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:5;a:5:{s:2:"id";s:15:"yPzF0l3DeuyBTar";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:7:"tipocol";s:14:"pluginfullname";s:15:"Tipo+de+Colegio";s:7:"summary";s:127:"Filtro+de+tipo+de+centro+%28P%C3%BAblico%2C+Concertado+y+Privado%29.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:6;a:5:{s:2:"id";s:15:"rfmo4Rrkt8KUdqd";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:9:"ccdocente";s:14:"pluginfullname";s:14:"Cuerpo+Docente";s:7:"summary";s:92:"Filtro+de+tipo+de+Cuerpo+Docente.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:7;a:5:{s:2:"id";s:15:"a7e7I4aHNt82dTD";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:4:"sexo";s:14:"pluginfullname";s:4:"Sexo";s:7:"summary";s:46:"Seleccion+entre+Hombre+%28H%29+y+Mujer+%28M%29";}i:8;a:5:{s:2:"id";s:15:"LKHtMSbpb0ZWlaE";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:11:"certificado";s:14:"pluginfullname";s:11:"Certificado";s:7:"summary";s:56:"Indica+si+se+ha+superado+la+tarea+de+Certificaci%C3%B3n.";}}}s:9:"customsql";a:1:{s:6:"config";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"8388608";s:8:"querysql";s:1370:"select+c1.Curso%2C+count%28c1.userid%29+as+Total%0D%0Afrom++%0D%0A%28SELECT+c.id+as+courseid%2C+c.fullname+as+Curso%2C+u.id+as+userid%0D%0A+++++FROM+prefix_user+u+left+join++prefix_edicion_user+eu+on+%28u.id%3Deu.userid%29+left+join+prefix_edicion_centro+ect+on+ect.codigo%3Deu.cc+%2C%0D%0A+++++prefix_edicion_course+ec+LEFT+JOIN+prefix_course+AS+c+on+ec.courseid%3Dc.id%2C+prefix_role_assignments+AS+ra+INNER+JOIN+prefix_context+AS+context+ON+ra.contextid%3Dcontext.id+++%0D%0A+++++where+context.contextlevel+%3D+50+AND+ra.roleid%3D5+AND+u.id%3Dra.userid+AND+context.instanceid%3Dc.id%0D%0A++%25%25FILTER_EDITIONS%3Aec.edicionid%25%25%0D%0A++%25%25FILTER_CATEGORIES%3Ac.category%25%25%0D%0A++%25%25FILTER_COURSES%3Ac.id%25%25%0D%0A++%25%25FILTER_CMAT%3Aget_ca%28eu.cc%29%25%25%0D%0A++%25%25FILTER_DGENERICA%3Aect.dgenerica%25%25%0D%0A++%25%25FILTER_TIPOCOL%3Aect.tipo%25%25%0D%0A++%25%25FILTER_CCDOCENTE%3Aeu.codcuerpodocente%25%25%0D%0A++%25%25FILTER_SEXO%3Aeu.sexo%25%25%0D%0A+++++%29+as+c1%0D%0Aleft+join+%0D%0A%28SELECT+courseid%2C+finalgrade%2C+userid++FROM+prefix_grade_grades+gg%2C+prefix_grade_items+gi+where+gg.itemid%3Dgi.id+and+itemname+like+%5C%27certif%25%5C%27%29+ccert+%0D%0Aon+%28c1.courseid+%3Dccert.courseid+and+c1.userid%3Dccert.userid%29+%0D%0AWhere%0D%0Atrue%0D%0A%25%25FILTER_CERTIFICADO%3Afinalgrade%25%25%0D%0Agroup+by+Curso%0D%0Aorder+by++Curso";s:12:"submitbutton";s:15:"Guardar+cambios";}}s:5:"calcs";a:1:{s:8:"elements";a:1:{i:0;a:5:{s:2:"id";s:15:"k2fRpdAPoobV3SE";s:8:"formdata";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"8388608";s:6:"column";s:1:"1";s:12:"submitbutton";s:7:"Agregar";}s:10:"pluginname";s:3:"sum";s:14:"pluginfullname";s:4:"Suma";s:7:"summary";s:5:"Total";}}}}';
		$reports['Report008']->export='ods,xls,pdf';
		$reports['Report008']->ownerid=$USER->id;
		$reports['Report008']->courseid=SITEID;

		##Información por Cursos y Sexo
		$reports['Report009']=new stdClass();
		$reports['Report009']->name =get_string('Report009', 'mgm');
		$reports['Report009']->summary =get_string('Report009_summary', 'mgm');
		$reports['Report009']->type ='sql';
		$reports['Report009']->jsordering= 1;
		$reports['Report009']->visible= 1;
		$reports['Report009']->components='a:3:{s:7:"filters";a:1:{s:8:"elements";a:9:{i:0;a:5:{s:2:"id";s:15:"X2MKTWAJCx81qot";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:8:"editions";s:14:"pluginfullname";s:9:"Ediciones";s:7:"summary";s:97:"Este+filtro+muestra+una+lista+de+ediciones.+Solo+se+puede+seleccionar+una+edicion+al+mismo+tiempo";}i:1;a:5:{s:2:"id";s:15:"152vl0oVP6UCZt6";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:10:"categories";s:14:"pluginfullname";s:25:"Filtro+de+categor%C3%ADas";s:7:"summary";s:31:"Para+filtrar+por+categor%C3%ADa";}i:2;a:5:{s:2:"id";s:15:"YdbsMGrbI6QLgbA";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:7:"courses";s:14:"pluginfullname";s:6:"Cursos";s:7:"summary";s:87:"Este+filtro+muestra+una+lista+de+cursos.+S%C3%B3lo+un+curso+puede+seleccionado+a+la+vez";}i:3;a:5:{s:2:"id";s:15:"TbnFT4b7TGP2d8M";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:4:"cmat";s:14:"pluginfullname";s:23:"Comunidad+Aut%C3%B3noma";s:7:"summary";s:104:"Filtro+de+Comunidad+Aut%C3%B3noma.+S%C3%B3lo+se+puede+seleccionar+una+Comunidad+autonoma+al+mismo+tiempo";}i:4;a:5:{s:2:"id";s:15:"kKVhMyJh42uxgxj";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:9:"dgenerica";s:14:"pluginfullname";s:31:"Denominaci%C3%B3n+Gen%C3%A9rica";s:7:"summary";s:112:"Filtro+de+Denominaci%C3%B3n+Ger%C3%A9rica+del+Centro.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:5;a:5:{s:2:"id";s:15:"yPzF0l3DeuyBTar";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:7:"tipocol";s:14:"pluginfullname";s:15:"Tipo+de+Colegio";s:7:"summary";s:127:"Filtro+de+tipo+de+centro+%28P%C3%BAblico%2C+Concertado+y+Privado%29.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:6;a:5:{s:2:"id";s:15:"rfmo4Rrkt8KUdqd";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:9:"ccdocente";s:14:"pluginfullname";s:14:"Cuerpo+Docente";s:7:"summary";s:92:"Filtro+de+tipo+de+Cuerpo+Docente.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:7;a:5:{s:2:"id";s:15:"a7e7I4aHNt82dTD";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:4:"sexo";s:14:"pluginfullname";s:4:"Sexo";s:7:"summary";s:46:"Seleccion+entre+Hombre+%28H%29+y+Mujer+%28M%29";}i:8;a:5:{s:2:"id";s:15:"LKHtMSbpb0ZWlaE";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:11:"certificado";s:14:"pluginfullname";s:11:"Certificado";s:7:"summary";s:56:"Indica+si+se+ha+superado+la+tarea+de+Certificaci%C3%B3n.";}}}s:9:"customsql";a:1:{s:6:"config";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"8388608";s:8:"querysql";s:1413:"select+c1.Curso%2C+c1.sexo+Sexo%2C+count%28c1.userid%29+as+Total%0D%0Afrom++%0D%0A%28SELECT+c.id+as+courseid%2C+c.fullname+as+Curso%2C+eu.sexo%2C+u.id+as+userid%0D%0A+++++FROM+prefix_user+u+left+join++prefix_edicion_user+eu+on+%28u.id%3Deu.userid%29+left+join+prefix_edicion_centro+ect+on+ect.codigo%3Deu.cc+%2C%0D%0A+++++prefix_edicion_course+ec+LEFT+JOIN+prefix_course+AS+c+on+ec.courseid%3Dc.id%2C+prefix_role_assignments+AS+ra+INNER+JOIN+prefix_context+AS+context+ON+ra.contextid%3Dcontext.id+++%0D%0A+++++where+context.contextlevel+%3D+50+AND+ra.roleid%3D5+AND+u.id%3Dra.userid+AND+context.instanceid%3Dc.id%0D%0A++%25%25FILTER_EDITIONS%3Aec.edicionid%25%25%0D%0A++%25%25FILTER_CATEGORIES%3Ac.category%25%25%0D%0A++%25%25FILTER_COURSES%3Ac.id%25%25%0D%0A++%25%25FILTER_CMAT%3Aget_ca%28eu.cc%29%25%25%0D%0A++%25%25FILTER_DGENERICA%3Aect.dgenerica%25%25%0D%0A++%25%25FILTER_TIPOCOL%3Aect.tipo%25%25%0D%0A++%25%25FILTER_CCDOCENTE%3Aeu.codcuerpodocente%25%25%0D%0A++%25%25FILTER_SEXO%3Aeu.sexo%25%25%0D%0A+++++%29+as+c1%0D%0Aleft+join+%0D%0A%28SELECT+courseid%2C+finalgrade%2C+userid++FROM+prefix_grade_grades+gg%2C+prefix_grade_items+gi+where+gg.itemid%3Dgi.id+and+itemname+like+%5C%27certif%25%5C%27%29+ccert+%0D%0Aon+%28c1.courseid+%3Dccert.courseid+and+c1.userid%3Dccert.userid%29+%0D%0AWhere%0D%0Atrue%0D%0A%25%25FILTER_CERTIFICADO%3Afinalgrade%25%25%0D%0Agroup+by+Curso%2C+Sexo%0D%0Aorder+by++Curso%2C+Sexo";s:12:"submitbutton";s:15:"Guardar+cambios";}}s:5:"calcs";a:1:{s:8:"elements";a:1:{i:0;a:5:{s:2:"id";s:15:"k2fRpdAPoobV3SE";s:8:"formdata";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"8388608";s:6:"column";s:1:"2";s:12:"submitbutton";s:7:"Agregar";}s:10:"pluginname";s:3:"sum";s:14:"pluginfullname";s:4:"Suma";s:7:"summary";s:5:"Total";}}}}';
		$reports['Report009']->export='ods,xls,pdf';
		$reports['Report009']->ownerid=$USER->id;
		$reports['Report009']->courseid=SITEID;

		##Información por Denominación generica y Comunidad autonoma
		$reports['Report010']=new stdClass();
		$reports['Report010']->name =get_string('Report010', 'mgm');
		$reports['Report010']->summary =get_string('Report010_summary', 'mgm');
		$reports['Report010']->type ='sql';
		$reports['Report010']->jsordering= 1;
		$reports['Report010']->visible= 1;
		$reports['Report010']->components='a:3:{s:7:"filters";a:1:{s:8:"elements";a:9:{i:0;a:5:{s:2:"id";s:15:"X2MKTWAJCx81qot";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:8:"editions";s:14:"pluginfullname";s:9:"Ediciones";s:7:"summary";s:97:"Este+filtro+muestra+una+lista+de+ediciones.+Solo+se+puede+seleccionar+una+edicion+al+mismo+tiempo";}i:1;a:5:{s:2:"id";s:15:"152vl0oVP6UCZt6";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:10:"categories";s:14:"pluginfullname";s:25:"Filtro+de+categor%C3%ADas";s:7:"summary";s:31:"Para+filtrar+por+categor%C3%ADa";}i:2;a:5:{s:2:"id";s:15:"YdbsMGrbI6QLgbA";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:7:"courses";s:14:"pluginfullname";s:6:"Cursos";s:7:"summary";s:87:"Este+filtro+muestra+una+lista+de+cursos.+S%C3%B3lo+un+curso+puede+seleccionado+a+la+vez";}i:3;a:5:{s:2:"id";s:15:"TbnFT4b7TGP2d8M";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:4:"cmat";s:14:"pluginfullname";s:23:"Comunidad+Aut%C3%B3noma";s:7:"summary";s:104:"Filtro+de+Comunidad+Aut%C3%B3noma.+S%C3%B3lo+se+puede+seleccionar+una+Comunidad+autonoma+al+mismo+tiempo";}i:4;a:5:{s:2:"id";s:15:"kKVhMyJh42uxgxj";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:9:"dgenerica";s:14:"pluginfullname";s:31:"Denominaci%C3%B3n+Gen%C3%A9rica";s:7:"summary";s:112:"Filtro+de+Denominaci%C3%B3n+Ger%C3%A9rica+del+Centro.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:5;a:5:{s:2:"id";s:15:"yPzF0l3DeuyBTar";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:7:"tipocol";s:14:"pluginfullname";s:15:"Tipo+de+Colegio";s:7:"summary";s:127:"Filtro+de+tipo+de+centro+%28P%C3%BAblico%2C+Concertado+y+Privado%29.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:6;a:5:{s:2:"id";s:15:"rfmo4Rrkt8KUdqd";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:9:"ccdocente";s:14:"pluginfullname";s:14:"Cuerpo+Docente";s:7:"summary";s:92:"Filtro+de+tipo+de+Cuerpo+Docente.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:7;a:5:{s:2:"id";s:15:"a7e7I4aHNt82dTD";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:4:"sexo";s:14:"pluginfullname";s:4:"Sexo";s:7:"summary";s:46:"Seleccion+entre+Hombre+%28H%29+y+Mujer+%28M%29";}i:8;a:5:{s:2:"id";s:15:"LKHtMSbpb0ZWlaE";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:11:"certificado";s:14:"pluginfullname";s:11:"Certificado";s:7:"summary";s:56:"Indica+si+se+ha+superado+la+tarea+de+Certificaci%C3%B3n.";}}}s:9:"customsql";a:1:{s:6:"config";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"8388608";s:8:"querysql";s:1461:"select+c1.dgenerica+Denominacion%2C+get_ca%28c1.cc%29+Comunidad%2C+count%28c1.userid%29+as+Total%0D%0Afrom++%0D%0A%28SELECT+c.id+as+courseid%2C+eu.cc%2C+ect.dgenerica%2C+u.id+as+userid%0D%0A+++++FROM+prefix_user+u+left+join++prefix_edicion_user+eu+on+%28u.id%3Deu.userid%29+left+join+prefix_edicion_centro+ect+on+ect.codigo%3Deu.cc+%2C%0D%0A+++++prefix_edicion_course+ec+LEFT+JOIN+prefix_course+AS+c+on+ec.courseid%3Dc.id%2C+prefix_role_assignments+AS+ra+INNER+JOIN+prefix_context+AS+context+ON+ra.contextid%3Dcontext.id+++%0D%0A+++++where+context.contextlevel+%3D+50+AND+ra.roleid%3D5+AND+u.id%3Dra.userid+AND+context.instanceid%3Dc.id%0D%0A++%25%25FILTER_EDITIONS%3Aec.edicionid%25%25%0D%0A++%25%25FILTER_CATEGORIES%3Ac.category%25%25%0D%0A++%25%25FILTER_COURSES%3Ac.id%25%25%0D%0A++%25%25FILTER_CMAT%3Aget_ca%28eu.cc%29%25%25%0D%0A++%25%25FILTER_DGENERICA%3Aect.dgenerica%25%25%0D%0A++%25%25FILTER_TIPOCOL%3Aect.tipo%25%25%0D%0A++%25%25FILTER_CCDOCENTE%3Aeu.codcuerpodocente%25%25%0D%0A++%25%25FILTER_SEXO%3Aeu.sexo%25%25%0D%0A+++++%29+as+c1%0D%0Aleft+join+%0D%0A%28SELECT+courseid%2C+finalgrade%2C+userid++FROM+prefix_grade_grades+gg%2C+prefix_grade_items+gi+where+gg.itemid%3Dgi.id+and+itemname+like+%5C%27certif%25%5C%27%29+ccert+%0D%0Aon+%28c1.courseid+%3Dccert.courseid+and+c1.userid%3Dccert.userid%29+%0D%0AWhere%0D%0Atrue%0D%0A%25%25FILTER_CERTIFICADO%3Afinalgrade%25%25%0D%0Agroup+by+Denominacion%2C+Comunidad%0D%0Aorder+by++Denominacion%2C+Comunidad";s:12:"submitbutton";s:15:"Guardar+cambios";}}s:5:"calcs";a:1:{s:8:"elements";a:1:{i:0;a:5:{s:2:"id";s:15:"k2fRpdAPoobV3SE";s:8:"formdata";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"8388608";s:6:"column";s:1:"2";s:12:"submitbutton";s:7:"Agregar";}s:10:"pluginname";s:3:"sum";s:14:"pluginfullname";s:4:"Suma";s:7:"summary";s:5:"Total";}}}}';
		$reports['Report010']->export='ods,xls,pdf';
		$reports['Report010']->ownerid=$USER->id;
		$reports['Report010']->courseid=SITEID;

		##Usuarios con DNI duplicados en el modulo MGM
		$reports['Report020']=new stdClass();
		$reports['Report020']->name =get_string('Report020', 'mgm');
		$reports['Report020']->summary =get_string('Report020_summary', 'mgm');
		$reports['Report020']->type ='sql';
		$reports['Report020']->jsordering= 1;
		$reports['Report020']->visible= 1;
		$reports['Report020']->components='a:1:{s:9:"customsql";a:1:{s:6:"config";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"2097152";s:8:"querysql";s:414:"select+eu.tipoid%2C+c1.dni%2C+c1.repeticiones%2C+eu.id%2C+eu.userid%2C+u.deleted%2C+u.firstname%2C+u.lastname+from+%0D%0A++%28select+dni%2C+count%28dni%29+as+repeticiones+from+prefix_edicion_user+where+dni+%21%3D+%5C%27%5C%27+group+by+dni+having+count%28dni%29%3E1%29+c1%2C%0D%0A++prefix_edicion_user+eu%2C%0D%0A++prefix_user+u%0D%0A++where+eu.dni+like+c1.dni+and+eu.userid%3Du.id%0D%0Aorder+by+c1.dni%2C+eu.tipoid";s:12:"submitbutton";s:15:"Guardar+cambios";}}}';
		$reports['Report020']->export='ods,xls';
		$reports['Report020']->ownerid=$USER->id;
		$reports['Report020']->courseid=SITEID;

		##Usuario eliminados y que tienen registro en mgm
		$reports['Report021']=new stdClass();
		$reports['Report021']->name =get_string('Report021', 'mgm');
		$reports['Report021']->summary =get_string('Report021_summary', 'mgm');
		$reports['Report021']->type ='sql';
		$reports['Report021']->jsordering= 1;
		$reports['Report021']->visible= 1;
		$reports['Report021']->components='a:1:{s:9:"customsql";a:1:{s:6:"config";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"8388608";s:8:"querysql";s:201:"select+firstname%2C+lastname%2C+idnumber%2C+dni%2C+userid%2C+deleted%2C+eu.id+as+euid+from+prefix_edicion_user+eu%2C+prefix_user+u+where++eu.userid%3Du.id+and+deleted%3D1+order+by+lastname%2C+firstname";s:12:"submitbutton";s:15:"Guardar+cambios";}}}';
		$reports['Report021']->export='ods,xls';
		$reports['Report021']->ownerid=$USER->id;
		$reports['Report021']->courseid=SITEID;

		##Historico de certificaciones
		$reports['Report030']=new stdClass();
		$reports['Report030']->name =get_string('Report030', 'mgm');
		$reports['Report030']->summary =get_string('Report030_summary', 'mgm');
		$reports['Report030']->type ='sql';
		$reports['Report030']->jsordering= 1;
		$reports['Report030']->visible= 1;
		$reports['Report030']->components='a:3:{s:9:"customsql";a:1:{s:6:"config";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"8388608";s:8:"querysql";s:488:"select+courseid+Curso%2C++u.lastname+as+Apellidos%2C+u.firstname+as+Nombre%2C+roleid+Rol%2C+numregistro+Registro%2Cconfirm+Confirmado%2C+numdocumento+%5C%22Numero+Documento%5C%22%2C+e.name+Edicion+from+prefix_edicion_cert_history+ch+left+join+prefix_edicion+e+on+ch.edicionid%3De.id%2C%0D%0Aprefix_user+u+%0D%0Awhere%0D%0Ach.userid%3Du.id%0D%0A%25%25FILTER_EDITIONS%3Ach.edicionid%25%25%0D%0A%25%25FILTER_NUMDOCUMENTO%3Anumdocumento%25%25%0D%0A%25%25FILTER_IDNUMBER%3Acourseid%25%25%0D%0A";s:12:"submitbutton";s:15:"Guardar+cambios";}}s:7:"filters";a:1:{s:8:"elements";a:3:{i:0;a:5:{s:2:"id";s:15:"BVYHeYW3nefY46I";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:8:"editions";s:14:"pluginfullname";s:9:"Ediciones";s:7:"summary";s:97:"Este+filtro+muestra+una+lista+de+ediciones.+Solo+se+puede+seleccionar+una+edicion+al+mismo+tiempo";}i:1;a:5:{s:2:"id";s:15:"QbOHfdZFDG0AaMS";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:8:"idnumber";s:14:"pluginfullname";s:21:"C%C3%B3digo+del+Curso";s:7:"summary";s:47:"Codigo+del+curso+%28N%C3%BAmero+id+del+Curso%29";}i:2;a:5:{s:2:"id";s:15:"FkELU0VpAABIQhL";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:12:"numdocumento";s:14:"pluginfullname";s:24:"N%C3%BAmero+de+documento";s:7:"summary";s:63:"Numero+de+documento%3A+DNI%2C+Pasaporte+o+Tarjeta+de+Residencia";}}}s:11:"permissions";a:1:{s:6:"config";O:6:"object":1:{s:13:"conditionexpr";s:0:"";}}}';
		$reports['Report030']->export='ods,xls';
		$reports['Report030']->ownerid=$USER->id;
		$reports['Report030']->courseid=SITEID;

		##Listado de centros
		$reports['Report031']=new stdClass();
		$reports['Report031']->name =get_string('Report031', 'mgm');
		$reports['Report031']->summary =get_string('Report031_summary', 'mgm');
		$reports['Report031']->type ='sql';
		$reports['Report031']->jsordering= 1;
		$reports['Report031']->visible= 1;
		$reports['Report002']->pagination=100;
		$reports['Report031']->components='a:2:{s:9:"customsql";a:1:{s:6:"config";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"8388608";s:8:"querysql";s:513:"select+codigo+Codigo%2C+pais+Pais%2C+get_ca%28codigo%29+Comunidad%2C+provincia+Provincia%2C+localidad+Localidad%2Ccp+CP%2C+tipo+Tipo%2C+dgenerica+Dgenerica%2C+despecifica+Despecifica%2C+naturaleza+Naturaleza%2C+direccion+%5C%22Direcci%C3%B3n%5C%22%2C+telefono+%5C%22Tel%C3%A9fono%5C%22+from+prefix_edicion_centro%0D%0Awhere+true%0D%0A++%25%25FILTER_CMAT%3Aget_ca%28codigo%29%25%25%0D%0A++%25%25FILTER_DGENERICA%3Adgenerica%25%25%0D%0A++%25%25FILTER_TIPOCOL%3Atipo%25%25%0D%0A++%25%25FILTER_CC%3Acodigo%25%25%0D%0A";s:12:"submitbutton";s:15:"Guardar+cambios";}}s:7:"filters";a:1:{s:8:"elements";a:4:{i:0;a:5:{s:2:"id";s:15:"zJHsxsRjEw4V8yr";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:4:"cmat";s:14:"pluginfullname";s:23:"Comunidad+Aut%C3%B3noma";s:7:"summary";s:104:"Filtro+de+Comunidad+Aut%C3%B3noma.+S%C3%B3lo+se+puede+seleccionar+una+Comunidad+autonoma+al+mismo+tiempo";}i:1;a:5:{s:2:"id";s:15:"zUSWrDN40NZKMt6";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:7:"tipocol";s:14:"pluginfullname";s:15:"Tipo+de+Colegio";s:7:"summary";s:127:"Filtro+de+tipo+de+centro+%28P%C3%BAblico%2C+Concertado+y+Privado%29.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:2;a:5:{s:2:"id";s:15:"oeGQI62s3AInJZk";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:9:"dgenerica";s:14:"pluginfullname";s:31:"Denominaci%C3%B3n+Gen%C3%A9rica";s:7:"summary";s:112:"Filtro+de+Denominaci%C3%B3n+Ger%C3%A9rica+del+Centro.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:3;a:5:{s:2:"id";s:15:"jgVklfiVI7EcRWH";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:2:"cc";s:14:"pluginfullname";s:21:"C%C3%B3digo+de+Centro";s:7:"summary";s:17:"Codigo+del+centro";}}}}';
		$reports['Report031']->export='ods,xls';
		$reports['Report031']->ownerid=$USER->id;
		$reports['Report031']->courseid=SITEID;

		##Descartes de matriculación
		$reports['Report032']=new stdClass();
		$reports['Report032']->name =get_string('Report032', 'mgm');
		$reports['Report032']->summary =get_string('Report032_summary', 'mgm');
		$reports['Report032']->type ='sql';
		$reports['Report032']->jsordering= 1;
		$reports['Report032']->visible= 1;
		$reports['Report002']->pagination=100;
		$reports['Report032']->components='a:3:{s:7:"filters";a:1:{s:8:"elements";a:8:{i:0;a:5:{s:2:"id";s:15:"X2MKTWAJCx81qot";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:8:"editions";s:14:"pluginfullname";s:9:"Ediciones";s:7:"summary";s:97:"Este+filtro+muestra+una+lista+de+ediciones.+Solo+se+puede+seleccionar+una+edicion+al+mismo+tiempo";}i:1;a:5:{s:2:"id";s:15:"152vl0oVP6UCZt6";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:10:"categories";s:14:"pluginfullname";s:25:"Filtro+de+categor%C3%ADas";s:7:"summary";s:31:"Para+filtrar+por+categor%C3%ADa";}i:2;a:5:{s:2:"id";s:15:"YdbsMGrbI6QLgbA";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:7:"courses";s:14:"pluginfullname";s:6:"Cursos";s:7:"summary";s:87:"Este+filtro+muestra+una+lista+de+cursos.+S%C3%B3lo+un+curso+puede+seleccionado+a+la+vez";}i:3;a:5:{s:2:"id";s:15:"TbnFT4b7TGP2d8M";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:4:"cmat";s:14:"pluginfullname";s:23:"Comunidad+Aut%C3%B3noma";s:7:"summary";s:104:"Filtro+de+Comunidad+Aut%C3%B3noma.+S%C3%B3lo+se+puede+seleccionar+una+Comunidad+autonoma+al+mismo+tiempo";}i:4;a:5:{s:2:"id";s:15:"kKVhMyJh42uxgxj";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:9:"dgenerica";s:14:"pluginfullname";s:31:"Denominaci%C3%B3n+Gen%C3%A9rica";s:7:"summary";s:112:"Filtro+de+Denominaci%C3%B3n+Ger%C3%A9rica+del+Centro.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:5;a:5:{s:2:"id";s:15:"yPzF0l3DeuyBTar";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:7:"tipocol";s:14:"pluginfullname";s:15:"Tipo+de+Colegio";s:7:"summary";s:127:"Filtro+de+tipo+de+centro+%28P%C3%BAblico%2C+Concertado+y+Privado%29.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:6;a:5:{s:2:"id";s:15:"rfmo4Rrkt8KUdqd";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:9:"ccdocente";s:14:"pluginfullname";s:14:"Cuerpo+Docente";s:7:"summary";s:92:"Filtro+de+tipo+de+Cuerpo+Docente.+S%C3%B3lo+se+puede+seleccionar+un+elemento+al+mismo+tiempo";}i:7;a:5:{s:2:"id";s:15:"a7e7I4aHNt82dTD";s:8:"formdata";O:6:"object":0:{}s:10:"pluginname";s:4:"sexo";s:14:"pluginfullname";s:4:"Sexo";s:7:"summary";s:46:"Seleccion+entre+Hombre+%28H%29+y+Mujer+%28M%29";}}}s:9:"customsql";a:1:{s:6:"config";O:6:"object":3:{s:13:"MAX_FILE_SIZE";s:7:"8388608";s:8:"querysql";s:1312:"%0D%0ASELECT+c.fullname+Curso%2C+c.idnumber+%5C%27C%C3%B3digo+Curso%5C%27%2C++u.lastname+Apellidos%2Cu.firstname+Nombre%2C+eu.dni+DNI%2C+get_ca%28eu.cc%29+Comunidad%2C+eu.cc+%5C%27C%C3%B3digo+Centro%5C%27%2C+%0D%0Acase+when+ect.tipo%3D0+then+%5C%27P%C3%BAblico%5C%27+when+ect.tipo%3D1+then+%5C%27Concertado%5C%27+when+ect.tipo%3D2+then+%5C%27Privado%5C%27+end+%5C%27Tipo+Centro%5C%27%2C%0D%0Acase+when+ed.code%3D1+then+%5C%27Fuera+de+Cupo%5C%27+when+ed.code%3D2+then+%5C%27Centro+privado%5C%27+when+ed.code%3D3++then+%5C%27Ya+certificado%5C%27+when+ed.code%3D4+then+%5C%27Comunidad+Aut.%5C%27+end+%5C%27Motivo+Descarte%5C%27+%0D%0AFROM+prefix_edicion_descartes+ed+left+join+prefix_edicion_user+eu+on+%28ed.userid%3Deu.userid%29%2C%0D%0A+++++prefix_user+u%2C+prefix_edicion_centro+ect%2C+prefix_course+c+where+ed.userid%3Du.id+and++eu.cc%3Dect.codigo+and+c.id%3Ded.courseid%0D%0A%0D%0A%0D%0A++%25%25FILTER_EDITIONS%3Aed.edicionid%25%25%0D%0A++%25%25FILTER_CATEGORIES%3Ac.category%25%25%0D%0A++%25%25FILTER_COURSES%3Ac.id%25%25%0D%0A++%25%25FILTER_CMAT%3Aget_ca%28eu.cc%29%25%25%0D%0A++%25%25FILTER_DGENERICA%3Aect.dgenerica%25%25%0D%0A++%25%25FILTER_TIPOCOL%3Aect.tipo%25%25%0D%0A++%25%25FILTER_CCDOCENTE%3Aeu.codcuerpodocente%25%25%0D%0A++%25%25FILTER_SEXO%3Aeu.sexo%25%25%0D%0Aorder+by+Apellidos%2C+Nombre%0D%0A";s:12:"submitbutton";s:15:"Guardar+cambios";}}s:5:"calcs";a:1:{s:8:"elements";a:0:{}}}';
		$reports['Report032']->export='ods,xls';
		$reports['Report032']->ownerid=$USER->id;
		$reports['Report032']->courseid=SITEID;
  $dev='';
	foreach($reports as $name => $report){
		if ($rec=get_record('edicion_ite', 'type',$TYPE, 'name', $name)){#Existe el informe
			if ($update){
				$report->id=$rec->value;
				$result = update_record('block_configurable_reports_report', $report);
			}
		}else{
			if ($create){
				//crear informe
				$retid = insert_record('block_configurable_reports_report', $report);
				//Registrar el informe en MGM
				if ($retid)	{
   				$rec2 = new stdClass;
        	$rec2->name = $name;
        	$rec2->type = $TYPE;
        	$rec2->value = $retid;
        	$result = insert_record('edicion_ite', $rec2);
   			}
			}
		}
		if(isset($result)){
			$dev=$dev.$name.': '.$result. '<br />';
		}
	}
	return $dev;
}


$mform = new report_form("$CFG->wwwroot".'/mod/mgm/reports/creports_mgm.php');

$mform = new report_form("$CFG->wwwroot".'/mod/mgm/reports/creports.php');
admin_externalpage_setup('updatereports', mgm_update_edition_button());
admin_externalpage_print_header();
print_heading($strtitle);
print_simple_box_start('center');

if ($data = $mform->get_data(false)) {
    if 	(!empty($data->cancel)){
    	unset($_POST);
    	redirect("$CFG->wwwroot".'/index.php');
    }else if (!empty($data->next)) {
    	$sql=$create=$update=0;
			if ($data->sql==1){
				$sql=1;
			}
    	if ($data->create==1){
				$create=1;
			}
    	if ($data->update==1){
				$update=1;
			}
			$dev=mgm_creports($create,$update, $sql);
			print $dev;
			print '<br /<b>Todos los datos procesados.<b />';
			print_simple_box_end();
			admin_externalpage_print_footer();
			die();
    }
}

$mform->display();
print_simple_box_end();
admin_externalpage_print_footer();






