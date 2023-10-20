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

use Dotclear\App;
use Dotclear\Core\Process;
use Dotclear\Interface\Core\BlogWorkspaceInterface;
use Exception;

class Install extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::INSTALL));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        try {
            $settings = My::settings();

            $old_version = App::version()->getVersion(My::id());

            if (version_compare((string) $old_version, '4.0', '<')) {
                // Change settings names (remove tweakurls_ prefix in them)

                $rename = function (string $name, BlogWorkspaceInterface $settings): void {
                    if ($settings->settingExists('tweakurls_' . $name, true)) {
                        $settings->rename('tweakurls_' . $name, $name);
                    }
                };

                $rename('posturltransform', $settings);
                $rename('caturltransform', $settings);
                $rename('mtidywildcard', $settings);
                $rename('mtidyremove', $settings);
            }

            // Global settings
            $settings->put('posturltransform', '', 'string', 'determines posts URL type.', false, true);
            $settings->put('caturltransform', '', 'string', 'determines categories URL type.', false, true);
            $settings->put('mtidywildcard', '-', 'string', 'Wildcard for mtidy mode.', false, true);
            $settings->put('mtidyremove', "_ ':[]-", 'string', 'Last exotic chars to remove for mtidy mode.', false, true);
        } catch (Exception $e) {
            App::error()->add($e->getMessage());
        }

        return true;
    }
}
