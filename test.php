<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
require 'BubbleSOAP.class.php';

$client = new BubbleSOAP('http://wsf.cdyne.com/WeatherWS/Weather.asmx?WSDL');
echo '<style>td{font-family:Courier;font-size:0.8em;white-space: pre-wrap;}</style>';
echo '<b>WSDL:</b> '.$client->__getWsdlUrl();
echo '<hr>';
echo '<h2>SoapClient</h2>';
echo '<table border=1><tr><th colspan=2>__getFunctions</th></tr>';
foreach($client->__getFunctions() as $k=>$v){
    echo '<tr><td>'.$k.'</td><td>'.$v.'</td></tr>';
}
echo '</table><br>';
echo '<table border=1><tr><th colspan=2>__getTypes</th></tr>';
foreach($client->__getTypes() as $k=>$v){
    echo '<tr><td>'.$k.'</td><td>'.$v.'</td></tr>';
}
echo '</table>';
echo '<hr>';
echo '<h2>BubbleSOAP</h2>';
echo '<table border=1><tr><th colspan=2>__getFunctionsNames()</th></tr>';
foreach($client->__getFunctionsNames() as $k=>$v){
    echo '<tr><td>'.$k.'</td><td>'.$v.'</td></tr>';
}
echo '</table><br>';
echo '<table border=1><tr><th>Method</th><th>Params</th><th>Return</th></tr>';
foreach($client->__getFunctionsNames() as $name){
    echo '<tr>';
    echo '<td>'.$name.'</td><td>';
    $params=$client->__getParams($name);
    if($params){
        foreach($params as $k=>$v){
            $type=$client->__getType($v);
            echo $k.'->'.var_export($type,1)."\r\n";
        }
    }
    else echo '<i>null</i>';
    $v= $client->__getReturn($name);
    echo '</td><td>'.$v.'->'.var_export($client->__getType($v),1).'</td>';
    echo '</tr>';
}
echo '</table><br>';
echo '<b>Internal WSDL parsed:</b>';
echo '<pre>'.var_export($client->__wsdl_parsed,1).'</pre>';

?>
