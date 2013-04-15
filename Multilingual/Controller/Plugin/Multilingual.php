<?php

class ZendX_Multilingual_Controller_Plugin_Multilingual extends Zend_Controller_Plugin_Abstract
{

	protected function getDefaultLocale()
	{
		$translator = null;
		
		
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
		
		// Set the default language
		if (strcasecmp($language, "www") === 0) {
			$locale = $this->getDefaultLocale();
			$language = $locale->getLanguage();
		}
		
		// Just in case the country code was sent
		// replace dashes with underscores
		$language = preg_replace('/\-/', '_', $language);
		
		// Set the translator language
		if (Zend_Registry::isRegistered('ZendX_Multilingual')) {
			$multilingual = Zend_Registry::get('ZendX_Multilingual');
			$multilingual->setLocale(new Zend_Locale($language));
		} else if (Zend_Registry::isRegistered('Zend_Translate')) {
			$translator = Zend_Registry::get('Zend_Translate');
			$translator->setLocale(new Zend_Locale($language));
		}
		
		// Set global param in router
		$front = Zend_Controller_Front::getInstance();
		$router = $front->getRouter();
		$router->setGlobalParam('language', $language);
	}
}