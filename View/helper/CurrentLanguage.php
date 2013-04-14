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
 * @category   ZendX
 * @package    ZendX_View
 * @subpackage Helper
 * @copyright  Copyright (c) 2012-2013 Juan Pedro Gonzalez Gutierrez
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_View_Helper_Abstract */
require_once 'Zend/View/Helper/Abstract.php';

/** Zend_Locale */
require_once 'Zend/Locale.php';

/**
 * Current Language view helper
 *
 * @category  ZendX
 * @package   Zend_View
 * @copyright  Copyright (c) 2012-2013 Juan Pedro Gonzalez Gutierrez
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class ZendX_View_Helper_CurrentLanguage extends Zend_View_Helper_Abstract
{
    /**
     * The locale
     * 
     * @var Zend_locale
     */
    protected $_locale;
        
	public function currentLanguage($defaultLocale = null)
	{
		return $this->getLocale($defaultLocale)->getLanguage();
	}
	
	/**
	 * Get the current locale.
	 * 
	 * @return Zend_Locale
	 */
	public function getLocale($defaultLocale = null)
	{
		if (null === $this->_locale) {
			// Get the translator if available
			$translator = null;
			require_once 'Zend/Registry.php';
            if (Zend_Registry::isRegistered('Zend_Translate')) {
                $translator = Zend_Registry::get('Zend_Translate');
            }
            
            // get the locale
            if (null === $translator) {
            	if (null != $defaultLocale) {
            		if ($defaultLocale instanceof Zend_Locale) {
            			if (Zend_Locale::isLocale($defaultLocale)) {
            				$this->_locale = $defaultLocale;
            			}
            		} elseif (is_string($defaultLocale)) {
            			if (Zend_Locale::isLocale($defaultLocale)) {
            				$this->_locale = new Zend_Locale($defaultLocale);
            			}
            		}
            	}

            	// Should I continue trying to figure out the locale?
            	if (null === $this->_locale) {
	            	$defaultLocale = Zend_Locale::getDefault();
			        if (count($defaultLocale) === 1) {
			        	$defaultLocale = array_keys($defaultLocale);
			        	$defaultLocale = $defaultLocale[0];
			        	if (Zend_Locale::isLocale($defaultLocale)) {
			        		$this->_locale = new Zend_Locale($defaultLocale);	
			        	}
			        }

			        // If everything went wrong default to english
			        if (null === $this->_locale) {
			        	// Default to english if nothing worked
			           	$this->_locale = new Zend_Locale("en");
			        }
            	}
            } else {
            	// Get the locale from the translator
            	$this->_locale = new Zend_Locale($translator->getLocale());
            }
		}
		
		return $this->_locale;
	}
	
}