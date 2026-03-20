=== Quick Download Button ===
Contributors: kusimo
Donate link: https://www.buymeacoffee.com/kusimo
Tags: download button, file download, countdown timer, gutenberg block, access control
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.6
Tested up to: 6.7
Stable tag: 1.3.0
Requires PHP: 7.4

Add stylish download buttons to any post or page — 7 styles, countdown, popup modal, access control. Gutenberg block and shortcode.

== Description ==

**Quick Download Button** lets you add professional download buttons anywhere on your WordPress site with minimal effort. Whether you are sharing PDFs, music, software, videos, or archives, every aspect of the button — style, colour, icon, countdown, and access rules — is controlled from a clean settings panel or a single shortcode.

---

**Seven button styles**

* **Large** — full-width panel with prominent icon area
* **Mid** — compact horizontal layout
* **Small** — minimal inline style
* **Basic** — clean, no-frills button
* **Pill** — modern rounded pill shape
* **Card** — elevated card with a panel background
* **Ghost** — outlined transparent style

---

**Countdown timer and popup modal**

Set a wait time in seconds before the download starts. During the countdown an animated spinner and optional message keep the visitor informed.

Add a **popup modal** to display any content while the countdown runs — AdSense, banner ads, custom HTML, shortcodes, or any other embed code. You control whether the visitor can dismiss the popup early or whether it stays until the download fires automatically.

---

**Full feature list**

* Works with both the **Gutenberg block editor** and the **Classic Editor** via shortcode
* **Button Row block** and `[quick_download_button_group]` shortcode — place multiple buttons side by side with gap, alignment, and stack-on-mobile controls
* **Countdown timer** — configurable wait time in seconds with a custom message
* **Popup modal during countdown** — show ads or custom HTML/shortcodes while the timer runs; optionally prevent dismissal until the download starts
* **File size display** — auto-calculated from the WordPress uploads folder, or enter any text manually (works with external URLs too)
* **File type icon** — automatically detected for common extensions
* **Custom download icons** — choose from 10 built-in icons or supply your own SVG
* **Custom file size icons** — same built-in set or custom SVG
* **Icon position** — left or right of the button label
* **Access control** — restrict downloads to logged-in users or specific roles
* **External URL support** — link to files hosted anywhere
* **Hidden download links** — the real file URL is never exposed in the page source
* **Force download** — bypasses the browser's open-in-tab behaviour for supported file types
* **Open in new tab or same window**
* **Custom button colour, font colour, border, border-radius, padding, and alignment**
* **Panel colour** for Card, Pill, and Ghost styles
* **Multisite compatible**
* **Optimised loading** — CSS and JS only enqueue on pages that contain a download button

---

**Supported file types for automatic icon display:**

`pdf`, `mp3`, `mov`, `zip`, `txt`, `doc`, `xml`, `mp4`, `ppt`, `htm`, `html`, `ps`, `tex`, `csv`, `xlsx`, `pptx`, `js`, `css`, `php`, and common images (`png`, `gif`, `jpg`, `jpeg`, `bmp`).

---

**Developer-friendly**

The plugin exposes several action and filter hooks so add-on plugins can extend it without editing core files:

* `qdb_editor_localize_data` — add data to the Gutenberg editor script
* `qdb_localize_script_data` — add data to the frontend script
* `qdb_shortcode_atts` — modify parsed shortcode attributes
* `qdb_user_can_access` — override or extend the access check
* `qdb_button_data_atts` — inject extra `data-*` attributes onto the button element
* `qdb_shortcode_output` — filter the final button HTML before output

== Installation ==

1. Upload the `quick-download-button` folder to `/wp-content/plugins/`.
2. Activate **Quick Download Button** through the **Plugins** menu in WordPress.
3. Add a download button using the Gutenberg block or the `[quick_download_button]` shortcode.

== Basic Usage ==

= Gutenberg Block =

1. Open the post or page editor and click the **Add Block** (+) icon.
2. Search for **Download Button** (listed under the Media category).
3. Click the block to set the button title, then click the upload icon to select or upload your file.
4. Configure colours, countdown, file size, access control, and popup in the block settings sidebar.

= Shortcode — minimal =

`[quick_download_button title="Download" url="https://yoursite.com/wp-content/uploads/file.pdf"]`

= Shortcode — external file in a new tab =

`[quick_download_button title="Download" url_external="https://example.com/file.zip" open_new_window="true"]`

= Shortcode — countdown with wait message =

`[quick_download_button title="Download" url_external="https://example.com/file.zip" wait="15" msg="Please wait 15 seconds..."]`

= Shortcode — popup modal during countdown =

The popup content goes between the opening and closing shortcode tags. It can include HTML, shortcodes, or ad embed code.

`[quick_download_button title="Download" url_external="https://example.com/file.zip" wait="15"]<h3>Sponsored by Example Co.</h3><p>Check out our latest offer.</p>[/quick_download_button]`

To prevent the visitor from closing the popup before the download starts:

`[quick_download_button title="Download" url_external="https://example.com/file.zip" wait="15" popup_closable="0"]Ad or message here[/quick_download_button]`

= Shortcode — button group (multiple buttons in a row) =

    [quick_download_button_group alignment="center" gap="16"]
      [quick_download_button title="Download v1.0" url_external="https://example.com/v1.zip"]
      [quick_download_button title="Download v2.0" url_external="https://example.com/v2.zip"]
    [/quick_download_button_group]

== More Shortcode Examples ==

**Auto-calculate file size** (file must be in your WordPress uploads folder)

`[quick_download_button file_size="1" title="Download" url="https://yoursite.com/wp-content/uploads/file.pdf"]`

**Manual file size** (works with any URL, including external)

`[quick_download_button file_size="14.5 MB" title="Download" url_external="https://example.com/file.zip"]`

**Hide the file extension icon**

`[quick_download_button title="Download" extension="0" url="https://yoursite.com/wp-content/uploads/file.pdf"]`

**Show file extension icon and text label**

`[quick_download_button title="Download" extension="1" extension_text="1" url="https://yoursite.com/wp-content/uploads/file.pdf"]`

**Change button style**

`[quick_download_button button_type="pill" title="Download Now" url_external="https://example.com/file.zip"]`

Available values: `large`, `mid`, `small`, `basic`, `pill`, `card`, `ghost`

**Custom colours**

`[quick_download_button button_type="ghost" color_bg="#0073aa" color_font="#ffffff" panel_color="#f0f8ff" title="Download Now" url_external="https://example.com/file.zip"]`

**Custom border**

`[quick_download_button button_type="small" border_width="2" border_style="solid" border_color="#333333" border_radius="9" title="Download" url_external="https://example.com/file.zip"]`

**Change icon colour (dark/light)**

`[quick_download_button button_type="large" color_icon_dark="false" color_bg="#000000" color_font="#ffffff" title="Download" url_external="https://example.com/file.zip"]`

**Place icon on the right**

`[quick_download_button icon_position="right" title="Download" url_external="https://example.com/file.zip"]`

**Restrict to logged-in users**

`[quick_download_button user_must_be="loggedin" title="Members Only Download" url_external="https://example.com/file.zip"]`

**Restrict to a specific role**

`[quick_download_button user_must_be="subscriber" title="Download" url_external="https://example.com/file.zip"]`

**Countdown with file size and message**

`[quick_download_button button_type="basic" wait="10" msg="Your download will begin shortly..." file_size="40 MB" title="Download Now" url_external="https://example.com/file.zip"]`

**Button alignment**

`[quick_download_button align="center" title="Download" url_external="https://example.com/file.zip"]`

**Stacked button group, mobile-friendly**

    [quick_download_button_group layout="stack" alignment="left" gap="8"]
      [quick_download_button title="Windows" url_external="https://example.com/app-win.zip"]
      [quick_download_button title="macOS"   url_external="https://example.com/app-mac.zip"]
      [quick_download_button title="Linux"   url_external="https://example.com/app-linux.tar.gz"]
    [/quick_download_button_group]

== For Developers: Using in a Theme or Template File ==

`echo do_shortcode('[quick_download_button title="Download" url="https://yoursite.com/wp-content/uploads/file.pdf"]');`

== Shortcode Attributes Reference ==

= [quick_download_button] =

| Attribute | Description | Default | Example value |
| --- | --- | --- | --- |
| `title` | Button label text | `"Download"` | `"Download Now"` |
| `url` | URL of a file in your WordPress uploads folder | — | `"https://yoursite.com/wp-content/uploads/file.pdf"` |
| `url_external` | URL of a file hosted outside WordPress | — | `"https://example.com/file.zip"` |
| `file_size` | `"1"` auto-detects size; any other value is displayed as-is | — | `"1"` or `"14.5 MB"` |
| `extension` | Show (`"1"`) or hide (`"0"`) the file type icon | `"1"` | `"0"` |
| `extension_text` | Also show the extension as text next to the icon | `"0"` | `"1"` |
| `open_new_window` | Open link in a new tab | `"false"` | `"true"` |
| `wait` | Seconds before the download starts | `0` | `"15"` |
| `msg` | Message shown during the countdown | `"Please wait..."` | `"Loading..."` |
| `button_type` | Button style | `"large"` | `large` / `mid` / `small` / `basic` / `pill` / `card` / `ghost` |
| `color_bg` | Button background colour | — | `"#ffc107"` |
| `panel_color` | Panel/card background colour (pill, card, ghost styles) | — | `"#f0f8ff"` |
| `color_font` | Button text colour | — | `"#ffffff"` |
| `color_icon_dark` | Use dark icon (`"true"`) or light icon (`"false"`) | `"true"` | `"false"` |
| `icon_id` | Built-in download icon | `"default"` | `default` / `cloud` / `circle` / `file-dl` / `inbox` / `save` / `bolt` / `folder` / `archive` / `info` / `chip` |
| `file_size_icon_id` | Built-in file size icon | `"folder"` | same set as `icon_id` |
| `icon_position` | Icon side of the button label | `"left"` | `"right"` |
| `border_width` | Border thickness in pixels | — | `"2"` |
| `border_style` | CSS border style | — | `"solid"` |
| `border_color` | Border colour | — | `"#333333"` |
| `border_radius` | Corner rounding in pixels | — | `"9"` |
| `align` | Button alignment on the page | — | `left` / `center` / `right` |
| `padding` | Vertical padding in pixels | — | `"12"` |
| `user_must_be` | Restrict access by role or login | — | `"loggedin"` / `"subscriber"` |
| `validate_msg` | Error message shown when access is denied | — | `"Members only."` |
| `popup_closable` | Allow (`"1"`) or prevent (`"0"`) dismissing the popup before countdown ends | `"1"` | `"0"` |

The `popup_content` is passed as enclosed shortcode content (between the opening and closing tags), not as an attribute. This allows unrestricted HTML, ad code, and nested shortcodes.

= [quick_download_button_group] =

| Attribute | Description | Default | Example value |
| --- | --- | --- | --- |
| `layout` | `"horizontal"` (side by side) or `"stack"` (column) | `"horizontal"` | `"stack"` |
| `stack_on_mobile` | Automatically stack buttons vertically on small screens | `"true"` | `"false"` |
| `alignment` | Justify the buttons within the row | `"left"` | `left` / `center` / `right` |
| `gap` | Gap between buttons in pixels | `"12"` | `"20"` |

== Frequently Asked Questions ==

= Can this plugin protect / hide download links? =

Yes. When a file is hosted in your WordPress uploads folder, the plugin stores the attachment ID rather than the URL. The real file URL is never printed in the page source. Downloads pass through a server-side check before the file is served.

= Does the countdown popup work with Google AdSense? =

Yes. Paste your AdSense or any other ad embed code as the shortcode content or enter it in the Popup Content textarea in the Gutenberg block. The plugin renders the content inside a modal overlay while the countdown runs. When the timer reaches zero the download fires and the popup closes automatically.

= Can I stop visitors from closing the popup early? =

Yes. Set `popup_closable="0"` in the shortcode or toggle **Allow user to close popup** off in the block settings. When disabled, the close button, overlay click, and Escape key are all inactive. The popup disappears only when the countdown ends.

= What happens when the timer finishes? =

The download opens automatically (in the same tab or a new one, depending on your setting), the loading indicator is removed, and the popup modal (if active) closes. No action is required from the visitor.

= Does it work with the Classic Editor? =

Yes. Use the `[quick_download_button]` shortcode in the Classic Editor or any text/HTML widget.

= Can I display the file size for an external URL? =

Yes. Enter the file size manually using the `file_size` attribute or the **Manual file size** field in the block editor. Auto-detection only works for files stored in the WordPress uploads folder.

= Can I group multiple buttons in one row? =

Yes. Use the **Button Row** block in Gutenberg (add it, then add Download Button blocks inside it), or wrap shortcodes in `[quick_download_button_group]`.

= Can I restrict who can download the file? =

Yes. Use `user_must_be="loggedin"` to require login, or `user_must_be="subscriber"` (or any role slug) to require a specific role. Visitors who do not meet the requirement see a configurable error message.

= Does loading the plugin slow down every page? =

No. The plugin's CSS and JavaScript only load on pages that actually contain a download button (block or shortcode). All other pages are unaffected.

= How do I hide the file size in the Gutenberg block? =

Toggle the **Show file size** option off in the block settings sidebar. In the shortcode, simply omit the `file_size` attribute.

= Can I use my own SVG icon? =

Yes. Both the download icon and the file size icon support custom SVG. In the shortcode use `custom_file_type_icon` or `custom_file_size_icon`. In the block editor use the **Custom SVG** textarea in the icon picker panel.

== Donations ==

If Quick Download Button saves you time and you'd like to support its development, [buy me a coffee](https://www.buymeacoffee.com/kusimo). Thank you!

== Documentation & Source Code ==

To report issues or contribute, visit the [GitHub repository](https://github.com/kusimo/quick-download-button).

== Screenshots ==

1. Large button style with file size and extension icon.
2. Small, Mid, and Basic button styles.
3. New Pill, Card, and Ghost button styles.
4. Countdown timer with loading spinner.
5. Popup modal during countdown — showing custom content.
6. Gutenberg block settings panel — Countdown & Popup section.
7. Gutenberg block settings panel — Button Icon and colours section.
8. Button Row block with multiple buttons in a horizontal layout.

== Changelog ==

= 1.3.0 - March 2025 =
* New: Three additional button styles — **Pill**, **Card**, and **Ghost** — joining the original Large, Mid, Small, and Basic.
* New: **Button Row block** (Gutenberg) and `[quick_download_button_group]` shortcode — arrange multiple buttons horizontally or stacked, with gap and alignment controls and an optional stack-on-mobile mode.
* New: **Popup modal during countdown** — display ads, custom HTML, shortcodes, or any embed code in an overlay modal while the timer counts down. Configured via an enclosed shortcode or a textarea in the block editor.
* New: **Popup closable control** — choose whether visitors can dismiss the popup early (close button, overlay click, Escape key) or whether it stays until the download fires. Default: closable.
* New: **Manual file size** — override auto-detected file size with any text, or set a file size for external URLs where auto-detection is not possible.
* New: **Built-in icon picker** for both the download icon and the file size icon — choose from 11 icons (default arrow, cloud, circle, file-dl, inbox, save, bolt, folder, archive, info, chip) or supply a custom SVG.
* New: **Icon position** — place the download icon to the left or right of the button label.
* New: **Panel colour** — separate background colour for the info panel on Pill, Card, and Ghost styles.
* Fixed: File type icons (PDF, ZIP, MP3, etc.) not rendering correctly inside Pill, Card, and Ghost buttons due to overflow clipping.
* Fixed: File size icon and text not properly aligned on the Large, Small, Mid, and Basic styles (missing flex layout on `p.down`).
* Improved: Frontend JavaScript fully rewritten — single interval timer drives both the inline spinner countdown and the popup countdown simultaneously, preventing double-trigger on fast clicks.
* Improved: `data-qdb-running` guard prevents countdown from stacking if the button is clicked while a timer is already running.

= 1.2.7 - March 2025 =
* Fixed: Fatal "ValueError: Path cannot be empty" when using the download block with internally hosted files.
* Technical: Removed incorrect `esc_url()` sanitisation that stripped local file paths.

= 1.2.6 - November 2023 =
* Optimised: CSS and JavaScript now only load on pages that contain a download button, improving performance site-wide.

= 1.2.5 - April 2023 =
* Fixed: 404 error when the Quick Download Button redirect page was deleted. Deactivate and reactivate the plugin to recreate it.

= 1.2.4 - March 2023 =
* Fixed: Nonce verification failure when the download link is an external URL.

= 1.2.3 - August 2022 =
* New: Access control — restrict downloads by user role and login status.

= 1.2.0 - August 2022 =
* Fixed: Large button style CSS broken after upgrade.

= 1.0.9 - August 2022 =
* New: Small, Mid, and Basic button styles.
* New: Button alignment — left, centre, right.

= 1.0.8 - August 2022 =
* Fixed: Wait message not displaying during the countdown timer.

= 1.0.5 - July 2022 =
* New: Multisite support.
* New: `wait` and `color_bg` shortcode attributes.

== Upgrade Notice ==

= 1.3.0 =
Major feature release. Three new button styles, popup modal with countdown, button row layout, manual file size, icon picker, and several CSS fixes. No breaking changes for existing buttons.

= 1.2.7 =
Fixes a fatal error on download when using the Gutenberg block with internally hosted files. Recommended for all users.
