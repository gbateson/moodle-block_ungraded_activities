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
 * @copyright  2014 Gordon Bateson <gordon.bateson@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 2.0
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/lib/filelib.php'); // send_file()

// cache the plugin name  - because it is quite long ;-)
$plugin = 'block_ungraded_activities';

// get the incoming block_instance id
$id = required_param('id', PARAM_INT);

if (! $block_instance = $DB->get_record('block_instances', array('id' => $id))) {
    print_error('invalidinstanceid', $plugin, '', $id);
}
if (! $block = $DB->get_record('block', array('name' => $block_instance->blockname))) {
    print_error('invalidblockname', $plugin, '', $block_instance);
}
if (! $context = $DB->get_record('context', array('id' => $block_instance->parentcontextid))) {
    print_error('invalidcontextid', $plugin, '', $block_instance);
}
if (! $course = $DB->get_record('course', array('id' => $context->instanceid))) {
    print_error('invalidcourseid', $plugin, '', $context);
}

require_login($course->id);

if (class_exists('context')) {
    $context = context::instance_by_id($context->id);
} else {
    $context = get_context_instance_by_id($context->id);
}
require_capability('moodle/site:manageblocks', $context);

if (! isset($block->version)) {
    $params = array('plugin' => 'block_ungraded_activities', 'name' => 'version');
    $block->version = $DB->get_field('config_plugins', 'value', $params);
}

$content = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
if ($config = unserialize(base64_decode($block_instance->configdata))) {

    // set main XML tag name for this block's config settings
    $BLOCK = strtoupper($block_instance->blockname);
    $BLOCK = strtr($BLOCK, array('_' => '')).'BLOCK';

    $content .= "<$BLOCK>\n";
    $content .= '  <VERSION>'.$block->version.'</VERSION>'."\n";
    $content .= '  <POSITION>'.$block_instance->defaultregion.'</POSITION>'."\n";
    $content .= '  <WEIGHT>'.$block_instance->defaultweight.'</WEIGHT>'."\n";
    $content .= '  <VISIBLE>1</VISIBLE>'."\n";

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

    $content .= "</$BLOCK>\n";
}

if (empty($config['title'])) {
    $filename = $block->name.'.xml';
} else {
    $filename = clean_filename(strip_tags(format_string($config['title'], true)).'.xml');
}
send_file($content, $filename, 0, 0, true, true);

/**
 * xml_tag_safe_content
 *
 * copied from Moodle 1.9 backup/backuplib.php
 */
function xml_tag_safe_content($content) {
    global $CFG;
    //If enabled, we strip all the control chars (\x0-\x1f) from the text but tabs (\x9),
    //newlines (\xa) and returns (\xd). The delete control char (\x7f) is also included.
    //because they are forbiden in XML 1.0 specs. The expression below seems to be
    //UTF-8 safe too because it simply ignores the rest of characters.
    $content = preg_replace("/[\x-\x8\xb-\xc\xe-\x1f\x7f]/is","",$content);
    $content = preg_replace("/\r\n|\r/", "\n", htmlspecialchars($content));
    return $content;
}
