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

class Install extends dcNsProcess
{
    public static function init(): bool
    {
        $module = basename(dirname(__DIR__));
        $check  = dcCore::app()->newVersion($module, dcCore::app()->plugins->moduleInfo($module, 'version'));

        self::$init = defined('DC_CONTEXT_ADMIN') && $check;

        return self::$init;
    }

    public static function process(): bool
    {
        if (!self::$init) {
            return false;
        }

        $settings = Helper::tweakurlsSettings();
        $settings->put('tweakurls_posturltransform', '', 'string', 'determines posts URL type.', false, true);
        $settings->put('tweakurls_caturltransform', '', 'string', 'determines categories URL type.', false, true);
        $settings->put('tweakurls_mtidywildcard', '-', 'string', 'Wildcard for mtidy mode.', false, true);
        $settings->put('tweakurls_mtidyremove', "_ ':[]-", 'string', 'Last exotic chars to remove for mtidy mode.', false, true);

        return true;
    }
}
