ZendX (Zend eXtensions)
=======================
This is _NOT_ the official ZendX but a personal implementation of things 
_I_ use together with Zend Framework.

Setup
-----
In order to autoload the library and make everything work we should edit
application.ini
    ; --- ZendX ---
    autoloaderNamespaces.ZendX = "ZendX_"
    resources.view.helperPath.ZendX_View_Helper = APPLICATION_PATH "/../library/ZendX/View/Helper"

Multilingual Site
-----------------
We can setup a multilingual site through the application.ini file.

	; Setting the router
	resources.multilingual.route = "host"
	; Setting up a translator for routes
    resources.multilingual.translate.adapter = "tmx"
    resources.multilingual.translate.data = APPLICATION_PATH "/languages/routes.tmx"
    
route parameter can be "host" or "path" (Being the default "path").
With host the locale will be part of the subdomain, for example:

    en.domain.com
    es.domain.com
    fr.domain.com

A default route to www.domain.com will be appended as the default subdomain.

When set as "path" the following format will be created:

    www.domain.com/en/
    www.domain.com/es/
    www.domain.com/fr/
    
If "translate" is defined the routes will allow for translations based on 
this transation adapter. It won't merge with the default translator which 
can be defined through "resources.translate". The adapter is defined the 
same way as Zend_Translate, the main difference is it will register as
"ZendX_Route_Translate".


ZendX_Seo
---------
It can be loaded from application.ini and allows for some SEO (Search Engine Optimization) features.

We can define the prefered domain if we are using multiple domain aliases 
for things as canonical links. Define some default keywords that should
appear on every page...

    ; --- ZendX_Seo Setup ---
    resources.seo.domain = "www.yourdomain.com"
    resources.seo.keywords = "this, that, other"
	resources.seo.robots = "follow,index"
	
resources.seo.robots defines the default robots policy.
   

ZendX_Validate_Username
-----------------------

Validates a username. If an "@" is detected then it validates the 
username as an email address.