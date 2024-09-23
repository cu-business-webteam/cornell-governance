=== Cornell In-Page Governance ===
Contributors: cgrymala
Donate link: https://cornell.edu
Tags: governance, notes, admin
Requires at least: 6.4
Tested up to: 6.5.3
Stable tag: 0.4.6
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Allows tracking and adding notes about the content, purpose, audiences, etc of individual pages

== Description ==

This plugin adds numerous fields to the page editor, allowing users to review their pages, provide certain relevant information about the page and its audiences, etc.

It also adds automated review messages that can be emailed out to a list of relevant users.

= Setup =

1. Go to Governance -> Audiences on each site and add the audiences that should be selectable for the Primary and Secondary Audience fields
1. Go to Governance -> Governance Settings on each site and make sure the settings make sense, then save them


== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/cornell-governance` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress


== Frequently Asked Questions ==

= What filters are available in this plugin? =

**Settings**

* `cornell/governance/capability` - filters the WordPress capability that determines whether a user is able to edit/manage the options and fields within this plugin.
* `cornell/governance/managing-office` - filters the name of the managing office that is in charge of reviewing the governance compliance.
* `cornell/governance/post-types` - filters the list of post types on which the governance information appears
* `cornell/governance/default-tasks` - filters the array of default tasks before they are added to the list on the page itself
* `cornell/governance/change-form/active` - filters whether the Change Form link should be displayed or not
* `cornell/governance/change-form/link-text` - filters the text that's used for the Change Form link
* `cornell/governance/change-form/url` - allows you to change the location of the form used for requesting changes
* `cornell/governance/change-form/props` - filters the array of query parameters appended to the change form URL when it's presented as a link (same as below)
* `cornell/governance/change-form-url/parameters` - filters the array of query parameters appended to the change form URL when it's presented as a link

**Reports**

_General_

* `cornell/governance/reports/current-user` - if a report should only show posts authored/owned by a specific user, use this filter to set that user ID

_Page List Report_

* `cornell/governance/page-list-table/columns` - filters the columns to be included in the WP_List_Table within the Page List report
* `cornell/governance/page-list-table/columns/visible` - filterswhich columns are visible in the Page List report
* `cornell/governance/page-list-table/columns/hidden` - filters which columns are hidden in the Page List Report
* `cornell/governance/page-list-table/columns/sortable` - filters which columns in the Page List report are sortable
* `cornell/governance/page-list-table/columns/default-orderby` - filters which column is the one used by default for sorting the information
* `cornell/governance/page-list-table/columns/query-args` - filters the query arguments that are used when querying posts for the Page List report
* `cornell/governance/page-list-table/data` - filters the data returned by the query to be displayed in the Page List report

_Unreviewed Pages Report_

* `cornell/governance/unreviewed-table/columns` - filters the columns to be included in the WP_List_Table within the Unreviewed Pages report
* `cornell/governance/unreviewed-table/columns/visible` - filters which columns are visible in the Unreviewed Pages report
* `cornell/governance/unreviewed-table/columns/hidden` - filters which columns are hidden in the Unreviewed Pages Report
* `cornell/governance/unreviewed-table/columns/sortable` - filters which columns in the Unreviewed Pages report are sortable
* `cornell/governance/unreviewed-table/columns/default-orderby` - filters which column is the one used by default for sorting the information
* `cornell/governance/unreviewed-table/columns/query-args` - filters the query arguments that are used when querying posts for the Unreviewed Pages report
* `cornell/governance/unreviewed-table/data` - filters the data returned by the query to be displayed in the Unreviewed Pages report

**Email Templates**

* `cornell/governance/emails/report-data` - filters the data shared with the email template. The first parameter is the array of report data. The second parameter is the name of the PHP class used to generate the email.

**Miscellaneous**

* `cornell/governance/textarea/value` - filters the value of a textarea field in meta boxes. The first parameter is the current value of the textarea; the second parameter is the HTML ID of the textarea field.

== Screenshots ==

1. A snapshot of the interface that a privileged user will see while editing a page
2. A snapshot of the interface a non-privileged user will see while editing a page
3. A snapshot of some of the basic reports that are available within the plugin
4. An example of the "Unreviewed Pages" report
5. An example of the Compliance Widget on the Dashboard for the current user


== Changelog ==

=== 0.4.6 ===

* Added `CORNELL_GOVERNANCE_EMAIL_CC` constant to allow users to force all messages to be CC'd to specific email addresses
* Added `CORNELL_GOVERNANCE_EMAIL_BCC` constant to allow users to force all messages to be BCC'd to specific email addresses

=== 0.4.5 ===

* Make default email templates more generic (removing any JCB-specific information)

=== 0.4.4 ===

* Implement email dispatches when a page is brought into compliance
    * Email message will be sent to page steward every time they complete a page review
    * Secondary contacts and liaisons will be CC’d on these messages when due date is within the next week (or already past)
* Add Site Name to all email subjects
* Re-enable the 7-day prompt to liaisons
* Change `Page Owner` to `Page Steward` in Secondary Prompt sent to Secondary Recipients

=== 0.4.3 ===

* Replace "Supervisor" with "Secondary Contact" in publicly-visible language
* Finish updating the Secondary Contact and Liaison email templates
* Remove secondary prompt from secondary contact list (they will only start receiving as of tertiary notice)
* Remove tertiary prompt from liaison contact list (they will only begin receiving as of "due today")

=== 0.4.2 ===

* Begin updating Web Steward email templates
* Implement Due Day email prompts

=== 0.4.1 ===

* Attempt to temporarily short-circuit all emails from prod/test/dev

=== 0.4.0 ===

* Re-implement different email schedules
* Fix email template-building
* Implement optional email debugging

=== 0.3.9 ===

* Stop page from loading when email cron task is triggered
* Force debug email address when appropriate
* Add debug information on cron task

=== 0.3.8 ===

* Removed extraneous report block

=== 0.3.7 ===

* Updated microcopy explaining how to save Governance Information for a page
* Attempt to fix email automation
* Fix subjects for prompt emails
* Add CSS to group Save Instructions together with Save button

=== 0.3.6 ===

* Fixes fatal error in some revisions

=== 0.3.5 ===

* Added message field above Save Governance Information button

=== 0.3.4 ===

* Fix fatal error in reports when date/time returns false
* Update access methods for email classes

=== 0.3.3 ===

* Began setting up email templates
* Fixed some uninitialized variables
* Set email headers to be merged, rather than overwritten

=== 0.3.2 ===

* Fixed the way commit messages were converted from the old format
* Ensured that commit messages are associated with the correct user

=== 0.3.0 ===

* Fixed commit messages being associated with multiple revisions (showing up as duplicate commits)
* Fixed time stamps on commit messages

=== 2023-09 ===

* Added automated email reports

=== 2023-04 ===

* Added reports and other functionality

=== 2022-12 ===

* This is the first version

== Upgrade Notice ==

* 0.4.6 - Added `CORNELL_GOVERNANCE_EMAIL_CC` and `CORNELL_GOVERNANCE_EMAIL_BCC` constants
* 0.4.5 - Make default email templates more generic
* 0.4.4 - Dispatches email message when page steward completes page review
* 0.4.3 - Updates the email templates to match the desired template designs
* 0.4.2 - Updates web steward email templates
* 0.3.6 - Fixes fatal error in some revisions
* 0.3.4 - Fixes fatal error on some page status lists
* 0.3.3 - Begins using email templates
* 0.3.2 - Fixes more issues with commit messages
* 0.3.0 - Fixes some issues with commit messages
* 2023-09 - This version implements automated email reports
* 2023-04 - Upgrade to get the newest features, including a host of useful reports
* 2022-12 - This is the first version
