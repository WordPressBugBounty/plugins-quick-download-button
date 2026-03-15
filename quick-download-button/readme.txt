=== Quick Download Button ===
Contributors: kusimo
Donate link: https://www.buymeacoffee.com/kusimo
Tags: download button, file download, download link, countdown timer, media download
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.0
Tested up to: 6.7
Stable tag: 1.2.7
Requires PHP: 7.4

Add a stylish, customizable download button to any post or page with block and shortcode support.

== Description ==

**Quick Download Button** makes it easy to add professional download buttons anywhere on your WordPress site. Whether you are sharing PDFs, music, videos, or zip archives, this plugin gives you full control over how your download buttons look and behave.

**Key Features:**

* Works with both the **Gutenberg block editor** and the **Classic Editor** (via shortcode)
* **4 button styles** — Large, Mid, Small, and Basic
* **Countdown timer** — make visitors wait before the download starts
* **File size display** — auto-calculated or manually specified
* **File type icon** — automatically detected for common file types
* **Access control** — restrict downloads by user role or login status
* **Hide download links** — protect the real URL from casual inspection
* **External URL support** — link to files hosted anywhere
* **Force download** — bypass the browser's default open-in-tab behavior
* **Open in new tab or same window**
* **Custom button colors, borders, padding, and alignment**
* **Multisite compatible**
* **Optimized loading** — CSS and JS only load on pages that use the button

**Supported file types for icon display:**
`pdf`, `mp3`, `mov`, `zip`, `txt`, `doc`, `xml`, `mp4`, `ppt`, `htm`, `html`, `ps`, `tex`, `csv`, `xlsx`, `pptx`, `js`, `css`, `php`, and common images (`png`, `gif`, `jpg`, `jpeg`, `bmp`).

== Installation ==

1. Upload the `quick-download-button` folder to the `/wp-content/plugins/` directory.
2. Activate **Quick Download Button** through the 'Plugins' menu in WordPress.
3. Use the Gutenberg block or the `[quick_download_button]` shortcode to add download buttons to your content.

== Basic Usage ==

**Shortcode**

Paste the shortcode into any post or page:

`[quick_download_button title="Download" url="https://yoursite.com/wp-content/uploads/file.pdf"]`

Replace the `url` value with your file's URL and `title` with your preferred button text.

**Gutenberg Block**

1. Open the post or page editor and click the **Add Block** icon (+).
2. Search for **Download Button** (found under the Media category) and click to insert it.
3. Click the button to set its title and click the download icon to select or upload your file.
4. Adjust colors, countdown, and file size in the block settings panel on the right.

To hide the file size in the block editor, add `hide-size` to the **Additional CSS class(es)** field in block settings.

== More Shortcode Usage ==

**Open in a new window**

`[quick_download_button title="Download" open_new_window="true" url_external="https://example.com/file.zip"]`

**Custom color, countdown timer, and wait message**

`[quick_download_button title="Download" color_bg="#ffc107" wait="15" msg="Please wait 15 seconds..." url_external="https://example.com/file.zip"]`

**Link to an external URL**

`[quick_download_button title="Download" url_external="https://example.com/file.zip"]`

**Auto-calculate file size**

The file must be in your WordPress uploads directory:

`[quick_download_button file_size="1" title="Download" url="https://yoursite.com/wp-content/uploads/file.pdf"]`

**Manually specify file size**

`[quick_download_button file_size="14.5MB" title="Download" url="https://yoursite.com/wp-content/uploads/file.pdf"]`

**Hide file extension icon**

`[quick_download_button title="Download" extension="0" url="https://yoursite.com/wp-content/uploads/file.pdf"]`

**Show file extension icon and text**

`[quick_download_button title="Download" extension="1" extension_text="1" url="https://yoursite.com/wp-content/uploads/file.pdf"]`

**Change button style**

`[quick_download_button button_type="small" title="Download Now" url_external="https://example.com/file.zip"]`

Button type options: `large`, `mid`, `small`, `basic`

**Add a border**

`[quick_download_button button_type="small" border_width="2" border_style="solid" border_color="black" title="Download Now" url_external="https://example.com/file.zip"]`

**Round the corners**

`[quick_download_button button_type="small" border_radius="9" title="Download Now" url_external="https://example.com/file.zip"]`

**Change icon color**

`[quick_download_button button_type="large" color_icon_dark="false" color_bg="black" color_font="#FFF" title="Download Now" url_external="https://example.com/file.zip"]`

**Change alignment**

`[quick_download_button button_type="small" align="left" title="Download Now" url_external="https://example.com/file.zip"]`

**Countdown with file size**

`[quick_download_button button_type="basic" wait="10" msg="Please wait..." file_size="40MB" title="Download Now" url_external="https://example.com/file.zip"]`

== For Developers: Using in a Theme File ==

`echo do_shortcode('[quick_download_button title="Download" url="https://yoursite.com/wp-content/uploads/file.pdf"]');`

== Shortcode Attributes ==

| Attribute | Description | Example value |
|---|---|---|
| `title` | Button label text | `"Download Now"` |
| `url` | URL of a file in your WordPress uploads folder | `"https://yoursite.com/wp-content/uploads/file.pdf"` |
| `url_external` | URL of a file hosted outside WordPress | `"https://example.com/file.zip"` |
| `file_size` | Set to `1` to auto-detect, or enter a value manually | `"1"` or `"14.5MB"` |
| `extension` | Show (`1`) or hide (`0`) the file extension icon | `"1"` |
| `extension_text` | Show (`1`) the extension as text alongside the icon | `"1"` |
| `open_new_window` | Open the link in a new tab (`true`) or same window (`false`) | `"true"` |
| `wait` | Seconds to wait before the download link appears | `"15"` |
| `msg` | Message shown during the countdown | `"Please wait..."` |
| `button_type` | Button style preset | `large` / `mid` / `small` / `basic` |
| `color_bg` | Button background color (hex or CSS color name) | `"#ffc107"` |
| `color_font` | Button text color | `"#ffffff"` |
| `color_icon_dark` | Use dark icon (`true`) or light icon (`false`) | `"false"` |
| `border_width` | Border thickness in pixels | `"2"` |
| `border_style` | CSS border style | `"solid"` |
| `border_color` | Border color | `"black"` |
| `border_radius` | Corner rounding in pixels | `"9"` |
| `align` | Button alignment | `left` / `center` / `right` |
| `padding` | Inner padding (CSS shorthand) | `"10px 20px"` |

== Frequently Asked Questions ==

= Can this plugin hide (protect) download links? =

Yes. The plugin can conceal the real download URL from the page source, making it harder for users to grab the direct link without going through the button.

= Does it work with the Classic Editor? =

Yes. Use the `[quick_download_button]` shortcode in the Classic Editor just as you would in any text or HTML block.

= Does it work with the Gutenberg block editor? =

Yes. Search for **Download Button** in the block inserter (under the Media category).

= Can it display the file size automatically? =

Yes, for files stored in your WordPress uploads directory. Set `file_size="1"` in the shortcode or enable it in the block settings panel.

= Can I set a countdown before the download link appears? =

Yes. Use the `wait` attribute (number of seconds) and an optional `msg` attribute for the countdown message. Set `msg=""` to hide the message entirely.

= Can I restrict downloads to logged-in users or specific roles? =

Yes. Access control based on login status and user roles is supported via the block settings or shortcode.

= Can I link to files hosted on other websites? =

Yes. Use the `url_external` attribute for any publicly accessible URL.

= What file types show an icon? =

`pdf`, `mp3`, `mov`, `zip`, `txt`, `doc`, `xml`, `mp4`, `ppt`, `htm`, `html`, `ps`, `tex`, `csv`, `xlsx`, `pptx`, `js`, `css`, `php`, and common images (`png`, `gif`, `jpg`, `jpeg`, `bmp`).

= How do I hide the file size in the Gutenberg block? =

Add `hide-size` to the **Additional CSS class(es)** field in the block settings sidebar.

== Donations ==

If this plugin saves you time and you would like to support its continued development, please consider [buying me a coffee](https://www.buymeacoffee.com/kusimo). Thank you!

== Documentation & Source Code ==

To report issues or contribute, visit the [GitHub repository](https://github.com/kusimo/quick-download-button).

== Screenshots ==

1. Large button style with file size and extension icon.
2. Small button style example.
3. Countdown timer in action before the download link appears.
4. Button styles
5. Gutenberg block settings panel.

== Changelog ==

= 1.2.7 - March 15, 2025 =
* Fixed: Fatal error "ValueError: Path cannot be empty" when using download block with internal files
* Technical: Removed incorrect `esc_url()` sanitization that was stripping local file paths
* Improved: File paths are now handled correctly for both internal and external downloads

= 1.2.6 - November 23, 2023 =
* Optimized resource loading: CSS and JavaScript files now only load on pages that contain a Quick Download Button.

= 1.2.5 - April 19, 2023 =
* Fixed a 404 error that occurred when the Quick Download Button page was deleted. After deleting the page, deactivate and reactivate the plugin to resolve.

= 1.2.4 - March 13, 2023 =
* Fixed nonce verification failure when the download link is an external URL.

= 1.2.3 - August 29, 2022 =
* Added access control — restrict downloads by user role and login status.

= 1.2.0 - August 27, 2022 =
* Fixed CSS for the large button style to work correctly after the upgrade.

= 1.0.9 - August 27, 2022 =
* Added 3 new button styles: small, medium, and basic.
* Added button alignment support: center, left, or right.

= 1.0.8 - August 13, 2022 =
* Fixed the wait message not displaying during the countdown timer.

= 1.0.5 - July 23, 2022 =
* Added multisite support.
* Added `wait` and `color_bg` attributes to the shortcode.

== Upgrade Notice ==

= 1.2.7 =
Fixes a fatal download error when using the Gutenberg block with internal files. Upgrade recommended.

= 1.2.6 =
Optimized loading: plugin assets now only load on pages that use the download button, improving page speed for all other pages.
