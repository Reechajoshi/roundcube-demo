function calendar_gui() {
    window.rcmail && (rcmail.env.calendar_gui_initialized = !1);
    this.minimalmode = function () {
        if ("undefined" != typeof rcmail.get_cookie && "larry" == rcmail.env.skin) {
            var b = rcmail.get_cookie("minimalmode");
            "undefined" != typeof rcmail.get_cookie && "string" == typeof b && (parseInt(b) || null === b && 850 > $(window).height()) ? ($("#mainscreen").css("top", "85px"), $("#calendaroverlay").css("top", "85px"), $("#remindersoverlay").css("top", "45px"), $("#calendar-header").css("top", "85px"), $("#messagetoolbar").css("top",
                "45px"), $("#todaybutton").css("top", "55px"), $("#calquicksearchbar").css("top", "55px")) : ($("#mainscreen").css("top", "110px"), $("#calendaroverlay").css("top", "110px"), $("#remindersoverlay").css("top", "55px"), $("#calendar-header").css("top", "110px"), $("#messagetoolbar").css("top", "69px"), $("#todaybutton").css("top", "80px"), $("#calquicksearchbar").css("top", "75px"));
            calendar_gui.adjustRight()
        }
        "classic" == rcmail.env.skin ? $("#taskscontent").height($("#prefs-box").height() - $("#taskscontent").position().top) :
            $("#taskscontent").height($("#message").position().top - $("#taskscontent").position().top - 5)
    };
    this.init = function (b) {
        if (rcmail.env.calsearch_id) {
            var a = $("#calendar").fullCalendar("clientEvents", rcmail.env.calsearch_id);
            if (a && a[0]) {
                rcmail.env.calsearch_id = !1;
                rcmail.env.calendar_gui_initialized = !0;
                calendar_callbacks.eventClick(a[0], rcmail.env.calsettings);
                return
            }
            rcmail.env.calsearch_id = !1
        }
        if (rcmail.env.calendar_gui_initialized) $("#upcoming").fullCalendar("removeEvents");
        else {
            rcmail.env.calendar_gui_initialized = !0;
            $("#calendar_button").addClass("button-selected");
            "false" != queryString("_view") && $("#calendar").fullCalendar("changeView", queryString("_view"));
            "larry" == rcmail.env.skin && $("#calendar-header").attr("class", "ui-widget-header");
            "false" != queryString("_event_id") && "false" != queryString("_date") && (rcmail.env.ts = queryString("_date"), rcmail.env.id = queryString("_event_id"), calendar_commands.gotoDate(rcmail.env.ts, rcmail.env.id), rcmail.env.calendar_gui_initialized = !1);
            if ("false" != queryString("_date")) {
                var a =
                    $("#calendar").fullCalendar("getDate"),
                    d = $.fullCalendar.parseDate(new Date(1E3 * queryString("_date")));
                (a.getDate() != d.getDate() || a.getMonth() != d.getMonth() || a.getFullYear() != d.getFullYear()) && $("#calendar").fullCalendar("gotoDate", $.fullCalendar.parseDate(new Date(1E3 * queryString("_date"))))
            }
            $("#calprintrevbut").get(0) && ($("#printmenu").css("top", $("#calendar-header").offset().top + "px"), $("#printmenu").css("left", $("#calprintprevbut").offset().left + "px"));
            $("#subscriptiontoggle").click(function () {
                $("#subscription-table-content").is(":visible") ?
                    ($(this).prop("checked") ? ($(".subscriptionchbox").prop("checked", !0), $(this).attr("title", rcmail.gettext("uncheckall", "calendar"))) : ($(".subscriptionchbox").prop("checked", !1), $(this).attr("title", rcmail.gettext("checkall", "calendar"))), $("#calendaroverlay").show(), $("#calsearchfilter").val(""), rcmail.http_post("plugin.calendar_subscribe", $("#subscription_form").serialize())) : ($(this).prop("checked") ? ($(".filterschbox").prop("checked", !0), $(this).attr("title", rcmail.gettext("uncheckall", "calendar"))) :
                    ($(".filterschbox").prop("checked", !1), $(this).attr("title", rcmail.gettext("checkall", "calendar"))), $("#calendaroverlay").show(), $("#calsearchfilter").val(""), rcmail.http_post("plugin.calendar_setfilters", $("#filters_form").serialize()))
            });
            $(".subscriptionchbox").click(function () {
                if (rcmail.env.replication_complete) {
                    rcmail.env.subscriptionsubmit && window.clearTimeout(rcmail.env.subscriptionsubmit);
                    $("#calsearchfilter").val("");
                    var a = this.id.replace("chbox_", "option_"),
                        b = this.id.replace("chbox_", "filter_"),
                        d = this.id.replace("chbox_", "user_");
                    $(this).prop("checked") ? ($("#" + a).show(), $("#" + b).show(), $("." + b).show(), $("#" + d).show()) : ($("#" + a).hide(), $("#" + b).hide(), $("." + b).hide(), $("#" + d).hide());
                    rcmail.env.subscriptiondata = $("#subscription_form").serialize();
                    rcmail.env.subscriptionsubmit = window.setTimeout("$('#calendaroverlay').show();rcmail.http_post('plugin.calendar_subscribe', rcmail.env.subscriptiondata);rcmail.env.subscriptiondata=false;rcmail.env.subscriptionsubmit=false", 2500)
                } else $(this).prop("checked") ?
                    $(this).prop("checked", !1) : $(this).prop("checked", !0), rcmail.display_message(rcmail.gettext("backgroundreplication", "calendar"), "error")
            });
            $("#subscriptions-table").mouseleave(function () {
                rcmail.env.subscriptiondata && rcmail.env.subscriptionsubmit && (window.clearTimeout(rcmail.env.subscriptionsubmit), $("#calendaroverlay").show(), rcmail.http_post("plugin.calendar_subscribe", rcmail.env.subscriptiondata), rcmail.env.subscriptiondata = !1, rcmail.env.subscriptionsubmit = !1)
            });
            $("#subscriptionlink").click(function () {
                $("#filterslink").removeClass("subscriptionlink-selected");
                $(this).addClass("subscriptionlink-selected");
                $("#filters-table-content").hide();
                $("#subscription-table-content").show();
                calendar_gui.adjustLeft();
                rcmail.http_post("plugin.calendar_subscription_view", "_view=subscriptions")
            });
            $("#taskquickinput").bind("keypress", function (a) {
                if (13 == rcube_event.get_keycode(a)) return "" != $("#taskquickinput").val() && (rcmail.http_post("plugin.newTask", "_raw=" + $("#taskquickinput").val() + "&_categories=" + $("#taskscategories").val()), $("#taskquickinput").val(""), $("#calendaroverlay").show()), !1
            });
            $("#taskquickinputsubmit").click(function () {
                "" != $("#taskquickinput").val() && (rcmail.http_post("plugin.newTask", "_raw=" + $("#taskquickinput").val() + "&_categories=" + $("#taskscategories").val()), $("#taskquickinput").val(""), $("#calendaroverlay").show())
            });
            $("#statussel").change(function () {
                "COMPLETED" == $("#statussel").val() ? ($("#endactive").prop("checked", !0), rcmail.env.endactive = !0, $("#slider").slider("value", 100), $("#percentage").val(100), $("#percentagedisplay").html(100)) : ($("#endactive").prop("checked", !1), rcmail.env.endactive = !1)
            });
            $("#startactive").mouseover(function () {
                rcmail.env.startactive = $(this).prop("checked")
            });
            $("#startactive").click(function () {
                rcmail.env.startactive ? ($(this).prop("checked", !1), rcmail.env.startactive = !1) : ($(this).prop("checked", !0), rcmail.env.startactive = !0)
            });
            $("#endactive").mouseover(function () {
                rcmail.env.endactive = $(this).prop("checked")
            });
            $("#endactive").click(function () {
                rcmail.env.endactive ? "COMPLETED" != $("#statussel").val() && ($(this).prop("checked", !1), rcmail.env.endactive = !1) : ($(this).prop("checked", !0), rcmail.env.endactive = !0, $("#statussel").val("COMPLETED").change())
            });
            $("#dueactive").mouseover(function () {
                rcmail.env.dueactive = $(this).prop("checked")
            });
            $("#dueactive").click(function () {
                rcmail.env.dueactive ? ($(this).prop("checked", !1), rcmail.env.dueactive = !1) : ($(this).prop("checked", !0), rcmail.env.dueactive = !0)
            });
            $("#recursel").change(function () {
                0 != $(this).val() ? (rcmail.env.startactive_remember = $("#startactive").prop("checked"), $("#startactive").prop("checked", !0)) :
                    $("#startactive").prop("checked", rcmail.env.startactive_remember)
            });
            $("#reminderenable").click(function () {
                $(this).prop("checked") && (rcmail.env.startactive_remember || (rcmail.env.startactive_remember = $("#startactive").prop("checked")), $("#startactive").prop("checked", !0))
            });
            $("#reminderdisable").click(function () {
                $("#startactive").prop("checked", rcmail.env.startactive_remember)
            });
            $("#filterslink").click(function () {
                $("#subscriptionlink").removeClass("subscriptionlink-selected");
                $(this).addClass("subscriptionlink-selected");
                $("#subscription-table-content").hide();
                $("#filters-table-content").show();
                calendar_gui.adjustLeft();
                rcmail.http_post("plugin.calendar_subscription_view", "_view=filters")
            });
            $(".filterschbox").click(function () {
                rcmail.env.replication_complete ? ($("#calsearchfilter").val(""), data = $("#filters_form").serialize(), $("#calendaroverlay").show(), rcmail.http_post("plugin.calendar_setfilters", data)) : ($(this).prop("checked") ? $(this).prop("checked", !1) : $(this).prop("checked", !0), rcmail.display_message(rcmail.gettext("backgroundreplication",
                    "calendar"), "error"))
            });
            this.upcoming(b);
            rcmail.env.google_ads || (b = 0, "larry" == rcmail.env.skin && (b = 110), $("#calsearchdialog").dialog({
                autoOpen: !1,
                modal: !1,
                resizable: !1,
                width: 285,
                height: 250,
                position: [$("#calquicksearchbar").position().left - b, $("#calquicksearchbar").position().top + 30]
            }));
            $("#calsearchfilter").click(function () {
                $("#calsearchset").hide();
                2 < $("#calsearchfilter").val().length && $("#calsearchdialog").dialog("open");
                $("#calsearchfilter").focus()
            });
            $("#calsearchfilter").bind("keypress", function (a) {
                if (13 ==
                    rcube_event.get_keycode(a)) return rcmail.env.cal_search_string = "", calendar_commands.triggerSearch(), !1
            });
            $("#calsearchfilter").bind("keyup", function () {
                calendar_commands.triggerSearch()
            });
            $("#calsearchreset").click(function () {
                $("#calsearchdialog").dialog("close");
                $("#calsearchfilter").val("");
                $("#calsearch_table").html("")
            });
            $("#calsearchmod").click(function () {
                $("#calsearchset").toggle()
            });
            $("#todaybut").click(function () {
                $("#jqdatepicker").datepicker("setDate", new Date);
                $("#calendar").fullCalendar("today");
                rcmail.env.clientEvents = !0
            });
            $("#prevbut").click(function () {
                rcmail.env.onChangeMonthYear = !1;
                $("#calendar").fullCalendar("prev");
                var a = new Date($("#calendar").fullCalendar("getDate")),
                    b = new Date;
                b.setTime(a.getTime() + 864E5);
                $("#calendar").fullCalendar("select", a, b, !1);
                rcmail.env.clientEvents = !0
            });
            $("#nextbut").click(function () {
                rcmail.env.onChangeMonthYear = !1;
                $("#calendar").fullCalendar("next");
                var a = new Date($("#calendar").fullCalendar("getDate")),
                    b = new Date;
                b.setTime(a.getTime() + 864E5);
                $("#calendar").fullCalendar("select",
                    a, b, !1);
                rcmail.env.clientEvents = !0
            });
            $("#daybut").click(function () {
                $("#calendar").fullCalendar("changeView", "agendaDay");
                calendar_gui.adjustRight()
            });
            $("#weekbut").click(function () {
                $("#calendar").fullCalendar("changeView", "agendaWeek");
                calendar_gui.adjustRight()
            });
            $("#monthbut").click(function () {
                $("#calendar").fullCalendar("changeView", "month");
                calendar_gui.adjustRight()
            });
            $("#jqdatepicker").mousedown(function () {
                rcmail.env.onChangeMonthYear = !0
            });
            $("#form").keypress(function () {
                $("#event").tabs("disable", 3)
            });
            $("#starttime").click(function () {
                $("#event").tabs("disable", 3)
            });
            $("#endtime").click(function () {
                $("#event").tabs("disable", 3)
            });
            $("#startdate").click(function () {
                $("#event").tabs("disable", 3)
            });
            $("#enddate").click(function () {
                $("#event").tabs("disable", 3)
            });
            $("#c1").click(function () {
                calendar_gui.nums(1, "dnums", 31)
            });
            $("#c2").click(function () {
                calendar_gui.nums(-1, "dnums", 31)
            });
            $("#c3").click(function () {
                calendar_gui.nums(1, "wnums", 99)
            });
            $("#c4").click(function () {
                calendar_gui.nums(-1, "wnums", 99)
            });
            $("#c5").click(function () {
                calendar_gui.nums(1, "mnums", 99)
            });
            $("#c6").click(function () {
                calendar_gui.nums(-1, "mnums", 99)
            });
            $("#c7").click(function () {
                calendar_gui.nums(1, "ynums", 99)
            });
            $("#c8").click(function () {
                calendar_gui.nums(-1, "ynums", 99)
            });
            $("#c9").click(function () {
                calendar_gui.nums(1, "ydnums", 99);
                calendar_gui.resetYear("advanced")
            });
            $("#c10").click(function () {
                calendar_gui.nums(-1, "ydnums", 99);
                calendar_gui.resetYear("advanced")
            });
            $("#c11").click(function () {
                calendar_gui.nums(1, "occurrences", 99)
            });
            $("#c12").click(function () {
                calendar_gui.nums(-1, "occurrences", 99)
            });
            $("#schedule").hide();
            $("#recurselnever").click(function () {
                $("#recursel").prop("selectedIndex", 0).change();
                calendar_gui.resetDay();
                calendar_gui.resetWeek();
                calendar_gui.resetMonth("advanced");
                calendar_gui.resetMonth("occurrences");
                calendar_gui.resetMonth();
                calendar_gui.resetYear("advanced");
                calendar_gui.resetYear("occurrences");
                calendar_gui.resetYear();
                $("#everyweekdiv").hide();
                $("#everymonthdiv").hide();
                $("#everyyeardiv").hide();
                $("#everydaydiv").hide();
                $("#occurences").val(1);
                calendar_gui.toggleRecur();
                $("#expires").val(rcmail.env.remember_expires);
                rcmail.env.recurselnever = !0
            });
            $("#daylink").click(function () {
                rcmail.env.event_initial = !1; - 1 == $("#everydaydiv").attr("style").indexOf("none") ? ($("#recursel").prop("selectedIndex", 0).change(), $("#schedule").hide(), $("#reset").hide(), calendar_gui.resetDay()) : ($("#recursel").prop("selectedIndex", 1).change(), $("#schedule").show(), $("#reset").show(), calendar_gui.resetWeek(), calendar_gui.resetMonth("advanced"),
                    calendar_gui.resetMonth("occurrences"), calendar_gui.resetMonth(), calendar_gui.resetYear("advanced"), calendar_gui.resetYear("occurrences"), calendar_gui.resetYear())
            });
            $("#weeklink").click(function () {
                rcmail.env.event_initial = !1; - 1 == $("#everyweekdiv").attr("style").indexOf("none") ? ($("#recursel").prop("selectedIndex", 0).change(), $("#schedule").hide(), $("#reset").hide(), calendar_gui.resetWeek()) : ($("#recursel").prop("selectedIndex", 3).change(), $("#schedule").show(), $("#reset").show(), calendar_gui.resetDay(),
                    calendar_gui.resetMonth("advanced"), calendar_gui.resetMonth("occurrences"), calendar_gui.resetMonth(), calendar_gui.resetYear("advanced"), calendar_gui.resetYear("occurrences"), calendar_gui.resetYear())
            });
            $("#monthlink").click(function () {
                rcmail.env.event_initial = !1; - 1 == $("#everymonthdiv").attr("style").indexOf("none") ? ($("#recursel").prop("selectedIndex", 0).change(), $("#schedule").hide(), $("#reset").hide(), calendar_gui.resetMonth("advanced"), calendar_gui.resetMonth("occurrences"), calendar_gui.resetMonth()) :
                    ($("#recursel").prop("selectedIndex", 4).change(), $("#schedule").show(), $("#reset").show(), calendar_gui.resetDay(), calendar_gui.resetWeek(), calendar_gui.resetYear("advanced"), calendar_gui.resetYear("occurrences"), calendar_gui.resetYear())
            });
            $("#yearlink").click(function () {
                rcmail.env.event_initial = !1; - 1 == $("#everyyeardiv").attr("style").indexOf("none") ? ($("#recursel").prop("selectedIndex", 0), $("#schedule").hide(), $("#reset").hide()) : ($("#recursel").prop("selectedIndex", 5), $("#schedule").show(), $("#reset").show(),
                    calendar_gui.resetDay(), calendar_gui.resetWeek());
                calendar_gui.resetYear("advanced");
                calendar_gui.resetYear("occurrences");
                calendar_gui.resetYear()
            });
            $("#workdaylink").click(function () {
                $("#recursel").prop("selectedIndex", 2)
            });
            $("#byweekdaylink").click(function () {
                calendar_gui.resetMonth("advanced")
            });
            $("#bymonthdayslink").click(function () {
                calendar_gui.resetMonth()
            });
            $("#byyeardaylink").click(function () {
                calendar_gui.resetYear("advanced")
            });
            $("#bymonthdaylink").click(function () {
                calendar_gui.resetYear()
            });
            $("#infinite").click(function () {
                $("#expires").val(rcmail.env.caleot.substr(0, 10))
            });
            $("#mevery").change(function () {
                calendar_gui.checkAll("#form", !1)
            });
            $("#mweekday").change(function () {
                calendar_gui.checkAll("#form", !1)
            });
            $("#ymonthday").change(function () {
                calendar_gui.checkAll("#form", !1);
                calendar_gui.resetYear("advanced")
            });
            $("#yevery").change(function () {
                calendar_gui.checkAll("#form", !1);
                calendar_gui.resetYear("default")
            });
            $("#yweekday").change(function () {
                calendar_gui.checkAll("#form", !1);
                calendar_gui.resetYear("default")
            });
            $("#ymonth").change(function () {
                calendar_gui.checkAll("#form", !1);
                calendar_gui.resetYear("default")
            });
            $("#export_to_file").click(function () {
                $("#send_invitation").prop("checked", "");
                window.setTimeout("$('#export_to_file').prop('checked','');var $dialogContent = $('#event');$dialogContent.dialog('close');", 1500);
                document.location.href = $("#export_as_file").attr("href")
            });
            $("#send_invitation").click(function () {
                $("#export_to_file").prop("checked", "");
                window.setTimeout("$('#send_invitation').prop('checked','');var $dialogContent = $('#event');$dialogContent.dialog('close');",
                    1500)
            });
            this.createOptions = function (a, b, d) {
                for (var g = d; g <= b; g += d) $(a).append($("<option></option>").val(g).html(g))
            };
            this.createOptions("#reminderminutesbefore", 59, 5);
            this.createOptions("#reminderhoursbefore", 23, 1);
            this.createOptions("#reminderdaysbefore", 6, 1);
            this.createOptions("#reminderweeksbefore", 4, 1);
            this.editTask = function (a, b, d) {
                "done" == b && ($("#calendaroverlay").show(), rcmail.http_post("plugin.editTask", "_event_id=" + a + "&_property=complete&_done=" + d))
            };
            this.getTask = function (a, b, d, g) {
                $("#calendaroverlay").show();
                rcmail.http_post("plugin.getTask", "_event_id=" + a + "&_start=" + b + "&_due=" + d + "&_clone=" + g)
            };

            this.custommail = function (a) {
                "new" == a ? rcmail.display_message($("#custommail").attr("label"), "error") : (a = ($("#startdate").val() + " " + $("#starttime").val() + ":00").replace(/^([0-9]{2,4})-([0-1][0-9])-([0-3][0-9]) (?:([0-2][0-9]):([0-5][0-9]):([0-5][0-9]))?$/, "$1 $2 $3 $4 $5 $6").split(" "), a = (new Date(a[0], a[1] - 1, a[2], a[3], a[4], a[5])).getTime() / 1E3, a = "./?_task=mail&_action=compose&_subject=" + encodeURIComponent($("#summary").val()) + "&_date=" + (Math.round(a) - 0), rcmail.env.compose_extwin = !0, rcmail.env.extwin = !1, rcmail.open_compose_step(a))
            };

			// ATTENDEES MODIFICATION
            this.set_attending_members_value = function (checkbox) {
                var attending_member_str = $(checkbox).val();
                var split_attending_member_str = attending_member_str.split("|");
                var attending_member_email = split_attending_member_str[0];
                var attending_member_username = split_attending_member_str[1];

                if ($(checkbox).is(":checked")) {
					// add it to attending selected member
                    UI.add_hidden_field_value($('#hidden_selected_invitee_username'), attending_member_username);
                    UI.add_hidden_field_value($('#hidden_selected_invitee_email'), attending_member_email);
					// remove from not attending members
					UI.remove_hidden_field_value( $( '#hidden_unselected_invitee_username' ), attending_member_username );
					UI.remove_hidden_field_value( $( '#hidden_unselected_invitee_email' ), attending_member_email );
					
                } 
				else {
					// add to unselected members
					UI.add_hidden_field_value( $( '#hidden_unselected_invitee_username' ), attending_member_username );
					UI.add_hidden_field_value( $( '#hidden_unselected_invitee_email' ), attending_member_email );
					// remove from selected members
                    UI.remove_hidden_field_value($('#hidden_selected_invitee_username'), attending_member_username);
                    UI.remove_hidden_field_value($('#hidden_selected_invitee_email'), attending_member_email);
                }
            };

			// CALLED WHEN EVENT IS CLICKED
            this.add_existing_attending_member = function (selected_invitee_email_arr, selected_invitee_usename_arr, selected_invitee_role_arr, unselected_invitee_email_arr, unselected_invitee_username_arr, unselected_invitee_role_arr) {
				// ["REQ-PARTICIPANT", "OPT-PARTICIPANT", "OPT-PARTICIPANT"] 
                // first define an array which will contain email and username of all members, including selected and unselected
                var all_members_email = [];
                var all_members_username = [];
				var all_members_role = [];
				
				// since jquery.merge method overwrites original array, store the old array in duplicate array
                // table with attending members
                var atending_members_table = $('#attendee_details_table');

                // first check if both selected and unselected members are empty
                if ((typeof (selected_invitee_email_arr) == 'undefined') && (selected_invitee_email_arr == null) && (typeof (unselected_invitee_email_arr) == 'undefined') && (unselected_invitee_email_arr == null)) {
                    return;
                }
				else if ((typeof (selected_invitee_email_arr) == 'undefined') && (selected_invitee_email_arr == null)) // if no selected invitee, then set all members will be set to unselected
                {
                    all_members_email = unselected_invitee_email_arr;
                    all_members_username = unselected_invitee_username_arr;
					all_members_role = unselected_invitee_role_arr;
                }
				else if ((typeof (unselected_invitee_email_arr) == 'undefined') && (unselected_invitee_email_arr == null)) // if no unselected members present, then set all members to selected 
                {
                    all_members_email = selected_invitee_email_arr;
                    all_members_username = selected_invitee_usename_arr;
					all_members_role = selected_invitee_role_arr;
                }
				else {
					all_members_email = UI.concat_array( selected_invitee_email_arr, unselected_invitee_email_arr );
					all_members_username = UI.concat_array( selected_invitee_usename_arr, unselected_invitee_username_arr );
					all_members_role = UI.concat_array( selected_invitee_role_arr, unselected_invitee_role_arr );
                }
				
                for (var i = 0; i < all_members_email.length; i++) {
					// if selected members are present
					if( ( typeof ( selected_invitee_email_arr ) != 'undefined' ) && ( selected_invitee_email_arr ) )
					{
						if( UI.check_inArray( all_members_email[i], selected_invitee_email_arr ) )
						{
							this.add_attending_member_table_row( all_members_email[ i ] + "|" + all_members_username[ i ], true, all_members_role[ i ] );
						}
						else
						{
							this.add_attending_member_table_row( all_members_email[ i ] + "|" + all_members_username[ i ], false, all_members_role[ i ] );
						}
					}
					else
					{
						this.add_attending_member_table_row( all_members_email[ i ] + "|" + all_members_username[ i ], false, all_members_role[ i ] );
					}
                }
				return false;
            }
			
			this.enable_allday = function ( checkbox ) {
				if( $(checkbox).is(":checked") )
				{
					$('#starttime').val( '00:00' );
					$('#endtime').val( '23:59' );
					$('#starttime').attr('readonly',true).datepicker("destroy");
					$('#endtime').attr('readonly',true).datepicker("destroy");
				}
				else
				{
					this.initClockPickers();
				}
			}
			
			this.format_date = function ( date ) {
				var month_array =  [ "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December" ];
				var day = date.getDate();
				var month = date.getMonth( date )
				var year = date.getFullYear();
				var formatted_day = UI.add_suffix_to_num( day );
				
				var hours = date.getHours()
				var minutes = date.getMinutes();
				
				if( hours.toString().length == 1 )
					hours = "0" + hours;
					
				if( minutes.toString().length == 1 )
					minutes = "0" + minutes;
				
				var formatted_date = formatted_day + " " + month_array[ month ] + ", " + year + " " + hours + ": " + minutes;
				
				return formatted_date;
			}
			
            this.add_event_attending_member = function ( attendees_detail_string, is_external_attendee ) {
				if( is_external_attendee ) // for external attendees, there are two possibilities.. 1. direct email. 2. collected contact with format username <email>
				{
					if( ( attendees_detail_string.indexOf( '<' ) != -1 ) && ( attendees_detail_string.indexOf( '>' ) != -1 ) )
					{
						var arrStr = attendees_detail_string.split(/[<>]/);
						username = arrStr[ 0 ];
						email = arrStr[ 1 ];
						var attending_members_details_str = email + "|" + username;
					}
					else
					{
						var attending_members_details_str = attendees_detail_string + "|" + attendees_detail_string.split( '@' )[ 0 ];
					}
				}
				else
				{
					var attending_members_details_str = attendees_detail_string;
				}
				
				var attending_members_details_arr = attending_members_details_str.split( '|' );
				var attending_member_username = attending_members_details_arr[ 1 ]; // not used
				var attending_member_email = attending_members_details_arr[ 0 ];
				
                // var attending_members_details_str = $("#invitees_details_dropdown").val(); // attending_members_username
				var _continue = true; // flag variable set to false if email is already selected.
				
				// since, removing of existing members on event click was not possible, validation is done on add click, where it checks whether the user is already added or not
				var event_attendees_table_rows = $('#attendee_details_table').find('tr'); // finds all TRs from the table
				
				// since first tr contains table headings skip first row
				for( var i = 1; i < event_attendees_table_rows.length; i++ )
				{
					// get all TDs
					var table_data = event_attendees_table_rows.eq( i ).find("td");
					// since 3rd td is label which we want to check, we directly use eq(2) and store the html in that
					var selected_email = $(table_data).eq(2).find('label').html();
					
					if( selected_email == attending_member_email )
					{
						// if both emails are same, donot add the email to table
						_continue = false;
						break;
					}
					else
					{
						// if email is not already selected, add it to table
						_continue = true;
					}
				}
				
				if( attendees_detail_string.length > 0 )
				{
					if( _continue == true )
					{
						this.add_attending_member_table_row( attending_members_details_str, false );
					}
					else
					{
						rcmail.display_message("This Email is already selected", "error");
					}
				}
				else
				{
					rcmail.display_message("Please Enter the Email ID", "error");

				}
            };
			
			this.show_attendee_block = function( radio_button, attendee_type ) 
			{
				if( attendee_type == 'company' )
				{
					$( '#company_attendee_details_block' ).show();
					$( '#external_attendee_details_block' ).hide();
				}
				else
				{
					$( '#external_attendee_details_block' ).show();
					$( '#company_attendee_details_block' ).hide();
				}
			}
			
			this.add_attending_member_table_row = function(user_details_str, isChecked, role) {
				var atending_members_table = $('#attendee_details_table');
				var user_details_arr = user_details_str.split( "|" );
				var username = user_details_arr[ 1 ];
				var email = user_details_arr[ 0 ];
				var role_value = [ 'REQ-PARTICIPANT', 'CHAIR', 'OPT-PARTICIPANT', 'NON-PARTICIPANT' ];
				var role_text = [ 'Required Participant', 'Chair', 'Optional Participant', 'Non Participant' ];
				
				var selected_user_details = [];
				UI.remove_hidden_field_value($('#hidden_unselected_invitee_username'), 'null');
				UI.remove_hidden_field_value($('#hidden_unselected_invitee_email'), 'null');
				UI.remove_hidden_field_value($('#hidden_selected_invitee_username'), 'null');
				UI.remove_hidden_field_value($('#hidden_selected_invitee_email'), 'null');
				
				// Select Checkbox
				if( isChecked )
				{
					var attending_member_checkbox = $('<input />').attr({
						type: 'checkbox',
						id: 'select_event_attendee',
						onclick: 'calendar_gui.set_attending_members_value($(this));',
						value: user_details_str,
						checked: 'checked'
					});
					
					// if checkbox is not checked, ie. if user is not selected, set the details to attending member username
					UI.add_hidden_field_value($('#hidden_selected_invitee_email'), email);
					UI.add_hidden_field_value($('#hidden_selected_invitee_username'), username);
				}
				else
				{
					var attending_member_checkbox = $('<input />').attr({
						type: 'checkbox',
						id: 'select_event_attendee',
						onclick: 'calendar_gui.set_attending_members_value($(this));',
						value: user_details_str
					});
					// if checkbox is not checked, ie. if user is not selected, set the details to attending member username
					UI.add_hidden_field_value($('#hidden_unselected_invitee_username'), username);
					UI.add_hidden_field_value($('#hidden_unselected_invitee_email'), email);
				}
				
				// Username
				var attending_member_username = $("<label>").text(username);
				
				// Email
				var attending_member_email = $("<label>").text(email);
				
				// Role
				var attending_member_role = $('<select />');
				attending_member_role.attr( { 'id': 'event_attendee_role', 'name': 'event_attendee_role[]' } );
				for( var i = 0; i < role_value.length; i++ )
				{
					// since the value doesnot specify for which email it is assigned, append email to val
					var value = role_value[ i ] + "|" + email;
					$('<option />', {value: value, text: role_text[ i ]}).appendTo(attending_member_role);
				}
				// when refreshing calendar page, roles of all events are set, where as when hitting the add button they're not set. so, check is done if role present or not
				if( ( typeof( role ) != 'undefined' ) && ( role != null ) )
					attending_member_role.val( role + "|" + email );
				
				// Remove
				var remove_attending_member = $('<a>');
				remove_attending_member.attr({
					class: 'remove_attending_member',
					onclick: 'calendar_gui.remove_attending_members($(this).parent().parent())',
					href: '#'
				}).html('Remove');
				
				// Create TDs of the HTML Elements
				var row_check_box = $('<td>').append($(attending_member_checkbox));
				var row_username = $('<td>').append($(attending_member_username));
				var row_user_email = $('<td>').append($(attending_member_email));
				var row_role_select = $('<td>').append($(attending_member_role));
				var row_delete_button = $('<td>').append($(remove_attending_member));
				
				// add TDs to TR
				var attending_member_details_row = $('<tr>').append($(row_check_box)).append($(row_username)).append($(row_user_email)).append($(row_role_select)).append($(row_delete_button));
				
				// Apply these values to table
				var table_rows = $(atending_members_table).find('tr');
				var rows_count = $(table_rows).length;
				var last_row = $(table_rows).eq(rows_count - 1);
				
				$(last_row).after(attending_member_details_row);
				
				$( '#external_invitee_email' ).val( '' );
			};

            this.remove_attending_members = function (remove_row) {
                var removed_member_details = $(remove_row).find('#select_event_attendee').val();
                var removed_member_details_arr = removed_member_details.split("|");
                var removed_member_username = removed_member_details_arr[1];
                var removed_member_email = removed_member_details_arr[0];
				
                UI.remove_hidden_field_value($('#hidden_selected_invitee_username'), removed_member_username);
                UI.remove_hidden_field_value($('#hidden_selected_invitee_email'), removed_member_email);
                UI.remove_hidden_field_value($('#hidden_unselected_invitee_username'), removed_member_username);
                UI.remove_hidden_field_value($('#hidden_unselected_invitee_email'), removed_member_email);

				// if all values are removed, hidden fields are set to "null" and in server side, code is tested
				if( ( $('#hidden_unselected_invitee_email').val() == "" ) && ( $('#hidden_selected_invitee_email').val() == "" ) )
				{
					$('#hidden_selected_invitee_username').val( "null" );
					$('#hidden_selected_invitee_email').val( "null" );
					$('#hidden_unselected_invitee_username').val( "null" );
					$('#hidden_unselected_invitee_email').val( "null" );
				}
				
                $(remove_row).remove();
            };
			
			this.confirm_send_invitees = function () {
				var selected_invitees_email_str =  $( '#hidden_selected_invitee_email' ).val();
				if( selected_invitees_email_str.length > 0 )
				{
					var email_arr = selected_invitees_email_str.split( "|" );
					var formatted_email_str = email_arr.join( ", " );
					var reply = confirm( "Would you like to send out an Invitation email to the following: " + formatted_email_str + "?" );
					if( reply == true  )
					{
						return true;
					}
					else
					{
						return false;
					}
				}
			};

            this.reminderControls = function (a) {
                a && a.getTime() < (new Date).getTime() - 6E4 
				?(
					$("#reminderenable").prop("disabled", !0), 
					$("#reminderdisable").prop("disabled", !0), 
					$("#minsel").prop("disabled", !0), 
					$("#hoursel").prop("disabled", !0), 
					$("#daysel").prop("disabled", !0), 
					$("#weeksel").prop("disabled", !0), 
					$("#reminderminutesbefore").prop("disabled", !0), 
					$("#reminderhoursbefore").prop("disabled", !0), 
					$("#reminderdaysbefore").prop("disabled", !0), 
					$("#reminderweeksbefore").prop("disabled", !0), 
					$("#customreminder").prop("disabled", !0), 
					$("#remindertype").prop("disabled", !0), 
					$("#remindermailto").prop("disabled", !0)
				)
				: (
					$("#reminderenable").prop("disabled", !1), 
					$("#reminderdisable").prop("disabled", !1), 
					a = !0, 
					$("#reminderenable").prop("checked") 
					?
						a = !1 
					: (
						$("#remindercustom").val("--"), 
						$("#customreminder").prop("checked", !1)
					), 
					$("#minsel").prop("disabled", a), 
					$("#hoursel").prop("disabled", a), 
					$("#daysel").prop("disabled", a), 
					$("#weeksel").prop("disabled", a), 
					$("#customreminder").prop("disabled", a), 
					$("#remindertype").prop("disabled", a), 
					$("#reminderminutesbefore").prop("disabled", a), 
					$("#reminderhoursbefore").prop("disabled", a), 
					$("#reminderdaysbefore").prop("disabled", a), 
					$("#reminderweeksbefore").prop("disabled", a), 
					$("#remindertype").prop("disabled", a), 
					$("#remindermailto").prop("disabled", a)
				)
            };
            $("#reminderenable").click(function () {
                calendar_gui.reminderControls()
            });
            $("#reminderdisable").click(function () {
                calendar_gui.reminderControls()
            });
            $("#remindertype").change(function () {
                "email" == $("#remindertype").val() ? $("#remindermailto").attr("disabled", !1) : $("#remindermailto").attr("disabled", !0)
            });
            $("#reminderminutesbefore").click(function () {
                $("#minsel").attr("checked", !0)
            });
            $("#reminderhoursbefore").click(function () {
                $("#hoursel").attr("checked", !0)
            });
            $("#reminderdaysbefore").click(function () {
                $("#daysel").attr("checked", !0)
            });
            $("#reminderweeksbefore").click(function () {
                $("#weeksel").attr("checked", !0)
            });
            $("#customreminder").click(function () {
                $("#remindercustom").focus()
            });
            this.initAccordion();
            $("#calendaroverlay").hide()
        }
    };
    this.upcoming = function (b) {
        $("#upcoming").fullCalendar("destroy");
        $("#upcoming").fullCalendar({
            header: {
                left: "",
                center: "",
                right: ""
            },
            titleFormat: {
                month: b.settings.titleFormatMonth,
                week: b.settings.titleFormatWeek,
                day: b.settings.titleFormatDay
            },
            columnFormat: {
                month: b.settings.columnFormatMonth,
                week: b.settings.columnFormatWeek,
                day: b.settings.columnFormatDay
            },
            theme: b.settings.ui_theme_upcoming,
            readyState: function () {},
            editable: !0,
            ignoreTimezone: !1,
            monthNames: b.settings.months,
            monthNamesShort: b.settings.months_short,
            dayNames: b.settings.days,
            dayNamesShort: b.settings.days_short,
            firstDay: b.settings.first_day,
            firstHour: b.settings.first_hour,
            slotMinutes: 60 / b.settings.timeslots,
            timeFormat: js_time_formats[rcmail.env.rc_time_format],
            axisFormat: js_time_formats[rcmail.env.rc_time_format],
            defaultView: "basicDay",
            allDayText: rcmail.gettext("all-day", "calendar"),
            height: 1,
            dayClick: function (a, d, e, f) {
                calendar_callbacks.dayClick(a, d, e, f, b)
            },
            eventClick: function (a) {
                calendar_callbacks.eventClick(a, b)
            },
            eventRender: function (a, d, e) {
                calendar_callbacks.eventRender_upcoming(a, d, e, b)
            }
        });
        this.adjustLeft();
        $(window).resize(function () {
            if ($("#filters-table-content").get(0)) {
                var a = Math.max($("#filters-table-content").position().top - $("#subscriptions-table").position().top, $("#subscription-table-content").position().top - $("#subscriptions-table").position().top) + 1;
                $("#subscriptions-table").css("max-height", "226px");
                $("#subscription-table-content").css("max-height", 226 - a + "px");
                $("#filters-table-content").css("max-height",
                    226 - a + "px")
            }
            calendar_gui.adjustLeft();
            window.setTimeout("calendar_gui.adjustRight();", 200)
        })
    };
    this.adjustLeft = function () {
        if ("classic" != rcmail.env.skin) {
            if ($("#filters-table-content").get(0)) {
                var b = 0;
                $("#upcoming-maincontainer").is(":visible") && (b = 150);
                b = $("#sectionslist").height() - $("#sectionslist").position().top - $("#subscriptions-table").position().top - b;
                if (b > $("#subscriptions-table").height()) {
                    var a = Math.max($("#filters-table-content").position().top - $("#subscriptions-table").position().top, $("#subscription-table-content").position().top -
                        $("#subscriptions-table").position().top) + 1;
                    $("#subscriptions-table").css("max-height", b + "px");
                    $("#subscription-table-content").css("max-height", b - a + "px");
                    $("#filters-table-content").css("max-height", b - a + "px")
                }
            } else $("#subscriptions-table").hide();
            $("#upcoming").css("top", $("#upcoming-maincontainer").position().top + "px");
            $(".fc-view-basicDay").height($("#upcoming").height())
        }
    };
    this.adjustRight = function () {
        try {
            $(".fc-agenda-divider").position() && $(".fc-agenda-divider").next().height($(".calstatusbar").position().top -
                $(".fc-agenda-divider").position().top - 45)
        } catch (b) {
            $(".fc-agenda-divider").position() && $(".fc-agenda-divider").next().height(Math.round($("#calendar").height() - $(".fc-agenda-divider").position().top - 17))
        }
        $(".fc-content").height($("#calendar").height());
        $(".fc-view-" + $("#calendar").fullCalendar("getView").name).height($("#calendar").height());
        $(".fc-view-" + $("#calendar").fullCalendar("getView").name + " table:first-child").attr("style", "width: 100%; height: 100%;");
        $("hr.timeline").remove();
        calendar_callbacks.setTimeline()
    };

    this.initTabs = function (b, a) {
        $("#event").tabs();
        $("#event").tabs("enable", b);
        $("#event").tabs("disable", a);
        $("#event").tabs("select", 0)
    };
    this.initAccordion = function () {
        $(function () {
            for (var a = 0; 4 > a; a++)
                $("#accordion" + a).accordion({
                    autoHeight: !1,
                    collapsible: !0,
                    active: !1
                })
        });
        for (var b = 0; 4 > b; b++)
            $("#accordion" + b).accordion("option", "header", "span")
    };
	
	// while enabling the timepickers for all day event, b cannot be defined. therefor first check if b is undefined, if yes then use hardcoded values and initialize date time pickers...
	this.initClockPickers = function (b) {
		if( ( typeof( b ) != 'undefined' ) && ( b != null ) )
		{
			var FormatTime = b.settings.FormatTime;
			var duration = b.settings.duration;
		}
		else
		{
			var FormatTime = "HH:mm";
			var duration = "3600";
		}
		
			$("#starttime").timepicker({
				timeOnlyTitle: rcmail.gettext("timeOnlyTitle", "calendar"),
				timeText: rcmail.gettext("timeText", "calendar"),
				hourText: rcmail.gettext("hourText", "calendar"),
				minuteText: rcmail.gettext("minuteText", "calendar"),
				stepMinute: 1,
				currentText: rcmail.gettext("currentText", "calendar"),
				closeText: rcmail.gettext("closeText", "calendar"),
				onClose: function () {
					var a = $("#starttime").val().split(":"),
						d = a[0],
						a = a[1].split(" "),
						e = a[0];
					a[1] && (a[1] = a[1].toLowerCase(),
						"p" == a[1].substr(0, 1) && (d = parseInt(d) + 12));
					2 > d.length && (d = "0" + d);
					2 > e.length && (e = "0" + e);
					$("#starttime").val(d + ":" + e);
					$("#enddate").val() + $("#endtime").val() <= $("#startdate").val() + $("#starttime").val() && (a = $("#starttime").val().split(":"),
						d = new Date((new Date).getFullYear(), (new Date).getMonth(), (new Date).getDate(), parseFloat(a[0]), parseFloat(a[1]), 0),
						a = new Date(1E3 * (d.getTime() / 1E3 + parseInt(duration))),
						d = a.getHours() + "",
						e = a.getMinutes() + "",
						2 > d.length && (d = "0" + d),
						2 > e.length && (e = "0" + e), $("#endtime").val(d + ":" + e));
					a = $("#startdate").val() + "-" + $("#starttime").val().replace(":", "-");
					a = a.split("-");
					var start_date = new Date( parseInt(a[0]), parseInt(a[1]) - 1, parseInt(a[2]), parseInt(a[3]), parseInt(a[4]), 0 );
					var formatted_start_date = calendar_gui.format_date( start_date );
					$("#startfulltext").html( "(" + formatted_start_date + ")" );
					/* $("#startfulltext").html("(" + (
						new Date(
							parseInt(a[0]),
							parseInt(a[1]) - 1,
							parseInt(a[2]),
							parseInt(a[3]),
							parseInt(a[4]),
							0
						)
					).toLocaleString() + ")") */
				},
				timeFormat: FormatTime
			});
			$("#endtime").timepicker({
				timeOnlyTitle: rcmail.gettext("timeOnlyTitle", "calendar"),
				timeText: rcmail.gettext("timeText", "calendar"),
				hourText: rcmail.gettext("hourText", "calendar"),
				minuteText: rcmail.gettext("minuteText", "calendar"),
				stepMinute: 1,
				currentText: rcmail.gettext("currentText", "calendar"),
				closeText: rcmail.gettext("closeText", "calendar"),
				onClose: function () {
					var a = $("#endtime").val().split(":"),
						d = a[0],
						a = a[1].split(" "),
						e = a[0];
					a[1] && (a[1] = a[1].toLowerCase(),
						"p" == a[1].substr(0, 1) && (d = parseInt(d) + 12));
					2 > d.length && (d = "0" + d);
					2 > e.length && (e = "0" + e);
					$("#endtime").val(d + ":" + e);
					$("#enddate").val() + $("#endtime").val() <= $("#startdate").val() + $("#starttime").val() && (a = $("#starttime").val().split(":"),
						d = new Date(
							(new Date).getFullYear(), (new Date).getMonth(), (new Date).getDate(),
							parseFloat(a[0]),
							parseFloat(a[1]), 0),
						a = new Date(1E3 * (d.getTime() / 1E3 + parseInt(duration))),
						d = a.getHours() + "",
						e = a.getMinutes() + "",
						2 > d.length && (d = "0" + d),
						2 > e.length && (e = "0" + e),
						$("#endtime").val(d + ":" + e));
					a = $("#enddate").val() + "-" + $("#endtime").val().replace(":", "-");
					a = a.split("-");
					var end_date = new Date(parseInt(a[0]), parseInt(a[1]) - 1, parseInt(a[2]), parseInt(a[3]), parseInt(a[4]), 0);
					var formatted_end_date = calendar_gui.format_date( end_date );
					/* $("#endfulltext").html("(" + (new Date(parseInt(a[0]), parseInt(a[1]) - 1, parseInt(a[2]), parseInt(a[3]), parseInt(a[4]), 0)).toLocaleString() + ")") */
					$("#endfulltext").html("(" + formatted_end_date + ")")
				},
				timeFormat: FormatTime
			});
			$("#duetime").timepicker({
				timeOnlyTitle: rcmail.gettext("timeOnlyTitle", "calendar"),
				timeText: rcmail.gettext("timeText", "calendar"),
				hourText: rcmail.gettext("hourText", "calendar"),
				minuteText: rcmail.gettext("minuteText", "calendar"),
				stepMinute: 1,
				currentText: rcmail.gettext("currentText", "calendar"),
				closeText: rcmail.gettext("closeText", "calendar"),
				onClose: function () {
					var a = $("#duetime").val().split(":"),
						d = a[0],
						a = a[1].split(" "),
						e = a[0];
					a[1] && (a[1] = a[1].toLowerCase(), "p" == a[1].substr(0, 1) && (d = parseInt(d) + 12));
					2 > d.length && (d = "0" + d);
					2 > e.length && (e = "0" + e);
					$("#duetime").val(d + ":" + e);
					$("#duedate").val() + $("#duetime").val() < $("#startdate").val() + $("#starttime").val() && (a = $("#starttime").val().split(":"),
						d = new Date(
							(new Date).getFullYear(), (new Date).getMonth(), (new Date).getDate(),
							parseFloat(a[0]),
							parseFloat(a[1]), 0),
						a = new Date(1E3 * (d.getTime() / 1E3 + parseInt(duration))),
						d = a.getHours() + "",
						e = a.getMinutes() + "",
						2 > d.length && (d = "0" + d),
						2 > e.length && (e = "0" + e),
						$("#duetime").val(d + ":" + e));
					a = $("#duedate").val() + "-" + $("#duetime").val().replace(":", "-");
					a = a.split("-");
					$("#duefulltext").html("(" + (new Date(parseInt(a[0]), parseInt(a[1]) - 1, parseInt(a[2]), parseInt(a[3]), parseInt(a[4]), 0)).toLocaleString() + ")")
				},
				timeFormat: FormatTime
			})
		
    };
    /* this.initClockPickers = function (b) {
        $("#starttime").timepicker({
            timeOnlyTitle: rcmail.gettext("timeOnlyTitle", "calendar"),
            timeText: rcmail.gettext("timeText", "calendar"),
            hourText: rcmail.gettext("hourText", "calendar"),
            minuteText: rcmail.gettext("minuteText", "calendar"),
            stepMinute: 1,
            currentText: rcmail.gettext("currentText", "calendar"),
            closeText: rcmail.gettext("closeText", "calendar"),
            onClose: function () {
                var a = $("#starttime").val().split(":"),
                    d = a[0],
                    a = a[1].split(" "),
                    e = a[0];
                a[1] && (a[1] = a[1].toLowerCase(),
                    "p" == a[1].substr(0, 1) && (d = parseInt(d) + 12));
                2 > d.length && (d = "0" + d);
                2 > e.length && (e = "0" + e);
                $("#starttime").val(d + ":" + e);
                $("#enddate").val() + $("#endtime").val() <= $("#startdate").val() + $("#starttime").val() && (a = $("#starttime").val().split(":"),
                    d = new Date((new Date).getFullYear(), (new Date).getMonth(), (new Date).getDate(), parseFloat(a[0]), parseFloat(a[1]), 0),
                    a = new Date(1E3 * (d.getTime() / 1E3 + parseInt(b.settings.duration))),
                    d = a.getHours() + "",
                    e = a.getMinutes() + "",
                    2 > d.length && (d = "0" + d),
                    2 > e.length && (e = "0" + e), $("#endtime").val(d + ":" + e));
                a = $("#startdate").val() + "-" + $("#starttime").val().replace(":", "-");
                a = a.split("-");
                $("#startfulltext").html("(" + (
                    new Date(
                        parseInt(a[0]),
                        parseInt(a[1]) - 1,
                        parseInt(a[2]),
                        parseInt(a[3]),
                        parseInt(a[4]),
                        0
                    )
                ).toLocaleString() + ")")
            },
            timeFormat: b.settings.FormatTime
        });
        $("#endtime").timepicker({
            timeOnlyTitle: rcmail.gettext("timeOnlyTitle", "calendar"),
            timeText: rcmail.gettext("timeText", "calendar"),
            hourText: rcmail.gettext("hourText", "calendar"),
            minuteText: rcmail.gettext("minuteText", "calendar"),
            stepMinute: 1,
            currentText: rcmail.gettext("currentText", "calendar"),
            closeText: rcmail.gettext("closeText", "calendar"),
            onClose: function () {
                var a = $("#endtime").val().split(":"),
                    d = a[0],
                    a = a[1].split(" "),
                    e = a[0];
                a[1] && (a[1] = a[1].toLowerCase(),
                    "p" == a[1].substr(0, 1) && (d = parseInt(d) + 12));
                2 > d.length && (d = "0" + d);
                2 > e.length && (e = "0" + e);
                $("#endtime").val(d + ":" + e);
                $("#enddate").val() + $("#endtime").val() <= $("#startdate").val() + $("#starttime").val() && (a = $("#starttime").val().split(":"),
                    d = new Date(
                        (new Date).getFullYear(), (new Date).getMonth(), (new Date).getDate(),
                        parseFloat(a[0]),
                        parseFloat(a[1]), 0),
                    a = new Date(1E3 * (d.getTime() / 1E3 + parseInt(b.settings.duration))),
                    d = a.getHours() + "",
                    e = a.getMinutes() + "",
                    2 > d.length && (d = "0" + d),
                    2 > e.length && (e = "0" + e),
                    $("#endtime").val(d + ":" + e));
                a = $("#enddate").val() + "-" + $("#endtime").val().replace(":", "-");
                a = a.split("-");
                $("#endfulltext").html("(" + (new Date(parseInt(a[0]), parseInt(a[1]) - 1, parseInt(a[2]), parseInt(a[3]), parseInt(a[4]), 0)).toLocaleString() + ")")
            },
            timeFormat: b.settings.FormatTime
        });
        $("#duetime").timepicker({
            timeOnlyTitle: rcmail.gettext("timeOnlyTitle", "calendar"),
            timeText: rcmail.gettext("timeText", "calendar"),
            hourText: rcmail.gettext("hourText", "calendar"),
            minuteText: rcmail.gettext("minuteText", "calendar"),
            stepMinute: 1,
            currentText: rcmail.gettext("currentText", "calendar"),
            closeText: rcmail.gettext("closeText", "calendar"),
            onClose: function () {
                var a = $("#duetime").val().split(":"),
                    d = a[0],
                    a = a[1].split(" "),
                    e = a[0];
                a[1] && (a[1] = a[1].toLowerCase(), "p" == a[1].substr(0, 1) && (d = parseInt(d) + 12));
                2 > d.length && (d = "0" + d);
                2 > e.length && (e = "0" + e);
                $("#duetime").val(d + ":" + e);
                $("#duedate").val() + $("#duetime").val() < $("#startdate").val() + $("#starttime").val() && (a = $("#starttime").val().split(":"),
                    d = new Date(
                        (new Date).getFullYear(), (new Date).getMonth(), (new Date).getDate(),
                        parseFloat(a[0]),
                        parseFloat(a[1]), 0),
                    a = new Date(1E3 * (d.getTime() / 1E3 + parseInt(b.settings.duration))),
                    d = a.getHours() + "",
                    e = a.getMinutes() + "",
                    2 > d.length && (d = "0" + d),
                    2 > e.length && (e = "0" + e),
                    $("#duetime").val(d + ":" + e));
                a = $("#duedate").val() + "-" + $("#duetime").val().replace(":", "-");
                a = a.split("-");
                $("#duefulltext").html("(" + (new Date(parseInt(a[0]), parseInt(a[1]) - 1, parseInt(a[2]), parseInt(a[3]), parseInt(a[4]), 0)).toLocaleString() + ")")
            },
            timeFormat: b.settings.FormatTime
        })
    }; */

    this.initNavDatepicker = function (b) {
        var a = new Date(0),
            d = new Date(1E3 * rcmail.env.caleot_unix);
        "caldav" == b.settings.backend && (d = new Date, a = d.getFullYear() - b.settings.caldav_replication_range.past, d = d.getFullYear() + b.settings.caldav_replication_range.future, a = new Date(a, 0, 1), d = new Date(d, 11, 31));
        $("#jqdatepicker").datepicker({
            dateFormat: "yy-mm-dd",
            firstDay: b.settings.first_day,
            minDate: a,
            maxDate: d,
            changeYear: !0,
            changeMonth: !0,
            onSelect: function (a) {
                temparr = a.split("-");
                a = new Date(temparr[0], temparr[1] - 1, temparr[2]);
                $("#calendar").fullCalendar("gotoDate", $.fullCalendar.parseDate(a));
                a = new Date($.fullCalendar.parseDate(a));
                var b = new Date;
                b.setTime(a.getTime() + 8634E4);
                $("#calendar").fullCalendar("select", a, b, !1)
            },
            onChangeMonthYear: function (a, b) {
                if (rcmail.env.onChangeMonthYear) {
                    var d = new Date(a, b - 1, 1);
                    $("#calendar").fullCalendar("gotoDate", $.fullCalendar.parseDate(d));
                    var d = new Date($.fullCalendar.parseDate(d)),
                        g = new Date;
                    g.setTime(d.getTime() + 8634E4);
                    $("#calendar").fullCalendar("select",
                        d, g, !1)
                }
            }
        })
    };
    this.initExpireDatepicker = function (b, a) {
        var d = b.getDate() + "",
            e = b.getMonth() + "",
            f = b.getFullYear() + "";
        $("#expires").datepicker("destroy");
        $("#expires").datepicker({
            dateFormat: "yy-mm-dd",
            changeMonth: !0,
            changeYear: !0,
            firstDay: a.settings.first_day,
            maxDate: new Date(1E3 * rcmail.env.caleot_unix),
            minDate: new Date(f, e, d),
            onSelect: function () {
                $("#expires").val() < $("#enddate").val() && $("#expires").val($("#enddate").val())
            }
        });
        2 > d.length && (d = "0" + d);
        e = parseInt(e) + 1 + "";
        2 > e.length && (e = "0" + e);
        $("#expires").val(f +
            "-" + e + "-" + d);
        rcmail.env.remember_expires = $("#expires").val()
    };
    this.initStartdateDatepicker = function (b, a) {
        var d = b.getDate() + "",
            e = b.getMonth() + "",
            f = b.getFullYear() + "";
        $("#startdate").datepicker("destroy");
        $("#startdate").datepicker({
            dateFormat: "yy-mm-dd",
            changeMonth: !0,
            changeYear: !0,
            firstDay: a.settings.first_day,
            maxDate: new Date(1E3 * rcmail.env.caleot_unix),
            onSelect: function () {
                if ($("#enddate").val() + $("#endtime").val() <= $("#startdate").val() + $("#starttime").val()) {
                    $("#enddate").val($("#startdate").val());
                    var b = $("#starttime").val().split(":");
                    (new Date).getFullYear();
                    (new Date).getMonth();
                    (new Date).getDate();
                    parseFloat(b[0]);
                    parseFloat(b[1])
                }
                $("#expires").val() < $("#enddate").val() && $("#expires").val($("#enddate").val());
                calendar_gui.initReminderDatepicker(new Date($("#startdate").datepicker("getDate").getTime() + 86399E3), a);
                b = $("#startdate").val() + "-" + $("#starttime").val().replace(":", "-");
                b = b.split("-");
				var start_date = new Date(parseInt(b[0]), parseInt(b[1]) - 1, parseInt(b[2]), parseInt(b[3]),
                    parseInt(b[4]), 0);
				var formatted_start_date = calendar_gui.format_date( start_date );
                /* $("#startfulltext").html("(" + (new Date(parseInt(b[0]), parseInt(b[1]) - 1, parseInt(b[2]), parseInt(b[3]),
                    parseInt(b[4]), 0)).toLocaleString() + ")") */
				$("#startfulltext").html("(" + formatted_start_date + ")")
            }
        });
        2 > d.length && (d = "0" + d);
        e = parseInt(e) + 1 + "";
        2 > e.length && (e = "0" + e);
        $("#startdate").val(f + "-" + e + "-" + d)
    };
    this.initEnddateDatepicker = function (b, a) {
        var d = b.getDate() + "",
            e = b.getMonth() + "",
            f = b.getFullYear() + "";
        $("#enddate").datepicker("destroy");
        $("#enddate").datepicker({
            dateFormat: "yy-mm-dd",
            changeMonth: !0,
            changeYear: !0,
            firstDay: a.settings.first_day,
            maxDate: new Date(1E3 * rcmail.env.caleot_unix),
            onSelect: function () {
                if ($("#enddate").val() + $("#endtime").val() <= $("#startdate").val() +
                    $("#starttime").val()) {
                    $("#startdate").val($("#enddate").val());
                    var a = $("#starttime").val().split(":");
                    (new Date).getFullYear();
                    (new Date).getMonth();
                    (new Date).getDate();
                    parseFloat(a[0]);
                    parseFloat(a[1])
                }
                $("#expires").val() < $("#enddate").val() && $("#expires").val($("#enddate").val());
                a = $("#enddate").val() + "-" + $("#endtime").val().replace(":", "-");
                a = a.split("-");
				var end_date = new Date(parseInt(a[0]), parseInt(a[1]) - 1, parseInt(a[2]), parseInt(a[3]), parseInt(a[4]), 0);
				var formatted_end_date = calendar_gui.format_date( end_date );
                /* $("#endfulltext").html("(" + (new Date(parseInt(a[0]), parseInt(a[1]) - 1, parseInt(a[2]), parseInt(a[3]), parseInt(a[4]), 0)).toLocaleString() + ")") */
				$("#endfulltext").html("(" + formatted_end_date + ")")
            }
        });
        2 > d.length && (d = "0" + d);
        e = parseInt(e) + 1 + "";
        2 > e.length && (e = "0" + e);
        $("#enddate").val(f + "-" + e + "-" + d)
    };
    this.initDuedateDatepicker = function (b, a) {
        var d = b.getDate() + "",
            e = b.getMonth() + "",
            f = b.getFullYear() + "";
        $("#duedate").datepicker("destroy");
        $("#duedate").datepicker({
            dateFormat: "yy-mm-dd",
            changeMonth: !0,
            changeYear: !0,
            firstDay: a.settings.first_day,
            maxDate: new Date(1E3 * rcmail.env.caleot_unix),
            onSelect: function () {
                $("#duedate").val() + $("#duetime").val() < $("#startdate").val() + $("#starttime").val() && $("#duedate").val($("#startdate").val());
                temparr = $("#duedate").val() + "-" + $("#duetime").val().replace(":", "-");
                temparr = temparr.split("-");
                $("#duefulltext").html("(" + (new Date(parseInt(temparr[0]), parseInt(temparr[1]) - 1, parseInt(temparr[2]), parseInt(temparr[3]), parseInt(temparr[4]), 0)).toLocaleString() + ")")
            }
        });
        2 > d.length && (d = "0" + d);
        e = parseInt(e) + 1 + "";
        2 > e.length && (e = "0" + e);
        $("#duedate").val(f + "-" + e + "-" + d)
    };
    this.initReminderDatepicker = function (b, a) {
        $("#remindercustom").datetimepicker("destroy");
        $("#remindercustom").val("--");
        b.getTime() > (new Date).getTime() &&
            $("#remindercustom").datetimepicker({
                beforeShow: function () {
                    !1 === $("#reminderenable").prop("checked") && window.setTimeout("$('#remindercustom').datetimepicker('hide');", 10)
                },
                onClose: function () {
                    "--" == $("#remindercustom").val() && ($("#customreminder").attr("checked", !1), $("#hoursel").attr("checked", !0))
                },
                timeText: rcmail.gettext("timeText", "calendar"),
                hourText: rcmail.gettext("hourText", "calendar"),
                minuteText: rcmail.gettext("minuteText", "calendar"),
                currentText: rcmail.gettext("currentText", "calendar"),
                closeText: rcmail.gettext("closeText",
                    "calendar"),
                dateFormat: "yy-mm-dd",
                changeMonth: !0,
                changeYear: !0,
                firstDay: a.settings.first_day,
                onSelect: function () {
                    $("#customreminder").attr("checked", !0)
                },
                maxDate: new Date(b),
                minDate: new Date
            })
    };

    this.resetForm = function (b) {
        $("input, textarea, select", "#form").each(function () {
            $(this).prop("disabled", !1)
        });
        $("#recursel").prop("disabled", !0);
        $("#everyweekdiv").hide();
        $("#everymonthdiv").hide();
        $("#everyyeardiv").hide();
        $("#everydaydiv").hide();
        for (var a = 0; 5 > a; a++)
            3 != a && calendar_gui.initTabs(a, 3);
        b.find("input:text").val("");
        b.find("textarea").val("");

        // Macgregor Changes
        var attendees_table = $('#attendee_details_table');
        var attendees_rows = $(attendees_table).find('tr');
        var attendees_rows_count = $(attendees_rows).length;

        for (var i = (attendees_rows_count - 1); i > 0; i--) {
            $(attendees_rows).eq(i).remove();
        }
		
        $('#hidden_unselected_invitee_username').val('');
        $('#hidden_unselected_invitee_email').val('');
        $('#hidden_selected_invitee_username').val('');
        $('#hidden_selected_invitee_email').val('');

        $("#description-container").html('<textarea name="description" id="description" rows="15" cols="39" style="width:350px;"></textarea>');
        b.find("select").prop("selectedIndex", 0);
        b.find("input:radio").attr("checked", !1);
        $("#reminderdisable").attr("checked", !0);
        b.find("input:checkbox").attr("checked", !1);
        $("#remindermailto").attr("disabled", !1);

        UI.load_invitees(true); // show default is true
		$('#company_attendee').attr( 'checked', 'checked' );
		$('#external_invitee_email').val( '' );
		$('#external_attendee_details_block').hide();
		$('#company_attendee_details_block').show();
    };

    this.resetDay = function () {
        $("#dnums").val(1)
    };
    this.resetWeek = function () {
        $("#wnums").val(1);
        $(".byweekdays").attr("checked", !1)
    };
    this.resetMonth = function (b) {
        "advanced" ==
            b ? ($("#mevery").prop("selectedIndex", 0), $("#mweekday").prop("selectedIndex", 0)) : "occurrences" == b ? $("#mnums").val(1) : $(".bymonthdays").attr("checked", !1)
    };
    this.resetYear = function (b) {
        "advanced" == b ? ($("#yweekday").prop("selectedIndex", 0), $("#yevery").prop("selectedIndex", 0), $("#ymonth").prop("selectedIndex", 0)) : "occurrences" == b ? $("#ynums").val(1) : ($("#ydnums").val(1), $("#ymonthday").prop("selectedIndex", 0))
    };
    this.toggleRecur = function () {
        var b = $("#recursel");
        0 == b.prop("selectedIndex") ? ($("#occurrences").val(0),
            $("#schedule").hide()) : $("#schedule").show();
        this.checkAll("#form", !1);
        $("#dnums").val(1);
        $("#wnums").val(1);
        $("#mnums").val(1);
        $("#ynums").val(1);
        $("#ydnums").val(0);
        0 <= b.prop("selectedIndex") && (calendar_gui.initAccordion(), $("#rrules").show());
        0 == b.prop("selectedIndex") ? $("#reset").hide() : $("#reset").show();
        1 == b.prop("selectedIndex") ? (this.currentStyle("daylink") && $("#everyweekdiv").hide(), $("#everymonthdiv").hide(), $("#everyyeardiv").hide(), $("#daylink").click()) : 2 == b.prop("selectedIndex") ? $("#rrules").hide() :
            3 == b.prop("selectedIndex") ? this.currentStyle("weeklink") && ($("#everydaydiv").hide(), $("#everymonthdiv").hide(), $("#everyyeardiv").hide(), $("#weeklink").click()) : 4 == b.prop("selectedIndex") ? this.currentStyle("monthlink") && ($("#everydaydiv").hide(), $("#everyweekdiv").hide(), $("#everyyeardiv").hide(), $("#monthlink").click()) : 5 == b.prop("selectedIndex") && this.currentStyle("yearlink") && ($("#everydaydiv").hide(), $("#everyweekdiv").hide(), $("#everymonthdiv").hide(), $("#yearlink").click())
    };
    this.checkAll = function (b,
        a) {
        null == b || void 0 == b || $(b + " input[type=checkbox]").each(function () {
            this.checked = a
        })
    };
    this.nums = function (b, a, d) {
        "" == $("#" + a).val() && $("#" + a).val(0);
        d && 0 < b && parseInt($("#" + a).val()) >= d && (b = 0);
        $("#" + a).val(Math.max(0, parseInt($("#" + a).val()) + b))
    };
    this.currentStyle = function (b) {
        b = document.getElementById(b);
        b.currentStyle ? (str = b.currentStyle.color, c = rcmail.env.linkcolor) : window.getComputedStyle && (str = window.getComputedStyle(b, null).color, c = rcmail.env.rgblinkcolor);
        return str != c ? !0 : !1
    };
    this.getView = function (b) {
        return "false" !=
            queryString("_view") ? queryString("_view") : b.settings.default_view
    }
}
calendar_gui = new calendar_gui;