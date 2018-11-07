(function(j)
{
	j.fullCalendar.jsonFeed=function(k,c){
		// HACK
		username_span = $(".username");
		username = username_span.html();
		c=c||{};
		return function(f,g,l) // start end callback
		{
			// console.log( l );
			var e=DstDetect(f); // arr
			e[0]||(e[0]=new Date(0));
			e[1]||(e[1]=new Date(0));
			c.additionaldays&&(g=new Date(g.getTime()+864E5*parseInt(c.additionaldays)));
			var e=c.category?c.category:'',
			m="layers"==c.cals?"plugin.calendar_fetchalllayers":"plugin.getEvents",
			h=jzTimezoneDetector.determine_timezone();
			response_text=h.key;
			response_text="undefined"==typeof h.timezone?"undefined":h.timezone.olson_tz;
			f={
				_action:m,
				_start:Math.round(f.getTime()/1E3),
				_end:Math.round(g.getTime()/1E3)+86400,
				_category:e,
				_tzname:response_text,
				_echo:1
			};
			j.ajax({
				url:k,
				data:f,
				dataType:"json",
				error:function(c,a,b){
					console.log("calendar.jsonfeeds.js Ajax Error: "+b);
					l({})
				},
				success:function(c){
					// window.debug_print(c);
					var a=[];
					c&&(a=c);
					if("object"==typeof a)
					{
						// console.log( a );
						for(var b in a)
						{
							if(a[b].end)
							{
								86340<=a[b].end-a[b].start&&(a[b].allDay=!0);
								var d=0;
								(new Date(1E3*a[b].start)).getTimezoneOffset()>(new Date(1E3*a[b].end)).getTimezoneOffset()&&(d=-3600,a[b].end-a[b].start==86400+d?a[b].allDay=!0:a[b].end-a[b].start>=86340+d&&(a[b].allDay=!0));
								(new Date(1E3*a[b].start)).getTimezoneOffset()<(new Date(1E3*a[b].end)).getTimezoneOffset()&&(d=3600,a[b].end-a[b].start==86400+d?a[b].allDay=!0:a[b].end-a[b].start>=86340+d&&(a[b].allDay=!0))
							}
							else 
								a[b].allDay=!0;
							(a[b].end-a[b].start-d)/86400==Math.floor((a[b].end-a[b].start-d)/86400)&&(a[b].end-=60)
						}l(a)
					}
				},
				complete:function(c,a)
				{
					"undefined"!=typeof a&&"error"==a&&console.log(rcmail.gettext("calendar.invalidresponse")+": "+k)
				}
			})
		}
	}
})(jQuery);

window.debug_print = function(x,ext)
{
	if(x)
	{
		if(typeof(ext)=="undefined")
			ext="";
			
		for(var k in x)
		{
			if( typeof(k) != "undefined" && typeof(x[k]) != "undefined")
			{
				
				if(typeof(x[k]) == "object")
					window.debug_print(x[k],"  ");
			}
		}
		console.debug("========================");
	}
}
