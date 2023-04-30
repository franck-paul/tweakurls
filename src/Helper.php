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
use dcNamespace;
use Dotclear\Helper\Text;

class Helper extends Text
{
    /**
     * Current settings
     *
     * @var        array
     */
    protected static $tweakurls_settings = [];

    /**
     * Get registered settings
     *
     * @return     dcNamespace|null
     */
    public static function tweakurlsSettings(): ?dcNamespace
    {
        return dcCore::app()->blog->settings->tweakurls;
    }

    /**
     * String to URL
     *
     * Transforms a string to a proper URL (keep slashes).
     *
     * @param string    $str            String to transform
     *
     * @return string
     */
    public static function nodiacriticURL(string $str): string
    {
        return self::str2URL($str);
    }

    /**
     * String to lowercase URL
     *
     * Transforms a string to a proper lowercase URL (keep slashes).
     *
     * @param string    $str            String to transform
     *
     * @return string
     */
    public static function lowercaseURL(string $str): string
    {
        return strtolower(self::nodiacriticURL($str));
    }

    /**
     * Custom URL cleanup
     *
     * Returns lowercase alphanumeric string,
     * with last exotic chars $search replaced by $replace.
     *
     * @param string    $str        String to clean
     * @param string    $search     Last exotic chars to replace
     * @param string    $replace    Char to use for replacement
     *
     * @return string
     */
    public static function neatURL(string $str, string $search = "_ ':[]-", string $replace = '-'): string
    {
        $quoted_search  = preg_quote($search);
        $quoted_replace = preg_quote($replace);

        // Tidy lowercase
        $str = self::lowercaseURL($str);

        // Replace last exotic $search chars by $replace
        $str = preg_replace('/[' . $quoted_search . ']/', $replace, $str);

        // Remove double $replace
        $str = preg_replace('/([' . $quoted_replace . ']{2,})/', $replace, $str);

        // Remove end $replace
        return rtrim((string) $str, $replace);
    }

    /**
     * Tweak URL according to a blog's settings
     *
     * Returns tweak URL.
     *
     * @param string    $str        String to clean
     * @param string    $format     Force predefine format
     * @param string    $search     Force last exotic chars to replace
     * @param string    $replace    Force char to use for replacement
     *
     * @return string
     */
    public static function tweakBlogURL(string $str, ?string $format = null, ?string $search = null, ?string $replace = null): string
    {
        # Read blog settings
        if (empty(self::$tweakurls_settings)) {
            $s = self::tweakurlsSettings();

            $s_format = (string) $s->tweakurls_posturltransform;
            if (empty($s_format)) {
                $s_format = 'default';
            }

            $s_search = (string) $s->tweakurls_mtidyremove;
            if (empty($s_search)) {
                $s_search = "_ ':[]-";
            }

            $s_replace = (string) $s->tweakurls_mtidywildcard;
            if (empty($s_replace)) {
                $s_replace = '-';
            }

            self::$tweakurls_settings = [
                'format'  => $s_format,
                'search'  => $s_search,
                'replace' => $s_replace,
            ];
        }
        $tweakurls_settings = self::$tweakurls_settings;

        # Read class settings
        if (!$format) {
            $format = $tweakurls_settings['format'];
        }
        if (!$search) {
            $search = $tweakurls_settings['search'];
        }
        if (!$replace) {
            $replace = $tweakurls_settings['replace'];
        }

        # Clean URL
        switch ($format) {
            case 'nodiacritic':
                $str = self::nodiacriticURL($str);

                break;
            case 'lowercase':
                $str = self::lowercaseURL($str);

                break;
            case 'mtidy':
            case 'neat':
                $str = self::neatURL($str, $search, $replace);

                break;

            default:
                break;
        }

        return $str;
    }
}
