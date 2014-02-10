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

$core->addBehavior('adminBlogPreferencesForm',array('tweakurlsAdminBehaviours','adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate',array('tweakurlsAdminBehaviours','adminBeforeBlogSettingsUpdate'));
$core->addBehavior('adminAfterPostCreate',array('tweakurlsAdminBehaviours','adminAfterPostSave'));
$core->addBehavior('adminAfterPageUpdate',array('tweakurlsAdminBehaviours','adminAfterPostSave'));
$core->addBehavior('adminAfterPageCreate',array('tweakurlsAdminBehaviours','adminAfterPostSave'));
$core->addBehavior('adminAfterPostUpdate',array('tweakurlsAdminBehaviours','adminAfterPostSave'));
$core->addBehavior('adminAfterCategoryCreate',array('tweakurlsAdminBehaviours','adminAfterCategorySave'));
$core->addBehavior('adminAfterCategoryUpdate',array('tweakurlsAdminBehaviours','adminAfterCategorySave'));
$core->addBehavior('adminPostsActionsCombo',array('tweakurlsAdminBehaviours','adminPostsActionsCombo'));
$core->addBehavior('adminPagesActionsCombo',array('tweakurlsAdminBehaviours','adminPostsActionsCombo'));
$core->addBehavior('adminPostsActions',array('tweakurlsAdminBehaviours','adminPostsActions'));
$core->addBehavior('adminPostsActionsContent',array('tweakurlsAdminBehaviours','adminPostsActionsContent'));

class tweakurlsAdminBehaviours
{
	public static function tweakurls_combo()
	{
		return array(
			__('default mode') => 'default',
			__('clean all diacritics') => 'nodiacritic',
			__('Lowercase') => 'lowercase',
			__('Much more tidy') => 'mtidy'
		);
	}

	public static function adminBlogPreferencesForm($core,$settings)
	{
		$tweekurls_settings = tweakurlsSettings($GLOBALS['core']);

		# URL modes
		$tweakurls_combo = self::tweakurls_combo();
		echo
		'<fieldset><legend>Tweak URLs</legend>'.
		'<div>'.
		'<p><label>'.
		__('Posts URL type:')." ".
		form::combo('tweakurls_posturltransform',$tweakurls_combo,$tweekurls_settings->tweakurls_posturltransform).
		'</label></p>'.
		'<p><label>'.
		__('Categories URL type:')." ".
		form::combo('tweakurls_caturltransform',$tweakurls_combo,$tweekurls_settings->tweakurls_caturltransform).
		'</label></p>'.
		'</div>'.
		'</fieldset>';
	}
	public static function adminBeforeBlogSettingsUpdate($settings)
	{
		$tweekurls_settings = tweakurlsSettings($GLOBALS['core']);
		$tweekurls_settings->put('tweakurls_posturltransform',$_POST['tweakurls_posturltransform']);
		$tweekurls_settings->put('tweakurls_caturltransform',$_POST['tweakurls_caturltransform']);
	}

	public static function adminAfterPostSave ($cur,$id=null)
	{
		global $core;

		if (isset($_POST['post_url'])||empty($_REQUEST['id']))
		{
			$cur->post_url = tweakUrls::tweakBlogURL($cur->post_url);
			$core->blog->updPost($id,$cur);
		}
	}

	public static function adminAfterCategorySave ($cur,$id)
	{
		global $core;

		$tweekurls_settings = tweakurlsSettings($core);
		$caturltransform = $tweekurls_settings->tweakurls_caturltransform;

		if (isset($_POST['cat_url'])||empty($_REQUEST['id']))
		{
			// if it is a sub-category, change only last part of its url
			$urls = explode('/',$cur->cat_url);
			$cat_url = array_pop($urls);
			$urls[] = tweakUrls::tweakBlogURL($cat_url,$caturltransform);
			$urls = implode('/',$urls);

			$new_cur = $core->con->openCursor($core->prefix.'category');
			$new_cur->cat_url = $urls;
			$new_cur->update('WHERE cat_id = '.$id);

			// todo: check children urls
		}
	}

	public static function adminPostsActionsCombo($combo_action)
	{
		global $core;

		if ($core->auth->check('admin',$core->blog->id))
		{
			$combo_action[0][__('Change')][__('Clean URLs')] = 'cleanurls';
		}
	}

	public static function adminPostsActions($core,$posts,$action,$redir)
	{
		if ($action == 'confirmcleanurls' && $core->auth->check('admin',$core->blog->id)
		&& !empty($_POST['posturltransform']) && $_POST['posturltransform'] != 'default')
		{
			try
			{
				while ($posts->fetch())
				{
					$cur = $core->con->openCursor($core->prefix.'post');
					$cur->post_url = $posts->post_url;

					$cur->post_url = tweakUrls::tweakBlogURL($cur->post_url);

					if ($cur->post_url != $posts->post_url) {
						$cur->update('WHERE post_id = '.(integer) $posts->post_id);
					}
				}
				http::redirect($redir);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
	}

	public static function adminPostsActionsContent($core,$action,$hidden_fields)
	{
		if ($action == 'cleanurls')
		{
			echo
			'<form action="posts_actions.php" method="post">'.
			'<h2>Tweak URLs</h2>'.
			'<p>'.
			__('By changing the URLs, you understand that the old URLs will never be accessible.').'<br />'.
			__('Internal links between posts will not work either.').'<br />'.
			__('The changes are irreversible.').'</p>'.
			'<p><label>'.__('Posts URL type:').' '.
			form::combo('posturltransform',self::tweakurls_combo(),'default').
			'</label></p>'.
			'<p>'.
			$hidden_fields.
			$core->formNonce().
			form::hidden(array('action'),'confirmcleanurls').
			'<input type="submit" value="'.__('save').'" /></p>'.
			'</form>';
		}
	}
}
