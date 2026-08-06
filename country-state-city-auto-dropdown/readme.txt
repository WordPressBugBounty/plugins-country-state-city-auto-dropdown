=== Country State City Dropdown CF7 ===
Contributors: TrustyPlugins
Donate link: https://trustyplugins.com/
Tags: country dropdown,states,contact form 7,forms,cities
Requires at least: 4.8
Tested up to: 7.0
Stable tag: 2.8.1
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add country state city dropdown CF7 in contact form 7 plugin. In PRO you can use these features on any type of form.

== Description ==
Country dropdown for contact form 7 with dynamic states and cities. Country State City Dropdown CF7 plugin is an add-on of Contact Form 7 plugin to show country, state and city dropdown. This plugin add three new form tag fields that is  (form-tag: country drop-down) and (form-tag: state dropdown) and (form-tag: city dropdown) in Contact form 7.

<a href='https://trustyplugins.com' target='_blank'>Buy PRO</a>  &nbsp; &nbsp;<a href='https://trustyplugins.com' target='_blank'>See Live Demo(PRO)</a>

<h3>Country State City dynamic dropdown PRO supports to any form.</h3>
<a href='https://trustyplugins.com' target='_blank'>Buy PRO</a>

<h4>Features Of PRO Plugin</h4>
<blockquote>
<p>1. Supports to any type of Form (WP Forms, Ninja, Divi Contact Module, Elementor Module, Custom PHP Form etc).</p>
<p>2. Select Default Country with User's IP.</p>
<p>3. Select Default Specific Country from list of All Countries.</p>
<p>4. Select Specific one or more Countries to display in the dropdown.</p>
<p>5. If State/City is missing then shows input text field to enter manually.</p>
<p>6. Have feature to add missing states/cities manually.</p>
<p>7. Multiple forms on same page.</p>
<p>8. Multiple country/state/city fields in same form.</p>
<p>9. Append only Cities from Country Dropdown.
<p>10. See more at Official Website <a href='https://trustyplugins.com'>Trusty Plugins</a>
</blockquote>



This helps you in creating a country drop-down list with state and city. The tag field will automatically add countries name in standard drop-down field of contact form 7. State and city auto populate according to selected country from country dropdown field.

How to add the fields in the contact form 7 
1.) Once you have installed, activated the Country State City Auto Dropdown plugin.
2.) Add the form-tag  "country drop-down" and  "state dropdown" and "city dropdown"  to your form and save the changes.

Optional: if you use two location sets in one form, add the same group option on each tag, e.g. [country_auto* country-shipping group:shipping].

City is optional — country + state only works if you omit the city drop-down tag.

Use the tag generator to set a custom placeholder (enter text and check “Use this text as the placeholder”).

Requirments:
* Contact form 7 must be active plugin.

= Recommended Plugins =
The following plugin is recommended :
* [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) by takayukister – With Conact Form 7, you can use this plugin. Without contact form 7 this plugin have no needs.

== Installation ==

1. Upload the entire `country-state-city-dropdown-cf7` folder to the `/wp-content/plugins/` directory.
1. Kindly make sure 'contact form 7' plugin active before activate this plugin.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Add form tags in desired contact form

You will find three new field types in your contact form 7 field list.

== Frequently Asked Questions ==

= How to set preferred countries list? =

All countries display in the free version. Filtering countries, default country, and IP detection are available in the PRO version.

= How to set State list? =

All States will display automatically according to selected country from dropdown field.

= How to set City list? =

All Cities will display automatically according to selected country and state from dropdown field.

= Will updating delete my data? =

No. Plugin updates keep your existing country/state/city tables and form tags. To load the newer world dataset (2.8.1+), use Settings → Country State City Dropdown → “Update to latest dataset” (optional; replaces location rows only).

= Where does the country / state / city data come from? =

Location lists are based on the open [countries-states-cities-database](https://github.com/dr5hn/countries-states-cities-database) project, licensed under the Open Database License (ODbL). See that project for full attribution and share-alike terms.

= Dropdowns are empty after install =

Go to Settings → Country State City Dropdown and use “Install missing data”. Use Force reinstall only if data is corrupted.


== Screenshots ==

1. screenshot-1.png
2. screenshot-2.png
3. screenshot-3.png


== Changelog ==
= 2.8.1 =
* World location dataset refreshed (dr5hn CSC): 250 countries, 5,308 states, 152,970 cities
* India: West Bengal and Ladakh included in seed (no Patch required for new data)
* Settings: “Update to latest dataset” for existing sites (opt-in replace)
* Data attribution: Open Database License (ODbL) — countries-states-cities-database
* Admin notice when a newer geography pack is available

= 2.8.0 =
* Safer upgrades for existing sites: never re-seeds or renames tables when data already exists
* Adds database indexes for faster state/city AJAX
* Loads front-end script only when Contact Form 7 is present (filter to force legacy all-pages load)
* Translatable placeholders and validation messages
* Optional group:xxx tag option + smarter pairing for multiple location fields
* Settings page: data health, install missing data, softer Pro link
* Security/hardening: fixed SQL prepare usage, capability checks, sanitization
* Client-side AJAX cache for states/cities (faster when revisiting a country)
* Disabled + loading states on dependent dropdowns (aria-busy)
* Tag generator: placeholder, ID, class, and group fields
* Documented country + state only mode (city tag optional)
* Custom placeholders via CF7 placeholder option

= 2.7.6 =
* Form Tag generator upgraded to version 2 [contact form 7]

= 2.7.5 =
* Validation error issue has been fixed.

= 2.7.4 =
* State, City List not appending (Issue Fixed)

= 2.7.3 =
* Plugin vulnerability Fixed

= 2.7.2 =
* vulnerability Fixed

= 2.7.1 =
* ABSPATH function added to files

= 2.7 =
* Added support to multiple CF7 forms on page
* Added Missing 'West Bengal', 'Ladakh' State/Cities Updated
* A Patch feature added to fix missing State/Cities

= 2.6 =
* Javascript Bug Fixed

= 2.5 =
* Bug Fixed

= 2.4 =
* internationalization and localization

= 2.3 =
* Bug Fixed

= 2.2 =
* Plugin's PRO version has been released

= 2.1 =
* Fixed the issue of Alphabetical order of States/Cities

= 2.0.0 =
* Added many more missing cities
* Added missing States

= 1.0.0 =
* First version of plugin.

== Upgrade Notice ==
= 2.8.1 =
Updated world location dataset (250 / 5,308 / 152,970). Existing tables kept until you click “Update to latest dataset”. Includes West Bengal & Ladakh.
= 2.8.0 =
Safe upgrade for existing users: keeps your location data and form tags. Adds indexes, AJAX cache, loading states, custom placeholders, and a data health screen under Settings.
