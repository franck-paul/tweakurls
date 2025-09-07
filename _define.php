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
    '8.0',
    [
        'date'        => '2025-09-07T15:43:23+0200',
        'requires'    => [['core', '2.36']],
        'permissions' => 'My',
        'type'        => 'plugin',

        'settings' => [
            'blog' => '#params.tweakurls',
        ],

        'details'    => 'https://open-time.net/?q=tweakurls',
        'support'    => 'https://github.com/franck-paul/tweakurls',
        'repository' => 'https://raw.githubusercontent.com/franck-paul/tweakurls/main/dcstore.xml',
        'license'    => 'gpl2',
    ]
);
