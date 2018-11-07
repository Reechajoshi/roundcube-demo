function calendar_callbacks()
{
	this.setTimeline=function()
	{
		var a=$(".fc-agenda-slots:visible").parent(),
		b=a.children(".timeline");
		rcmail.env.calendar_timeline=b;
		0==b.length&&(
			b=$("<hr>").addClass("timeline"),
			a.prepend(b)
		);
		var c=new Date,
		e=$("#calendar").fullCalendar("getView");
		e.visStart<c&&e.visEnd>c?b.show():b.hide();
		c=(3600*c.getHours()+60*c.getMinutes()+c.getSeconds())/86400;
		a=Math.floor(a.height()*c);
		b.css("top",a+"px");
		if("agendaWeek"==e.name||"agendaDay"==e.name)
		{
			var a=1,c=0,d,f;
			if("agendaWeek"==e.name&&(f=$("#calendar .fc-today:visible"))&&f.position())
				try
				{
					c=f.position().left,
					d=f.width()+"px"
				}
				catch(g)
				{
					b.hide()
				}
				"agendaDay"==e.name&&(c=70,a=0,d="100%");
				0<c&&b.css({
					left:c+a+"px",
					width:d
				})
		}
		else 
			b.hide()
	};
	
	this.viewDisplay=function(a,b)
	{
		var c=$("#calendar").fullCalendar("getDate");
		wn=c.getWeekOfYearStartMonday(c);
		var e=new Date(c.getFullYear(),c.getMonth(),1,0,0,0);
		bds=c.getWeekOfYearStartMonday(e);
		e=new Date(
			c.getFullYear(),
			c.getMonth(),
			c.getDaysInMonth(),
			23,
			59,
			59
		);
		eds=c.getWeekOfYearStartMonday(e);
		c=bds+" - "+eds;
		"month"==a.name&&($("#monthbut").focus(),
		$("#datepicker-title").html("<center>"+a.title+"</center>"),
		$("#weeknumber").html("<center>"+b.settings.calendar_week+": "+c+"</center>"));
		"agendaDay"==a.name&&($("#daybut").focus(),
			$("#datepicker-title").html("<center>"+a.title+"</center>"),
			$("#weeknumber").html("&nbsp;")
		);
		"agendaWeek"==a.name&&($("#weekbut").focus(),
		$("#datepicker-title").html("<center>"+a.title+"</center>"),
		$("#weeknumber").html("<center>"+b.settings.calendar_week+": "+wn+"</center>"));
		
		if("agendaWeek"==a.name||"agendaDay"==a.name)
		{
			d&&window.clearInterval(d);
			var d=window.setInterval(calendar_callbacks.setTimeline,1E4);
			try
			{
				calendar_callbacks.setTimeline()
			}
			catch(f)
			{}
		}
		else 
			rcmail.env.calendar_timeline&&rcmail.env.calendar_timeline.hide()
	};
	this.qtip=function(a,b)
	{
		return b=calendar_common.qtip(a,b)
	};
	this.loading=function(a,b)
	{
		var c=$("#calendar").fullCalendar("getView"),
		e=(new Date).getTime(),
		d=c.start.getTime(),
		c=c.end.getTime();
		$("#todaybut").attr("title",new Date(e));
		e<d||e>c?$("#todaybut").prop("disabled",!1):$("#todaybut").prop("disabled",!0);
		a?($("#calendaroverlay").show(),
		b||(b=rcmail.set_busy(!0,"loading")),
		rcmail.enable_command("plugin.calendar_print",!1),
		rcmail.enable_command("plugin.events_print",!1),
		rcmail.enable_command("plugin.tasks_print",!1)):(rcmail.set_busy(!1,"loading",b),
		$("#calendaroverlay").hide(),
		rcmail.env.calpopup&&(rcmail.env.calpopup=calendar_commands.previewPrintEvents()),
		"show"!=rcmail.env.action&&calendar_commands.triggerSearch());
		b&&(rcmail.env.calendar_msgid=b);
		return b
	};
	this.eventRender_upcoming=function(a,b,c,e)
	{
		b=this.qtip(a,b);
		a.end&&1!=a.allDay?(
			c=a.end.format(js_time_formats[rcmail.env.rc_time_format]),
			b.find(".fc-event-title").html(" - "+c+" "+a.title)
		):
		calendar_common.modifyEvents(a,b,c,e);
		a.onclick&&b.bind("click",function(){})
	};
	this.eventRender=function(a,b,c,e)
	{
		rcmail.enable_command("plugin.calendar_print",!0);
		rcmail.enable_command("plugin.events_print",!0);
		rcmail.enable_command("plugin.tasks_print",!0);
		""==b.find("span.fc-event-title").html()&&b.find("span.fc-event-title").html("&nbsp;");
		b=this.qtip(a,b);
		if("month"!=c.name)
		{
			if(a.className&&!a.allDay)
			{
				var d=a.classNameDisp;
				d||(d=a.className);
				"null"==d&&(d="");
				""!=d&&b.find("div.fc-event-title").after('<div class="fc-event-categories">['+d+"]</div>")
			}
			a.location&&b.find("div.fc-event-title").after('<div class="fc-event-location">@'+a.location+"</div>");
			a.description&&!a.allDay&&(d=a.description,20<d.length&&(d=d.substring(0,20)+" ..."),
			b.find("div.fc-event-title").after('<div class="fc-event-description">'+d+"</div>"));
			d=".fc-event-time";
			if(a.allDay&&(calendar_common.modifyEvents(a,b,c,e),d=".fc-event-title",0!=a.rr))
				var f=b.find(".fc-event-title").html();
			0!=a.rr&&(
				f=b.find(d).html(),
				b.find(d).html(f+"&nbsp;<img style='padding: 0 1px' src='plugins/calendar/skins/"+rcmail.env.skin+"/images/recur.png' />")
			);
			0!=a.reminderservice&&(
				f=b.find(d).html(),
				b.find(d).html(f+"&nbsp;<img style='padding: 0 1px' src='plugins/calendar/skins/"+rcmail.env.skin+"/images/reminder.png' />")
			)
		}
		"month"==c.name&&(
			a.end&&1!=a.allDay?
			(
				c=a.end.format(js_time_formats[rcmail.env.rc_time_format]),
				b.find(".fc-event-title").html(" - "+c+" "+a.title)
			):
			calendar_common.modifyEvents(a,b,c,e)
		);
		a.onclick&&b.bind("click",function(){})
	};
	this.eventDragStart=function(a)
	{
		rcmail.env.clone_gap=0;
		rcmail.env.allday_drag=8634E4<=a.end.getTime()-a.start.getTime()&&864E5>=a.end.getTime()-a.start.getTime()?!0:!1;
		a.clone&&(rcmail.env.clone_gap=a.start.getTime()/1E3-a.clone);
		rcmail.env.replication_complete||rcmail.display_message(rcmail.gettext("backgroundreplication","calendar"),"error")
	};
	this.eventDrop=function(a,b,c,e,d,f)
	{
		if(rcmail.env.replication_complete)
		{
			e&&864E5>=a.end.getTime()-a.start.getTime()&&(
				a.start=new Date(
					a.start.getFullYear(),
					a.start.getMonth(),
					a.start.getDate(),
					0,
					0,
					0
				),
				a.end=new Date(
					a.start.getFullYear(),
					a.start.getMonth(),
					a.start.getDate(),
					23,
					59,
					0
				)
			);
			rcmail.env.allday_drag&&!e&&(
				a.end=new Date(
					a.start.getTime()+1E3*rcmail.env.calsettings.settings.duration
				)
			);
			rcmail.env.edit_event=a;
			rcmail.env.edit_dayDelta=b;
			rcmail.env.edit_minuteDelta=c;
			rcmail.env.edit_allDay=e;
			rcmail.env.edit_revertFunc=d;
			b=0;
			if(a.clone)
			{
				if(!rcmail.env.edit_recurring)
				{
					calendar_commands.edit_recurring_html("move");
					return
				}
				"initial"==rcmail.env.edit_recurring&&
				(
					a.start=new Date(a.start.getTime()-1E3*rcmail.env.clone_gap),
					a.end=new Date(a.end.getTime()-1E3*rcmail.env.clone_gap),
					b=1
				)
			}
			0!=a.rr&&(b=1);
			c=(new Date).getTime();
			if(a.past&&a.end.getTime()>=c||!a.past&&a.end.getTime()<c)
			{
				if(a.past&&a.end.getTime()>=c)
				{
					var g="#"+a.color_save;
					a.past=!1
				}
				!a.past&&a.end.getTime()<c&&(g="#"+lighter(a.backgroundColor,50),a.past=!0);
				a.color=g;
				a.backgroundColor=g;
				a.borderColor=g
			}
			g="";
			rcmail.env.edit_recurring&&(g="&_mode="+rcmail.env.edit_recurring);
			rcmail.env.clone_gap&&(g=g+"&_gap="+rcmail.env.clone_gap);
			rcmail.env.edit_recurring=!1;
			$("#calendaroverlay").show();
			msgid=rcmail.set_busy(!0,"loading");
			rcmail.http_post(
				"plugin.moveEvent",
				"_event_id="+a.id+"&_uid="+a.uid+"&_start="+Math.round(a.start.getTime()/1E3)+"&_end="+Math.round(a.end.getTime()/1E3)+"&_allDay="+e+"&_refetch="+b+"&_reminder="+a.reminder+"&_msgid="+msgid+g
			);
			calendar_gui.upcoming(f)
		}
		else 
			rcmail.display_message(rcmail.gettext("backgroundreplication","calendar"),"error"),
		d(a,b,c,e,d,f)
	};
	this.eventResizeStop=function()
	{
		rcmail.env.replication_complete||(
			rcmail.display_message(
				rcmail.gettext("backgroundreplication","calendar"),"error"),
				$("#calendar").fullCalendar("refetchEvents")
			)
	};
	this.eventResize=function(a,b)
	{
		if(rcmail.env.replication_complete)
		{
			var c=0;
			rcmail.env.edit_event=a;
			rcmail.env.edit_delta=b;
			if(a.clone)
			{
				if(!rcmail.env.edit_recurring)
				{
					calendar_commands.edit_recurring_html("resize");
					return
				}
				"initial"==rcmail.env.edit_recurring&&(
					c=a.start.getTime()/1E3-a.clone,
					a.start=new Date(1E3*a.clone),
					a.end=new Date(a.end.getTime()-1E3*c),
					c=1
				)
			}
			var e=(new Date).getTime();
			if(a.past&&a.end.getTime()>=e||!a.past&&a.end.getTime()<e)
			{
				if(a.past&&a.end.getTime()>=e)
				{
					var d="#"+a.color_save;
					a.past=!1
				}
				!a.past&&a.end.getTime()<e&&(d="#"+lighter(a.backgroundColor,50),a.past=!0);
				a.color=d;
				a.backgroundColor=d;
				a.borderColor=d
			}
			d="";
			rcmail.env.edit_recurring&&(d="&_mode="+rcmail.env.edit_recurring);
			rcmail.env.edit_recurring=!1;
			$("#calendaroverlay").show();
			msgid=rcmail.set_busy(!0,"loading");
			rcmail.http_post(
				"plugin.resizeEvent",
				"_event_id="+a.id+"&_uid="+a.uid+"&_start="+Math.round(a.start.getTime()/1E3)+"&_end="+Math.round(a.end.getTime()/1E3)+"&_allDay="+a.allDay+"&_reminder="+a.reminder+"&_refetch="+c+"&_msgid="+msgid+d)
		}
		else 
			rcmail.display_message(rcmail.gettext("backgroundreplication","calendar"),"error")
	};
	this.select=function(a,b,c,e,d,f)
	{
		this.dayClick(a,c,e,d,f,b)
	};

	this.dayClick=function(a,b,c,e,d,f)
	{
		if(c)
			if(rcmail.env.replication_complete)
			{
				$("#event .vtodo").hide();
				$("#event .vevent").show();
				$("#component").val("vevent");
				$("#catprotected").remove();
				
				// MACGREGOR CHANGES
				$("#summary").css( 'width', '350px' );
				$("#categories").css( 'width', '350px' );
				$("#location").css( 'width', '350px' );
				
				$("#custommail").attr("onclick","calendar_gui.custommail('new')");
				var g=$("#event");
				try
				{
					if(!0===g.dialog("isOpen"))
					return
				}
				catch(j)
				{
				}
				a||(a=new Date(a.getTime()/1E3));
				f||(f=new Date(1E3*(a.getTime()/1E3+d.settings.duration)));
				rcmail.env.cal_remindermailto&&$("#remindermailto").html(rcmail.env.cal_remindermailto);
				calendar_gui.resetForm(g);
				calendar_gui.initTabs(2,3);
				$("#hoursel").attr("checked",!0);
				calendar_gui.initExpireDatepicker(f,d);
				calendar_gui.initStartdateDatepicker(a,d);
				calendar_gui.initEnddateDatepicker(f,d);
				calendar_gui.initReminderDatepicker(a,d);
				calendar_gui.reminderControls(a);
				var m=$("#summary"),
				s=$("#description"),
				u=$("#categories");
				u||(u="");
				var l=$("#location"),
				n=$("#expires");
				c=a.getHours()+"";
				2>c.length&&(c="0"+c);
				e=a.getMinutes()+"";
				2>e.length&&(e="0"+e);
				c=c+":"+e;
				b&&"month"!=$("#calendar").fullCalendar("getView").name&&(c="00:00");
				// var starttime = $('#starttime').val(start_val);
				$("#starttime").val(c);
				var q=$("#starttime").val();
				c=f.getHours()+"";
				2>c.length&&(c="0"+c);
				f=f.getMinutes()+"";
				2>f.length&&(f="0"+f);
				f=c+":"+f;
				b&&(f="23:59");
				$("#endtime").val(f);
				var p=$("#endtime").val();
				f=$("#startdate").val()+"-"+$("#starttime").val().replace(":","-");
				f=f.split("-");
				
				var start_date = new Date(parseInt(f[0]),parseInt(f[1])-1,parseInt(f[2]),parseInt(f[3]),parseInt(f[4]),0);
				var formatted_start_date = calendar_gui.format_date( start_date );
				
				// $("#startfulltext").html("("+(new Date(parseInt(f[0]),parseInt(f[1])-1,parseInt(f[2]),parseInt(f[3]),parseInt(f[4]),0)).toLocaleString()+")");
				$("#startfulltext").html( "( " + formatted_start_date + " )" );
				f=$("#enddate").val()+"-"+$("#endtime").val().replace(":","-");
				f=f.split("-");
				
				var end_date = new Date(parseInt(f[0]),parseInt(f[1])-1,parseInt(f[2]), parseInt(f[3]),parseInt(f[4]),0);
				var formatted_end_date = calendar_gui.format_date( end_date );
				
				/* $("#endfulltext").html("("+(new Date(parseInt(f[0]),parseInt(f[1])-1,parseInt(f[2]), parseInt(f[3]),parseInt(f[4]),0)).toLocaleString()+")"); */
				$("#endfulltext").html( "(" + formatted_end_date + ")" );
				f=$("#duedate").val()+"-"+$("#duetime").val().replace(":","-");
				f=f.split("-");
				$("#duefulltext").html("("+(new Date(parseInt(f[0]),parseInt(f[1])-1,parseInt(f[2]),parseInt(f[3]),parseInt(f[4]),0)).toLocaleString()+")");
				$("#occurrences").val(0);
				var r=$("#recursel");
				r.val(0);
				calendar_gui.toggleRecur();
				var v=$("#calimport");
				f=rcmail.gettext("save","calendar");
				c=rcmail.gettext("cancel","calendar");
				e={};
				e[f]=function()
				{
					// SEND MAIL TEST
					if( calendar_gui.confirm_send_invitees() == true )
						sendmail = true;
					else
						sendmail = false;
					if(v.get(0)&&""!=v.val()){g.dialog("close");
					$("#calendaroverlay").show();
					rcmail.env.calendar_msgid=rcmail.set_busy(!0,"loading");
					var c=$('<iframe name="uploader" id="uploader" src="" style="width:0;height:0;visibility:hidden;" />');
					$("body").append(c);
					$("#form").submit();
					$("#uploader").load(function()
					{
						$("#calendar").fullCalendar("removeEvents");
						$("#calendar").fullCalendar("refetchEvents");
						rcmail.set_busy(!1,"loading",rcmail.env.calendar_msgid);
						$("#calendaroverlay").hide();
						v.val("")
					});
					return!1
					}
					if($("#event-3").is(":visible"))
						return!1;
					var c=$.trim($("#starttime").val()),
					e=$.trim($("#startdate").val()),
					f=$.trim($("#endtime").val());
					if(c!=q||f!=p)
						b=!1;
					var j=$.trim($("#enddate").val());
					j+" "+f>n.val()&&n.val(j+" "+f);
					if(0!=r.val()&&(h=n.val().split(" "),h[0]<=j&&0==$("#occurrences").val()))
						return alert(rcmail.gettext("calendar.verifyexpiredate")),
					$("#tab2").trigger("click"),
					n.val(h[0]),
					n.focus(),
					!1;
					var h=a.getFullYear(),
					w=a.getMonth()+1+"";
					2>w.length&&(w="0"+w);
					var y=a.getDate()+"";
					2>y.length&&(y="0"+y);
					j+" "+f<=h+"-"+w+"-"+y+" "+c&&(f=a.getTime()/1E3+d.settings.duration,
					f=new Date(1E3*f),
					c=f.getHours()+"",
					2>c.length&&(c="0"+c),
					h=a.getMinutes()+"",
					2>h.length&&(h="0"+h),
					f=$("#endtime").val(c+":"+h));
					var h=$("#starttime").val(),
					h=h.split(":"),
					f=h[0],
					j=h[1],
					h=$("#endtime").val(),
					h=h.split(":"),
					c=h[0],
					h=h[1],
					h=e.split("-"),
					c=new Date(parseFloat(h[0]),parseFloat(h[1])-1,parseFloat(h[2]),parseFloat(f),parseFloat(j),0),
					e=$("#remindercustom").val(),
					h=e.split(" "),
					e=h[0];
					if(h=h[1])
						h=h.split(":"),
					f=h[0],
					j=h[1],
					h=e.split("-"),
					e=new Date(parseFloat(h[0]),parseFloat(h[1])-1,parseFloat(h[2]),parseFloat(f),parseFloat(j),0),
					e=(c.getTime()-e.getTime())/1E3,
					$("#durationcustom").val(e);
					c=$("#occurrences");
					""==c.val()&&c.val(0);
					rrsec=recv=r.val();
					rrsec=recv.substr(0,1);
					recv=recv.substr(1);
					switch(rrsec)
					{
						case "d":
							recv*=parseInt($("#dnums").val());
							break;
						case "w":
							recv*=parseInt($("#wnums").val());
							break;
						case "m":
							recv*=parseInt($("#mnums").val());
							break;
						case "y":
							recv*=parseInt($("#ynums").val())
					}
					recv=rrsec+recv;
					var t="",
					x=-1,
					C=0;
					mydays="SU MO TU WE TH FR SA".split(" ");
					$("#form input[type=checkbox]").each(function()
					{
						x++;
						this.checked&&(C=x,t=7>x?t+mydays[x]+",":t+(x-6)+",")
					});
					t=t.substr(0,t.length-1);
					e="";
					6<C?e="&_bymonthday="+t:""!=t&&(e="&_byday="+t);
					"m"==rrsec?0!=$("xmevery").val()&&0!=$("#mweekday").val()&&(e="&_byday="+$("#mevery").val()+$("#mweekday").val()):"y"==rrsec&&(0!=$("#yevery").val()&&0!=$("yweekday").val()&&(e="&_byday="+$("#yevery").val()+$("#yweekday").val()),0!=$("#ymonth").val()&&(e=e+"&_bymonth="+$("#ymonth").val()),0!=$("#ydnums").val()&&0!=$("#ymonthday").val()&&(e=e+"&_bymonthday="+$("#ydnums").val()+"&_bymonth="+$("#ymonthday").val()));
					$("#calendaroverlay").show();
					msgid=rcmail.set_busy(!0,"loading");
					for(0==recv&&$("#occurrences").val(0);-1<m.val().indexOf("?");)
						m.val(m.val().replace("?","%3F"));
					for(;-1<s.val().indexOf("?");)
						s.val(s.val().replace("?","%3F"));
					for(;-1<l.val().indexOf("?");)
						l.val(l.val().replace("?","%3F"));
					for(;-1<m.val().indexOf("&");)
						m.val(m.val().replace("&","%26"));
					for(;-1<s.val().indexOf("&");)
						s.val(s.val().replace("&","%26"));
					for(;-1<l.val().indexOf("&");)
						l.val(l.val().replace("&","%26"));

					rcmail.http_post("plugin.newEvent",$("#form").serialize()+"&_summary="+m.val()+"&_sendmail="+sendmail+"&_description="+s.val()+"&_location="+l.val()+"&_categories="+u.val()+"&_allDay="+b+"&_occurrences="+c.val()+"&_recur="+recv+e+"&_msgid="+msgid);
					g.dialog("close");
					rcmail.env.cal_search_string=""
				};

				e[c]=function(){g.dialog("close")};g.dialog({modal:!0,width:630,position:"center",zIndex:5001,title:rcmail.gettext("new_event","calendar"),close:function(){g.dialog("destroy");
				g.hide()},buttons:e}).show();$("#summary").focus()
			}
			else 
				rcmail.display_message(rcmail.gettext("backgroundreplication","calendar"),"error")
	};

	this.eventClick=function(a,b,c)
	{
		if(a.editable&&!rcmail.env.replication_complete&&"false"==queryString("_event_id"))
			rcmail.display_message(rcmail.gettext("backgroundreplication","calendar"),"error");
		else
		{
			var e=a;
			// ATTENDEES MODIFICATION
			if( ( typeof( a[ 'selected_invitee_email_str' ] ) != "undefined" ) && ( a[ 'selected_invitee_email_str' ] !== null ) )
			{
				var selected_invitee_email_str = a[ 'selected_invitee_email_str' ];
				var selected_invitee_username_str = a[ 'selected_invitee_username_str' ];
				var selected_invitee_role_str = a[ 'selected_invitee_role_str' ];
				
				if( selected_invitee_email_str.length > 0 )
				{
					var selected_invitee_email_arr = selected_invitee_email_str.split( "|" );
					var selected_invitee_usename_arr = selected_invitee_username_str.split( "|" );
					var selected_invitee_role_arr = selected_invitee_role_str.split( "|" );
				}
			}
			if( ( typeof( a[ 'unselected_invitee_email_str' ] ) != "undefined" ) && ( a[ 'unselected_invitee_email_str' ] !== null ) )
			{
				var unselected_invitee_email_str = a[ 'unselected_invitee_email_str' ];
				var unselected_invitee_username_str = a[ 'unselected_invitee_username_str' ];
				var unselected_invitee_role_str = a[ 'unselected_invitee_role_str' ];
				
				if( unselected_invitee_email_str.length > 0 )
				{
					var unselected_invitee_email_arr = unselected_invitee_email_str.split( "|" );
					var unselected_invitee_username_arr = unselected_invitee_username_str.split( "|" );
					var unselected_invitee_role_arr = unselected_invitee_role_str.split( "|" );
				}
			}
			
			!c&&a.component&&(c=a.component);
			"vtodo"==c?
				($("#component").val("vtodo"),$("#event .vevent").hide(),$("#event .vtodo").show()):
				(c="vevent",$("#component").val("vevent"),$("#event .vtodo").hide(),$("#event .vevent").show());
			$("#custommail").attr("onclick","calendar_gui.custommail('edit')");
			if(a.onclick)
				-1<a.onclick.indexOf("?_task=mail&_action=compose")?
				"function"==typeof opencomposewindow?
					opencomposewindow(a.onclick):
					window.open(a.onclick):
				window.open(a.onclick);
			else
			{
				rcmail.env.edit_event=a;
				if("vtodo"==c)
					if(a.complete||(a.complete=0),
					$("#percentage").val(a.complete),
					$("#percentagedisplay").html(a.complete),
					$("#slider").slider({
						value:a.complete,
						step:5,
						stop:function(a,b){
							$("#percentage").val(b.value);
							$("#percentagedisplay").html(b.value);
							100==b.value?
								($("#endactive").prop("checked",!0),$("#statussel").val("COMPLETED")):
								0<b.value&&$("#statussel").val("IN-PROCESS")
								}
					}),
					0==a.start)
					{
						a.start=new Date(9E5*Math.round((new Date).getTime()/1E3/60/15+0.5));
						var d=!1
					}
					else
						"number"==typeof a.start&&(a.start=new Date(1E3*a.start),d=!0);
					
					var formatted_start_date = calendar_gui.format_date( a.start );
					var formatted_end_date = calendar_gui.format_date( a.end );
					
					// a.start&&0!=a.start&&$("#startfulltext").html("("+a.start.toLocaleString()+")");
					a.start&&0!=a.start&&$("#startfulltext").html("("+formatted_start_date+")");
					// a.end&&0!=a.end&&$("#endfulltext").html("("+a.end.toLocaleString()+")");
					a.end&&0!=a.end&&$("#endfulltext").html("("+formatted_end_date+")");
					a.due&&0!=a.due&&$("#duefulltext").html("("+(new Date(1E3*a.due)).toLocaleString()+")");
					
					if((a.clone||0!=a.rr)&&a.editable)
					{
						if(!rcmail.env.edit_recurring)
						{
							"vtodo"==c?
								a.clone?
									calendar_commands.edit_recurring_html("edit"):
									calendar_commands.edit_recurring_html("edit",!0):
								calendar_commands.edit_recurring_html("edit",!0);
							return
						}
						switch(rcmail.env.edit_recurring)
						{
							case "initial":
								if(a.initial)
									a=a.initial,
									a.start=new Date(1E3*a.start_unix),
									e=0,
									a.allDay&&(e=parseInt(a.start.getTimezoneOffset())),
									a.start=new Date(1E3*(parseInt(a.start_unix)+60*e)),
									a.end&&0!=a.end&&(a.end=new Date(1E3*a.end_unix),e=0,a.allDay&&(e=parseInt(a.end.getTimezoneOffset())),a.end=new Date(1E3*(parseInt(a.end_unix)+60*e))),
									rcmail.env.edit_recurring=!1,
									$("#calendaroverlay").hide(),
									$("#calendaroverlay").html("");
								else
								{
									a.clone?
										calendar_commands.gotoDate(a.clone,a.id):
										(e.initial=e,calendar_callbacks.eventClick(e,b,c));
									return
								}
								break;
							case "single":
								var f=a.start_unix;
								break;
							case "future":
								a.occurrences=Math.max(0,a.occurrences-a.hasoccurred)
						}
					}
					
					if(!a.end||0==a.end)
					{
						a.end=(c="vtodo",new Date(9E5*Math.round((new Date).getTime()/1E3/60/15+0.5)));
						var g=!1
					}
					else
						"number"==typeof a.end&&(a.end=new Date(1E3*a.end),g=!0);
					
					if(!a.due||0==a.due)
					{
						a.due=new Date(9E5*Math.round((new Date).getTime()/1E3/60/15+0.5));
						var j=!1
					}
					else
						"number"==typeof a.due&&(a.due=new Date(1E3*a.due),j=!0);
					
					// dialog content
					var m=$("#event");
					
					calendar_gui.resetForm(m);
					
					$("#startactive").prop("checked",d);
					$("#endactive").prop("checked",g);
					$("#dueactive").prop("checked",j);
					
					a.status&&$("#statussel").val(a.status);
					a.priority&&$("#prioritysel").val(a.priority);
					calendar_gui.initTabs(3,2);
					
					d=a.start;
					calendar_gui.initExpireDatepicker(a.start,b);
					calendar_gui.initStartdateDatepicker(a.start,b);
					calendar_gui.initEnddateDatepicker(a.start,b);
					calendar_gui.initDuedateDatepicker(a.due,b);
					calendar_gui.initReminderDatepicker(a.start,b);
					
					shour=d.getHours()+"";
					2>shour.length&&(shour="0"+shour);
					sminute=d.getMinutes()+"";
					2>sminute.length&&(sminute="0"+sminute);
					$("#starttime").val(shour+":"+sminute);
					var s=$("#starttime").val();
					ehour=(g=a.end)?g.getHours()+"":"0";
					2>ehour.length&&(ehour="0"+ehour);
					eminute=g?g.getMinutes()+"":"0";
					2>eminute.length&&(eminute="0"+eminute);
					b=g.getDate()+"";
					2>b.length&&(b="0"+b);
					d=g.getMonth()+1+"";
					2>d.length&&(d="0"+d);
					g=g.getFullYear()+"";
					$("#enddate").val(g+"-"+d+"-"+b);
					$("#endtime").val(ehour+":"+eminute);
					
					var u=$("#endtime").val();
					dhour=(g=a.due)?g.getHours()+"":"0";
					
					2>dhour.length&&(dhour="0"+dhour);
					dminute=g?g.getMinutes()+"":"0";2>dminute.length&&(dminute="0"+dminute);b=g.getDate()+"";
					2>b.length&&(b="0"+b);
					d=g.getMonth()+1+"";
					2>d.length&&(d="0"+d);
					g=g.getFullYear()+"";
					
					$("#duedate").val(g+"-"+d+"-"+b);
					$("#duetime").val(dhour+":"+dminute);
					$("#duetime").val();
					
					var l=$("#summary").val(a.title),
					n=$("#description").val(a.description),
					q=$("#location").val(a.location),
					p=$("#categories"),
					r=a.classNameDisp;
					
					a.classNameICS&&(r=a.classNameICS);
					r||(r=a.className);
					""!=r?
						p.val(r):
						p.prop("selectedIndex",0);
						
					a.classProtected&&a.editable?
						(p.prop("disabled",!0),
						$("#catprotected").remove(),
						p.parent().append('<span id="catprotected">&nbsp;<i>('+rcmail.gettext("calendar.protected")+")</i></span>")):
						$("#catprotected").remove();
						
					var v=p.val();
					b=(new Date(1E3*(parseInt(a.expires)+3600*parseInt((new Date(a.start)).getTimezoneOffset()/60)))).format("yyyy-MM-dd");
					var z=$("#expires").val(b),
					A=$("#occurrences").val(a.occurrences);
					$("#export_as_file").attr("href","./?_task=dummy&_action=plugin.calendar_single_export_as_file&_id="+a.id+"&_edit="+a.editable);
					$("#send_invitation").attr("href","./?_task=dummy&_action=plugin.calendar_send_invitation&_id="+a.id+"&_edit="+a.editable);
					var k=!1;
					
					// event.rr
					switch(a.rr)
					{
						case "d":
							k=86400;
							break;
						case "w":
							k=604800;
							break;
						case "m":
							k=2592E3;
							break;
						case "y":
							k=31536E3
					}
					var B=$("#recursel");
					a.recur&&0!=a.recur&&k&&B.val(a.rr+k);
					calendar_gui.toggleRecur("recursel");
					if(rrsec=k=B.val())
						rrsec=k.substr(0,1),
						k=k.substr(1);
					// b = weekdays	
					b=[];
					b.SU=0;
					b.MO=1;
					b.TU=2;
					b.WE=3;
					b.TH=4;
					b.FR=5;
					b.SA=6;
					d=a.byday.split(",");
					
					switch(rrsec)
					{
						case "d":
							$("#dnums").val(parseInt(a.recur)/86400);
							break;
						case "w":
							$("#wnums").val(parseInt(a.recur)/604800);
							if(0!=a.byday)
							{
								calendar_gui.currentStyle("byweekdayslink")&&$("#byweekdayslink").click();
								for(var g=document.forms.form,h=0;h<g.elements.length&&"checkbox"!=g.elements[h].type;h++);
								for(j=0;j<d.length;j++)
									if(void 0!=typeof b[d[j]])
										try{g.elements[h+b[d[j]]].checked=!0}
										catch(w){}
							}
							break;
						case "m":
							$("#mnums").val(parseInt(a.recur)/2592E3);
							if(0!=a.bymonthday)
							{
								calendar_gui.currentStyle("bymonthdayslink")&&$("#bymonthdayslink").click();
								d=a.bymonthday.split(",");
								g=document.forms.form;
								for(h=0;h<g.elements.length&&"checkbox"!=g.elements[h].type;h++);
								for(j=0;j<d.length;j++)
									0<d[j]&&(g.elements[h+7+parseInt(d[j])].checked=!0)
							}
							0!=a.byday&&(calendar_gui.currentStyle("byweekdaylink")&&$("#byweekdaylink").click(),
							$("#mweekday").val(a.byday.substr(a.byday.length-2)),
							$("#mevery").val(a.byday.substr(0,a.byday.length-2)));
							break;
						case "y":
							$("#ynums").val(parseInt(a.recur)/31536E3),
							0!=a.bymonthday&&0!=a.bymonth&&(calendar_gui.currentStyle("bymonthdaylink")&&$("#bymonthdaylink").click(),
							$("#ydnums").val(a.bymonthday),
							$("#ymonthday").val(a.bymonth)),
							0!=a.byday&&(0!=a.bymonth&&0==a.bymonthday)&&(calendar_gui.currentStyle("byyeardaylink")&&$("#byyeardaylink").click(),$("#yweekday").val(a.byday.substr(a.byday.length-2)),$("#yevery").val(a.byday.substr(0,a.byday.length-2)),$("#ymonth").val(a.bymonth))
					}
					if(0!=a.reminderservice)
						switch($("#reminderenable").attr("checked",!0),a.reminderservice)
						{
							case "email":
								$("#remindertype").prop("selectedIndex",0);
								$("#remindermailto").prop("disabled",!1);
								break;
							case "popup":
								$("#remindertype").prop("selectedIndex",1),
								$("#remindermailto").prop("disabled",!0)
						}
					else 
						$("#reminderdisable").attr("checked",!0);
						
					a.remindermailto&&(
						b=a.remindermailto,
						$("#remindermailto").val(b),
						$("#remindermailto").val()!=b&&($("#remindermailto").html($("#remindermailto").html()+'<option value="'+b+'">'+b+"</option>"),
						$("#remindermailto").val(b))
					);
					
					0<a.reminder?
					(
						d=!0,
						1<=a.reminder/604800?
						(
							b=a.reminder/604800,
							b==Math.round(b)&&0<$("#reminderweeksbefore option[value="+b+"]").length&&($("#weeksel").attr("checked",!0),
							$("#reminderweeksbefore").val(b),d=!1)
						):
						1<=a.reminder/86400?
						(
							b=a.reminder/86400,
							b==Math.round(b)&&0<$("#reminderdaysbefore option[value="+b+"]").length&&($("#daysel").attr("checked",!0),
							$("#reminderdaysbefore").val(b),
							d=!1
							)
						):
						1<=a.reminder/3600?
						(
							b=a.reminder/3600,
							b==Math.round(b)&&0<$("#reminderhoursbefore option[value="+b+"]").length&&($("#hoursel").attr("checked",!0),
							$("#reminderhoursbefore").val(b),
							d=!1
							)
						):
						1<=a.reminder/60&&(b=a.reminder/60,
						
						b==Math.round(b)&&0<$("#reminderminutesbefore option[value="+b+"]").length&&($("#minsel").attr("checked",!0),
						$("#reminderminutesbefore").val(b),d=!1)),
						d&&
						(
							$("#customreminder").attr("checked",!0),
							e=new Date(a.start.getTime()-1E3*a.reminder),
							b=e.getFullYear(),
							d=e.getMonth()+1+"",
							2>d.length&&(d="0"+d),
							g=e.getDate()+"",
							2>g.length&&(g="0"+g),
							j=e.getHours()+"",
							2>j.length&&(j="0"+j),
							e=e.getMinutes()+"",
							2>e.length&&(e="0"+e),
							$("#remindercustom").val(b+"-"+d+"-"+g+" "+j+":"+e)
						)
					):
						$("#hoursel").attr("checked",!0);
						
					// ALL DAY EVENT
					if( a.all_day == '1' )
					{
						$('#event_allday').attr( 'checked', 'checked' );
						$('#starttime').attr('readonly',true).datepicker("destroy");
						$('#endtime').attr('readonly',true).datepicker("destroy");
						$('#starttime').val( '00:00' );
						$('#endtime').val( '23:59' );
					}
					else
					{
						$('#event_allday').removeAttr( 'checked' );
						calendar_gui.initClockPickers();
					}
					
					calendar_gui.reminderControls(a.start);
					rcmail.env.event_initial=a.initial?
						!1:
						$("#form").serialize();
					b=rcmail.gettext("save","calendar");
					d=rcmail.gettext("remove","calendar");
					g=rcmail.gettext("cancel","calendar");
					j={};
					calendar_gui.add_existing_attending_member( selected_invitee_email_arr, selected_invitee_usename_arr, selected_invitee_role_arr, unselected_invitee_email_arr, unselected_invitee_username_arr, unselected_invitee_role_arr );
					
					a.editable||"undefined"==typeof a.editable?
					(
						j[b]=function(){
							if(rcmail.env.event_initial==$("#form").serialize())
							{
								$("#rcmrow"+a.id).removeClass("selected"),
								$("#rcmmatch"+a.id).removeClass("calsearchmatchselected"),
								$("#rcmmatch"+a.id).addClass("calsearchmatch");
							}
							else
							{
								// SEND MAIL TEST
								if( calendar_gui.confirm_send_invitees() == true )
									sendmail = true;
								else
									sendmail = false;
									
								a.title=l.val();
								a.description=n.val();
								a.location=q.val();
								a.className=p.val();
								var b=$.trim($("#starttime").val()),
								c=$.trim($("#endtime").val());
								if(b!=s||c!=u)
									a.allDay=!1;
								var c=$("#starttime").val(),
								c=c.split(":"),
								b=c[0],
								d=c[1],
								c=$("#endtime").val();
								c.split(":");
								startdate=$("#startdate").val();
								c=startdate.split("-");
								b=new Date(
									parseFloat(c[0]),
									parseFloat(c[1])-1,
									parseFloat(c[2]),
									parseFloat(b),
									parseFloat(d),
									0
								);
								c=$("#endtime").val();
								d=$("#enddate").val();
								d+" "+c>z.val()+" "+$("#starttime").val()&&z.val(d+" "+c);
								c=$("#remindercustom").val();
								c=c.split(" ");
								d=c[0];
								if(c=c[1])
								{
									var c=c.split(":"),
									e=c[0],
									g=c[1],
									c=d.split("-"),
									c=new Date(
										parseFloat(c[0]),
										parseFloat(c[1])-1,
										parseFloat(c[2]),
										parseFloat(e),
										parseFloat(g),
										0
									),
									c=(b.getTime()-c.getTime())/1E3;
									$("#durationcustom").val(c)
								}
								z.val().split("-");
								""==A.val()&&A.val(0);
								b="";
								$("#categories").val()&&(b=b+"&_categories="+p.val());
								a.token&&(b=b+"&_ct="+a.token);
								a.userid&&(b=b+"&_userid"+a.userid);
								rrsec=k=B.val();
								rrsec=k.substr(0,1);
								k=k.substr(1);
								switch(rrsec)
								{
									case "d":
										k*=parseInt($("#dnums").val());
										break;
									case "w":
										k*=parseInt($("#wnums").val());
										break;
									case "m":
										k*=parseInt($("#mnums").val());
										break;
									case "y":
										k*=parseInt($("#ynums").val())
								}
								k=rrsec+k;
								c=document.forms.form;
								d="";
								e=-1;
								g=0;
								
								mydays="SU MO TU WE TH FR SA".split(" ");
								for(h=0;h<c.elements.length;h++)
									"checkbox"==c.elements[h].type&&(
										e++,
										!0==c.elements[h].checked&&(
											g=e,
											d=7>e?
												d+mydays[e]+",":
												d+(e-6)+","
										)
									);
									
								// rrule
								d=d.substr(0,d.length-1);
								6<g?
									b="&_bymonthday="+d:
									""!=d&&(b="&_byday="+d);
								"m"==rrsec?
									0!=$("#mevery").val()&&0!=$("#mweekday").val()&&
									(
										b="&_byday="+$("#mevery").val()+$("#mweekday").val()
									):
									"y"==rrsec&&
									(
										0!=$("#yevery").val()&&0!=$("#yweekday").val()&&
										(
											b="&_byday="+$("#yevery").val()+$("#yweekday").val()
										),
										0!=$("#ymonth").val()&&
										(
											b=b+"&_bymonth="+$("#ymonth").val()
										),
										0!=$("#ydnums").val()&&0!=$("#ymonthday").val()&&(b=b+"&_bymonthday="+$("#ydnums").val()+"&_bymonth="+$("#ymonthday").val())
									);
									
								$("#calendaroverlay").show();
								msgid=rcmail.set_busy(!0,"loading");
								c=0;
								rcmail.env.recurselnever&&(c=1);
								0==k&&$("#occurrences").val(0);
								d=0;
								r!=p.val()&&(d=1);
								a.recurrence_id&&(b=b+"&_recurrence_id="+a.recurrence_id);
								f&&(b=b+"&_recurrence_id="+f);
								for(rcmail.env.edit_recurring&&(b=b+"&_mode="+rcmail.env.edit_recurring);-1<l.val().indexOf("?");)
									l.val(l.val().replace("?","%3F"));
								for(;-1<n.val().indexOf("?");)
									n.val(n.val().replace("?","%3F"));
								for(;-1<q.val().indexOf("?");)
									q.val(q.val().replace("?","%3F"));
								for(;-1<l.val().indexOf("&");)
									l.val(l.val().replace("&","%26"));
								for(;-1<n.val().indexOf("&");)
									n.val(n.val().replace("&","%26"));
								for(;-1<q.val().indexOf("&");)
									q.val(q.val().replace("&","%26"));
								
								rcmail.http_post("plugin.editEvent",$("#form").serialize()+"&_event_id="+a.id+"&_uid="+a.uid+"&_sendmail="+sendmail+"&_summary="+l.val()+"&_description="+n.val()+"&_location="+q.val()+"&_categories="+p.val()+"&_old_categories="+v+"&_allDay="+a.allDay+"&_occurrences="+A.val()+"&_recur="+k+b+"&_recurselnever="+c+"&_reload="+d+"&_edit=1&_msgid="+msgid+b);
								
								rcmail.env.cal_search_string=""
							}
							m.dialog("close")
						},
						// buttons[remove]
						j[d]=function()
						{
							var b="";
							rcmail.env.edit_recurring&&(b="&_mode="+rcmail.env.edit_recurring);
							$("#rcmrow"+a.id).remove();
							$("#calendaroverlay").show();
							msgid=rcmail.set_busy(!0,"loading");
							rcmail.http_post("plugin.removeEvent","_event_id="+a.id+"&_start="+a.start_unix+"&_msgid="+msgid+"&_uid="+a.uid+b);
							$("#calendar").fullCalendar("removeEvents",a.id);
							$("#upcoming").fullCalendar("removeEvents",a.id);
							m.dialog("close");
							rcmail.env.cal_search_string="";
							calendar_commands.triggerSearch()
						}
					):
					$("input, textarea, select","#form").each(function()
					{
						$(this).prop("disabled",!0);
						$("#event").tabs("disable",1);
						$("#event").tabs("disable",3);
						$("#event").tabs("disable",4)
					});
					
					// buttons[cancel]
					j[g]=function()
					{
						$("#rcmrow"+a.id).removeClass("selected");
						$("#rcmmatch"+a.id).removeClass("calsearchmatchselected");
						$("#rcmmatch"+a.id).addClass("calsearchmatch");
						m.dialog("close")
					};
					m.dialog({
						modal:!1,
						title:"vtodo"==c?rcmail.gettext("edit_task","calendar"):rcmail.gettext("edit_event","calendar"),width:630,
						zIndex:5001,
						position:"center",
						close:function()
						{
							m.dialog("destroy");
							m.hide();
							rcmail.env.edit_recurring=!1
						},
						buttons:j
					}).show();
					
					$("#summary").focus()
			}
		}
	}

}calendar_callbacks=new calendar_callbacks;