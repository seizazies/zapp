<?php
/**
 *  
 *  
 * 
 * @author 
 **/

Class Core_Input
{
	protected $headers		= array();
    protected $userAgent    = FALSE;
    protected $ipAddress    = FALSE;
    
	/**
	 * Fetch from array
	 *
	 * This is a helper function to retrieve values from global arrays
	 *
	 * @access	private
	 * @param	array
	 * @param	string
	 * @param	bool
	 * @return	string
	 */
	private function _fetchFromArray(&$array, $index = '', $xss_clean = FALSE)
	{
		if ( ! isset($array[$index]))
		{
			return FALSE;
		}

		if ($xss_clean === TRUE)
		{
            //Later On
			//return $this->security->xss_clean($array[$index]);
		}

		return $array[$index];
	}

	/**
	* Fetch an item from the GET array
	*
	* @access	public
	* @param	string
	* @param	bool
	* @return	string
	*/
	public function get($index = NULL, $xss_clean = FALSE)
	{
		// Check if a field has been provided
		if ($index === NULL AND ! empty($_GET))
		{
			$get = array();

			// loop through the full _GET array
			foreach (array_keys($_GET) as $key)
			{
				$get[$key] = $this->_fetchFromArray($_GET, $key, $xss_clean);
			}
			return $get;
		}

		return $this->_fetchFromArray($_GET, $index, $xss_clean);
	}

	// --------------------------------------------------------------------

	/**
	* Fetch an item from the POST array
	*
	* @access	public
	* @param	string
	* @param	bool
	* @return	string
	*/
	public function post($index = NULL, $xss_clean = FALSE)
	{
		// Check if a field has been provided
		if ($index === NULL AND ! empty($_POST))
		{
			$post = array();

			// Loop through the full _POST array and return it
			foreach (array_keys($_POST) as $key)
			{
				$post[$key] = $this->_fetchFromArray($_POST, $key, $xss_clean);
			}
			return $post;
		}

		return $this->_fetchFromArray($_POST, $index, $xss_clean);
	}


	// --------------------------------------------------------------------

	/**
	* Fetch an item from either the GET array or the POST
	*
	* @access	public
	* @param	string	The index key
	* @param	bool	XSS cleaning
	* @return	string
	*/
	public function get_post($index = '', $xss_clean = FALSE)
	{
		if ( ! isset($_POST[$index]) )
		{
			return $this->get($index, $xss_clean);
		}
		else
		{
			return $this->post($index, $xss_clean);
		}
	}

	// --------------------------------------------------------------------

	/**
	* Fetch an item from the COOKIE array
	*
	* @access	public
	* @param	string
	* @param	bool
	* @return	string
	*/
	public function cookie($index = '', $xss_clean = FALSE)
	{
		return $this->_fetchFromArray($_COOKIE, $index, $xss_clean);
	}

	/**
	* Fetch an item from the SERVER array
	*
	* @access	public
	* @param	string
	* @param	bool
	* @return	string
	*/
	public function server($index = '', $xss_clean = FALSE)
	{
		return $this->_fetchFromArray($_SERVER, $index, $xss_clean);
	}

	/**
	* User Agent
	*
	* @access	public
	* @return	string
	*/
	public function userAgent()
	{
		if ($this->userAgent !== FALSE)
		{
			return $this->userAgent;
		}

		$this->userAgent = ( ! isset($_SERVER['HTTP_USER_AGENT'])) ? FALSE : $_SERVER['HTTP_USER_AGENT'];

		return $this->userAgent;
	}

	/**
	 * Request Headers
	 *
	 * In Apache, you can simply call apache_request_headers(), however for
	 * people running other webservers the function is undefined.
	 *
	 * @param	bool XSS cleaning
	 *
	 * @return array
	 */
	public function requestHeaders($xss_clean = FALSE)
	{
		// Look at Apache go!
		if (function_exists('apache_request_headers'))
		{
			$headers = apache_request_headers();
		}
		else
		{
			$headers['Content-Type'] = (isset($_SERVER['CONTENT_TYPE'])) ? $_SERVER['CONTENT_TYPE'] : @getenv('CONTENT_TYPE');

			foreach ($_SERVER as $key => $val)
			{
				if (strncmp($key, 'HTTP_', 5) === 0)
				{
					$headers[substr($key, 5)] = $this->_fetchFromArray($_SERVER, $key, $xss_clean);
				}
			}
		}

		// take SOME_HEADER and turn it into Some-Header
		foreach ($headers as $key => $val)
		{
			$key = str_replace('_', ' ', strtolower($key));
			$key = str_replace(' ', '-', ucwords($key));

			$this->headers[$key] = $val;
		}

		return $this->headers;
	}

	/**
	 * Get Request Header
	 *
	 * Returns the value of a single member of the headers class member
	 *
	 * @param 	string		array key for $this->headers
	 * @param	boolean		XSS Clean or not
	 * @return 	mixed		FALSE on failure, string on success
	 */
	public function getRequestHeader($index, $xss_clean = FALSE)
	{
		if (empty($this->headers))
		{
			$this->requestHeaders();
		}

		if ( ! isset($this->headers[$index]))
		{
			return FALSE;
		}

		if ($xss_clean === TRUE)
		{
			//return $this->security->xss_clean($this->headers[$index]);
		}

		return $this->headers[$index];
	}

	/**
	 * Is ajax Request?
	 *
	 * Test to see if a request contains the HTTP_X_REQUESTED_WITH header
	 *
	 * @return 	boolean
	 */
	public function isAjaxRequest()
	{
		return ($this->server('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest');
	}
    
	/**
	 * Is HTTP Post Request?
	 *
	 * Test to see if a request is HTTP Post
	 *
	 * @return 	boolean
	 */
    public function isPost ()
    {
        return ($this->server('REQUEST_METHOD') === 'POST' && isset($_POST) && ! empty ($_POST));
    }

	/**
	* Fetch the IP Address
	*
	* @access	public
	* @return	string
	*/
	public function ipAddress()
	{
		if ($this->ipAddress !== FALSE)
		{
			return $this->ipAddress;
		}

		if ($this->server('HTTP_X_FORWARDED_FOR') && $this->server('REMOTE_ADDR'))
		{
            $_ipProxies = zApp::loadConfig('main')->ipProxies;
            $proxies = preg_split('/[\s,]/', $_ipProxies, -1, PREG_SPLIT_NO_EMPTY);
            $proxies = is_array($proxies) ? $proxies : array($proxies);
			$this->ipAddress = in_array($_SERVER['REMOTE_ADDR'], $proxies) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
		}
		elseif ($this->server('REMOTE_ADDR') AND $this->server('HTTP_CLIENT_IP'))
		{
			$this->ipAddress = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif ($this->server('REMOTE_ADDR'))
		{
			$this->ipAddress = $_SERVER['REMOTE_ADDR'];
		}
		elseif ($this->server('HTTP_CLIENT_IP'))
		{
			$this->ipAddress = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif ($this->server('HTTP_X_FORWARDED_FOR'))
		{
			$this->ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}

		if ($this->ipAddress === FALSE)
		{
			$this->ipAddress = '0.0.0.0';
			return $this->ipAddress;
		}

		if (strpos($this->ipAddress, ',') !== FALSE)
		{
			$x = explode(',', $this->ipAddress);
			$this->ipAddress = trim(end($x));
		}

		if ( ! $this->validIp($this->ipAddress))
		{
			$this->ipAddress = '0.0.0.0';
		}

		return $this->ipAddress;
	}

	/**
	* Validate IP Address
	*
	* Updated version suggested by Geert De Deckere
	*
	* @access	public
	* @param	string
	* @return	string
	*/
	public function validIp($ip)
	{
		$ip_segments = explode('.', $ip);

		// Always 4 segments needed
		if (count($ip_segments) != 4)
		{
			return FALSE;
		}
		// IP can not start with 0
		if ($ip_segments[0][0] == '0')
		{
			return FALSE;
		}
		// Check each segment
		foreach ($ip_segments as $segment)
		{
			// IP segments must be digits and can not be
			// longer than 3 digits or greater then 255
			if ($segment == '' OR preg_match("/[^0-9]/", $segment) OR $segment > 255 OR strlen($segment) > 3)
			{
				return FALSE;
			}
		}

		return TRUE;
	}
    
}
 
