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
	
	protected $_locale;
	
	// ToDo: Add constructor and allow options
	//       SEO object MUST be initialized here
	public function __construct()
	{
		$this->getSeo();
	}
	
	private $_processed = false;
	
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
            	$this->_locale = $translator->getLocale();
            }
		}
		
		return $this->_locale;
	}
	
	
	protected function getMetaTags($html)
	{
		preg_match_all('/<meta[^>]+name=\\"([^\\"]*)\\"[^>]+content=\\"([^\\"]*)\\"[^>]+>/i',  $html, $out,PREG_PATTERN_ORDER);

    	$meta['raw'] = $out[0];
    	$meta['name'] = $out[1];
    	$meta['content'] = $out[2];
    	
    	return $meta;
	}
	
	/**
     * preDispatch() plugin hook -- set the SEO tags
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     */
	public function postDispatch(Zend_Controller_Request_Abstract $request)
	{
		if (!$this->_processed) {
			$response = $this->getResponse();
			
			$headers = $response->getHeaders();
			foreach($headers as $header)
			{
				//Do not proceed if content-type is not html/xhtml or such
				if($header['name'] == 'Content-Type' && strpos($header['value'], 'html') === false)
					return;			
			}
			
			// Get the SEO resource from registry
			if ((null !== $this->_seo) && ($request->isDispatched())) {
				$response = $this->getResponse();
				$view = Zend_Controller_Action_HelperBroker::getExistingHelper('ViewRenderer')->view;
				
				// --- META ---
				$keywords = $this->_seo->getKeywords();
				if (is_array($keywords)) {
					if (count($keywords) > 0) {
						$keywords = implode(',', $keywords);
						$view->headMeta()->appendName('keywords', $keywords);
					}
				}
				unset($keywords);
				
				$robots = $this->_seo->getRobots();
				if (is_array($robots)) {
					if (count($robots) > 0) {
						$robots = implode(',', $robots);
						$view->headMeta()->appendName('robots', $robots);
					}
				}
				unset($robots);
				
				// --- LINKS ---
				$host = $this->_seo->getDomain();
				
				switch (true) {
		            case (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] === true)):
		            case (isset($_SERVER['HTTP_SCHEME']) && ($_SERVER['HTTP_SCHEME'] == 'https')):
		            case (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == 443)):
		                $scheme = 'https';
		                break;
		            default:
		            $scheme = 'http';
		        }
		        
		        $baseUri = $scheme . '://' . $host;

		        
		        
	            
	            
	            
	            
	            
	            
	            
		        
		        // canonical
		        $view->headLink()->headLink(
		        	array(
		        		'rel'	=> 'canonical',
		        		'href'	=> $baseUri . $_SERVER['REQUEST_URI']
		        	)
		        );

		        
			}
			
			$this->_processed = true;
		}
	} 
	
	public function dispatchLoopShutdown()
	{
		/**
		 * Response object
		 * @var Zend_Controller_Response_Abstract
		 */
		$response = $this->getResponse();
		
		// Load the page
		$html = $response->getBody();
		
		$headers = $response->getHeaders();
		foreach($headers as $header)
		{
			//Do not proceed if content-type is not html/xhtml or such
			if($header['name'] == 'Content-Type' && strpos($header['value'], 'html') === false) {
				// Google returns Content-Length... So we are going to do the same
				$response->setHeader('Content-Length', strlen($html));
					
				return;
			}
		}
		
		
		// METAS
		$metas = $this->getMetaTags($html);
		
		$httpCode = trim($response->getHttpResponseCode());
		if (($httpCode[0] === "4") || ($httpCode[0] === "5")) {
			for ($i=0;$i<count($metas['raw']);$i++) {
				if (strcasecmp($metas['name'][$i], 'robots') === 0) {
					$html = str_replace($metas['raw'][$i], '<meta name="robots" content="noindex, nofollow" >', $html);
				} elseif (((strcasecmp($metas['name'][$i], 'keywords') === 0) || (strcasecmp($metas['name'][$i], 'description')) === 0)) {
					// Remove keywords and description
					$html = str_replace($metas['raw'][$i], '', $html);
				}
			}
			
			// Remove Dublin Core (DC) Meta tags
			$html = preg_replace('/<meta[^>]+name=\\"dc\.([^\\"]*)\\"[^>]+content=\\"([^\\"]*)\\"[^>]+>/i', '', $html);
			// Remove Open Graph (OG) Meta tags
			$html = preg_replace('/<meta[^>]+property=\\"og\:([^\\"]*)\\"[^>]+content=\\"([^\\"]*)\\"[^>]+>/i', '', $html);
			
			// Remove the canonical url if present in error pages
			$html = preg_replace('/<link[^>]+rel=\\"canonical"[^>]+>/i', '', $html);
			
			// Finally make the code look better
			$html = preg_replace('/([\r\n]+)/',"\n", $html);
			$html = preg_replace('/\n+/',"\n", $html);
		}
	
		$response->setBody($html);
		
		// Google returns Content-Length... So we are going to do the same
		$response->setHeader('Content-Length', strlen($html));
		
		// We also want to set the language header
		// as some search engines use it to improve search results
		$response->setHeader('Content-Language', $this->getLocale()->getLanguage());
	}
	
	
}