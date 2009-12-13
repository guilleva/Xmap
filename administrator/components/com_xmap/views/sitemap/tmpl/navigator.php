<?php
/**
 * @version             $Id$
 * @copyright		Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license             GNU General Public License version 2 or later; see LICENSE.txt
 * @author              Guillermo Vargas (guille@vargas.co.cr)
 */

defined('_JEXEC') or die;

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
		div: \'xmap-nav_tree\', 
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
	tree.root.load(\'index.php?option=com_xmap&task=navigator-links&sitemap='.$this->item->id.'&e_name='.$name.'&tmpl=component\');
	});
	');
?>
<div id="xmap-nav_tree" style="height:250px;overflow:auto;border:1px solid #CCC;"></div>
      <div id="xmap-nav_linkinfo" style="margin-top:3px;border:1px solid #CCC;height:120px;">
	    <form name="adminForm" action="#" onSubmit="return false;">
	    <table width="100%">
		   <tr>
		       <td><?php echo JText::_('Xmap_Link_Text'); ?></td>
		       <td colspan="3"><input type="text" name="text" id="f_text" value="" size="30" /></td>
		   </tr>
		   <tr>
		       <td><?php echo JText::_('Xmap_Link_Title'); ?></td>
		       <td colspan="3"><input type="text" name="title" id="f_title"  value="" size="30" /></td>
		   </tr>
		   <tr>
		       <td><?php echo JText::_('Xmap_Link_Link'); ?></td>
		       <td colspan="3"><input type="text" name="link" id="f_link"  value="" size="50" /></td>
		   </tr>
		   <tr>
		       <td><?php echo JText::_('Xmap_Link_Style'); ?></td>
		       <td><input type="text" name="cssstyle" id="f_cssstyle"  value="" /></td>
		       <td><?php echo JText::_('Xmap_Link_Class'); ?></td>
		       <td><input type="text" name="cssclass" id="f_cssclass"  value="" /></td>
		   </tr>
		   <tr>
		       <td colspan="4" align="right"><button name="cssstyle" id="f_cssstyle" onclick="insertLink();"><?php echo JText::_('OK'); ?></button> 
			     <button name="cssstyle" id="f_cssstyle" onclick="window.parent.SqueezeBox.close();"><?php echo JText::_('Cancel'); ?></button></td>
		   </tr>
		</table>
		</form>
      </div>
<ul id="xmap-nav"></ul>
