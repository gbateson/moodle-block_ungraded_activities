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
 * blocks/ungraded_activities/lang/en_utf8/block_ungraded_activities.php
 *
 * @package    blocks
 * @subpackage ungraded_activities
 * @copyright  2014 Gordon Bateson (gordon.bateson@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 2.0
 */

// essential strings
$string['pluginname'] = 'Ungraded activities';
$string['blockdescription'] = 'This block displays a list of ungraded activities on a course page.';
$string['blockname'] = 'Ungraded Activities';
$string['blocknameplural'] = 'Ungraded activities';

// roles strings
$string['ungraded_activities:addinstance'] = 'Add a new Ungraded Activities block';

// more strings
$string['adduserlinks'] = 'Add links to user profiles';
$string['adduserlinks_help'] = '**Yes**  
&nbsp; the names in the user list will be linked to the each user\'s profile page

**No**  
&nbsp; the name of each user will displayed without a link to the user\'s profile page';

$string['apply'] = 'Apply';
$string['applyselectedvalues'] = 'Apply selected values to the following courses';
$string['clicktograde'] = 'Click to grade this {$a}';
$string['customdatefmt'] = 'Custom date format string';
$string['checkoverrides'] = 'Check for overrides';
$string['checkoverrides_help'] = 'This setting specifies whether or not the block should display grades that have been overridden in the grade book. Overridden grades cannot be modified via the normal grading pages for activities, so it can be confusing if this block displays them.

Note that enabling this setting causes extra work for the database server, so the default setting is &quot;No&quot;.

**No**  
&nbsp; The block will not check to see if activity grades have been overridden in the Moodle gradebook.

**Yes - ignore if overridden**  
&nbsp; The block will ignore submissions with grades that have been overridden in the gradebook, regardless of whether the work was submitted before or after the grade was overridden.

**Yes - ignore if recently overridden**  
&nbsp; The block will ignore submissions whose grades has been overridden since the work was submitted. If work was submitted after the grade was overridden, a link will be shown to the page where the teacher can modify the overridden grade.';
$string['checkoverrides1'] = 'Yes - ignore if overridden';
$string['checkoverrides2'] = 'Yes - ignore if recently overridden';
$string['customdatefmt_help'] = 'Any string specified here will be used to format the submission dates.

The format codes are those used by the PHP &quot;strftime&quot; function. More information about these codes is available via the &quot;Help&quot; link next to the textbox for this setting.';
$string['fixdaymonth'] = 'Remove leading zeros in dates';
$string['exportsettings'] = 'Export settings';
$string['exportsettings_help'] = 'This link allows you export the configuration settings for this block to a file that you can import into a similar block in another course.';
$string['fixdaymonth_help'] = '**Yes**  
&nbsp; the leading zero on day and month numbers less than 10 will be removed

**No**  
&nbsp; day and month numbers less than 10 will be displayed as 01, 02, 03 and so on.';
$string['forexample'] = 'e.g.';
$string['head'] = 'Head';
$string['importsettings'] = 'Import settings';
$string['importsettings_help'] = 'This link takes you to a screen where you can import configuration settings from a configuration settings file exported from the same type of block in another course.';
$string['invalidblockname'] = 'Invalid block name in block instance record: id={$a->id}, blockname={$a->blockname}';
$string['invalidcontextid'] = 'Invalid parentcontextid in block instance record: id = {$a->id}, parentcontextid = {$a->parentcontextid}';
$string['invalidcourseid'] = 'Invalid instanceid in course context record: id={$a->id}, instanceid={$a->instanceid}';
$string['invalidimportfile'] = 'Import file was missing, empty or invalid';
$string['invalidinstanceid'] = 'Invalid block instance id: id = {$a}';
$string['moodledatefmt'] = 'Moodle date format string';
$string['moodledatefmt_help'] = 'The dates of ungraded submissions will be formatted in a similar way to the date selected here.

If you click on the &quot;+&quot; sign next to one of the dates, the name of the date format string for that date will be displayed, along with its format codes. This is useful if you want to create your own date format string in the &quot;Custom date format string&quot; setting below.

Note that if the &quot;Show date last modified&quot; is set to &quot;No&quot; then no date will be displayed. Also, if a format is specified in the &quot;Custom date format string&quot; setting, then that will override the string selected here.';
$string['mycourses'] = 'My courses';
$string['mycourses_help'] ='On this list you can specify other courses to which you wish to copy this block\'s settings. The list only includes courses where you are a teacher and which already have an Ungraded Activities block.';
$string['noactivities'] = 'No gradeable activities';
$string['noitems'] = 'No ungraded items';
$string['refreshthispage'] = 'Refresh this page';
$string['save'] = 'Save';
$string['settingsmenu'] = 'Settings menu';
$string['selectallnone'] = 'Select';
$string['selectallnone_help'] = 'The checkboxes in this column allow you to select certain settings in this block and copy them to TaskChain navigation blocks in other Moodle courses on this site.

Settings can be selected individually, or you can use the "All" or "None" links to select all or none of the settings with one click.

To select the courses to which you wish copy this block\'s settings, use the course menu at the bottom of this block configuration page.

Note that you can only copy the settings to courses in which you are a teacher (or administrator) and which already have a TaskChain navigation block.

To copy these settings to blocks in other Moodle sites, use the "export" function on this page, and the "import" function of the block on the destination site.';
$string['showactivities'] = 'Show activities';
$string['showactivities_help'] = 'The checkboxes here allow you to select the type of activities that you wish to be included in the list of items that have not yet been graded or approved.';
$string['showassigns'] = 'Assignments (Moodle >= 2.3)';
$string['showassignments'] = 'Assignments (Moodle <= 2.2)';
$string['showattendances'] = 'Attendance';
$string['showcountitems'] = 'Show number of items';
$string['showcountitems_help'] = '**Yes**  
&nbsp; display a message showing the total number ungraded items that were found and the total number of activities that have ungraded items. If the are no such actvities, a message will be displayed. If there is only one such activity, then this message will not be displayed, because the link to the activity already includes the number of its ungraded items.

**No**  
&nbsp; do not display a message showing how many ungraded items and activities were found. However, the number of ungraded items in each activity will still be displayed in the links to the activities.';
$string['showdatabases'] = 'Databases (with ratings)';
$string['showforums'] = 'Forums (with ratings)';
$string['showglossaries'] = 'Glossaries (with ratings)';
$string['showlessons'] = 'Lessons (with essay questions)';
$string['showquizzes'] = 'Quizzes (with essay questions)';
$string['showquizzestext'] = 'Quizzes are not being included in the list of activities.';
$string['showtimes'] = 'Show date last modified';
$string['showtimes_help'] = '**Yes**  
&nbsp; the information about each ungraded submission will include the time of the submission

**No**  
&nbsp; no information about the times of the ungraded submissios will be displayed';
$string['showuserlist'] = 'Show lists of users';
$string['showuserlist_help'] = '**No**  
&nbsp; For each ungraded activity, this block will not display a list of users with ungraded items, but will instead simply show a link to the main grading page for the activity.

**Yes - collapsed**  
&nbsp; For each ungraded activity, a list of users with ungraded submissions will be created. Initially the list will be collapsed, so the user names will not be visible, but the list can be expanded by clicking on the activity name.

**Yes - expanded**  
&nbsp; The list of users for each activity will be displayed in full and will not be collapsible.';

$string['showuserlist1'] = 'Yes - collapsed';
$string['showuserlist2'] = 'Yes - expanded';
$string['showusertype'] = 'Users to show in lists';
$string['showusertype_help'] = '**All users**  
&nbsp; The user list for each activity can potentially include any users who have ever submitted anything for the activity.

**All participants in this course**  
&nbsp; Only users who currently have a role in the course can appear in the lists of users. This includes current administrators, teachers and students, but excludes guests and ex-students.

**Students enrolled in this course**  
&nbsp; Only users who are currently enrolled as students in the course can appear in the lists of users. Administrators, teachers, ex-students and guests will not appear in the user lists.';

$string['showusertype0'] = 'All users';
$string['showusertype1'] = 'All participants in this course';
$string['showusertype2'] = 'Students enrolled in this course';
$string['showworkshops'] = 'Workshops';
$string['tail'] = 'Tail';
$string['textlength'] = 'Text length';
$string['textlength_help'] = 'These settings specify how to format activity names that are too long to be displayed in a single line in the block.

If the length of the activity name exceeds the &quot;Total&quot; number of characters specified here, then it will be reformatted as HEAD ... TAIL, where HEAD is the &quot;Head&quot; number of characters from the beginning of the name, and TAIL is the &quot;Tail&quot; number of characters from the end of the name.

You can specify separate values for each of the languages used in this course. Note that a value of zero will effectively disable then setting.';
$string['title'] = 'Title';
$string['title_help'] = 'This is the string that will be displayed as the title of this block. If this field is blank, no title will be displayed for this block.';
$string['total'] = 'Total';
$string['ungradeditem'] = '{$a} ungraded item';
$string['ungradeditems'] = '{$a} ungraded items';
$string['ungradedactivity'] = '(in {$a} activity)';
$string['ungradedactivities'] = '(in {$a} activities)';
$string['validimportfile'] = 'Configuration settings were successfully imported';
