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
 * @return array Array of function parameters
 */
public function __getParams($method)

/**
 * Gets the return type of the specified function
 * @param string $method Function name
 * @return array Array of function parameters
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

## Requirements
* PHP >= 5.0.1
* PHP compiled with SOAP support

## Using
```php
//constructor
$client = new BubbleSOAP('http://example.com/service.asmx?wsdl');
//enabled trace
$client->__enableTrace();
//print params
$params = $client->__getParams('methodName');
foreach($params as $param){
    echo $param.' - type:'.$client->__getType($param).'<br>';
}
//print return
echo $client->__getReturn('methodName');
```
