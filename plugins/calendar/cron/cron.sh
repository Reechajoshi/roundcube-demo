#!/bin/bash


echo 'cron.sh called'
php /var/www/webmail/plugins/calendar/cron/reminders.php
php /var/www/webmail/plugins/calendar/cron/sync_caldav_reminders.php
