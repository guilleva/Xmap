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

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();

?>
<xsl:stylesheet
    version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xna="https://www.sitemaps.org/schemas/sitemap/0.9"
    xmlns:image="https://www.google.com/schemas/sitemap-image/1.1"
    exclude-result-prefixes="xna">
    <xsl:output indent="yes" method="html" omit-xml-declaration="yes"/>
    <xsl:template match="/">
        <html lang="<?php echo $this->language; ?>">
        <head>
            <title><?php echo Text::_('COM_OSMAP_XML_SITEMAP_FILE'); ?></title>
            <link rel="stylesheet" type="text/css" href="<?php echo JUri::base(); ?>media/jui/css/icomoon.css"/>
            <style type="text/css">
                <![CDATA[
                body {
                    font-family: tahoma, sans-serif;
                    position: relative;
                }

                table {
                    font-size: 11px;
                    width: 100%;
                }

                th {
                    background: #9f8Fbf;
                    color: #fff;
                    text-align: left;
                    padding: 4px;
                }

                tr:nth-child(even) {
                    background: #eeF8ff;
                }

                td {
                    padding: 1px;
                }

                .data a {
                    text-decoration: none;
                }

                .icon-new-tab {
                    font-size: 10px;
                    margin-left: 4px;
                    color: #b5b5b5;
                }

                .count {
                    font-size: 12px;
                    margin-bottom: 10px;
                }
                ]]>
            </style>
        </head>
        <body>
        <div class="header">
            <div class="title">
                <?php if (!empty($this->pageHeading)) : ?>
                    <h1><?php echo Text::_($this->pageHeading); ?></h1>
                <?php endif; ?>
                <div class="count">
                    <?php echo Text::_('COM_OSMAP_NUMBER_OF_URLS'); ?>:
                    <xsl:value-of select="count(xna:urlset/xna:url)"/>
                </div>
            </div>
        </div>

        <table class="data">
            <thead>
            <tr>
                <th><?php echo Text::_('COM_OSMAP_URL'); ?></th>
                <th><?php echo Text::_('COM_OSMAP_MODIFICATION_DATE'); ?></th>
                <th><?php echo Text::_('COM_OSMAP_CHANGE_FREQ'); ?></th>
                <th><?php echo Text::_('COM_OSMAP_PRIORITY_LABEL'); ?></th>
            </tr>
            </thead>
            <tbody>
            <xsl:for-each select="xna:urlset/xna:url">
                <xsl:variable name="sitemapURL">
                    <xsl:value-of select="xna:loc"/>
                </xsl:variable>
                <tr>
                    <td>
                        <a href="{$sitemapURL}"
                           target="_blank">
                            <xsl:value-of select="$sitemapURL"/>
                        </a>
                        <span class="icon-new-tab"></span>
                    </td>
                    <td>
                        <xsl:value-of select="xna:lastmod"/>
                    </td>
                    <td>
                        <xsl:value-of select="xna:changefreq"/>
                    </td>
                    <td>
                        <xsl:value-of select="xna:priority"/>
                    </td>
                </tr>
            </xsl:for-each>
            </tbody>
        </table>
        </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
