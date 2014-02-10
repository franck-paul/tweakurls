<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
*  This is 'Tweak URLs', a plugin for Dotclear 2              *
  *                                                             *
 *  Copyright (c) 2010                                         *
 *  xave and contributors.                                     *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with 'My Favicon' (see COPYING.txt);         *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */			"tweakURLs",
	/* Description*/		"Tweaks you posts URLs",
	/* Author */			"xave",
	/* Version */			'0.8.1',
	/* Permissions */		'admin'
);
