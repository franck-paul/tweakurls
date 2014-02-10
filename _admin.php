<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is 'Tweak URLs', a plugin for Dotclear 2              *
 *                                                             *
 *  Copyright (c) 2009                                         *
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

$core->addBehavior('adminBlogPreferencesForm',array('tweakurlsAdminBehaviours','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('tweakurlsAdminBehaviours','adminBeforeBlogSettingsUpdate'));
$core->addBehavior('adminAfterPostCreate',array('tweakurlsAdminBehaviours','adminAfterPostSave'));
$core->addBehavior('adminAfterPostUpdate',array('tweakurlsAdminBehaviours','adminAfterPostSave'));

class tweakurlsAdminBehaviours
{
	public static function adminBlogPreferencesForm($core,$settings)
	{

		# URL modes
		$tweakurls_combo = array(
			__('default mode') => 'default',
			__('clean all diacritics') => 'nodiacritic',
			__('Lowercase') => 'lowercase'
		);
		echo
		'<fieldset><legend>Tweak URLs</legend>'.
		'<div>'.
		'<p><label>'.
		__('Posts URL type:')." ".
		form::combo('tweakurls_posturltransform',$tweakurls_combo,$settings->tweakurls->tweakurls_posturltransform).
		'</label></p>'.
		'</div>'.
		'</fieldset>';
	}
	public static function adminBeforeBlogSettingsUpdate($settings)
	{
		$settings->addNameSpace('tweakurls');
		$settings->tweakurls->put('tweakurls_posturltransform',$_POST['tweakurls_posturltransform']);
	}

	public static function adminAfterPostSave ($cur,$id=null)
	{
		global $core;

		$posturltransform = $core->blog->settings->tweakurls->tweakurls_posturltransform;
		if (isset($_POST['post_url'])||empty($_REQUEST['id']))
		{
			switch ($posturltransform)
			{
				case 'nodiacritic':
					$cur->post_url = text::str2URL($cur->post_url);
					break;
				case 'lowercase':
					$cur->post_url = strtolower(text::str2URL($cur->post_url));
					break;
			}
			$core->blog->updPost($id,$cur);
		}
	}
}
?>