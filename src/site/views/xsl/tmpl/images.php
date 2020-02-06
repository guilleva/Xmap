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
<link rel="stylesheet" type="text/css" href="<?php echo $this->icoMoonUri; ?>" />
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

    tr.sitemap-url td {
        background: #e6e3ec;
        padding: 1px 2px;
        color: #b3b3b3;
    }

    tr.sitemap-url td a.url {
        color: #b3b3b3;
    }

    .image-url td {
       padding-left: 12px;
       position: relative;
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
                (<xsl:value-of select="count(xna:urlset/xna:url/image:image/image:loc)"/>
                <?php echo Text::_('COM_OSMAP_IMAGES'); ?>)
            </div>
        </div>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th><?php echo Text::_('COM_OSMAP_HEADING_URL'); ?></th>
                <th><?php echo Text::_('COM_OSMAP_HEADING_TITLE'); ?></th>
            </tr>
        </thead>
        <tbody>
            <xsl:for-each select="xna:urlset/xna:url">
                <xsl:variable name="sitemapURL">
                    <xsl:value-of select="xna:loc"/>
                </xsl:variable>
                <tr class="sitemap-url">
                    <td>
                        <a href="{$sitemapURL}" target="_blank" class="url">
                            <xsl:value-of select="$sitemapURL"/>
                        </a>
                        <span class="icon-new-tab"></span>
                        (<xsl:value-of select="count(./image:image/image:loc)"/>
                        <?php echo Text::_('COM_OSMAP_IMAGES'); ?>)
                    </td>
                    <td>
                        <xsl:value-of select="./title"/>
                    </td>
                </tr>

                <xsl:for-each select="image:image">
                    <xsl:variable name="imageURL"><xsl:value-of select="image:loc"/></xsl:variable>
                    <tr class="image-url">
                        <td>
                            <a href="{$imageURL}"
                                target="_blank"
                                class="image-url">
                                <xsl:value-of select="$imageURL"/>
                            </a>
                            <span class="icon-new-tab"></span>
                        </td>
                        <td>
                            <xsl:value-of select="image:title"/>
                        </td>
                    </tr>
                </xsl:for-each>
            </xsl:for-each>
        </tbody>
    </table>
</body>
</html>
</xsl:template>
</xsl:stylesheet>
