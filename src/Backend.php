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
use Dotclear\Core\Process;

class Backend extends Process
{
    public static function init(): bool
    {
        // dead but useful code, in order to have translations
        __('tweakURLs') . __('Tweaks you posts URLs');

        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        dcCore::app()->addBehaviors([
            'adminBlogPreferencesFormV2'    => BackendBehaviors::adminBlogPreferencesForm(...),
            'adminBeforeBlogSettingsUpdate' => BackendBehaviors::adminBeforeBlogSettingsUpdate(...),

            'coreBeforePostCreate' => BackendBehaviors::coreBeforePost(...),
            'coreBeforePostUpdate' => BackendBehaviors::coreBeforePost(...),

            'adminAfterCategoryCreate' => BackendBehaviors::adminAfterCategorySave(...),
            'adminAfterCategoryUpdate' => BackendBehaviors::adminAfterCategorySave(...),

            'adminPostsActions' => BackendBehaviors::adminPostsActions(...),
            'adminPagesActions' => BackendBehaviors::adminPagesActions(...),
        ]);

        return true;
    }
}
