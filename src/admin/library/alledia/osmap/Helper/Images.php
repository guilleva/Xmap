<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap\Helper;

defined('_JEXEC') or die();


class Images
{
    /**
     * Extracts images from the given text.
     *
     * @param string $text
     * @param int    $max
     *
     * @return array
     */
    public function getImagesFromText($text, $max = 9999)
    {
        if (!isset($urlBase)) {
            $urlBase    = \JURI::base();
            $urlBaseLen = strlen($urlBase);
        }

        $images  = null;
        $matches = $matches1 = $matches2 = array();

        // Look <img> tags
        preg_match_all('/<img[^>]*?(?:(?:[^>]*src="(?P<src>[^"]+)")|(?:[^>]*alt="(?P<alt>[^"]+)")|(?:[^>]*title="(?P<title>[^"]+)"))+[^>]*>/i', $text, $matches1, PREG_SET_ORDER);

        // Look for <a> tags with href to images
        preg_match_all('/<a[^>]*?(?:(?:[^>]*href="(?P<src>[^"]+\.(gif|png|jpg|jpeg))")|(?:[^>]*alt="(?P<alt>[^"]+)")|(?:[^>]*title="(?P<title>[^"]+)"))+[^>]*>/i', $text, $matches2, PREG_SET_ORDER);

        $matches = array_merge($matches1, $matches2);

        if (count($matches)) {
            $images = array();

            $count = count($matches);

            $j = 0;
            for ($i = 0; $i < $count && $j < $max; $i++) {
                if (trim($matches[$i]['src']) && (substr($matches[$i]['src'], 0, 1) == '/' || !preg_match('/^https?:\/\//i', $matches[$i]['src']) || substr($matches[$i]['src'], 0, $urlBaseLen) == $urlBase)) {
                    $src = $matches[$i]['src'];

                    if (substr($src, 0, 1) == '/') {
                        $src = substr($src, 1);
                    }

                    if (!preg_match('/^https?:\//i', $src)) {
                        $src = $urlBase . $src;
                    }

                    $image = new \stdClass;
                    $image->src   = $src;
                    $image->title = (isset($matches[$i]['title']) ? $matches[$i]['title'] : @$matches[$i]['alt']);

                    $images[] = $image;

                    $j++;
                }
            }
        }

        return $images;
    }
}
