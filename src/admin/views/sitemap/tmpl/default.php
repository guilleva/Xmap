<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

JHtml::_('stylesheet', 'com_osmap/admin.min.css', array('relative' => true));
$input = JFactory::getApplication()->input;

$actionQuery = array(
    'option' => 'com_osmap',
    'view'   => 'sitemap',
    'layout' => 'edit',
    'id'     => (int)$this->item->id
);
?>
<script>
    ;(function($) {
        Joomla.submitbutton = function(task) {
            if (task === 'sitemap.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
                var ordering = $('#ul_menus').sortable('toArray').toString();
                $('#menus_ordering').val(ordering);

                Joomla.submitform(task, document.getElementById('adminForm'));
            }
        }
    })(jQuery);
</script>

<form action="<?php echo JRoute::_('index.php?' . http_build_query($actionQuery)); ?>"
      method="post"
      name="adminForm"
      id="adminForm"
      class="form-validate sitemap">

    <?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="form-horizontal">
        <div class="row-fluid">
            <div class="span9">
                <?php echo $this->form->getField('menus')->renderField(array('hiddenLabel' => true)); ?>
            </div>

            <div class="span3">
                <?php echo $this->form->renderFieldset('params'); ?>
            </div>
        </div>
    </div>

    <input type="hidden" id="menus_ordering" name="jform[menus_ordering]" value=""/>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>"/>
    <?php echo JHtml::_('form.token'); ?>
</form>

