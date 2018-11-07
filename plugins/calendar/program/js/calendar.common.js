function cal_setCookie(a,c,e){var d=new Date;d.setDate(d.getDate()+e);c=escape(c)+(null==e?"":"; expires="+d.toUTCString());document.cookie=a+"="+c}cal_setCookie("clienttimezone",-3600*((new Date).getTimezoneOffset()/60),1);
function calendar_common()
{
	this.eSources=function(a)
	{
		var c=[],e=(new Date).getTimezoneOffset()/60,d;
		7<a.settings.cal_previews&&(a.settings.cal_previews=7);
		if("caldav"==a.settings.backend&&a.settings.caldavs&&a.settings.caldavs.length)
		{
			for(var b=0;b<a.settings.caldavs.length;b++)
				d=a.settings.caldavs[b]?a.settings.caldavs[b]:"",
			c[b]=$.fullCalendar.jsonFeed(rcmail.env.comm_path,
			{
				gmtoffset:e,
				additionaldays:a.settings.cal_previews,
				cals:"calendars",
				category:d
			});
			b-=1
		}
		else 
			b=-1;
			
		c[b+1]=$.fullCalendar.jsonFeed(rcmail.env.comm_path,
		{
			gmtoffset:e,
			additionaldays:a.settings.cal_previews,
			cals:"calendar"
		});
		c[b+2]=$.fullCalendar.jsonFeed(rcmail.env.comm_path,
		{
			gmtoffset:e,
			additionaldays:a.settings.cal_previews,
			cals:"layers"
		});
		return c
	};
	
	this.localeTimeString=function(a)
	{
		var c=new Date(a);
		if("undefined"!=typeof js_date_formats&&"undefined"!=typeof js_time_formats&&rcmail.env.rc_date_format&&rcmail.env.rc_time_format&&js_date_formats[rcmail.env.rc_date_format]&&js_time_formats[rcmail.env.rc_time_format]&&c.getTime())
			for(i in a=c.format("dddd, "+js_date_formats[rcmail.env.rc_date_format]+" "+js_time_formats[rcmail.env.rc_time_format]),Date.dayNames)
				a=a.replace(
					Date.dayNames[i],
				rcmail.gettext("calendar."+Date.dayNames[i].toLowerCase()));
		else 
			a=a.toLocaleString(),
		a=a.split("("),
		a=a[0].substr(0,a[0].length-3);
		return a
	};
	this.qtip=function(a,c)
	{
		if(!a)
			return c;
		var e="";
		"undefined"!=typeof a.location&&""!=a.location&&
		(
			e=e+"<div><hr /><small>@"+a.location+"</small></div>");
			"undefined"!=typeof a.classNameDisp&&""!=a.classNameDisp&&(e=e+"<div><hr /><small><i>["+a.classNameDisp+"]</i></small></div>");
			var d="";
			0!=a.start&&(d="<div style='white-space:nowrap'><small><img width='15' height='15' align='absmiddle' src='./plugins/calendar/skins/"+rcmail.env.skin+"/images/start.png' />&nbsp;"+this.localeTimeString(new Date(a.start))+"</small></div>");
			var b="";
			a.end&&(0<a.end&&(new Date(a.end)).getTime()>(new Date(a.start)).getTime())&&(b="<div style='white-space:nowrap'><small><img width='15' height='15' align='absmiddle' src='./plugins/calendar/skins/"+rcmail.env.skin+"/images/end.png' />&nbsp;"+this.localeTimeString(new Date(a.end))+"</small></div>");
			a.title||(a.title="---");
			if(a.title||a.description)
			{
				if("undefined"!=typeof a.description&&""!=a.description)
				{
					if(qtipbody=a.description)
						for(;-1<qtipbody.indexOf("\n");)
							qtipbody=qtipbody.replace("\n","<br>");
						ct={
							title:"<div><small>"+a.title+"</small></div><div><hr /></div>"+d+b,
							text:"<div><small>"+qtipbody+"</small></div>"+e
						}
				}
				else 
					ct={
						title:d+b,
						text:"<div><small>"+a.title+"</small></div>"+e
					};
				c.qtip({
					content:ct,
					position:{
						my:"top left",
						at:"left bottom",
						target:c,
						viewport:$(window)
					},
					hide:{
						effect:function()
						{
							$(this).slideUp(5,function()
							{
								$(this).dequeue()
							})
						}
					},
					style:{
						classes:"ui-tooltip-light"
					}
				})
			}
			return c
	};
	this.modifyEvents=function(a,c,e)
	{
		var d="";
		a.end||(a.end=a.start);
		a.title||(a.title=d);
		var b=a.title,
		f=1;
		a.start&&(
			a.end&&"basicDay"!=e.name&&"agendaDay"!=e.name
		)&&(
			f=a.end.getTime()-a.start.getTime(),
			f=parseInt(f/864E5),
			1>f&&(f=1)
		);
		b.length>25*f&&(b=b.substr(0,25*f-3)+"...");
		a.start&&a.end?0!=parseInt(a.start.getHours())+parseInt(a.start.getMinutes())||82!=parseInt(a.end.getHours())+parseInt(a.end.getMinutes())?(f=a.start.getTime()<e.visStart.getTime()?"":0!=parseInt(a.start.getHours())+parseInt(a.start.getMinutes())?a.start.format(js_time_formats[rcmail.env.rc_time_format]):"",
		a=a.end.getTime()>e.visEnd.getTime()||a.end==a.start?"":a.end.format(js_time_formats[rcmail.env.rc_time_format]),""!=f||""!=a?(""!=f&&(d=f+"&nbsp;<span class='fc' style='padding:2px;'>"),d+=b,""!=f&&(d+="</span>"),""!=a&&(d=d+"<span class='fc' style='float:right;'>&nbsp;"+a+"</span>"),c.find(".fc-event-title").html(d)):c.find(".fc-event-title").html(b)):c.find(".fc-event-title").html(b):c.find(".fc-event-title").html(b)
	}
}
calendar_common=new calendar_common;