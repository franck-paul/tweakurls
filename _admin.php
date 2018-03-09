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

$core->addBehavior('adminBlogPreferencesForm', array('tweakurlsAdminBehaviours', 'adminBlogPreferencesForm'));
$core->addBehavior('adminBeforeBlogSettingsUpdate', array('tweakurlsAdminBehaviours', 'adminBeforeBlogSettingsUpdate'));

$core->addBehavior('adminAfterPostCreate', array('tweakurlsAdminBehaviours', 'adminAfterPostSave'));
$core->addBehavior('adminAfterPageUpdate', array('tweakurlsAdminBehaviours', 'adminAfterPostSave'));
$core->addBehavior('adminAfterPageCreate', array('tweakurlsAdminBehaviours', 'adminAfterPostSave'));
$core->addBehavior('adminAfterPostUpdate', array('tweakurlsAdminBehaviours', 'adminAfterPostSave'));
$core->addBehavior('adminAfterCategoryCreate', array('tweakurlsAdminBehaviours', 'adminAfterCategorySave'));
$core->addBehavior('adminAfterCategoryUpdate', array('tweakurlsAdminBehaviours', 'adminAfterCategorySave'));

$core->addBehavior('adminPostsActionsPage', array('tweakurlsAdminBehaviours', 'adminPostsActionsPage'));
$core->addBehavior('adminPagesActionsPage', array('tweakurlsAdminBehaviours', 'adminPagesActionsPage'));

class tweakurlsAdminBehaviours
{
    public static function tweakurls_combo()
    {
        return array(
            __('default mode')         => 'default',
            __('clean all diacritics') => 'nodiacritic',
            __('Lowercase')            => 'lowercase',
            __('Much more tidy')       => 'mtidy'
        );
    }

    public static function adminBlogPreferencesForm($core, $settings)
    {
        $tweekurls_settings = tweakurlsSettings($GLOBALS['core']);

        # URL modes
        $tweakurls_combo = self::tweakurls_combo();
        echo
        '<div class="fieldset"><h4>Tweak URLs</h4>' .
        '<p><label for="tweakurls_posturltransform">' .
        __('Posts URL type:') . " " .
        form::combo('tweakurls_posturltransform', $tweakurls_combo, $tweekurls_settings->tweakurls_posturltransform) .
        '</label></p>' .
        '<p><label for="tweakurls_caturltransform">' .
        __('Categories URL type:') . " " .
        form::combo('tweakurls_caturltransform', $tweakurls_combo, $tweekurls_settings->tweakurls_caturltransform) .
            '</label></p>' .
            '</div>';
    }
    public static function adminBeforeBlogSettingsUpdate($settings)
    {
        $tweekurls_settings = tweakurlsSettings($GLOBALS['core']);
        $tweekurls_settings->put('tweakurls_posturltransform', $_POST['tweakurls_posturltransform']);
        $tweekurls_settings->put('tweakurls_caturltransform', $_POST['tweakurls_caturltransform']);
    }

    public static function adminAfterPostSave($cur, $id = null)
    {
        global $core;

        if (isset($_POST['post_url']) || empty($_REQUEST['id'])) {
            $cur->post_url = tweakUrls::tweakBlogURL($cur->post_url);
            $core->blog->updPost($id, $cur);
        }
    }

    public static function adminAfterCategorySave($cur, $id)
    {
        global $core;

        $tweekurls_settings = tweakurlsSettings($core);
        $caturltransform    = $tweekurls_settings->tweakurls_caturltransform;

        if (isset($_POST['cat_url']) || empty($_REQUEST['id'])) {
            // if it is a sub-category, change only last part of its url
            $urls    = explode('/', $cur->cat_url);
            $cat_url = array_pop($urls);
            $urls[]  = tweakUrls::tweakBlogURL($cat_url, $caturltransform);
            $urls    = implode('/', $urls);

            $new_cur          = $core->con->openCursor($core->prefix . 'category');
            $new_cur->cat_url = $urls;
            $new_cur->update('WHERE cat_id = ' . $id);

            // todo: check children urls
        }
    }

    public static function adminPostsActionsPage($core, $ap)
    {
        // Add menuitem in actions dropdown list
        if ($core->auth->check('admin', $core->blog->id)) {
            $ap->addAction(
                array(__('Change') => array(__('Clean URLs') => 'cleanurls')),
                array('tweakurlsAdminBehaviours', 'adminPostsDoReplacements')
            );
        }
    }

    public static function adminPagesActionsPage($core, $ap)
    {
        // Add menuitem in actions dropdown list
        if ($core->auth->check('admin', $core->blog->id)) {
            $ap->addAction(
                array(__('Change') => array(__('Clean URLs') => 'cleanurls')),
                array('tweakurlsAdminBehaviours', 'adminPagesDoReplacements')
            );
        }
    }

    public static function adminPostsDoReplacements($core, dcPostsActionsPage $ap, $post)
    {
        self::adminEntriesDoReplacements($core, $ap, $post, 'post');
    }

    public static function adminPagesDoReplacements($core, dcPostsActionsPage $ap, $post)
    {
        self::adminEntriesDoReplacements($core, $ap, $post, 'page');
    }

    public static function adminEntriesDoReplacements($core, dcPostsActionsPage $ap, $post, $type = 'post')
    {
        if (!empty($post['confirmcleanurls']) && $core->auth->check('admin', $core->blog->id) &&
            !empty($post['posturltransform']) && $post['posturltransform'] != 'default') {
            // Do replacements
            $posts = $ap->getRS();
            if ($posts->rows()) {
                while ($posts->fetch()) {
                    $cur           = $core->con->openCursor($core->prefix . 'post');
                    $cur->post_url = $posts->post_url;

                    $cur->post_url = tweakUrls::tweakBlogURL($cur->post_url);

                    if ($cur->post_url != $posts->post_url) {
                        $cur->update('WHERE post_id = ' . (integer) $posts->post_id);
                    }
                }
                $ap->redirect(true, array('upd' => 1));
            } else {
                $ap->redirect();
            }
        } else {
            // Ask confirmation for replacements
            if ($type == 'page') {
                $ap->beginPage(
                    dcPage::breadcrumb(
                        array(
                            html::escapeHTML($core->blog->name) => '',
                            __('Pages')                         => 'plugin.php?p=pages',
                            __('Clean URLs')                    => ''
                        )));
            } else {
                $ap->beginPage(
                    dcPage::breadcrumb(
                        array(
                            html::escapeHTML($core->blog->name) => '',
                            __('Entries')                       => 'posts.php',
                            __('Clean URLs')                    => ''
                        )));
            }

            echo
            '<form action="' . $ap->getURI() . '" method="post">' .

            '<p>' .
            __('By changing the URLs, you understand that the old URLs will never be accessible.') . '<br />' .
            __('Internal links between posts will not work either.') . '<br />' .
            __('The changes are irreversible.') . '</p>' .

            '<p><label>' . __('Posts URL type:') . ' ' .
            form::combo('posturltransform', self::tweakurls_combo(), 'default') .
            '</label></p>' .

            $ap->getCheckboxes() .

            '<p><input type="submit" value="' . __('save') . '" /></p>' .

            $core->formNonce() . $ap->getHiddenFields() .
            form::hidden(array('confirmcleanurls'), 'true') .
            form::hidden(array('action'), 'cleanurls') .
                '</form>';

            $ap->endPage();
        }
    }
}
