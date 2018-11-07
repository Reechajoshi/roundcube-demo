<?php








$CURR_SECTION = get_input_value('_superadmin', RCUBE_INPUT_GPC);
list($SECTIONS,) = rcmail_superadmin_menu($CURR_SECTION);
list($SECTIONS2,) = rcmail_superadmin_update($CURR_SECTION);


function rcube_superadmin_form( $attrib )
{
	global $RCMAIL, $CURR_SECTION, $SECTIONS;
	unset($attrib['form']);
	
	list($form_start, $form_end) = get_form_tags($attrib, 'save-superadmin', null,
		array('name' => '_superadmin', 'value' => $CURR_SECTION));
		
	$out = $form_start;
	
	foreach ($SECTIONS[$CURR_SECTION]['blocks'] as $idx => $block) {
		if (!empty($block['options'])) {
			$table = new html_table(array('cols' => 2));

			foreach ($block['options'] as $option) {
				if ($option['advanced'])
					$table->set_row_attribs('advanced');

				if(isset($option['title'])) {
					$table->add('title', $option['title']);
					$table->add(null, $option['content']);
				}
				else {
					$table->add(array('colspan' => 2), $option['content']);
				}
			}

			$out .= html::tag('fieldset', null, html::tag('legend', null, $block['name']) . $table->show($attrib));
		
		}
		else if (!empty($block['content'])) {
		  $out .= html::tag('fieldset', null, html::tag('legend', null, $block['name']) . $block['content']);
		}
	
	}
	return $out . $form_end;
}

function rcube_superadmin_edit_form( $attrib )
{
	global $EDIT_FORM, $RCMAIL, $CURR_SECTION, $SECTIONS2;
	unset($attrib['form']);
	
	$EDIT_FORM = null; // set to null because 2 forms are used
	
	list($form_start, $form_end) = get_form_tags($attrib, 'save-superadmin', null,
		array('name' => '_superadmin', 'value' => $CURR_SECTION));
		
	$out = $form_start;
	
	foreach ($SECTIONS2[$CURR_SECTION]['blocks'] as $idx => $block) {
		if (!empty($block['options'])) {
			$table = new html_table(array('cols' => 2));

			foreach ($block['options'] as $option) {
				if ($option['advanced'])
					$table->set_row_attribs('advanced');

				if(isset($option['title'])) {
					$table->add('title', $option['title']);
					$table->add(null, $option['content']);
				}
				else {
					$table->add(array('colspan' => 2), $option['content']);
				}
			}

			$out .= html::tag('fieldset', null, html::tag('legend', null, $block['name']) . $table->show($attrib));
		
		}
		else if (!empty($block['content'])) {
		  $out .= html::tag('fieldset', null, html::tag('legend', null, $block['name']) . $block['content']);
		}
	
	}
	return $out . $form_end;
}

function rcube_superadmin_title()
{
	global $CURR_SECTION;
	return rcube_label( $CURR_SECTION ) ;
}


$OUTPUT->include_script('list.js');

if( $RC_HELP->show_update_form )
{
	$OUTPUT->add_handler('superadminform', 'rcube_superadmin_form');
	$OUTPUT->add_handler('superadmineditform', 'rcube_superadmin_edit_form');
}
else
{
	$OUTPUT->add_handler('superadminform', 'rcube_superadmin_form');
}



$OUTPUT->add_handler('superadmin_menu_title', 'rcube_superadmin_title');
// display the form again
$OUTPUT->send('superadminedit');

