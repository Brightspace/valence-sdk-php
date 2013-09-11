<?php

function setCurlOptions($uri){
    $ch = curl_init();
    $options = array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CAINFO => getcwd().'/cacert.pem'
        );

    curl_setopt_array($ch, $options);
    curl_setopt($ch, CURLOPT_URL, $uri);
    return $ch;
}

function runCurlRequest($ch, $method, $opContext, $data=null){
   
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if ($data){
         curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data))
            );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    }

    $numAttempts = 1;
    $errorArray = array(
        D2LUserContext::RESULT_OKAY => "Success",
        D2LUserContext::RESULT_INVALID_SIG => "Invalid signature.",
        D2LUserContext::RESULT_INVALID_TIMESTAMP => "There is a time skew between server and local machine.  Try again.",
        D2LUserContext::RESULT_NO_PERMISSION => "Not authorized to perform this operation.",
        D2LUserContext::RESULT_UNKNOWN => "Unknown error occured"
    );
    $responseCode = "";
    while ($responseCode != D2LUserContext::RESULT_INVALID_TIMESTAMP && $numAttempts < 5) {
        $response = curl_exec($ch);
        
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $responseCode = $opContext->handleResult($response, $httpCode, $contentType);

        if ($responseCode == D2LUserContext::RESULT_OKAY) {
            $ret = "$response";
        }else{
            if($httpCode == 302) {
                // This usually happens when a call is made non-anonymously prior to logging in.
                // The D2L server will send a redirect to the log in page.
                $ret = "Redirect encountered (need to log in for this API call?) (HTTP status 302)";
            } else {
                $ret = "{$errorArray[$responseCode]} (HTTP status $httpCode)";
            }
            $protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
            header($protocol . ' ' . '400 Bad Request');
            $GLOBALS['http_response_code'] = $httpCode;
        }
        $numAttempts++;
    }
    curl_close($ch);
    return $ret;
}

?>