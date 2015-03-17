<?php
if(!extension_loaded('soap')){
    throw new Exception('BubbleSOAP: SOAP extension not enabled!');
}
/**
 * BubbleSOAP Class
 *
 * @author andreaval <andrea.vallorani@gmail.com>
 * @license MIT License <http://opensource.org/licenses/MIT>
 * @link GitHub Repository: https://github.com/andreaval/Bubble-SOAP
 * @version 1.0.3
 */
class BubbleSOAP extends SoapClient{
    
    /**
     * WSDL url
     * @var string
     */
    protected $__wsdl_url;
    
    /**
     * WSDL dom
     * @var DOMDocument
     */
    protected $__wsdl_dom;
    
    /**
     * WSDL parsed
     * @var array 
     */
    public $__wsdl_parsed = array();
    
    /**
     * Client configuration
     * @var array 
     */
    protected $__options=array();
    
    function __construct($wsdl,$options=array()){
        $this->__wsdl_url = $wsdl;
        $this->__options = $options;
        parent::__construct($wsdl,$options);
    }
    
    /**
     * Enables or disables tracing of request.
     * @param boolean $state (This defaults to TRUE)
     */
    public function __enableTrace($state=TRUE){
        $this->__options['trace'] = $state;
        parent::__construct($this->__wsdl_url,$this->__options);
    }
    
    /**
     * Sets quickly SOAP header for subsequent calls
     * @param string $name Header tag name
     * @param string $content Header tag content
     * @param int $type The encoding ID, one of the XSD_... constants. 
     */
    public function __setHeader($name,$content,$type=XSD_ANYXML){
        $var_header = new SoapVar("<$name>$content</$name>",$type,null,null,null);
        $header = new SOAPHeader('namespace',$name,$var_header);
        $this->__setSoapHeaders($header);
    }
    
    /**
     * The array of SOAP function prototypes, detailing only the function name 
     * @return array Ordered array of functions names
     */
    public function __getFunctionsNames(){
        $this->__parseWSDL('operations');
        return array_keys($this->__wsdl_parsed['operations']);
    }
    
    /**
     * Gets the parameters of the specified function
     * @param string $method Function name
     * @return array Array of function paramaters
     */
    public function __getParams($method){
        $this->__parseWSDL('operations');
        $params = $this->__wsdl_parsed['operations'][$method]['in'];
        if(count($params)==1 && $this->__getType(current($params))==''){
            $params = array();
        }
        return $params;
    }
    
    /**
     * Gets the return type of the specified function
     * @param string $method Function name
     * @return array Array of function paramaters
     */
    public function __getReturn($method){
        $this->__parseWSDL('operations');
        return $this->__wsdl_parsed['operations'][$method]['out'];
    }
    
    /**
     * Gets the format of the data type specified
     * @param string $name Name of data type
     * @return mixed array (struct), empty string (null value), int, date
     */
    public function __getType($name){
        $this->__parseWSDL('types');
        return $this->__parseType($name);
    }
    
    /**
     * Returns WSDL address
     * @return string
     */
    public function __getWsdlUrl(){
        return $this->__wsdl_url;
    }
    
    // -----------------
    // PROTECTED METHODS
    // -----------------
    
    protected function __parseType($name){
        if(!$name) return;
        if(!isset($this->__wsdl_parsed['types'][$name])) return $name;
        $type = $this->__wsdl_parsed['types'][$name];
        if(is_object($type)){
            $list = new stdClass();
            foreach((array)$type as $k=>$v){
                $list->$k = $this->__parseType($v);
            }
            return $list;
        }
        elseif(is_array($type)){
            $list = array();
            foreach($type as $k=>$v){
                $list[$k] = $this->__parseType($v);
            }
            return $list;
        }
        else return $type;
    }
    
    protected function __loadWSDL(){
        if(!isset($this->__wsdl_dom)){
            if(!ini_get('allow_url_fopen')) throw new Exception('BubbleSOAP: WSDL document not loaded because "allow_url_fopen" is set to off!');
            $this->__wsdl_dom = new DOMDocument;
            $this->__wsdl_dom->preserveWhiteSpace = false;
            $this->__wsdl_dom->load($this->__wsdl_url);
            if(!$this->__wsdl_dom->xmlVersion) throw new Exception('BubbleSOAP: WSDL document not loaded, unknown problem!');
        }
    }
    
    protected function __parseWSDL($type){
        if(isset($this->__wsdl_parsed[$type])) return;
        switch($type){
            case 'operations':
                $operations = $this->__getFunctions();
                $list = array();
                foreach($operations as $op){
                    $matches = array();
                    if(preg_match('/^(\w[\w\d_]*) (\w[\w\d_]*)\(([\w\$\d,_ ]*)\)$/',$op,$matches)){
                        $return = $matches[1];
                        $name = $matches[2];
                        $params = $matches[3];
                    } 
                    elseif(preg_match('/^(list\([\w\$\d,_ ]*\)) (\w[\w\d_]*)\(([\w\$\d,_ ]*)\)$/',$op,$matches)) {
                        $return = $matches[1];
                        $name = $matches[2];
                        $params = $matches[3];
                    }
                    $paramList = array();
                    $params = explode(',',$params);
                    foreach($params as $param){
                        list($paramType,$paramName) = explode(' ',trim($param));
                        $paramName = trim($paramName,'$)');
                        $paramList[$paramName] = $paramType;
                    }
                    $list[$name] = array('in'=>$paramList,'out'=>$return);
                }
                ksort($list);
                $this->__wsdl_parsed['operations'] = $list;
            break;
            case 'types':
                $types = $this->__getTypes();
                $list = array();
                foreach($types as $type){
                    $parts = explode("\n", $type);
                    $prefix = explode(' ', $parts[0]);
                    $class = $prefix[1];
                    // array
                    if(substr($class,-2) == '[]'){
                        $class=substr($class,0,-2);
                        $list[$class] = array($prefix[0]);
                        continue;
                    }
                    // 'ArrayOf*' types (from MS.NET, Axis etc.)
                    if(substr($class,0,7) == 'ArrayOf'){
                        list($type, $member) = explode(' ',trim($parts[1]));
                        $list[$class] = array($type);
                        continue;
                    }
                    $members = new stdClass();
                    for($i = 1; $i < count($parts) - 1; $i++) {
                        $parts[$i] = trim($parts[$i]);
                        list($type, $member) = explode(' ',substr($parts[$i],0,-1));

                        if(preg_match('/^$\w[\w\d_]*$/', $member)) {
                            throw new Exception('illegal syntax for member variable: ' . $member);
                        }

                        if(strpos($member, ':')) { // keep the last part
                            $tmp = explode(':', $member, 2);
                            $member = (isset($tmp[1])) ? $tmp[1] : null;
                        }

                        $add = true;
                        foreach($members as $mem) {
                            if(isset($mem['member']) && $mem['member'] == $member){
                                $add = false;
                            }
                        }
                        if($add) $members->$member = $type;
                    }
                    // gather enumeration values
                    $values = array();
                    if (count((array)$members) == 0) {
                        $this->__loadWSDL();
                        $values = $this->__checkForEnum($this->__wsdl_dom, $class);
                        if($values){
                            //$list[$class] = array($class=>$values);
                            $list[$class] = 'string';
                        }
                        else{
                            if($prefix[0]=='struct') $list[$class] = '';
                            else{
                                $list[$class] = $prefix[0];
                            }
                        }
                    }
                    else $list[$class] = $members;
                }
                ksort($list);
                $this->__wsdl_parsed['types'] = $list;
            break;
        }
    }
    
    /**
     * Look for enumeration
     * 
     * @param DOM $dom
     * @param string $class
     * @return array
     */
    protected function __checkForEnum(&$dom, $class) {
        $values = array();

        $node = $this->__findType($dom, $class);
        if (!$node) {
            return $values;
        }

        $value_list = $node->getElementsByTagName('enumeration');
        if ($value_list->length == 0) {
            return $values;
        }

        for ($i = 0; $i < $value_list->length; $i++) {
            $values[] = $value_list->item($i)->attributes->getNamedItem('value')->nodeValue;
        }
        return $values;
    }

    /**
     * Look for a type
     * 
     * @param DOM $dom
     * @param string $class
     * @return DOMNode
     */
    protected function __findType(&$dom, $class) {
        $types_node = $dom->getElementsByTagName('types')->item(0);
        $schema_list = $types_node->getElementsByTagName('schema');

        for ($i = 0; $i < $schema_list->length; $i++) {
            $children = $schema_list->item($i)->childNodes;
            for ($j = 0; $j < $children->length; $j++) {
                $node = $children->item($j);
                if ($node instanceof DOMElement &&
                        $node->hasAttributes() &&
                        is_object($node->attributes->getNamedItem('name')) &&
                        $node->attributes->getNamedItem('name')->nodeValue == $class) {
                    return $node;
                }
            }
        }
        return null;
    }
}
