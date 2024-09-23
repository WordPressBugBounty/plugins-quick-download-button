=== Quick Download Button ===
Contributors: kusimo
Donate link: https://www.buymeacoffee.com/kusimo
Tags: media download, hide download link, download button
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 3.0.1
Tested up to: 6.4.1
Stable tag: 1.2.6
Requires PHP: 5.6 
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html

Quick download button with block and shortcode support.

== Description ==
The Quick Download Button plugin for WordPress allows you to easily integrate a simple but sophisticated download button into your posts and pages. This versatile plugin offers a range of features like a countdown timer, multiple button styles, and the ability to hide or show file size and extension. It supports both WordPress blocks and shortcodes, making it highly adaptable to your website's needs.


== Installation ==
1. Upload the quick-download-button folder to the /wp-content/plugins/ directory
2. Activate the Delete Quick Download Button plugin through the \'Plugins\' menu in WordPress

== Features ==

* Display file size and extension.
* Create customizable download buttons via shortcode.
* Link your download button to any publicly accessible web location.
* Support for a wide range of file types including music, video, PDFs, spreadsheets, and more.
* Option to hide the download link for added security.
* Countdown feature to specify a waiting time before the download starts.
* Customizable waiting message for user engagement.
* Support for external download links.
* Shows download file extension for 'pdf','mp3','mov','zip','txt','doc','xml','mp4','ppt' and images ( png, gif, jpg, jpeg, bmp).
* Support for htm, html, ps, tex, xml, txt, csv, xlsx (Microsoft Excel), pptx (Microsoft PowerPoint), js, css, php.
* Open external download in new tab or same window.
* Force file download.
* Access control based on user roles and login status.
* WordPress Gutenberg block support for easy integration.


 == Basic Usage ==

** Shortcode **
To add a download button, open the post or page editor and paste the following shortcode example:

`
[quick_download_button title="Download" url="http://yoursite/wp-content/upload/fileto_download.pdf"]
`
Replace the url value with your file's URL. Change the title value to customize the button text.


== More Shortcode Usage ==

** Open link in a new window **

Set open_new_window to true to open the download link in a new tab, or false to open it in the same window.

`
[quick_download_button title="Download" open_new_window="true" url_external="https://google.com"]
`

** Set the button background color (color_bg="#ffc107"), waiting timer (wait=15) and a custom message (msg="Please wait 15 seconds") **

`
[quick_download_button  title="Download" color_bg="#ffc107" open_new_window="true" wait=15 msg="Please wait 15 seconds" url_external="https://google.com"]
`

** Link to an External URL **

To use external url, add url_external attribute.

`
[quick_download_button title="Download" url_external="https://google.com"]
`

** Auto Calculate File Size **

To have the plugin automatically generate the file size, ensure the file URL is in the WordPress upload directory and set file_size to 1.

`
[quick_download_button file_size="1" title="Download" url="http://yoursite/wp-content/upload/fileto_download.pdf"]
`

** Manually Add File Size **

You can manually specify the file size in the file_size attribute.

`
[quick_download_button file_size="14.5MB" title="Download" url="http://yoursite/wp-content/upload/fileto_download.pdf"]
`


** Hide File Extension Icon **

To display both the file extension icon and text, set extension and extension_text to 1.

`
[quick_download_button  title="Download" filesize="1" extension="0" url="http://yoursite/wp-content/upload/fileto_download.pdf"]
`

** Display File Extension Icon and Text **

To showcase both the file extension icon and its text, set both the extension and extension_text attributes to 1.

`
[quick_download_button  title="Download" filesize="1" extension="1" extension_text="1"  url="http://yoursite/wp-content/upload/fileto_download.pdf"]
`

** Using the Gutenberg Block **

1. Open the post where you wish to add a download link and click on the 'Add Block' icon (+).
2. Look for 'Download Button' under the Media category and click to add it.
3. Click on the button to edit its title and the adjacent download icon to upload the file for download.
4. Provide a custom title in the text box if desired. The default title is 'Download'. That's it!

** Advanced Gutenberg Usage **
To hide the file size in the Gutenberg editor, utilize the 'Additional CSS class(es)' field in the block settings. Add 'hide-size' to this field to apply the effect.


== For Developers: Integrating in a Theme File ==

To incorporate the download button within a theme file, use the following code with your specified attributes and values:

`
echo do_shortcode('[quick_download_button title="Download" url="http://yoursite/wp-content/upload/fileto_download.pdf"]');
`

== Donations ==

If you find this plugin helpful and would like to support its ongoing development, please consider [Donate](https://www.buymeacoffee.com/kusimo)


== Documentation ==
To contribute and improve this plugin please visit the [Git repo](https://github.com/kusimo/quick-download-button).

== Frequently Asked Questions ==

= Can this plugin be used to hide (protect) download links on my site? =

Yes, it can.

= Can I use the plugin in the Classic Editor? =

Yes, you can use this plugin with shortcodes in the Classic Editor. 

= Can this plugin be used to display download file size on my site? =

Yes, it displays the download file size.

= Can I hide the 'Please wait...' message for the countdown timer? =

For shortcodes, leave the msg attribute blank. Example:
`
[quick_download_button button_type="small" msg="" title="Download Now" url_external="http://external-url-here/"]
`

For blocks, under 'Countdown Settings', select the time to wait (e.g., 15 for 15 seconds) and remove the default message from the message box.

= What are the shortcode attributes? =

* 'title'     
* 'file_size'  
* 'url'            	
* 'extension'  
* 'url_external'   	
* 'open_new_window'
* 'wait'			 		
* 'color_bg'				
* 'color_font'			
* 'color_icon_dark'
* 'msg'				
* 'button_type'		(large / mid / small / basic )	
* 'border_width'			
* 'border_style'			
* 'border_color'			
* 'border_radius'			
* 'align'					
* 'padding'

Change button style with 'button_type':
`
[quick_download_button button_type="small" title="Download Now" url_external="http://external-url-here/"]
`

Add file from your WordPress site with url:
`
[quick_download_button button_type="mid" title="Download Now" url="http://yoursite/wp-content/uploads/yourfile.pdf"]
`

Add border radius with 'border_radius':
`
[quick_download_button button_type="small" border_radius="9" title="Download Now" url_external="http://external-url-here/"]
`

Change icon color with color_icon_dark:
`
[quick_download_button button_type="large" color_icon_dark="false" color_bg="black" color_font="#FFF" title="Download Now" url_external="http://external-url-here/"]
`

Add countdown and wait message with wait and msg:
`
[quick_download_button button_type="basic" wait="10" msg="Please wait..."  title="Download Now" url_external="http://external-url-here/"]
`

Manually add file size with file_size:
`
[quick_download_button button_type="basic" wait="10" msg="Please wait..." file_size="40MB"  title="Download Now" url_external="http://external-url-here/"]
`

Hide file extension with extension:
`
[quick_download_button button_type="basic" extension="0"  title="Download Now" url_external="http://external-url-here/"]
`

Add border style:
`
[quick_download_button button_type="small" border_width="2" border_style="solid" border_color="black"  title="Download Now" url_external="http://external-url-here/"]
`

Change alignment with align:
`
[quick_download_button button_type="small" align="left"  title="Download Now" url_external="http://external-url-here/"]
`

== Screenshots ==

1. Download button example (Large button).


== Changelog ==

= 1.2.6 - November 23rd, 2023 =
* Optimised resource loading: CSS and JavaScript files are now only loaded when the Quick Download Button is present on the page.

= 1.2.5 April 19th, 2023 =
* Minor fixes of 404 error that occurs when quick download button page is deleted. After deletion of the QDB page, please deactivate and reactive the plugin.

= 1.2.4 March 13th, 2023 =
* Fixed the error nonce did not verify when download link is external URL

= 1.2.3 August 29th, 2022 =
* Control access to download using users' role and logged in. 

= 1.2.0 August 27th, 2022  =
* Fixed CSS for the large button so it works with new upgrade.

= 1.0.9 August 27th, 2022  =
* Add 3 extra buttons - small, medium and basic.
* Add button alignment - center, left or right.

= 1.0.8 August 13th, 2022  =
* Fixed message not showing up when user is waiting for download.

= 1.0.5 July 23rd, 2022  =
* Add support for multi site.
* Add wait time attribute and background color attribute to shortcode. 


== Upgrade Notice ==

= 1.2.6 - November 23rd, 2023 =
* Optimised resource loading: CSS and JavaScript files are now only loaded when the Quick Download Button is present on the page.

= 1.2.5 April 19th, 2023 =
* Minor fixes of 404 error that occurs when quick download button page is deleted. After deletion of the QDB page, please deactivate and reactive the plugin.

= 1.2.4 March 13th, 2023 =
* Fixed the error nonce did not verify when download link is external URL
