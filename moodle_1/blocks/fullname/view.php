<?php

require('../../config.php');
require_once('../../lib/moodlelib.php');

require_login();

//get our config
$def_config = get_config('block_fullname');

$usercontext = context_user::instance($USER->id);
$PAGE->set_course($COURSE);
$PAGE->set_url('/blocks/fullname/view.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->set_pagelayout($def_config->pagelayout);
$PAGE->set_title(get_string('pluginname', 'block_fullname'));
$PAGE->navbar->add(get_string('pluginname', 'block_fullname'));


// start output to browser
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'block_fullname'),2);


echo '<div><h3>' . fullname($USER) . '</h3></div>';

echo '<br>' . $OUTPUT->user_picture($USER);

//send footer out to browser
echo $OUTPUT->footer();
return;


	