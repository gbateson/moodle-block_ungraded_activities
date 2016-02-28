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
 * blocks/ungraded_activities/block_ungraded_activities.php
 *
 * @package    blocks
 * @subpackage ungraded_activities
 * @copyright  2014 Gordon Bateson (gordon.bateson@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 2.0
 */

// disable direct access to this block
defined('MOODLE_INTERNAL') || die();

/**
 * block_ungraded_activities
 *
 * @copyright 2014 Gordon Bateson
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since     Moodle 2.0
 */
class block_ungraded_activities extends block_base {

    /* cached "selectmanual" config setting. it maybe set to true later, if we check for "quiz" items */
    var $qtype_random_selectmanual = false;

    /**
     * init
     */
    function init() {
        $this->title = get_string('blockname', 'block_ungraded_activities');
        $this->version = 2011111100;
    }

    /**
     * hide_header
     *
     * @return xxx
     */
    function hide_header() {
        return empty($this->config->title);
    }

    /**
     * applicable_formats
     *
     * @return xxx
     */
    function applicable_formats() {
        return array('course' => true); // 'course-view-social' ?
    }

    /**
     * instance_allow_config
     *
     * @return xxx
     */
    function instance_allow_config() {
        return true;
    }

    /**
     * specialization
     */
    function specialization() {
        $plugin = 'block_ungraded_activities';
        $defaults = array(
            'title'           => get_string('blockname', $plugin),
            'namelength'      => 22, // 0=no limit
            'headlength'      => 9, // 0=no limit
            'taillength'      => 9, // 0=no limit
            'showcountitems'  => 1, // 0=no, 1=yes
            'showuserlist'    => 1, // 0=no, 1=yes (collapsed), 2=yes (expanded)
            'showusertype'    => 2, // 0=all users, 1=enrolled users, 2=enrolled students
            'adduserlinks'    => 0, // 0=no, 1=add links to each user's profile
            'fixdaymonth'     => 1, // 0=no, 1=remove leading "0" from days and months in dates
            'showtimes'       => 1, // 0=no, 1=show time that each ungraded item was submitted
            'showassigns'     => 1,
            'showassignments' => 1,
            'showattendances' => 0,
            'showdatabases'   => 0,
            'showforums'      => 0,
            'showglossaries'  => 0,
            'showlessons'     => 0,
            'showquizzes'     => 0,
            'showworkshops'   => 0,
            'checkoverrides'  => 0, // 0=no, 1=ignore if overridden, 2=ignore if overridden after submission
            'moodledatefmt'   => 'strftimerecent', // 11 Nov, 10:12
            'customdatefmt'   => '%Y %b %d (%a) %H:%M', // 2011 Nov 11 (Fri) 10:12
        );

        if (empty($this->config)) {
            $this->config = new stdClass();
        }

        foreach ($defaults as $name => $items) {
            if (! isset($this->config->$name)) {
                $this->config->$name = $items;
            }
        }

        // load user-defined title (may be empty)
        $this->title = $this->config->title;
    }

    /**
     * get_activity_configs
     *
     * @return xxx
     */
    function get_activity_configs() {
        return array('showassigns',   'showassignments', 'showattendances',
                     'showdatabases', 'showforums',      'showglossaries',
                     'showlessons',   'showquizzes',     'showworkshops');
    }

    /**
     * instance_config_save
     *
     * @param xxx $config
     * @param xxx $pinned (optional, default=false)
     * @return xxx
     */
    function instance_config_save($config, $pinned=false) {
        global $DB;

        // do nothing if user hit the "cancel" button
        if (optional_param('cancel', 0, PARAM_INT)) {
            return true;
        }

        // convert activity array to string
        $name = 'showactivities';
        if (isset($config->$name) && is_array($config->$name)) {
            $config->$name = array_keys($config->$name, 1); // selected keys only
            $config->$name = preg_grep('/^[a-z][a-z0-9_]*$/', $config->$name);
            $config->$name = implode(',', $config->$name); // convert to string
        }

        // selected fields to be copied to other occurrences of this block
        $selected = array();

        $names = array_keys((array)$config);
        foreach ($names as $name) {
            $selectname = 'select_'.$name;
            if (empty($_POST[$selectname])) {
                continue;
            }
            switch ($name) {
                case 'textlength':
                    $langs = $translations = get_string_manager()->get_list_of_translations();
                    $langs = array_keys($langs);
                    array_unshift($langs, '');
                    foreach ($langs as $lang) {
                        $selected[] = 'namelength'.$lang;
                        $selected[] = 'headlength'.$lang;
                        $selected[] = 'taillength'.$lang;
                    }
                    break;
                default:
                    $selected[] = $name;
            }
        }

        // copy selected values to block instance in another course
        if (isset($config->mycourses) && is_array($config->mycourses)) {
            $contextids = implode(',', $config->mycourses);

            // get Activity List block instances in selected courses
            $select = "blockname = ? AND pagetypepattern = ? AND parentcontextid IN ($contextids)";
            $params = array($this->instance->blockname, 'course-view-*');
            if ($instances = $DB->get_records_select('block_instances', $select, $params)) {

                // user requires this capbility to update blocks
                $capability = 'block/taskchain_navigation:addinstance';

                // update values in the selected block instances
                foreach ($instances as $instance) {
                    if (class_exists('context')) {
                        $context = context::instance_by_id($instance->parentcontextid);
                    } else {
                        $context = get_context_instance_by_id($instance->parentcontextid);
                    }
                    if (has_capability($capability, $context)) {
                        $instance->config = unserialize(base64_decode($instance->configdata));
                        if (empty($instance->config)) {
                            $instance->config = new stdClass();
                        }
                        foreach ($selected as $name) {
                            if (empty($config->$name)) {
                                unset($instance->config->$name);
                            } else {
                                $instance->config->$name = $config->$name;
                            }
                        }
                        $instance->configdata = base64_encode(serialize($instance->config));
                        $DB->set_field('block_instances', 'configdata', $instance->configdata, array('id' => $instance->id));
                    }
                }
            }
        }
        unset($config->mycourses);

        //  save config settings as usual
        return parent::instance_config_save($config, $pinned);
    }

    /**
     * get_content
     *
     * @return xxx
     */
    function get_content() {
        global $CFG, $COURSE, $DB, $PAGE, $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = (object)array(
            'text' => '',
            'footer' => ''
        );

        if (empty($this->instance)) {
            return $this->content; // shouldn't happen !!
        }

        if (empty($COURSE)) {
            return $this->content; // shouldn't happen !!
        }

        if (empty($COURSE->context)) {
            $COURSE->context = self::context(CONTEXT_COURSE, $COURSE->id);
        }

        // quick check to filter out students
        if (! has_capability('moodle/grade:viewall', $COURSE->context)) {
            return $this->content;
        }

        $plugin = 'block_ungraded_activities';

        if ($this->config->showcountitems) {
            // set default message, in case we return no results
            $text = get_string('noitems', $plugin);
            $params = array('class' => 'ungradeditems');
            $this->content->text = html_writer::tag('p', $text, $params);
        }

        // define image for "refresh this page"
        $img = $PAGE->theme->pix_url('i/reload', 'core')->out();
        $img = html_writer::empty_tag('img', array('class' => 'refreshicon', 'src' => $img));

        // add the footer
        $this->content->footer .= html_writer::start_tag('p');
        $this->content->footer .= html_writer::start_tag('a', array('href' => $_SERVER['REQUEST_URI']));
        $this->content->footer .= get_string('refreshthispage', $plugin).' '.$img;
        $this->content->footer .= html_writer::end_tag('a');
        $this->content->footer .= html_writer::end_tag('p');

        // initialize $mods array
        $mods = array();

        // add required activity types to $mods array
        $this->add_mod($mods, 'assign',      'mod/assign:grade',     $COURSE->context);
        $this->add_mod($mods, 'assignment',  'mod/assignment:grade', $COURSE->context);
        $this->add_mod($mods, 'attendance',  'mod/attendance:manageattendances', $COURSE->context);
        $this->add_mod($mods, 'data',        'mod/data:rate',        $COURSE->context);
        $this->add_mod($mods, 'forum',       'mod/forum:rate',       $COURSE->context);
        $this->add_mod($mods, 'glossary',    'mod/glossary:rate',    $COURSE->context);
        $this->add_mod($mods, 'lesson',      'mod/lesson:manage',    $COURSE->context);
        $this->add_mod($mods, 'quiz',        'mod/quiz:grade',       $COURSE->context);
        $this->add_mod($mods, 'workshop',    'mod/workshop:grade',   $COURSE->context);

        if (empty($mods)) {
            return $this->content;
        }

        // add instances of required activities in this course to $mods array
        $this->add_instances($mods, $COURSE);

        // get the SQL to filter users
        list($select_users, $from_users, $where_users) = $this->get_users_sql($COURSE);

        $query = array();
        $this->add_query($query, $mods, 'assign',     $select_users, $from_users, $where_users);
        $this->add_query($query, $mods, 'assignment', $select_users, $from_users, $where_users);
        $this->add_query($query, $mods, 'attendance', $select_users, $from_users, $where_users);
        $this->add_query($query, $mods, 'data',       $select_users, $from_users, $where_users);
        $this->add_query($query, $mods, 'forum',      $select_users, $from_users, $where_users);
        $this->add_query($query, $mods, 'glossary',   $select_users, $from_users, $where_users);
        $this->add_query($query, $mods, 'lesson',     $select_users, $from_users, $where_users);
        $this->add_query($query, $mods, 'quiz',       $select_users, $from_users, $where_users);
        $this->add_query($query, $mods, 'workshop',   $select_users, $from_users, $where_users);

        if (empty($query)) {
            return $this->content;
        }

        $query = implode(' UNION ', $query);
        $query = preg_replace('/\{(\w+)\}/', $CFG->prefix.'$1', $query);
        $items = $DB->get_records_sql($query.' ORDER BY modname, lastname, firstname, timemodified');

        if (empty($items)) {
            return $this->content;
        }

        // sort items by activity
        $cmids = array();
        foreach ($items as $item) {
            $modname = $item->modname;
            $alt = get_string('modulename', $modname);
            $src = $PAGE->theme->pix_url('icon', $modname)->out();
            $mods[$modname]->icon = html_writer::empty_tag('img', array('class' => 'activityicon', 'src' => $src, 'alt' => $alt));

            $instanceid = $item->instanceid;
            $mods[$modname]->instances[$instanceid]->items[] = $item;

            $cmid = $mods[$modname]->instances[$instanceid]->id;
            $cmids[$cmid] = true;
        }

        $this->remove_empty_activities($mods, $cmids);

        // if this site allows manual graded questions in random questions,
        // we must identify, and remove from the $items array,
        // any 'essay' questions that were used in attempts at random questions,
        // but subsequently deleted from the question bank

        if ($this->qtype_random_selectmanual) {
            $this->remove_deleted_random_questions($mods, $cmids, $items);
        }

        $count_activities = count($cmids);
        $count_items = count($items);
        unset($items, $cmids);

        if ($count_items==0) {
            return $this->content;
        }

        // (re)start the content text
        $this->content->text = '';;

        // add message about number of item and activities

        if ($this->config->showcountitems) {
            if ($count_activities > 1) {
                $name = ($count_items==1 ? 'ungradeditem' : 'ungradeditems');
                $str  = get_string($name, $plugin, $count_items);
                $this->content->text .= '<p class="ungradeditems">'.$str.'</p>';

                $name = ($count_activities==1 ? 'ungradedactivity' : 'ungradedactivities');
                $str  = get_string($name, $plugin, $count_activities);
                $this->content->text .= '<p class="ungradedactivities">'.$str.'</p>';
            }
        }

        // get the CSS to initially show/hide the lists of ungraded items
        if ($this->config->showuserlist==1) {
            $itemsCSS = ' style="display:none;"';
        } else {
            $itemsCSS = '';
        }

        // set date format string
        if (! $fmt = $this->config->customdatefmt) {
            if (! $fmt = $this->config->moodledatefmt) {
                $fmt = 'strftimedatetime'; // default: 26 April 2011, 04:10 pm
            }
            $fmt = get_string($fmt);
        }

        // settings to remove leading zeros from dates
        // for userdate() in "moodlelib.php"
        $fixday = $this->config->fixdaymonth;
        $fixmonth = $this->config->fixdaymonth;

        if (function_exists('sesskey')) {
            $sesskey = '&sesskey='.sesskey();
        } else {
            $sesskey = '';
        }

        // cache for scales, if any, that are used
        $scales = array();

        // loop through $items and build html to display them
        // including javascript to allow show/hide the item lists
        foreach ($mods as $modname => $mod) {

            switch ($modname) {
                case 'assign'     : $itemname = get_string('submission', $modname); break;
                case 'assignment' : $itemname = get_string('submission', $modname); break;
                case 'attendance' : $itemname = get_string('session', $modname); break;
                case 'data'       : $itemname = get_string('entry', $modname); break;
                case 'forum'      : $itemname = get_string('post'); break;
                case 'glossary'   : $itemname = get_string('entry', $modname); break;
                case 'lesson'     : $itemname = get_string('essay', $modname); break;
                case 'quiz'       : $itemname = get_string('question', $modname); break;
                case 'worshop'    : $itemname = get_string('submission', $modname); break;
                default: $itemname = ''; // shouldn't happen
            }
            $linktitle = get_string('clicktograde', $plugin, $itemname);

            foreach ($mod->instances as $activityid => $activity) {
                $cmid = $activity->id;

                //javascript id to identify the div containing the ungraded items for this activity
                $itemsID = 'block_ungraded_activities_items_'.$cmid;

                //display the activity

                $this->content->text .= '<div class="ungradedactivity">';

                // link to this activity's main grading page
                $href = $CFG->wwwroot.'/mod/'.$modname;
                switch ($activity->modname) {
                    case 'assign'     : $href .= '/view.php?id='.$cmid.'&action=grading'; break;
                    case 'assignment' : $href .= '/submissions.php?id='.$cmid; break;
                    case 'attendance' : $href .= '/manage.php?id='.$cmid; break;
                    case 'data'       : $href .= '/view.php?id='.$cmid.'&mode=list&order=DESC'; break;
                    case 'forum'      : $href .= '/view.php?id='.$cmid; break;
                    case 'glossary'   : $href .= '/view.php?id='.$cmid.'&mode=date&sortkey=UPDATE&sortorder=DESC'; break;
                    case 'lesson'     : $href .= '/essay.php?id='.$cmid; break;
                    case 'quiz'       : $href .= '/report.php?id='.$cmid.'&mode=grading'; break;
                    case 'workshop'   : $href .= '/view.php?id='.$cmid; break;
                }
                $href = str_replace('&', '&amp;', $href); // for strict XHTML
                $this->content->text .= '<a href="'.$href.'">'.$mod->icon.'</a>';

                // format activity name
                $name = $this->trim_name($activity->name).' ['.count($activity->items).']';

                // add javascript to expand user list, if necessary
                if ($this->config->showuserlist==1) {
                    $onclick = "var x=(document.getElementById('$itemsID').style.display=='none');".
                               "document.getElementById('$itemsID').style.display=(x ? '' : 'none');".
                               "return false;";
                    $name = '<a href="#" onclick="'.$onclick.'">'.$name.'</a>';
                }

                $this->content->text .= $name;
                $this->content->text .= '</div>';

                if ($this->config->showuserlist) {
                    $this->content->text .= '<ul class="ungradeditems" id="'.$itemsID.'"'.$itemsCSS.'>';

                    // display details about each ungraded item
                    foreach ($activity->items as $item) {

                        $this->content->text .= '<li class="ungradeditem">';

                        // user object  - for print_user_picture() and fullname()
                        $user = new stdClass();
                        $fields = explode(',', self::get_userfields());
                        foreach ($fields as $field) {
                            if ($field=='id') {
                                $user->$field = $item->userid;
                            } else {
                                $user->$field = $item->$field;
                            }
                        }

                        if ($this->config->adduserlinks && $user->id) {
                            $this->content->text .= print_user_picture($item, $COURSE->id, null, 24, true, true, true);
                        }

                        // link to this item's grading/approval page
                        $href = $CFG->wwwroot.'/mod/'.$modname;
                        switch ($activity->modname) {
                            case 'assign'     : $href .= '/view.php?id='.$cmid.'&action=grade&rownum=0&userid='.$item->userid.'&attemptnumber='.$item->extrainfo; break;
                            case 'assignment' : $href .= '/submissions.php?id='.$cmid.'&userid='.$item->userid.'&mode=single&offset=1'; break;
                            case 'attendance' : $href .= '/take.php?id='.$cmid.'&sessionid='.$item->id.'&grouptype=0'; break;
                            case 'data'       : $href .= '/view.php?rid='.$item->id; break;
                            case 'forum'      : $href .= '/discuss.php?d='.$item->extrainfo.'&parent='.$item->id; break;
                            case 'glossary'   : $href .= '/view.php?id='.$cmid.($item->extrainfo ? '&mode=approval' : '&mode=entry&hook='.$item->id); break;
                            case 'lesson'     : $href .= '/essay.php?id='.$cmid.'&mode=grade&attemptid='.$item->id; break;
                            case 'quiz'       : $href .= '/report.php?id='.$cmid.'&mode=grading&attemptid='.$item->id; break;
                            case 'workshop'   :
                                if ($item->extrainfo) {
                                    // teacher needs to grade student peer asssesment
                                    $href .= '/viewassessment.php?aid='.$item->extrainfo;
                                } else {
                                    // teacher needs to assess a submission
                                    $href .= '/assess.php?id='.$cmid.'&sid='.$item->id;
                                }
                                break;
                        }

                        if ($this->config->checkoverrides) {
                            if ($item->overridden) {
                                $href = $CFG->wwwroot.'/grade/edit/tree/grade.php';
                                $href .= '?courseid='.$COURSE->id.'&id='.$item->gradeid;
                                $href .= '&gpr_type=report&gpr_plugin=grader';
                                $href .= '&gpr_courseid='.$COURSE->id;
                            }
                        }

                        $href = str_replace('&', '&amp;', $href.$sesskey); // for strict XHTML

                        if ($user->id) {
                            $userfullname = fullname($user);
                        } else {
                            if ($name = $this->trim_name($item->extrainfo)) {
                                $userfullname = $name; // attendance
                            } else {
                                $userfullname = $itemname;
                            }
                        }
                        $this->content->text .= '<a href="'.$href.'" title="'.$linktitle.'" onclick="'."this.target='_blank'".'">'.$userfullname.'</a>';

                        $grade = $item->grade;
                        $maxgrade = $item->maxgrade;

                        if ($grade===null || $grade==='' || $grade < 0) {
                            // use "new" icon to indicate no previous grade
                            $this->content->text .= ' <img src="../pix/i/new.gif" alt="'.get_string('new').'" />';
                        } else if ($maxgrade < 0) {
                            $grade = $this->convert_grade_to_scale($scales, abs($maxgrade), $grade);
                            $this->content->text .= ' ('.$grade.')';
                        } else if ($maxgrade==100) {
                            $this->content->text .= ' ('.$grade.'%)';
                        } else if ($maxgrade==1) {
                            $this->content->text .= ' ('.$grade.')';
                        } else {
                            $this->content->text .= ' ('.$grade.'/'.$maxgrade.')';
                        }

                        if ($this->config->showtimes) {
                            $this->content->text .= ' '.userdate($item->timemodified, $fmt, 99, $fixday, $fixmonth);
                        }

                        $this->content->text .= '</li>';
                    }

                    $this->content->text .= '</ul>';
                }
            }
        }

        return $this->content;
    }

    /**
     * add_mod
     *
     * @param xxx $mods (passed by reference)
     * @param xxx $modname
     * @param xxx $config
     * @param xxx $capability
     * @param xxx $context
     */
    function add_mod(&$mods, $modname, $capability, $context) {
        $name = 'showactivities';
        if (isset($this->config->$name) && in_array($modname, explode(',', $this->config->$name))) {
            if (get_capability_info($capability) && has_capability($capability, $context)) {
                $mods[$modname] = new stdClass();
                $mods[$modname]->instances = array();
            }
        }
    }

    /**
     * add_instances
     *
     * @param xxx $mods (passed by reference)
     */
    function add_instances(&$mods) {
        global $COURSE;
        // get cm ids of relevant activities in this course
        $modinfo = get_fast_modinfo($COURSE);
        foreach ($modinfo->sections as $sectionnum => $cmids) {
            foreach ($cmids as $cmid) {
                $modname = $modinfo->cms[$cmid]->modname;
                $instance = $modinfo->cms[$cmid]->instance;
                if (array_key_exists($modname, $mods)) {
                    $mods[$modname]->instances[$instance] = (object)array(
                        'id'      => $modinfo->cms[$cmid]->id,
                        'name'    => $modinfo->cms[$cmid]->name,
                        'modname' => $modinfo->cms[$cmid]->modname,
                        'items'   => array()
                    );
                }
            }
        }
    }

    /**
     * remove_empty_activities
     *
     * @param xxx $mods (passed by reference)
     * @param xxx $cmids (passed by reference)
     */
    function remove_empty_activities(&$mods, &$cmids) {
        // prune empty activities (i.e. no items) and mods (i.e. no activities)
        foreach ($mods as $modname => $mod) {
            foreach ($mod->instances as $activityid => $activity) {
                if (empty($activity->items)) {
                    unset($cmids[$activity->id]); // cm id
                    unset($mods[$modname]->instances[$activityid]);
                }
            }
            if (empty($mod->instances)) {
                unset($mods[$modname]);
            }
        }
    }

    /**
     * remove_deleted_random_questions
     *
     * @param xxx $mods (passed by reference)
     * @param xxx $cmids (passed by reference)
     * @param xxx $items (passed by reference)
     * @return xxx
     */
    function remove_deleted_random_questions(&$mods, &$cmids, &$items) {
        global $DB;
        // remove quiz questions used in random questions but subsequently deleted

        if (empty($mods['quiz'])) {
            return true;
        }

        $random_questions = array();
        foreach ($mods['quiz']->instances as $activityid => $activity) {
            foreach ($activity->items as $index => $item) {
                if ($id = $item->extrainfo) { // a question id
                    $random_questions[$id][$activityid][$index] = $item->mainid;
                }
            }
        }

        // check that questions which were used in $random_questions still exist
        if ($ids = implode(',', array_keys($random_questions))) {
            if ($ids = $DB->get_records_select('question', "id IN ($ids)", '', 'id,id')) {

                // remove any existing questions from the $random_questions array
                foreach ($ids as $id => $activities) {
                    unset($random_questions[$id]);
                }
            }
        }


        // any remaining $random_questions are those that have been deleted
        // so we remove them from the array of ungraded items,
        // because they can no longer be graded

        foreach ($random_questions as $id => $activities) {
            foreach ($activities as $activityid => $indexes) {
                $indexes = array_reverse($indexes, true);
                foreach ($indexes as $index => $mainid) {
                    unset($items[$mainid]);
                    unset($mods['quiz']->instances[$activityid]->items[$index]);
                }
            }
        }

        $this->remove_empty_activities($mods, $cmids);
    }

    /**
     * get_users_sql
     *
     * @return xxx
     */
    function get_users_sql() {
        global $COURSE;

        $select = self::get_userfields('u', null, 'userid');
        $from   = '';
        $where  = '';

        $preferencename = 'quizport_navigation_groupid_'.$COURSE->id;
        $groupid = get_user_preferences($preferencename, 0);
        $groupid = optional_param('groupid', $groupid, PARAM_INT);

        // get groupmode: 0=NOGROUPS, 1=VISIBLEGROUPS, 2=SEPARATEGROUPS
        $groupmode = groups_get_course_groupmode($COURSE);

        if ($groupmode==NOGROUPS || $groupmode==VISIBLEGROUPS || has_capability('moodle/site:accessallgroups', $COURSE->context)) {
            $accessallgroups = true;
        } else {
            $accessallgroups = false;
        }

        if ($groupid==0 && $accessallgroups) {
            // user can access all student users in the course
            switch ($this->config->showusertype) {
                case 0: // all users (i.e. anyone who ever attempted these activities)
                    break;
                case 1: // all users enrolled this course (includes teachers and admins)
                    $from  .= ' JOIN {role_assignments} ra ON (ra.userid = u.id)';
                    $where .= " AND ra.contextid = {$COURSE->context->id}";
                    break;
                case 2: // all students enrolled in this course
                    $from  .= ' JOIN {role_assignments} ra ON (ra.userid = u.id)'.
                              ' JOIN {role} r ON (r.id = ra.roleid)';
                    $where .= " AND ra.contextid = {$COURSE->context->id}".
                              " AND r.shortname = 'student'";
                    break;
            }
        } else {
            $groupids = 'SELECT id FROM {groups}'." WHERE courseid=$COURSE->id";
            if ($groupid) {
                $groupids .= " AND id=$groupid";
            }
            if ($accessallgroups==false) {
                // user can only see members in groups to which (s)he belongs
                // (e.g. non-editing teacher when groups are separate)
                $groupids = 'SELECT groupid FROM {group_members}'." WHERE userid=$USER->id AND groupid IN ($groupids)";
            }
            $where .= ' AND u.id IN (SELECT DISTINCT userid FROM {groups_members}'." WHERE groupid IN ($groupids))";
        }

        return array($select, $from, $where);
    }

    /**
     * add_query
     *
     * @param xxx $query (passed by reference)
     * @param xxx $mods
     * @param xxx $modname
     * @param xxx $config
     * @param xxx $select_users
     * @param xxx $from_users
     * @param xxx $where_users
     */
    function add_query(&$query, $mods, $modname, $select_users, $from_users, $where_users) {
        global $CFG, $COURSE, $DB, $USER;

        $name = 'showactivities';
        if (! $this->config->$name) {
            return; // shouldn't happen !!
        }
        if (! in_array($modname, explode(',', $this->config->$name))) {
            return; // this activity type not required
        }
        if (! array_key_exists($modname, $mods)) {
            return; // shouldn't happen !!
        }
        if (! $ids = implode(',', array_keys($mods[$modname]->instances))) {
            return; // no activities of required type in this course
        }

        switch ($modname) {

            case 'assign':
                $instanceid = 'a1.id';
                $timemodified = 's1.timemodified';
                $mainid = $DB->sql_concat("'assign_submission_'", 's1.id');
                $select = "$mainid AS mainid,".
                          " s1.id AS id, $timemodified AS timemodified,".
                          ' g1.grade AS grade, a1.grade AS maxgrade,'.
                          " '$modname' AS modname, $instanceid AS instanceid,".
                          ' s1.attemptnumber AS extrainfo';
                $from   = '{assign} a1'.
                          ' JOIN {assign_submission} s1 ON (a1.id = s1.assignment)'.
                          ' LEFT JOIN {assign_grades} g1 ON (a1.id = g1.assignment AND '.
                                                       's1.userid = g1.userid AND '.
                                                       's1.attemptnumber = g1.attemptnumber)'.
                          ' JOIN {user} u ON (s1.userid = u.id)';
                $where  = "a1.id IN ($ids)".
                          ' AND s1.latest = 1'.
                          ' AND (g1.id IS NULL OR g1.timemodified < s1.timemodified)';
                break;

            case 'assignment':
                $instanceid = 'a2.id';
                $timemodified = 's2.timemodified';
                $mainid = $DB->sql_concat("'assignment_submissions_'", 's2.id');
                $select = "$mainid AS mainid,".
                          " s2.id AS id, $timemodified AS timemodified,".
                          ' s2.grade AS grade, a2.grade AS maxgrade,'.
                          " '$modname' AS modname, $instanceid AS instanceid,".
                          ' 0 AS extrainfo';
                $from   = '{assignment} a2'.
                          ' JOIN {assignment_submissions} s2 ON (a2.id = s2.assignment)'.
                          ' JOIN {user} u ON (s2.userid = u.id)';
                $where  = "a2.id IN ($ids)".
                          ' AND s2.timemodified > s2.timemarked';
                break;

            case 'attendance':
                $timemodified = 'ase.sessdate';
                $instanceid = 'a.id';
                $mainid = $DB->sql_concat("'attendance_sessions_'", 'ase.id');
                $select = "$mainid AS mainid,".
                          " ase.id AS id, $timemodified AS timemodified,".
                          " '' AS grade, 100 AS maxgrade,".
                          " '$modname' AS modname, $instanceid AS instanceid,".
                          ' ase.description AS extrainfo';
                $from   = '{attendance} a'.
                          ' JOIN {attendance_sessions} ase ON (ase.attendanceid = a.id)'.
                          ' LEFT JOIN {attendance_log} al ON (al.sessionid = ase.id)'.
                          ' LEFT JOIN {user} u ON (u.id = al.studentid)';
                $where  = "a.id IN ($ids)".
                          ' AND ase.sessdate >= '.$COURSE->startdate.
                          ' AND ase.sessdate < '.time().
                          ' AND ase.lasttaken = 0'.
                          ' AND (al.id IS NULL OR u.id IS NULL)';
                // unset user filters
                $from_users = '';
                $where_users = '';
                break;

            case 'data':
                $timemodified = 'dr.timemodified';
                $instanceid = 'd.id';
                $mainid = $DB->sql_concat("'data_records_'", 'dr.id');
                $select = "$mainid AS mainid,".
                          " dr.id AS id, $timemodified AS timemodified,".
                          ' rt.rating AS grade, d.scale AS maxgrade,'.
                          " '$modname' AS modname, $instanceid AS instanceid,".
                          ' 0 AS extrainfo';
                $from   = '{data} d'.
                          ' JOIN {course_modules} cm ON (cm.instance = d.id)'.
                          ' JOIN {modules} m ON (m.id = cm.module AND m.name = '."'$modname'".')'.
                          ' JOIN {context} ctx ON (ctx.contextlevel = '.CONTEXT_MODULE.' AND ctx.instanceid = cm.id)'.
                          ' JOIN {data_records} dr ON (d.id = dr.dataid)'.
                          ' JOIN {user} u ON (dr.userid = u.id)'.
                          ' LEFT JOIN {rating} rt ON (dr.id = rt.itemid AND ctx.id = rt.contextid)';
                $where  = "d.id IN ($ids)".
                          ' AND ((d.approval = 1 AND dr.approved = 0) OR (d.assessed = 1 AND rt.rating IS NULL))';
                break;

            case 'forum':
                $timemodified = 'fp.modified';
                $instanceid = 'f.id';
                $mainid = $DB->sql_concat("'forum_posts_'", 'fp.id');
                $select = "$mainid AS mainid,".
                          " fp.id AS id, $timemodified AS timemodified,".
                          ' rt.rating AS grade, f.scale AS maxgrade,'.
                          " '$modname' AS modname, $instanceid AS instanceid,".
                          ' fd.id AS extrainfo'; // forum_discussion.id
                $from   = '{forum} f'.
                          ' JOIN {course_modules} cm ON (cm.instance = f.id)'.
                          ' JOIN {modules} m ON (m.id = cm.module AND m.name = '."'$modname'".')'.
                          ' JOIN {context} ctx ON (ctx.contextlevel = '.CONTEXT_MODULE.' AND ctx.instanceid = cm.id)'.
                          ' JOIN {forum_discussions} fd ON (f.id = fd.forum)'.
                          ' JOIN {forum_posts} fp ON (fd.id = fp.discussion)'.
                          ' JOIN {user} u ON (fp.userid = u.id)'.
                          ' LEFT JOIN {rating} rt ON (fp.id = rt.itemid AND ctx.id = rt.contextid)';
                $where  = "f.id IN ($ids)".
                          ' AND f.assessed > 0'.
                          ' AND (f.assesstimestart = 0 OR f.assesstimestart <= fp.modified)'.
                          ' AND (f.assesstimefinish = 0 OR f.assesstimefinish >= fp.modified)'.
                          ' AND rt.rating IS NULL';
                break;

            case 'glossary':
                $timemodified = 'ge.timemodified';
                $instanceid = 'g.id';
                $extrainfo = '(CASE WHEN (g.defaultapproval=0 AND ge.approved=0) THEN 1 ELSE 0 END)';
                $mainid = $DB->sql_concat("'glossary_entries_'", 'ge.id');
                $select = "$mainid AS mainid,".
                          " ge.id AS id, $timemodified AS timemodified,".
                          ' rt.rating AS grade, g.scale AS maxgrade,'.
                          " '$modname' AS modname, $instanceid AS instanceid,".
                          " $extrainfo AS extrainfo"; // 1=entry in need of approval
                $from   = '{glossary} g'.
                          ' JOIN {course_modules} cm ON (cm.instance = g.id)'.
                          ' JOIN {modules} m ON (m.id = cm.module AND m.name = '."'$modname'".')'.
                          ' JOIN {context} ctx ON (ctx.contextlevel = '.CONTEXT_MODULE.' AND ctx.instanceid = cm.id)'.
                          ' JOIN {glossary_entries} ge ON (ge.glossaryid = g.id)'.
                          ' JOIN {user} u ON (u.id = ge.userid)'.
                          ' LEFT JOIN {rating} rt ON (ge.id = rt.itemid AND ctx.id = rt.contextid)';
                $where  = "g.id IN ($ids)".
                          ' AND (('.
                              'g.defaultapproval = 0 AND ge.approved = 0'. // entry not yet approved
                          ') OR ('.
                              'g.assessed > 0'.
                              ' AND (g.assesstimestart = 0 OR g.assesstimestart <= ge.timemodified)'.
                              ' AND (g.assesstimefinish = 0 OR g.assesstimefinish >= ge.timemodified)'.
                              ' AND rt.rating IS NULL'. // i.e. glossary entry has not been rated yet
                          '))';
                break;

            case 'lesson':
                $ILIKE = sql_ilike();
                $ungraded = '%s:6:"graded";i:0;%'; // in lesson_attempts.useranswer

                $timemodified = 'lat.timeseen';
                $instanceid = 'l.id';
                $mainid = $DB->sql_concat("'lesson_attempts_'", 'lat.id');
                $select = "$mainid AS mainid,".
                          " lat.id AS id, $timemodified AS timemodified,".
                          " '' AS grade, lan.score AS maxgrade,".
                          " '$modname' AS modname, $instanceid AS instanceid,".
                          " '' AS extrainfo";
                $from   = '{lesson} l'.
                          ' JOIN {lesson_pages} lp ON (l.id = lp.lessonid)'.
                          ' JOIN {lesson_answers} lan ON (lp.id = lan.pageid)'. // AND l.id = lan.lessonid
                          ' JOIN {lesson_attempts} lat ON (lp.id = lat.pageid)'. // AND l.id = lat.lessonid
                          ' JOIN {user} u ON (lat.userid = u.id)';
                $where  = "l.id IN ($ids)".
                          ' AND lp.qtype = 10'. // essay question
                          " AND lat.useranswer $ILIKE '$ungraded'";
                break;

            case 'quiz':
                // get the proper "selectmanual" config setting
                // Site administration -> Miscellaneous -> Experimental
                $this->qtype_random_selectmanual = get_config('qtype_random', 'selectmanual');

                // copied from admin/qbehaviours.php
                $this->qtype_random_selectmanual = true;
                if ($disabled = get_config('question', 'disabledbehaviours')) {
                    $disabled = explode(',', $disabled);
                    if (in_array('manualgraded', $disabled)) {
                        $this->qtype_random_selectmanual = false;
                    }
                }

                // see "latest_step_for_qa_subquery()"
                // in "question/engine/datalib.php"
                $latest_question_attempt_step = 'SELECT MAX(qnasmax.sequencenumber) '.
                                                'FROM {question_attempt_steps} qnasmax '.
                                                'WHERE qnasmax.questionattemptid = qnas.questionattemptid';

                if ($this->qtype_random_selectmanual) {
                    // This site allows manually graded questions to be included in random questions

                    // For random questions, we need to know the actual question that was used in the quiz attempt
                    // so that we can check that it still exists in the question bank - it may have been deleted !!

                    // The actual question number used in a random question is stored in question_attempt_steps.answer
                    // which is formatted thus (see "question/type/random/questiontype.php"): ^random([0-9]+)-(.*)$

                    // get suitable SQL to find position of '-' following the question id
                    switch ($CFG->dbfamily) {
                        case 'mssql'  : $position = "CHARINDEX('-', qnas.answer)"; break;
                        case 'oracle' : $position = "INSTR(qnas.answer, '-')"; break;
                        default       : $position = "POSITION( '-' IN qnas.answer)";
                    }

                    // store random question's actual question id (or 0) in $extrainfo
                    $extrainfo = "CASE ".
                                 "WHEN (SUBSTRING(qnas.answer, 1, 6 ) = 'random') ".
                                 "THEN SUBSTRING(qnas.answer, 7, $position - 7) ".
                                 "ELSE '0' ".
                                 "END";
                    $qn_qtype  = "(qn.qtype = 'essay' OR qtype = 'random')";
                } else {
                    $extrainfo = "''";
                    $qn_qtype  = "qn.qtype = 'essay'";
                }

                $timemodified = 'qa.timemodified';
                $instanceid = 'q.id';
                $mainid = $DB->sql_concat("'question_attempt_steps_'", 'qnas.id');
                $select = "$mainid AS mainid,".
                          " qa.uniqueid AS id, $timemodified AS timemodified,".
                          ' qnas.fraction AS grade, qs.maxmark AS maxgrade,'.
                          " '$modname' AS modname, $instanceid AS instanceid,".
                          " $extrainfo AS extrainfo"; // '' OR real question id in random questions
                $from   = '{quiz} q'.
                          ' JOIN {quiz_attempts} qa ON (q.id = qa.quiz)'.
                          ' JOIN {user} u ON (u.id = qa.userid)'.
                          ' JOIN {question_attempts} qna ON (qna.questionusageid = qa.uniqueid)'.
                          ' JOIN {question_attempt_steps} qnas ON (qnas.questionattemptid = qna.id)'.
                          ' JOIN {question} qn ON (qn.id = qna.questionid)'.
                          ' JOIN {quiz_slots} qs ON (qs.questionid = qn.id AND qs.quizid = q.id)';
                $where  = 'q.id IN ('.$ids.')'.
                          ' AND qa.timefinish > 0 AND qa.preview = 0'.
                          " AND qnas.sequencenumber = ($latest_question_attempt_step)".
                          " AND qnas.fraction IS NULL".
                          " AND $qn_qtype"; // qn.type = 'essay' (OR qn.type = 'random')
                break;

            case 'workshop':

                // wa1 : (workshop_assessments) teacher assessments of submissions
                // wa2 : (workshop_assessments) teacher gradings of student assessments of submissions
                $unassessed = 'wa1.id IS NULL OR wa1.grade < 0';
                $ungraded   = 'wa2.id IS NOT NULL AND (wa2.teachergraded = 0 OR wa2.timegraded < ws.timecreated)';

                $extrainfo  = "(CASE WHEN ($ungraded) THEN wa2.id ELSE 0 END)";

                $userid = $USER->id;
                $timemodified = 'ws.timecreated';
                $instanceid = 'w.id';
                $mainid = $DB->sql_concat("'workshop_submissions_'", 'ws.id');
                $select = "$mainid AS mainid,".
                          " ws.id AS id, $timemodified AS timemodified,".
                          ' wa1.grade AS grade, w.grade AS maxgrade,'.
                          " '$modname' AS modname, $instanceid AS instanceid,".
                          " wa2.id AS extrainfo"; // student assessment
                $from   = '{workshop} w'.
                          ' JOIN {workshop_submissions} ws ON (w.id = ws.workshopid)'.
                          ' JOIN {user} u ON (ws.userid = u.id)'.
                          " LEFT JOIN {workshop_assessments} wa1 ON (ws.id = wa1.submissionid AND wa1.userid = $userid)".
                          " LEFT JOIN {workshop_assessments} wa2 ON (ws.id = wa2.submissionid AND wa2.userid <> $userid)";
                $where  = "w.id IN ($ids) AND (($unassessed) OR ($ungraded))";
                break;

            default: // shouldn't happen !!
                $select = '';
                $from   = '';
                $where  = '';

        } // end switch

        if ($select && $from && $where) {
            if ($this->config->checkoverrides) {
                switch ($this->config->checkoverrides) {
                    case 1: $override = 'gg.overridden = 0'; break;
                    case 2: $override = "gg.overridden < $timemodified"; break;
                }
                $select .= ', gg.id AS gradeid'.
                           ', gg.finalgrade AS finalgrade'.
                           ', gg.overridden AS overridden';
                $from   .= " LEFT JOIN {grade_items} gi ON (gi.itemtype = 'mod' AND gi.itemmodule = '$modname' AND gi.iteminstance = $instanceid)".
                           " LEFT JOIN {grade_grades} gg ON (gg.itemid = gi.id AND gg.userid = u.id)";
                $where  .= " AND (gg.overridden IS NULL OR $override)";
            }
            if ($select_users) {
                $select .= ','.$select_users;
            }
            if ($from_users) {
                $from .= $from_users;
            }
            if ($where_users) {
                $where .= $where_users;
            }

            $query[] = "SELECT $select FROM $from WHERE $where";
        }
    }

    /**
     * convert_grade_to_scale
     *
     * @param xxx $scales (passed by reference)
     * @param xxx $scaleid
     * @param xxx $grade
     * @return xxx
     */
    function convert_grade_to_scale(&$scales, $scaleid, $grade) {
        if (! isset($scales[$scaleid])) {
            if ($scale = get_field('scale', 'scale', 'id', $scaleid)) {
                $scale = explode(',', ','.$scale);
                $scale = array_map('trim', $scale);
            } else {
                $scale = array(); // shouldn't happen !!
            }
            $scales[$scaleid] = $scale;
        }
        if (! isset($scales[$scaleid][$grade])) {
            return ''; // shouldn't happen !!
        } else {
            return $scales[$scaleid][$grade];
        }
    }

    /**
     * trim_name
     *
     * @param xxx $name
     * @return
     */
    function trim_name($name) {
        $name = self::filter_text($name);
        $name = trim(strip_tags($name));

        list($namelength, $headlength, $taillength) = $this->get_namelength();

        $strlen = self::textlib('strlen', $name);

        if ($strlen > $namelength) {
            $head = self::textlib('substr', $name, 0, $headlength);
            $tail = self::textlib('substr', $name, $strlen - $taillength, $taillength);
            $name = $head.' ... '.$tail;
        }

        return $name;
    }

    /**
     * get_namelength
     *
     * @return array($namelength, $headlength, $taillength)
     */
    function get_namelength() {
        static $namelength = null;
        static $headlength = null;
        static $taillength = null;

        if (is_null($namelength)) {
            $lang = $this->get_lang_code();

            $namelength = 'namelength'.$lang;
            $headlength = 'headlength'.$lang;
            $taillength = 'taillength'.$lang;

            // get name length details for this language
            $namelength = $this->config->$namelength; // 22
            $headlength = $this->config->$headlength; // 9
            $taillength = $this->config->$taillength; // 9

            if ($namelength < 0) {
                $namelength = 0;
            }
            if ($headlength < 0) {
                $headlength = 0;
            }
            if ($taillength < 0) {
                $taillength = 0;
            }
        }

        return array($namelength, $headlength, $taillength);
    }

    /**
     * get_lang_code
     *
     * @return string
     */
    function get_lang_code() {
        static $lang = null;

        if (isset($lang)) {
            return $lang;
        }

        $lang = substr(current_language(), 0, 2);

        $namelength = 'namelength'.$lang;
        if (isset($this->config->$namelength)) {
            return $lang;
        }

        $lang = 'en';

        $namelength = 'namelength'.$lang;
        if (isset($this->config->$namelength)) {
            return $lang;
        }

        $lang = '';
        return $lang;
    }

    /**
     * context
     *
     * a wrapper method to offer consistent API to get contexts
     * in Moodle 2.0 and 2.1, we use self::context() function
     * in Moodle >= 2.2, we use static context_xxx::instance() method
     *
     * @param integer $contextlevel
     * @param integer $instanceid (optional, default=0)
     * @param int $strictness (optional, default=0 i.e. IGNORE_MISSING)
     * @return required context
     * @todo Finish documenting this function
     */
    static public function context($contextlevel, $instanceid=0, $strictness=0) {
        if (class_exists('context_helper')) {
            // use call_user_func() to prevent syntax error in PHP 5.2.x
            $class = context_helper::get_class_for_level($contextlevel);
            return call_user_func(array($class, 'instance'), $instanceid, $strictness);
        } else {
            return self::context($contextlevel, $instanceid);
        }
    }

    /**
     * textlib
     *
     * a wrapper method to offer consistent API for textlib class
     * in Moodle 2.0 and 2.1, $textlib is first initiated, then called
     * in Moodle 2.2 - 2.5, we use only static methods of the "textlib" class
     * in Moodle >= 2.6, we use only static methods of the "core_text" class
     *
     * @param string $method
     * @param mixed any extra params that are required by the textlib $method
     * @return result from the textlib $method
     * @todo Finish documenting this function
     */
    static public function textlib() {
        if (class_exists('core_text')) {
            // Moodle >= 2.6
            $textlib = 'core_text';
        } else if (method_exists('textlib', 'textlib')) {
            // Moodle 2.0 - 2.2
            $textlib = textlib_get_instance();
        } else {
            // Moodle 2.3 - 2.5
            $textlib = 'textlib';
        }
        $args = func_get_args();
        $method = array_shift($args);
        $callback = array($textlib, $method);
        return call_user_func_array($callback, $args);
    }

    /**
     * filter_text
     *
     * @param string $text
     * @return string
     */
    static public function filter_text($text) {
        global $COURSE, $PAGE;

        $filter = filter_manager::instance();

        if (method_exists($filter, 'setup_page_for_filters')) {
            // Moodle >= 2.3
            $filter->setup_page_for_filters($PAGE, $PAGE->context);
        }

        return $filter->filter_text($text, $PAGE->context);
    }

    /**
     * get_userfields
     *
     * @param string $tableprefix name of database table prefix in query
     * @param array  $extrafields extra fields to be included in result (do not include TEXT columns because it would break SELECT DISTINCT in MSSQL and ORACLE)
     * @param string $idalias     alias of id field
     * @param string $fieldprefix prefix to add to all columns in their aliases, does not apply to 'id'
     * @return string
     */
     static public function get_userfields($tableprefix='', array $extrafields=null, $idalias='id', $fieldprefix='') {
        if (class_exists('user_picture')) {
            // Moodle >= 2.6
            return user_picture::fields($tableprefix, $extrafields, $idalias, $fieldprefix);
        } else {
            // Moodle <= 2.5
            $fields = array('id', 'firstname', 'lastname', 'picture', 'imagealt', 'email');
            if ($tableprefix || $extrafields || $idalias) {
                if ($tableprefix) {
                    $tableprefix .= '.';
                }
                if ($extrafields) {
                    $fields = array_unique(array_merge($fields, $extrafields));
                }
                if ($idalias) {
                    $idalias = " AS $idalias";
                }
                if ($fieldprefix) {
                    $fieldprefix = " AS $fieldprefix";
                }
                foreach ($fields as $i => $field) {
                    $fields[$i] = "$tableprefix$field".($field=='id' ? $idalias : ($fieldprefix=='' ? '' : "$fieldprefix$field"));
                }
            }
            return implode(',', $fields); // 'u.id AS userid, u.username, u.firstname, u.lastname, u.picture, u.imagealt, u.email';
        }
    }
}
