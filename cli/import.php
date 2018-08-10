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
 * CLI script for tool_copier.
 *
 * @package     tool_copier
 * @subpackage  cli
 * @copyright   Matt Porritt <mattp@catalyst-au.net>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__.'/../../../../config.php');
require_once($CFG->libdir.'/clilib.php');
require_once($CFG->dirroot . '/course/externallib.php');

// Get the cli options.
list($options, $unrecognized) = cli_get_params(array(
    'help' => false
),
array(
    'h' => 'help'
));

$help =
"
Help message for tool_copier cli script.

Options:
-s, --source         The source Moodle id of the course to copy from.
-d, --dest           The destination Moodle id of the course to copy to.
-f, --file           A correctly formatted csv file (see README) to use.
-h, --help           Print out this help.

Example:
\$sudo -u www-data /usr/bin/php admin/tool/copier/cli/import.php\n
";

if ($unrecognized) {
    $unrecognized = implode("\n\t", $unrecognized);
    cli_error(get_string('cliunknownoption', 'admin', $unrecognized));
}

if ($options['help']) {
    cli_writeln($help);
    die();
}

// Call the core course import webservice that copies data between courses.
core_course_external::import_course($importfrom, $importto);

exit(0);
