Bubble-SOAP
===========

BubbleSOAP extends the PHP5 SoapClient class adding the following methods:

```php
/**
 * Enables or disables tracing of request.
 * @param boolean $state (This defaults to TRUE)
 */
public function __enableTrace($state=TRUE)

/**
 * The array of SOAP function prototypes, detailing only the function name 
 * @return array Ordered array of functions names
 */
public function __getFunctionsNames()

/**
 * Gets the parameters of the specified function
 * @param string $method Function name
 * @return array Array of function paramaters
 */
public function __getParams($method)

/**
 * Gets the return type of the specified function
 * @param string $method Function name
 * @return array Array of function paramaters
 */
public function __getReturn($method)

/**
 * Gets the format of the data type specified
 * @param string $name Name of data type
 * @return mixed array (struct), empty string (null value), int, date
 */
public function __getType($name)

/**
 * Returns WSDL address
 * @return string
 */
public function __getWsdlUrl()

/**
 * Sets quickly SOAP header for subsequent calls
 * @param string $name Header tag name
 * @param string $content Header tag content
 * @param int $type The encoding ID, one of the XSD_... constants. 
 */
public function __setHeader($name,$content,$type=XSD_ANYXML)
```

##Use
```php
//constructor
$client = new BubbleSOAP('http://example.com/service.asmx?wsdl');
//enabled trace
$client->__enableTrace();
//invoke service
$client->myMethodName();
//trace request
echo $client->__getLastRequest();
//trace response
echo $client->__getLastResponse();
```

##Changelog

**1.0.0** (2013-06-03)
* First version