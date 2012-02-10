<?php //$Id: user_bulk_forms.php,v 1.1.2.3 2007/12/20 10:54:07 skodak Exp $

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/datalib.php');

class edicion_form extends moodleform {
    function definition() {
        $mform =& $this->_form;
        $achoices = array();
        $filter_editions = optional_param('edition',0,PARAM_INT);
				$editionlist = array_keys(get_records('edicion'));
				$editionoptions = array();
				$editionoptions[0] = get_string('choose');
				if(!empty($editionlist)){
					$editions = get_records_select('edicion','id in ('.(implode(',',$editionlist)).')');
					foreach($editions as $c){
						$editionoptions[$c->id] = format_string($c->name);
					}
				}
				$mform->addElement('select', 'edition', get_string('edition', 'mgm'), $editionoptions);
				$mform->setType('edition', PARAM_INT);        $objs = array();
				$objs[0] =& $mform->createElement('submit', 'cancel', get_string('cancel'));
        $objs[1] =& $mform->createElement('submit', 'next', get_string('next'));
        $mform->addElement('group', 'actionsgrp', '', $objs, ' ', false);

    }
}

class admin_form extends moodleform {
    function definition() {
        $mform =& $this->_form;
				$actionoptions = array('elegir'=>'Elegir','loadchistory'=>"Cargar historico de certificaciones",'loadcolegios' =>'Actualizar centros');
				$mform->addElement('select', 'action', get_string('action', 'mgm'), $actionoptions);
				$mform->addElement('file', 'userfile', get_string('file'));
				$objs[0] =& $mform->createElement('submit', 'cancel', get_string('cancel'));
        $objs[1] =& $mform->createElement('submit', 'next', get_string('next'));
        $mform->addElement('group', 'actionsgrp', '', $objs, ' ', false);

    }
}

class report_form extends moodleform {
    function definition() {
        $mform =& $this->_form;
				$options = array(1=>"Si", 0=>'No');
				$mform->addElement('select', 'sql', get_string('sql_report', 'mgm'),$options);
				$mform->addElement('select', 'create', get_string('create_report', 'mgm'),$options);
				$mform->addElement('select', 'update', get_string('update_report', 'mgm'),$options);
				$objs[0] =& $mform->createElement('submit', 'cancel', get_string('cancel'));
        $objs[1] =& $mform->createElement('submit', 'next', get_string('next'));
        $mform->addElement('group', 'actionsgrp', '', $objs, ' ', false);

    }
}


/* User filterig by edition course and other*/
class user_filter extends moodleform {
	var $_filters;

  function definition() {
  			global $CFG;
        $mform =& $this->_form;
        $this->filters=array();
        $filter_edition = optional_param('edition',-1,PARAM_INT);
        $filter_catetory = optional_param('category',0,PARAM_INT);
        $filter_course = optional_param('course',0,PARAM_INT);
        $filter_group = optional_param('group',0,PARAM_INT);
				$editionlist = array_keys(get_records('edicion'));
				$editionoptions = array();
				$editionoptions[0] = '- Elegir -';

				if(!empty($editionlist)){
					$editions = get_records_select('edicion','id in ('.(implode(',',$editionlist)).')');
					foreach($editions as $c){
						$editionoptions[$c->id] = format_string($c->name);
					}
				}
				$categoryoptions = array(0=>'- Elegir -');
				if ($cats=get_records('course_categories')){
					foreach($cats as $c){
						$categoryoptions[$c->id] = format_string($c->name);
					}
					asort($categoryoptions);
				}
				$ed=mgm_get_active_edition();
				if($ed && $filter_edition==-1){
					$filter_edition=$ed->id;
				}
				//Course filter
				$ids=false;
		    $courseoptions = array(0=>'- Elegir -');
		    if ($filter_edition){
		    	$sql="select courseid from ".$CFG->prefix."edicion_course where edicionid=".$filter_edition;
					$ids= array_keys(get_records_sql($sql));
		    }
		    else{
		    	$ids=array_keys(get_records('course'));
		    }
		    if ($ids){
		    	$courses = get_records_select('course','id in ('.(implode(',',$ids)).')');
		    	foreach($courses as $c){
						$courseoptions[$c->id] = format_string($c->fullname);
		    	}
		    	asort($courseoptions);
		    }
		    //Group filter
		    $ids=false;
		    $groupoptions = array(0=>'- Elegir -');
		    if ($filter_course){
		    	$ids=array_keys(get_records('groups','courseid',$filter_course));
		    }
		    else{
		    	$ids=false;
		    }

		    if ($ids){
		    	$groups = get_records_select('groups','id in ('.(implode(',',$ids)).')');
		    	foreach($groups as $c){
						$groupoptions[$c->id] = format_string($c->name);
		    	}
		    	asort($groupoptions);
		    }

				$mform->addElement('select', 'edition', get_string('edition', 'mgm'), $editionoptions);
				$mform->setType('edition', PARAM_INT);
				if ($ed){
					$mform->setDefault('edition', $ed->id);
				}

				$mform->addElement('select', 'category', get_string('category', 'mgm'), $categoryoptions);
				$mform->setType('category', PARAM_INT);
				$mform->addElement('select', 'course', get_string('course'), $courseoptions);
				$mform->setType('course', PARAM_INT);
				$mform->addElement('select', 'group', get_string('group'), $groupoptions);
				$mform->setType('group', PARAM_INT);
				$mform->addElement('text', 'cc', get_string('cc', 'mgm'), array('size'=>'8'));
				$mform->setType('cc', PARAM_TEXT);
				$mform->addElement('text', 'fullname', get_string('name'), array('size'=>'25'));
				$mform->setType('name', PARAM_TEXT);
				$objs = array();
				$objs[0] =& $mform->createElement('submit', 'add', get_string('add'));
				$objs[1] =& $mform->createElement('submit', 'reset', get_string('reset'));
        $objs[2] =& $mform->createElement('submit', 'show', get_string('show_users', 'mgm'));
        $mform->addElement('group', 'actionsgrp', '', $objs, ' ', false);
  }

  function set_filters($data){
  	 global $SESSION;
			if ($data->edition){
				$this->_filters['edicionid']=$data->edition;
				$SESSION->bulk_filters['edicionid']=$data->edition;
			}
    	if ($data->category){
				$this->_filters['category']=$data->category;
				$SESSION->bulk_filters['category']=$data->category;
			}
    	if ($data->course){
				$this->_filters['courseid']=$data->course;
				$SESSION->bulk_filters['courseid']=$data->course;
			}
    	if ($data->group){
				$this->_filters['groupid']=$data->group;
				$SESSION->bulk_filters['groupid']=$data->group;
			}
  		if ($data->cc){
				$this->_filters['cc']=$data->cc;
				$SESSION->bulk_filters['cc']=$data->cc;
			}
  		if ($data->fullname){
				$this->_filters['fullname']=$data->fullname;
				$SESSION->bulk_filters['fullname']=$data->fullname;
			}
  }

	function reset_filters($filters){
				$this->_filters=array();
  }

  function get_users(){
  	  global $CFG;
  	  $arol='5';
  	  if (isset($this->_filters['groupid']) && $this->_filters['groupid']!=0){
 	    	$sqlbase="SELECT u.id, ".sql_fullname()." AS fullname, eu.dni, eu.cc, u.email, c.fullname as curso
     		FROM prefix_user u left join  prefix_edicion_user eu on (u.id=eu.userid) left join prefix_edicion_centro ect on ect.codigo=eu.cc,
     		prefix_edicion_course ec left join prefix_course c on ec.courseid=c.id , prefix_role_assignments AS ra INNER JOIN prefix_context AS context ON ra.contextid=context.id,
     		(SELECT g.id groupid, g.courseid coursegr, gm.userid FROM prefix_groups_members gm join prefix_groups g on (gm.groupid=g.id)) cg
     		where context.contextlevel = 50 AND ra.roleid=".$arol." AND u.id=ra.userid AND context.instanceid=ec.courseid AND cg.userid=u.id AND cg.coursegr=ec.courseid";
  	  }else{// No filter group
  	  	$sqlbase="SELECT u.id, ".sql_fullname()." AS fullname, eu.dni, eu.cc, u.email, c.fullname as curso
     		FROM prefix_user u left join  prefix_edicion_user eu on (u.id=eu.userid) left join prefix_edicion_centro ect on ect.codigo=eu.cc,
     		prefix_edicion_course ec left join prefix_course c on ec.courseid=c.id , prefix_role_assignments AS ra INNER JOIN prefix_context AS context ON ra.contextid=context.id
     		where context.contextlevel = 50 AND ra.roleid=".$arol." AND u.id=ra.userid AND context.instanceid=ec.courseid";
  	  }
 	    $sqlbase=str_replace("prefix_", $CFG->prefix, $sqlbase);
 	    $where='';
 	    foreach ($this->_filters as $field=>$value){
 	    	if ($field=='cc' ){
 	    		$where=$where. " AND ".$field." like '".$value."'";
 	    	}else if ($field=='fullname'){
 	    		$where=$where. " AND concat(u.firstname,' ',u.lastname) like '%".$value."%'";
 	    	}else{
 	    		$where=$where. ' AND ' .$field.'='.$value;
 	    	}
 	    }
 	    $limit=' limit 5000';
 	    $order=' order by cc';

 	    $sql=$sqlbase.$where.$order.$limit;
 	    if($regs=get_records_sql($sql)){
 	    	return $regs;
 	    }
			return false;
  }

  function print_users_table($users){
  	  global $CFG;
			$table=new stdClass();
			$table->head=array(
			'',
			get_string('user'),
			'ID Documento',
			get_string('cc', 'mgm'),
			get_string('email'),
			get_string('course'),
			);
			$table->width = '100%';
			$table->data=array();
			$cnt=1;
			foreach($users as $user){
				  $input='<input type="checkbox" name="users['.$user->id.']" checked value="'.$user->id.'" />';
				  $name='<a href="'.$CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.SITEID.'">'.$user->fullname.'</a>';
				  $table->data[] = array(
				  $cnt,
				  $input.' '.$name,
				  strtoupper($user->dni),
				 	$user->cc,
				  $user->email,
				  $user->curso
				  );
				  $cnt++;
			}
			print_table($table);
  }

}

class action_users extends moodleform {
    function definition() {
        $mform =& $this->_form;
				$actionoptions = array(0=>'Elegir',1=>"Enviar correo",2 =>'Generar cartas');
				$mform->addElement('select', 'action', get_string('action', 'mgm'), $actionoptions);
				$objs = array();
				$objs[0] =& $mform->createElement('submit', 'cancel', get_string('cancel'));
        $objs[1] =& $mform->createElement('submit', 'next', get_string('next'));
        $mform->addElement('group', 'actionsgrp', '', $objs, ' ', false);
    }


}



class user_letter_form extends moodleform {

    function definition() {

        $mform =& $this->_form;
        $data=$this->getDefault();
        $mform->addElement('header', 'page1', get_string('page', 'mgm'));
        $mform->addElement('text', 'pagetitle', get_string('pagetitle', 'mgm'), array('size'=>'50'));
        $mform->addRule('pagetitle', '', 'required', null, 'client');
				$mform->addElement('textarea', 'pagehead1', get_string('pagehead', 'mgm'). ' 1', array('rows'=>4, 'cols'=>60));
				$mform->addElement('textarea', 'pagehead2', get_string('pagehead', 'mgm'). ' 2', array('rows'=>4, 'cols'=>60));
				$mform->addElement('header', 'letter', get_string('letter', 'mgm'));
				$mform->addElement('textarea', 'letterhead', get_string('letterhead', 'mgm'), array('rows'=>6, 'cols'=>60));
        $mform->addElement('textarea', 'letterbody', get_string('letterbody', 'mgm'), array('rows'=>15, 'cols'=>60));
        $mform->addElement('textarea', 'letterfoot', get_string('letterfoot', 'mgm'), array('rows'=>4, 'cols'=>60));
        $mform->addRule('letterfoot', '', 'required', null, 'client');
        $mform->addElement('header', 'page2', get_string('page', 'mgm'));
        $mform->addElement('textarea', 'pagefoot1', get_string('pagefoot', 'mgm'). ' 1', array('rows'=>4, 'cols'=>60));
        $mform->addRule('pagefoot1', '', 'required', null, 'client');
        $mform->addElement('textarea', 'pagefoot2', get_string('pagefoot', 'mgm'). ' 2', array('rows'=>4, 'cols'=>60));
        $this->add_action_buttons();
        foreach($data as $element=>$value){
        	$mform->setDefault($element, $value, $slashed=false);
        }
    }

    function getDefault(){
			global $CFG;
			$filename=$CFG->dirroot.'/mod/mgm/data/letter.template.txt';

			$labels=array('pagetitle', 'pagehead1', 'pagehead2', 'letterhead', 'letterbody', 'letterfoot', 'pagefoot1', 'pagefoot2');
			$data=array();
			$field=false;
			$handle = fopen($filename, "r");
			if ($handle){
				while (! feof($handle)) {
					$line = fgets($handle);
					$f=rtrim($line, " :\n");
					if ($f==''){//saltamos lineas en blanco, con espacios
						continue;
					}
					if (array_search($f, $labels)!==false){
						$field=$f;
						$data[$field]='';
						continue;
					}
					if($field){
						$data[$field]=$data[$field].$line;
					}
				}
				fclose($handle);
				return $data;
			}else{
				return false;
			}
    }


}
?>
