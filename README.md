ZendX (Zend eXtensions)
=======================

Setup
-----
In order to autoload the library and make everything work we should edit
application.ini
	; --- ZendX ---
	autoloaderNamespaces.ZendX = "ZendX_"
	resources.view.helperPath.ZendX_View_Helper = APPLICATION_PATH "/../library/ZendX/View/Helper"

ZendX_Validate_Username
-----------------------

Validates a username. If an "@" is detected then it validates the 
username as an email address.