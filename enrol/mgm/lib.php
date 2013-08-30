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
 * MGM enrolment plugin.
 *
 * This plugin allows you to set up paid courses.
 *
 * @package    enrol
 * @subpackage mgm
 * @copyright  2013 Jesus Jaen { jesus.jaen@open-phoeix.com }
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Paypal enrolment plugin implementation.
 * @author  Eugene Venter - based on code by Martin Dougiamas and others
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_mgm_plugin extends enrol_plugin {

    /**
     * Returns optional enrolment information icons.
     *
     * This is used in course list for quick overview of enrolment options.
     *
     * We are not using single instance parameter because sometimes
     * we might want to prevent icon repetition when multiple instances
     * of one type exist. One instance may also produce several icons.
     *
     * @param array $instances all enrol instances of this type in one course
     * @return array of pix_icon
     */
    public function get_info_icons(array $instances) {
        return array(new pix_icon('mgm', get_string('pluginname', 'enrol_mgm'), 'enrol_mgm'));
    }

    public function roles_protected() {
        // users with role assign cap may tweak the roles later
        return false;
    }

    public function allow_unenrol(stdClass $instance) {
        // users with unenrol cap may unenrol other users manually - requires enrol/paypal:unenrol
        return true;
    }

    public function allow_manage(stdClass $instance) {
        // users with manage cap may tweak period and status - requires enrol/paypal:manage
        return true;
    }

    public function show_enrolme_link(stdClass $instance) {
        return ($instance->status == ENROL_INSTANCE_ENABLED);
    }

    /**
     * Sets up navigation entries.
     *
     * @param object $instance
     * @return void
     */
    public function add_course_navigation($instancesnode, stdClass $instance) {
        if ($instance->enrol !== 'mgm') {
             throw new coding_exception('Invalid enrol instance type!');
        }

        $context = context_course::instance($instance->courseid);
        if (has_capability('enrol/mgm:config', $context)) {
            $managelink = new moodle_url('/enrol/mgm/edit.php', array('courseid'=>$instance->courseid, 'id'=>$instance->id));
            $instancesnode->add($this->get_instance_name($instance), $managelink, navigation_node::TYPE_SETTING);
        }
    }

    /**
     * Returns edit icons for the page with list of instances
     * @param stdClass $instance
     * @return array
     */
    public function get_action_icons(stdClass $instance) {
        global $OUTPUT;

        if ($instance->enrol !== 'mgm') {
            throw new coding_exception('invalid enrol instance!');
        }
        $context = context_course::instance($instance->courseid);

        $icons = array();

        if (has_capability('enrol/mgm:config', $context)) {
            $editlink = new moodle_url("/enrol/mgm/edit.php", array('courseid'=>$instance->courseid, 'id'=>$instance->id));
            $icons[] = $OUTPUT->action_icon($editlink, new pix_icon('t/edit', get_string('edit'), 'core',
                    array('class' => 'smallicon')));
        }

        return $icons;
    }

    /**
     * Returns link to page which may be used to add new instance of enrolment plugin in course.
     * @param int $courseid
     * @return moodle_url page url
     */
    public function get_newinstance_link($courseid) {
    	global $DB;
        $context = context_course::instance($courseid, MUST_EXIST);

        if (!has_capability('moodle/course:enrolconfig', $context) or !has_capability('enrol/mgm:config', $context)) {
            return NULL;
        }
        // multiple instances supported are not supported
        if ($DB->record_exists('enrol', array('courseid'=>$courseid, 'enrol'=>'mgm'))) {
        	return NULL;
        }        
        return new moodle_url('/enrol/mgm/edit.php', array('courseid'=>$courseid));
    }

    /**
     * Creates course enrol form, checks if form submitted
     * and enrols user if necessary. It can also redirect.
     *
     * @param stdClass $instance
     * @return string html text, usually a form in a text box
     */
    function enrol_page_hook(stdClass $instance) {
        global $CFG, $USER, $OUTPUT, $PAGE, $DB, $form, $SESSION, $_POST;
        ob_start();

        if ($DB->record_exists('user_enrolments', array('userid'=>$USER->id, 'enrolid'=>$instance->id))) {
            return ob_get_clean();
        }

        if ($instance->enrolstartdate != 0 && $instance->enrolstartdate > time()) {
            return ob_get_clean();
        }

        if ($instance->enrolenddate != 0 && $instance->enrolenddate < time()) {
            return ob_get_clean();
        }

        $course = $DB->get_record('course', array('id'=>$instance->courseid));
        $context = context_course::instance($course->id);

        $shortname = format_string($course->shortname, true, array('context' => $context));
        $strloginto = get_string("loginto", "", $shortname);
        $strcourses = get_string("courses");

        // Pass $view=true to filter hidden caps if the user cannot see them
//         if ($users = get_users_by_capability($context, 'moodle/course:update', 'u.*', 'u.id ASC',
//                                              '', '', '', '', false, true)) {
//             $users = sort_by_roleassignment_authority($users, $context);
//             $teacher = array_shift($users);
//         } else {
//             $teacher = false;
//         }

//         if ( (float) $instance->cost <= 0 ) {
//             $cost = (float) $this->get_config('cost');
//         } else {
//             $cost = (float) $instance->cost;
//         }

//         if (abs($cost) < 0.01) { // no cost, other enrolment methods (instances) should be used
//             echo '<p>'.get_string('nocost', 'enrol_paypal').'</p>';
//         } else {

//             if (isguestuser()) { // force login only for guest user, not real users with guest role
//                 if (empty($CFG->loginhttps)) {
//                     $wwwroot = $CFG->wwwroot;
//                 } else {
//                     // This actually is not so secure ;-), 'cause we're
//                     // in unencrypted connection...
//                     $wwwroot = str_replace("http://", "https://", $CFG->wwwroot);
//                 }
//                 echo '<div class="mdl-align"><p>'.get_string('paymentrequired').'</p>';
//                 echo '<p><b>'.get_string('cost').": $instance->currency $cost".'</b></p>';
//                 echo '<p><a href="'.$wwwroot.'/login/">'.get_string('loginsite').'</a></p>';
//                 echo '</div>';
//             } else {
//                 //Sanitise some fields before building the PayPal form
//                 $coursefullname  = format_string($course->fullname, true, array('context'=>$context));
//                 $courseshortname = $shortname;
//                 $userfullname    = fullname($USER);
//                 $userfirstname   = $USER->firstname;
//                 $userlastname    = $USER->lastname;
//                 $useraddress     = $USER->address;
//                 $usercity        = $USER->city;
//                 $instancename    = $this->get_instance_name($instance);

//                 include($CFG->dirroot.'/enrol/paypal/enrol.html');
//             }

//         }

//         return $OUTPUT->box(ob_get_clean());
        redirect(new moodle_url($CFG->wwwroot . '/enrol/mgm/enrol.php', array('id'=>$instance->id)));
       
    }

    /**
     * Gets an array of the user enrolment actions
     *
     * @param course_enrolment_manager $manager
     * @param stdClass $ue A user enrolment object
     * @return array An array of user_enrolment_actions
     */
    public function get_user_enrolment_actions(course_enrolment_manager $manager, $ue) {
        $actions = array();
        $context = $manager->get_context();
        $instance = $ue->enrolmentinstance;
        $params = $manager->get_moodlepage()->url->params();
        $params['ue'] = $ue->id;
        if ($this->allow_unenrol($instance) && has_capability("enrol/mgm:unenrol", $context)) {
            $url = new moodle_url('/enrol/unenroluser.php', $params);
            $actions[] = new user_enrolment_action(new pix_icon('t/delete', ''), get_string('unenrol', 'enrol'), $url, array('class'=>'unenrollink', 'rel'=>$ue->id));
        }
        return $actions;
    }
}
