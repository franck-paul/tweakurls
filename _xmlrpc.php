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
if (!defined('DC_RC_PATH')) {
    return;
}

// coreAfterCategoryCreate
dcCore::app()->addBehavior('coreAfterCategoryCreate', ['tweakurlsXmlrpcBehaviours', 'coreAfterCategorySave']);

// coreAfterPostCreate, coreAfterPostUpdate
dcCore::app()->addBehavior('coreAfterPostCreate', ['tweakurlsXmlrpcBehaviours', 'coreAfterPostSave']);
dcCore::app()->addBehavior('coreAfterPostUpdate', ['tweakurlsXmlrpcBehaviours', 'coreAfterPostSave']);

class tweakurlsXmlrpcBehaviours
{
    public static function coreAfterPostSave($cur)
    {
        if ($cur->post_id) {
            $cur->post_url = tweakUrls::tweakBlogURL($cur->post_url);
            dcCore::app()->blog->updPost($cur->post_id, $cur);
        }
    }

    public static function coreAfterCategorySave($cur)
    {
        if ($cur->cat_id) {
            $tweekurls_settings = tweakUrls::tweakurlsSettings();
            $caturltransform    = $tweekurls_settings->tweakurls_caturltransform;

            // if it is a sub-category, change only last part of its url
            $urls    = explode('/', $cur->cat_url);
            $cat_url = array_pop($urls);
            $urls[]  = tweakUrls::tweakBlogURL($cat_url, $caturltransform);
            $urls    = implode('/', $urls);

            $new_cur          = dcCore::app()->con->openCursor(dcCore::app()->prefix . 'category');
            $new_cur->cat_url = $urls;
            $new_cur->update('WHERE cat_id = ' . $cur->cat_id);
        }
    }
}
