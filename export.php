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
 * blocks/ungraded_activities/export.php
 *
 * @package    blocks
 * @subpackage ungraded_activities
 * @copyright  2014 Gordon Bateson (gordon.bateson@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 2.0
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/lib/filelib.php'); // send_file()
require_once($CFG->dirroot.'/backup/backuplib.php'); // xml_tag_safe_content()

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

$content = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
if ($config = unserialize(base64_decode($block_instance->configdata))) {

    $content .= '<UNGRADEDACTIVITIESBLOCK>'."\n";
    $content .= '  <VERSION>'.$block->version.'</VERSION>'."\n";
    $content .= '  <POSITION>'.$block_instance->position.'</POSITION>'."\n";
    $content .= '  <WEIGHT>'.$block_instance->weight.'</WEIGHT>'."\n";
    $content .= '  <VISIBLE>'.$block_instance->visible.'</VISIBLE>'."\n";
    $content .= '  <CONFIGFIELDS>'."\n";

    $config = get_object_vars($config);
    foreach ($config as $name => $value) {
        if (empty($name) || is_array($value) || is_object($value)) {
            continue; // shouldn't happen !!
        }
        $content .= '    <CONFIGFIELD>'."\n";
        $content .= '      <NAME>'.xml_tag_safe_content($name).'</NAME>'."\n";
        $content .= '      <VALUE>'.xml_tag_safe_content($value).'</VALUE>'."\n";
        $content .= '    </CONFIGFIELD>'."\n";
    }

    $content .= '  </CONFIGFIELDS>'."\n";
    $content .= '</UNGRADEDACTIVITIESBLOCK>'."\n";
}

if (empty($config['title'])) {
    $filename = $block->name.'.xml';
} else {
    $filename = clean_filename(strip_tags(format_string($config['title'], true)).'.xml');
}
send_file($content, $filename, 0, 0, true, true);
