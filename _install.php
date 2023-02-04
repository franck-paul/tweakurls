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
if (!defined('DC_CONTEXT_ADMIN')) {
    exit;
}

if (!dcCore::app()->newVersion(basename(__DIR__), dcCore::app()->plugins->moduleInfo(basename(__DIR__), 'version'))) {
    return;
}

$settings = tweakUrls::tweakurlsSettings();
$settings->put('tweakurls_posturltransform', '', 'string', 'determines posts URL type.', false, true);
$settings->put('tweakurls_caturltransform', '', 'string', 'determines categories URL type.', false, true);
$settings->put('tweakurls_mtidywildcard', '-', 'string', 'Wildcard for mtidy mode.', false, true);
$settings->put('tweakurls_mtidyremove', "_ ':[]-", 'string', 'Last exotic chars to remove for mtidy mode.', false, true);

$old_version = dcCore::app()->getVersion(basename(__DIR__));

return true;
