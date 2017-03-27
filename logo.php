<?php

require __DIR__.'/inc.php';

require_once $CFG->libdir . '/formslib.php';
class logstore_edumessenger_logo_form extends moodleform {
	function definition() {
		$mform = & $this->_form;

		$mform->addElement('header', 'comment', 'Logo Upload');
		$mform->addElement('html', '');
		$mform->addElement('filepicker', 'file', get_string("file"),null,array('accepted_types'=>'image'));
		//$mform->addRule('file', get_string('commentshouldnotbeempty'), 'required', null, 'client');

		$this->add_action_buttons();
	}
}

require_login();

require_capability('moodle/site:config', context_system::instance());

$url = '/admin/tool/log/store/edumessenger/logo.php';
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());

$mform = new logstore_edumessenger_logo_form();
if ($mform->is_cancelled()) {
	redirect(new moodle_url('/admin/settings.php?section=logsettingedumessenger'));
} else if ($mform->is_submitted()) {

	$fs = get_file_storage();

	// delete old logo
	$fs->delete_area_files(context_system::instance()->id, 'logstore_edumessenger', 'main_logo', 0);

	// save new logo
	$mform->save_stored_file('file', context_system::instance()->id	, 'logstore_edumessenger', 'main_logo', 0);
}

echo $OUTPUT->header();

if ($logo = logstore_edumessenger_get_logo_url()) {
	echo '<img style="max-width: 840px" src="'.$logo.'" />';
}

$mform->display();

echo $OUTPUT->footer();
