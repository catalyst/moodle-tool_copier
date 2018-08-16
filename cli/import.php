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
        'source' => '',
        'dest' => '',
        'file' => '',
        'user' => '',
        'help' => false
),
array(
    's' => 'source',
    'd' => 'dest',
    'f' => 'file',
    'u' => 'user',
    'h' => 'help'
));

$help =
"
Help message for tool_copier cli script.

Options:
-s, --source    The source Moodle id of the course to copy from.
-d, --dest      The destination Moodle id of the course to copy to.
-f, --file      A correctly formatted csv file (see README) to use.
-u, --user      The user to perform the action as. (Must have perms to copy courses etc.)
-h, --help      Print out this help.

Example:
\$sudo -u www-data /usr/bin/php admin/tool/copier/cli/import.php  --user=2 --file='test.csv'\n
";

if ($unrecognized) {
    $unrecognized = implode("\n\t", $unrecognized);
    cli_error(get_string('cliunknownoption', 'admin', $unrecognized));
}

if ($options['help']) {
    cli_writeln($help);
    die();
}

// Log in as the supplied user.
// This feels bad, but there isn't really any other way to do it.
if ($options['user']) {
    global $DB;
    $user = $DB->get_record('user', array('id' => 2), '*', MUST_EXIST);
    enrol_check_plugins($user);
    \core\session\manager::set_user($user);
    set_login_session_preferences();
} else {
    cli_writeln($help);
    exit(1);
}

// If file has been provided parse it. If not take source and dest options.
if ($options['file']) {
    cli_writeln('We are processing a file of courses');

    // Check source file exists.
    $filename = trim($options['file']);
    $fp = fopen($filename, 'r');
    $count = 0;

    if ($fp) {
        // Go through CSV file line by line extracting data and inserting into database.
        while (($data = fgetcsv($fp)) !== false) {

            $source = $data[0]; // Column 1 is the source course ID.
            $dest = $data[1];  // Column 2 is the destination course ID.

            // Call the core course import webservice that copies data between courses.
            core_course_external::import_course($source, $dest);

            $count++;

        }

        echo "\n" . 'Processed: ' . $count . ' courses' . "\n";
        fclose($fp);
    } else {
        echo 'Unable to open file at location: ' . $filename;
        echo "\n";
        exit(1);
    }

} else if ($options['source'] && $options['dest']) {
    cli_writeln('We are processing a single course');
    // Call the core course import webservice that copies data between courses.
    core_course_external::import_course($options['source'], $options['dest']);
} else {
    cli_writeln($help);
}


exit(0);
