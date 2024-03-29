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
 * Dump.php file.
 *
 * @package    gradeexport_apogee
 * @author     Anthony Durif - Université Clermont Auvergne
 * @copyright  2019 Anthony Durif - Université Clermont Auvergne
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_MOODLE_COOKIES', true); // Session not used here.
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/grade/export/apogee/grade_export_apogee.php');

$id                 = required_param('id', PARAM_INT);
$groupid            = optional_param('groupid', 0, PARAM_INT);
$itemids            = required_param('itemids', PARAM_RAW);
$exportfeedback     = optional_param('export_feedback', 0, PARAM_BOOL);
$separator          = optional_param('separator', 'comma', PARAM_ALPHA);
$displaytype        = optional_param('displaytype', $CFG->grade_export_displaytype, PARAM_RAW);
$decimalpoints      = optional_param('decimalpoints', $CFG->grade_export_decimalpoints, PARAM_INT);
$onlyactive         = optional_param('export_onlyactive', 0, PARAM_BOOL);

if (!$course = $DB->get_record('course', array('id' => $id))) {
    throw new moodle_exception('invalidcourseid');
}

require_user_key_login('grade/export', $id); // We want different keys for each course.

$context = context_course::instance($id);
require_capability('moodle/grade:export', $context);
require_capability('gradeexport/apogee:view', $context);

if (!groups_group_visible($groupid, $COURSE)) {
    throw new moodle_exception('cannotaccessgroup', 'grades');
}

// Get all url parameters and create an object to simulate a form submission.
$formdata = grade_export::export_bulk_export_data($id, $itemids, $exportfeedback, $onlyactive, $displaytype,
        $decimalpoints, null, $separator);

$export = new grade_export_apogee($course, $formdata);
$export->print_grades();
