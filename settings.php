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
 * eduMessenger log store plugin settings
 *
 * @package    logstore_edumessenger
 * @copyright  2016, Binoj David <dbinoj@gmail.com>
 * @author     Binoj David, https://www.dbinoj.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
	$healthurl = new moodle_url('/admin/tool/log/store/edumessenger/index.php', array('sesskey' => sesskey()));
	$ADMIN->add('reports', new admin_externalpage(
		'logstoreedumessengerhealth',
		new lang_string('reporttitle', 'logstore_edumessenger'),
		$healthurl,
		'moodle/site:config'
	));

	$settings->add(new admin_setting_configtext(
		'logstore_edumessenger/serverurl',
		'eduMessenger Server Adresse',
		'', 'https://messenger.dibig.at/services/messenger.php', PARAM_URL
	));

	$settings->add(new admin_setting_configpasswordunmask(
		'logstore_edumessenger/admintoken',
		'Admin-Tokens für den eduMessenger Webservice',
		'', ''
	));

	$settings->add(new admin_setting_configtext(
		'logstore_edumessenger/logofile',
		'Logo',
		'', '', PARAM_TEXT
	));

	$settings->add(new admin_setting_configcheckbox(
		'logstore_edumessenger/allow_registration',
		'Erlaube Registrierung neuer Konten',
		'', 1
	));
	$settings->add(new admin_setting_configcheckbox(
		'logstore_edumessenger/allow_course_creation',
		'Erlaube Verwaltung von Kursen / Gruppen',
		'', 1
	));

	$settings->add(new admin_settings_coursecat_select(
		'logstore_edumessenger/base_category',
		'Kursbereich für eduMessenger-Gruppen',
		'', 1
	));

	$settings->add(new admin_setting_configtext(
		'logstore_edumessenger/base_course',
		'Vorlagekurs für eduMessenger-Gruppen',
		'', '', PARAM_INT
	));

	$settings->add(new admin_setting_configtext(
		'logstore_edumessenger/etherpadurl',
		'Etherpad Url',
		'', 'https://etherpad.net/p/', PARAM_URL
	));

	/*
    $settings->add(new admin_setting_configtext(
        'logstore_edumessenger/hostname',
        new lang_string('hostname', 'logstore_edumessenger'),
        '', 'localhost', PARAM_HOST
    ));

    $settings->add(new admin_setting_configtext(
        'logstore_edumessenger/port',
        new lang_string('port', 'logstore_edumessenger'),
        '', '12201', PARAM_INT
    ));

    $settings->add(new admin_setting_configselect(
        'logstore_edumessenger/transport',
        new lang_string('transport', 'logstore_edumessenger'),
        '', 'udp', array(
        'udp' => new lang_string('udp', 'logstore_edumessenger'),
        'tcp' => new lang_string('tcp', 'logstore_edumessenger')
    )));
	*/

	$settings->add(new admin_setting_configtext(
		'logstore_edumessenger/tcptimeout',
		new lang_string('tcptimeout', 'logstore_edumessenger'),
		new lang_string('tcptimeout_desc', 'logstore_edumessenger'), '30', PARAM_INT
	));

	$settings->add(new admin_setting_configselect(
		'logstore_edumessenger/mode',
		new lang_string('mode', 'logstore_edumessenger'),
		'', 'realtime', array(
		'realtime' => new lang_string('realtime', 'logstore_edumessenger'),
		'background' => new lang_string('background', 'logstore_edumessenger'),
	)));
}