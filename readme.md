# Silverstripe Cookie Consent

[![Version](http://img.shields.io/packagist/v/innoweb/silverstripe-cookie-consent.svg?style=flat-square)](https://packagist.org/packages/innoweb/silverstripe-cookie-consent)
[![License](http://img.shields.io/packagist/l/innoweb/silverstripe-cookie-consent.svg?style=flat-square)](license.md)

## Overview

This module provides cookie consent popups and a cookie policy page. 

This module is based on [TheBnl's cookie consent module](https://github.com/TheBnl/silverstripe-cookie-consent). Thanks for your work and inspiration!

> [!WARNING]
> While we try to tick as many legal boxes as we can, we give no warranty for this module to adhere to any legislation, including GDPR.
> We are not lawyers and we are not responsible for any legal consequences of using this module.

## Requirements

* Silverstripe CMS 5.x

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

The following cookie groups are available by default:
- Necessary
- Analytics
- Marketing
- Preferences
- External

## Global Privacy Control (GPC)

Adheres to the Sec-GPC HTTP header and sets consent to necessary cookies only.

By default, this is enabled globally. If you wish to only use this for specific countries, you can chnage the setting
as follows:

```yaml
Innoweb\CookieConsent\CookieConsent:
  global_privacy_control:
    - US
```

## Geo location and juristiction specific consent solutions

This module covers multiple solutions for multiple jurtistictions. 

The module itself doesn't provide geo location, but relies on your CDN to provide the country code in a HTTP header. 
To use your CDN's geo location capability, you can configure the HTTP header that should be used to retrieve the country
code transmitted by the CDN request:

```yaml
Innoweb\CookieConsent\CookieConsent:
  geolocation_header_name: 'X-Country-Code'
```

Once a geo location header is configured, the following options are enabled:

**1. Opt-In Cookie Consent Popup**

Adheres to the EU Cookie Law (GDPR).

By default this is enabled for all European countries, as well as Brazil, Canada, China, India, Japan, Mexico, Singapore, 
South Africa, South Korea and Türkiye. 

Make sure you have a link in the footer to the privacy policy and cookie policy pages.

**2. Opt-Out Popup**

By default this is not enabled for any country.

Make sure you have a link in the footer to the cookie policy page, labelled "Your privacy choices" or similar.

**3. Do-Not-Sell Popup**

By default this is enabled for the US.

Make sure you have a link in the footer to the cookie policy page, labelled "Do not sell or share my personal information" or "Your privacy choices".

### Default consent behaviour if geo location is enabled

If the geo location is set to a country that is not covered by any of the above options, the default behaviour is to 
enable all cookies and not show any consent popup.

### Country override for testing

In Dev and Test mode, you can test the country specific consent by adding a `?country=XX` query parameter to the URL.

## Default Content

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

You can also configure the requirement of the default css styles and js.

```yaml
Innoweb\CookieConsent\CookieConsent:
  include_css: true
  include_js: true
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

You can check for consent given in your PHP code by calling

```php
if (CookieConsent::check('Analytics')) {
    // include analytics script
}
```

In templates, you can check for consent given using

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
