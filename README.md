ZendX (Zend eXtensions)
=======================

Setup
-----
In order to autoload the library and make everything work we should edit
application.ini
    ; --- ZendX ---
    autoloaderNamespaces.ZendX = "ZendX_"
    resources.view.helperPath.ZendX_View_Helper = APPLICATION_PATH "/../library/ZendX/View/Helper"

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