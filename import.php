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
 * blocks/ungraded_activities/import.php
 *
 * @package    blocks
 * @subpackage ungraded_activities
 * @copyright  2014 Gordon Bateson (gordon.bateson@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 2.0
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/lib/xmlize.php');
require_once($CFG->dirroot.'/lib/uploadlib.php');

$id = required_param('id', PARAM_INT); // block_instance id

if (! $block_instance = get_record('block_instance', 'id', $id)) {
    print_error('invalidinstanceid', 'block_ungraded_activities');
}

if (! $block = get_record('block', 'id', $block_instance->blockid)) {
    print_error('invalidblockid', 'block_ungraded_activities', $block_instance->blockid);
}

if (! $course = get_record('course', 'id', $block_instance->pageid)) {
    print_error('invalidcourseid', 'block_ungraded_activities', $block_instance->pageid);
}

require_login($course->id);

$context = get_context_instance(CONTEXT_COURSE, $course->id);
require_capability('moodle/site:manageblocks', $context);

if (optional_param('cancel', '', PARAM_ALPHA)) {
    $url = $CFG->wwwroot.'/course/view.php?id='.$course->id.'&instanceid='.$block_instance->id.'&blockaction=config';
    if (function_exists('sesskey')) {
        $url .= '&sesskey='.sesskey();
    }
    // return to block config page
    redirect($url);
}

$blockname = get_string('blockname', 'block_ungraded_activities');
$importsettings = get_string('importsettings', 'block_ungraded_activities');

$navlinks = array(
    array('name' => get_string('blocks'), 'link' => '', 'type' => 'title'),
    array('name' => $blockname, 'link' => '', 'type' => 'title'),
    array('name' => $importsettings, 'link' => '', 'type' => 'title')
);

print_header($course->fullname, $blockname, build_navigation($navlinks));
print_heading($blockname.': '.$importsettings);
print_box_start('generalbox');

if (data_submitted()) {
    // import settings from xml file

    // check session
    if (function_exists('require_sesskey')) {
        require_sesskey();
    } else if (function_exists('confirm_sesskey')) {
        confirm_sesskey();
    }

    // get upload manager and do standard check on uploaded file
    $um = new upload_manager('', false, false, $course);
    if ($um->preprocess_files() && ungraded_activities_import($block_instance)) {
        // successful import
        $msg   = get_string('validimportfile', 'block_ungraded_activities');
        $style = 'notifysuccess';
        $url   = $CFG->wwwroot.'/course/view.php?id='.$course->id;
    } else {
        // import didn't work - shouldn't happen !!
        $msg   = get_string('invalidimportfile', 'block_ungraded_activities');
        $style = 'notifyproblem';
        $url   = $CFG->wwwroot.'/blocks/ungraded_activities/import.php?id='.$id;
    }

    notify($msg, $style);
    print_continue($url);

} else {
    // show the import form
    ungraded_activities_import_form($course, $block_instance);
}

print_box_end();
print_footer($course);

/**
 * ungraded_activities_import_form
 *
 * @param xxx $course
 * @param xxx $block_instance
 */
function ungraded_activities_import_form($course, $block_instance) {
    global $CFG;

    echo '<form method="post" action="import.php" enctype="multipart/form-data">'."\n";
    echo '<table border="0" cellpadding="4" cellspacing="4" width="600" style="margin: auto;">'."\n";
    echo '<tr>'."\n";

    echo '<td align="left" valign="top">'."\n";
    print_string('filetoimport', 'glossary');
    echo ' ';
    helpbutton('filetoimport', get_string('filetoimport', 'glossary'), 'glossary');
    echo '<br />';
    echo '<span style="font-size:smaller;">(';
    print_string('maxsize', '', display_size(get_max_upload_file_size($CFG->maxbytes, $course->maxbytes)));
    echo ')</span>'."\n";
    echo '</td>'."\n";

    echo '<td align="left" valign="top">'."\n";
    upload_print_form_fragment(); // file upload element + trailing <br />
    echo '<input type="submit" name="import" value="'.get_string('importsettings', 'block_ungraded_activities').'" />'."\n";
    echo ' &nbsp; &nbsp; ';
    echo '<input type="submit" name="cancel" value="'.get_string('cancel').'" />'."\n";
    echo '<input type="hidden" name="id" value="'.$block_instance->id.'" />'."\n";
    if (function_exists('sesskey')) {
        echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />'."\n";
    }
    echo '</td>'."\n";

    echo '</tr>'."\n";
    echo '</table>'."\n";
    echo '</form>'."\n";
}

/**
 * ungraded_activities_import_form
 *
 * @param xxx $block_instance
 * @return boolean true if import was successful, false otherwise
 */
function ungraded_activities_import($block_instance) {
    if (! $file = array_shift($_FILES)) {
        return false;
    }
    if (! isset($file['tmp_name'])) {
        return false;
    }
    if (! file_exists($file['tmp_name'])) {
        return false;
    }
    if (! is_file($file['tmp_name'])) {
        return false;
    }
    if (! is_readable($file['tmp_name'])) {
        return false;
    }
    if (! $xml = file_get_contents($file['tmp_name'])) {
        return false;
    }
    if (! $xml = xmlize($xml, 0)) {
        return false;
    }
    if (! isset($xml['UNGRADEDACTIVITIESBLOCK']['#']['CONFIGFIELDS'][0]['#']['CONFIGFIELD'])) {
        return false;
    }

    $configfield = &$xml['UNGRADEDACTIVITIESBLOCK']['#']['CONFIGFIELDS'][0]['#']['CONFIGFIELD'];
    $config = unserialize(base64_decode($block_instance->configdata));

    $i = 0;
    while (isset($configfield[$i]['#'])) {
        $name = $configfield[$i]['#']['NAME'][0]['#'];
        $value = $configfield[$i]['#']['VALUE'][0]['#'];
        $config->$name = $value;
        $i++;
    }

    if ($i==0) {
        return false;
    }

    $block_instance->configdata = base64_encode(serialize($config));
    set_field('block_instance', 'configdata', $block_instance->configdata, 'id', $block_instance->id);
    return true;
}
