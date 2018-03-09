<?php
/**
 * @brief tweakurls, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author xave
 *
 * @copyright xave
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('DC_RC_PATH')) {return;}

// coreAfterCategoryCreate
$core->addBehavior('coreAfterCategoryCreate', array('tweakurlsXmlrpcBehaviours', 'coreAfterCategorySave'));

// coreAfterPostCreate, coreAfterPostUpdate
$core->addBehavior('coreAfterPostCreate', array('tweakurlsXmlrpcBehaviours', 'coreAfterPostSave'));
$core->addBehavior('coreAfterPostUpdate', array('tweakurlsXmlrpcBehaviours', 'coreAfterPostSave'));

class tweakurlsXmlrpcBehaviours
{
    public static function coreAfterPostSave($cur)
    {
        global $core;

        if ($cur->post_id) {
            $cur->post_url = tweakUrls::tweakBlogURL($cur->post_url);
            $core->blog->updPost($cur->post_id, $cur);
        }
    }

    public static function coreAfterCategorySave($cur)
    {
        global $core;

        if ($cur->cat_id) {
            $tweekurls_settings = tweakurlsSettings($core);
            $caturltransform    = $tweekurls_settings->tweakurls_caturltransform;

            // if it is a sub-category, change only last part of its url
            $urls    = explode('/', $cur->cat_url);
            $cat_url = array_pop($urls);
            $urls[]  = tweakUrls::tweakBlogURL($cat_url, $caturltransform);
            $urls    = implode('/', $urls);

            $new_cur          = $core->con->openCursor($core->prefix . 'category');
            $new_cur->cat_url = $urls;
            $new_cur->update('WHERE cat_id = ' . $cur->cat_id);
        }
    }
}
