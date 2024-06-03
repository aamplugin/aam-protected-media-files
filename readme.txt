=== AAM Protected Media Files ===
Contributors: vasyltech
Tags: protected media, files, protected videos, documents, media library
Requires at least: 4.7.0
Requires PHP: 5.6.0
License: GPLv2 or later
Tested up to: 6.5.3
Stable tag: 1.2.5

Add-on to the free Advanced Access Manager plugin that protects media files from direct access for visitors, roles or users

== Description ==

Prevent direct access to the unlimited number of media library items either for visitors, individual users or groups of users (roles). This plugin does not modify a physical file's location or URL.

> The AAM Protected Media Files plugins is the official add-on to the free Advanced Access Manager (aka AAM). While this plugin actually manages physical access to the media files, AAM provides the UI interface to define access to files.

= Few Facts =

* It requires a simple manual steps in order to configure a webserver to protect direct access to `/wp-content/uploads` folder. For more information, please check [our installation instructions](https://aamportal.com/article/protected-media-files-installation);
* It does not change a physical file's location, content or URL. Upon deactivation, everything goes back to normal;
* It protects all the allowed by WordPress core file types and those that are extended with third-party plugins (e.g. `.svg`, `.sketch`, etc.). For the list of all allowed extensions, check official WP documentation for the [wp_get_ext_types()](https://developer.wordpress.org/reference/functions/wp_get_ext_types/) core function;
* It allows you to manage access to any media file for visitors, any individual user, roles or even define the default access to all media files for everybody (this one is available with [premium](https://aamportal.com/premium) add-on for AAM plugin);

For more information about how properly install and use it, refer to [our documentation](https://aamportal.com/support).

== Installation ==

1. Upload `aam-protected-media-files` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Configure your webserver to redirect all media request to access handler as described in [installation guide](https://aamportal.com/article/protected-media-files-installation).

== Screenshots ==

1. Restrict access to media file with AAM UI
2. Restrict access to media file while editing it

== Changelog ==

= 1.2.5 =
* Changed: Bumped up the minimum required WP version to 5.0.0

= 1.2.4 =
* Fixed: Give the ability to serve files outside of the wp-content folder [https://github.com/aamplugin/aam-protected-media-files/issues/18](https://github.com/aamplugin/aam-protected-media-files/issues/18)
* Fixed: Access is not correctly restricted for some paths [https://github.com/aamplugin/aam-protected-media-files/issues/17](https://github.com/aamplugin/aam-protected-media-files/issues/17)

= 1.2.3 =
* Fixed: Uncaught TypeError: mime_content_type(): Argument #1 [https://github.com/aamplugin/aam-protected-media-files/issues/16](https://github.com/aamplugin/aam-protected-media-files/issues/16)

= 1.2.2 =
* Fixed: Deprecated PHP warning [https://github.com/aamplugin/aam-protected-media-files/issues/12](https://github.com/aamplugin/aam-protected-media-files/issues/12)
* Fixed: Handle files with encoded characters [https://github.com/aamplugin/aam-protected-media-files/issues/13](https://github.com/aamplugin/aam-protected-media-files/issues/13)
* Fixed: NGINX hard redirect issue [https://github.com/aamplugin/aam-protected-media-files/issues/15](https://github.com/aamplugin/aam-protected-media-files/issues/15)
* Added New: Allow the ability to override the ABSPATH [https://github.com/aamplugin/aam-protected-media-files/issues/14](https://github.com/aamplugin/aam-protected-media-files/issues/14)

= 1.2.1 =
* Changed: Renamed the 401 redirect option to "Use Access Denied Redirect For Restricted Media Items"

= 1.2.0 =
* Fixed Bug: Fails to serve files with special characters [https://github.com/aamplugin/aam-protected-media-files/issues/7](https://github.com/aamplugin/aam-protected-media-files/issues/7)
* Added New: The ability to verify that file was served by the plugin [https://github.com/aamplugin/aam-protected-media-files/issues/9](https://github.com/aamplugin/aam-protected-media-files/issues/9)

= 1.1.7 =
* Fixed Bug: SVGs are blocked by default [https://github.com/aamplugin/aam-protected-media-files/issues/6](https://github.com/aamplugin/aam-protected-media-files/issues/6)

= 1.1.6 =
* Fixed Bug: Incorrectly path comparisson on Windows servers [https://github.com/aamplugin/aam-protected-media-files/issues/3](https://github.com/aamplugin/aam-protected-media-files/issues/3)
* Added New: Allow to hook into media search for other AAM add-ons [https://github.com/aamplugin/aam-protected-media-files/issues/4](https://github.com/aamplugin/aam-protected-media-files/issues/4)

= 1.1.5 =
* Fixed Bug: URI Access service does not work on NGINX servers [https://github.com/aamplugin/advanced-access-manager/issues/33](https://github.com/aamplugin/advanced-access-manager/issues/33)

= 1.1.4 =
* Fixed Bug: Incorrectly computed file path on NGINX servers

= 1.1.3 =
* Fixed Bug: Incorrectly computed physical path to a file when website is located in subfolder

= 1.1.2 =
* Fixed Bug: Incorrectly returned image size [https://forum.aamplugin.com/d/448-wrong-image-sizes-are-returned-with-aam-protected-media-files-plugin-enabled](https://forum.aamplugin.com/d/448-wrong-image-sizes-are-returned-with-aam-protected-media-files-plugin-enabled)

= 1.1.1 =
* Fixed Bug: User was not redirecting properly when access was denied. Incorrectly handled HTTP redirecting
* Fixed Bug: Incorrectly managed file search when media file's name follows reserved naming convention

= 1.1.0 =
* Fixed Bug: AAM URI Access access options were not honored for stand-alone files in the `uploads` folder
* Added New: Introduced new AAM settings that enables Access Denied Redirect service when access is denied to protected files

= 1.0.0 =
* Initial version