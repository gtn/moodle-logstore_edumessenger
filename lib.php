<?php

defined('MOODLE_INTERNAL') || die;

require_once __DIR__.'/inc.php';

/**
 * @return stored_file
 */
function logstore_edumessenger_get_logo() {
	$fs = get_file_storage();

	$areafiles = $fs->get_area_files(context_system::instance()->id, 'logstore_edumessenger', 'main_logo', 0, 'itemid', false);

	return reset($areafiles) ?: null;
}

function logstore_edumessenger_get_logo_url() {
	if (!$file = logstore_edumessenger_get_logo()) {
		return null;
	}

	return moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), null, null, null);
}

function logstore_edumessenger_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
	// Make sure the user is logged in and has access to the module (plugins that are not course modules should leave out the 'cm' part).
	require_login($course, true, $cm);

	if (($filearea == 'main_logo' ) && ($file = logstore_edumessenger_get_logo())) {
		require_capability('moodle/site:config', context_system::instance());

		send_stored_file($file, 0, 0, $forcedownload, $options);
		exit;
	}
}
