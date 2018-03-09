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

if (!defined('DC_RC_PATH')) {return;}

global $__autoload;
$__autoload['tweakUrls'] = dirname(__FILE__) . '/inc/lib.tweakurls.php';

# Keep compatibility with Dotclear < 2.2
function tweakurlsSettings($core, $ns = 'tweakurls')
{
    if (version_compare(DC_VERSION, '2.2-alpha', '>=')) {
        $core->blog->settings->addNamespace($ns);
        return $core->blog->settings->{$ns};
    } else {
        $core->blog->settings->setNamespace($ns);
        return $core->blog->settings;
    }
}
