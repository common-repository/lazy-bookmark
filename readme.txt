=== Lazy Bookmark ===
Contributors: Freelynx  
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LTUB9HAHBXTHJ
Tags: website, registration, metadata, bookmark, links
Requires at least: 3.0.3
Tested up to: 3.0.5
Stable tag: 0.7
License: GPLv2

== Description ==

Lazy Bookmark records metadata information embeded in a website, namely url, title, author, keywords and description. This plugin might be useful when you want to save pages or domains of websites and share them to public. It's a bookmark-like. As an additional feature, this plugin may also allow public user to be contributed to the list of the url.


The plugin provides several different ready-to-use shortcodes:

1. [lbm_form] : Displaying the form for URLs to be submitted based on Admin selection.
2. [lbm_form_domain] : Displaying the form for submitting domain of the given URLs.
3. [lbm_form_page] : Displaying the form for submitting page of given URLs.
4. [lbm_list] : Displaying the list selected by administrator.
5. [lbm_list_domain] : Displaying the list of collected domain.
6. [lbm_list_page] : Displaying the list of collected pages.
7. [lbm_search] : Search the contents.

== Installation ==

1. Download Lazy Bookmark plugin
2. Extract the .zip package you've just downloaded
2. Upload all files and folders into your wp-content/plugins directory
3. Activate the plugin


== Screenshots ==
Screenshot is available on my blog 

http://freelynx.wordpress.com/2011/01/24/lazy-bookmark/


== Frequently Asked Questions ==

The more detail and technical FAQ is available in the admin page of the plugin.

= What does this plugin do? =

In a simple term: Records IP Address, Title, Authors, Keywords, and Description of a website.

= How can I use it? =

Just type a domain or website, i.e. http://yahoo.com in the form on the Lazy Bookmark Setting Page then press Submit. You'll find what this plugin actually do.

= Can I use this form on my frontend page? =

Yes you can. 

= Do I always need to write the protocol like 'http://' ? =

Absolutely! it's part of the valid URL.

= In that case what kind of protocol that is allowed? =

http, https, and ftp

= I got a strange behaviour when I use this plugin on my frontend page. What's wrong? =

It depends. But the first thing that you need to check is that the permalinks should not be set as a 'Default'. If this does not fix the issues then please email and let me know.
 
= Can I choose which metadata to be display? =

Yes, absolutely.
 
= Can I edit the url and its metadata? =

Sorry, no.

= Can I filter or search the result? =

Yes.

= Is this plugin a crawler? =

Nope. Not even remotely.

= Can I export the result as an XML or JSon? =

No. But soon in the next release.


== Changelog ==

= 0.7 =
* Now the uniqueness of a website is determined from the domain name, not IP address.
* no more excessive and long pagination. It has been replaced with dropdown menu.

= 0.6 =
* Fix bug: avoid collision with cUrlPage function

= 0.5 =
* Initial version  

== Upgrade Notice ==

= 0.7 =
Minimized the ugliness of pagination as it has been replaced with dropdown menu.

= 0.5 =
No upgrade notice at this moment

