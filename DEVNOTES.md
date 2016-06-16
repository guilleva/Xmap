# Development Notes

## Restore:

* Languages in the manifest to fix comp params
* db migration from older versions
* after install, migrate legacy plugin's settings to the new ones
* restore free/pro
* plugins (backward compatibility)
* exclude external links? add params to control that

* Remove commented memory profile code from the fetch method

## For Pro

* Cache
* robots param checking
* Check all getAuthorisedViewLevels, to call OSMap\Helper::getAuthorisedViewLevels() (plugins)
