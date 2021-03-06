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
 * blocks/ungraded_activities/db/upgrade.php
 *
 * @package    blocks
 * @subpackage ungraded_activities
 * @copyright  2014 Gordon Bateson (gordon.bateson@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 2.0
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
