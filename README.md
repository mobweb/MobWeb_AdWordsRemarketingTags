# MobWeb_AdWordsRemarketingTags extension for Magento

A simple Magento extension that creates an «AdWords Remarketing Tag» on every page on the frontend, according to: https://developers.google.com/adwords-remarketing-tag/parameters.

Currently, the following page types are supported:

- home
- searchresults
- category
- product
- cart
- purchase
- other

And the following parameters:

- ecomm_prodid
- ecomm_pagetype
- ecomm_totalvalue
- ecomm_category
- isSaleItem
- returnCustomer

However, adding a new page type or parameter should be fairly easy. Feel free to send a pull request. :)

To get a better idea of exactly what is included in a tag on each page, enable the debug option in the extension's settings (under *System -> Configuration -> Sales -> Google API -> AdWords Remarketing Tag*) and open your browser's console to see the parameters used on each page:

![Screenshot](http://mbwb.info/adwords-remarketing-tag-github/screenshot.png)

## Installation

Install using [colinmollenhour/modman](https://github.com/colinmollenhour/modman/).

## Configuration

Go to *System -> Configuration -> Sales -> Google API -> AdWords Remarketing Tag* to configure the extension.

## Questions? Need help?

Most of my repositories posted here are projects created for customization requests for clients, so they probably aren't very well documented and the code isn't always 100% flexible. If you have a question or are confused about how something is supposed to work, feel free to get in touch and I'll try and help: [info@mobweb.ch](mailto:info@mobweb.ch).