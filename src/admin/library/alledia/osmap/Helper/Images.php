<?php
/**
 * @package   OSMap
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2020 Joomlashack.com. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 *
 * This file is part of OSMap.
 *
 * OSMap is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * (at your option) any later version.
 *
 * OSMap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSMap.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Alledia\OSMap\Helper;

use Alledia\OSMap\Factory;
use Exception;

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
     * @throws Exception
     */
    public function getImagesFromText($text, $max = 9999)
    {
        $container = Factory::getContainer();
        $images    = array();

        // Look <img> tags
        preg_match_all(
            '/<img[^>]*?(?:(?:[^>]*src="(?P<src>[^"]+)")|(?:[^>]*alt="(?P<alt>[^"]+)")|(?:[^>]*title="(?P<title>[^"]+)"))+[^>]*>/i',
            $text,
            $matches1,
            PREG_SET_ORDER
        );

        // Look for <a> tags with href to images
        preg_match_all(
            '/<a[^>]*?(?:(?:[^>]*href="(?P<src>[^"]+\.(gif|png|jpg|jpeg))")|(?:[^>]*alt="(?P<alt>[^"]+)")|(?:[^>]*title="(?P<title>[^"]+)"))+[^>]*>/i',
            $text,
            $matches2,
            PREG_SET_ORDER
        );

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

                        $image = (object)array(
                            'src'   => $src,
                            'title' => empty($matches[$i]['title'])
                                ? (empty($matches[$i]['alt'])
                                    ? ''
                                    : $matches[$i]['alt'])
                                : $matches[$i]['title']
                        );

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
     * @param object $item
     *
     * @return array
     * @throws Exception
     */
    public function getImagesFromParams($item)
    {
        $container   = Factory::getContainer();
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
