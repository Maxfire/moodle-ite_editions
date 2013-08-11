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

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');

require_login();

$systemcontext = context_system::instance();
require_capability('mod/mgm:aprobe', $systemcontext);

$scala = mgm_get_certification_scala();
$availablescalas = $DB->get_records('scale', array('courseid'=> 0));

$strtitle = get_string('setscala', 'mgm');


require_once($CFG->libdir.'/adminlib.php');
$PAGE->set_url('/mod/mgm/edicionesscala.php');
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('admin');
admin_externalpage_setup('edicionesmgmt', mgm_update_edition_button());

if ($frm = data_submitted() and confirm_sesskey()) {
    if (isset($frm->scala)) {
        mgm_set_certification_scala($frm->scala);
        redirect('index.php', get_string('scaladone', 'mgm'), 5);
    }
}
echo $OUTPUT->header();
echo $OUTPUT->heading($strtitle);
echo $OUTPUT->box_start('center');
?>
<form id="scalaform" method="post" action="">
    <div style="text-align: center;">
        <input type="hidden" name="sesskey" value="<?php p(sesskey()) ?>" />
        <select name="scala" id="scalaselect">
            <?php
                foreach($availablescalas as $avalscala) {
                    if ($scala && ($scala->value == $avalscala->id)) {
                        ?>
                        <option value="<?php echo $avalscala->id; ?>" selected><?php echo $avalscala->name; ?></option>
                        <?php
                    } else {
                        ?>
                        <option value="<?php echo $avalscala->id; ?>"><?php echo $avalscala->name; ?></option>
                        <?php
                    }
                }
            ?>
        </select>
        <input name="submit" id="submit" type="submit" value="<?php p(get_string('save', 'quiz')); ?>" />
    </div>
</form>
<?php
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
