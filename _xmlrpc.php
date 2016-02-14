<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
*  This is 'Tweak URLs', a plugin for Dotclear 2              *
  *                                                             *
 *  Copyright (c) 2016                                         *
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

// coreAfterCategoryCreate
$core->addBehavior('coreAfterCategoryCreate',array('tweakurlsXmlrpcBehaviours','coreAfterCategorySave'));

// coreAfterPostCreate, coreAfterPostUpdate
$core->addBehavior('coreAfterPostCreate',array('tweakurlsXmlrpcBehaviours','coreAfterPostSave'));
$core->addBehavior('coreAfterPostUpdate',array('tweakurlsXmlrpcBehaviours','coreAfterPostSave'));

class tweakurlsXmlrpcBehaviours
{
	public static function coreAfterPostSave ($cur)
	{
		global $core;

		if ($cur->post_id) {
			$cur->post_url = tweakUrls::tweakBlogURL($cur->post_url);
			$core->blog->updPost($cur->post_id,$cur);
		}
	}

	public static function coreAfterCategorySave ($cur)
	{
		global $core;

		if ($cur->cat_id) {
			$tweekurls_settings = tweakurlsSettings($core);
			$caturltransform = $tweekurls_settings->tweakurls_caturltransform;

			// if it is a sub-category, change only last part of its url
			$urls = explode('/',$cur->cat_url);
			$cat_url = array_pop($urls);
			$urls[] = tweakUrls::tweakBlogURL($cat_url,$caturltransform);
			$urls = implode('/',$urls);

			$new_cur = $core->con->openCursor($core->prefix.'category');
			$new_cur->cat_url = $urls;
			$new_cur->update('WHERE cat_id = '.$cur->cat_id);
		}
	}
}
