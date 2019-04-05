<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die();

use Alledia\OSMap;

class OSMapViewAdminSitemapItems extends JViewLegacy
{
    /**
     * @param string $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $this->checkAccess();

        $container = OSMap\Factory::getContainer();

        try {
            $id = $container->input->getInt('id');

            $this->params = OSMap\Factory::getApplication()->getParams();

            // Load the sitemap instance
            $this->sitemap     = OSMap\Factory::getSitemap($id, 'standard');
            $this->osmapParams = JComponentHelper::getParams('com_osmap');
        } catch (Exception $e) {
            $this->message = $e->getMessage();
        }

        parent::display($tpl);
    }

    /**
     * This view should only be available from the backend
     *
     * @return void
     * @throws Exception
     */
    protected function checkAccess()
    {
        $server  = new JInput(array_change_key_case($_SERVER, CASE_LOWER));
        $referer = parse_url($server->getString('http_referer'));

        if (!empty($referer['query'])) {
            parse_str($referer['query'], $query);

            $option = empty($query['option']) ? null : $query['option'];
            $view   = empty($query['view']) ? null : $query['view'];

            if ($option == 'com_osmap' && $view == 'sitemapitems') {
                // Good enough
                return;
            }
        }

        throw new Exception(JText::_('JERROR_PAGE_NOT_FOUND'), 404);
    }
}
