<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Alledia\OSMap;

defined('_JEXEC') or die();


class OSMapControllerSitemapItems extends OSMap\Controller\Form
{
    /**
     * Method override to check if the user can edit an existing record.
     *
     * @param    array    An array of input data.
     * @param    string   The name of the key for the primary key.
     *
     * @return   boolean
     */
    protected function _allowEdit($data = array(), $key = 'id')
    {
        // Initialise variables.
        $recordId = (int) isset($data[$key]) ? $data[$key] : 0;

        // Assets are being tracked, so no need to look into the category.
        return JFactory::getUser()->authorise('core.edit', 'com_osmap.sitemap.' . $recordId);
    }

    public function cancel($key = null)
    {
        $this->setRedirect('index.php?option=com_osmap&view=sitemaps');
    }

    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

        $app = OSMap\Factory::getApplication();

        $sitemapId  = $app->input->getInt('id');
        $updateData = $app->input->getRaw('update-data');
        $language   = $app->input->getRaw('language');

        $model = $this->getModel();

        if (!empty($updateData)) {
            $updateData = json_decode($updateData, true);

            if (!empty($updateData) && is_array($updateData)) {
                foreach ($updateData as $data) {
                    $row = $model->getTable();
                    $row->load(
                        array(
                            'sitemap_id'    => $sitemapId,
                            'uid'           => $data['uid'],
                            'settings_hash' => $data['settings_hash']
                        )
                    );

                    $data['sitemap_id'] = $sitemapId;
                    $data['format']     = '2';

                    $row->save($data);
                }
            }
        }

        if ($this->getTask() === 'apply') {
            $url = 'index.php?option=com_osmap&view=sitemapitems&id=' . $sitemapId;

            if (!empty($language)) {
                $url .= '&lang=' . $language;
            }

            $this->setRedirect($url);
        } else {
            $this->setRedirect('index.php?option=com_osmap&view=sitemaps');
        }
    }
}
