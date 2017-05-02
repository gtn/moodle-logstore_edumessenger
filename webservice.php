<?php

/**
 * logic copied from webservice/pluginfile.php
 */

/**
 * AJAX_SCRIPT - exception will be converted into JSON
 */
define('AJAX_SCRIPT', true);

/**
 * NO_MOODLE_COOKIES - we don't want any cookie
 */
define('NO_MOODLE_COOKIES', true);


require __DIR__.'/inc.php';
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/webservice/lib.php');

// Allow CORS requests.
header('Access-Control-Allow-Origin: *');

//authenticate the user
$wstoken = required_param('wstoken', PARAM_ALPHANUM);
$function = required_param('wsfunction', PARAM_ALPHANUMEXT);
$webservicelib = new \webservice();
$authenticationinfo = $webservicelib->authenticate_user($wstoken);

// check if it is a moodle token
if ($authenticationinfo['service']->name != 'moodle_mobile_app') {
	throw new moodle_exception('not an moodle_mobile_app webservice token');
}

class simple_service {
	static function attach_forum_file() {
		$courseid = required_param('courseid', PARAM_INT);

		if (!$course = g::$DB->get_record('course', array('id' => $courseid))) {
			print_error('invalidcourse', 'block_simplehtml', $courseid);
		}

		require_login($course);

		$context = \context_course::instance($courseid);
	}
}

if (is_callable(['\block_exacomp\simple_service', $function])) {
	$ret = simple_service::$function();

	// pretty print if available (since php 5.4.0)
	echo defined('JSON_PRETTY_PRINT') ? json_encode($ret, JSON_PRETTY_PRINT) : json_encode($ret);
} else {
	throw new \moodle_exception("wsfunction '$function' not found");
}
