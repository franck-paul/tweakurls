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
if (!defined('DC_RC_PATH')) {
    return;
}

global $__autoload;
$__autoload['tweakUrls'] = __DIR__ . '/inc/lib.tweakurls.php';

function tweakurlsSettings($core, $ns = 'tweakurls')
{
    dcCore::app()->blog->settings->addNamespace($ns);

    return dcCore::app()->blog->settings->{$ns};
}
