#!/bin/bash
#
# Author: macgregor
#
#

BASE="/var/vmail"

if [ ${#} -eq 2 ] ; then
	DOM="${1}"
	USER="${2}"
	
	if [ -d "${BASE}/${DOM}/${USER}" ] ; then
		TLOC="${BASE}/_trash_/${DOM}"

		if [ ! -d "${TLOC}" ] ; then 
			mkdir "${TLOC}"
		fi

		mv "${BASE}/${DOM}/${USER}" "${TLOC}"
	fi
fi
