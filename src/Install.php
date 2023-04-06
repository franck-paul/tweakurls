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
use Exception;

class Install extends dcNsProcess
{
    public static function init(): bool
    {
        $module = basename(dirname(__DIR__));
        $check  = dcCore::app()->newVersion($module, dcCore::app()->plugins->moduleInfo($module, 'version'));

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

            $module      = basename(dirname(__DIR__));
            $old_version = dcCore::app()->getVersion($module);

            if (version_compare((string) $old_version, '4.0', '<')) {
                // Change settings names (remove tweakurls_ prefix in them)
                if ($settings->settingExists('tweakurls_posturltransform', true)) {
                    $settings->rename('tweakurls_posturltransform', 'posturltransform');
                }
                if ($settings->settingExists('tweakurls_caturltransform', true)) {
                    $settings->rename('tweakurls_caturltransform', 'caturltransform');
                }
                if ($settings->settingExists('tweakurls_mtidywildcard', true)) {
                    $settings->rename('tweakurls_mtidywildcard', 'mtidywildcard');
                }
                if ($settings->settingExists('tweakurls_mtidyremove', true)) {
                    $settings->rename('tweakurls_mtidyremove', 'mtidyremove');
                }
            }

            // Global settings
            $settings->put('posturltransform', '', 'string', 'determines posts URL type.', false, true);
            $settings->put('caturltransform', '', 'string', 'determines categories URL type.', false, true);
            $settings->put('mtidywildcard', '-', 'string', 'Wildcard for mtidy mode.', false, true);
            $settings->put('mtidyremove', "_ ':[]-", 'string', 'Last exotic chars to remove for mtidy mode.', false, true);

            return true;
        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());
        }

        return true;
    }
}
