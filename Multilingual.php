<?php

/**
 * Zend Framework Extensions
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 *
 * @category  ZendX
 * @package   ZendX_Multilingual
 * @copyright  Copyright (c) 2012-2013 Juan Pedro Gonzalez Gutierrez
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: Locale.php 24593 2012-01-05 20:35:02Z matthew $
 */

/**
 * Base class for multilingual sites
 *
 * @category  ZendX
 * @package   ZendX_Multilingual
 * @copyright  Copyright (c) 2012-2013 Juan Pedro Gonzalez Gutierrez
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class ZendX_Multilingual
{
	
	/**
	 * Translator object used in routes
	 * 
	 * @var Zend_Translate
	 */
	protected $_translator;
	
	/**
     * Generates the standard translation object
     *
     * @param  array|Zend_Config $options Options to use
     */
	public function __construct($options = array())
	{
		if ($options instanceof Zend_Config) {
            $options = $options->toArray();
		}
		
		// ToDo: Call set locale with the default locale defined in Zend Framework
		
		if (is_array($options)) {
			if (isset($options['translate'])) {
				$this->_translator = new Zend_Translate($options['translate']);
			}
		}
	}
	
	/**
	 * Get the translator for routes.
	 * 
	 * @return Zend_Translate|null
	 */
	public function getRouteTranslator()
	{
		return $this->_translator;
	}
	
	/**
	 * Set the translator for routes
	 * 
	 * @param Zend_Translate $translator
	 */
	public function setRouteTranslator($translator)
	{
		$this->_translator = $translator;
		return $this;
	}
	
	/**
     * Gets locale
     *
     * @return Zend_Locale|string|null
     */
    public function getLocale()
    {
    	// Get the locale
    	if (Zend_Registry::isRegistered('Zend_Locale')) {
    		$locale = Zend_Registry::get('Zend_Locale');
    		return $locale;
    	}
    	
    	// Get the locale from the default translator
    	if (Zend_Registry::isRegistered('Zend_Translate')) {
			$translator = Zend_Registry::get('Zend_Translate');
			$locale = $translator->getLocale($locale);
			if (null !== $locale) return new Zend_Locale($locale);
		}
		
		// get the locale from the route translator
		if (null !== $this->_translator) {
			$locale = $this->_translator->getLocale($locale);
			if (null !== $locale) return new Zend_Locale($locale);
		}
		
		// Get the default framework locale
		$locales = Zend_Locale::getDefault();
		if (is_array($locales)) {
			foreach ($locale as $locale => $value) {
				try {
		            $locale = Zend_Locale::findLocale($locale);
		            return new Zend_Locale($locale);
		        } catch (Zend_Locale_Exception $e) {
				}
			}
		}
		
		// Get the environmant locale
    	$locales = Zend_Locale::getEnvironment();
		if (is_array($locales)) {
			foreach ($locale as $locale => $value) {
				try {
		            $locale = Zend_Locale::findLocale($locale);
		            return new Zend_Locale($locale);
		        } catch (Zend_Locale_Exception $e) {
				}
			}
		}
		
		require_once 'Zend/Translate/Exception.php';
		throw new Zend_Translate_Exception("The default Language does not exist", 0);
    }
    
	/**
     * Sets locale
     *
     * @param  string|Zend_Locale $locale Locale to set
     * @throws Zend_Translate_Exception
     * @return Zend_Translate_Adapter Provides fluent interface
     */
	public function setLocale($locale)
	{
		// Set the locale
		if (Zend_Registry::isRegistered('Zend_Locale')) {
			$zend_locale = Zend_Registry::get('Zend_Locale');
			$zend_locale->setLocale($locale);
		}
		
		// Set the translator locale if present
		if (Zend_Registry::isRegistered('Zend_Translate')) {
			$translator = Zend_Registry::get('Zend_Translate');
			$translator->setLocale($locale);
		}
		
		// Set the route translator if present
		if (null !== $this->_translator) {
			try {
				$this->_translator->setLocale($locale);	
			} catch (Exception $e) {
				require_once 'Zend/Translate/Exception.php';
				throw new Zend_Translate_Exception("Error loading the locale for the router", 0, $e);
			}
			
		}
	}
	
	
	
}