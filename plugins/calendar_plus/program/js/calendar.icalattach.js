/***************************************************************************
 * This file is part of Roundcube "calendar_plus" plugin.              
 *                                                                 
 * Your are not allowed to distribute this file or parts of it.    
 *                                                                 
 * This file is distributed in the hope that it will be useful,    
 * but WITHOUT ANY WARRANTY; without even the implied warranty of  
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.          
 *                                                                 
 * Copyright (c) 2012 - 2013 Roland 'Rosali' Liebl - all rights reserved
 * dev-team [at] myroundcube [dot] com
 * http://myroundcube.com
 ***************************************************************************/

/* $(document).ready(function(){rcmail.env.event_imported=!1;rcmail.env.event_previewed=[]});
function calendar_icalattach(){this.save=function(e,a,d){var b="&_items="+urlencode(a)+"&_category="+d;c=1;if(!a){try{c=rcmail.env.myevents.length}catch(f){c=parent.rcmail.env.myevents.length}b="&_category="+d}confirm(rcmail.gettext("calendar.importconfirmation").replace("%s",c),!1,'calendar_icalattach.importical("'+e+'", "'+b+'")',"return false",!1)&&this.importical(e,b);return!1};this.save_after=function(){this.remove();try{parent.$("#upcoming").fullCalendar("refetchEvents")}catch(e){$("#upcoming").fullCalendar("refetchEvents")}};
this.importical=function(e,a){rcmail.http_post("plugin.saveical","_uid="+rcmail.env.uid+"&_mbox="+urlencode(rcmail.env.mailbox)+"&_part="+urlencode(e)+a,!0);rcmail.env.event_imported=!0;setTimeout("calendar_icalattach.save_after()",1E3)};this.preview=function(e){var a=new Date(e),d=a.getDate(),b=a.getMonth(),f=a.getFullYear();try{var g=parent.$("#upcoming").fullCalendar("getDate")}catch(h){g=$("#upcoming").fullCalendar("getDate")}if(g.getDate()!=a||g.getMonth()!=b||g.getFullYear()!=f){try{parent.$("#upcoming").fullCalendar("gotoDate",
f,b,d),parent.$("#upcoming").fullCalendar("refetchEvents")}catch(j){$("#upcoming").fullCalendar("gotoDate",f,b,d),$("#upcoming").fullCalendar("refetchEvents")}a=new Date(e+864E5);d=a.getDate();b=a.getMonth();f=a.getFullYear();try{parent.$("#upcoming_1").fullCalendar("gotoDate",f,b,d)}catch(k){$("#upcoming_1").fullCalendar("gotoDate",f,b,d)}a=new Date(e+1728E5);d=a.getDate();b=a.getMonth();f=a.getFullYear();try{parent.$("#upcoming_2").fullCalendar("gotoDate",f,b,d)}catch(l){$("#upcoming_2").fullCalendar("gotoDate",
f,b,d)}}};this.remove=function(){try{parent.rcmail.env.myevents=[],parent.$("#upcoming").fullCalendar("removeEvents","preview"),parent.$("#upcoming-container").scrollTop(0)}catch(e){rcmail.env.myevents=[],$("#upcoming").fullCalendar("removeEvents","preview"),$("#upcoming-container").scrollTop(0)}}}calendar_icalattach=new calendar_icalattach; */

$(document).ready(function () {
    rcmail.env.event_imported = !1;
    rcmail.env.event_previewed = []
});

function calendar_icalattach() {
    this.save = function (e, a, d) {
        var b = "&_items=" + urlencode(a) + "&_category=" + d;
        c = 1;
        if (!a) {
            try {
                c = rcmail.env.myevents.length
            } catch (f) {
                c = parent.rcmail.env.myevents.length
            }
            b = "&_category=" + d
        }
        confirm(rcmail.gettext("calendar.importconfirmation").replace("%s", c), !1, 'calendar_icalattach.importical("' + e + '", "' + b + '")', "return false", !1) && this.importical(e, b);
        return !1
    };
    this.save_after = function () {
        this.remove();
        try {
            parent.$("#upcoming").fullCalendar("refetchEvents")
        } catch (e) {
            $("#upcoming").fullCalendar("refetchEvents")
        }
    };
    this.importical = function (e, a) {
        rcmail.http_post("plugin.saveical", "_uid=" + rcmail.env.uid + "&_mbox=" + urlencode(rcmail.env.mailbox) + "&_part=" + urlencode(e) + a, !0);
        rcmail.env.event_imported = !0;
        setTimeout("calendar_icalattach.save_after()", 1E3)
    };
    this.preview = function (e) {
        var a = new Date(e),
            d = a.getDate(),
            b = a.getMonth(),
            f = a.getFullYear();
        try {
            var g = parent.$("#upcoming").fullCalendar("getDate")
        } catch (h) {
            g = $("#upcoming").fullCalendar("getDate")
        }
        if (g.getDate() != a || g.getMonth() != b || g.getFullYear() != f) {
            try {
                parent.$("#upcoming").fullCalendar("gotoDate",
                    f, b, d), parent.$("#upcoming").fullCalendar("refetchEvents")
            } catch (j) {
                $("#upcoming").fullCalendar("gotoDate", f, b, d), $("#upcoming").fullCalendar("refetchEvents")
            }
            a = new Date(e + 864E5);
            d = a.getDate();
            b = a.getMonth();
            f = a.getFullYear();
            try {
                parent.$("#upcoming_1").fullCalendar("gotoDate", f, b, d)
            } catch (k) {
                $("#upcoming_1").fullCalendar("gotoDate", f, b, d)
            }
            a = new Date(e + 1728E5);
            d = a.getDate();
            b = a.getMonth();
            f = a.getFullYear();
            try {
                parent.$("#upcoming_2").fullCalendar("gotoDate", f, b, d)
            } catch (l) {
                $("#upcoming_2").fullCalendar("gotoDate",
                    f, b, d)
            }
        }
    };
    this.remove = function () {
        try {
            parent.rcmail.env.myevents = [], parent.$("#upcoming").fullCalendar("removeEvents", "preview"), parent.$("#upcoming-container").scrollTop(0)
        } catch (e) {
            rcmail.env.myevents = [], $("#upcoming").fullCalendar("removeEvents", "preview"), $("#upcoming-container").scrollTop(0)
        }
    }
}
calendar_icalattach = new calendar_icalattach;