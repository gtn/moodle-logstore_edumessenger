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
 * eduMessenger log store plugin
 *
 * @package    logstore_edumessenger
 * @copyright  2016, Binoj David <dbinoj@gmail.com>
 * @author     Binoj David, https://www.dbinoj.com
 * @thanks     2016, Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace logstore_edumessenger\task;

defined('MOODLE_INTERNAL') || die();

class export_task extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('taskexport', 'logstore_edumessenger');
    }

    /**
     * Export logs to eduMessenger.
     */
    public function execute() {
        global $DB;

        // Check mode.
        $config = get_config('logstore_edumessenger');
        if ($config->mode == 'realtime') {
            return true;
        }

        // Check Splunk works.
        $edumessenger = \logstore_edumessenger\edumessenger::instance();
        if (!$edumessenger->is_ready()) {
            return false;
        }

        // Things may have changed.
        $config = (object)$DB->get_records_menu('config_plugins', array('plugin' => 'logstore_edumessenger'), '', 'name, value');

        // Grab our last ID.
        $lastid = -1;
        if (isset($config->lastentry)) {
            $lastid = $config->lastentry;
        }

        // Grab the recordset.
        $rs = $DB->get_recordset_select('logstore_standard_log', 'id > ?', array($lastid), 'id', '*', 0, 100000);
        foreach ($rs as $row) {
            \logstore_edumessenger\edumessenger::log_standardentry($row);

            $lastid = $row->id;
        }
        $rs->close();

        // Flush eduMessenger.
        $edumessenger->flush();

        // Update config.
        set_config('lastentry', $lastid, 'logstore_edumessenger');
        set_config('lastrun', time(), 'logstore_edumessenger');

        return true;
    }
}