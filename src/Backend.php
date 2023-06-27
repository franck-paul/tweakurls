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

use dcCore;
use dcNsProcess;

class Backend extends dcNsProcess
{
    protected static $init = false; /** @deprecated since 2.27 */
    public static function init(): bool
    {
        static::$init = My::checkContext(My::BACKEND);

        // dead but useful code, in order to have translations
        __('tweakURLs') . __('Tweaks you posts URLs');

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        dcCore::app()->addBehaviors([
            'adminBlogPreferencesFormV2'    => [BackendBehaviors::class, 'adminBlogPreferencesForm'],
            'adminBeforeBlogSettingsUpdate' => [BackendBehaviors::class, 'adminBeforeBlogSettingsUpdate'],

            'coreBeforePostCreate' => [BackendBehaviors::class, 'coreBeforePost'],
            'coreBeforePostUpdate' => [BackendBehaviors::class, 'coreBeforePost'],

            'adminAfterCategoryCreate' => [BackendBehaviors::class, 'adminAfterCategorySave'],
            'adminAfterCategoryUpdate' => [BackendBehaviors::class, 'adminAfterCategorySave'],

            'adminPostsActions' => [BackendBehaviors::class, 'adminPostsActions'],
            'adminPagesActions' => [BackendBehaviors::class, 'adminPagesActions'],
        ]);

        return true;
    }
}
