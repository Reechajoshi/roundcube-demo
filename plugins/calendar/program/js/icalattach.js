var imported = !1,
    previewed = [];

function plugin_ical_save(d) {
    imported || (rcmail.http_post("plugin.saveical", "_uid=" + rcmail.env.uid + "&_mbox=" + urlencode(rcmail.env.mailbox) + "&_part=" + urlencode(d), !0), imported = !0, setTimeout("plugin_ical_save_after()", 1E3), plugin_ical_confirmation());
    return !1
}

function plugin_ical_save_after() {
    try {
        plugin_ical_remove(), parent.$("#upcoming").fullCalendar("refetchEvents")
    } catch (d) {
        plugin_ical_remove(), $("#upcoming").fullCalendar("refetchEvents")
    }
}

function plugin_ical_preview(d, f) {
    if (!imported && !previewed[d]) {
        previewed[d] = !0;
        var a = new Date(f),
            e = a.getDate(),
            b = a.getMonth(),
            c = a.getFullYear();
        try {
            var g = parent.$("#upcoming").fullCalendar("getDate")
        } catch (h) {
            g = $("#upcoming").fullCalendar("getDate")
        }
        if (g.getDate() != a || g.getMonth() != b || g.getFullYear() != c) {
            try {
                parent.$("#upcoming").fullCalendar("gotoDate", c, b, e)
            } catch (i) {
                $("#upcoming").fullCalendar("gotoDate", c, b, e)
            }
            a = new Date(f + 864E5);
            e = a.getDate();
            b = a.getMonth();
            c = a.getFullYear();
            try {
                parent.$("#upcoming_1").fullCalendar("gotoDate",
                    c, b, e)
            } catch (j) {
                $("#upcoming_1").fullCalendar("gotoDate", c, b, e)
            }
            a = new Date(f + 1728E5);
            e = a.getDate();
            b = a.getMonth();
            c = a.getFullYear();
            try {
                parent.$("#upcoming_2").fullCalendar("gotoDate", c, b, e)
            } catch (k) {
                $("#upcoming_2").fullCalendar("gotoDate", c, b, e)
            }
        }
    }
}

function plugin_ical_source(d) {
    if (!imported && !previewed[d]) try {
        parent.$("#upcoming").fullCalendar("refetchEvents")
    } catch (f) {
        try {
            $("#upcoming").fullCalendar("refetchEvents")
        } catch (a) {}
    }
}

function plugin_ical_remove() {
    try {
        parent.$("#upcoming").fullCalendar("removeEvents", "preview")
    } catch (d) {
        $("#upcoming").fullCalendar("removeEvents", "preview")
    }
}

function plugin_ical_confirmation() {};