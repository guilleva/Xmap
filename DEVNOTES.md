# Development Notes

## Restore:

* sitemap edit (in admin - acl)
* custom settings per sitemap node
* publish/unpublish nodes
* plugins (backward compatibility)

* robots param checking
* exclude external links? add params to control that
* modification date
* levels in the HTML sitemap
* images (inside the plugins)
* news
* db migration from older versions
* node->secure
* add new methods to plugins
* add new plugin events
* after install, migrate legacy plugin's settings to the new ones
* restore free/pro
* generate UID from plugin:id? the url is probably not the best source... test that

* menuitem->params? osmap.php:101
* mergecomponent params?
* Double check why plugin's names are not being translated
* Remove commented memory profile code from the fetch method
* Check all getAuthorisedViewLevels, to call OSMap\Helper::getAuthorisedViewLevels() (plugins)


## For Pro

* Cache
