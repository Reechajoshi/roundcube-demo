<?php

/*
 +-----------------------------------------------------------------------+
 | program/steps/settings/save_prefs.inc                                 |
 |                                                                       |
 | This file is part of the Roundcube Webmail client                     |
 | Copyright (C) 2005-2009, The Roundcube Dev Team                       |
 |                                                                       |
 | Licensed under the GNU General Public License version 3 or            |
 | any later version with exceptions for skins & plugins.                |
 | See the README file for a full license statement.                     |
 |                                                                       |
 | PURPOSE:                                                              |
 |   Save user preferences to DB and to the current session              |
 |                                                                       |
 +-----------------------------------------------------------------------+
 | Author: Macgregor Technology                                          |
 +-----------------------------------------------------------------------+
*/
$CURR_SECTION = get_input_value('_admin', RCUBE_INPUT_GPC);
list($SECTIONS,) = rcmail_admin_menu($CURR_SECTION);
function rcube_admin_form($attrib)
{
	global $RCMAIL, $CURR_SECTION, $SECTIONS;
	unset($attrib['form']);

	list($form_start, $form_end) = get_form_tags($attrib, 'save-admin', null,
		array('name' => '_admin', 'value' => $CURR_SECTION));
	
	 $out = $form_start;
	 foreach ($SECTIONS[$CURR_SECTION]['blocks'] as $idx => $block) {
		 if (!empty($block['options'])) {
      $table = new html_table(array('cols' => 2));

      foreach ($block['options'] as $option) {
        if ($option['advanced'])
	      $table->set_row_attribs('advanced');

        if (isset($option['title'])) {
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

function rcube_admin_title()
{
	global $CURR_SECTION;
	return rcube_label( $CURR_SECTION ) ;
}

// 'sectionname' => 'rcmail_prefs_section_name', not used because not there in html file

$OUTPUT->include_script('list.js');

/* ROUNDCUBE OBJECT IS ASSOCIATED WITH A METHOD */
/* $OUTPUT->add_handler('adminform', 'rcube_admin_form');
$OUTPUT->add_handler('admin_menu_title', 'rcube_admin_title'); */

$OUTPUT->add_handlers(array(
	'admin_menu_title' => 'rcube_admin_title',
	'adminform' => 'rcube_admin_form'
));
// display the form again
$OUTPUT->send('adminedit');

