<?php
/**
 * @version          $Id$
 * @copyright        Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license          GNU General Public License version 2 or later; see LICENSE.txt
 * @author           Guillermo Vargas (guille@vargas.co.cr)
 */
defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
if(version_compare(JVERSION,'3.0.0','ge')) {
    JHtml::_('formbehavior.chosen', 'select');
}
?>
<script type="text/javascript">
<!--
    function submitbutton(task)
    {
        if (task == 'sitemap.cancel' || document.formvalidator.isValid($('adminForm'))) {
            submitform(task);
        }
    }
// -->
</script>
<form action="<?php echo JRoute::_('index.php?option=com_xmap&layout=edit&id='.$this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
    <div class="row-fluid">
        <!-- Begin Content -->
        <div class="span10 form-horizontal">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#general" data-toggle="tab"><?php echo JText::_('XMAP_SITEMAP_DETAILS_FIELDSET');?></a></li>
                <li><a href="#attrib-menus" data-toggle="tab"><?php echo JText::_('XMAP_FIELDSET_MENUS');?></a></li>
                <?php
                $fieldSets = $this->form->getFieldsets('attribs');
                foreach ($fieldSets as $name => $fieldSet) :
                ?>
                <li><a href="#attrib-<?php echo $name;?>" data-toggle="tab"><?php echo JText::_($fieldSet->label);?></a></li>
                <?php
                endforeach;
                ?>
            </ul>

            <div class="tab-content">
                <div class="tab-pane active" id="general">
                    <div class="row-fluid">
                        <div class="span10">
                            <div class="control-group">
                                <?php echo $this->form->getLabel('title'); ?>
                                <div class="controls">
                                    <?php echo $this->form->getInput('title'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <?php echo $this->form->getLabel('alias'); ?>
                                <div class="controls">
                                    <?php echo $this->form->getInput('alias'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <?php echo $this->form->getLabel('state'); ?>
                                <div class="controls">
                                    <?php echo $this->form->getInput('state'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <?php echo $this->form->getLabel('access'); ?>
                                <div class="controls">
                                    <?php echo $this->form->getInput('access'); ?>
                                </div>
                            </div>
                            <div class="control-group">
                                <div class="clr"></div>
                                <?php echo $this->form->getLabel('introtext'); ?><br />
                                <div class="clr"></div>
                                <div class="controls">
                                    <?php echo $this->form->getInput('introtext'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="attrib-menus">
                    <div style="width:500px">
                        <?php echo $this->form->getInput('selections'); ?>
                    </div>
                </div>
                <?php
                $fieldSets = $this->form->getFieldsets('attribs');
                foreach ($fieldSets as $name => $fieldSet) :
                ?>
                <div class="tab-pane" id="attrib-<?php echo $name;?>">
                    <?php
                    if (isset($fieldSet->description) && trim($fieldSet->description)) :
                        echo '<p class="tip">' . $this->escape(JText::_($fieldSet->description)) . '</p>';
                    endif;

                    foreach ($this->form->getFieldset($name) as $field) :
                    ?>
                    <div class="control-group">
                        <?php echo $field->label; ?>
                        <div class="controls">
                            <?php echo $field->input; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <input type="hidden" name="task" value="" />
    <?php echo $this->form->getInput('is_default'); ?>
    <?php echo JHtml::_('form.token'); ?>
</form>
<div class="clr"></div>
