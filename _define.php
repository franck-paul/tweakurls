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

if (isset($this) && is_object($this) && method_exists($this, 'registerModule') && isset($this->id) && is_string($this->id)) {
    $this->registerModule(
        'tweakURLs',
        'Tweaks you posts URLs',
        'xave',
        '10.0',
        [
            'date'        => '2026-11-08T10:26:21+0100',
            'requires'    => [['core', '2.40']],
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
}
