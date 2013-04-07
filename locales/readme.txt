Localization in webDiplomacy
============================
26/12/2012 - Chris Kuliukas. This mod sponsored by Alex Lebedev.

In webDiplomacy localization is intended to allow webmasters to use the webDiplomacy code to create 
alternative language communities, without having to re-translate every time there is a change in the
official code.


Design choices
==============
=> There is only one locale per site.
		- This means a locale can make modifications as needed, without having to factor in other locales.
		
=> All localized code and data remains within locales/ . (The only exception being cache/d HTML and maps)
		- This lets upgrades be done to the main code without worrying about needing to retranslate.
		
=> Localization is done only when the data is just about to be sent to the screen; the database 
	and variant cache contains no localized data.
		- This means the core code doesn't have to be aware of localization; it can do its logic in English
		on any server.
		
=> If localization cannot be done (e.g. a translation isn't available) the system will silently 
	revert to English.
		- This allows translations to be done bit by bit over time.


Quick guide for locale developers
=================================
Take a copy of the English directory, rename to whatever your locale is, then change to that in your 
config.php's Config::$locale. Copy the Italian/layer.js, /layer.php, /lookup.js, and /lookup.php.txt 
into your locale's directory.

Alter layer.php and layer.js where necessary; changing references to the Italian folder to your variant,
and commenting out anything you don't need.

Translate credits.php, faq.php, help.php, gamecreate.php, etc; all scripts which contain mainly text, 
and so are simply translated outright.

Alter LargeMapNames.png and SmallMapNames.png to use territory names in your language (watch out to be
sure they look okay when superimposed on the actual maps.

Open up locales/lookup_examples.txt, and replace the contents of all Italian translations with the translations
in your language. (This will take you the longest). Once done upload the results using 
the admin control panel's Locales tab (you need to be admin to use this), which will parse & process the 
translations, saving them to lookup.php.txt and lookup.js.

If there are any unusual language quirks you may need help to make them work with the layer. Feel free to 
ask at forum.webdiplomacy.net for any help with translations.

Remember you don't need to translate everything; you can start small and chip away at it over time.


Design
======
locales/layer.php and locales/layer.js contain a set of hooks through which translatable data is passed. 
These are a set of functions intended to be lightweight enough that they can be ubiquitous without looking
too unpleasant.

The main example is l_t(); a function through which translatable text is passed.

These lightweight hooks will look for the presence of a $Locale object, which it can pass the translation
request through. The default $Locale objects are also in locales/layer.php and locales/layer.js .

These $Locale will, by default, simply let all translatable data pass directly through without modification.
This results in English, which is the default translation. If no translation operations are done the text will
come out as English.

In order for the $Locale object to perform translations it has to be extended and overwritten by language-
specific code, contained in e.g. locales/Italian/layer.php . 
header.php will require 'locales/'.Config::$locale.'/layer.php' , which gives the locale its opportunity to 
define its own extension of the Locale class, and replace the default $Locale with an instance of it.

This locale-specific class will then override the default translation functions with its own, which can 
then perform translations on translatable calls as necessary.


PHP-specific: layer.php
-----------------------
PHP has a more diverse set of hooks than JavaScript, but they operate in a similar way:
- l_t($text, $arg1, $arg2, [...]) -> $Locale->text($text, array $args)
	The main translation function, passes through a translatable string and any arguments which need to be
	substituted into it. e.g. l_t("Welcome %s", $User->name);
- l_r($include) -> $Locale->includePHP($include)
	Can replace PHP scripts which are being included. Intended mainly for the scripts which contain more text
	than code, like locales/English/faq.php , however if required it could be used to override other scripts
	(however this may cause issues when updating, and should be avoided).
- l_s($resource) -> $Locale->staticFile($resource)
	Replaces static resources such as CSS files or images.
- l_j(), l_jf() -> $Locale->includeJS(), $Locale->functionJS()
	Used to alter JavaScript functionality, see below.
- l_vc($variantClassName) -> $Locale->variantClass($variantClassName)
	Used to replace the name of a variant class. Can be used to change which class is loaded by a variant, 
	which basically allows l_r() PHP script replacement functionality but for variants which may not have 
	predictable file locations. Ideally variants would run their text / static files / javascript functions 
	through the functions above, and so wouldn't require actual translation.

JS-specific: layer.js
---------------------
JavaScript works in an almost identical way, except it is loaded in lib/html.php. The locales/layer.js is 
included, as is locales/English/layer.js. Both are passed through l_j(), which allows the PHP variant layer
to intercept the call to locales/English/layer.js and replace it with an alternative variant layer. 
(Note that this does not happen automatically based on Config::$locale , as with layer.php, because there 
may be no need to do JavaScript translations.)

It only includes l_t() for text, and l_s() for static files. 

The JavaScript locale layer is called before any other webDiplomacy scripts are run, via Locale.beforeLoad,
and after any other webDiplomacy scripts are run, via Locale.afterLoad.
This means with JavaScript files that need translation there are three ways:
- Use the l_t() or l_s() hooks, along with a customized Locale layer, to replace translatable content as it
	goes through (if possible this approach should be used).
- Use the l_jf() hook running from within PHP to replace a certain JavaScript function call with an alternative
	(most likely loaded in the layer.js). This is a good approach because it allows a targeted alteration
	of a specific JavaScript function call, but it only works of JavaScript functions referenced from PHP, not
	for functions which are called from within JavaScript / triggered by events.
- Use the l_j() hook running from within PHP to replace a certain JavaScript file being loaded with an 
	alternative script which is translated. (This is not ideal, since it needs the scripts to be retranslated
	when there are updates.)
- Use the Locale.beforeLoad / Locale.afterLoad functions to alter other JavaScript objects which need alteration,
	by e.g. overriding the functions / variables loaded by other code. (This is better than replacing a whole
	file, but also runs a smaller risk of breaking code)


Text translation
================
Text translation is done by taking translatable English text, and finding the translated version in a lookup.
In PHP this is usually loaded from a serialized array in lookups.php.txt , and in JavaScript it's loaded from 
lookups.js. Both of these files will generally contain the same lookups, both saved from a list of strings 
which are entered into the admin control panel via an uploaded text file, then converted into PHP and JavaScript
format.

Because all text translations go through code if there are odd language specific cases which can't be handled
using lookups they can be handled using code (the classic example is "la flotta"/"l'armata"; translations which
vary based on whether it's a fleet or army).


Tools for locale developers
===========================
Locales were designed so that people could translate as much as they like; 

- Serialize input
- Missed locale text
- 