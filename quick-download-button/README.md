# Quick Download Button WordPress Plugin
Quick download button is a download button for WordPress. You can easily add a better download link to your post with this plugin. Some of the features include countdown timer, 4 button styles, colors, hide/show file size and extension. Support for wordpress block and shortcodes.

![Image of Quick Download Button](https://github.com/kusimo/quick-download-button/blob/master/screenshot/screenshot-1.gif)

## Plugin's features: 

* Support for WordPress Gutenberg
* Display file size and file extension. 
* Create a download button link with shortcode with options
* Link your download button to anywhere on the web where it's publicly available.
* Allow free music download, video download, PDF download, spreadsheet file download and more.
* Hide download link
* Support for external download link 
* Countdown - Wait before download, you can specify how many seconds you want the user to wait before download starts.
* Show the user a message while waiting for the download to start. 
* Shows download file extension for 'pdf','mp3','mov','zip','txt','doc','xml','mp4','ppt' and images ( png, gif, jpg, jpeg, bmp)
* Support for htm, html, ps, tex, xml, txt, csv, xlsx (Microsoft Excel), pptx (Microsoft PowerPoint), js, css, php
* Open external download in new tab
* Force file download


# Basic Usage 
## Gutenberg Block
1. Open the post you want to add a download link to, click on add block icon (+).
2. Under Media, click on the Download Button icon.
3. Click on the button to change the title, click on the download icon next to the button to upload a file for download.
4. Enter a suitable title in the text box, the default title is download. Wola! :punch:

## Shortcode
Open the post or page you want to add download button to, paste the shortcode example below

```
[quick_download_button title="Download" url="http://yoursite/wp-content/upload/fileto_download.pdf"]
```
The url value needs to be replaced with your media link URL. To change the title, enter a different text in the title value. The default value is Download.

# Additional Shortcode Usage

## Use external URL

To use external url, add url_external attribute

```
[quick_download_button title="Download" url_external="https://google.com"]
```

## Auto calculate file size. 

To let the plugin generate file size, make sure the file is in the WordPress upload directory e.g wp-content/upload and change filesize value to 1 like below

```
[quick_download_button filesize="1" title="Download" url="http://yoursite/wp-content/upload/fileto_download.pdf"]
```

## Display file size manually

The plugin can calculate the file size for you by enter 1 in filesize value. You don't have to enter it manually but in case, enter the file size value in the filesize attribute like below.

```
[quick_download_button file_size="14.5MB" title="Download" url="http://yoursite/wp-content/upload/fileto_download.pdf"]
```


## Hide file extention icon
To hide file extension icon, set extension value to 0. Note, this will also hide extension text.

```
[quick_download_button  title="Download" file_size="1" extension="0" url="http://yoursite/wp-content/upload/fileto_download.pdf"]
```

## Show file extention icon and text

To show both file extension icon and text,  set extension value to 1 and extension_text to 1

```
[quick_download_button  title="Download" file_size="1" extension="1" extension_text="1"  url="http://yoursite/wp-content/upload/fileto_download.pdf"]
```



# To use in a theme file (Developers)

```
echo do_shortcode('[quick_download_button title="Download" url="http://yoursite/wp-content/upload/fileto_download.pdf"]');
```
