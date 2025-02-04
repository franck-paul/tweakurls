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
    '6.3.1',
    [
        'date'        => '2003-08-13T13:42:00+0100',
        'requires'    => [['core', '2.32']],
        'permissions' => 'My',
        'type'        => 'plugin',

        'settings' => [
            'blog' => '#params.tweakurls',
        ],

        'details'    => 'https://open-time.net/?q=tweakurls',
        'support'    => 'https://github.com/franck-paul/tweakurls',
        'repository' => 'https://raw.githubusercontent.com/franck-paul/tweakurls/main/dcstore.xml',
    ]
);
