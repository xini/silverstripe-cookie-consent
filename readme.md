# Silverstripe Cookie Consent

[![Version](http://img.shields.io/packagist/v/innoweb/silverstripe-cookie-consent.svg?style=flat-square)](https://packagist.org/packages/innoweb/silverstripe-cookie-consent)
[![License](http://img.shields.io/packagist/l/innoweb/silverstripe-cookie-consent.svg?style=flat-square)](license.md)

## Overview

Creates a cookie consent popup and cookie policy page. 

While we try to tick as many legal boxes as we can, we give no warranty for this module to adhere to any legislation, including GDPR.

This is an amended and simplified version of [TheBnl's cookie consent module](https://github.com/TheBnl/silverstripe-cookie-consent). Thanks for your work and inspiration!

## Requirements

* Silverstripe CMS 5.x

Note: this version is compatible with Silverstripe 5. For Silverstripe 4, please see the [2 release line](https://github.com/xini/silverstripe-cookie-consent/tree/2).

## Installation

Install the module using composer:
```bash
composer require innoweb/silverstripe-cookie-consent
```

Then run dev/build.

Include the popup template in your base Page.ss
```html
<% include CookieConsent %>
```

## Configuration
You can configure the cookies and cookie groups trough the yml config. You need to configure by provider, 
for providers the dots are converted to underscores e.g. ads.marketingcompany.com becomes ads_marketingcompany_com.

By configuring cookies trough yml you can check for consent in your code and make the necessary changes e.g. require
the analytics or other cookies or skip placing them.

The texts for the configured cookies are editable trough the Site Config, here other cookies can also be added by CMS
users.
For example if a site user decides to embed a Youtube video he or she can specify the cookies that are placed by Youtube.
I reccomend the following three groups to be created, these have default content, of course you are free to configure 
groups as you see fit.

```yaml
Innoweb\CookieConsent\CookieConsent:
  cookies:
    Necessary:
      local:
        - PHPSESSID
        - CookieConsent
    Marketing:
      ads_marketingcompany_com:
        - _track
    Analytics:
      local:
        - _ga
        - _gid
```

This module comes with some default content for cookies we've encountered before. If you want to set default content 
for these cookies yourself that is possible trough the lang files. If you have cookie descriptions that are not in 
this module, contributions to the lang files are much appreciated!

The files are structured as such:

```yaml
en:
  CookieConsent_{provider}:
    {cookie}_Purpose: 'Cookie description'
    {cookie}_Expiry: 'Cookie expire time'
  # for cookies from your own domain:
  CookieConsent_local:
    PHPSESSID_Purpose: 'Session'
    PHPSESSID_Expiry: 'Session'
  # for cookies from an external domain:
  CookieConsent_ads_marketingcompany_com:
    _track_Purpose: 'Cookie description'
    _track_Expiry: 'Cookie expire time'
```

You can also configure the requirement of the default css styles.

```yaml
Innoweb\CookieConsent\CookieConsent:
  include_css: true
```

If your site uses multiple domains (e.g. domain.com and domain.de), you can configure the module to set consent 
cookies for all hosts allowed through SS_ALLOWED_HOSTS config:

```yaml
Innoweb\CookieConsent\CookieConsent:
  include_all_allowed_hosts: true
```

Caution: If you are using the `CookieConsent.cookie_domain` setting, `CookieConsent.include_all_allowed_hosts` will 
be ignored. 

## Usage

Then you can check for consent in your PHP code by calling

```php
if (CookieConsent::check('Analytics')) {
    // include analytics script
}
```

In templates, you can check for consent using

```html
<% if $CookieConsent(Analytics) %>
    // include analytics script
<% end_if %>
```

The CookieConsent popup fires a custom JavaScript event `updateCookieConsent` when the acceptance buttons in the popup
are clicked. You can use that event to conditionally load parts of your site depending on what cookies have been set.

### JavaScript example

Here an example that lazy-loads a video embed only if marketing cookies have been accepted:

Template:
```html
<% if $EmbedCode %>
<div class="VideoEmbed js-load-video" data-required-cookies="Marketing">
    <p class="message warning">Please accept marketing cookies to view this video.</p>
    <noscript><p class="message warning">Please enable JavaScript to view this video.</p></noscript>
	<div hidden>
		<!--
		$EmbedCode.RAW
		-->
	</div>
</div>
<% end_if %>
```

Script:
```javascript
let loadVideos = function() {
    
    // unwraps hidden embed code if correct cookie value is set
    let showVideo = function(video) {
        let requiredCookies = video.getAttribute('data-required-cookies');
        let cookieValue = Cookies.get('CookieConsent');
        if (requiredCookies === null || requiredCookies === '' || (cookieValue !== null && cookieValue.indexOf(requiredCookies) !== -1)) {
            let hidden = video.querySelector('[hidden]');
            let content = hidden.innerHTML;
            // get content from within html comment
            content = content.replace(/<!--([\S\s]+?)-->/g, '$1');
            // replace content
            video.innerHTML = content;
            video.classList.remove('js-load-video');
            video.classList.add('js-video-loaded');
            return true;
        }
        return false;
    };
    
    // intersection observer to load video if in viewport
    let observer = new IntersectionObserver(function(entries, observer) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                if (showVideo(entry.target)) {
                    observer.unobserve(entry.target);
                }
            }
        });
    });
    
    // load all videos and observe
    let videos = document.querySelectorAll('.js-load-video');
    videos.forEach(function(video) {
        observer.observe(video);
    });
};
// load videos on page init
loadVideos();
// load videos when JavaScript event is fired
document.addEventListener('updateCookieConsent', loadVideos);
```

## Default Pages

This module also sets up one default privacy policy page on running dev/build.

If you want to prevent that behaviour you should disable the `create_default_pages` config setting.

```yaml
Innoweb\CookieConsent\CookieConsent:
  create_default_pages: false
```

The page created is filled with bare-bones content. 
_Of course, it is your or your CMS users responsibility to alter these texts to make them fit your use case!_

## todo

* [ ] Remove `Innoweb\CookieConsent\Control\CookieJar` workaround for SS6
 
## License

BSD 3-Clause License, see [License](license.md)
