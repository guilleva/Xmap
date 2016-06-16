## 4.0.0 Free

* Code refactored and cleaned up, keeping backward compatibility with 3rd party plugins (OSMap and XMap)
* Removed J2.5 legacy code. J3 Only.
* Refined UI
* Menu selector, click anywhere to select. Improved a little bit the visual feedback
* Sitemap params moved to the respectives menu items
* Removed metadata fields from sitemap. Use the menu's metadata if needed
* Sitemap edit form moved to the sitemap list in the admin
* New sitemap items collector
* Renamed osmap plugin: com_content -> joomla
* Removed other plugins from the package, moving to the Pro. Only support the Joomla's native content out of the box. Still accepts the legacy plugins.
* Added param to display the UID of items in the admin
* Allow to see raw link hovering the item in the admin
* In the sitemap list, the links now detect a menu and uses the SEF url, if enabled
* Removed option to beautify and compact XML. It is now compacted by default and displayed as raw XML.
* New event onOSMapAddAdminSubmenu - allow to modify or add submenus
* Table __osmap_sitemap renamed to __osmap_sitemaps
* Simplified sitemap settings between XML and HTML views, unifying. If needs custom settings per view, create a new sitemap.
* Better control of item UID to avoid duplicated content (not only duplicated urls) - joomla content
* Added option to hide/show external urls
* Added visual feedback in the admin to see what items will be ignored due to duplication or external link
* Improved item settings to allow custom settings even for duplicated items (in case one is unpublished)
* Level mark in the items list in the admin
* Added debug option to the HTML sitemap

## 3.3.0 Free - Last release with J2.5 support
