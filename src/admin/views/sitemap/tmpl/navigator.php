<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas. All rights reserved.
 * @copyright 2015 Open Source Training, LLC. All rights reserved..
 * @author    Guillermo Vargas <guille@vargas.co.cr>
 * @author    Alledia <support@alledia.com>
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file is part of OSMap.
 *
 * OSMap is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * OSMap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSMap. If not, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die('Restricted access');

$name = JRequest::getCmd('e_name');

$doc =& JFactory::getDocument();
$doc->addScriptDeclaration('
    var tree;
    var autotext = \'\';
    insertLink = function (){
        var link = $(\'f_link\').get(\'value\');
        var text = $(\'f_text\').get(\'value\');
        var title = $(\'f_title\').get(\'value\');
        var cssstyle = $(\'f_cssstyle\').get(\'value\');
        var cssclass = $(\'f_cssclass\').get(\'value\');
        if (link != \'\' && text != \'\') {
            var extra =\'\';
            if (title != \'\') {
                extra = extra + \' title=\'+title.replace(\'"\',\'&quot;\')+\'"\';
            }
            if (cssclass != \'\') {
                extra = extra + \' class=\'+cssclass.replace(\'"\',\'&quot;\')+\'"\';
            }
            if (cssstyle != \'\') {
                extra = extra + \' style=\'+cssstyle.replace(\'"\',\'&quot;\')+\'"\';
            }
            var tag = "<a href=\""+link+"\" "+extra+">"+text+"</a>";
            window.parent.jInsertEditorText(tag, "'.htmlspecialchars($name).'");
        }
        window.parent.SqueezeBox.close();
    };
    window.addEvent("domready",function(){
        tree =  new MooTreeControl({
        div: \'osmap-nav_tree\',
        mode: \'files\',
        grid: true,
        theme: \'../media/media/images/mootree.gif\',
        onSelect: function (node,state) {
            if (typeof node.data.link != \'undefined\' && node.data.selectable == \'true\') {
                document.adminForm.link.value = node.data.link;
                if (document.adminForm.text.value == autotext ) {
                    document.adminForm.text.value = node.text;
                    autotext =  node.text;
                }
            }
        }
    },{
        text: \'Home\',
        open: true
    });
    tree.root.load(\'index.php?option=com_osmap&task=navigator-links&sitemap='.$this->item->id.'&e_name='.$name.'&tmpl=component\');
    });
    ');
?>
<div id="osmap-nav_tree" style="height:250px;overflow:auto;border:1px solid #CCC;"></div>
    <div id="osmap-nav_linkinfo" style="margin-top:3px;border:1px solid #CCC;height:120px;">
        <form name="adminForm" action="#" onSubmit="return false;">
        <table width="100%">
            <tr>
                <td><?php echo JText::_('OSMAP_LINK_TEXT'); ?></td>
                <td colspan="3"><input type="text" name="text" id="f_text" value="" size="30" /></td>
            </tr>
            <tr>
                <td><?php echo JText::_('OSMAP_LINK_TITLE'); ?></td>
                <td colspan="3"><input type="text" name="title" id="f_title"  value="" size="30" /></td>
            </tr>
            <tr>
                <td><?php echo JText::_('OSMAP_LINK_LINK'); ?></td>
                <td colspan="3"><input type="text" name="link" id="f_link"  value="" size="50" /></td>
            </tr>
            <tr>
                <td><?php echo JText::_('OSMAP_LINK_STYLE'); ?></td>
                <td><input type="text" name="cssstyle" id="f_cssstyle"  value="" /></td>
                <td><?php echo JText::_('OSMAP_LINK_CLASS'); ?></td>
                <td><input type="text" name="cssclass" id="f_cssclass"  value="" /></td>
            </tr>
            <tr>
                <td colspan="4" align="right">
                    <button name="cssstyle" id="f_cssstyle" onclick="insertLink();"><?php echo JText::_('OK'); ?></button>
                    <button name="cssstyle" id="f_cssstyle" onclick="window.parent.SqueezeBox.close();"><?php echo JText::_('CANCEL'); ?></button>
                </td>
           </tr>
        </table>
    </form>
</div>
<ul id="osmap-nav"></ul>
