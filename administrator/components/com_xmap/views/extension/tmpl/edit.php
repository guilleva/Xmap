<?php
    /**
    * @version             $Id$
    * @copyright		Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
    * @license             GNU General Public License version 2 or later; see LICENSE.txt
    * @author              Guillermo Vargas (guille@vargas.co.cr)
    */

    defined('_JEXEC') or die;

    // Include the component HTML helpers.
    JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

    jimport('joomla.html.pane');

    // Load the tooltip behavior.
    JHtml::_('behavior.tooltip');
    JHtml::_('behavior.formvalidation');
?>

<script type="text/javascript">
    <!--
    function submitbutton(task)
    {
        if (task == 'extension.cancel' || document.formvalidator.isValid($('item-form'))) {
            <?php //echo $this->form->fields['introtext']->editor->save('jform[introtext]'); ?>
            submitform(task);
        }
    }
    // -->
</script>

<form action="<?php JRoute::_('index.php?option=com_xmap'); ?>" method="post" name="adminForm" id="item-form" class="form-validate">

    <div class="width-40" style="float:left">
        <fieldset class="adminform">
            <legend><?php echo JText::_( 'XMAP_EXTENSION_DETAILS' ); ?></legend>

            <table class="admintable">
                <tr>
                    <td width="100" class="key"><?php echo $this->form->getLabel('name'); ?></td>
                    <td><?php echo $this->form->getInput('name'); ?></td>
                </tr>

                <tr>
                    <td width="100" class="key"><label><?php echo JText::_( 'XMAP_EXTENSION_AUTHOR' ); ?></label></td>
                    <td><?php echo $this->extension->author; ?></td>
                </tr>

                <tr>
                    <td width="100" class="key"><label><?php echo JText::_( 'XMAP_EXTENSION_AUTHOR_EMAIL' ); ?></label></td>
                    <td><?php echo $this->extension->authorEmail; ?></td>
                </tr>

                <tr>
                    <td width="100" class="key"><label><?php echo JText::_( 'XMAP_EXTENSION_AUTHOR_WEBSITE' ); ?></label></td>
                    <td><?php echo $this->extension->authorUrl; ?></td>
                </tr>

                <tr>
                    <td width="100" class="key"><label><?php echo JText::_( 'XMAP_EXTENSION_DESCRIPTION' ); ?></label></td>
                    <td><?php echo $this->extension->description; ?></td>
                </tr>
                <?php if ($this->extension->extension_id) : ?>
                <tr>
                    <td width="100" class="key"><?php echo $this->form->getLabel('extension_id'); ?></td>
                    <td><?php echo $this->form->getInput('extension_id'); ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </fieldset>
    </div>

    <div class="width-60 fltrt">
        <?php echo JHtml::_('sliders.start','plugin-sliders-'.$this->extension->extension_id); ?>
        <?php
            $fieldSets = $this->form->getFieldsets('params');

            foreach ($fieldSets as $name => $fieldSet) :
                $label = !empty($fieldSet->label) ? $fieldSet->label : 'XMAP_'.$name.'_FIELDSET_LABEL';
                echo JHtml::_('sliders.panel',JText::_($label), $name.'-options');
                if (isset($fieldSet->description) && trim($fieldSet->description)) :
                    echo '<p class="tip">'.$this->escape(JText::_($fieldSet->description)).'</p>';
                    endif;
            ?>
            <fieldset class="panelform">
                <?php foreach ($this->form->getFieldset($name) as $field) : ?>
                    <?php echo $field->label; ?>
                    <?php echo $field->input; ?>
                    <?php endforeach; ?>
            </fieldset>
            <?php endforeach; ?>
        <?php echo JHtml::_('sliders.end'); ?>
    </div>

    <?php echo $this->form->getInput('folder'); ?>
    <?php echo $this->form->getInput('element'); ?>

    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
</form>
<div class="clr"></div>
