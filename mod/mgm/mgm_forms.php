<?php //$Id: user_bulk_forms.php,v 1.1.2.3 2007/12/20 10:54:07 skodak Exp $

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/datalib.php');

class edicion_form extends moodleform {
    function definition() {
        $mform =& $this->_form;
        #$total  =& $this->_customdata['total'];
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
        #$mform->addElement('filepicker', 'userfile', get_string('file'), null, array('maxbytes' => 55555555555, 'accepted_types' => '*'));
        $mform->addElement('group', 'actionsgrp', '', $objs, ' ', false);

//        $renderer =& $mform->defaultRenderer();
//        $template = '<label class="qflabel" style="vertical-align:top">{label}</label> {element}';
//        $renderer->setGroupElementTemplate($template, 'usersgrp');
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


?>
