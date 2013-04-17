#!/bin/bash
# Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
# For licensing, see LICENSE.html or http://ckeditor.com/license

# Updates cklangtool. This script should not be executed separately, it is included in other scripts.

CKLANGTOOL_VERSION="1.0"
CKLANGTOOL_URL="http://download.cksource.com/CKLangTool/$CKLANGTOOL_VERSION/langtool.jar"

PROGNAME=$(basename $0)
MSG_UPDATE_FAILED="Warning: The attempt to update cklangtooljar failed. The existing file will be used."
MSG_DOWNLOAD_FAILED="It was not possible to download langtool.jar"

function error_exit
{
	echo "${PROGNAME}: ${1:-"Unknown Error"}" 1>&2
	exit 1
}

function command_exists
{
	command -v "$1" > /dev/null 2>&1;
}

# Move to the script directory.
cd $(dirname $0)

# Download/update langtool.jar
mkdir -p cklangtool/$CKLANGTOOL_VERSION
cd cklangtool/$CKLANGTOOL_VERSION
if [ -f langtool.jar ]; then
	echo "Checking/Updating CKLangTool..."
	if command_exists curl ; then
	curl -O -R -z langtool.jar $CKLANGTOOL_URL || echo "$MSG_UPDATE_FAILED"
	else
	wget -N $CKLANGTOOL_URL || echo "$MSG_UPDATE_FAILED"
	fi
else
	echo "Downloading CKLangTool..."
	if command_exists curl ; then
	curl -O -R $CKLANGTOOL_URL || error_exit "$MSG_DOWNLOAD_FAILED"
	else
	wget -N $CKLANGTOOL_URL || error_exit "$MSG_DOWNLOAD_FAILED"
	fi
fi
cd ../..


plugins=( about basicstyles bidi blockquote clipboard colorbutton colordialog contextmenu div docprops elementspath fakeobjects find flash font format forms horizontalrule iframe image indent justify link list liststyle maximize newpage pagebreak pastefromword pastetext preview print removeformat save scayt selectall showblocks smiley sourcearea specialchar stylescombo table templates toolbar undo wsc )
plugins_dialogs=( a11yhelp specialchar )

