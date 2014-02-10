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

if (!defined('DC_CONTEXT_ADMIN')) exit;
global $core;

$this_version = $core->plugins->moduleInfo('tweakurls','version');
$installed_version = $core->getVersion('tweakurls');

if (version_compare($installed_version,$this_version,'>=')) {
	return;
}

$settings = tweakurlsSettings($core);
$settings->put('tweakurls_posturltransform','','string','determines posts URL type.',false,true);
$settings->put('tweakurls_caturltransform','','string','determines categories URL type.',false,true);
$settings->put('tweakurls_mtidywildcard','-','string','Wildcard for mtidy mode.',false,true);
$settings->put('tweakurls_mtidyremove',"_ ':[]-",'string','Last exotic chars to remove for mtidy mode.',false,true);

$core->setVersion('tweakurls',$this_version);

return true;
