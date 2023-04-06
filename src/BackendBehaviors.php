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
use dcBlog;
use dcCategories;
use dcCore;
use dcPage;
use dcPostsActions;
use Dotclear\Database\Cursor;
use Dotclear\Helper\Html\Form\Fieldset;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Hidden;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Legend;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Select;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Html;
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
        (new Fieldset('tweakurls'))
        ->legend((new Legend(__('Tweak URLs'))))
        ->fields([
            (new Para())->items([
                (new Select('tweakurls_posturltransform'))
                ->items($tweakurls_combo)
                ->default($tweekurls_settings->tweakurls_posturltransform)
                ->label((new Label(__('Posts URL type:'), Label::INSIDE_TEXT_BEFORE))),
            ]),
            (new Para())->items([
                (new Select('tweakurls_caturltransform'))
                ->items($tweakurls_combo)
                ->default($tweekurls_settings->tweakurls_caturltransform)
                ->label((new Label(__('Categories URL type:'), Label::INSIDE_TEXT_BEFORE))),
            ]),
        ])
        ->render();
    }

    /**
     * Store plugin settings (from blog parameters form)
     */
    public static function adminBeforeBlogSettingsUpdate()
    {
        $tweekurls_settings = Helper::tweakurlsSettings();
        $tweekurls_settings->put('posturltransform', $_POST['tweakurls_posturltransform']);
        $tweekurls_settings->put('caturltransform', $_POST['tweakurls_caturltransform']);
    }

    /**
     * Cope URLs tweak on entry save
     *
     * @param      dcBlog  $blog   The blog
     * @param      cursor  $cur    The cursor
     */
    public static function coreBeforePost(dcBlog $blog, Cursor $cur)
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
    public static function adminAfterCategorySave(Cursor $cur, int $id)
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

            // TODO: check children urls
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
            dcCore::app()->auth::PERMISSION_ADMIN,
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
            dcCore::app()->auth::PERMISSION_ADMIN,
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
            dcCore::app()->auth::PERMISSION_ADMIN,
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
                            Html::escapeHTML(dcCore::app()->blog->name) => '',
                            __('Pages')                                 => dcCore::app()->adminurl->get('admin.plugin.pages'),
                            __('Clean URLs')                            => '',
                        ]
                    )
                );
            } else {
                $ap->beginPage(
                    dcPage::breadcrumb(
                        [
                            Html::escapeHTML(dcCore::app()->blog->name) => '',
                            __('Entries')                               => dcCore::app()->adminurl->get('admin.post'),
                            __('Clean URLs')                            => '',
                        ]
                    )
                );
            }

            echo
            (new Form('ap-tweakurl'))
            ->fields([
                (new Para())->items([
                    (new Text())
                    ->value(__('By changing the URLs, you understand that the old URLs will never be accessible.') . '<br />' .
                        __('Internal links between posts will not work either.') . '<br />' .
                        __('The changes are irreversible.')),
                ]),
                (new Para())->items([
                    (new Select('posturltransform'))
                    ->items(self::tweakurls_combo())
                    ->default('default')
                    ->label((new Label(__('Posts URL type:'), Label::INSIDE_TEXT_BEFORE))),
                ]),
                (new Text(null, $ap->getCheckboxes())),
                (new Para())->items([
                    (new Submit('ap-tweakurl-do', __('Save'))),
                    dcCore::app()->formNonce(false),
                    ...$ap->hiddenFields(),
                    (new Hidden(['confirmcleanurls'], 'true')),
                    (new Hidden(['action'], 'cleanurls')),
                ]),
            ])
            ->render();

            $ap->endPage();
        }
    }
}
