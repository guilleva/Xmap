<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

header('Content-Type: text/xsl; charset="utf-8"');
header('Content-Disposition: inline');
?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xna="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="http://www.google.com/schemas/sitemap-news/0.9" exclude-result-prefixes="xna">

<xsl:output indent="yes" method="html" omit-xml-declaration="yes"/>
<xsl:template match="/">
<html>
<head>
<title><?php echo JText::_('COM_OSMAP_XML_SITEMAP_FILE'); ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo JUri::base(); ?>media/jui/css/icomoon.css" />
<style type="text/css">
    <![CDATA[
    body {
        font-family: tahoma;
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
                <h1><?php echo JText::_($this->pageHeading); ?></h1>
            <?php endif; ?>
            <div class="count">
                <?php echo JText::_('COM_OSMAP_NUMBER_OF_URLS'); ?>: <xsl:value-of select="count(xna:urlset/xna:url)"></xsl:value-of>
            </div>
        </div>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th><?php echo JText::_('COM_OSMAP_HEADING_URL'); ?></th>
                <th><?php echo JText::_('COM_OSMAP_HEADING_TITLE'); ?></th>
                <th><?php echo JText::_('COM_OSMAP_HEADING_PUBLICATION_DATE'); ?></th>
            </tr>
        </thead>
        <tbody>
            <xsl:for-each select="xna:urlset/xna:url">
                <xsl:variable name="sitemapURL"><xsl:value-of select="xna:loc"/></xsl:variable>
                <tr>
                    <td>
                        <a href="{$sitemapURL}" target="_blank" ref="nofollow" class="url"><xsl:value-of select="$sitemapURL"></xsl:value-of></a>
                        <span class="icon-new-tab"></span>
                    </td>
                    <td>
                        <xsl:value-of select="news:news/news:title" />
                    </td>
                    <td>
                        <xsl:value-of select="news:news/news:publication_date" />
                    </td>
                </tr>
            </xsl:for-each>
        </tbody>
    </table>
</body>
</html>
</xsl:template>
</xsl:stylesheet>
