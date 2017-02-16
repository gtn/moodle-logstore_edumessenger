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
 * eduMessenger log store plugin language definitions
 *
 * @package    logstore_edumessenger
 * @copyright  2016, Binoj David <dbinoj@gmail.com>
 * @author     Binoj David, https://www.dbinoj.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'eduMessenger Logstore';
$string['pluginname_desc'] = 'A logstore plugin to ship logs to eduMessenger or any other GELF compatible logstores.';

$string['hostname'] = 'eduMessenger Input Hostname';
$string['port'] = 'Input Port';
$string['tcptimeout'] = 'TCP Connection Timeout';
$string['tcptimeout_desc'] = 'Used only for TCP Transports. Seconds to wait for a TCP connection to be established before timing out. If using TCP transport in realtime mode, consider setting this to a low value so that when eduMessenger is not reachable, user experience is not affected severely.';
$string['transport'] = 'Input Transport Type';
$string['udp'] = 'GELF UDP';
$string['tcp'] = 'GELF TCP';
$string['mode'] = 'Export mode';
$string['realtime'] = 'Realtime';
$string['background'] = 'Background';

$string['taskexport'] = 'Export to eduMessenger';

$string['reporttitle'] = 'eduMessenger Logstore Status';
$string['repstatus'] = 'Moodle->eduMessenger Status';
$string['nodestatus'] = 'eduMessenger Node Processing Status';
$string['never'] = 'Never';
$string['lastran'] = 'Last ran';
$string['progress'] = 'Progress';