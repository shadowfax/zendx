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
 * @package    ZendX_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2012-2013 Juan Pedro Gonzalez Gutierrez
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Controller_Plugin_Abstract */
require_once 'Zend/Controller/Plugin/Abstract.php';

/**
 * Render layouts
 *
 * @uses       Zend_Controller_Plugin_Abstract
 * @category   ZendX
 * @package    ZendX_Controller
 * @subpackage Plugins
 * @copyright  Copyright (c) 2012-2013 Juan Pedro Gonzalez Gutierrez
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class ZendX_Seo_Controller_Plugin_Seo extends Zend_Controller_Plugin_Abstract
{
	
	/**
	 * The SEO object
	 * 
	 * @var ZendX_Seo
	 */
	protected $_seo;
	
	/**
	 * Retrieve SEO object.
	 * 
	 * @return ZendX_Seo
	 */
	public function getSeo()
	{
		if (null === $this->_seo) {
			require_once 'Zend/Registry.php';
            if (Zend_Registry::isRegistered('ZendX_Seo')) {
                $this->_seo = Zend_Registry::get('ZendX_Seo');
            }
		}
		
		return $this->_seo;
	}
	
	/**
     * preDispatch() plugin hook -- render layout
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		$this->getSeo();
		
		// Get the SEO resource from registry
		if (null !== $this->_seo) {
			$view = Zend_Controller_Action_HelperBroker::getExistingHelper('ViewRenderer')->view;
			
			$keywords = $this->_seo->getKeywords();
			if (is_array($keywords)) {
				$keywords = implode(',', $keywords);
				$view->headMeta()->appendName('keywords', $keywords);
			}
			unset($keywords);
			
			// Canonical
			/*
			$view->headLink()->headLink(
				array(
					'rel' => 'canonical', 
					'href' => 'http://localhost/'
				)
			);
			*/
		}
		
		
	}
	
}