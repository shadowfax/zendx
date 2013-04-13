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
 * @package    ZendX_Validate
 * @copyright  Copyright (c) 2012-2013 Juan Pedro Gonzalez Gutierrez
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * @see Zend_Validate_EmailAddress
 */
require_once 'Zend/Validate/EmailAddress.php';


/**
 * @category   ZendX
 * @package    ZendX_Validate
 * @copyright  Copyright (c) 2012-2013 Juan Pedro Gonzalez Gutierrez
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class ZendX_Validate_Username extends Zend_Validate_Abstract
{
    const INVALID            = 'usernameInvalid';
    const INVALID_FORMAT     = 'usernameInvalidFormat';
    
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID            => "Invalid type given. String expected",
        self::INVALID_FORMAT     => "'%value%' is not a valid username"
    );

    protected $_options = array(
    	'format'   => 'strict',
    	'email'    => null
    );
    
    /**
     * Instantiates hostname validator for local use
     *
     * The following option keys are supported:
     * 'format'	=> Allowed username format (Can be "strict", "medium" or "loose")
     * 'email'  => An email address validator, see Zend_Validate_EmailAddress
     *
     * @param  integer|array|Zend_Config $options
     * @return void
     */
    public function __construct($options = array())
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (!is_array($options)) {
            $options     = func_get_args();
            $temp['format'] = array_shift($options);
            
        	if (!empty($options)) {
                $temp['email'] = array_shift($options);
            }
            
            $options = $temp;
        }

        $options += $this->_options;
        $this->setOptions($options);
    }

	/**
     * Returns all set Options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }
    
	/**
     * Set options for the username validator
     *
     * @param array $options
     * @return ZendX_Validate_Username fluid interface
     */
    public function setOptions(array $options = array())
    {
        if (array_key_exists('messages', $options)) {
            $this->setMessages($options['messages']);
        }
        
    	if (array_key_exists('email', $options)) {
            if (array_key_exists('allow', $options)) {
                $this->setEmailAddressValidator($options['email'], $options['allow']);
            } else {
                $this->setEmailAddressValidator($options['email']);
            }
        } elseif ($this->_options['email'] == null) {
            $this->setEmailAddressValidator();
        }
        
    	if (array_key_exists('format', $options)) {
            $this->setFormat($options['format']);
        }

        return $this;
    }
    
	/**
     * Returns the set email address validator
     *
     * @return Zend_Validate_EmailAddress
     */
    public function getEmailAddressValidator()
    {
        return $this->_options['hostname'];
    }
    
	/**
     * @param Zend_Validate_EmailAddress $emailAddressValidator OPTIONAL
     * @param int                        $allow                 OPTIONAL
     * @return void
     */
    public function setEmailAddressValidator(Zend_Validate_EmailAddress $emailAddressValidator = null, $allow = Zend_Validate_Hostname::ALLOW_DNS)
    {
        if (!$emailAddressValidator) {
            $emailAddressValidator = new Zend_Validate_EmailAddress($allow);
        }

        $this->_options['email'] = $emailAddressValidator;
        $this->_options['allow'] = $allow;
        return $this;
    }
    
    /**
     * Set the username format. Can be "strict", "medium" and "loose"
     * Enter description here ...
     * @param unknown_type $format
     */
    public function setFormat($format)
    {
    	$this->_options['format'] = strtolower((string)$format);
    	return $this;
    }
    
	/**
     * Internal method to validate the email address
     *
     * @return boolean
     */
    private function _validateEmailAddress()
    {
        $emailAddress = $this->_options['email']->setTranslator($this->getTranslator())
                             ->isValid($this->_value);
        if (!$emailAddress) {
            $this->_error(Zend_Validate_EmailAddress::INVALID);

            // Get messages and errors from hostnameValidator
            foreach ($this->_options['email']->getMessages() as $code => $message) {
                $this->_messages[$code] = $message;
            }

            foreach ($this->_options['email']->getErrors() as $error) {
                $this->_errors[] = $error;
            }
        }

        return $emailAddress;
    }
    
    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value is a valid username 
     * or email address according to RFC2822
     *
     * @link   http://www.ietf.org/rfc/rfc2822.txt RFC2822
     * @link   http://www.columbia.edu/kermit/ascii.html US-ASCII characters
     * @param  string $value
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $value = (string)$value;
        $this->_setValue($value);

        // If an "@" is found then validate as an email address
        if (preg_match("/[\@]/", $value)) {
    		return $this->_validateEmailAddress();    	
        } else {
	        switch ($this->_options['format']) {
	            case "strict":
	            default:
	                $restriction = '/[^a-zA-Z0-9\_\-]/';
	                break;
	            case "medium":
	                $restriction = '/[^a-zA-Z0-9\_\-\<\>\,\.\$\%\#\@\!\\\'\"]/';
	                break;
	            case "loose":
	                $restriction = '/[\000-\040]/';
	                break;
	        }
	        if (!preg_match($restriction, $value)) {
	            return true;
	        }
	        
	        $this->_error(self::INVALID_FORMAT);
        }

        return false;
    }
}