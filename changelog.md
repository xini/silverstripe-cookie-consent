# Changelog

All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](http://semver.org/).

## [3.9.1]

* fix formatting

## [3.9.0]

* update default texts for cookie policy page and cookie categories after external review

## [3.8.0]

* fail silently and log error instead of throwing exception when checked cookie category is not defined

## [3.7.5]

* change wording of 'Accept necessary cookies' to 'Necessary cookies only'

## [3.7.4]

* improve removal of cookies when consent is withdrawn, covering cookies for higher level domains and cookies configured as wildcards (e.g. Google Analytics _ga_* cookies)

## [3.7.3]

* return empty array for CookieConsent::getConsent() if no consent has been given yet

## [3.7.2]

* fix gridfield config extension hook

## [3.7.1]

* fix redirect after form submit to update cookies for additional hosts

## [3.7.0]

* add setting of consent cookies for all hosts allowed through SS_ALLOWED_HOSTS config

## [3.6.0]

* wait for DOMContentLoaded before attaching acceptance event in case custom links have been added to dom

## [3.5.0]

* update frontend dependencies to gulp 5

## [3.4.1]

* fix typos in language files
* fix p in span tags in field content

## [3.4.0]

* simplify popup if only necessary cookies are configured

## [3.3.1]

* fix links in de and fr translations

## [3.3.0]

* add fluent config

## [3.2.5]

* fix styles of cookie checkboxes

## [3.2.4]

* fix PHP 8.1 deprecation warning

## [3.2.3]

* fix styles

## [3.2.2]

* fix heading level in popup

## [3.2.1]

* fix missing request variable

## [3.2.0]

* set cookie via js, fire js event

## [3.1.0]

* add xhr requests for accepting cookies

## [3.0.0]

* update for Silverstripe 5

## [2.2.0]

* add button to accept necessary cookies only
* update dependencies

## [2.1.2]

* update dependencies

## [2.1.1]

* fix german translation key for cookie form

## [2.1.0]

* hide popup using js if cookie is present. Necessary if page is cached.
* update dependencies

## [2.0.0]

rebuild most of the module using https://github.com/TheBnl/silverstripe-cookie-consent for inspiration:
* list individual cookies on cookie policy page
* add form to accept different cookie categories
* add translations

## [1.0.1]

* update dependencies
* update default texts
* fix check for existing cookie groups

## [1.0.0]

* initial release
