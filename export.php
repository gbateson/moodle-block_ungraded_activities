<?php
/**
 * blocks/ungraded_activities/export.php
 *
 * @package    blocks
 * @subpackage ungraded_activities
 * @copyright  2010 Gordon Bateson <gordon.bateson@gmail.com>
 * @license    you may not copy of distribute any part of this package without prior written permission
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
