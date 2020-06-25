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

$this->registerModule(
    "tweakURLs",             // Name
    "Tweaks you posts URLs", // Description
    "xave",                  // Author
    '1.1',                   // Version
    [
        'requires'    => [['core', '2.13']],                         // Dependencies
        'permissions' => 'usage,admin',                              // Permissions
        'support'     => 'https://github.com/franck-paul/tweakurls', // Support URL
        'type'        => 'plugin'                                   // Type
    ]
);
