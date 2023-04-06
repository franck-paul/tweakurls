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
$this->registerModule(
    'tweakURLs',
    'Tweaks you posts URLs',
    'xave',
    '4.0',
    [
        'requires'    => [['core', '2.26']],
        'permissions' => dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_USAGE,
            dcAuth::PERMISSION_ADMIN,
        ]),
        'type' => 'plugin',

        'settings' => [
            'blog' => '#params.tweakurls',
        ],

        'details'    => 'https://open-time.net/?q=tweakurls',
        'support'    => 'https://github.com/franck-paul/tweakurls',
        'repository' => 'https://raw.githubusercontent.com/franck-paul/tweakurls/master/dcstore.xml',
    ]
);
