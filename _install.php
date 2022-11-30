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

$this_version      = dcCore::app()->plugins->moduleInfo('tweakurls', 'version');
$installed_version = dcCore::app()->getVersion('tweakurls');

if (version_compare((string) $installed_version, $this_version, '>=')) {
    return;
}

$settings = tweakUrls::tweakurlsSettings();
$settings->put('tweakurls_posturltransform', '', 'string', 'determines posts URL type.', false, true);
$settings->put('tweakurls_caturltransform', '', 'string', 'determines categories URL type.', false, true);
$settings->put('tweakurls_mtidywildcard', '-', 'string', 'Wildcard for mtidy mode.', false, true);
$settings->put('tweakurls_mtidyremove', "_ ':[]-", 'string', 'Last exotic chars to remove for mtidy mode.', false, true);

if (version_compare((string) $installed_version, '1.3', '<')) {
    try {
        // Some cleanup is needed
        @unlink(__DIR__ . DIRECTORY_SEPARATOR . '_xmlrpc.php');
    } catch (Exception $e) {
        dcCore::app()->error->add($e->getMessage());
    }
}

dcCore::app()->setVersion('tweakurls', $this_version);

return true;
