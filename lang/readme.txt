----------------------------------------------------------------------

  Axel pimped the Apache-status

  Language files

----------------------------------------------------------------------

In this directory are the language files.
Each language consists of 2 files: 1 php file and 1 javascript.
Both must have the same name but different extensions.

1) The php file conatains translated texts for my application.
2) The javascript file contains the translation for the jQuery plugin 
   datatable.
   Several translations you can find here.
   http://www.datatables.net/plug-ins/i18n

Remember to use htmlentities for special chars!
	
To enable the language in the dropdown add it in the config (config/config_user.json) as

    'selectLang': 'en,de,[your language]',

or
    'lang': '[your language]',

to use it as default language.

A helper tool you get with dump, i.e. 
http://localhost/apachestatus1/?&view=dump.php 
... on tab "$aLang"

----------------------------------------------------------------------
