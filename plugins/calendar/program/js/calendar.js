$(document).ready(function()
{
	"larry"==rcmail.env.skin&&$("#calendar_button").attr("style","color: #3CF; background-color: #2c2c2c;");
	$(window).resize(function(){calendar_gui.minimalmode()});
	rcmail.addEventListener("plugin.reloadCalendar",function(a)
	{
		$("#calendar").fullCalendar("removeEvents");
		$("#calendar").fullCalendar("refetchEvents");
		rcmail.http_post("plugin.getTasks","_init=0");
		rcmail.set_busy(!1,"loading",a);
		$("#calendaroverlay").hide()
	});
	rcmail.addEventListener("plugin.syncCalendar",calendar_commands.reload);
	rcmail.addEventListener("plugin.calendar_unlockGUI",function(a)
	{
		rcmail.set_busy(!1,"loading",a);
		$("#calendaroverlay").hide();
		calendar_commands.triggerSearch()
	});
	rcmail.addEventListener("plugin.calendar_errorGUI",function(a)
	{
		rcmail.set_busy(!1,"loading",a);
		$("#calendaroverlay").hide();
		rcmail.display_message(rcmail.gettext("calendar.errorsaving"),"error");
		$("#calendar").fullCalendar("refetchEvents");
		rcmail.http_post("plugin.getTasks","_init=0")
	});
	rcmail.addEventListener("plugin.calendar_searchEvents",function(a)
	{
		if(0!=a.rows)
		{
			rcmail.env.cal_search_results=a.rows;
			$("#calsearch_table").html(a.rows);
			$("#calsearchdialog").dialog("open");
			var b=-1;
			if(a.events)
				$("#calsearch_table tr td").each(function()
				{
					b++;
					a.events[b].start*=1E3;
					a.events[b].end&&(a.events[b].end*=1E3);
					elem=calendar_callbacks.qtip(a.events[b],$(this),"search")
				});
				else 
					for(;-1<$("#calsearchfilter").val().indexOf(" ");)
					{
						$("#calsearchfilter").val($("#calsearchfilter").val().replace(" ","*"));
						calendar_commands.triggerSearch();
						break
					}
					var g=$("#prefs-box").height()-5,c=$("#calsearch_table tr").height(),d=55;
					"larry"==rcmail.env.skin&&(d=85);
					c=Math.max(75+(b+1)*c,d);
					$("#calsearchdialog").dialog("option","height",Math.min(g,c));
					0<a.filters.length&&$("#cal_search_field_categories").attr("checked",!1);
					$("#calsearchfilter").focus()
		}
	});
	rcmail.addEventListener("plugin.calendar_triggerSearch",function()
	{
		calendar_commands.triggerSearch()
	});
	rcmail.addEventListener("plugin.calendar_refresh",function(a){
		$("#calendar").fullCalendar("removeEvents");
		$("#calendar").fullCalendar("refetchEvents");
		$("#calendaroverlay").hide();
		$("#cal_boxtitle").html(a[0].content);
		rcmail.http_post("plugin.getTasks","_init=0")
	});
	
	rcmail.addEventListener("plugin.getTasks",function(a)
	{
		$("#taskscontent").html(a.html);
		var b=!1;
		0<$(".all").length?($("#call").html("("+$(".all").length+")").show(),$("#all").show(),b=!0):($("#call").html(0).hide(),$("#all").hide());
		0<$(".overdue").length?($("#coverdue").html("("+$(".overdue").length+")").show(),$("#overdue").addClass("overduered").show()):($("#coverdue").html(0).hide(),$("#overdue").removeClass("overduered").hide());
		0<$(".today").length?($("#ctoday").html("("+$(".today").length+")").show(),$("#today").show(),b=!0):($("#ctoday").html(0).hide(),$("#today").hide());
		0<$(".tomorrow").length?($("#ctomorrow").html("("+$(".tomorrow").length+")").show(),$("#tomorrow").show(),b=!0):($("#ctomorrow").html(0).hide(),$("#tomorrow").hide());
		0<$(".sevendays").length?($("#csevendays").html("("+$(".sevendays").length+")").show(),$("#sevendays").show(),b=!0):($("#csevendays").html(0).hide(),$("#sevendays").hide());
		0<$(".later").length?($("#clater").html("("+$(".later").length+")").show(),$("#later").show(),b=!0):($("#clater").html(0).hide(),$("#later").hide());
		0<$(".nodate").length?($("#cnodate").html("("+$(".nodate").length+")").show(),$("#nodate").show(),b=!0):($("#cnodate").html(0).hide(),$("#nodate").hide());
		0<$(".high").length?($("#chigh").html("("+$(".high").length+")").show(),$("#high").show(),b=!0):($("#chigh").html(0).hide(),$("#high").hide());
		0<$(".low").length?($("#clow").html("("+$(".low").length+")").show(),$("#low").show(),b=!0):($("#clow").html(0).hide(),$("#low").hide());
		0<$(".complete").length?($("#ccomplete").html("("+$(".complete").length+")").show(),$("#complete").show(),b=!0):($("#ccomplete").html(0).hide(),$("#complete").hide());
		b?$("#tasksfilter").show():$("#tasksfilter").hide();
		$("#tasks th.selected").trigger("click");
		a.script&&eval(a.script);
		a=rcmail.get_cookie("tasksvisible");
		(1==a||null===a)&&$("#tasks").show();
		$("#taskquickinput").focus();
		$("#calendaroverlay").hide()
	});
	rcmail.addEventListener("plugin.getTask",function(a){
		$("#calendaroverlay").hide();
		calendar_callbacks.eventClick(a,rcmail.env.calsettings,"vtodo")
	});
	rcmail.http_post("plugin.getTasks","_init=1");
	rcmail.addEventListener("plugin.getSettings",function(a)
	{
		rcmail.env.calsettings=a;
		if("caldav"==a.settings.backend)
		{
			var b=1E3;
			rcmail.env.noreplication&&(b=100);
			window.setTimeout("calendar_replicate.init(rcmail.env.calsettings, 'calendar');",b)
		}
		$("#cal_boxtitle").html(a.settings.boxtitle.content);
		$("#calusers").html(a.settings.usersselector.content);
		$("#categories_html").html(a.settings.categorieshtml);
		$("#taskscategories_html").html(a.settings.categorieshtml.replace("categories","taskscategories"));
		calendar_gui.initNavDatepicker(a);
		calendar_gui.initClockPickers(a);
		calendar_commands.init();
		b=a.settings.first_hour;
		-1==b&&(b=(new Date).getHours()-1);
		$("#calendar").fullCalendar({
			readyState:function(){
				"caldav"!=a.settings.backend&&(rcmail.env.cal_env="calendar",
				calendar_replicate.done("_"+Math.round((new Date).getTime()/1E3)));
				calendar_gui.init(a);
				$("#jqdatepicker").datepicker("setDate",$("#calendar").fullCalendar("getDate"));
				$("#upcoming").fullCalendar("addEventSource",
				$("#calendar").fullCalendar("clientEvents"));
				$("#upcoming").fullCalendar("gotoDate",$("#calendar").fullCalendar("getDate"))
			},
			header:{left:"",center:"",right:""},
			titleFormat:{month:a.settings.titleFormatMonth,week:a.settings.titleFormatWeek,day:a.settings.titleFormatDay},
			columnFormat:{month:a.settings.columnFormatMonth,week:a.settings.columnFormatWeek,day:a.settings.columnFormatDay},
			theme:a.settings.ui_theme_main,selectable:!0,unselectAuto:!1,
			height:$("#prefs-box").height(),
			lazyFetching:!1,
			editable:!0,
			ignoreTimezone:!1,
			eventSources:calendar_common.eSources(a,!0),
			monthNames:a.settings.months,
			monthNamesShort:a.settings.months_short,
			dayNames:a.settings.days,
			dayNamesShort:a.settings.days_short,
			firstDay:a.settings.first_day,
			firstHour:b,
			slotMinutes:60/a.settings.timeslots,
			timeFormat:js_time_formats[rcmail.env.rc_time_format],
			axisFormat:js_time_formats[rcmail.env.rc_time_format],
			defaultView:calendar_gui.getView(a),
			allDayText:rcmail.gettext("all-day","calendar"),
			height:$("#calendar-container").height()-20,
			viewDisplay:function(b)
			{
				calendar_callbacks.viewDisplay(b,a)
			},
			loading:function(a)
			{
				calendar_gui.minimalmode();rcmail.env.calendar_msgid=calendar_callbacks.loading(a,rcmail.env.calendar_msgid);

				calendar_callbacks.loading(a,rcmail.env.calendar_msgid)
			},
			eventRender:function(b,c,d)
			{
				calendar_callbacks.eventRender(b,c,d,a)
			},
			eventDragStart:function(b,c,d,e)
			{
				calendar_callbacks.eventDragStart(b,c,d,e,a)
			},
			eventDrop:function(b,c,d,e,f)
			{
				calendar_callbacks.eventDrop(b,c,d,e,f,a)
			},
			eventResize:function(b,c)
			{
				calendar_callbacks.eventResize(b,c,a)
			},
			eventResizeStop:function(a,b,d,e)
			{
				calendar_callbacks.eventResizeStop(a,b,d,e)
			},
			select:function(b,c,d,e,f)
			{
				calendar_callbacks.select(b,c,d,e,f,a)
			},
			dayClick:function(b,c,d,e)
			{
				calendar_callbacks.dayClick(b,c,d,e,a)
			},
			eventClick:function(b)
			{
				// HERE CALENDAR
				calendar_callbacks.eventClick(b,a)
			}
		});
		$("#taskstoggle").click(function()
		{
			"+"==$(this).html()?($(this).html("&minus;"),$(this).attr("title",rcmail.gettext("hide_tasks","calendar")),$("#tasks").show(),$("#calendar").css("right","450px"),rcmail.set_cookie("tasksvisible",1)):($(this).html("+"),$(this).attr("title",rcmail.gettext("show_tasks","calendar")),$("#tasks").hide(),$("#calendar").css("right","0"),rcmail.set_cookie("tasksvisible",0));
			$("#calendar").fullCalendar("render")});0==rcmail.get_cookie("tasksvisible")&&$("#taskstoggle").trigger("click");a.settings.date&&calendar_commands.gotoDate(a.settings.date)
	});
	var f=jzTimezoneDetector.determine_timezone();
	response_text=f.key;
	response_text="undefined"==typeof f.timezone?"undefined":f.timezone.olson_tz;
	rcmail.http_post("plugin.getSettings","_tzname="+response_text+"&_init=1");$("#calendar_button").attr("href","#");
	
	// SELECT2 PLUGIN
	$("#invitees_details_dropdown").select2();
	$("#external_invitee_email").autocomplete("../../../../../ajax.php?main=auto_complete", {
		selectFirst: true
	});
});