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
 | Author: Thomas Bruederli <roundcube@gmail.com>                        |
 +-----------------------------------------------------------------------+
*/
$CURR_SECTION = get_input_value('_manage', RCUBE_INPUT_GPC);

list($SECTIONS,) = rcmail_manage_add($CURR_SECTION);
list($SECTIONS1,) = rcmail_manage_users($CURR_SECTION);
list($SECTIONS2,) = rcmail_manage_update($CURR_SECTION);
list($SECTIONS3,) = rcmail_manage_delete($CURR_SECTION);

function rcube_manage_form_add($attrib)
{
	global $RCMAIL, $CURR_SECTION, $SECTIONS, $EDIT_FORM;
	unset($attrib['form']);
	$EDIT_FORM = null;

	list($form_start, $form_end) = get_form_tags($attrib, 'save-manage', null,
		array('name' => '_manage', 'value' => $CURR_SECTION));
	
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

function rcube_manage_form_users( $attrib )
{
	global $EDIT_FORM, $RCMAIL, $CURR_SECTION, $SECTIONS2;
	unset($attrib['form']);
	$EDIT_FORM = null; // set to null because 2 forms are used
	list($form_start, $form_end) = get_form_tags($attrib, 'save-manage', 'del_user',
		array('name' => '_manage', 'value' => $CURR_SECTION));
	
	 $out = $form_start;
	 foreach ($SECTIONS2[$CURR_SECTION]['blocks'] as $idx => $block) {
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

function rcube_manage_form_delete($attrib)
{
	global $EDIT_FORM, $RCMAIL, $CURR_SECTION, $SECTIONS3;
	unset($attrib['form']);
	$EDIT_FORM = null; // set to null because 2 forms are used
	list($form_start, $form_end) = get_form_tags($attrib, 'save-manage', 'del_user',
		array('name' => '_manage', 'value' => $CURR_SECTION));
	
	 $out = $form_start;
	 foreach ($SECTIONS3[$CURR_SECTION]['blocks'] as $idx => $block) {
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

function rcube_manage_form_update($attrib)
{
	global $EDIT_FORM, $RCMAIL, $CURR_SECTION, $SECTIONS1;
	unset($attrib['form']);
	$EDIT_FORM = null; // set to null because 2 forms are used
	list($form_start, $form_end) = get_form_tags($attrib, 'save-manage', 'form_id',
		array('name' => '_manage', 'value' => $CURR_SECTION));
	
	 $out = $form_start;
	 foreach ($SECTIONS1[$CURR_SECTION]['blocks'] as $idx => $block) {
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

function rcube_manage_title()
{
	global $CURR_SECTION;
	return rcube_label( $CURR_SECTION ) ;
}

// 'sectionname' => 'rcmail_prefs_section_name', not used because not there in html file
$OUTPUT->include_script('list.js');

if( $RC_HELP->show_update_form )
{
	$OUTPUT->add_handler('manageformusers', 'rcube_manage_form_users'); // update user form
}
else if( $RC_HELP->show_delete_form )
{
	$OUTPUT->add_handler('manageformdelete', 'rcube_manage_form_delete'); // delete user form
}
else if( ( $CURR_SECTION != 'accounts' ) && ( $CURR_SECTION != 'user_rules' ) ) // display the rcmail_manage_users only for options other than accounts because there is no blocks in accounts
{
	$OUTPUT->add_handler('manageformupdate', 'rcube_manage_form_update'); // users list
	$OUTPUT->add_handler('manageformadd', 'rcube_manage_form_add');  // add users form
}
else // display both forms
{
	$OUTPUT->add_handler('manageformadd', 'rcube_manage_form_add');  // add users form
}

$OUTPUT->add_handler('manage_menu_title', 'rcube_manage_title');
// display the form again
$OUTPUT->send('manageedit');

