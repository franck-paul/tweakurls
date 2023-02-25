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
declare(strict_types=1);

namespace Dotclear\Plugin\tweakurls;

use ArrayObject;
use cursor;
use dcAuth;
use dcBlog;
use dcCategories;
use dcCore;
use dcPage;
use dcPostsActions;
use form;
use html;

use Dotclear\Plugin\pages\BackendActions as PagesBackendActions;

class BackendBehaviors
{
    /**
     * Compose list (combobox) of tweak URLs modes
     *
     * @return     array
     */
    private static function tweakurls_combo(): array
    {
        return [
            __('Default mode')         => 'default',
            __('Clean all diacritics') => 'nodiacritic',
            __('Lowercase')            => 'lowercase',
            __('Much more tidy')       => 'mtidy',
        ];
    }

    /**
     * Display plugin settings (in blog parameters form)
     */
    public static function adminBlogPreferencesForm()
    {
        $tweekurls_settings = Helper::tweakurlsSettings();

        // URL modes
        $tweakurls_combo = self::tweakurls_combo();

        echo
        '<div class="fieldset" id="tweakurls"><h4>Tweak URLs</h4>' .
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

    /**
     * Store plugin settings (from blog parameters form)
     */
    public static function adminBeforeBlogSettingsUpdate()
    {
        $tweekurls_settings = Helper::tweakurlsSettings();
        $tweekurls_settings->put('tweakurls_posturltransform', $_POST['tweakurls_posturltransform']);
        $tweekurls_settings->put('tweakurls_caturltransform', $_POST['tweakurls_caturltransform']);
    }

    /**
     * Cope URLs tweak on entry save
     *
     * @param      dcBlog  $blog   The blog
     * @param      cursor  $cur    The cursor
     */
    public static function coreBeforePost(dcBlog $blog, cursor $cur)
    {
        if ($cur->post_url) {
            $cur->post_url = Helper::tweakBlogURL($cur->post_url);
        }
    }

    /**
     * Cope URLs tweak on category save
     *
     * @param      cursor  $cur    The cursor
     * @param      int     $id     The category identifier
     */
    public static function adminAfterCategorySave(cursor $cur, int $id)
    {
        $tweekurls_settings = Helper::tweakurlsSettings();
        $caturltransform    = $tweekurls_settings->tweakurls_caturltransform;

        if (isset($_POST['cat_url']) || empty($_REQUEST['id'])) {
            // if it is a sub-category, change only last part of its url
            $urls    = explode('/', $cur->cat_url);
            $cat_url = array_pop($urls);
            $urls[]  = Helper::tweakBlogURL($cat_url, $caturltransform);
            $urls    = implode('/', $urls);

            $new_cur          = dcCore::app()->con->openCursor(dcCore::app()->prefix . dcCategories::CATEGORY_TABLE_NAME);
            $new_cur->cat_url = $urls;
            $new_cur->update('WHERE cat_id = ' . $id);

            // todo: check children urls
        }
    }

    /**
     * Add posts action
     *
     * @param      dcPostsActions  $ap
     */
    public static function adminPostsActions(dcPostsActions $ap)
    {
        // Add menuitem in actions dropdown list
        if (dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_ADMIN,
        ]), dcCore::app()->blog->id)) {
            $ap->addAction(
                [__('Change') => [__('Clean URLs') => 'cleanurls']],
                [self::class, 'adminPostsDoReplacements']
            );
        }
    }

    /**
     * Add pages action
     *
     * @param      PagesBackendActions  $ap     { parameter_description }
     */
    public static function adminPagesActions(PagesBackendActions $ap)
    {
        // Add menuitem in actions dropdown list
        if (dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_ADMIN,
        ]), dcCore::app()->blog->id)) {
            $ap->addAction(
                [__('Change') => [__('Clean URLs') => 'cleanurls']],
                [self::class, 'adminPagesDoReplacements']
            );
        }
    }

    /**
     * Cope with posts action
     *
     * @param      dcPostsActions  $ap
     * @param      arrayObject     $post   Form POST
     */
    public static function adminPostsDoReplacements(dcPostsActions $ap, arrayObject $post)
    {
        self::adminEntriesDoReplacements($ap, $post, 'post');
    }

    /**
     * Cope with pages actions
     *
     * @param      PagesBackendActions  $ap
     * @param      arrayObject          $post   Form POST
     */
    public static function adminPagesDoReplacements(PagesBackendActions $ap, arrayObject $post)
    {
        self::adminEntriesDoReplacements($ap, $post, 'page');
    }

    /**
     * Cope with posts/pages action
     *
     * @param      dcPostsActions|PagesBackendActions       $ap
     * @param      arrayObject                              $post   The form POST
     * @param      string                                   $type   The entries type
     */
    private static function adminEntriesDoReplacements($ap, arrayObject $post, $type = 'post')
    {
        if (!empty($post['confirmcleanurls']) && dcCore::app()->auth->check(dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_ADMIN,
        ]), dcCore::app()->blog->id) && !empty($post['posturltransform']) && $post['posturltransform'] != 'default') {
            // Do replacements
            $posts = $ap->getRS();
            if ($posts->rows()) {
                while ($posts->fetch()) {
                    $cur           = dcCore::app()->con->openCursor(dcCore::app()->prefix . dcBlog::POST_TABLE_NAME);
                    $cur->post_url = $posts->post_url;

                    $cur->post_url = Helper::tweakBlogURL($cur->post_url);

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
                            __('Pages')                                 => dcCore::app()->adminurl->get('admin.plugin.pages'),
                            __('Clean URLs')                            => '',
                        ]
                    )
                );
            } else {
                $ap->beginPage(
                    dcPage::breadcrumb(
                        [
                            html::escapeHTML(dcCore::app()->blog->name) => '',
                            __('Entries')                               => dcCore::app()->adminurl->get('admin.post'),
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

            '<p><input type="submit" value="' . __('Save') . '" /></p>' .

            dcCore::app()->formNonce() . $ap->getHiddenFields() .
            form::hidden(['confirmcleanurls'], 'true') .
            form::hidden(['action'], 'cleanurls') .
            '</form>';

            $ap->endPage();
        }
    }
}
