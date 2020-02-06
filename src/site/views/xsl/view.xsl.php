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

use Alledia\OSMap\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView;

defined('_JEXEC') or die();

class OSMapViewXsl extends HtmlView
{
    public function __construct($config = array())
    {
        parent::__construct($config);
    }

    /**
     * @var string
     */
    protected $pageHeading = null;

    /**
     * @var string
     */
    protected $language = null;

    /**
     * @var string
     */
    protected $icoMoonUri = null;

    /**
     * @param string $tpl
     *
     * @return void
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $document = Factory::getDocument();

        $this->language   = $document->getLanguage();
        $this->icoMoonUri = HTMLHelper::_(
            'stylesheet',
            'jui/icomoon.css',
            array('relative' => true, 'pathOnly' => true)
        );

        $this->pageHeading = htmlspecialchars(
            urldecode(
                Factory::getApplication()->input->getString('title')
            )
        );

        // We're going to cheat Joomla here because some referenced urls MUST remain http/insecure
        header(sprintf('Content-Type: text/xsl; charset="%s"', $this->_charset));
        header('Content-Disposition: inline');

        parent::display($tpl);

        jexit();
    }
}
