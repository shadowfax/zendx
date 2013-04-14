<?php

class ZendX_Multilingual_Controller_Plugin_Multilingual extends Zend_Controller_Plugin_Abstract
{

	protected function getDefaultLocale()
	{
		$translator = null;
		if (Zend_Registry::isRegistered('Zend_Translate')) {
			$translator = Zend_Registry::get('Zend_Translate');
		}
		
		// We have to determine the default language
		$defaultLocales = Zend_Locale::getDefault();
		if (count($defaultLocales) > 0) {
			foreach ($defaultLocales as $locale => $value) {
				if (Zend_Locale::isLocale($locale)) {
					if (null !== $translator) {
						if ($translator->isAvailable($locale)) {
							return new Zend_Locale($locale);
						}
					} else {
						// FIFO
						return new Zend_Locale($locale);
					}
					break;
				}
			}
		}

		// If we don't have a locale try to get it from the translator if available
		if(null !== $translator) {
			return new Zend_Locale($translator->getLocale());
		}
		
		// If we still don't have one we must throw an exception
		require_once 'Zend/Exception.php';
		throw new Zend_Exception('No default locale avialable');
		
		return null;
	}
	
	
	
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		$language = $request->getParam("language", "www");
		
		$translator = null;
		if (Zend_Registry::isRegistered('Zend_Translate')) {
			$translator = Zend_Registry::get('Zend_Translate');
		}	
		
		if (strcasecmp($language, "www") === 0) {
			$locale = $this->getDefaultLocale();
			$language = $locale->getLanguage();
		}
		
		// Set the translator language
		if (null !== $translator) {
			if ((Zend_Locale::isLocale($language)) && ($translator->isAvailable($language))) { 
				$translator->setLocale(new Zend_Locale($language));
			}
		}
		
		$front = Zend_Controller_Front::getInstance();
		$router = $front->getRouter();
		$router->setGlobalParam('language', $language);
	}
}