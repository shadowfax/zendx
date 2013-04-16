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
		
		// Check the requested locale is available
		if (Zend_Registry::isRegistered('Zend_Translate'))
		{
			$translator = Zend_Registry::get('Zend_Translate');
			if (!$translator->isAvailable($language)) {
				$request->clearParams();
				throw new Zend_Controller_Dispatcher_Exception("The language '{$language}' has to be added before it can be used.", 404);
			}
		}
		
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
	
	public function dispatchLoopShutdown()
	{
		// Check the language tag is there!
		
		$response = $this->getResponse();
		$headers = $response->getHeaders();
		foreach($headers as $header)
		{
			//Do not proceed if content-type is not html/xhtml or such
			if($header['name'] == 'Content-Type' && strpos($header['value'], 'html') === false) {
				return;
			}
		}
		
		// Get the html tag
		// Load the page
		$html = $response->getBody();
		
		if (preg_match('/(<html>|<html [^>]*>)/i', $html, $matches) > 0) {
			$tag = trim($matches[0]);
			
			// Is the lang attribute present?
			$locale = null;
			if (preg_match('/ lang="[^"]+"| lang=\'[^\']+\'/i', $tag) == 0) {
				if (Zend_Registry::isRegistered('Zend_Locale')) {
					$locale = Zend_Registry::get('Zend_Locale');
				} 
				
				if (empty($locale)) {
					if (Zend_Registry::isRegistered('Zend_Translate')) {
						$translate = Zend_Registry::get('Zend_Translate');
						$locale = $translate->getLocale();
					}
					
					if (empty($locale)) {
						$locale = $this->getDefaultLocale();
					}	
				}
				
				// Make sure it is a Zend_Locale and not a string
				$locale = new Zend_Locale($locale);
				
				$new_tag  = substr($tag, 0, strlen($tag) - 1);
				$new_tag .= ' lang="' . $locale->getLanguage() . '"';
				$new_tag .= ">";
				
				$html = preg_replace('/' . $tag . '/i', $new_tag, $html);
				$response->setBody($html);
			}	
		}
		
	}
}