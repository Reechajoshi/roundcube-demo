/*

 FullCalendar v1.5.1
 http://arshaw.com/fullcalendar/

 Use fullcalendar.css for basic styling.
 For event drag & drop, requires jQuery UI draggable.
 For event resizing, requires jQuery UI resizable.

 Copyright (c) 2011 Adam Shaw
 Dual licensed under the MIT and GPL licenses, located in
 MIT-LICENSE.txt and GPL-LICENSE.txt respectively.

 Date: Sat Apr 9 14:09:51 2011 -0700

*/
(function(h,y)
{
	function ob(a,b,d)
	{
		// console.log( "ob called" );
		function e()
		{
			// console.log( "e called" );
			setTimeout(function()
			{
				// console.log( "setimeout called" );
				!u.start&&0!==h("body")[0].offsetWidth&&f()
			},0)
		}
		function c(a)
		{
			// console.log( "c called" );
			if(!u||a!=u.name)
			{
				H++;
				r();
				var b=u,d;
				b?
				((b.beforeHide||Sa)(),va(B,B.height()),b.element.hide()):
				va(B,1);
				B.css("overflow","hidden");
				(u=K[a])?
				u.element.show():
                console.log( "before error..." );
                console.log( "d: " );
                console.log( d );
                console.log( "Q: " );
                console.log( Q );
                console.log( "h: " );
                console.log( h );
                console.log( "a: " );
                console.log(  );
				u=K[a]=new ea[a](d=Q=h("<div class='fc-view fc-view-"+a+"' style='position:absolute'/>").appendTo(B),x);
				b&&E.deactivateButton(b.name);
				E.activateButton(a);
				f();
				B.css("overflow","");
				b&&va(B,1);
				d||(u.afterShow||Sa)();
				H--
			}
		}
		
		function f(d)
		{
			// console.log( "f called" );
			if(0!==z.offsetWidth)
			{
				H++;
				r();
				za===y&&n();
				var c=!1;
				!u.start||d||w<u.start||w>=u.end?(u.render(w,d||0),k(!0),c=!0):u.sizeDirty?(u.clearEvents(),k(),c=!0):u.eventsDirty&&(u.clearEvents(),c=!0);u.sizeDirty=!1;
				u.eventsDirty=!1;
				d=c;
				!b.lazyFetching||l(u.visStart,u.visEnd)?m():d&&v();
				V=a.outerWidth();
				E.updateTitle(u.title);
				d=new Date;
				d>=u.start&&d<u.end?E.disableButton("today"):E.enableButton("today");
				H--;
				u.trigger("viewDisplay",z)
			}
		}
		function j()
		{
			// console.log( "j called" );
			t();
			0!==z.offsetWidth&&(n(),k(),r(),u.clearEvents(),u.renderEvents(C),u.sizeDirty=!1)
		}
		function t()
		{
			// console.log( "t called" );
			h.each(K,function(a,b){b.sizeDirty=!0})
		}
		function n()
		{
			// console.log( "n called" );
			za=b.contentHeight?b.contentHeight:b.height?b.height-(L?L.height():0)-qa(B):Math.round(B.width()/Math.max(b.aspectRatio,0.5))
		}
		function k(a)
		{
			// console.log( "k called" );
			// console.log( "a" );
			// console.log( a );
			H++;
			u.setHeight(za,a);
			Q&&(Q.css("position","relative"),Q=null);
			u.setWidth(B.width(),a);
			H--
		}
		function g()
		{
			// console.log( "g called" );
			if(!H)
				if(u.start)
				{
					var b=++S;
					setTimeout(function()
					{
						if(b==S&&(!H&&0!==z.offsetWidth)&&V!=(V=a.outerWidth()))
							H++,
						j(),
						u.trigger("windowResize",z),
						H--
					},
					200)
				}
				else e()
		}
		function m()
		{
			// console.log( "m called" );
			q(u.visStart,u.visEnd)
		}
		function v(a)
		{
			// console.log( "before render events" );
			// console.log( "c" );
			// console.log( c );
			// console.log( "a" );
			// console.log( a );
			// console.log( "v called" );
			O();
			0!==z.offsetWidth&&(u.clearEvents(),
			u.renderEvents(C,a),
			u.eventsDirty=!1)
		}
		function O()
		{
			// console.log( "o called" );
			h.each(K,function(a,b){b.eventsDirty=!0})
		}
		function r()
		{
			// console.log( "r called" );
			u&&u.unselect()
		}
		var x=this;
		x.options=b;
		x.render=function(d)
		{
			// console.log( "x.render called" );
			B?(n(),t(),O(),f(d)):
			(a.addClass("fc"),b.isRTL&&a.addClass("fc-rtl"),b.theme&&a.addClass("ui-widget"),B=h("<div class='fc-content' style='position:relative'/>").prependTo(a),E=new pb(x,b),(L=E.render())&&a.prepend(L),c(b.defaultView),h(window).resize(g),0!==h("body")[0].offsetWidth||e())
		};
		x.destroy=function()
		{
			// console.log( "x.destroy called" );
			h(window).unbind("resize",g);E.destroy();B.remove();a.removeClass("fc fc-rtl ui-widget")
		};
		x.refetchEvents=m;
		x.reportEvents=function(a)
		{
			// console.log( "x.reportEvents called" );
			C=a;
			v()
		};
		x.reportEventChange=function(a)
		{
			// console.log( "x.reporteventchange called" );
			v(a)
		};
		x.rerenderEvents=v;
		x.changeView=c;
		x.select=function(a,b,d)
		{
			// console.log( "x.select called" );
			u.select(a,b,d===y?!0:d)
		};
		x.unselect=r;
		x.prev=function()
		{
			// console.log( "x.prev called" );
			f(-1)
		};
		x.next=function()
		{
			// console.log( "x.next called" );
			f(1)
		};
		x.prevYear=function()
		{
			// console.log( "x.prevYear called" );
			Aa(w,-1);
			f()
		};
		x.nextYear=function()
		{
			// console.log( "x.nextYear called" );
			Aa(w,1);
			f()
		};
		x.today=function()
		{
			// console.log( "x.today called" );
			w=new Date;
			f()
		};
		x.gotoDate=function(a,b,d)
		{
			// console.log( "x.gotoDate called" );
			a instanceof Date?
			w=p(a):
			Ta(w,a,b,d);
			f()
		};
		x.incrementDate=function(a,b,d)
		{
			// console.log( "x.incrementDate called" );
			a!==y&&Aa(w,a);
			b!==y&&Ba(w,b);
			d!==y&&s(w,d);
			f()
		};
		x.formatDate=function(a,d)
		{
			// console.log( "x.formatDate called" );
			return fa(a,d,b)
		};
		x.formatDates=function(a,d,c)
		{
			// console.log( "x.formatDates called" );
			return Ca(a,d,c,b)
		};
		x.getDate=function()
		{
			// console.log( "x.geDate called" );
			return p(w)
		};
		x.getView=function()
		{
			// console.log( "x.getView called" );
			return u
		};
		x.option=function(a,d)
		{
			// console.log( "x.otions called" );
			if(d===y)
				return b[a];
			if("height"==a||"contentHeight"==a||"aspectRatio"==a)
				b[a]=d,j()
		};
		x.trigger=function(a,d)
		{
			// console.log( "x.trigger called" );
			// console.log( "a" );
			// console.log( a );
			// console.log( "d" );
			// console.log( d );
			if(b[a])
				return b[a].apply(d||z,Array.prototype.slice.call(arguments,2))
		};
		qb.call(x,b,d);
		var l=x.isFetchNeeded,
		q=x.fetchEvents,
		z=a[0],E,L,B,u,K={},V,za,Q,S=0,H=0,
		w=new Date,
		C=[],F;
		Ta(w,b.year,b.month,b.date);
		b.droppable&&h(document).bind("dragstart",function(a,d)
		{
			// console.log( "bind function 1 called" );
			var c=a.target,E=h(c);
			if(!E.parents(".fc").length)
			{
				var e=b.dropAccept;
				if(h.isFunction(e)?e.call(c,E):E.is(e))
					F=c,u.dragStart(F,a,d)
			}
		})
		.bind("dragstop",function(a,b)
		{
			// console.log( "bind function 2 called" );
			F&&(u.dragStop(F,a,b),F=null)
		}
		)
	}
	function pb(a,b)
	{
		// console.log( "pb called" );
		function d(d)
		{
			// console.log( "d called" );
			var e=h("<td class='fc-header-"+d+"'/>");
			(d=b.header[d])&&h.each(d.split(" "),
			function(d)
			{
				// console.log( "d 2 called" );
				0<d&&e.append("<span class='fc-header-space'/>");
				var f;
				h.each(this.split(","),
					function(d,g)
					{
						// console.log( "dg called" );
						if("title"==g)
							e.append("<span class='fc-header-title'><h2>&nbsp;</h2></span>"),
							f&&f.addClass(c+"-corner-right"),
							f=null;
						else
						{
							var t;
							a[g]?
							t=a[g]:
							ea[g]&&
							(t=function()
							{
								// console.log( "t called" );
								r.removeClass(c+"-state-hover");
								a.changeView(g)
							});
							if(t)
							{
								var p=b.theme?
								Da(b.buttonIcons,g):
								null,
								s=Da(b.buttonText,g),
								r=h("<span class='fc-button fc-button-"+g+" "+c+"-state-default'><span class='fc-button-inner'><span class='fc-button-content'>"+(p?"<span class='fc-icon-wrap'><span class='ui-icon ui-icon-"+p+"'/></span>":s)+"</span><span class='fc-button-effect'><span></span></span></span></span>");
								r&&(r.click(function()
									{
										// console.log( "r clidk called" );
										r.hasClass(c+"-state-disabled")||t()
									})
									.mousedown(function()
									{
										// console.log( "mousedown called" );
										r.not("."+c+"-state-active").not("."+c+"-state-disabled").addClass(c+"-state-down")
									})
									.mouseup(function()
									{
										// console.log( "mouseup  called" );
										r.removeClass(c+"-state-down")
									})
									.hover(function()
									{
										// console.log( "hover called" );
										r.not("."+c+"-state-active").not("."+c+"-state-disabled").addClass(c+"-state-hover")
									},
									function()
									{
										// console.log( "only function called" );
										r.removeClass(c+"-state-hover").removeClass(c+"-state-down")
									})
									.appendTo(e),
									f||r.addClass(c+"-corner-left"),f=r
								)
							}
						}
					}
				);
				f&&f.addClass(c+"-corner-right")
			});
			return e
		}
		this.render=function()
		{
			// console.log( "render  called" );
			c=b.theme?
			"ui":
			"fc";
			if(b.header)
				return e=h("<table class='fc-header' style='width:100%'/>").append(h("<tr/>").append(d("left")).append(d("center")).append(d("right")))
		};
		this.destroy=function()
		{
			// console.log( "destroy called" );
			e.remove()
		};
		this.updateTitle=function(a)
		{
			// console.log( "update title called" );
			// console.log( "a" );
			// console.log( a );
			e.find("h2").html(a)
		};
		this.activateButton=function(a)
		{
			// console.log( "activate button called" );
			e.find("span.fc-button-"+a).addClass(c+"-state-active")
		};
		this.deactivateButton=function(a)
		{
			// console.log( "deactivate button called" );
			e.find("span.fc-button-"+a).removeClass(c+"-state-active")
		};
		this.disableButton=function(a)
		{
			// console.log( "disable button called" );
			// console.log( "a" );
			// console.log( a );
			e.find("span.fc-button-"+a).addClass(c+"-state-disabled")
		};
		this.enableButton=function(a)
		{
			// console.log( "enable button called" );
			e.find("span.fc-button-"+a).removeClass(c+"-state-disabled")
		};
		var e=h([]),c
	}
	function qb(a,b)
	{
		// console.log( "qb called" );
		// fetchEventSource
		function d(a,b) // source, fetchID
		{
			// console.log( "fetchEventSource" );
			// console.log( "source" );
			// console.log( a );
			// console.log( "source" );
			// console.log( b );
			e(a,function(d)
			{
				if(b==r)
				{
					if(d)
					{
						for(var c=0;c<d.length;c++)
							d[c].source=a,
						f(d[c]);
						q=q.concat(d)
					}
					x--;
					x||k(q)
				}
			}
			)
		}
		// _fetchEventSource
		function e(b,d)
		{
			var c,f=J.sourceFetchers,g;
			
			for(c=0;c<f.length;c++)
			{
				g=f[c](b,s,O,d);
				if(!0===g)
					return;
				if("object"==typeof g)
				{
					e(g,d);
					return
				}
			}
			if(c=b.events)
				h.isFunction(c)?
				(
					l++||t("loading",null,!0),
					c(p(s),
					p(O),
					function(a)
					{
						d(a);
						--l||t("loading",null,!1)
					}
					)
				):
				h.isArray(c)?d(c):d();
			else 
				if(b.url)
				{
					var j=b.success,
					n=b.error,
					k=b.complete;
					c=h.extend({},b.data||{});
					f=ha(b.startParam,a.startParam);
					g=ha(b.endParam,a.endParam);
					f&&(c[f]=Math.round(+s/1E3));
					g&&(c[g]=Math.round(+O/1E3));
					l++||t("loading",null,!0);
					h.ajax(
						h.extend(
							{},
							rb,
							b,
							{
								data:c,
								success:function(a)
								{
									// console.log( "ajax success called" );
									a=a||[];
									var b=wa(j,this,arguments);
									h.isArray(b)&&(a=b);
									d(a)
								},
								error:function()
								{
									// console.log( "ajax error called" );
									wa(n,this,arguments);
									d()
								},
								complete:function()
								{
									// console.log( "complete called" );
									wa(k,this,arguments);
									--l||t("loading",null,!1)
								}
							}
						)
					)
				}
			else 
			d()
		}

		function c(a)
		{
			// console.log( "c3 new funcion 1 called" );
			h.isFunction(a)||h.isArray(a)?
			a={events:a}:
			"string"==typeof a&&(a={url:a});
			if("object"==typeof a)
			{
				var b=a;
				b.className?
				"string"==typeof b.className&&(b.className=b.className.split(/\s+/)):
				b.className=[];
				for(var d=J.sourceNormalizers,c=0;c<d.length;c++)
					d[c](b);
				m.push(a);
				return a
			}
		}
	
		function f(b)
		{
			// console.log( "f new function 01 called" );
			var d=b.source||{},
			c=ha(d.ignoreTimezone,a.ignoreTimezone);
			b._id=b._id||(b.id===y?"_fc"+sb++:b.id+"");
			b.date&&(b.start||(b.start=b.date),delete b.date);
			b._start=p(b.start=Ea(b.start,c));
			b.end=Ea(b.end,c);
			b.end&&b.end<=b.start&&(b.end=null);
			b._end=b.end?
			p(b.end):
			null;
			b.allDay===y&&(b.allDay=ha(d.allDayDefault,a.allDayDefault));
			b.className?
			"string"==typeof b.className&&(b.className=b.className.split(/\s+/)):
			b.className=[]
		}
		function j(a)
		{
			// console.log( "j new  called" );
			return("object"==typeof a?a.events||a.url:"")||a
		}
		this.isFetchNeeded=function(a,b)
		{
			// console.log( "isfetched neeede called" );
			return!s||a<s||b>O
		};
		// fetchEvents
		this.fetchEvents=function(a,b)
		{
			// console.log( "fetch events called" );
			// console.log( "a" );
			// console.log( a );
			// console.log( "b" );
			// console.log( b );
			s=a; // rangeStart
			// console.log( "rangeStart" );
			// console.log( s );
			O=b; // rangeEnd			
			q=[]; // cache			
			var c=++r, // fetcID
			e=m.length; // len = sources.length
			x=e; // pendingSourceCnt
			
			for(var f=0;f<e;f++)
			{
				d(m[f],c)
			}
		};
		this.addEventSource=function(a)
		{
			// console.log( "addeventsource called" );
			// console.log( "source" );
			// console.log( a );
			if(a=c(a))
				x++,
			d(a,r)
		};
		this.removeEventSource=function(a)
		{
			// console.log( "remove evnet source called" );
			m=h.grep(m,function(b)
			{
				return!(b&&a&&j(b)==j(a))
			});
			q=h.grep(q,function(b)
			{
				return!(b.source&&a&&j(b.source)==j(a))
			});
			k(q)
		};
		this.updateEvent=function(a)
		{
			// console.log( "update event called" );
			var b,d=q.length,c,e=n().defaultEventEnd,
			g=a.start-a._start,
			j=a.end?
			a.end-(a._end||e(a)):
			0;
			for(b=0;b<d;b++)
				c=q[b],
			c._id==a._id&&c!=a&&
			(c.start=new Date(+c.start+g),
			c.end=a.end?c.end?
			new Date(+c.end+j):
			new Date(+e(c)+j):
			null,
			c.title=a.title,
			c.url=a.url,
			c.allDay=a.allDay,
			c.className=a.className,
			c.editable=a.editable,
			c.color=a.color,
			c.backgroudColor=a.backgroudColor,
			c.borderColor=a.borderColor,
			c.textColor=a.textColor,
			f(c));
			f(a);
			k(q)
		};
		this.renderEvent=function(a,b)
		{
			// console.log( "render event called" );
			f(a);
			a.source||(b&&(g.events.push(a),a.source=g),
			q.push(a));
			k(q)
		};
		this.removeEvents=function(a)
		{
			// console.log( "remove event called" );
			if(a)
			{
				if(!h.isFunction(a))
				{
					var b=a+"";
					a=function(a)
					{
						return a._id==b
					}
				}
				q=h.grep(q,a,!0);
				for(d=0;d<m.length;d++)
					h.isArray(m[d].events)&&
				(m[d].events=h.grep(m[d].events,a,!0))
			}
			else
			{
				q=[];
				for(var d=0;d<m.length;d++)
					h.isArray(m[d].events)&&(m[d].events=[])
			}
			k(q)
		};
		this.clientEvents=function(a)
		{
			// console.log( "click events 01 called" );
			return h.isFunction(a)?h.grep(q,a):a?(a+="",h.grep(q,function(b)
			{
				return b._id==a
			})):q
		};
		this.normalizeEvent=f;
		for(var t=this.trigger,n=this.getView,k=this.reportEvents,g={events:[]},m=[g],s,O,r=0,x=0,l=0,q=[],z=0;z<b.length;z++)
			c(b[z])
	}
	
	function Aa(a,b,d)
	{
		// console.log( "Aa called" );
		a.setFullYear(a.getFullYear()+b);
		d||ka(a);
		return a
	}
	function Ba(a,b,d)
	{
		// console.log( "Ba called" );
		if(+a)
		{
			b=a.getMonth()+b;
			var e=p(a);
			e.setDate(1);
			e.setMonth(b);
			a.setMonth(b);
			for(d||ka(a);a.getMonth()!=e.getMonth();)
				a.setDate(a.getDate()+(a<e?1:-1))
		}
		return a
	}
	function s(a,b,d)
	{
		// console.log( "s called" );
		// console.log( "a" );
		// console.log( a );
		// console.log( "b" );
		// console.log( b );
		// console.log( "d" );
		// console.log( d );
		if(+a)
		{
			b=a.getDate()+b;
			var e=p(a);
			e.setHours(9);
			e.setDate(b);
			a.setDate(b);
			d||ka(a);
			Fa(a,e)
		}
		return a
	}
	
	function Fa(a,b)
	{
		// console.log( "Fa called" );
		// console.log( "a" );
		// console.log( a );
		// console.log( "b" );
		// console.log( b );
		if(+a)
			for(;a.getDate()!=b.getDate();)
				a.setTime(+a+(a<b?1:-1)*tb)
	}
	
	function G(a,b)
	{
		// console.log( "G called" );
		a.setMinutes(a.getMinutes()+b);
		return a
	}
	
	function ka(a)
	{
		// console.log( "ka called" );
		a.setHours(0);
		a.setMinutes(0);
		a.setSeconds(0);
		a.setMilliseconds(0);
		return a
	}
	function p(a,b)
	{
		// console.log( "p called" );
		// console.log( "a" );
		// console.log( a );
		// console.log( "b" );
		// console.log( b );
		return b?
		ka(new Date(+a)):
		new Date(+a)
	}
	
	function Ua()
	{
		// console.log( "Ua called" );
		var a=0,b;
		do b=new Date(1970,a++,1);
		while(b.getHours());
		return b
	}
	
	function Y(a,b,d)
	{
		// console.log( "Y called" );
		for(b=b||1;!a.getDay()||d&&1==a.getDay()||!d&&6==a.getDay();)
			s(a,b);
		return a
	}
	
	function ba(a,b)
	{
		// console.log( "ba called" );
		return Math.round((p(a,!0)-p(b,!0))/Va)
	}
	
	function Ta(a,b,d,e)
	{
		// console.log( "Ta called" );
		b!==y&&b!=a.getFullYear()&&(a.setDate(1),
		a.setMonth(0),
		a.setFullYear(b));
		d!==y&&d!=a.getMonth()&&(a.setDate(1),a.setMonth(d));
		e!==y&&a.setDate(e)
	}
	
	function Ea(a,b)
	{
		// console.log( "Ea called" );
		if("object"==typeof a)
			return a;
		if("number"==typeof a)
			return new Date(1E3*a);
		if("string"==typeof a)
		{
			if(a.match(/^\d+(\.\d+)?$/))
				return new Date(1E3*parseFloat(a));
			b===y&&(b=!0);
			return Wa(a,b)||(a?new Date(a):null)
		}
		return null
	}
	
	function Wa(a,b)
	{
		// console.log( "Wa called" );
		var d=a.match(/^([0-9]{4})(-([0-9]{2})(-([0-9]{2})([T ]([0-9]{2}):([0-9]{2})(:([0-9]{2})(\.([0-9]+))?)?(Z|(([-+])([0-9]{2})(:?([0-9]{2}))?))?)?)?)?$/);
		if(!d)
			return null;
		var e=new Date(d[1],0,1);
		if(b||!d[14])
		{
			var c=new Date(d[1],0,1,9,0);
			d[3]&&(e.setMonth(d[3]-1),
			c.setMonth(d[3]-1));
			d[5]&&(e.setDate(d[5]),c.setDate(d[5]));
			Fa(e,c);
			d[7]&&e.setHours(d[7]);
			d[8]&&e.setMinutes(d[8]);
			d[10]&&e.setSeconds(d[10]);
			d[12]&&e.setMilliseconds(1E3*Number("0."+d[12]));
			Fa(e,c)
		}
		else 
			e.setUTCFullYear(d[1],d[3]?d[3]-1:0,d[5]||1),
		e.setUTCHours(d[7]||0,d[8]||0,d[10]||0,d[12]?1E3*Number("0."+d[12]):0),
		c=60*Number(d[16])+(d[18]?Number(d[18]):0),
		c*="-"==d[15]?1:-1,
		e=new Date(+e+6E4*c);
		return e
	}
	function Ga(a)
	{
		// console.log( "Ga called" );
		if("number"==typeof a)
			return 60*a;
		if("object"==typeof a)
			return 60*a.getHours()+a.getMinutes();
		if(a=a.match(/(\d+)(?::(\d+))?\s*(\w+)?/))
		{
			var b=parseInt(a[1],10);
			a[3]&&(b%=12,"p"==a[3].toLowerCase().charAt(0)&&(b+=12));
			return 60*b+(a[2]?parseInt(a[2],10):0)
		}
	}
	function fa(a,b,d)
	{
		// console.log( "fa called" );
		return Ca(a,null,b,d)
	}
	function Ca(a,b,d,e)
	{
		// console.log( "Ca called" );
		e=e||ra;
		var c=a,f=b,j,h=d.length,n,k,g,m="";
		for(j=0;j<h;j++)
			if(n=d.charAt(j),"'"==n)
		for(k=j+1;k<h;k++)
		{
			if("'"==d.charAt(k))
			{
				c&&(m=k==j+1?m+"'":m+d.substring(j+1,k),j=k);
				break
			}
		}
		else if("("==n)
			for(k=j+1;k<h;k++)
			{
				if(")"==d.charAt(k))
				{
					j=fa(c,d.substring(j+1,k),e);
					parseInt(j.replace(/\D/,""),10)&&(m+=j);
					j=k;
					break
				}
			}
			else if("["==n)
				for(k=j+1;k<h;k++)
				{
					if("]"==d.charAt(k))
					{
						n=d.substring(j+1,k);
						j=fa(c,n,e);
						j!=fa(f,n,e)&&(m+=j);
						j=k;
						break
					}
				}
				else if("{"==n)
					c=b,f=a;
				else if("}"==n)
					c=a,f=b;
				else
				{
					for(k=h;k>j;k--)
						if(g=ub[d.substring(j,k)])
						{
							c&&(m+=g(c,e));
							j=k-1;
							break
						}
						k==j&&c&&(m+=n)
				}
				return m
	}
	function sa(a)
	{
		// console.log( "sa called" );
		if(a.end)
		{
			var b=a.end;
			a=a.allDay;
			b=p(b);
			return a||b.getHours()||b.getMinutes()?s(b,1):ka(b)
		}
		return s(p(a.start),1)
	}
	function vb(a,b)
	{
		// console.log( "vb called" );
		return 100*(b.msLength-a.msLength)+(a.event.start-b.event.start)
	}
	function Ha(a,b,d,e)
	{
		// console.log( "Ha called" );
		var c=[],f,j=a.length,h,n,k,g,m;
		for(f=0;f<j;f++)
			h=a[f],n=h.start,k=b[f],k>d&&n<e&&(n<d?(n=p(d),g=!1):
			g=!0,k>e?(k=p(e),m=!1):
			m=!0,c.push({event:h,start:n,end:k,isStart:g,isEnd:m,msLength:k-n})
		);
		return c.sort(vb)
	}
	function Ia(a)
	{
		// console.log( "Ta called" );
		var b=[],d,e=a.length,c,f,j,h;
		for(d=0;d<e;d++)
		{
			c=a[d];
			for(f=0;;)
			{
				j=!1;
				if(b[f])
					for(h=0;h<b[f].length;h++)
						if(b[f][h].end>c.start&&b[f][h].start<c.end)
						{
							j=!0;
							break
						}
						if(j)
							f++;
						else 
							break
			}
			b[f]?b[f].push(c):b[f]=[c]
		}
		return b
	}
	// lazySegBind
	function Xa(a,b,d)
	{
		// console.log( "Xa called" );
		a.unbind("mouseover").mouseover(function(a)
		{
			for(var c=a.target,f;c!=this;)
				f=c,c=c.parentNode;
			if((c=f._fci)!==y)
				f._fci=y,f=b[c],d(f.event,f.element,f),h(a.target).trigger(a);
			a.stopPropagation()
		})
	}
	
	function ta(a,b,d)
	{
		// console.log( "ta called" );
		// console.log( "a" );
		// console.log( a );
		// console.log( "b" );
		// console.log( b );
		// console.log( "c" );
		// console.log( c );
		for(var e=0,c;e<a.length;e++)
			c=h(a[e]),c.width(Math.max(0,b-(Ja(c)+Ka(c)+(d?La(c):0))))
	}
	function Ya(a,b,d)
	{
		// console.log( "Ya called" );
		for(var e=0,c;e<a.length;e++)
			c=h(a[e]),c.height(Math.max(0,b-qa(c,d)))
	}
	
	function Ja(a)
	{
		// console.log( "Ja called" );
		// console.log( "a" );
		// console.log( a );
		return(parseFloat(h.curCSS(a[0],"paddingLeft",!0))||0)+(parseFloat(h.curCSS(a[0],"paddingRight",!0))||0)
	}
	
	function La(a)
	{
		// console.log( "La called" );
		return(parseFloat(h.curCSS(a[0],"marginLeft",!0))||0)+(parseFloat(h.curCSS(a[0],"marginRight",!0))||0)
	}
	
	function Ka(a)
	{
		// console.log( "Ka called" );
		// console.log( "a" );
		// console.log( a );
		return(parseFloat(h.curCSS(a[0],"borderLeftWidth",!0))||0)+(parseFloat(h.curCSS(a[0],"borderRightWidth",!0))||0)
	}
	
	function qa(a,b)
	{
		// console.log( "qa called" );
		// console.log( "a" );
		// console.log( a );
		// console.log( "b" );
		// console.log( b );
		return(parseFloat(h.curCSS(a[0],"paddingTop",!0))||0)+(parseFloat(h.curCSS(a[0],"paddingBottom",!0))||0)+((parseFloat(h.curCSS(a[0],"borderTopWidth",!0))||0)+(parseFloat(h.curCSS(a[0],"borderBottomWidth",!0))||0))+(b?Za(a):0)
	}
	
	function Za(a)
	{
		// console.log( "Za called" );
		return(parseFloat(h.curCSS(a[0],"marginTop",!0))||0)+(parseFloat(h.curCSS(a[0],"marginBottom",!0))||0)
	}
	
	function va(a,b)
	{
		// console.log( "va called" );
		// console.log( "a" );
		// console.log( a );
		// console.log( "b" );
		// console.log( b );
		b="number"==typeof b?b+"px":b;
		a.each(function(a,e){e.style.cssText+=";min-height:"+b+";_height:"+b})
	}
	
	function Sa(){}
	
	function $a(a,b)
	{
		// console.log( "dollar a called" );
		return a-b
	}
	
	function ga(a)
	{
		// console.log( "ga called" );
		return(10>a?"0":"")+a
	}
	
	function Da(a,b)
	{
		// console.log( "Da called" );
		if(a[b]!==y)
			return a[b];
		for(var d=b.split(/(?=[A-Z])/),e=d.length-1,c;0<=e;e--)
			if(c=a[d[e].toLowerCase()],c!==y)
				return c;
		return a[""]
	}
	
	function W(a)
	{
		// console.log( "W called" );
		return a.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/'/g,"&#039;").replace(/"/g,"&quot;").replace(/\n/g,"<br />")
	}
	
	function ab(a)
	{
		// console.log( "ab called" );
		return a.id+"/"+a.className+"/"+a.style.cssText.replace(/(^|;)\s*(top|left|width|height)\s*:[^;]*/ig,"")
	}
	function Ma(a)
	{
		// console.log( "Ma called" );
		a.attr("unselectable","on").css("MozUserSelect","none").bind("selectstart.ui",function(){return!1})
	}
	function xa(a)
	{
		// console.log( "xa called" );
		a.children().removeClass("fc-first fc-last").filter(":first-child").addClass("fc-first").end().filter(":last-child").addClass("fc-last")
	}
	function Na(a,b)
	{
		// console.log( "na called" );
		// console.log( "a" );
		// console.log( a );
		// console.log( "b" );
		// console.log( b );
		a.each(function(a,e){e.className=e.className.replace(/^fc-\w*/,"fc-"+wb[b.getDay()])})
	}
	
	function bb(a,b)
	{
		// console.log( "bb called" );
		var d=a.source||{},e=a.color,c=d.color,f=b("eventColor"),h=a.backgroundColor||e||d.backgroundColor||c||b("eventBackgroundColor")||f,e=a.borderColor||e||d.borderColor||c||b("eventBorderColor")||f,d=a.textColor||d.textColor||b("eventTextColor"),c=[];h&&c.push("background-color:"+h);e&&c.push("border-color:"+e);d&&c.push("color:"+d);return c.join(";")
	}
	function wa(a,b,d)
	{
		// console.log( "wa called" );
		h.isFunction(a)&&(a=[a]);if(a){var e,c;for(e=0;e<a.length;e++)c=a[e].apply(b,d)||c;return c}
	}
	function ha()
	{
		// console.log( "ha called" );
		for(var a=0;a<arguments.length;a++)if(arguments[a]!==y)return arguments[a]
	}
	function Oa(a,b,d)
	{
		// console.log( "Oa called" );
		function e(a)
		{
			// console.log( "e called" );
			if(!m("selectable")){var b=parseInt(this.className.match(/fc\-day(\d+)/)[1]),b=n(b);v("dayClick",this,b,!0,a)}
		}
		function c(a,b,d)
		{
			// console.log( "c called" );
			d&&F.build();d=p(g.visStart);for(var c=s(p(d),C),h=0;h<w;h++){var X=new Date(Math.max(d,a)),j=new Date(Math.min(c,b));if(X<j){var R;I?(R=ba(j,d)*A+T+1,X=ba(X,d)*A+T+1):(R=ba(X,d),X=ba(j,d));f(h,R,h,X-1).click(e).mousedown(l)}s(d,7);s(c,7)}
		}
		function f(b,d,c,e)
		{
			// console.log( "f called" );
			b=F.rect(b,d,c,e,a);return r(b,a)
		}
		function j(a)
		{
			// console.log( "j called" );
			return{row:Math.floor(ba(a,g.visStart)/7),col:k(a.getDay())}
		}
		function t(a)
		{
			// console.log( "t called" );
			return s(p(g.visStart),7*a.row+a.col*A+T)
		}
		function n(a)
		{
			// console.log( "n called" );
			// console.log( "a" );
			// console.log( a );
			return s(p(g.visStart),7*Math.floor(a/C)+a%C*A+T)
		}
		function k(a)
		{
			// console.log( "k called" );
			return(a-Math.max(N,X)+C)%C*A+T
		}
		var g=this;
		g.renderBasic=function(b,d,c,f)
		{
			// console.log( "render basic called" );
			w=d;C=c;(I=m("isRTL"))?(A=-1,T=C-1):(A=1,T=0);N=m("firstDay");X=m("weekends")?0:1;R=m("theme")?"ui":"fc";ca=m("columnFormat");if(d=!L){var j=R+"-widget-header",k=R+"-widget-content",t;c="<table class='fc-border-separate' style='width:100%' cellspacing='0'><thead><tr>";for(t=0;t<C;t++)c+="<th class='fc- "+j+"'/>";c+="</tr></thead><tbody>";for(t=0;t<b;t++){c+="<tr class='fc-week"+t+"'>";for(j=0;j<C;j++)c+="<td class='fc- "+k+" fc-day"+(t*C+j)+"'><div>"+(f?"<div class='fc-day-number'/>":"")+"<div class='fc-day-content'><div style='position:relative'>&nbsp;</div></div></div></td>";c+="</tr>"}b=h(c+"</tbody></table>").appendTo(a);z=b.find("thead");E=z.find("th");L=b.find("tbody");B=L.find("tr");u=L.find("td");K=u.filter(":first-child");V=B.eq(0).find("div.fc-day-content div");
			xa(z.add(z.find("tr")));xa(B);B.eq(0).addClass("fc-first");u.click(e).mousedown(l);y=h("<div style='position:absolute;z-index:8;top:0;left:0'/>").appendTo(a)}else O();var p=d||1==w,P=g.start.getMonth(),x=ka(new Date),s,r,v;p&&E.each(function(a,b){s=h(b);r=n(a);s.html(q(r,ca));Na(s,r)});u.each(function(a,b){s=h(b);r=n(a);r.getMonth()==P?s.removeClass("fc-other-month"):s.addClass("fc-other-month");+r==+x?s.addClass(R+"-state-highlight fc-today"):s.removeClass(R+"-state-highlight fc-today");s.find("div.fc-day-number").text(r.getDate());p&&Na(s,r)});B.each(function(a,b){v=h(b);a<w?(v.show(),a==w-1?v.addClass("fc-last"):v.removeClass("fc-last")):v.hide()})
		};
		g.setHeight=function(a)
		{
			// console.log( "setheight called" );
			// console.log( "a" );
			// console.log( a );
			S=a;a=S-z.height();var b,d,c;"variable"==m("weekMode")?b=d=Math.floor(a/(1==w?2:6)):(b=Math.floor(a/w),d=a-b*(w-1));K.each(function(a,e){a<w&&(c=h(e),va(c.find("> div"),(a==w-1?d:b)-qa(c)))})
		};
		g.setWidth=function(a)
		{
			// console.log( "xsetwidth called" );
			// console.log( "a" );
			// console.log( a );
			Q=a;P.clear();H=Math.floor(Q/C);ta(E.slice(0,-1),H)
		};
		g.renderDayOverlay=c;
		g.defaultSelectionEnd=function(a)
		{
			// console.log( "defaultselectionend called" );
			return p(a)
		};
		g.renderSelection=function(a,b)
		{
			// console.log( "renderselection called" );
			c(a,s(p(b),1),!0)
		};
		g.clearSelection=function()
		{
			// console.log( "clearselection called" );
			x()
		};
		g.reportDayClick=function(a,b,d)
		{
			// console.log( "reportdayclikc called" );
			var c=j(a);v("dayClick",u[c.row*C+c.col],a,b,d)
		};
		g.dragStart=function(a,b)
		{
			// console.log( "dragstart called" );
			G.start(function(a)
			{
				// console.log( "g.start called" );
				x();a&&f(a.row,a.col,a.row,a.col)
			},b)
		};
		g.dragStop=function(a,b,d)
		{
			// console.log( "g.dragstop called" );
			var c=G.stop();x();c&&(c=t(c),v("drop",a,c,!0,b,d))
		};
		g.defaultEventEnd=function(a)
		{
			// console.log( "g.defaulteventend called" );
			return p(a.start)
		};
		g.getHoverListener=function()
		{
			// console.log( "g.gethoverlistener called" );
			return G
		};
		g.colContentLeft=function(a)
		{
			// console.log( "colcontentleft called" );
			return P.left(a)
		};
		g.colContentRight=function(a)
		{
			// console.log( "colcontentright called" );
			return P.right(a)
		};
		g.dayOfWeekCol=k;
		g.dateCell=j;
		g.cellDate=t;
		g.cellIsAllDay=function()
		{
			// console.log( "cellisallday called" );
			return!0
		};
		g.allDayRow=function(a)
		{
			// console.log( "alldayrow called" );
			return B.eq(a)
		};
		g.allDayBounds=function()
		{
			// console.log( "alldaybounds called" );
			return{left:0,right:Q}
		};
		g.getRowCnt=function()
		{
			// console.log( "getrowcount called" );
			return w
		};
		g.getColCnt=function()
		{
			// console.log( "getcolumncount called" );
			return C
		};
		g.getColWidth=function()
		{
			// console.log( "getcolwidth called" );
			return H
		};
		g.getDaySegmentContainer=function()
		{
			// console.log( "getdaysegmentcontainer called" );
			return y
		};
		gb.call(g,a,b,d);
		hb.call(g);
		ib.call(g);
		xb.call(g);
		var m=g.opt,v=g.trigger,O=g.clearEvents,r=g.renderOverlay,x=g.clearOverlays,l=g.daySelectionMousedown,q=b.formatDate,z,E,L,B,u,K,V,y,Q,S,H,w,C,F,G,P,I,A,T,N,X,R,ca;Ma(a.addClass("fc-grid"));
		F=new jb(function(a,b)
		{
			// console.log( "jb function called" );
			var d,c,e;E.each(function(a,f){d=h(f);c=d.offset().left;a&&(e[1]=c);e=[c];b[a]=e});e[1]=c+d.outerWidth();B.each(function(b,f){b<w&&(d=h(f),c=d.offset().top,b&&(e[1]=c),e=[c],a[b]=e)});e[1]=c+d.outerHeight()
		});
		G=new kb(F);
		P=new lb(function(a)
		{
			// console.log( "lb called" );
			return V.eq(a)
		})
	}

function xb()
{
	// console.log( "xb called" );
	function a(a)
	{
		// console.log( "a xb called" );
		// 3 HERE CALENDAR
		var d=l(),
		c=q(),
		e=p(b.visStart),
		c=s(p(e),c),
		f=h.map(a,sa),g,j,n,k,t,z,m=[];
		for(g=0;g<d;g++)
		{
			j=Ia(Ha(a,f,e,c));
			for(n=0;n<j.length;n++)
			{
				k=j[n];
				for(t=0;t<k.length;t++)
					z=k[t],
					z.row=g,
					z.level=n,
					m.push(z)
			}
			s(e,7);
			s(c,7)
		}
		return m
	}
	var b=this;
	b.renderEvents=function(b,d)
	{
		// console.log( "b.renderevents called" );
		// 2 HERE CALENDAR
		j(b);
		z(a(b),d)
	};
	b.compileDaySegs=a;
	b.clearEvents=function()
	{
		// console.log( "b.calendarevents1 called" );
		// 1 HERE CALENDAR
		t();
		v().empty()
	};
	b.bindDaySeg=function(a,b,h)
	{
		// console.log( "b.binddayseg 1 called" );
		if(c(a))
		{
			var j=y(),t;
			b.draggable({
				zIndex:9,
				delay:50,
				opacity:d("dragOpacity"),
				revertDuration:d("dragRevertDuration"),
				start:function(c,f)
				{
					e("eventDragStart",b,a,c,f);
					g(a,b);
					j.start(function(c,e,f,g)
					{
						b.draggable("option","revert",!c||!f&&!g);
						x();
						c?(t=7*f+g*(d("isRTL")?-1:1),r(s(p(a.start),t),s(sa(a),t))):
						t=0
					},
					c,
					"drag"
					)
				},
				stop:function(d,c)
				{
					j.stop();
					x();
					e("eventDragStop",b,a,d,c);
					t?m(this,a,t,0,a.allDay,d,c):(b.css("filter",""),k(a,b))
				}
			}
		)}
		h.isEnd&&f(a)&&E(a,b,h);
		n(a,b)
	};
	mb.call(b);
	var d=b.opt,
	e=b.trigger,
	c=b.isEventDraggable,
	f=b.isEventResizable,
	j=b.reportEvents,
	t=b.reportEventClear,
	n=b.eventElementHandlers,
	k=b.showEvents,
	g=b.hideEvents,
	m=b.eventDrop,
	v=b.getDaySegmentContainer,
	y=b.getHoverListener,
	r=b.renderDayOverlay,
	x=b.clearOverlays,
	l=b.getRowCnt,
	q=b.getColCnt,
	z=b.renderDaySegs,
	E=b.resizableDayEvent
}
function nb(a,b,d)
{
	// console.log( "nb function called" );
	function e(a)
	{
		// console.log( "nb.e called" );
		if(!q("selectable")){var b=Math.min(M-1,Math.floor((a.pageX-S.offset().left-Z)/ya)),d=n(b),c=this.parentNode.className.match(/fc-slot(\d+)/);c?(c=parseInt(c[1])*q("slotMinutes"),d.setHours(Math.floor(c/60)),d.setMinutes(c%60+ma),z("dayClick",F[b],d,!1,a)):z("dayClick",F[b],d,!0,a)}
	}
	function c(a,b,d)
	{
		// console.log( "nb.c called" );
		d&&$.build();var c=p(l.visStart);ga?(d=ba(b,c)*ia+ja+1,a=ba(a,c)*ia+ja+1):(d=ba(a,c),a=ba(b,c));d=Math.max(0,d);a=Math.min(M,a);d<a&&f(0,d,0,a-1).click(e).mousedown(V)
	}
	function f(a,b,d,c)
	{
		// console.log( "nb.f called" );
		a=$.rect(a,b,d,c,A);return L(a,A)
	}
	function j(a,b)
	{
		// console.log( "nb.j called" );
		for(var d=p(l.visStart),c=s(p(d),1),f=0;f<M;f++){var g=new Date(Math.max(d,a)),h=new Date(Math.min(c,b));if(g<h){var j=f*ia+ja,j=$.rect(0,j,0,j,ca),g=m(d,g),h=m(d,h);j.top=g;j.height=h-g;L(j,ca).click(e).mousedown(r)}s(d,1);s(c,1)}
	}
	function t(a)
	{
		// console.log( "nb.t called" );
		var b=n(a.col);a=a.row;q("allDaySlot")&&a--;0<=a&&G(b,ma+a*q("slotMinutes"));return b
	}
	function n(a)
	{
		// console.log( "nb.n called" );
		return s(p(l.visStart),a*ia+ja)
	}
	function k(a)
	{
		// console.log( "nb.k called" );
		return q("allDaySlot")&&!a.row
	}
	function g(a)
	{
		// console.log( "nb.g called" );
		return(a-Math.max(ea,fa)+M)%M*ia+ja
	}
	function m(a,b)
	{
		// console.log( "nb.m called" );
		a=p(a,!0);if(b<G(p(a),ma))return 0;if(b>=G(p(a),W))return la.height();var d=q("slotMinutes"),c=60*b.getHours()+b.getMinutes()-ma,e=Math.floor(c/d),f=Ra[e];f===y&&(f=Ra[e]=la.find("tr:eq("+e+") td div")[0].offsetTop);return Math.max(0,Math.round(f-1+U*(c%d/d)))
	}
	function v(a,b)
	{
		// console.log( "nb.v called" );
		var d=q("selectHelper");$.build();if(d){var c=ba(a,l.visStart)*ia+ja;if(0<=c&&c<M){var c=$.rect(0,c,0,c,ca),f=m(a,a),g=m(a,b);if(g>f){c.top=f;c.height=g-f;c.left+=2;c.width-=5;if(h.isFunction(d)){if(d=d(a,b))c.position="absolute",c.zIndex=8,da=h(d).css(c).appendTo(ca)}else c.isStart=!0,c.isEnd=!0,da=h(J({title:"",start:a,end:b,className:["fc-select-helper"],editable:!1},c)),da.css("opacity",q("dragOpacity"));da&&(da.click(e).mousedown(r),ca.append(da),ta(da,c.width,!0),Ya(da,c.height,!0))}}}else j(a,b)
	}
	function O()
	{
		// console.log( "nb.o called" );
		B();da&&(da.remove(),da=null)
	}
	function r(a)
	{
		// console.log( "nb.r called" );
		if(1==a.which&&q("selectable")){K(a);var b;pa.start(function(a,d){O();if(a&&a.col==d.col&&!k(a)){var c=t(d),e=t(a);b=[c,G(p(c),q("slotMinutes")),e,G(p(e),q("slotMinutes"))].sort($a);v(b[0],b[3])}else b=null},a);h(document).one("mouseup",function(a){pa.stop();b&&(+b[0]==+b[1]&&x(b[0],!1,a),u(b[0],b[3],!1,a))})}
	}
	function x(a,b,d)
	{
		// console.log( "nb.x called" );
		z("dayClick",F[g(a.getDay())],a,b,d)
	}
	var l=this;
	l.renderAgenda=function(b)
	{
		// console.log( "l.renderagends acalled" );
		M=b;ua=q("theme")?"ui":"fc";fa=q("weekends")?0:1;ea=q("firstDay");(ga=q("isRTL"))?(ia=-1,ja=M-1):(ia=1,ja=0);ma=Ga(q("minTime"));W=Ga(q("maxTime"));ha=q("columnFormat");if(S)E();else{b=ua+"-widget-header";var d=ua+"-widget-content",c,f,g,j,k,t=0==q("slotMinutes")%15;c="<table style='width:100%' class='fc-agenda-days fc-border-separate' cellspacing='0'><thead><tr><th class='fc-agenda-axis "+b+"'>&nbsp;</th>";for(f=0;f<M;f++)c+="<th class='fc- fc-col"+f+" "+b+"'/>";c+="<th class='fc-agenda-gutter "+b+"'>&nbsp;</th></tr></thead><tbody><tr><th class='fc-agenda-axis "+b+"'>&nbsp;</th>";for(f=0;f<M;f++)c+="<td class='fc- fc-col"+f+" "+d+"'><div><div class='fc-day-content'><div style='position:relative'>&nbsp;</div></div></div></td>";S=h(c+("<td class='fc-agenda-gutter "+d+"'>&nbsp;</td></tr></tbody></table>")).appendTo(a);H=S.find("thead");w=H.find("th").slice(1,-1);C=S.find("tbody");F=C.find("td").slice(0,-1);Y=F.find("div.fc-day-content div");P=F.eq(0);I=P.find("> div");xa(H.add(H.find("tr")));xa(C.add(C.find("tr")));na=H.find("th:first");oa=S.find(".fc-agenda-gutter");A=h("<div style='position:absolute;z-index:2;left:0;width:100%'/>").appendTo(a);q("allDaySlot")?(T=h("<div style='position:absolute;z-index:8;top:0;left:0'/>").appendTo(A),c="<table style='width:100%' class='fc-agenda-allday' cellspacing='0'><tr><th class='"+b+" fc-agenda-axis'>"+q("allDayText")+"</th><td><div class='fc-day-content'><div style='position:relative'/></div></td><th class='"+b+" fc-agenda-gutter'>&nbsp;</th></tr></table>",N=h(c).appendTo(A),X=N.find("tr"),X.find("td").click(e).mousedown(V),na=na.add(N.find("th:first")),oa=oa.add(N.find("th.fc-agenda-gutter")),A.append("<div class='fc-agenda-divider "+b+"'><div class='fc-agenda-divider-inner'/></div>")):T=h([]);R=h("<div style='position:absolute;width:100%;overflow-x:hidden;overflow-y:auto'/>").appendTo(A);ca=h("<div style='position:relative;width:100%;overflow:hidden'/>").appendTo(R);cb=h("<div style='position:absolute;z-index:8;top:0;left:0'/>").appendTo(ca);c="<table class='fc-agenda-slots' style='width:100%' cellspacing='0'><tbody>";g=Ua();j=G(p(g),W);G(g,ma);for(f=Qa=0;g<j;f++)k=g.getMinutes(),c+="<tr class='fc-slot"+f+" "+(!k?"":"fc-minor")+"'><th class='fc-agenda-axis "+b+"'>"+(!t||!k?Q(g,q("axisFormat")):"&nbsp;")+"</th><td class='"+d+"'><div style='position:relative'>&nbsp;</div></td></tr>",G(g,q("slotMinutes")),Qa++;c+="</tbody></table>";la=h(c).appendTo(ca);db=la.find("div:first");la.find("td").click(e).mousedown(r);na=na.add(la.find("th:first"))}g=ka(new Date);for(b=0;b<M;b++)f=n(b),d=w.eq(b),d.html(Q(f,ha)),c=F.eq(b),+f==+g?c.addClass(ua+"-state-highlight fc-today"):c.removeClass(ua+"-state-highlight fc-today"),Na(d.add(c),f)
	};
	l.setWidth=function(a)
	{
		// console.log( "l.setwidht called" );
		eb=a;aa.clear();Z=0;ta(na.width("").each(function(a,b){Z=Math.max(Z,h(b).outerWidth())}),Z);a=R[0].clientWidth;(Pa=R.width()-a)?(ta(oa,Pa),oa.show().prev().removeClass("fc-last")):oa.hide().prev().addClass("fc-last");ya=Math.floor((a-Z)/M);ta(w.slice(0,-1),ya)
	};
	l.setHeight=function(a,b)
	{
		// console.log( "l.setheight called" );
		a===y&&(a=D);D=a;Ra={};var c=C.position().top,d=R.position().top,e=Math.min(a-c,la.height()+d+1);I.height(e-qa(P));A.css("top",c);R.height(e-d-1);U=db.height()+1;if(b){c=function(){R.scrollTop(f)};d=Ua();e=p(d);e.setHours(q("firstHour"));var f=m(d,e)+1;c();setTimeout(c,0)}
	};
	l.beforeHide=function()
	{
		// console.log( "l.beforehide called" );
		fb=R.scrollTop()
	};
	l.afterShow=function()
	{
		// console.log( "l.aftershow called" );
		R.scrollTop(fb)
	};
	l.defaultEventEnd=function(a)
	{
		// console.log( "l.defaultevent end called" );
		var b=p(a.start);return a.allDay?b:G(b,q("defaultEventMinutes"))
	};
	l.timePosition=m;
	l.dayOfWeekCol=g;
	l.dateCell=function(a)
	{
		// console.log( ".ldatecell called" );
		return{row:Math.floor(ba(a,l.visStart)/7),col:g(a.getDay())}
	};
	l.cellDate=t;l.cellIsAllDay=k;l.allDayRow=function()
	{
		// console.log( "l.celldate called" );
		return X
	};
	l.allDayBounds=function()
	{
		// console.log( ".alldaybounds called" );
		return{left:Z,right:eb-Pa}
	};
	l.getHoverListener=function()
	{
		// console.log( "l.gethoverlistener called" );
		return pa
	};
	l.colContentLeft=function(a)
	{
		// console.log( "l.colcontentleft called" );
		return aa.left(a)
	};
	l.colContentRight=function(a)
	{
		// console.log( "l.colcontentright called" );
		return aa.right(a)
	};
	l.getDaySegmentContainer=function()
	{
		// console.log( "mini funciton  called" );
		return T
	};
	l.getSlotSegmentContainer=function()
	{
		// console.log( "mini function 2 called" );
		return cb
	};
	l.getMinMinute=function()
	{
		// console.log( "getminminute called" );
		return ma
	};
	l.getMaxMinute=function()
	{
		// console.log( "getmaxminute called" );
		return W
	};
	l.getBodyContent=function()
	{
		// console.log( "getbodycontent called" );
		return ca
	};
	l.getRowCnt=function()
	{
		// console.log( "getrowcont called" );
		return 1
	};
	l.getColCnt=function()
	{
		// console.log( "getcolcount called" );
		return M
	};
	l.getColWidth=function()
	{
		// console.log( "getcolwidth called" );
		return ya
	};
	l.getSlotHeight=function()
	{
		// console.log( "getslotheight called" );
		return U
	};
	l.defaultSelectionEnd=function(a,b)
	{
		// console.log( "l.defautlselction end called" );
		return b?p(a):G(p(a),q("slotMinutes"))
	};
	l.renderDayOverlay=c;
	l.renderSelection=function(a,b,d)
	{
		// console.log( "render selection called" );
		d?q("allDaySlot")&&c(a,s(p(b),1),!0):v(a,b)
	};
	l.clearSelection=O;
	l.reportDayClick=x;
	l.dragStart=function(a,b){pa.start(function(a)
	{
		// console.log( "dragstart called" );
		B();if(a)if(k(a))f(a.row,a.col,a.row,a.col);else{a=t(a);var b=G(p(a),q("defaultEventMinutes"));j(a,b)}
	},b
	)};
	l.dragStop=function(a,b,c)
	{
		// console.log( "drag sotp called" );
		var d=pa.stop();B();d&&z("drop",a,t(d),k(d),b,c)
	};
	gb.call(l,a,b,d);
	hb.call(l);
	ib.call(l);yb.call(l);var q=l.opt,z=l.trigger,E=l.clearEvents,L=l.renderOverlay,B=l.clearOverlays,u=l.reportSelection,K=l.unselect,V=l.daySelectionMousedown,J=l.slotSegHtml,Q=b.formatDate,S,H,w,C,F,Y,P,I,A,T,N,X,R,ca,cb,la,db,na,oa,da,eb,D,Z,ya,Pa,U,fb,M,Qa,$,pa,aa,Ra={},ua,ea,fa,ga,ia,ja,ma,W,ha;Ma(a.addClass("fc-agenda"));
	$=new jb(function(a,b)
	{
		// console.log( "new jb called" );
		var c,d,e;w.each(function(a,f){c=h(f);d=c.offset().left;a&&(e[1]=d);e=[d];b[a]=e});e[1]=d+c.outerWidth();q("allDaySlot")&&(c=X,d=c.offset().top,a[0]=[d,d+c.outerHeight()]);for(var f=ca.offset().top,g=R.offset().top,j=g+R.outerHeight(),k=0;k<Qa;k++)a.push([Math.max(g,Math.min(j,f+U*k)),Math.max(g,Math.min(j,f+U*(k+1)))])
	});
	pa=new kb($);
	aa=new lb(function(a){return Y.eq(a)})
}

function yb()
{
	// console.log( "yb called" );
	function a(a)
	{
		// console.log( "yb.a called" );
		a=Ia(Ha(a,h.map(a,sa),c.visStart,c.visEnd));var b,d=a.length,e,f,g,j=[];for(b=0;b<d;b++){e=a[b];for(f=0;f<e.length;f++)g=e[f],g.row=0,g.level=b,j.push(g)}return j
	}
	function b(a)
	{
		// console.log( "yb.a called" );
		return a.end?p(a.end):G(p(a.start),f("defaultEventMinutes"))
	}
	function d(a,b)
	{
		// console.log( "yb.d called" );
		var c="<",d=a.url,e=bb(a,f),g=e?" style='"+e+"'":"",j=["fc-event","fc-event-skin","fc-event-vert"];t(a)&&j.push("fc-event-draggable");b.isStart&&j.push("fc-corner-top");b.isEnd&&j.push("fc-corner-bottom");j=j.concat(a.className);a.source&&(j=j.concat(a.source.className||[]));c=d?c+("a href='"+W(a.url)+"'"):c+"div";c+=" class='"+j.join(" ")+"' style='position:absolute;z-index:8;top:"+b.top+"px;left:"+b.left+"px;"+e+"'><div class='fc-event-inner fc-event-skin'"+g+"><div class='fc-event-head fc-event-skin'"+g+"><div class='fc-event-time'>"+W(N(a.start,a.end,f("timeFormat")))+"</div></div><div class='fc-event-content'><div class='fc-event-title'>"+W(a.title)+"</div></div><div class='fc-event-bg'></div></div>";b.isEnd&&n(a)&&(c+="<div class='ui-resizable-handle ui-resizable-s'>=</div>");return c+("</"+(d?"a":"div")+">")
	}
	function e(a,b,c)
	{
		// console.log( "yb.e called" );
		var d=b.find("div.fc-event-time");
		if(t(a))
		{
			var e=function(b)
			{
				// console.log( "yb.e called" );
				var c=G(p(a.start),b),e;a.end&&(e=G(p(a.end),b));d.text(N(c,e,f("timeFormat")))
			},
			g=function()
			{
				// console.log( "yb.g called" );
				z&&(d.css("display",""),b.draggable("option","grid",[B,A]),z=!1)
			},
			h,z=!1,m,u,E,q=f("isRTL")?-1:1,r=l(),x=V(),B=J(),A=Q();
			eb.draggable({
				zIndex:9,
				scroll:!1,
				grid:[B,A],
				axis:1==x?"y":!1,
				opacity:f("dragOpacity"),
				revertDuration:f("dragRevertDuration"),
				start:function(c,e)
				{
					// console.log( "start funciton draggable called" );
					j("eventDragStart",b,a,c,e);C(a,b);h=b.position();u=E=0;r.start(function(c,e,j,h){b.draggable("option","revert",!c);I();c&&(m=h*q,f("allDaySlot")&&!c.row?(z||(z=!0,d.hide(),b.draggable("option","grid",null)),P(s(p(a.start),m),s(sa(a),m))):g())},c,"drag")
				},
				drag:function(a,b)
				{
					// console.log( "drag draggable called" );
					u=Math.round((b.position.top-h.top)/A)*f("slotMinutes");u!=E&&(z||e(u),E=u)
				},
				stop:function(c,d)
				{
					// console.log( "draggable stop called" );
					var f=r.stop();I();j("eventDragStop",b,a,c,d);f&&(m||u||z)?F(this,a,m,z?0:u,z,c,d):(g(),b.css("filter",""),b.css(h),e(0),w(a,b))
				}
			})
		}
		if(c.isEnd&&n(a))
		{
			var L,T,y=Q();b.resizable({handles:{s:"div.ui-resizable-s"},grid:y,start:function(c,d){L=T=0;C(a,b);b.css("z-index",9);j("eventResizeStart",this,a,c,d)},resize:function(c,e){L=Math.round((Math.max(y,b.height())-e.originalSize.height)/y);L!=T&&(d.text(N(a.start,!L&&!a.end?null:G(k(a),f("slotMinutes")*L),f("timeFormat"))),T=L)},stop:function(c,d){j("eventResizeStop",this,a,c,d);L?Y(this,a,0,f("slotMinutes")*L,c,d):(b.css("z-index",8),w(a,b))}})
		}
		v(a,b)
	}
	var c=this;
	c.renderEvents=function(k,n)
	{
		// console.log( "c.renderevents called" );
		g(k);var t,m=k.length,l=[],N=[];for(t=0;t<m;t++)k[t].allDay?l.push(k[t]):N.push(k[t]);f("allDaySlot")&&(u(a(l),n),O());var m=V(),l=z(),P=q(),r=G(p(c.visStart),l),A=h.map(N,b),I,D,v,w,C,U;t=[];for(I=0;I<m;I++){v=D=Ia(Ha(N,A,r,G(p(r),P-l)));var K=U=C=w=void 0,M=void 0,F=void 0;for(w=v.length-1;0<w;w--){K=v[w];for(C=0;C<K.length;C++){M=K[C];for(U=0;U<v[w-1].length;U++)F=v[w-1][U],M.end>F.start&&M.start<F.end&&(F.forward=Math.max(F.forward||0,(M.forward||0)+1))}}for(v=0;v<D.length;v++){w=D[v];for(C=0;C<w.length;C++)U=w[C],U.col=I,U.level=v,t.push(U)}s(r,1,!0)}var N=t.length,$,Q,aa;D="";P={};r={};I=x();m=V();f("readyState");(v=f("isRTL"))?(w=-1,K=m-1):(w=1,K=0);for(m=0;m<N;m++)l=t[m],A=l.event,C=E(l.start,l.start),U=E(l.start,l.end),$=l.col,M=l.level,F=l.forward||0,Q=L($*w+K),aa=B($*w+K)-Q,aa=Math.min(aa-6,0.95*aa),$=M?aa/(M+F+1):F?2*(aa/(F+1)-6):aa,M=Q+aa/(M+F+1)*M*w+(v?aa-$:0),l.top=C,l.left=M,l.outerWidth=$,l.outerHeight=U-C,D+=d(A,l);I[0].innerHTML=D;v=I.children();for(m=0;m<N;m++)l=t[m],A=l.event,D=h(v[m]),w=j("eventRender",A,A,D),!1===w?D.remove():(w&&!0!==w&&(D.remove(),D=h(w).css({position:"absolute",top:l.top,left:l.left}).appendTo(I)),l.element=D,A._id===n?e(A,D,l):D[0]._fci=m,H(A,D));Xa(I,t,e);for(m=0;m<N;m++)if(l=t[m],D=l.element)I=P[A=l.key=ab(D[0])],l.vsides=I===y?P[A]=qa(D,!0):I,I=r[A],l.hsides=I===y?r[A]=Ja(D)+Ka(D)+La(D):I,A=D.find("div.fc-event-content"),A.length&&(l.contentTop=A[0].offsetTop);for(m=0;m<N;m++)if(l=t[m],D=l.element)D[0].style.width=Math.max(0,l.outerWidth-l.hsides)+"px",P=Math.max(0,l.outerHeight-l.vsides),D[0].style.height=P+"px",A=l.event,l.contentTop!==y&&10>P-l.contentTop&&(D.find("div.fc-event-time").text(T(A.start,f("timeFormat"))+" - "+A.title),D.find("div.fc-event-title").remove()),j("eventAfterRender",A,A,D);j("readyState")
	};
	c.compileDaySegs=a;
	c.clearEvents=function(){m();r().empty();x().empty()};
	c.slotSegHtml=d;

	c.bindDaySeg=function(a,b,c)
	{
		// console.log( "c.bindddayseg called" );
		if(t(a))
		{
			var d=c.isStart,
			e=function()
			{
				// console.log( "e.function called" );
				k||(b.width(g).height("").draggable("option","grid",null),k=!0)
			},
			g,h,k=!0,m,A=f("isRTL")?-1:1,u=l(),N=J(),r=Q(),E=z();

			b.draggable({
				zIndex:9,
				opacity:f("dragOpacity","month"),
				revertDuration:f("dragRevertDuration"),
				start:function(c,l)
				{
					// console.log( "start function draggable called" );
					j("eventDragStart",b,a,c,l);
					C(a,b);
					g=b.width();
					u.start(function(c,g,j,l)
					{
						// console.log( "u.start called" );
						I();
						c?(h=!1,m=l*A,c.row?d?k&&(b.width(N-10),Ya(b,r*Math.round((a.end?(a.end-a.start)/zb:f("defaultEventMinutes"))/f("slotMinutes"))),b.draggable("option","grid",[N,1]),k=!1):h=!0:(P(s(p(a.start),m),s(sa(a),m)),e()),h=h||k&&!m):(e(),h=!0);b.draggable("option","revert",h)
					},c,"drag")
				},
				stop:function(c,d)
				{
					// console.log( "stop funciton called" );
					u.stop();
					I();
					j("eventDragStop",b,a,c,d);
					if(h)
						e(),
						b.css("filter",""),
						w(a,b);
					else
					{
						var g=0;
						k||(g=Math.round((b.offset().top-S().offset().top)/r)*f("slotMinutes")+E-(60*a.start.getHours()+a.start.getMinutes()));
						F(this,a,m,g,k,c,d)
					}
				}
			})
		}
		c.isEnd&&n(a)&&K(a,b,c);
		v(a,b)
	};
	mb.call(c);
	var f=c.opt,
	j=c.trigger,t=c.isEventDraggable,
	n=c.isEventResizable,
	k=c.eventEnd,
	g=c.reportEvents,
	m=c.reportEventClear,
	v=c.eventElementHandlers,
	O=c.setHeight,
	r=c.getDaySegmentContainer,
	x=c.getSlotSegmentContainer,
	l=c.getHoverListener,
	q=c.getMaxMinute,
	z=c.getMinMinute,
	E=c.timePosition,
	L=c.colContentLeft,
	B=c.colContentRight,
	u=c.renderDaySegs,
	K=c.resizableDayEvent,
	V=c.getColCnt,
	J=c.getColWidth,
	Q=c.getSlotHeight,
	S=c.getBodyContent,
	H=c.reportEventElement,
	w=c.showEvents,
	C=c.hideEvents,
	F=c.eventDrop,
	Y=c.eventResize,
	P=c.renderDayOverlay,
	I=c.clearOverlays,
	A=c.calendar,
	T=A.formatDate,
	N=A.formatDates
}

function gb(a,b,d)
{
	function e(a,b)
	{
		// console.log( "gb.e called" );
		// console.log( "a" );
		// console.log( a );
		// console.log( "b" );
		// console.log( b );
		var c=q[a];return"object"==typeof c?Da(c,b||d):c
	}
	// trigger
	function c(a,c)
	{
		// console.log( "gb.c called" );
		// console.log( "a" );
		// console.log( a );
		// console.log( "c" );
		// console.log( c );
		return b.trigger.apply(b,[a,c||g].concat(Array.prototype.slice.call(arguments,2),[g]))
	}
	function f(a)
	{
		// console.log( "gb.f called" );
		return ha(a.editable,(a.source||{}).editable,e("editable"))
	}
	function j(a)
	{
		// console.log( "gb.j called" );
		return a.end?p(a.end):m(a)
	}
	function t(a,b,c)
	{
		// console.log( "gb.t called" );
		a=l[a._id];var d,e=a.length;for(d=0;d<e;d++)if(!b||a[d][0]!=b[0])a[d][c]()
	}
	function n(a,b,c,d)
	{
		// console.log( "gb.n called" );
		c=c||0;for(var e,f=a.length,g=0;g<f;g++)e=a[g],d!==y&&(e.allDay=d),G(s(e.start,b,!0),c),e.end&&(e.end=G(s(e.end,b,!0),c)),v(e,q)
	}
	function k(a,b,c)
	{
		// console.log( "gb.k called" );
		c=c||0;for(var d,e=a.length,f=0;f<e;f++)d=a[f],d.end=G(s(j(d),b,!0),c),v(d,q)
	}
	var g=this;
	g.element=a;
	g.calendar=b;
	g.name=d;
	g.opt=e;
	g.trigger=c;
	g.isEventDraggable=function(a)
	{
		// console.log( "g.iseventdraggable called" );
		return f(a)&&!e("disableDragging")
	};
	g.isEventResizable=function(a)
	{
		// console.log( "g.iseventresiable called" );
		return f(a)&&!e("disableResizing")
	};
	g.reportEvents=function(a)
	{
		// console.log( "g.reportevents called" );
		r={};var b,c=a.length,d;for(b=0;b<c;b++)d=a[b],r[d._id]?r[d._id].push(d):r[d._id]=[d]
	};
	g.eventEnd=j;
	g.reportEventElement=function(a,b)
	{
		// console.log( "g.reporteventelements called" );
		x.push(b);l[a._id]?l[a._id].push(b):l[a._id]=[b]
	};
	g.reportEventClear=function()
	{
		// console.log( "g.reporteventclear called" );
		x=[];l={}
	};

g.eventElementHandlers=function(a,b){
	// console.log( "g.eventelementhandlers called" );
	b.mouseup(function(d){
		// console.log( "b.mouseup called" );
		2==d.button&&(c("dayClick",this,a.start,!0,d),
			h(b).bind("contextmenu",function()
			{
				return!1
			})
		)
	})
	.click(function(d){
		// console.log( "here calendar 1 called" );
		// HERE CALENDAR
		if(!b.hasClass("ui-draggable-dragging")&&!b.hasClass("ui-resizable-resizing"))
		return c("eventClick",this,a,d)
	})
	.hover(function(b){
		// console.log( ".hover 1 funciotn called" );
		c("eventMouseover",this,a,b)
	},function(b){
			c("eventMouseout",this,a,b)
		}
	)
};

g.showEvents=function(a,b)
{
	// console.log( "g.showevents funciton called" );
	t(a,b,"show")
};
g.hideEvents=function(a,b)
{
	// console.log( "g.hideout called" );
	t(a,b,"hide")
};
g.eventDrop=function(a,b,d,e,f,g,j)
{
	// console.log( "g.eventdrop called" );
	var h=b.allDay,k=b._id;n(r[k],d,e,f);c("eventDrop",a,b,d,e,f,function(){n(r[k],-d,-e,h);O(k)},g,j);O(k)
};
g.eventResize=function(a,b,d,e,f,g)
{
	// console.log( "g.eventresize called" );
	var j=b._id;k(r[j],d,e);c("eventResize",a,b,d,e,function(){k(r[j],-d,-e);O(j)},f,g);O(j)
};
var m=g.defaultEventEnd,v=b.normalizeEvent,O=b.reportEventChange,r={},x=[],l={},q=b.options
}
function mb()
{
	// console.log( "mb function o1 called" );
	function a(a)
	{
		// console.log( "mb.a rfunciotn called" );
		var b=k("isRTL"),c,d=a.length,e,f,g,j;c=B();var h=c.left,l=c.right,t,n,p,r,s,q="";for(c=0;c<d;c++)e=a[c],f=e.event,j=["fc-event","fc-event-skin","fc-event-hori"],m(f)&&j.push("fc-event-draggable"),b?(e.isStart&&j.push("fc-corner-right"),e.isEnd&&j.push("fc-corner-left"),t=J(e.end.getDay()-1),n=J(e.start.getDay()),p=e.isEnd?u(t):h,r=e.isStart?K(n):l):(e.isStart&&j.push("fc-corner-left"),e.isEnd&&j.push("fc-corner-right"),t=J(e.start.getDay()),n=J(e.end.getDay()-1),p=e.isStart?u(t):h,r=e.isEnd?K(n):l),j=j.concat(f.className),f.source&&(j=j.concat(f.source.className||[])),g=f.url,s=bb(f,k),q=g?q+("<a href='"+W(g)+"'"):q+"<div",q+=" class='"+j.join(" ")+"' style='position:absolute;z-index:8;left:"+p+"px;"+s+"'><div class='fc-event-inner fc-event-skin'"+(s?" style='"+s+"'":"")+">",!f.allDay&&e.isStart&&(q+="<span class='fc-event-time'>"+W(w(f.start,f.end,k("timeFormat")))+"</span>"),q+="<span class='fc-event-title'>"+W(f.title)+"</span></div>",e.isEnd&&v(f)&&(q+="<div class='ui-resizable-handle ui-resizable-"+(b?"w":"e")+"'>&nbsp;&nbsp;&nbsp;</div>"),q+="</"+(g?"a":"div")+">",e.left=p,e.outerWidth=r-p,e.startCol=t,e.endCol=n+1;return q
	}
	// daySegElementResolve
	function b(a,b)
	{
		// console.log( "mb.b function called" );
		var c,d=a.length,e,f,j;
		for(c=0;c<d;c++)
			e=a[c],
		f=e.event,
		j=h(b[c]),
		f=g("eventRender",f,f,j),
		!1===f?j.remove():(f&&!0!==f&&(f=h(f).css({position:"absolute",left:e.left}),j.replaceWith(f),j=f),e.element=j)
	}
	function d(a)
	{
		// console.log( "mb.d function called" );
		var b,c=a.length,d,e,f,g,j={};for(b=0;b<c;b++)if(d=a[b],e=d.element)f=d.key=ab(e[0]),g=j[f],g===y&&(g=j[f]=Ja(e)+Ka(e)+La(e)),d.hsides=g
	}
	function e(a)
	{
		// console.log( "mb.e funciton called" );
		var b,c=a.length,d,e;for(b=0;b<c;b++)if(d=a[b],e=d.element)e[0].style.width=Math.max(0,d.outerWidth-d.hsides)+"px"
	}
	function c(a)
	{
		// console.log( "mb.c function called" );
		var b,c=a.length,d,e,f,g,j={};for(b=0;b<c;b++)if(d=a[b],e=d.element)f=d.key,g=j[f],g===y&&(g=j[f]=Za(e)),d.outerHeight=e[0].offsetHeight+g
	}
	function f()
	{
		// console.log( "mb.f function called" );
		var a,b=z(),c=[];for(a=0;a<b;a++)c[a]=G(a).find("td:first div.fc-day-content > div");return c
	}
	function j(a)
	{
		// console.log( "mb.j function called" );
		var b,c=a.length,d=[];for(b=0;b<c;b++)d[b]=a[b][0].offsetTop;return d
	}
	function t(a,b)
	{
		// console.log( "mb.t function called" );
		var c,d=a.length,e,f;for(c=0;c<d;c++)if(e=a[c],f=e.element)f[0].style.top=b[e.row]+(e.top||0)+"px",e=e.event,g("eventAfterRender",e,e,f);g("readyState")
	}
	var n=this;
	n.renderDaySegs=function(g,h)
	{
		// console.log( "n.render days segas called" );
		var k=S(),l=z(),m=E(),n=0,p,q,s,u=g.length,x,v;k[0].innerHTML=a(g);b(g,k.children());q=g.length;for(p=0;p<q;p++)s=g[p],(v=s.element)&&r(s.event,v);q=g.length;var w;for(p=0;p<q;p++)if(s=g[p],v=s.element)w=s.event,w._id===h?H(w,v,s):v[0]._fci=p;Xa(k,g,H);d(g);e(g);c(g);k=f();for(p=0;p<l;p++){q=[];for(s=0;s<m;s++)q[s]=0;for(;n<u&&(x=g[n]).row==p;){s=q.slice(x.startCol,x.endCol);s=Math.max.apply(Math,s);x.top=s;s+=x.outerHeight;for(v=x.startCol;v<x.endCol;v++)q[v]=s;n++}k[p].height(Math.max.apply(Math,q))}t(g,j(k))
	};
	n.resizableDayEvent=function(m,r,v)
	{
		// console.log( "n.resizable day event called" );
		var u=k("isRTL"),w=u?"w":"e",y=r.find("div.ui-resizable-"+w),B=!1;Ma(r);
		r.mousedown(function(a)
		{
			a.preventDefault()
		})
		.click(function(a)
		{
			B&&(a.preventDefault(),a.stopImmediatePropagation())
		});
		y.mousedown(function(k)
		{
			// console.log( "y.mousedown 1 called" );
			function y(a)
			{
				// console.log( "y.mousedown function called" );
				g("eventResizeStop",this,m,a);h("body").css("cursor","");G.stop();F();D&&q(this,m,D,0,a);setTimeout(function(){B=!1},0)
			}
			if(1==k.which)
			{
				B=!0;var G=n.getHoverListener(),H=z(),L=E(),J=u?-1:1,K=u?L-1:0,X=r.css("top"),D,Z,V=h.extend({},m),W=Y(m.start);ba();h("body").css("cursor",w+"-resize").one("mouseup",y);g("eventResizeStart",this,m,k);G.start(function(g,k){if(g){var n=Math.max(W.row,g.row),q=g.col;1==H&&(n=0);n==W.row&&(q=u?Math.min(W.col,q):Math.max(W.col,q));D=7*n+q*J+K-(7*k.row+k.col*J+K);n=s(O(m),D,!0);if(D){V.end=n;var q=Z,r=Q([V]),z=v.row,y=h("<div/>"),B=S(),E=r.length,G;y[0].innerHTML=a(r);y=y.children();B.append(y);b(r,y);d(r);e(r);c(r);t(r,j(f()));y=[];for(B=0;B<E;B++)if(G=r[B].element)r[B].row===z&&G.css("top",X),y.push(G[0]);Z=h(y);Z.find("*").css("cursor",w+"-resize");q&&q.remove();l(m)}else Z&&(x(m),Z.remove(),Z=null);F();C(m.start,s(p(n),1))}},k)
			}
		})
	};
	var k=n.opt,g=n.trigger,m=n.isEventDraggable,v=n.isEventResizable,O=n.eventEnd,r=n.reportEventElement,x=n.showEvents,l=n.hideEvents,q=n.eventResize,z=n.getRowCnt,E=n.getColCnt,G=n.allDayRow,B=n.allDayBounds,u=n.colContentLeft,K=n.colContentRight,J=n.dayOfWeekCol,Y=n.dateCell,Q=n.compileDaySegs,S=n.getDaySegmentContainer,H=n.bindDaySeg,w=n.calendar.formatDates,C=n.renderDayOverlay,F=n.clearOverlays,ba=n.clearSelection
}
function ib()
{
	// console.log( "ib function called called" );
	function a(a)
	{
		// console.log( "ib.a function called" );
		n&&(n=!1,t(),c("unselect",null,a))
	}
	function b(a,b,d,e)
	{
		// console.log( "ib.b function called" );
		n=!0;c("select",null,a,b,d,e)
	}
	var d=this;
	d.select=function(c,d,e)
	{
		// console.log( "ib.d function called" );
		a();d||(d=f(c,e));j(c,d,e);b(c,d,e)
	};
	d.unselect=a;
	d.reportSelection=b;
	d.daySelectionMousedown=function(c)
	{
		// console.log( "d.dayselection mousedown called" );
		var f=d.cellDate,n=d.cellIsAllDay,p=d.getHoverListener(),s=d.reportDayClick;
		if(1==c.which&&e("selectable"))
		{
			a(c);
			var r;
			p.start(function(a,b)
			{
				t();a&&n(a)?(r=[f(b),f(a)].sort($a),j(r[0],r[1],!0)):r=null
			},c);
			h(document).one("mouseup",function(a)
			{
				p.stop();r&&(+r[0]==+r[1]&&s(r[0],!0,a),b(r[0],r[1],!0,a))
			})
		}
	};
	var e=d.opt,c=d.trigger,f=d.defaultSelectionEnd,j=d.renderSelection,t=d.clearSelection,n=!1;
	e("selectable")&&e("unselectAuto")&&h(document).mousedown(function(b)
	{
		var c=e("unselectCancel");
		(!c||!h(b.target).parents(c).length)&&a(b)
	})
}
function hb()
{
	// console.log( "hb function called called" );
	this.renderOverlay=function(d,e)
	{
		// console.log( "this.renderoverlay called" );
		var c=b.shift();
		c||(c=h("<div class='fc-cell-overlay' style='position:absolute;z-index:3'/>"));c[0].parentNode!=e[0]&&c.appendTo(e);a.push(c.css(d).show());
		return c
	};
	this.clearOverlays=function()
	{
		// console.log( "this.clearoverlay called" );
		for(var d;d=a.shift();)
			b.push(d.hide().unbind())
	};
	var a=[],b=[]
}
function jb(a)
{
	// console.log( "jb function called called" );
	var b,d;
	this.build=function()
	{
		// console.log( "jb.build called" );
		b=[];d=[];a(b,d)
	};
	this.cell=function(a,c)
	{
		// console.log( "jb.celll called" );
		var f=b.length,j=d.length,h,n=-1,k=-1;for(h=0;h<f;h++)if(c>=b[h][0]&&c<b[h][1]){n=h;break}for(h=0;h<j;h++)if(a>=d[h][0]&&a<d[h][1]){k=h;break}return 0<=n&&0<=k?{row:n,col:k}:null
	};
	this.rect=function(a,c,f,j,h)
	{
		// console.log( "jb.rect called" );
		h=h.offset();return{top:b[a][0]-h.top,left:d[c][0]-h.left,width:d[j][1]-d[c][0],height:b[f][1]-b[a][0]}
	}
}
function kb(a)
{
	// console.log( "jb.kb called" );
	function b(b)
	{
		// console.log( "kb.b called" );
		b=a.cell(b.pageX,b.pageY);
		if(!b!=!f||b&&(b.row!=f.row||b.col!=f.col))
			b?(c||(c=b),e(b,c,b.row-c.row,b.col-c.col)):e(b,c),f=b
	}
	var d,e,c,f;
	this.start=function(j,t,n)
	{
		// console.log( "kb.start called" );
		e=j;c=f=null;a.build();b(t);d=n||"mousemove";h(document).bind(d,b)
	};
	this.stop=function()
	{
		// console.log( "kb.stop called" );
		h(document).unbind(d,b);return f
	}
}
function lb(a)
{
	// console.log( "lb called" );
	var b=this,d={},e={},c={};
	b.left=function(b)
	{
		// console.log( "lb.b.left called" );
		return e[b]=e[b]===y?(d[b]=d[b]||a(b)).position().left:e[b]};
		b.right=function(e)
		{
			// console.log( "lb.b.right called" );
			return c[e]=c[e]===y?b.left(e)+(d[e]=d[e]||a(e)).width():c[e]
		};
		b.clear=function()
		{
			// console.log( "lb.b.clear called" );
			d={};e={};c={}
		}
}
var ra={
	defaultView:"month",
	aspectRatio:1.35,
	header:{
		left:"title",
		center:"",
		right:"today prev,next"
	},
	weekends:!0,
	allDayDefault:!0,
	ignoreTimezone:!0,
	lazyFetching:!0,
	startParam:"start",
	endParam:"end",
	titleFormat:{
		month:"MMMM yyyy",
		week:"MMM d[ yyyy]{ '&#8212;'[ MMM] d yyyy}",
		day:"dddd, MMM d, yyyy"
	},
	columnFormat:{
		month:"ddd",
		week:"ddd M/d",
		day:"dddd M/d"
	},
	timeFormat:{"":"h(:mm)t"},
	isRTL:!1,
	firstDay:0,
	monthNames:"January February March April May June July August September October November December".split(" "),
	monthNamesShort:"Jan Feb Mar Apr May Jun Jul Aug Sep Oct Nov Dec".split(" "),
	dayNames:"Sunday Monday Tuesday Wednesday Thursday Friday Saturday".split(" "),
	dayNamesShort:"Sun Mon Tue Wed Thu Fri Sat".split(" "),
	buttonText:{
		prev:"&nbsp;&#9668;&nbsp;",
		next:"&nbsp;&#9658;&nbsp;",
		prevYear:"&nbsp;&lt;&lt;&nbsp;",
		nextYear:"&nbsp;&gt;&gt;&nbsp;",
		today:"today",
		month:"month",
		week:"week",
		day:"day"
	},
	theme:!1,
	buttonIcons:{
		prev:"circle-triangle-w",
		next:"circle-triangle-e"
	},
	unselectAuto:!0,
	dropAccept:"*"
},
Ab={
	header:{
		left:"next,prev today",
		center:"",
		right:"title"
	},
	buttonText:{
		prev:"&nbsp;&#9658;&nbsp;",
		next:"&nbsp;&#9668;&nbsp;",
		prevYear:"&nbsp;&gt;&gt;&nbsp;",
		nextYear:"&nbsp;&lt;&lt;&nbsp;"
	},
	buttonIcons:{
		prev:"circle-triangle-e",
		next:"circle-triangle-w"
	}
},
J=h.fullCalendar={
	version:"1.5.1"
},
ea=J.views={};
h.fn.fullCalendar=function(a)
{
	// console.log( "h.fn.fullcalenar called" );
	// console.log( "a" );
	// console.log( a );
	if("string"==typeof a)
	{
		var b=Array.prototype.slice.call(arguments,1),d;
		this.each(function()
		{
			var c=h.data(this,"fullCalendar");c&&h.isFunction(c[a])&&(c=c[a].apply(c,b),d===y&&(d=c),"destroy"==a&&h.removeData(this,"fullCalendar"))
		});
		return d!==y?d:this
	}
	var e=a.eventSources||[];
	delete a.eventSources;a.events&&(e.push(a.events),delete a.events);
	a=h.extend(!0,{},ra,a.isRTL||a.isRTL===y&&ra.isRTL?Ab:{},a);
	this.each(function(b,d)
	{
		// console.log( "this.each function called" );
		var j=h(d),t=new ob(j,a,e);j.data("fullCalendar",t);t.render()
	});
	return this
};
J.sourceNormalizers=[];
J.sourceFetchers=[];
var rb={
	dataType:"json",
	cache:!1
},
sb=1;
J.addDays=s;
J.cloneDate=p;
J.parseDate=Ea;
J.parseISO8601=Wa;
J.parseTime=Ga;
J.formatDate=fa;
J.formatDates=Ca;
var wb="sun mon tue wed thu fri sat".split(" "),
Va=864E5,
tb=36E5,
zb=6E4,
ub={
	s:function(a)
	{
		// console.log( "s function cll called" );
		return a.getSeconds()
	},
	ss:function(a)
	{
		// console.log( "ss funcitonc all called" );
		return ga(a.getSeconds())
	},
	m:function(a)
	{
		// console.log( "m function call called" );
		return a.getMinutes()
	},
	mm:function(a)
	{
		// console.log( "mm functionc all called" );
		return ga(a.getMinutes())
	},
	h:function(a)
	{
		// console.log( "h function call called" );
		return a.getHours()%12||12
	},
	hh:function(a)
	{
		// console.log( "hh functionc all called" );
		return ga(a.getHours()%12||12)
	},
	H:function(a)
	{
		// console.log( "h funcitn call called" );
		return a.getHours()
	},
	HH:function(a)
	{
		// console.log( "hh functionc all called" );
		return ga(a.getHours())
	},
	d:function(a)
	{
		// console.log( "d function cll called" );
		return a.getDate()
	},
	dd:function(a)
	{
		// console.log( "dd function call called" );
		return ga(a.getDate())
	},
	ddd:function(a,b)
	{
		// console.log( "ddd function call called" );
		return b.dayNamesShort[a.getDay()]
	},
	dddd:function(a,b)
	{
		// console.log( "ddd function  called" );
		return b.dayNames[a.getDay()]
	},
		M:function(a)
		{
			// console.log( "m function call called" );
			return a.getMonth()+1
		},
		MM:function(a)
		{
			// console.log( "MM funciton called" );
			return ga(a.getMonth()+1)
		},
		MMM:function(a,b)
		{
			// console.log( "MMM function  called" );
			return b.monthNamesShort[a.getMonth()]
		},
		MMMM:function(a,b)
		{
			// console.log( "MMMM function called" );
			return b.monthNames[a.getMonth()]
		},
		yy:function(a)
		{
			// console.log( "YY called" );
			return(a.getFullYear()+"").substring(2)
		},
		yyyy:function(a)
		{
			// console.log( "YYYYY called" );
			return a.getFullYear()
		},
		t:function(a)
		{
			// console.log( "t function called" );
			return 12>a.getHours()?"a":"p"
		},
		tt:function(a)
		{
			// console.log( "tt function called called" );
			return 12>a.getHours()?"am":"pm"
		},
		T:function(a)
		{
			// console.log( "T function  called" );
			return 12>a.getHours()?"A":"P"
		},
		TT:function(a)
		{
			// console.log( "TT function  called" );
			return 12>a.getHours()?"AM":"PM"
		},
		u:function(a)
		{
			return fa(a,"yyyy-MM-dd'T'HH:mm:ss'Z'")
		},
		S:function(a)
		{
			// console.log( "S function called" );
			a=a.getDate();
			return 10<a&&20>a?"th":["st","nd","rd"][a%10-1]||"th"
		}
	};
	J.applyAll=wa;
	"function"!=typeof h.curCSS&&(h.curCSS=function(a,b,d)
	{
		return h(a).css(b,d)
	});
	ea.month=function(a,b)
	{
		// console.log( "ea.month called" );
		var d=this;
		d.render=function(a,b)
		{
			// console.log( "d.render called" );
			b&&(Ba(a,b),a.setDate(1));var h=p(a,!0);h.setDate(1);var k=Ba(p(h),1),g=p(h),m=p(k),v=e("firstDay"),y=e("weekends")?0:1;y&&(Y(g),Y(m,-1,!0));s(g,-((g.getDay()-Math.max(v,y)+7)%7));s(m,(7-m.getDay()+Math.max(v,y))%7);v=Math.round((m-g)/(7*Va));"fixed"==e("weekMode")&&(s(m,7*(6-v)),v=6);d.title=f(h,e("titleFormat"));d.start=h;d.end=k;d.visStart=g;d.visEnd=m;c(6,v,y?5:7,!0)
		};
		Oa.call(d,a,b,"month");
		var e=d.opt,c=d.renderBasic,f=b.formatDate
	};
	ea.basicWeek=function(a,b)
	{
		// console.log( "ea.basicweek called" );
		var d=this;
		d.render=function(a,b)
		{
			// console.log( "d.render called" );
			b&&s(a,7*b);var h=s(p(a),-((a.getDay()-e("firstDay")+7)%7)),k=s(p(h),7),g=p(h),m=p(k),v=e("weekends");v||(Y(g),Y(m,-1,!0));d.title=f(g,s(p(m),-1),e("titleFormat"));d.start=h;d.end=k;d.visStart=g;d.visEnd=m;c(1,1,v?7:5,!1)
		};
		Oa.call(d,a,b,"basicWeek");
		var e=d.opt,c=d.renderBasic,f=b.formatDates
	};
	ea.basicDay=function(a,b)
	{
		// console.log( "ea.basicday called" );
		var d=this;d.render=function(a,b){b&&(s(a,b),e("weekends")||Y(a,0>b?-1:1));d.title=f(a,e("titleFormat"));d.start=d.visStart=p(a,!0);d.end=d.visEnd=s(p(d.start),1);c(1,1,1,!1)};Oa.call(d,a,b,"basicDay");var e=d.opt,c=d.renderBasic,f=b.formatDate
	};
	h.extend(!0,ra,{weekMode:"fixed"});
	ea.agendaWeek=function(a,b)
	{
		// console.log( "ea.agendaweek called" );
		var d=this;d.render=function(a,b){b&&s(a,7*b);var h=s(p(a),-((a.getDay()-e("firstDay")+7)%7)),k=s(p(h),7),g=p(h),m=p(k),v=e("weekends");v||(Y(g),Y(m,-1,!0));d.title=f(g,s(p(m),-1),e("titleFormat"));d.start=h;d.end=k;d.visStart=g;d.visEnd=m;c(v?7:5)};nb.call(d,a,b,"agendaWeek");var e=d.opt,c=d.renderAgenda,f=b.formatDates
	};
	ea.agendaDay=function(a,b)
	{
		// console.log( "ea.agendaday called" );
		var d=this;d.render=function(a,b){b&&(s(a,b),e("weekends")||Y(a,0>b?-1:1));var h=p(a,!0),k=s(p(h),1);d.title=f(a,e("titleFormat"));d.start=d.visStart=h;d.end=d.visEnd=k;c(1)};nb.call(d,a,b,"agendaDay");var e=d.opt,c=d.renderAgenda,f=b.formatDate
	};
	h.extend(!0,ra,{allDaySlot:!0,allDayText:"all-day",firstHour:6,slotMinutes:30,defaultEventMinutes:120,axisFormat:"h(:mm)tt",timeFormat:{agenda:"h:mm{ - h:mm}"},dragOpacity:{agenda:0.5},minTime:0,maxTime:24})})(jQuery);
