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
use Dotclear\App;
use Dotclear\Core\Backend\Action\ActionsPosts;
use Dotclear\Core\Backend\Page;
use Dotclear\Core\Categories;
use Dotclear\Database\Cursor;
use Dotclear\Helper\Html\Form\Caption;
use Dotclear\Helper\Html\Form\Fieldset;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Hidden;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Legend;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Select;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Helper\Html\Html;
use Dotclear\Interface\Core\BlogInterface;
use Dotclear\Plugin\pages\BackendActions as PagesBackendActions;
use stdClass;

class BackendBehaviors
{
    /**
     * Compose list (combobox) of tweak URLs modes
     *
     * @return     array<string, string>
     */
    private static function combo(): array
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
    public static function adminBlogPreferencesForm(): string
    {
        $settings = My::settings();

        // URL modes
        $combo = self::combo();

        $sample = '2023/08/13/Twenty-Ye@rs-old_=O=_!';

        echo
        (new Fieldset('tweakurls'))
        ->legend((new Legend(__('Tweak URLs'))))
        ->fields([
            (new Para())->items([
                (new Select('tweakurls_posturltransform'))
                ->items($combo)
                ->default($settings->posturltransform)
                ->label((new Label(__('Posts URL type:'), Label::INSIDE_TEXT_BEFORE))),
            ]),
            (new Para())->items([
                (new Select('tweakurls_caturltransform'))
                ->items($combo)
                ->default($settings->caturltransform)
                ->label((new Label(__('Categories URL type:'), Label::INSIDE_TEXT_BEFORE))),
            ]),
            (new Para())->items([
                (new Text(null, '<hr>')),
                (new Table())
                    ->caption(new Caption(__('Examples with following URL:') . ' <code>' . $sample . '</code>'))
                    ->thead((new Thead())
                        ->items([
                            (new Tr())
                                ->items([
                                    (new Th())
                                        ->text(__('Mode')),
                                    (new Th())
                                        ->text(__('Result')),
                                ]),
                        ]))
                    ->tbody((new Tbody())
                        ->items([
                            (new Tr())
                                ->items([
                                    (new Td())
                                        ->text(__('Default mode')),
                                    (new Td())
                                        ->text('<code>' . Helper::tweakBlogURL($sample, 'default') . '</code>'),
                                ]),
                            (new Tr())
                                ->items([
                                    (new Td())
                                        ->text(__('Clean all diacritics')),
                                    (new Td())
                                        ->text('<code>' . Helper::tweakBlogURL($sample, 'nodiacritic') . '</code>'),
                                ]),
                            (new Tr())
                                ->items([
                                    (new Td())
                                        ->text(__('Lowercase')),
                                    (new Td())
                                        ->text('<code>' . Helper::tweakBlogURL($sample, 'lowercase') . '</code>'),
                                ]),
                            (new Tr())
                                ->items([
                                    (new Td())
                                        ->text(__('Much more tidy')),
                                    (new Td())
                                        ->text('<code>' . Helper::tweakBlogURL($sample, 'mtidy') . '</code>'),
                                ]),
                        ])),
            ]),
        ])
        ->render();

        return '';
    }

    /**
     * Store plugin settings (from blog parameters form)
     */
    public static function adminBeforeBlogSettingsUpdate(): string
    {
        $settings = My::settings();

        $settings->put('posturltransform', $_POST['tweakurls_posturltransform']);
        $settings->put('caturltransform', $_POST['tweakurls_caturltransform']);

        return '';
    }

    /**
     * Cope URLs tweak on entry save
     *
     * @param      BlogInterface    $blog   The blog
     * @param      cursor           $cur    The cursor
     */
    public static function coreBeforePost(BlogInterface $blog, Cursor $cur): string
    {
        if ($cur->post_url) {
            $cur->post_url = Helper::tweakBlogURL((string) $cur->post_url);
        }

        return '';
    }

    /**
     * Cope URLs tweak on getting post URL
     *
     * @param      stdClass           $obj    The object containing URL
     */
    public static function coreGetPostURL(stdClass $obj): string
    {
        if ($obj->url) {
            $obj->url = Helper::tweakBlogURL($obj->url);
        }

        return '';
    }

    /**
     * Cope URLs tweak on category save
     *
     * @param      cursor  $cur    The cursor
     * @param      int     $id     The category identifier
     */
    public static function adminAfterCategorySave(Cursor $cur, int $id): string
    {
        $settings        = My::settings();
        $caturltransform = $settings->caturltransform;

        if (isset($_POST['cat_url']) || empty($_REQUEST['id'])) {
            // if it is a sub-category, change only last part of its url
            $urls    = explode('/', $cur->cat_url);
            $cat_url = array_pop($urls);
            $urls[]  = Helper::tweakBlogURL($cat_url, $caturltransform);
            $urls    = implode('/', $urls);

            $new_cur          = App::db()->con()->openCursor(App::db()->con()->prefix() . Categories::CATEGORY_TABLE_NAME);
            $new_cur->cat_url = $urls;
            $new_cur->update('WHERE cat_id = ' . $id);

            // TODO: check children urls
        }

        return '';
    }

    /**
     * Add posts action
     */
    public static function adminPostsActions(ActionsPosts $ap): string
    {
        // Add menuitem in actions dropdown list
        if (App::auth()->check(App::auth()->makePermissions([
            App::auth()::PERMISSION_ADMIN,
        ]), App::blog()->id())) {
            $ap->addAction(
                [__('Change') => [__('Clean URLs') => 'cleanurls']],
                self::adminPostsDoReplacements(...)
            );
        }

        return '';
    }

    /**
     * Add pages action
     */
    public static function adminPagesActions(PagesBackendActions $ap): string
    {
        // Add menuitem in actions dropdown list
        if (App::auth()->check(App::auth()->makePermissions([
            App::auth()::PERMISSION_ADMIN,
        ]), App::blog()->id())) {
            $ap->addAction(
                [__('Change') => [__('Clean URLs') => 'cleanurls']],
                self::adminPagesDoReplacements(...)
            );
        }

        return '';
    }

    /**
     * Cope with posts action
     *
     * @param      ArrayObject<string, mixed>   $post   Form POST
     */
    public static function adminPostsDoReplacements(ActionsPosts $ap, arrayObject $post): void
    {
        self::adminEntriesDoReplacements($ap, $post, 'post');
    }

    /**
     * Cope with pages actions
     *
     * @param      ArrayObject<string, mixed>   $post   Form POST
     */
    public static function adminPagesDoReplacements(PagesBackendActions $ap, arrayObject $post): void
    {
        self::adminEntriesDoReplacements($ap, $post, 'page');
    }

    /**
     * Cope with posts/pages action
     *
     * @param      ArrayObject<string, mixed>           $post   The form POST
     * @param      string                               $type   The entries type
     */
    private static function adminEntriesDoReplacements(ActionsPosts|PagesBackendActions $ap, arrayObject $post, string $type = 'post'): void
    {
        if (!empty($post['confirmcleanurls']) && App::auth()->check(App::auth()->makePermissions([
            App::auth()::PERMISSION_ADMIN,
        ]), App::blog()->id()) && !empty($post['posturltransform']) && $post['posturltransform'] != 'default') {
            // Do replacements
            $posts = $ap->getRS();
            if ($posts->rows()) {
                while ($posts->fetch()) {
                    $cur           = App::db()->con()->openCursor(App::db()->con()->prefix() . App::blog()::POST_TABLE_NAME);
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
            if ($type === 'page') {
                $ap->beginPage(
                    Page::breadcrumb(
                        [
                            Html::escapeHTML(App::blog()->name()) => '',
                            __('Pages')                           => App::backend()->url()->get('admin.plugin.pages'),
                            __('Clean URLs')                      => '',
                        ]
                    )
                );
            } else {
                $ap->beginPage(
                    Page::breadcrumb(
                        [
                            Html::escapeHTML(App::blog()->name()) => '',
                            __('Entries')                         => App::backend()->url()->get('admin.post'),
                            __('Clean URLs')                      => '',
                        ]
                    )
                );
            }

            echo
            (new Form('ap-tweakurl'))
            ->action($ap->getURI())
            ->method('post')
            ->fields([
                (new Para())->items([
                    (new Text())
                    ->value(__('By changing the URLs, you understand that the old URLs will never be accessible.') . '<br>' .
                        __('Internal links between posts will not work either.') . '<br>' .
                        __('The changes are irreversible.')),
                ]),
                (new Para())->items([
                    (new Select('posturltransform'))
                    ->items(self::combo())
                    ->default('default')
                    ->label((new Label(__('Posts URL type:'), Label::INSIDE_TEXT_BEFORE))),
                ]),
                (new Text(null, $ap->getCheckboxes())),
                (new Para())->items([
                    (new Submit('ap-tweakurl-do', __('Save'))),
                    ...$ap->hiddenFields(),
                    (new Hidden(['confirmcleanurls'], 'true')),
                    (new Hidden(['action'], 'cleanurls')),
                    (new Hidden(['process'], ($type === 'post' ? 'Posts' : 'Plugin'))),
                    App::nonce()->formNonce(),
                ]),
            ])
            ->render();

            $ap->endPage();
        }
    }
}
