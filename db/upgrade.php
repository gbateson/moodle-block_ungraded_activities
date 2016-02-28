<?php
/**
 * blocks/ungraded_activities/db/upgrade.php
 *
 * @package    blocks
 * @subpackage ungraded_activities
 * @copyright  2014 Gordon Bateson <gordon.bateson@gmail.com>
 * @license    you may not copy of distribute any part of this package without prior written permission
 */

// prevent direct access to this script
defined('MOODLE_INTERNAL') || die();

function xmldb_block_ungraded_activities_upgrade($oldversion=0) {
    global $CFG, $DB;

    $result = true;

    $newversion = 2014070100;
    if ($oldversion < $newversion) {
        update_capabilities('block/ungraded_activities');
        upgrade_block_savepoint($result, "$newversion", 'ungraded_activities');
    }

    $newversion = 2014070101;
    if ($oldversion < $newversion) {
        if ($instances = $DB->get_records('block_instances', array('blockname' => 'ungraded_activities'))) {
            foreach ($instances as $instance) {
                $instance->config = unserialize(base64_decode($instance->configdata));
                if (empty($instance->config->showactivities)) {
                    continue;
                }
                $activities = explode(',', $instance->config->showactivities);
                if (($i = array_search('attforblock', $activities))===false) {
                    continue;
                }
                $activities = array_splice($activities, $i, 1, array('attendance'));
                $instance->config->showactivities = implode(',', $activities);
                $instance->configdata = base64_encode(serialize($instance->config));
                set_field('block_instances', 'configdata', $instance->configdata, 'id', $instance->id);
            }
        }
        upgrade_block_savepoint($result, "$newversion", 'ungraded_activities');
    }

    return $result;
}
