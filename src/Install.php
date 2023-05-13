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
use dcNamespace;
use dcNsProcess;
use Exception;

class Install extends dcNsProcess
{
    public static function init(): bool
    {
        $check = My::checkContext(My::INSTALL);

        static::$init = defined('DC_CONTEXT_ADMIN') && $check;

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        try {
            $settings = Helper::tweakurlsSettings();

            $old_version = dcCore::app()->getVersion(My::id());

            if (version_compare((string) $old_version, '4.0', '<')) {
                // Change settings names (remove tweakurls_ prefix in them)

                $rename = function (string $name, dcNamespace $settings): void {
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
            dcCore::app()->error->add($e->getMessage());
        }

        return true;
    }
}
