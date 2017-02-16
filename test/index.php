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
 * eduMessenger log store plugin page
 *
 * @package    logstore_edumessenger
 * @copyright  2016, Binoj David <dbinoj@gmail.com>
 * @author     Binoj David, https://www.dbinoj.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../inc.php');

require_login();
require_capability('moodle/site:config', \context_system::instance());

\logstore_edumessenger\edumessenger::set_debug(true);

$forum = $DB->get_record('forum', array('id' => 1), '*', MUST_EXIST);
$params = array(
	'context' => \context_module::instance(1),
	'objectid' => 1,
	'other' => array(
		'discussionid' => 3,
		'forumid' => $forum->id,
		'forumtype' => $forum->type,
	),
);
$event = \mod_forum\event\post_created::create($params);
$event->trigger();
$event = \mod_forum\event\post_created::create($params);
$event->trigger();

$params = array(
	'context' => \context_module::instance(1),
	'objectid' => 3,
	'other' => array('forumid' => 1),
);

$event = \mod_forum\event\discussion_created::create($params);
$event->trigger();

die('done');
