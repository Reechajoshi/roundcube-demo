<?php












$CURR_SECTION = get_input_value('_superadmin', RCUBE_INPUT_GPC);

$OUTPUT->set_pagetitle(rcube_label('superadmin'));   // sets the page title to admin
$OUTPUT->include_script('list.js');



function rcmail_superadmin_frame($attrib)
{
	global $OUTPUT;

	if (!$attrib['id'])
		$attrib['id'] = 'rcmSuperAdminFrame';

	return $OUTPUT->frame($attrib, true);
}

// register UI objects
$OUTPUT->add_handlers(array('superadminframe' => 'rcmail_superadmin_frame'));

$OUTPUT->send('superadmin');