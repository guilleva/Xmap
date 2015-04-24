<?php
use \AcceptanceTester;

require_once ALLEDIA_BUILDER_PATH . '/src/codeception/acceptance/ExtensionInstallerAbstractCest.php';

class XmapMigrationCest extends ExtensionInstallerAbstractCest
{
    /**
     * @before loginIntoAdmin
     */
    public function installExtension(AcceptanceTester $I)
    {
        parent::installExtension($I);
    }
}
