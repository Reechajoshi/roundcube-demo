/**
 * Roundcube functions for default skin interface
 *
 * Copyright (c) 2013, The Roundcube Dev Team
 *
 * The contents are subject to the Creative Commons Attribution-ShareAlike
 * License. It is allowed to copy, distribute, transmit and to adapt the work
 * by keeping credits to the original autors in the README file.
 * See http://creativecommons.org/licenses/by-sa/3.0/ for details.
 *
 * @license magnet:?xt=urn:btih:90dc5c0be029de84e523b9b3922520e79e0e6f08&dn=cc0.txt CC0-1.0
 */

function rcube_mail_ui()
{
  var env = {};
  var popups = {};
  var popupconfig = {
    forwardmenu:        { editable:1 },
    searchmenu:         { editable:1, callback:searchmenu },
    attachmentmenu:     { },
    listoptions:        { editable:1 },
    groupmenu:          { above:1 },
    mailboxmenu:        { above:1 },
    spellmenu:          { callback: spellmenu },
    'folder-selector':  { iconized:1 }
  };

  var me = this;
  var mailviewsplit;
  var compose_headers = {};
  var prefs;

  // export public methods
  this.set = setenv;
  this.init = init;
  this.init_tabs = init_tabs;
  this.show_about = show_about;
  this.show_popup = show_popup;
  this.toggle_popup = toggle_popup;
  this.add_popup = add_popup;
  this.set_searchmod = set_searchmod;
  this.set_searchscope = set_searchscope;
  this.show_uploadform = show_uploadform;
  this.show_header_row = show_header_row;
  this.hide_header_row = hide_header_row;
  this.update_quota = update_quota;
  this.get_pref = get_pref;
  this.save_pref = save_pref;
  this.folder_search_init = folder_search_init;

  /** MACGREGOR CHANGES **/
  this.add_block_email = add_block_email;
  this.add_oof_details = add_oof_details;
  this.toggle_oof_details = toggle_oof_details;
  this.remove_oof_details = remove_oof_details;
  this.add_forward_rule = add_forward_rule; //Add forward rule
  this.add_domain_record = add_domain_record;
  this.remove_block_email = remove_block_email;
  this.remove_row = remove_row; //Remove forward rule row
  this.remove_domain_record = remove_domain_record; //Remove Domain Record row
  // this.enable_outofoffice_tb = enable_outofoffice_tb; // NOT USED ANYMORE
  this.enable_custom_rule_tb = enable_custom_rule_tb;
  this.enable_folder_rule = enable_folder_rule;
  this.show_user_detail = show_user_detail;
  this.show_domain_details = show_domain_details;
  this.show_admin_user_details = show_admin_user_details;
  this.show_list_detail = show_list_detail;
  this.add_user_by_domain = add_user_by_domain;
  this.hide_user_detail = hide_user_detail;
  this.delete_users = delete_users;
  this.add_user_for_calendar_share = add_user_for_calendar_share;
  this.delete_admin = delete_admin;
  this.delete_list = delete_list;
  this.delete_domain_alias = delete_domain_alias;
  
  this.set_fwd_rule_header = set_fwd_rule_header;
  this.set_oof_rule_header = set_oof_rule_header;
  this.display_local_list_drodown = display_local_list_drodown;
  this.display_external_list_txt = display_external_list_txt;
  this.show_delte_list_confirm = show_delte_list_confirm;
  this.show_delete_admin_confirm = show_delete_admin_confirm;
  this.delete_domain_alias_confirm = delete_domain_alias_confirm;
  this.show_delete_domain_record_confirm = show_delete_domain_record_confirm;
  this.confirm_delete_list_members = confirm_delete_list_members;
  this.confirm_delte_alias = confirm_delte_alias;
  this.set_edit_list_name = set_edit_list_name;
  this.set_edit_list_name_del = set_edit_list_name_del;
  this.set_delete_domain_record = set_delete_domain_record;
  this.delete_user_alias = delete_user_alias;
  this.toggle_pwd = toggle_pwd;
  this.check_inArray = check_inArray;
  this.concat_array = concat_array;
  this.set_caldav_url = set_caldav_url;
  this.add_suffix_to_num = add_suffix_to_num;
  this.load_invitees = load_invitees;
  this.add_hidden_field_value = add_hidden_field_value;
  this.remove_hidden_field_value = remove_hidden_field_value;
  this.show_eula = show_eula;

  // set minimal mode on small screens (don't wait for document.ready)
  if (window.$ && document.body) {
    var minmode = get_pref('minimalmode');
    if (parseInt(minmode) || (minmode === null && $(window).height() < 850)) {
      $(document.body).addClass('minimal');
    }

    if (bw.tablet) {
      $('#viewport').attr('content', "width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0");
    }
  }


  /**
   *
   */
  function setenv(key, val)
  {
    env[key] = val;
  }

  /**
   * Get preference stored in browser
   */
  function get_pref(key)
  {
    if (!prefs) {
      prefs = rcmail.local_storage_get_item('prefs.larry', {});
    }

    // fall-back to cookies
    if (prefs[key] == null) {
      var cookie = rcmail.get_cookie(key);
      if (cookie != null) {
        prefs[key] = cookie;

        // copy value to local storage and remove cookie (if localStorage is supported)
        if (rcmail.local_storage_set_item('prefs.larry', prefs)) {
          rcmail.set_cookie(key, cookie, new Date());  // expire cookie
        }
      }
    }

    return prefs[key];
  }

  /**
   * Saves preference value to browser storage
   */
  function save_pref(key, val)
  {
    prefs[key] = val;

    // write prefs to local storage (if supported)
    if (!rcmail.local_storage_set_item('prefs.larry', prefs)) {
      // store value in cookie
      var exp = new Date();
      exp.setYear(exp.getFullYear() + 1);
      rcmail.set_cookie(key, val, exp);
    }
  }

  /**
   * Initialize UI
   * Called on document.ready
   */
  function init()
  {
    rcmail.addEventListener('message', message_displayed);
    
	/*** MACGREGOR CHANGES ***/
	/*** set the margin top for block email for header row ***/
	$("table").find('tr').each( function() {
		var adm_BlockEmail_headerRow = $(this).find('#rcmfd_header').closest('tr');
		// var domain_alias_headerRow = $(this).find('#_add_domain_record_name').closest('tr');
		
		$(adm_BlockEmail_headerRow).attr( 'class', 'blockEmail_header_row' );
		// $(domain_alias_headerRow).attr( 'class', 'blockEmail_header_row' );
	});
	
	/*** display distribtuon list add members divs for local and external ***/
	if( $( '#_edit_list_local' ).is( ':checked' ) )
	{
		$( '#add_member_to_list_div' ).parent().parent().show();
		$( '#_edit_list_ext_email' ).hide();
	}
	else
	{
		$( '#add_member_to_list_div' ).parent().parent().hide();
		$( '#_edit_list_ext_email' ).show();
	}
	
	if( $( '#_edit_list_external' ).is( ':checked' ) )
	{
		$( '#add_member_to_list_div' ).parent().parent().hide();
		$( '#_edit_list_ext_email' ).show();
	}
	else
	{
		$( '#add_member_to_list_div' ).parent().parent().show();
		$( '#_edit_list_ext_email' ).hide();
	}
	
	/*** copy folder rule filter header ***/
	$('#_folder_rule_header').click(function() {
		$('#_hidden_filter_header').val($('#_folder_rule_header option:selected').val());
	});
	
	/*** set forward rule header ***/
	/* $("#rcmfwrule_header").click(function() {
		set_fwd_rule_header();
	}); */
	
	/** END OF MACGREGOR CHANGES **/

    /*** prepare minmode functions ***/
    $('#taskbar a').each(function(i,elem){
      $(elem).append('<span class="tooltip">' + $('.button-inner', this).html() + '</span>')
    });

    $('#taskbar .minmodetoggle').click(function(e){
      var ismin = $(document.body).toggleClass('minimal').hasClass('minimal');
      save_pref('minimalmode', ismin?1:0);
      $(window).resize();
    });

    /***  mail task  ***/
    if (rcmail.env.task == 'mail') {
      rcmail.addEventListener('menu-open', menu_toggle)
        .addEventListener('menu-close', menu_toggle)
        .addEventListener('menu-save', save_listoptions)
        .addEventListener('responseafterlist', function(e){ switch_view_mode(rcmail.env.threading ? 'thread' : 'list', true) })
        .addEventListener('responseaftersearch', function(e){ switch_view_mode(rcmail.env.threading ? 'thread' : 'list', true) });

      var dragmenu = $('#dragmessagemenu');
      if (dragmenu.length) {
        rcmail.gui_object('dragmenu', 'dragmessagemenu');
        popups.dragmenu = dragmenu;
      }

      if (rcmail.env.action == 'show' || rcmail.env.action == 'preview') {
        rcmail.addEventListener('enable-command', enable_command)
          .addEventListener('aftershow-headers', function() { layout_messageview(); })
          .addEventListener('afterhide-headers', function() { layout_messageview(); });
        $('#previewheaderstoggle').click(function(e) {
            toggle_preview_headers();
            if (this.blur && !rcube_event.is_keyboard(e))
                this.blur();
            return false;
        });

        // add menu link for each attachment
        $('#attachment-list > li').each(function() {
          $(this).append($('<a class="drop" tabindex="0" aria-haspopup="true">Show options</a>')
              .bind('click keypress', function(e) {
                  if (e.type != 'keypress' || rcube_event.get_keycode(e) == 13) {
                      attachmentmenu(this, e);
                      return false;
                  }
              })
          );
        });

        if (get_pref('previewheaders') == '1') {
          toggle_preview_headers();
        }
      }
      else if (rcmail.env.action == 'compose') {
        rcmail.addEventListener('aftersend-attachment', show_uploadform)
          .addEventListener('add-recipient', function(p){ show_header_row(p.field, true); })
          .addEventListener('aftertoggle-editor', function(e){
            window.setTimeout(function(){ layout_composeview() }, 200);
            if (e && e.mode)
              $("select[name='editorSelector']").val(e.mode);
          });

        // Show input elements with non-empty value
        var f, v, field, fields = ['cc', 'bcc', 'replyto', 'followupto'];
        for (f=0; f < fields.length; f++) {
          v = fields[f]; field = $('#_'+v);
          if (field.length) {
            field.on('change', {v: v}, function(e) { if (this.value) show_header_row(e.data.v, true); });
            if (field.val() != '')
              show_header_row(v, true);
          }
        }

        $('#composeoptionstoggle').click(function(e){
          var expanded = $('#composeoptions').toggle().is(':visible');
          $('#composeoptionstoggle').toggleClass('remove').attr('aria-expanded', expanded ? 'true' : 'false');
          layout_composeview();
          save_pref('composeoptions', expanded ? '1' : '0');
          if (!rcube_event.is_keyboard(e))
            this.blur();
          return false;
        }).css('cursor', 'pointer');

        if (get_pref('composeoptions') !== '0') {
          $('#composeoptionstoggle').click();
        }

        // adjust hight when textarea starts to scroll
        $("textarea[name='_to'], textarea[name='_cc'], textarea[name='_bcc']").change(function(e){ adjust_compose_editfields(this); }).change();
        rcmail.addEventListener('autocomplete_insert', function(p){ adjust_compose_editfields(p.field); });

        // toggle compose options if opened in new window and they were visible before
        var opener_rc = rcmail.opener();
        if (opener_rc && opener_rc.env.action == 'compose' && $('#composeoptionstoggle', opener.document).hasClass('remove'))
          $('#composeoptionstoggle').click();

        new rcube_splitter({ id:'composesplitterv', p1:'#composeview-left', p2:'#composeview-right',
          orientation:'v', relative:true, start:206, min:170, size:12, render:layout_composeview }).init();
      }
      else if (rcmail.env.action == 'list' || !rcmail.env.action) {
        var previewframe = $('#mailpreviewframe').is(':visible');

        $('#mailpreviewtoggle').addClass(previewframe ? 'enabled' : 'closed').attr('aria-expanded', previewframe ? 'true' : 'false')
          .click(function(e) { toggle_preview_pane(e); return false; });
        $('#maillistmode').addClass(rcmail.env.threading ? '' : 'selected').click(function(e) { switch_view_mode('list'); return false; });
        $('#mailthreadmode').addClass(rcmail.env.threading ? 'selected' : '').click(function(e) { switch_view_mode('thread'); return false; });

        mailviewsplit = new rcube_splitter({ id:'mailviewsplitter', p1:'#mailview-top', p2:'#mailview-bottom',
          orientation:'h', relative:true, start:310, min:150, size:12, offset:4 });
        if (previewframe)
          mailviewsplit.init();

        rcmail.addEventListener('setquota', update_quota)
          .addEventListener('enable-command', enable_command)
          .addEventListener('afterimport-messages', show_uploadform);
      }
      else if (rcmail.env.action == 'get') {
        new rcube_splitter({ id:'mailpartsplitterv', p1:'#messagepartheader', p2:'#messagepartcontainer',
          orientation:'v', relative:true, start:226, min:150, size:12}).init();
      }

      if ($('#mailview-left').length) {
        new rcube_splitter({ id:'mailviewsplitterv', p1:'#mailview-left', p2:'#mailview-right',
          orientation:'v', relative:true, start:206, min:150, size:12, callback:render_mailboxlist, render:resize_leftcol }).init();
      }
    }
    /***  settings task  ***/
    else if (rcmail.env.task == 'settings') {
      rcmail.addEventListener('init', function(){
        var tab = '#settingstabpreferences';
        if (rcmail.env.action)
          tab = '#settingstab' + (rcmail.env.action.indexOf('identity')>0 ? 'identities' : rcmail.env.action.replace(/\./g, ''));

        $(tab).addClass('selected')
          .children().first().removeAttr('onclick').click(function() { return false; });
      });

      if (rcmail.env.action == 'folders') {
        new rcube_splitter({ id:'folderviewsplitter', p1:'#folderslist', p2:'#folder-details',
          orientation:'v', relative:true, start:266, min:180, size:12 }).init();

        rcmail.addEventListener('setquota', update_quota);

        folder_search_init($('#folderslist'));
      }
      else if (rcmail.env.action == 'identities') {
        new rcube_splitter({ id:'identviewsplitter', p1:'#identitieslist', p2:'#identity-details',
          orientation:'v', relative:true, start:266, min:180, size:12 }).init();
      }
      else if (rcmail.env.action == 'responses') {
        new rcube_splitter({ id:'responseviewsplitter', p1:'#identitieslist', p2:'#identity-details',
          orientation:'v', relative:true, start:266, min:180, size:12 }).init();
      }
      else if (rcmail.env.action == 'preferences' || !rcmail.env.action) {
        new rcube_splitter({ id:'prefviewsplitter', p1:'#sectionslist', p2:'#preferences-box',
          orientation:'v', relative:true, start:266, min:180, size:12 }).init();
      }
      else if (rcmail.env.action == 'edit-prefs') {
        var legend = $('#preferences-details fieldset.advanced legend'),
          toggle = $('<a href="#toggle"></a>')
            .text(env.toggleoptions)
            .attr('title', env.toggleoptions)
            .addClass('advanced-toggle');

        legend.click(function(e) {
          toggle.html($(this).hasClass('collapsed') ? '&#9650;' : '&#9660;');

          $(this).toggleClass('collapsed')
            .closest('fieldset').children('.propform').toggle()
        }).append(toggle).addClass('collapsed')

        // this magically fixes incorrect position of toggle link created above in Firefox 3.6
        if (bw.mz)
          legend.parents('form').css('display', 'inline');
      }
      
	  /** MACGREGOR CHANGES **/
	  /* admin splitter added from here */
	  else if (rcmail.env.action == 'admin' || !rcmail.env.action) {
        new rcube_splitter({ id:'testviewsplitter', p1:'#adminlist', p2:'#admin_details',
          orientation:'v', relative:true, start:266, min:180, size:12 }).init();
      }
	  /* rules splitter added from here */
	  else if (rcmail.env.action == 'rules' || !rcmail.env.action) {
        new rcube_splitter({ id:'testviewsplitter', p1:'#ruleslist', p2:'#rules_details',
          orientation:'v', relative:true, start:266, min:180, size:12 }).init();
      }
	  /* manage splitter added from here */
	  else if (rcmail.env.action == 'manage' || !rcmail.env.action) {
        new rcube_splitter({ id:'testviewsplitter', p1:'#managelist', p2:'#manage_details',
          orientation:'v', relative:true, start:266, min:180, size:12 }).init();
      }
	  /* superadmin splitter added from here */
	  else if (rcmail.env.action == 'superadmin' || !rcmail.env.action) {
        new rcube_splitter({ id:'testviewsplitter', p1:'#superadminlist', p2:'#superadmin_details',
          orientation:'v', relative:true, start:266, min:180, size:12 }).init();
      }
    }
    /***  addressbook task  ***/
    else if (rcmail.env.task == 'addressbook') {
      rcmail.addEventListener('afterupload-photo', show_uploadform)
        .addEventListener('beforepushgroup', push_contactgroup)
        .addEventListener('beforepopgroup', pop_contactgroup);

      if (rcmail.env.action == '') {
        new rcube_splitter({ id:'addressviewsplitterd', p1:'#addressview-left', p2:'#addressview-right',
          orientation:'v', relative:true, start:206, min:150, size:12, render:resize_leftcol }).init();
        new rcube_splitter({ id:'addressviewsplitter', p1:'#addresslist', p2:'#contacts-box',
          orientation:'v', relative:true, start:266, min:260, size:12 }).init();
      }

      var dragmenu = $('#dragcontactmenu');
      if (dragmenu.length) {
        rcmail.gui_object('dragmenu', 'dragcontactmenu');
        popups.dragmenu = dragmenu;
      }
    }

    // turn a group of fieldsets into tabs
    $('.tabbed').each(function(idx, elem){ init_tabs(elem); })

    // decorate select elements
    $('select.decorated').each(function(){
      if (bw.opera) {
        $(this).removeClass('decorated');
        return;
      }

      var select = $(this),
        parent = select.parent(),
        height = Math.max(select.height(), 26) - 2,
        width = select.width() - 22,
        title = $('option', this).first().text();

      if ($('option:selected', this).val() != '')
        title = $('option:selected', this).text();

      var overlay = $('<a class="menuselector" tabindex="-1"><span class="handle">' + title + '</span></a>')
        .css('position', 'absolute')
        .offset(select.position())
        .insertAfter(select);

      overlay.children().width(width).height(height).css('line-height', (height - 1) + 'px');

      if (parent.css('position') != 'absolute')
        parent.css('position', 'relative');

      // re-set original select width to fix click action and options width in some browsers
      select.width(overlay.width())
        .on(bw.mz ? 'change keyup' : 'change', function() {
          var val = $('option:selected', this).text();
          $(this).next().children().text(val);
        });

      select
        .on('focus', function(e){ overlay.addClass('focus'); })
        .on('blur', function(e){ overlay.removeClass('focus'); });
    });

    // set min-width to show all toolbar buttons
    var screen = $('body.minwidth');
    if (screen.length) {
      screen.css('min-width', $('.toolbar').width() + $('#quicksearchbar').width() + $('#searchfilter').width() + 30);
    }

    // don't use $(window).resize() due to some unwanted side-effects
    window.onresize = resize;
    resize();
  }

  /**
   * Update UI on window resize
   */
  function resize(e)
  {
    // resize in intervals to prevent lags and double onresize calls in Chrome (#1489005)
    var interval = e ? 10 : 0;

    if (rcmail.resize_timeout)
      window.clearTimeout(rcmail.resize_timeout);

    rcmail.resize_timeout = window.setTimeout(function() {
      if (rcmail.env.task == 'mail') {
        if (rcmail.env.action == 'show' || rcmail.env.action == 'preview')
          layout_messageview();
        else if (rcmail.env.action == 'compose')
          layout_composeview();
      }

      // make iframe footer buttons float if scrolling is active
      $('body.iframe .footerleft').each(function(){
        var footer = $(this),
          body = $(document.body),
          floating = footer.hasClass('floating'),
          overflow = body.outerHeight(true) > $(window).height();

        if (overflow != floating) {
          var action = overflow ? 'addClass' : 'removeClass';
          footer[action]('floating');
          body[action]('floatingbuttons');
        }
      });
    }, interval);
  }

  /**
   * Triggered when a new user message is displayed
   */
  function message_displayed(p)
  {
    var siblings = $(p.object).siblings('div');
    if (siblings.length)
      $(p.object).insertBefore(siblings.first());

    // show a popup dialog on errors
    if (p.type == 'error' && rcmail.env.task != 'login') {
      // hide original message object, we don't want both
      rcmail.hide_message(p.object);

      if (me.message_timer) {
        window.clearTimeout(me.message_timer);
      }

      if (!me.messagedialog) {
        me.messagedialog = $('<div>').addClass('popupdialog').hide();
      }

      var msg = p.message,
        dialog_close = function() {
          // check if dialog is still displayed, to prevent from js error
          me.messagedialog.is(':visible') && me.messagedialog.dialog('destroy').hide();
        };

      if (me.messagedialog.is(':visible') && me.messagedialog.text() != msg)
        msg = me.messagedialog.html() + '<p>' + p.message + '</p>';

      me.messagedialog.html(msg)
        .dialog({
          resizable: false,
          closeOnEscape: true,
          dialogClass: 'popupmessage ' + p.type,
          title: env.errortitle,
          close: dialog_close,
          position: ['center', 'center'],
          hide: {effect: 'fadeOut'},
          width: 420,
          minHeight: 90
        }).show();

      me.messagedialog.closest('div[role=dialog]').attr('role', 'alertdialog');

      if (p.timeout > 0)
        me.message_timer = window.setTimeout(dialog_close, p.timeout);
    }
  }


  /**
   * Adjust UI objects of the mail view screen
   */
  function layout_messageview()
  {
    $('#messagecontent').css('top', ($('#messageheader').outerHeight() + 1) + 'px');
    $('#message-objects div a').addClass('button');

    if (!$('#attachment-list li').length) {
      $('div.rightcol').hide().attr('aria-hidden', 'true');
      $('div.leftcol').css('margin-right', '0');
    }
  }


  function render_mailboxlist(splitter)
  {
    // TODO: implement smart shortening of long folder names
  }


  function resize_leftcol(splitter)
  {
    // STUB
  }

  function adjust_compose_editfields(elem)
  {
    if (elem.nodeName == 'TEXTAREA') {
      var $elem = $(elem), line_height = 14,  // hard-coded because some browsers only provide the outer height in elem.clientHeight
        content_height = elem.scrollHeight,
        rows = elem.value.length > 80 && content_height > line_height*1.5 ? 2 : 1;
      $elem.css('height', (line_height*rows) + 'px');
      layout_composeview();
    }
  }

  function layout_composeview()
  {
    var body = $('#composebody'),
      form = $('#compose-content'),
      bottom = $('#composeview-bottom'),
      w, h, bh, ovflw, btns = 0,
      minheight = 300,

    bh = form.height() - bottom.position().top;
    ovflw = minheight - bh;
    btns = ovflw > -100 ? 0 : 40;
    bottom.height(Math.max(minheight, bh));
    form.css('overflow', ovflw > 0 ? 'auto' : 'hidden');

    w = body.parent().width() - 5;
    h = body.parent().height() - 8;
    body.width(w).height(h);

    $('#composebodycontainer > div').width(w+8);
    $('#composebody_ifr').height(h + 4 - $('div.mce-toolbar').height());
    $('#googie_edit_layer').width(w).height(h);
//    $('#composebodycontainer')[(btns ? 'addClass' : 'removeClass')]('buttons');
//    $('#composeformbuttons')[(btns ? 'show' : 'hide')]();

    var abooks = $('#directorylist');
    if (abooks.length)
      $('#compose-contacts .scroller').css('top', abooks.position().top + abooks.outerHeight());
  }


  function update_quota(p)
  {
    var element = $('#quotadisplay'), menu = $('#quotamenu'),
      step = 24, step_count = 20,
      y = p.total ? Math.ceil(p.percent / 100 * step_count) * step : 0;

    // never show full-circle if quota is close to 100% but below.
    if (p.total && y == step * step_count && p.percent < 100)
      y -= step;

    element.css('background-position', '0 -' + y + 'px');

    if (p.table) {
      if (!menu.length)
        menu = $('<div id="quotamenu" class="popupmenu">').appendTo($('body'));

      menu.html(p.table);
      element.css('cursor', 'pointer').off('click').on('click', function(e) {
        return rcmail.command('menu-open', 'quotamenu', e.target, e);
      });
    }
  }

  function folder_search_init(container)
  {
    // animation to unfold list search box
    $('.boxtitle a.search', container).click(function(e) {
      var title = $('.boxtitle', container),
        box = $('.listsearchbox', container),
        dir = box.is(':visible') ? -1 : 1,
        height = 34 + ($('select', box).length ? 22 : 0);

      box.slideToggle({
        duration: 160,
        progress: function(animation, progress) {
          if (dir < 0) progress = 1 - progress;
            $('.scroller', container).css('top', (title.outerHeight() + height * progress) + 'px');
        },
        complete: function() {
          box.toggleClass('expanded');
          if (box.is(':visible')) {
            box.find('input[type=text]').focus();
            height = 34 + ($('select', box).length ? $('select', box).outerHeight() + 4 : 0);
            $('.scroller', container).css('top', (title.outerHeight() + height) + 'px');
          }
          else {
            $('a.reset', box).click();
          }
          // TODO: save state in localStorage
        }
      });

      return false;
    });
  }

  function enable_command(p)
  {
    if (p.command == 'reply-list' && rcmail.env.reply_all_mode == 1) {
      var label = rcmail.gettext(p.status ? 'replylist' : 'replyall');
      if (rcmail.env.action == 'preview')
        $('a.button.replyall').attr('title', label);
      else
        $('a.button.reply-all').text(label).attr('title', label);
    }
  }


  /**
   * Register a popup menu
   */
  function add_popup(popup, config)
  {
    var obj = popups[popup] = $('#'+popup);
    obj.appendTo(document.body);  // move it to top for proper absolute positioning

    if (obj.length)
      popupconfig[popup] = $.extend(popupconfig[popup] || {}, config || {});
  }

  /**
   * Trigger for popup menus
   */
  function toggle_popup(popup, e, config)
  {
    // auto-register menu object
    if (config || !popupconfig[popup])
      add_popup(popup, config);

    return rcmail.command('menu-open', popup, e.target, e);
  }

  /**
   * (Deprecated) trigger for popup menus
   */
  function show_popup(popup, show, config)
  {
    // auto-register menu object
    if (config || !popupconfig[popup])
      add_popup(popup, config);

    config = popupconfig[popup] || {};
    var ref = $(config.link ? config.link : '#'+popup+'link'),
      pos = ref.offset();
    if (ref.has('.inner'))
      ref = ref.children('.inner');

    // fire command with simulated mouse click event
    return rcmail.command('menu-open',
      { menu:popup, show:show },
      ref.get(0),
      $.Event('click', { target:ref.get(0), pageX:pos.left, pageY:pos.top, clientX:pos.left, clientY:pos.top }));
  }


  /**
   * Show/hide the preview pane
   */
  function toggle_preview_pane(e)
  {
    var button = $(e.target),
      frame = $('#mailpreviewframe'),
      visible = !frame.is(':visible'),
      splitter = mailviewsplit.pos || parseInt(get_pref('mailviewsplitter') || 320),
      topstyles, bottomstyles, uid;

    frame.toggle();
    button.toggleClass('enabled closed').attr('aria-expanded', visible ? 'true' : 'false');

    if (visible) {
      $('#mailview-top').removeClass('fullheight').css({ bottom:'auto' });
      $('#mailview-bottom').css({ height:'auto' }).show();

      rcmail.env.contentframe = 'messagecontframe';
      if (uid = rcmail.message_list.get_single_selection())
        rcmail.show_message(uid, false, true);

      // let the splitter set the correct size and position
      if (mailviewsplit.handle) {
        mailviewsplit.handle.show();
        mailviewsplit.resize();
      }
      else
        mailviewsplit.init();
    }
    else {
      rcmail.env.contentframe = null;
      rcmail.show_contentframe(false);

      $('#mailview-top').addClass('fullheight').css({ height:'auto', bottom:'0px' });
      $('#mailview-bottom').css({ top:'auto', height:'0px' }).hide();

      if (mailviewsplit.handle)
        mailviewsplit.handle.hide();
    }

    if (rcmail.message_list) {
      if (visible && uid)
          rcmail.message_list.scrollto(uid);
      rcmail.message_list.resize();
    }

    rcmail.command('save-pref', { name:'preview_pane', value:(visible?1:0) });
  }


  /**
   * Switch between short and full headers display in message preview
   */
  function toggle_preview_headers()
  {
    $('#preview-shortheaders').toggle();
    var full = $('#preview-allheaders').toggle(),
      button = $('a#previewheaderstoggle');

    // add toggle button to full headers table
    if (full.is(':visible'))
      button.attr('href', '#hide').removeClass('add').addClass('remove').attr('aria-expanded', 'true');
    else
      button.attr('href', '#details').removeClass('remove').addClass('add').attr('aria-expanded', 'false');

    save_pref('previewheaders', full.is(':visible') ? '1' : '0');
  }


  /**
   *
   */
  function switch_view_mode(mode, force)
  {
    if (force || !$('#mail'+mode+'mode').hasClass('disabled')) {
      $('#maillistmode, #mailthreadmode').removeClass('selected').attr('tabindex', '0').attr('aria-disabled', 'false');
      $('#mail'+mode+'mode').addClass('selected').attr('tabindex', '-1').attr('aria-disabled', 'true');
    }
  }


  /**** popup menu callbacks ****/

  /**
   * Handler for menu-open and menu-close events
   */
  function menu_toggle(p)
  {
    if (p && p.name == 'messagelistmenu') {
      show_listoptions(p);
    }
    else if (p) {
      // adjust menu position according to config
      var config = popupconfig[p.name] || {},
        ref = $(config.link || '#'+p.name+'link'),
        visible = p.obj && p.obj.is(':visible'),
        above = config.above;

      // fix position according to config
      if (p.obj && visible && ref.length) {
        var parent = ref.parent(),
          win = $(window), pos;

        if (parent.hasClass('dropbutton'))
          ref = parent;

        if (config.above || ref.hasClass('dropbutton')) {
          pos = ref.offset();
          p.obj.css({ left:pos.left+'px', top:(pos.top + (config.above ? -p.obj.height() : ref.outerHeight()))+'px' });
        }
      }

      // add the right classes
      if (p.obj && config.iconized) {
        p.obj.children('ul').addClass('iconized');
      }

      // apply some data-attributes from menu config
      if (p.obj && config.editable)
        p.obj.attr('data-editable', 'true');

      // trigger callback function
      if (typeof config.callback == 'function') {
        config.callback(visible, p);
      }
    }
  }

  function searchmenu(show)
  {
    if (show && rcmail.env.search_mods) {
      var n, all,
        obj = popups['searchmenu'],
        list = $('input:checkbox[name="s_mods[]"]', obj),
        mbox = rcmail.env.mailbox,
        mods = rcmail.env.search_mods,
        scope = rcmail.env.search_scope || 'base';

      if (rcmail.env.task == 'mail') {
        if (scope == 'all')
          mbox = '*';
        mods = mods[mbox] ? mods[mbox] : mods['*'];
        all = 'text';
        $('input:radio[name="s_scope"]').prop('checked', false).filter('#s_scope_'+scope).prop('checked', true);
      }
      else {
        all = '*';
      }

      if (mods[all])
        list.map(function() {
          this.checked = true;
          this.disabled = this.value != all;
        });
      else {
        list.prop('disabled', false).prop('checked', false);
        for (n in mods)
          $('#s_mod_' + n).prop('checked', true);
      }
    }
  }

  function attachmentmenu(elem, event)
  {
    var id = elem.parentNode.id.replace(/^attach/, '');

    $('#attachmenuopen').unbind('click').attr('onclick', '').click(function(e) {
      return rcmail.command('open-attachment', id, this);
    });

    $('#attachmenudownload').unbind('click').attr('onclick', '').click(function() {
      rcmail.command('download-attachment', id, this);
    });

    popupconfig.attachmentmenu.link = elem;
    rcmail.command('menu-open', {menu: 'attachmentmenu', id: id}, elem, event);
  }

  function spellmenu(show, p)
  {
    var k, link, li,
      lang = rcmail.spellcheck_lang(),
      ul = $('ul', p.obj);

    if (!ul.length) {
      ul = $('<ul class="toolbarmenu selectable" role="menu">');

      for (k in rcmail.env.spell_langs) {
        li = $('<li role="menuitem">');
        link = $('<a href="#'+k+'" tabindex="0"></a>').text(rcmail.env.spell_langs[k])
          .addClass('active').data('lang', k)
          .bind('click keypress', function(e) {
              if (e.type != 'keypress' || rcube_event.get_keycode(e) == 13) {
                  rcmail.spellcheck_lang_set($(this).data('lang'));
                  rcmail.hide_menu('spellmenu', e);
                  return false;
              }
          });

        link.appendTo(li);
        li.appendTo(ul);
      }

      ul.appendTo(p.obj);
    }

    // select current language
    $('li', ul).each(function() {
      var el = $('a', this);
      if (el.data('lang') == lang)
        el.addClass('selected').attr('aria-selected', 'true');
      else if (el.hasClass('selected'))
        el.removeClass('selected').removeAttr('aria-selected');
    });
  }


  /**
   *
   */
  function show_listoptions(p)
  {
    var $dialog = $('#listoptions');

    // close the dialog
    if ($dialog.is(':visible')) {
      $dialog.dialog('close', p.originalEvent);
      return;
    }

    // set form values
    $('input[name="sort_col"][value="'+rcmail.env.sort_col+'"]').prop('checked', true);
    $('input[name="sort_ord"][value="DESC"]').prop('checked', rcmail.env.sort_order == 'DESC');
    $('input[name="sort_ord"][value="ASC"]').prop('checked', rcmail.env.sort_order != 'DESC');

    // set checkboxes
    $('input[name="list_col[]"]').each(function() {
      $(this).prop('checked', $.inArray(this.value, rcmail.env.listcols) != -1);
    });

    $dialog.dialog({
      modal: true,
      resizable: false,
      closeOnEscape: true,
      title: null,
      open: function(e) {
        setTimeout(function(){ $dialog.find('a, input:not(:disabled)').not('[aria-disabled=true]').first().focus(); }, 100);
      },
      close: function(e) {
        $dialog.dialog('destroy').hide();
        if (e.originalEvent && rcube_event.is_keyboard(e.originalEvent))
          $('#listmenulink').focus();
      },
      minWidth: 500,
      width: $dialog.width()+25
    }).show();
  }


  /**
   *
   */
  function save_listoptions(p)
  {
    $('#listoptions').dialog('close');

    if (rcube_event.is_keyboard(p.originalEvent))
      $('#listmenulink').focus();

    var sort = $('input[name="sort_col"]:checked').val(),
      ord = $('input[name="sort_ord"]:checked').val(),
      cols = $('input[name="list_col[]"]:checked')
        .map(function(){ return this.value; }).get();

    rcmail.set_list_options(cols, sort, ord, rcmail.env.threading);
  }


  /**
   *
   */
  function set_searchmod(elem)
  {
    var all, m, task = rcmail.env.task,
      mods = rcmail.env.search_mods,
      mbox = rcmail.env.mailbox,
      scope = $('input[name="s_scope"]:checked').val();

    if (scope == 'all')
      mbox = '*';

    if (!mods)
      mods = {};

    if (task == 'mail') {
      if (!mods[mbox])
        mods[mbox] = rcube_clone_object(mods['*']);
      m = mods[mbox];
      all = 'text';
    }
    else { //addressbook
      m = mods;
      all = '*';
    }

    if (!elem.checked)
      delete(m[elem.value]);
    else
      m[elem.value] = 1;

    // mark all fields
    if (elem.value == all) {
      $('input:checkbox[name="s_mods[]"]').map(function() {
        if (this == elem)
          return;

        this.checked = true;
        if (elem.checked) {
          this.disabled = true;
          delete m[this.value];
        }
        else {
          this.disabled = false;
          m[this.value] = 1;
        }
      });
    }

    rcmail.set_searchmods(m);
  }

  function set_searchscope(elem)
  {
    rcmail.set_searchscope(elem.value);
  }

  function push_contactgroup(p)
  {
    // lets the contacts list swipe to the left, nice!
    var table = $('#contacts-table'),
      scroller = table.parent().css('overflow', 'hidden');

    table.clone()
      .css({ position:'absolute', top:'0', left:'0', width:table.width()+'px', 'z-index':10 })
      .appendTo(scroller)
      .animate({ left: -(table.width()+5) + 'px' }, 300, 'swing', function(){
        $(this).remove();
        scroller.css('overflow', 'auto')
      });
  }

  function pop_contactgroup(p)
  {
    // lets the contacts list swipe to the left, nice!
    var table = $('#contacts-table'),
      scroller = table.parent().css('overflow', 'hidden'),
      clone = table.clone().appendTo(scroller);

      table.css({ position:'absolute', top:'0', left:-(table.width()+5) + 'px', width:table.width()+'px', height:table.height()+'px', 'z-index':10 })
        .animate({ left:'0' }, 300, 'linear', function(){
        clone.remove();
        $(this).css({ position:'relative', left:'0', width:'100%', height:'auto', 'z-index':1 });
        scroller.css('overflow', 'auto')
      });
  }

  function show_uploadform(e)
  {
    var $dialog = $('#upload-dialog');

    // close the dialog
    if ($dialog.is(':visible')) {
      $dialog.dialog('close');
      return;
    }

    // add icons to clone file input field
    if (rcmail.env.action == 'compose' && !$dialog.data('extended')) {
      $('<a>')
        .addClass('iconlink add')
        .attr('href', '#add')
        .html('Add')
        .appendTo($('input[type="file"]', $dialog).parent())
        .click(add_uploadfile);
      $dialog.data('extended', true);
    }

    $dialog.dialog({
      modal: true,
      resizable: false,
      closeOnEscape: true,
      title: $dialog.attr('title'),
      open: function(e) {
        if (!document.all)
          $('input[type=file]', $dialog).first().click();
      },
      close: function() {
        try { $('#upload-dialog form').get(0).reset(); }
        catch(e){ }  // ignore errors

        $dialog.dialog('destroy').hide();
        $('div.addline', $dialog).remove();
      },
      width: 480
    }).show();
  }

  function add_uploadfile(e)
  {
    var div = $(this).parent();
    var clone = div.clone().addClass('addline').insertAfter(div);
    clone.children('.iconlink').click(add_uploadfile);
    clone.children('input').val('');

    if (!document.all)
      $('input[type=file]', clone).click();
  }


  /**
   *
   */
  function show_header_row(which, updated)
  {
    var row = $('#compose-' + which);
    if (row.is(':visible'))
      return;  // nothing to be done here

    if (compose_headers[which] && !updated)
      $('#_' + which).val(compose_headers[which]);

    row.show();
    $('#' + which + '-link').hide();

    layout_composeview();
    $('input,textarea', row).focus();

    return false;
  }

  /**
   *
   */
  function hide_header_row(which)
  {
    // copy and clear field value
    var field = $('#_' + which);
    compose_headers[which] = field.val();
    field.val('');

    $('#compose-' + which).hide();
    $('#' + which + '-link').show();
    layout_composeview();
    return false;
  }


  /**
   * Fieldsets-to-tabs converter
   */
  function init_tabs(elem, current)
  {
    var content = $(elem),
      id = content.get(0).id,
      fs = content.children('fieldset');

    if (!fs.length)
      return;

    if (!id) {
      id = 'rcmtabcontainer';
      content.attr('id', id);
    }

    // create tabs container
    var tabs = $('<ul>').addClass('tabsbar').prependTo(content);

    // convert fildsets into tabs
    fs.each(function(idx) {
      var tab, a, elm = $(this),
        legend = elm.children('legend'),
        tid = id + '-t' + idx;

      // create a tab
      a   = $('<a>').text(legend.text()).attr('href', '#' + tid);
      tab = $('<li>').addClass('tablink');

      // remove legend
      legend.remove();

      // link fieldset with tab item
      elm.attr('id', tid);

      // add the tab to container
      tab.append(a).appendTo(tabs);
    });

    // use jquery UI tabs widget to do the interaction and styling
    content.tabs({
      active: current || 0,
      heightStyle: 'content',
      activate: function(e, ui) {resize(); }
    });
  }

  /**
   * Show about page as jquery UI dialog
   */
  function show_about(elem)
  {
    var frame = $('<iframe>').attr({id: 'aboutframe', src: rcmail.url('settings/about'), frameborder: '0'});
      h = Math.floor($(window).height() * 0.75),
      buttons = {},
      supportln = $('#supportlink');

    if (supportln.length && (env.supporturl = supportln.attr('href')))
      buttons[supportln.html()] = function(e){ env.supporturl.indexOf('mailto:') < 0 ? window.open(env.supporturl) : location.href = env.supporturl };

    frame.dialog({
      modal: true,
      resizable: false,
      closeOnEscape: true,
      title: elem ? elem.title || elem.innerHTML : null,
      close: function() {
        frame.dialog('destroy').remove();
      },
      buttons: buttons,
      width: 640,
      height: h
    }).width(640);
  }
  
  /**
   * Show about page as jquery UI dialog
   */
  function show_eula(elem)
  {
    var frame = $('<iframe>').attr({id: 'eulaframe', src: '/legal/eula.pdf', frameborder: '0'});
      h = Math.floor($(window).height() * 0.75),
      buttons = {},
      window_width = $(window).width(),
      
    frame.dialog({
      modal: true,
      resizable: false,
      closeOnEscape: true,
      title: elem ? elem.title || elem.innerHTML : null,
      close: function() {
        frame.dialog('destroy').remove();
      },
      buttons: buttons,
      width: window_width - 100,
      height: h
    }).width(window_width - 130);
  }
}


/**
 * Roundcube Scroller class
 *
 * @deprecated Use treelist widget
 */
function rcube_scroller(list, top, bottom)
{
  var ref = this;

  this.list = $(list);
  this.top = $(top);
  this.bottom = $(bottom);
  this.step_size = 6;
  this.step_time = 20;
  this.delay = 500;

  this.top
    .mouseenter(function() { if (rcmail.drag_active) ref.ts = window.setTimeout(function() { ref.scroll('down'); }, ref.delay); })
    .mouseout(function() { if (ref.ts) window.clearTimeout(ref.ts); });

  this.bottom
    .mouseenter(function() { if (rcmail.drag_active) ref.ts = window.setTimeout(function() { ref.scroll('up'); }, ref.delay); })
    .mouseout(function() { if (ref.ts) window.clearTimeout(ref.ts); });

  this.scroll = function(dir)
  {
    var ref = this, size = this.step_size;

    if (!rcmail.drag_active)
      return;

    if (dir == 'down')
      size *= -1;

    this.list.get(0).scrollTop += size;
    this.ts = window.setTimeout(function() { ref.scroll(dir); }, this.step_time);
  };
};


/**
 * Roundcube UI splitter class
 *
 * @constructor
 */
function rcube_splitter(p)
{
  this.p = p;
  this.id = p.id;
  this.horizontal = (p.orientation == 'horizontal' || p.orientation == 'h');
  this.halfsize = (p.size !== undefined ? p.size : 10) / 2;
  this.pos = p.start || 0;
  this.min = p.min || 20;
  this.offset = p.offset || 0;
  this.relative = p.relative ? true : false;
  this.drag_active = false;
  this.render = p.render;
  this.callback = p.callback;

  var me = this;
  rcube_splitter._instances[this.id] = me;

  this.init = function()
  {
    this.p1 = $(this.p.p1);
    this.p2 = $(this.p.p2);
    this.parent = this.p1.parent();

    // check if referenced elements exist, otherwise abort
    if (!this.p1.length || !this.p2.length)
      return;

    // create and position the handle for this splitter
    this.p1pos = this.relative ? this.p1.position() : this.p1.offset();
    this.p2pos = this.relative ? this.p2.position() : this.p2.offset();
    this.handle = $('<div>')
      .attr('id', this.id)
      .attr('unselectable', 'on')
      .attr('role', 'presentation')
      .addClass('splitter ' + (this.horizontal ? 'splitter-h' : 'splitter-v'))
      .appendTo(this.parent)
      .bind('mousedown', onDragStart);

    if (this.horizontal) {
      var top = this.p1pos.top + this.p1.outerHeight();
      this.handle.css({ left:'0px', top:top+'px' });
    }
    else {
      var left = this.p1pos.left + this.p1.outerWidth();
      this.handle.css({ left:left+'px', top:'0px' });
    }

    // listen to window resize on IE
    if (bw.ie)
      $(window).resize(onResize);

    // read saved position from cookie
    var cookie = this.get_cookie();
    if (cookie && !isNaN(cookie)) {
      this.pos = parseFloat(cookie);
      this.resize();
    }
    else if (this.pos) {
      this.resize();
      this.set_cookie();
    }
  };

  /**
   * Set size and position of all DOM objects
   * according to the saved splitter position
   */
  this.resize = function()
  {
    if (this.horizontal) {
      this.p1.css('height', Math.floor(this.pos - this.p1pos.top - Math.floor(this.halfsize)) + 'px');
      this.p2.css('top', Math.ceil(this.pos + Math.ceil(this.halfsize) + 2) + 'px');
      this.handle.css('top', Math.round(this.pos - this.halfsize + this.offset)+'px');
      if (bw.ie) {
        var new_height = parseInt(this.parent.outerHeight(), 10) - parseInt(this.p2.css('top'), 10) - (bw.ie8 ? 2 : 0);
        this.p2.css('height', (new_height > 0 ? new_height : 0) + 'px');
      }
    }
    else {
      this.p1.css('width', Math.floor(this.pos - this.p1pos.left - Math.floor(this.halfsize)) + 'px');
      this.p2.css('left', Math.ceil(this.pos + Math.ceil(this.halfsize)) + 'px');
      this.handle.css('left', Math.round(this.pos - this.halfsize + this.offset + 3)+'px');
      if (bw.ie) {
        var new_width = parseInt(this.parent.outerWidth(), 10) - parseInt(this.p2.css('left'), 10) ;
        this.p2.css('width', (new_width > 0 ? new_width : 0) + 'px');
      }
    }

    this.p2.resize();
    this.p1.resize();

    // also resize iframe covers
    if (this.drag_active) {
      $('iframe').each(function(i, elem) {
        var pos = $(this).offset();
        $('#iframe-splitter-fix-'+i).css({ top: pos.top+'px', left: pos.left+'px', width:elem.offsetWidth+'px', height: elem.offsetHeight+'px' });
      });
    }

    if (typeof this.render == 'function')
      this.render(this);
  };

  /**
   * Handler for mousedown events
   */
  function onDragStart(e)
  {
    // disable text selection while dragging the splitter
    if (bw.konq || bw.chrome || bw.safari)
      document.body.style.webkitUserSelect = 'none';

    me.p1pos = me.relative ? me.p1.position() : me.p1.offset();
    me.p2pos = me.relative ? me.p2.position() : me.p2.offset();
    me.drag_active = true;

    // start listening to mousemove events
    $(document).bind('mousemove.'+this.id, onDrag).bind('mouseup.'+this.id, onDragStop);

    // enable dragging above iframes
    $('iframe').each(function(i, elem) {
      $('<div>')
        .attr('id', 'iframe-splitter-fix-'+i)
        .addClass('iframe-splitter-fix')
        .css({ background: '#fff',
          width: elem.offsetWidth+'px', height: elem.offsetHeight+'px',
          position: 'absolute', opacity: '0.001', zIndex: 1000
        })
        .css($(this).offset())
        .appendTo('body');
      });
  };

  /**
   * Handler for mousemove events
   */
  function onDrag(e)
  {
    if (!me.drag_active)
      return false;

    // with timing events dragging action is more responsive
    window.clearTimeout(me.ts);
    me.ts = window.setTimeout(function() { onDragAction(e); }, 1);

    return false;
  };

  /**
   * Dragging action (see onDrag())
   */
  function onDragAction(e)
  {
    var pos = rcube_event.get_mouse_pos(e);

    if (me.relative) {
      var parent = me.parent.offset();
      pos.x -= parent.left;
      pos.y -= parent.top;
    }

    if (me.horizontal) {
      if (((pos.y - me.halfsize) > me.p1pos.top) && ((pos.y + me.halfsize) < (me.p2pos.top + me.p2.outerHeight()))) {
        me.pos = Math.max(me.min, pos.y - Math.max(0, me.offset));
        if (me.pos > me.min)
          me.pos = Math.min(me.pos, me.parent.height() - me.min);

        me.resize();
      }
    }
    else {
      if (((pos.x - me.halfsize) > me.p1pos.left) && ((pos.x + me.halfsize) < (me.p2pos.left + me.p2.outerWidth()))) {
        me.pos = Math.max(me.min, pos.x - Math.max(0, me.offset));
        if (me.pos > me.min)
          me.pos = Math.min(me.pos, me.parent.width() - me.min);

        me.resize();
      }
    }

    me.p1pos = me.relative ? me.p1.position() : me.p1.offset();
    me.p2pos = me.relative ? me.p2.position() : me.p2.offset();
  };

  /**
   * Handler for mouseup events
   */
  function onDragStop(e)
  {
    // resume the ability to highlight text
    if (bw.konq || bw.chrome || bw.safari)
      document.body.style.webkitUserSelect = 'auto';

    // cancel the listening for drag events
    $(document).unbind('.'+me.id);
    me.drag_active = false;

    // remove temp divs
    $('div.iframe-splitter-fix').remove();

    me.set_cookie();

    if (typeof me.callback == 'function')
      me.callback(me);

    return bw.safari ? true : rcube_event.cancel(e);
  };

  /**
   * Handler for window resize events
   */
  function onResize(e)
  {
    if (me.horizontal) {
      var new_height = parseInt(me.parent.outerHeight(), 10) - parseInt(me.p2[0].style.top, 10) - (bw.ie8 ? 2 : 0);
      me.p2.css('height', (new_height > 0 ? new_height : 0) +'px');
    }
    else {
      var new_width = parseInt(me.parent.outerWidth(), 10) - parseInt(me.p2[0].style.left, 10);
      me.p2.css('width', (new_width > 0 ? new_width : 0) + 'px');
    }
  };

  /**
   * Get saved splitter position from cookie
   */
  this.get_cookie = function()
  {
    return window.UI ? UI.get_pref(this.id) : null;
  };

  /**
   * Saves splitter position in cookie
   */
  this.set_cookie = function()
  {
    if (window.UI)
      UI.save_pref(this.id, this.pos);
  };

} // end class rcube_splitter


// static getter for splitter instances
rcube_splitter._instances = {};

rcube_splitter.get_instance = function(id)
{
  return rcube_splitter._instances[id];
};

/** MACGREGOR CHANGES **/

function remove_block_email(row)
{
	/* var row3 = row;
	var row2 = row.closest('tr');
	var row1 = row2.closest('tr');
	
	row.prev().prev().remove(); // removes first row of the block
	row.prev().remove(); // removes second row of the block
	row.remove(); // removes third row of the block
	
	return false; */
	
	var row3 = row;
	var row2 = row3.prev();
	var row1 = row2.prev();
	
	row1.remove();
	row2.remove();
	row3.remove();
	
}


function remove_row(row)
{
	var row4 = row ;
	var row3 = row4.prev();
	var row2 = row3.prev();
	var row1 = row2.prev();
	
	row1.remove();
	row2.remove();
	row3.remove();
	row4.remove();
	
}

function remove_domain_record( row )
{
	$(row).remove();
}

function add_block_email()
{
	/* NEW CODE */
	// create labels for 2 rows
	var header_label = $("<label>").text('Header'); // creates label for header ie. dropdown
	$(header_label).attr( "for", "rcmfd_header" );
	
	var filter_label = $("<label>").text('Match'); // creates label for filter ie. textbox
	$(filter_label).attr( "for", "rcmfd_filter" );
	
	//create dropdown
	var option_data = {
		'0': 'From',
		'1': 'Subject',
	}

	var select_box = $('<select />');
	select_box.attr( { "id":"rcmfd_header", "name":"_block_email_header[]" } );

	for(var option_val in option_data) {
		$('<option />', {value: option_val, text: option_data[option_val]}).appendTo(select_box);
	}
	
	// create text box
	var text_box = $('<input/>').attr({ type: 'text', name:'_block_email_filter[]', id:"field_id"});
	
	// create remove button
	var remove_button = $('<a>');
	remove_button.attr({class:'rules_block_email_remove', onclick:'UI.remove_block_email($(this).parent().parent())', href:'#', title:'Remove Block Email'}).html('Remove');
	
	//create table
	var table = $( document ).find( "table" );
	
	// creating row1
	var row1_label = $('<td>').append($(header_label)).attr( "class", "title");
	var row1_dropdown = $('<td>').append($(select_box));
	var row1 = $('<tr>').append(row1_label).append(row1_dropdown).attr( "class", "blockEmail_header_row" );
	
	// creating row2
	var row2_label = $('<td>').append($(filter_label)).attr( "class", "title");
	var row2_textbox = $('<td>').append($(text_box));
	var row2 = $('<tr>').append(row2_label).append(row2_textbox);
	
	// creating row3
	var row3_remove_button = $('<td>').append($(remove_button)).attr( { colspan:"2", class:"title"} );
	var row3 = $('<tr>').append(row3_remove_button);
	
	// get last row
	var rows = $(table).find("tr"); // retrives all rows from table
	var count = $(rows).length
	var last_row = $(rows).eq(count - 1); // selects the last row where new rows can be appended
	var child_elem = String( last_row.children().html() ); // since it is used in "manage user rules" also, it was appending it after save button. hence it is checking, if last element is save button, then insert before that, else append it
	
	if( child_elem.indexOf( 'input type="submit"' ) != -1 )
	{
		last_row.prev().after(row3).after(row2).after(row1);
	}
	else
	{
		last_row.parent().append(row1).append(row2).append(row3);
	}
	
	return false;
}

function toggle_oof_details(checkbox_row)
{
	var enable_oof_rule_checkbox = $(checkbox_row).find('#enable_oof_details');
	
	var row_with_oof_header = $(checkbox_row).next();
	var row_with_oof_header_match = $(row_with_oof_header).next();
	var row_with_oof_subject = $(row_with_oof_header_match).next();
	var row_with_oof_message = $(row_with_oof_subject).next();
	var row_with_hidden_oof_enabled = $(row_with_oof_message).nextAll().eq(2);
	
	var oof_header_dropdown = $(row_with_oof_header).find('#oof_header');
	var oof_header_match_textbox = $(row_with_oof_header_match).find('#oof_header_match');
	var oof_subject_textbox = $(row_with_oof_subject).find('#oof_subject');
	var oof_message_textarea = $(row_with_oof_message).find('#oof_message');
	var oof_hidden_enabled = $(row_with_hidden_oof_enabled).find('#hidden_oof_enabled');
	
	var oof_dropdown_value = $(oof_header_dropdown).val();
	
	if( $(enable_oof_rule_checkbox).is(':checked') )
	{
		$(oof_header_dropdown).removeAttr( 'disabled' );
		$(oof_subject_textbox).removeAttr('readonly').attr( "style", "background:#fff" );
		$(oof_message_textarea).removeAttr('readonly').attr( "style", "background:#fff" );
		if( oof_dropdown_value != '0' ) // if selectbox value is not all, only then enable textbox
			$(oof_header_match_textbox).removeAttr('readonly').attr( "style", "background:#fff" );
		$(oof_hidden_enabled).val('1');
	}
	else
	{
		$(oof_header_dropdown).attr( 'disabled', 'true' );
		$(oof_subject_textbox).attr( { readonly:'true', style:'background:#F0F0F0' } );
		$(oof_message_textarea).attr( { readonly:'true', style:'background:#F0F0F0' } );
		$(oof_header_match_textbox).attr( { readonly:'true', style:'background:#F0F0F0' } );
		$(oof_hidden_enabled).val('0');
	}
	
}

function add_oof_details()
{
	// TO DISPLAY: enable checkbox, header select, header filter, header match, message
	
	// 1: Enable Checkbox
	var enable_checkbox_label = $("<label>").text('Enable');
	var enable_checkbox = $('<input />', { type: 'checkbox', id: 'enable_oof_details', name: 'enable_oof_details[]', onclick: 'UI.toggle_oof_details($(this).parent().parent())', value: 1 });
	var row1_checkbox_label = $('<td>').append($(enable_checkbox_label)).attr( 'class', 'title' );
	var row1_checkbox = $('<td>').append($(enable_checkbox));
	var row1 = $('<tr>').append($(row1_checkbox_label)).append($(row1_checkbox));
	
	// 2: Header Select Box
	var header_select_label = $('<label>').text('Header').attr('disabled', 'true');
	
	var option_data = {
		'0' : 'All',
		'1' : 'From',
		'2' : 'Subject',
		'3' : 'To'
	};
	
	var header_select = $('<select />').attr( { id: 'oof_header', name: 'oof_header[]', onclick: 'UI.set_oof_rule_header($(this).parent().parent())', 'disabled' : 'true' } );
	var row2_header_select_label = $('<td>').append($(header_select_label));
	var row2_header_select = $('<td>').append($(header_select));
	
	for( var option_val in option_data )
	{
		$('<option />', {value: option_val, text: option_data[option_val]}).appendTo($(header_select));
	}
	
	var row2 = $('<tr>').append($(row2_header_select_label)).append($(row2_header_select));
	
	
	// 3: Header Match Text Box
	var header_match_label = $('<label />').text('Match');
	var header_match_textbox = $('<input/>').attr({ type: 'text', name:'oof_header_match[]', id:"oof_header_match", readonly:'true', style:'background:#F0F0F0' });
	var row3_header_match_label = $('<td>').append($(header_match_label));
	var row3_header_match_textbox = $('<td>').append($(header_match_textbox));
	var row3 = $('<tr>').append($(row3_header_match_label)).append($(row3_header_match_textbox));
	
	// 4: Out Of Office Subject
	var oof_subject_label = $('<label />').text('Subject');
	var oof_subject_text_box = $('<input/>').attr({ type: 'text', name:'oof_subject[]', id:'oof_subject', readonly:'true', style:'background:#F0F0F0' });
	var row4_oof_subject_label = $('<td>').append($(oof_subject_label));
	var row4_oof_subject_text_box = $('<td>').append($(oof_subject_text_box));
	var row4 = $('<tr>').append($(row4_oof_subject_label)).append($(row4_oof_subject_text_box));
	
	// 5: Out of Office Message
	var oof_message_label = $('<label />').text('Message');
	var oof_message_textarea = $('<textarea />').attr({ id: 'oof_message', name : 'oof_message[]', rows : 10, cols : 60, readonly:'true', style:'background:#F0F0F0' });
	var row5_oof_message_label = $('<td>').append($(oof_message_label));
	var row5_oof_message_textarea = $('<td>').append($(oof_message_textarea));
	var row5 = $('<tr>').append($(row5_oof_message_label)).append($(row5_oof_message_textarea));
	
	// 6: Remove Button
	var remove_button = $('<a>');
	remove_button.attr({class:'rules_block_email_remove', onclick:'UI.remove_oof_details($(this).parent().parent())', href:'#', title:'Remove Block Email'}).html('Remove');
	var row6_remove_button = $('<td>').append($(remove_button)).attr( 'colspan', 2 );
	var row6 = $('<tr>').append($(row6_remove_button));
	
	// 7: oof Header Hidden Field
	var oof_header_hidden_field = $('<input/>').attr({ type: 'hidden', name:'hidden_oof_header[]', id:'hidden_oof_header', value: '0' });
	var row7_oof_header = $('<td>').append($(oof_header_hidden_field)).attr( 'colspan', 2 );
	var row7 = $('<tr>').append($(row7_oof_header));
	
	// 8: oof Enable Rule Hidden Field
	var oof_enabled_hidden_field = $('<input/>').attr({ type: 'hidden', name:'hidden_oof_enabled[]', id:'hidden_oof_enabled', value: '0' });
	var row8_oof_enabled = $('<td>').append($(oof_enabled_hidden_field)).attr( 'colspan', 2 );
	var row8 = $('<tr>').append($(row8_oof_enabled));
	
	var tables = $( document ).find( "table" );
	
	if( $(tables).length == 2 )
		var table = $(tables).eq(1);
	else
		var table = $(tables).eq(0);
	
	var rows = $(table).find("tr");
	var total_rows = $(rows).length;
	
	var last_row = $(rows).eq(total_rows - 1); // last row is td
	
	if( $(tables).length == 2 )
		$(last_row).prev().after($(row8)).after($(row7)).after($(row6)).after($(row5)).after($(row4)).after($(row3)).after($(row2)).after($(row1));
	else
		$(last_row).after($(row8)).after($(row7)).after($(row6)).after($(row5)).after($(row4)).after($(row3)).after($(row2)).after($(row1));
	
	return false;
}

function remove_oof_details( delete_row )
{
	// first delete row1
	// $(delete_row).parent().parent().parent().parent().parent().html();
	$(delete_row).prevAll().eq(4).remove();
	$(delete_row).prevAll().eq(3).remove();
	$(delete_row).prevAll().eq(2).remove();
	$(delete_row).prevAll().eq(1).remove();
	$(delete_row).prevAll().eq(0).remove();
	$(delete_row).nextAll().eq(1).remove();
	$(delete_row).nextAll().eq(0).remove();
	$(delete_row).remove();
}

function add_forward_rule()
{
	// create labels for 3 rows
	var header_label = $("<label>").text('Header'); // creates label for header ie. dropdown
	$(header_label).attr( "for", "rcmfwrule_header" );
	
	var filter_label = $("<label>").text('Match'); // creates label for filter ie. textbox
	$(filter_label).attr( "for", "rcmfwrule_filter" ); //  rcmfd_filter
	
	var email_label = $("<label>").text('Forward'); // creates label for filter ie. textbox
	$(email_label).attr( "for", "rcmfwrule_email" );
	
	//create dropdown
	var option_data = {
		'0' : 'All',
		'1' : 'From',
		'2' : 'Subject',
		'3' : 'To',
		'4': 'CC'
	}

	var select_box = $('<select />');
	select_box.attr( { "id":"rcmfwrule_header", "name":"_forward_rule_header[]", onclick : "UI.set_fwd_rule_header();" } );

	for(var option_val in option_data) {
		$('<option />', {value: option_val, text: option_data[option_val]}).appendTo(select_box);
	}
	
	// create text box
	var text_box = $('<input/>').attr({ type: 'text', name:'_forward_rule_filter[]', id:"rcmfwrule_filter"});
	
	
	var email_id_box = $('<input/>').attr({type:'text' , name:'_forward_rule_email[]' , id:"fwd_rule_id"});
	
	// create remove button
	var remove_button = $('<a>');
	remove_button.attr({class:'rules_block_email_remove', onclick:'UI.remove_row($(this).parent().parent())', href:'#', title:'Remove Block Email'}).html('Remove');
	
	//create table
	var table = $( document ).find( "table" );
	
	// creating row1
	var row1_label = $('<td>').append($(header_label)).attr( "class", "title");
	var row1_dropdown = $('<td>').append($(select_box));
	var row1 = $('<tr>').append(row1_label).append(row1_dropdown).attr( { class: "blockEmail_header_row" } );
	
	// creating row2
	var row2_label = $('<td>').append($(filter_label)).attr( "class", "title"); //TODO : change filer_label , filter_label
	var row2_textbox = $('<td>').append($(text_box));
	$(text_box).attr( { readonly:'true', style:'background:#F0F0F0', id:'rcmfwrule_filter' } );
	var row2 = $('<tr>').append(row2_label).append(row2_textbox);
	
	// creating row3 , adding new email ID row
	var row3_label = $('<td>').append($(email_label)).attr( "class", "title");
	var row3_email_id_box = $('<td>').append($(email_id_box));
	var row3 = $('<tr>').append(row3_label).append(row3_email_id_box);
	
	// creating row4
	var row4_remove_button = $('<td>').append($(remove_button)).attr( { colspan:"2", class:"title"} );
	var row4 = $('<tr>').append(row4_remove_button);
	
	// get last row
	var rows = $(table).find("tr"); // retrives all rows from table
	var count = $(rows).length
	var last_row = $(rows).eq(count - 1); // selects the last row where new rows can be appended
	var child_elem = String( last_row.children().html() ); // since it is used in "manage user rules" also, it was appending it after save button. hence it is checking, if last element is save button, then insert before that, else append it
	
	if( child_elem.indexOf( 'input type="submit"' ) != -1 )
	{
		last_row.prev().after(row4).after(row3).after(row2).after(row1);
	}
	else
	{
		last_row.parent().append(row1).append(row2).append(row3).append(row4);
	}
	
	return false;
}


function add_domain_record()
{
	var new_record_label = $("<label>").text('Record:	');
	$( new_record_label ).attr( "for", "_add_domain_record_name" ); // fill out the label tb name
	
	var record_name_txt_box = $('<input/>').attr({ type: 'text', name:'_add_domain_record_name[]', id:"_add_domain_record_name"});
	
	var record_type_select_box = $('<select />');
	record_type_select_box.attr( { "name":"_add_domain_record_type[]", "id":"_add_domain_record_type" } );
	
	var option_data = {
		'A' : 'A',
		'CNAME' : 'CNAME'
	}
	
	for( var option_val in option_data )
	{
		$('<option />', {value: option_val, text: option_data[option_val]}).appendTo(record_type_select_box);
	}
	
	var record_value_txt_box = $('<input/>').attr({ type: 'text', name:'_add_domain_record_value[]', id:"_add_domain_record_value"});
	
	var remove_button = $('<a>');
	remove_button.attr({class:'rules_block_email_remove', onclick:'UI.remove_domain_record($(this).parent().parent())', href:'#', title:'Remove Domain Record'}).html('Remove');
	
	var new_record_td = $('<td>').append($(new_record_label)).attr( "class", "title").append(record_name_txt_box).append( "   " ).append(record_type_select_box).append( "   " ).append(record_value_txt_box).append(remove_button);
	
	var new_record_row = $('<tr>').append( new_record_td );
	
	// create table
	var table = $(document).find("table").eq(0);
	
	var rows = $(table).find("tr"); 
	var count = $(rows).length;
	var last_row = $(rows).eq(count - 1);
	
	last_row.prev().prev().after(new_record_row);
	
	return false;
}

// NOT USED ANYMORE
/* function enable_outofoffice_tb()
{
	var enable_cb = $('[name="_out_of_office_enable"]');
	var subject_tb = $('[name="_out_of_office_sub"]');
	var message_tb = $('[name="_out_of_office_message"]');
	if ($(enable_cb).is(':checked')) {
		$(subject_tb).removeAttr('readonly').attr( "style", "background:#fff" );
		$(message_tb).removeAttr('readonly').attr( "style", "background:#fff" );
	} 
	else {
		$(subject_tb).attr( { readonly:'true', style:'background:#F0F0F0' } );
		$(message_tb).attr( { readonly:'true', style:'background:#F0F0F0' } );
	}
} */


function enable_custom_rule_tb()
{
	var enable_cb = $('[name="_custom_rule_enable"]');
	var message_tb = $('[name="_custom_rule_desc"]');
	if ($(enable_cb).is(':checked')) {
		$(message_tb).removeAttr('readonly').attr( "style", "background:#fff" );
	} 
	else {
		$(message_tb).attr( { readonly:'true', style:'background:#F0F0F0' } );
	}
}	

function enable_folder_rule()
{
	var enable_cb = $('[name = "_enable_folder_rule"]');
	var filter = $('[name = "_folder_rule_header"]');
	var filter_match = $('[name = "_folder_rule_filterMatch"]');
	
	if($(enable_cb).is(':checked')) {
		$(filter).removeAttr('disabled');
		$(filter_match).removeAttr('readonly').attr( "style", "background:#fff" );
	}
	else{
		$(filter).attr( { disabled:'true' } );
		$(filter_match).attr( { readonly:'true', style:'background:#F0F0F0' } );
	}
}

function show_user_detail( updt_email )
{
	$('#_hidden_email').val( updt_email );	
	$('#_hidden_flag').val( "Edit" );
	
	return false;
}


function show_domain_details( flag, domain_name )
{
	if( flag == 'primary' ) // if primary domain, set hidden primary domain
	{
		$( '#_hidden_primary_domain_name' ).val( domain_name );
	}
	else // if domain alias, set domain name
	{
		$( '#_hidden_domain_name' ).val( domain_name );
	}
}

function show_admin_user_details( admin_user_email )
{
	$( '#_hidden_admin_user_email' ).val( admin_user_email );
}
   
function show_list_detail( list_val)
{
	$( '#_hidden_edit_list' ).val( list_val );
}

function add_user_by_domain( select_box )
{
	var selected_domain = select_box.options[select_box.selectedIndex].value;
	$.ajax({
		url: 'ajax.php?main=dom_em&sel_dom='+selected_domain,
		success: function( result ) {
			var user_select = $("#_add_new_admin_useremail")[0];
			$(user_select).children().remove();
			var email_arr = result.split( "|" );
			for( var i = 0; i < email_arr.length; i++ )
			{
				$(user_select).append('<option id="'+email_arr[ i ]+'">'+email_arr[ i ]+'</option>');
			}
		},
		error: function( error ) {
			console.log( error );
		}
	});
}

function load_invitees( show_default )
{
	if( show_default == true )
	{
		var select2_id = [];
		var select2_value = [];
		var ajax_url = "ajax.php?main=attendee_em&all"; // if all is set, retrives all email ids.. else retrives all attendees
		$.ajax({
			url: ajax_url,
			success: function(result){
				var attendees_details = JSON.parse(result);
				attendees_details.sort();
				$("#invitees_details_dropdown").empty(); // first empty the values
				
				for( var i = 0; i < attendees_details.length; i++ )
				{
					var split_attendee_details = attendees_details[ i ].split( "|" );
					var attendee_user_email = split_attendee_details[ 0 ];
					var attendee_username = split_attendee_details[ 1 ];
					
					$("#invitees_details_dropdown").append( '<option value="' + attendees_details[ i ]  + ' " > ' + attendee_user_email + '</option>' );
                    console.log( "seting...." );
					// $("#invitees_details_dropdown").select2( "destroy" );
					// $("#invitees_details_dropdown").select2({ data:[{id:0,text:'enhancement'},{id:1,text:'bug'},{id:2,text:'duplicate'},{id:3,text:'invalid'},{id:4,text:'wontfix'}] });
				}
			},
			error: function( error ) {
				console.log( error );
			}
		});
	}
	
}

function hide_user_detail()
{
	// var add_user_block = $("div").eq(0);
	// var manage_users_block = $("div").eq(1);
	// var manage_edit_block = $("div").eq(2);
	// $(add_user_block).show();
	// $(manage_users_block).show();
	// $(manage_edit_block).hide();
}

function delete_list( chk_box, del_lst_name )
{
	if( chk_box.checked == true )
	{
		// add_delete_list_name( del_lst_name );
		add_hidden_field_value( $('#_hidden_edit_list'), del_lst_name );
	}		
	else
	{
		// remove_delete_list_name( del_lst_name );
		remove_hidden_field_value( $('#_hidden_edit_list'), del_lst_name );
	}
}


function delete_users( chk_box, del_email )
{
	if( chk_box.checked == true )
	{
		add_hidden_field_value( $('#_hidden_email'), del_email );
	}		
	else
	{
		remove_hidden_field_value( $('#_hidden_email'), del_email );
	}
}

function add_user_for_calendar_share( chk_box, user_email )
{
	if( chk_box.checked == true )
	{
		add_hidden_field_value( $('#_cal_share_hidden_username'), user_email );
	}		
	else
	{
		remove_hidden_field_value( $('#_cal_share_hidden_username'), user_email );
	}
}

function delete_domain_alias( chk_box, delete_domain_alias_nm )
{
	if( chk_box.checked == true )
	{
		add_hidden_field_value( $('#_hidden_domain_name'), delete_domain_alias_nm );
	}		
	else
	{
		remove_hidden_field_value( $('#_hidden_domain_name'), delete_domain_alias_nm );
	}
}

function delete_admin( chk_box, del_admin_email, del_admin_domain )
{
	if( chk_box.checked == true )
	{
		add_hidden_field_value( $('#_hidden_admin_email'), del_admin_email );
		add_hidden_field_value( $('#_hidden_admin_manage_domain'), del_admin_domain );
	}
	else
	{
		remove_hidden_field_value( $('#_hidden_admin_email'), del_admin_email );
		remove_hidden_field_value( $('#_hidden_admin_manage_domain'), del_admin_domain );
	}
}

function delete_user_alias( chk_box, user_alias, orig_email )
{
	// src is alias and dest is orig email
	if( chk_box.checked == true )
	{
		add_hidden_field_value( $('#_hidden_user_aliases_dest'), orig_email );
		add_hidden_field_value( $('#_hidden_user_aliases_src'), user_alias );
	}		
	else
	{
		remove_hidden_field_value( $('#_hidden_user_aliases_src'), user_alias );
		remove_hidden_field_value( $('#_hidden_user_aliases_dest'), orig_email );
	}
}

function add_delete_user_email( del_email )
{
	var email_list = $('#_hidden_email').val();
	var del_email_str = "";
	if( email_list.length > 0 ) // if there is atleast 1 email in hidden field
	{
		var email_arr = email_list.split('|');
		for( i in email_arr )
		{
			if( del_email == email_arr[i] ) // if email is present in the email list
				break;
			else
			{
				email_arr.push(del_email);
				break;
			}
		}
		del_email_str = email_arr.join('|');
	}
	else
	{
		del_email_str = del_email;
	}
	$('#_hidden_email').val(del_email_str);
}

function remove_delete_user_email( del_email )
{
	var email_list = $('#_hidden_email').val();
	// console.log( "b4 removing: " + email_list );
	var del_email_str = "";
	if( email_list.length > 0 )
	{
		var email_arr = email_list.split("|");
		for( i in email_arr )
		{
			if( del_email == email_arr[i] )
			{
				var index = email_arr.indexOf( del_email );
				email_arr.splice( index, 1 );
				break;
			}
			else
				continue;
		}
		del_email_str = email_arr.join("|");
	}
	else
	{
		del_email_str = "";
	}
	$('#_hidden_email').val(del_email_str);
}

function add_delete_list_name( del_lst_name )
{
	var dist_list = $('#_hidden_edit_list').val();
	var del_email_str = "";
	if( dist_list.length > 0 ) // if there is atleast 1 email in hidden field
	{
		var list_arr = dist_list.split('|');
		for( i in list_arr )
		{
			if( del_lst_name == list_arr[i] ) // if email is present in the email list
				break;
			else
			{
				list_arr.push(del_lst_name);
				break;
			}
		}
		del_email_str = list_arr.join('|');
	}
	else
	{
		del_email_str = del_lst_name;
	}
	$('#_hidden_edit_list').val(del_email_str);
}

function remove_delete_list_name( del_lst_name )
{
	var dist_list = $('#_hidden_edit_list').val();
	var del_email_str = "";
	if( dist_list.length > 0 )
	{
		var list_arr = dist_list.split("|");
		for( i in list_arr )
		{
			if( del_lst_name == list_arr[i] )
			{
				var index = list_arr.indexOf( del_lst_name );
				list_arr.splice( index, 1 );
				break;
			}
			else
				continue;
		}
		del_email_str = list_arr.join("|");
	}
	else
	{
		del_email_str = "";
	}
	$('#_hidden_edit_list').val(del_email_str);
}

// common functions to set and remove values of hidden field
function add_hidden_field_value( hidden_fld_nm, value_to_be_added )
{
	var hidden_fld_val = $( hidden_fld_nm ).val();
	// console.log( "hidden field vlaue" + hidden_fld_val );
	var add_hidden_fld_str = "";
	if( hidden_fld_val.length > 0 ) // if ther is atleast 1 entity name in hidden field
	{
		var entity_arr = hidden_fld_val.split( '|' );
		entity_arr.push( value_to_be_added );
		add_hidden_fld_str = entity_arr.join( "|" );
	}
	else // if ther is no element in hidden field directly add value to that
	{
		add_hidden_fld_str = value_to_be_added;
	}
	$( hidden_fld_nm ).val( add_hidden_fld_str );
	
}

function remove_hidden_field_value( hidden_fld_nm, value_to_be_removed )
{
	var hidden_fld_val = $( hidden_fld_nm ).val();
	var remove_hidden_fld_str = "";
	if( hidden_fld_val.length > 0 )
	{
		var entity_arr = hidden_fld_val.split( "|" );
		for( i in entity_arr )
		{
			if( value_to_be_removed == entity_arr[ i ] )
			{
				var index = entity_arr.indexOf( value_to_be_removed );
				entity_arr.splice( index, 1 );
				break;
			}
			else
				continue;
		}
		remove_hidden_fld_str = entity_arr.join( "|" );
	}
	else
	{
		remove_hidden_fld_str = "";
	}
	$( hidden_fld_nm ).val( remove_hidden_fld_str );
}

function toggle_pwd( chkbox_id, pwd_field_id )
{
	if ( $( "#" + chkbox_id ).is(":checked") )
	{
		$( "#" + pwd_field_id ).replaceWith('<input type="password" name="'+pwd_field_id+'" id="'+pwd_field_id+'" value="' + $("#" + pwd_field_id).attr('value') + '" />');
	}
	else
	{
		$( "#" + pwd_field_id ).replaceWith('<input type="text" name="'+pwd_field_id+'" id="'+pwd_field_id+'" value="' + $("#" + pwd_field_id).attr('value') + '" />')
	}
}

function check_inArray( needle, haystack )
{
	for( var i = 0; i < haystack.length; i++ )
	{
		if( jQuery.trim( haystack[ i ] ) == jQuery.trim( needle ) )
		{
			return true;
		}
	}
	return false;
}

function concat_array( arr1, arr2 )
{
	var new_array = [];
	for( var i = 0; i < arr1.length; i++ )
	{
		new_array.push( arr1[ i ] );
	}
	
	for( var i = 0; i < arr2.length; i++ )
	{
		new_array.push( arr2[ i ] );
	}
	
	return new_array;
}

function set_caldav_url( select_box, username )
{
	var selected_calendar_str = select_box.options[select_box.selectedIndex].value;
	if( selected_calendar_str != "null" ) // if --Select-- is not selected
	{
		var selected_calendar_arr = selected_calendar_str.split( "|" );
		var selected_calendar_url = selected_calendar_arr[ 1 ];
		$( "#_caldav_url" ).val( selected_calendar_url );
		$.ajax({
			url: 'ajax.php?main=cal_share',
			data: { "url" : selected_calendar_url, "username" : username },
			success: function( result ) {
				var subscribed_email_arr = $.parseJSON( result );
				if( subscribed_email_arr.length > 0 )
				{
					var users_table = $( document ).find( "#cal_share" ); // table with all users in the form, username|email|checkbox
					
					var users_table_body = $(users_table).find('tbody'); // since there is header
					var users_table_rows = $(users_table_body).find("tr"); // get all the rows of table
					var users_count = $(users_table_rows).length // count of all the rows
					
					for( var i = 0; i < users_count; i++ )
					{
						var user_row = $(users_table_rows).eq(i);
						var user_email_block = $(user_row).find( ".email" );
						var user_email = $(user_email_block).html();
						var subscribe_check_box = user_row.find( $( "input[type=checkbox]" ) );
						
						if( jQuery.inArray( user_email, subscribed_email_arr ) != -1 ) // ie. if user is subscribed
						{
							subscribe_check_box.prop('checked', true);
						}
						else
						{
							subscribe_check_box.prop('checked', false);
						}
					}
					
					// set the value of the hidden field with the subscribed emails
					subscribed_email_str = subscribed_email_arr.join( "|" );
					$( "#_cal_share_hidden_username" ).val( subscribed_email_str );
					
					/* WRONG CODE */
					
					/* for( var i = 0; i < count; i++ )
					{
						var curr_row = $(rows).eq(i);
						var email_block = $(curr_row).find( ".email" );
						var email = $(email_block).html();
						var check_box = curr_row.find( $( "input[type=checkbox]" ) );
						
						for( var j = 0; j < subscribed_email_arr.length; j++ )
						{
							var subscribed_email = subscribed_email_arr[j];
							console.log( "subscribed_email: " + subscribed_email );
							console.log( "email: " + email );
							if( subscribed_email == email )
							{
								// console.log( "1: subscribed email: " + subscribed_email + " email: " + email );
								$(check_box).prop('checked', true);
							}
							else
							{
								// console.log( "2: subscribed email: " + subscribed_email + " email: " + email );
								$(check_box).prop('checked', false);
							}
						}
					} */
				}
				else
				{
					$( "#_cal_share_hidden_username" ).val( "" ); // set the value of hidden field as blank
					$('input:checkbox').removeAttr('checked'); // uncheck all textboxes
				}
			},
			error: function( error ) {
				alert( "error" );
			}
		});
	}
	else
		$('input:checkbox').removeAttr('checked');
}

function display_local_list_drodown()
{
	if( $('#_edit_list_local').is( ":checked" ) )
	{
		$( '#add_member_to_list_div' ).parent().parent().show();
		$( '#_edit_list_ext_email' ).hide();
	}
}

function display_external_list_txt()
{
	$('#_edit_list_hidden_field').val('');
	if( $('#_edit_list_external').is( ":checked" ) )
	{
		$( '#add_member_to_list_div' ).parent().parent().hide();
		$( '#_edit_list_ext_email' ).show();
	}	
}

function show_delte_list_confirm()
{
	var delete_list_names = $('#_hidden_edit_list').val();
	if( delete_list_names.length == 0 )
	{
		rcmail.display_message( "Please select atleast 1 email to delete", "error" );
		return false;
	}
	else
	{
		var list_email_arr = delete_list_names.split( "|" );
		var delete_list = list_email_arr.join( ", " );
		return confirm('Are you sure you want to delte ' + delete_list +' Lists?');
	}
}

function show_delete_admin_confirm()
{
	var delete_admin_email = $( '#_hidden_admin_email' ).val();
	
	if( delete_admin_email.length == 0 )
	{
		rcmail.display_message( "Please select atleast 1 email to delete", "error" );
		return false;
	}
	else
	{
		var admin_email_arr = delete_admin_email.split( "|" );
		var admin_delete = admin_email_arr.join( ", " );
		return confirm('Are you sure you want to delete ' + admin_delete +' Admins?');
	}
}

function delete_domain_alias_confirm()
{
	var delete_domain_alias = $( '#_hidden_domain_name' ).val();
	
	if( delete_domain_alias.length == 0 )
	{
		rcmail.display_message( "Please select atleast 1 Alias to delete", "error" );
		return false;
	}
	else
	{
		var domain_alias_arr = delete_domain_alias.split( "|" );
		var domain_alias_str = domain_alias_arr.join( ", " );
		return confirm( "Are you sure you want to Delete " + domain_alias_str + " Domain Aliases" );
	}
}

function show_delete_domain_record_confirm()
{
	var delete_domain_record = $( '#_hidden_domain_record_nm' ).val();
	
	if( delete_domain_record.length == 0 )
	{
		rcmail.display_message( "Please select atleast 1 Domain Record to delete", "error" );
		return false;
	}
	else
	{
		var domain_record_arr = delete_domain_record.split( "|" );
		var domain_record_str = domain_record_arr.join( ", " );
		return confirm( 'Are you sure you want to Delete ' + domain_record_str + ' Domain Record' );
	}
}

function confirm_delete_list_members()
{	
	var delete_list_mem_names = $('#_del_list_mem_hidden_field').val();
	if( delete_list_mem_names.length == 0 )
	{
		rcmail.display_message( "Please select atleast 1 email to delete", "error" );
		return false;
	}
	else
	{
		var list_email_arr = delete_list_mem_names.split( "|" );
		var delete_list = list_email_arr.join( ", " );
		return confirm('Are you sure you want to delte ' + delete_list +' Members?');
	}
}

function confirm_delte_alias()
{
	var delete_alias_names = $( "#_hidden_user_aliases_src" ).val();
	if( delete_alias_names.length == 0 )
	{
		rcmail.display_message( "Please select atleast 1 alias to delete", "error" );
		return false;
	}
	else
	{
		var list_alias_arr = delete_alias_names.split( "|" );
		var alias_list = list_alias_arr.join( ", " );
		return confirm('Are you sure you want to delete ' + alias_list +' Members?');
	}
}

function set_edit_list_name( chk_box, edit_list_name )
{
	if( chk_box.checked == true )
	{
		add_hidden_field_value( $( '#_edit_list_hidden_field' ), edit_list_name );
	}		
	else
	{
		remove_hidden_field_value( $( '#_edit_list_hidden_field' ), edit_list_name );
	}
}

function set_edit_list_name_del( chk_box, edit_list_name )
{
	if( chk_box.checked == true )
	{
		add_hidden_field_value( $( '#_del_list_mem_hidden_field' ), edit_list_name );
	}		
	else
	{
		remove_hidden_field_value( $( '#_del_list_mem_hidden_field' ), edit_list_name );
	}
}

function set_delete_domain_record( chk_box, delete_domain_record_name )
{
	if( chk_box.checked == true )
	{
		add_hidden_field_value( $( '#_hidden_domain_record_nm' ), delete_domain_record_name );
	}		
	else
	{
		remove_hidden_field_value( $( '#_hidden_domain_record_nm' ), delete_domain_record_name );
	}
}

function set_fwd_rule_header()
{
	// MG TODO: check all and enabled on change of select box
	// due to same ids for multiple select boxes, this is iterating through all the selectbox withid rcmfwrule_header, finding next txt box and disabling it
	var total_select_box = $("#rcmfwrule_header").length;
	$("[id=rcmfwrule_header]").each( function() {
		var select_box = $(this);
		$(select_box).find('option').each(function() {
			var option = $(this);
			if( $(option).is(':selected') )
			{
				var selected = $(option).text();
				var tr_with_dropdown = $(select_box).parent().parent(); // retrives row with select box
				var tr_with_txtbox = $(select_box).parent().parent().next(); // retrives row with txtbox
				var txt_box = $(tr_with_txtbox).find( '#rcmfwrule_filter' ); // finds txt box forom prev row
				
				if( selected == "All" )
				{
					$(txt_box).attr( { readonly:'true', style:'background:#F0F0F0' } ).val("");
				}
				else
				{
					$(txt_box).removeAttr('readonly').attr( "style", "background:#fff" );
				}
			}
		});
	} );
}

function set_oof_rule_header( select_box_row )
{
	var select_box = $(select_box_row).find('#oof_header');
	var rows = $(select_box_row).nextAll();
	var header_match_textbox_row = $(rows).eq(0); // first row after select box
	var hidden_oof_header_row = $(rows).eq(4);
	
	var selected_header = $(select_box).val();
	var header_match_textbox = $(header_match_textbox_row).find('#oof_header_match');
	var hidden_oof_header = $(hidden_oof_header_row).find('#hidden_oof_header');
	
	if(selected_header != '0')
	{
		$(header_match_textbox).removeAttr('readonly').attr( "style", "background:#fff" );
	}
	else
	{
		$(header_match_textbox).attr( { readonly:'true', style:'background:#F0F0F0' } ).val('');
	}
	
	$(hidden_oof_header).val(selected_header);
}

function add_suffix_to_num( number )
{
	var suffix = "";
	
	if( number == 1 )
	{
		suffix = "st";
	}
	else if( number == 2 )
	{
		suffix = "nd";
	}
	else if( number == 3 )
	{
		suffix = "rd";
	}
	else
	{
		suffix = "th";
	}
	
	return number + suffix;
	
}
// @license-end
