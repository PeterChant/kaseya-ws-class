#Kaseya WS PHP Class
This is a small PHP class I wrote to make API calls to Kaseya. It could use some extra features but it can make most the basic calls.

##PHP Dependences
- cURL

##Example Usage
```
<?php
require 'kaseya-ws-class.php';

$k = new KaseyaWS;
$k->host = 'your.kaseyaHostUrl';
$k->url = 'your.kaseyaWSUrl'; //Example: http://kaseya.company.com/vsaWS/KaseyaWS.asmx
$k->user = 'kaseyaUser';
$k->password = 'kaseyaUserPassword';
$k->auth();
$p = $k->operation('GetPackageURLs'); //Get the list of available agents and URLs 
?>
```
##Available Kaseya WS API Calls
[http://help.kaseya.com/WebHelp/EN/VSA/6050000/index.asp#3515.htm](http://help.kaseya.com/WebHelp/EN/VSA/6050000/index.asp#3515.htm)