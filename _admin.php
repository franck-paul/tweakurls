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
dcCore::app()->addBehavior('adminBlogPreferencesForm', ['tweakurlsAdminBehaviours', 'adminBlogPreferencesForm']);
dcCore::app()->addBehavior('adminBeforeBlogSettingsUpdate', ['tweakurlsAdminBehaviours', 'adminBeforeBlogSettingsUpdate']);

dcCore::app()->addBehavior('adminAfterPostCreate', ['tweakurlsAdminBehaviours', 'adminAfterPostSave']);
dcCore::app()->addBehavior('adminAfterPageUpdate', ['tweakurlsAdminBehaviours', 'adminAfterPostSave']);
dcCore::app()->addBehavior('adminAfterPageCreate', ['tweakurlsAdminBehaviours', 'adminAfterPostSave']);
dcCore::app()->addBehavior('adminAfterPostUpdate', ['tweakurlsAdminBehaviours', 'adminAfterPostSave']);
dcCore::app()->addBehavior('adminAfterCategoryCreate', ['tweakurlsAdminBehaviours', 'adminAfterCategorySave']);
dcCore::app()->addBehavior('adminAfterCategoryUpdate', ['tweakurlsAdminBehaviours', 'adminAfterCategorySave']);

dcCore::app()->addBehavior('adminPostsActionsPage', ['tweakurlsAdminBehaviours', 'adminPostsActionsPage']);
dcCore::app()->addBehavior('adminPagesActionsPage', ['tweakurlsAdminBehaviours', 'adminPagesActionsPage']);

class tweakurlsAdminBehaviours
{
    public static function tweakurls_combo()
    {
        return [
            __('default mode')         => 'default',
            __('clean all diacritics') => 'nodiacritic',
            __('Lowercase')            => 'lowercase',
            __('Much more tidy')       => 'mtidy',
        ];
    }

    public static function adminBlogPreferencesForm($core = null, $settings)
    {
        $tweekurls_settings = tweakurlsSettings(dcCore::app());

        # URL modes
        $tweakurls_combo = self::tweakurls_combo();
        echo
        '<div class="fieldset"><h4>Tweak URLs</h4>' .
        '<p><label for="tweakurls_posturltransform">' .
        __('Posts URL type:') . ' ' .
        form::combo('tweakurls_posturltransform', $tweakurls_combo, $tweekurls_settings->tweakurls_posturltransform) .
        '</label></p>' .
        '<p><label for="tweakurls_caturltransform">' .
        __('Categories URL type:') . ' ' .
        form::combo('tweakurls_caturltransform', $tweakurls_combo, $tweekurls_settings->tweakurls_caturltransform) .
            '</label></p>' .
            '</div>';
    }
    public static function adminBeforeBlogSettingsUpdate($settings)
    {
        $tweekurls_settings = tweakurlsSettings(dcCore::app());
        $tweekurls_settings->put('tweakurls_posturltransform', $_POST['tweakurls_posturltransform']);
        $tweekurls_settings->put('tweakurls_caturltransform', $_POST['tweakurls_caturltransform']);
    }

    public static function adminAfterPostSave($cur, $id = null)
    {
        if (isset($_POST['post_url']) || empty($_REQUEST['id'])) {
            $cur->post_url = tweakUrls::tweakBlogURL($cur->post_url);
            dcCore::app()->blog->updPost($id, $cur);
        }
    }

    public static function adminAfterCategorySave($cur, $id)
    {
        $tweekurls_settings = tweakurlsSettings(dcCore::app());
        $caturltransform    = $tweekurls_settings->tweakurls_caturltransform;

        if (isset($_POST['cat_url']) || empty($_REQUEST['id'])) {
            // if it is a sub-category, change only last part of its url
            $urls    = explode('/', $cur->cat_url);
            $cat_url = array_pop($urls);
            $urls[]  = tweakUrls::tweakBlogURL($cat_url, $caturltransform);
            $urls    = implode('/', $urls);

            $new_cur          = dcCore::app()->con->openCursor(dcCore::app()->prefix . 'category');
            $new_cur->cat_url = $urls;
            $new_cur->update('WHERE cat_id = ' . $id);

            // todo: check children urls
        }
    }

    public static function adminPostsActionsPage($core = null, $ap)
    {
        // Add menuitem in actions dropdown list
        if (dcCore::app()->auth->check('admin', dcCore::app()->blog->id)) {
            $ap->addAction(
                [__('Change') => [__('Clean URLs') => 'cleanurls']],
                ['tweakurlsAdminBehaviours', 'adminPostsDoReplacements']
            );
        }
    }

    public static function adminPagesActionsPage($core = null, $ap)
    {
        // Add menuitem in actions dropdown list
        if (dcCore::app()->auth->check('admin', dcCore::app()->blog->id)) {
            $ap->addAction(
                [__('Change') => [__('Clean URLs') => 'cleanurls']],
                ['tweakurlsAdminBehaviours', 'adminPagesDoReplacements']
            );
        }
    }

    public static function adminPostsDoReplacements($core = null, dcPostsActionsPage $ap, $post)
    {
        self::adminEntriesDoReplacements(dcCore::app(), $ap, $post, 'post');
    }

    public static function adminPagesDoReplacements($core = null, dcPostsActionsPage $ap, $post)
    {
        self::adminEntriesDoReplacements(dcCore::app(), $ap, $post, 'page');
    }

    public static function adminEntriesDoReplacements($core = null, dcPostsActionsPage $ap, $post, $type = 'post')
    {
        if (!empty($post['confirmcleanurls']) && dcCore::app()->auth->check('admin', dcCore::app()->blog->id) && !empty($post['posturltransform']) && $post['posturltransform'] != 'default') {
            // Do replacements
            $posts = $ap->getRS();
            if ($posts->rows()) {
                while ($posts->fetch()) {
                    $cur           = dcCore::app()->con->openCursor(dcCore::app()->prefix . 'post');
                    $cur->post_url = $posts->post_url;

                    $cur->post_url = tweakUrls::tweakBlogURL($cur->post_url);

                    if ($cur->post_url != $posts->post_url) {
                        $cur->update('WHERE post_id = ' . (int) $posts->post_id);
                    }
                }
                $ap->redirect(true, ['upd' => 1]);
            } else {
                $ap->redirect();
            }
        } else {
            // Ask confirmation for replacements
            if ($type == 'page') {
                $ap->beginPage(
                    dcPage::breadcrumb(
                        [
                            html::escapeHTML(dcCore::app()->blog->name) => '',
                            __('Pages')                                 => 'plugin.php?p=pages',
                            __('Clean URLs')                            => '',
                        ]
                    )
                );
            } else {
                $ap->beginPage(
                    dcPage::breadcrumb(
                        [
                            html::escapeHTML(dcCore::app()->blog->name) => '',
                            __('Entries')                               => 'posts.php',
                            __('Clean URLs')                            => '',
                        ]
                    )
                );
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

            dcCore::app()->formNonce() . $ap->getHiddenFields() .
            form::hidden(['confirmcleanurls'], 'true') .
            form::hidden(['action'], 'cleanurls') .
                '</form>';

            $ap->endPage();
        }
    }
}
