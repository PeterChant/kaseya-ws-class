<?php 
class KaseyaWS {
    public $host = '';
    public $url = ''; //KaseyaWS URI
    public $user = ''; //User you want to auth as
    public $password = ''; //Password for user

    /*********/

    public $sessionid;
    public $xmlTop = '<?xml version="1.0" encoding="utf-8"?><soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"><soap:Body>';
    public $xmlBottom ='</soap:Body></soap:Envelope>';   

    //Setup Hashing and salt for sha256
    public $randomNumber;

    public function salt() {
        //Can't use uniqid() here
        for ($i = 1; ; $i++) {
            if ($i > 14) {
                break;
            }

            $this->randomNumber .= strval(rand());
        } 

        return substr($this->randomNumber, 0, 14);       
    }

    public function preHash() {
        return hash("sha256", $this->password . $this->user);
    }

    public function hash() {
        return hash("sha256", $this->preHash() . $this->salt());
    }

    public function __construct() {
        //$this->auth();
    }        

    //Make call to get session ID
    public function auth() {
        $xml = '<Authenticate xmlns="KaseyaWS">
                  <req>
                    <UserName>' . $this->user . '</UserName>
                    <CoveredPassword>' . $this->hash() . '</CoveredPassword>'.
                    '<RandomNumber>' . $this->salt() . '</RandomNumber>'.
                    '<BrowserIP>' . $_SERVER['REMOTE_ADDR'] . '</BrowserIP>                              
                    <HashingAlgorithm>SHA-256</HashingAlgorithm>
                  </req>
                </Authenticate>'; //$_SERVER['REMOTE_ADDR']

        $a = $this->makeCall($xml, 'Authenticate');

        //Handle errors
        if($a['ErrorMessage']) {
            echo '<h2 style="color: red;">Authentication Error</h2>';
            foreach($a as $key=>$value) {
                echo '<h3>'.$key.'</h3>';
                echo '<p>'.$value.'</p>';
            }            
        }
        else {
            foreach($a as $key=>$value) {
                if($key =='SessionID') {
                    $this->sessionid = $value;
                }
            }  

            return $this->sessionid;         
        }
    }

    //PHP curl connection function
    public function makeCall($xmlData, $operation) {
        $headers = array(
            'POST /vsaWS/KaseyaWS.asmx HTTP/1.1',
            'Host:'.$this->host,                      
            'Content-type: text/xml;charset="utf-8"',
            'Content-length: ' . strlen($this->xmlTop . $xmlData . $this->xmlBottom),
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->xmlTop . $xmlData . $this->xmlBottom); // the SOAP request
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch); 
        curl_close($ch);

        //Convert to normal xml and remove some un-needed tags
        $response = str_replace('<soap:Body>','',$response);
        $response = str_replace('</soap:Body>','',$response);
        $response = str_replace('<' . $operation . 'Response xmlns="KaseyaWS">','',$response);
        $response = str_replace('</' . $operation . 'Response>', '', $response);
        $response = str_replace('<' . $operation . 'Result>', '', $response);
        $response = str_replace('</' . $operation . 'Result>', '', $response);

        //Conver to array
        $xmlArray = get_object_vars(simplexml_load_string($response));  

        return $xmlArray;
    }

    //KaseyaWS operations
    public function operation($operation) {
        $xml = '<' . $operation . ' xmlns="KaseyaWS">
                  <req>
                    <GroupName></GroupName>
                    <BrowserIP>'.$_SERVER['REMOTE_ADDR'].'</BrowserIP>
                    <SessionID>'.$this->sessionid.'</SessionID>
                  </req>
                </' . $operation . '>';

        $a = $this->makeCall($xml, $operation);

        //Handle errors
        if($a['ErrorMessage']) {
            echo '<h2 style="color: red;">'.$operation.'Error</h2>';
            foreach($a as $key=>$value) {
                echo '<h3>'.$key.'</h3>';
                echo '<p>'.$value.'</p>';
            }            
        }
        else {
            return $a;
        }
    }    
}