function show_caldav_dialog(a,b,d)
{
	var c=$("#caldav_dialog");
	try
	{
		if(!0===c.dialog("isOpen"))
		return
	}
	catch(e){}
	var f={};
	f[rcmail.gettext("cancel","calendar")]=function()
	{
		c.dialog("close");
		b.checked=!1
	};
	f[rcmail.gettext("save","calendar")]=function()
	{
		$("#caldav_category").val(a);
		""!=$("#caldav_user").val()&&""!=$("#caldav_password").val()&&""!=$("#caldav_url").val()?(rcmail.http_post(
			"plugin.calendar_saveCalDAV",
			$("#caldav_form").serialize()),
			c.dialog("destroy"),
			c.hide(),
			$("#edit_"+d).attr("style","visibility:visible"),
			$("#category_handler_"+d).attr("class","protected_category"),
			$("#dialog_handler_"+d).hide(),
			$("#category_handler_"+d).attr("onclick","calendar_toggle_caldav(this, '"+a+"')"),
			$("#category_handler_"+d).attr("title",rcmail.gettext("unlink_caldav","calendar")),
			$("#rcmfd_category_"+d).attr("readonly",!0),b.checked=!1):$("#caldav_user").focus()
	};
	b&&b.src&&(f[rcmail.gettext("remove","calendar")]=function()
	{
		rcmail.env.elem=b;
		rcmail.env.dialogContent=c;
		confirm(rcmail.gettext("unlink_caldav_warning","calendar"),
		rcmail.gettext("unlink_events_warning_html","calendar"),
		"$('#caldav_remove').val(1); unlink_caldav('"+d+"', '"+a+"');",
		"$('#caldav_remove').val(0); unlink_caldav('"+d+"', '"+a+"');",!0)&&(confirm(rcmail.gettext("unlink_events_warning","calendar"))?$("#caldav_remove").val(1):$("#caldav_remove").val(0),unlink_caldav(d,a))});
		c.dialog(
		{
			modal:!0,
			width:700,
			position:"center",
			title:a,
			close:function()
			{
				c.dialog("destroy");
				c.hide();
				b.checked=!1
			},
			buttons:f
		}).hide()
}

function unlink_caldav(a,b){elem=rcmail.env.elem;$dialogContent=rcmail.env.dialogContent;$("#caldav_category").val(b);rcmail.http_post("plugin.calendar_removeCalDAV",$("#caldav_form").serialize());$dialogContent.dialog("destroy");$dialogContent.hide();$("#edit_"+a).attr("style","visibility:hidden");$("#category_handler_"+a).attr("class","");$("#category_handler_"+a).attr("onclick","removeRow(this.parentNode.parentNode)");$("#category_handler_"+a).attr("title",rcmail.gettext("remove_category","calendar"));
$("#rcmfd_category_"+a).attr("readonly",!1);$("#dialog_handler_"+a).show();elem.checked=!1}

function calendar_toggle_caldav(a,b,d)
{
	"X"==a.value&&(a=document.getElementById("dialog_handler_"+d));
	rcmail.http_post("plugin.calendar_getCalDAVs","_category="+b);
	show_caldav_dialog(b,a,d)
}

function show_default_shared_cal_url( a,b,d )
{
	"X"==a.value&&(a=document.getElementById("dialog_handler_"+d));
	rcmail.http_post("plugin.calendar_getCalDAVs","_category="+b+"&onlyURL=true");
	show_caldav_dialog(b,a,d)
}

function calendar_getCalDAVs(a)
{
	// if shared calendar edit is clicked, display only url of calendar in dialog box.
	if( a.onlyURL )
	{
		calendar_categories_url(a)
	}
	else // display all edit details
	{
		var b=0;
		$(".edit_caldav").each(function()
		{
			"visibility:visible"==$(this).attr("style")&&b++
		});
		b>=a.max_caldavs?$("input:radio").each(function()
		{
			$(this).attr("disabled",!0)
		})
		:
		$("input:radio").each(function()
		{
			$(this).attr("disabled",!1)
		});
		if(a.tabbed&&!bw.mz&&"function"==typeof parent.parent.getIFRAMEDiv)
			try
			{
				parent.parent.getIFRAMEDiv("dummy:plugin.calendar");
				var d=parent.parent.rcmail.env.tabbed_target.replace("#tabbed_","tabbed_frame_"),
				c=window.top.frames[d].document.location.href,
				e=c.split("_task="),
				c="./?_task="+e[1],
				e=c.split("&_s="),
				c=e[0]+"&_onload=no&_s="+(new Date).getTime();
				window.top.frames[d].document.location.href=c
			}
			catch(f){}
			calendar_categories_gui(a)
	}
}

function calendar_categories_url(a)
{
	var buttons_div = $('.ui-dialog-buttonset');
	$(buttons_div).hide();
	
	var table = $("#caldav_dialog").find( "table" );
	$("#caldav_url").val(a.url);
	$("#url_row").show();
	$("#username_row").hide();
	$("#password_row").hide();
	$("#reminder_row").hide();
	$("#authentication_row").hide();
	
	/* var url_label = $("<label>").text("Calendar URL: ");
	var td_url_label = $('<td>').append( $(url_label) );
	var url_text_box = $('<input/>').attr({ type: 'text', name:'calendar_url', value: a.url, style: "width:500px;", "readonly" : "true" });
	var td_url_text_box = $('<td>').append( $(url_text_box) );
	
	var table_row = $('<tr>').append( $(td_url_label) ).append( url_text_box );
	
	$(table).empty();
	$(table).append( $(table_row) ); */
	$("#caldav_dialog").append( $(table) );
	$("#caldav_dialog").show();
}

function calendar_categories_gui(a)
{
	// if shared or default calendars are clicked, other textboxes are hidden. therefore show the textboxes here
	$("#url_row").show();
	$("#username_row").show();
	$("#password_row").show();
	$("#reminder_row").show();
	$("#authentication_row").show();
	$("#caldav_user").val(a.user);
	var buttons_div = $('.ui-dialog-buttonset');
	$(buttons_div).show();
	
	"ENCRYPTED"==a.pass||"SESSION"==a.pass
	?(
		$("#caldav_password").val(a.pass),
		$("#caldav_password").attr("title",rcmail.gettext("passwordisset","calendar"))
	):(
		$("#caldav_password").val(""),
		$("#caldav_password").attr("title",rcmail.gettext("passwordisnotset","calendar"))
	);
	a.url?(
		$("#caldav_url").val(a.url),
		rcmail.env.caldav_url=a.url
	):(
		$("#caldav_url").val(a.cat),
		rcmail.env.caldav_url=a.cat
	);
	a.saved
	?
		$("#edit_"+a.category_disp).attr("style","visibility:visible")
	:
		$("#edit_"+a.category_disp).attr("style","visibility:hidden");
	a.cal_dont_save_passwords&&($("#caldav_password").attr("readonly",!0),
	$("#caldav_url").blur(function()
	{
		-1<$("#caldav_url").val().indexOf("?access=")&&rcmail.env.caldav_url!=$("#caldav_url").val()&&($("#caldav_password").val(""),
		$("#caldav_password").attr("title",rcmail.gettext("passwordisnotset","calendar")),
		$("#caldav_password").attr("readonly",!1))
	}));
	"external"==a.extr||!0===a.extr
	?
		$("#caldav_extr").val("external")
	:
		$("#caldav_extr").val("internal");
	"detect"==a.auth
	?
		$("#caldav_auth").val("detect")
	:
		$("#caldav_auth").val("basic");
	a.show&&$("#caldav_dialog").show()
}

function addRowCategories(a){var b=document.getElementsByTagName("table")[0],d=b.rows.length;a='<input type="text" name="_categories[]" size="'+a+'" />';var c='<input type="button" value="X" onclick="removeRow(this.parentNode.parentNode)" title="'+rcmail.gettext("remove_category","calendar")+'" />';try{var e=b.insertRow(d),f=e.insertCell(0);f.innerHTML="&nbsp;";f.className="title";f=e.insertCell(1);f.innerHTML=c+"&nbsp;"+a+'&nbsp;<input type="text" name="_colors[]" size="6" class="color" value="ffffff" />';
jscolor.init()}catch(g){}"function"==typeof jscolor_removeHexStrings&&jscolor_removeHexStrings()}
function addRowCalFeeds(a){var b=document.getElementsByTagName("table")[0],d=b.rows.length;a='<input type="text" name="_calendarfeeds[]" size="'+a+'" />';var c='<select name="_feedscategories[]">',e;for(e in categories)c+='<option value="'+e+'">'+e+"</option>";c+="</select>";e='<input type="button" value="X" onclick="removeRow(this.parentNode.parentNode)" title="'+rcmail.gettext("remove_feed","calendar")+'" />';try{var f=b.insertRow(d),g=f.insertCell(0);g.innerHTML="&nbsp;";g.className="title";g=
f.insertCell(1);g.innerHTML=e+"&nbsp;"+a+"&nbsp;"+c;jscolor.init()}catch(h){}}function removeRow(a){var b=document.getElementsByTagName("table")[0];try{b.deleteRow(a.rowIndex),document.forms.form.submit()}catch(d){}}
$(document).ready(function(){rcmail.addEventListener("plugin.calendar_getCalDAVs",calendar_getCalDAVs);rcmail.env.form_categories=$(document.forms.form).serialize();$(".color").each(function(){$(this).blur(function(){rcmail.env.form_categories!=$(document.forms.form).serialize()&&$(jscolor.picker.boxB)&&$(jscolor.picker.boxB).mouseleave(function(){document.forms.form.submit()})})})});