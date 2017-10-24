<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap\Helper;

use Alledia\OSMap;

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
        $container = OSMap\Factory::getContainer();
        $images    = array();
        $matches   = $matches1 = $matches2 = array();

        // Look <img> tags
        preg_match_all('/<img[^>]*?(?:(?:[^>]*src="(?P<src>[^"]+)")|(?:[^>]*alt="(?P<alt>[^"]+)")|(?:[^>]*title="(?P<title>[^"]+)"))+[^>]*>/i', $text, $matches1, PREG_SET_ORDER);

        // Look for <a> tags with href to images
        preg_match_all('/<a[^>]*?(?:(?:[^>]*href="(?P<src>[^"]+\.(gif|png|jpg|jpeg))")|(?:[^>]*alt="(?P<alt>[^"]+)")|(?:[^>]*title="(?P<title>[^"]+)"))+[^>]*>/i', $text, $matches2, PREG_SET_ORDER);

        $matches = array_merge($matches1, $matches2);

        if (count($matches)) {
            $count = count($matches);

            $j = 0;
            for ($i = 0; $i < $count && $j < $max; $i++) {
                $src = trim($matches[$i]['src']);

                if (!empty($src)) {
                    if ($container->router->isInternalURL($src)) {
                        if ($container->router->isRelativeUri($src)) {
                            $src = $container->router->convertRelativeUriToFullUri($src);
                        }

                        $image = new \stdClass;
                        $image->src   = $src;
                        $image->title = (isset($matches[$i]['title']) ? $matches[$i]['title'] : @$matches[$i]['alt']);

                        $images[] = $image;

                        $j++;
                    }
                }
            }
        }

        return $images;
    }

    /**
     * Return an array of images found in the content image params.
     *
     * @param stdClass $item
     *
     * @return array
     */
    public function getImagesFromParams($item)
    {
        $container   = OSMap\Factory::getContainer();
        $imagesParam = json_decode($item->images);
        $images      = array();

        if (isset($imagesParam->image_intro) && !empty($imagesParam->image_intro)) {
            $images[] = (object)array(
                'src'   => $container->router->convertRelativeUriToFullUri($imagesParam->image_intro),
                'title' => $imagesParam->image_intro_caption
            );
        }

        if (isset($imagesParam->image_fulltext) && !empty($imagesParam->image_fulltext)) {
            $images[] = (object)array(
                'src'   => $container->router->convertRelativeUriToFullUri($imagesParam->image_fulltext),
                'title' => $imagesParam->image_fulltext_caption
            );
        }

        return $images;
    }
}
