<?php
namespace App\Models;

/*
 *  Tanto la forma de generar la signature como la generación de los headers de cURL,
 *  fueron extraídos de aquí:
 *  - https://blog.jacobemerick.com/web-development/working-with-twitters-api-via-php-oauth/
 *  - https://blog.jacobemerick.com/web-development/passing-extra-parameters-to-twitter-via-oauth/
 * 
 *  Haciéndoles algunas modificaciones, generé esta clase.
 */

class Twitter {
    private $oauthAccessToken;
    private $oauthAccessTokenSecret;
    private $consumerKey;
    private $consumerSecret;

    function __construct($credentials) {
        // Esta función extrae todas las claves de un array, convirtiéndolas en variables
        extract($credentials);

        if (!isset($oauthAccessToken) ||
            !isset($oauthAccessTokenSecret) ||
            !isset($consumerKey) ||
            !isset($consumerSecret)) {
            throw new \Exception("The credentials are missing");
        }

        $this->oauthAccessToken       = $oauthAccessToken;
        $this->oauthAccessTokenSecret = $oauthAccessTokenSecret;
        $this->consumerKey            = $consumerKey;
        $this->consumerSecret         = $consumerSecret;
    }

    // Generación de la signature
    private function generateSignature($rawParams) {
        $time = time();

        $params = [];

        // Proceso los parámetros para darle el formato adecuado
        foreach ($rawParams as $param => $value) {
            $params[] = "$param=$value";
        }

        $oauthData = [
            "oauth_consumer_key=$this->consumerKey",
            "oauth_nonce=$time",
            "oauth_signature_method=HMAC-SHA1",
            "oauth_timestamp=$time",
            "oauth_token=$this->oauthAccessToken",
            "oauth_version=1.0"
        ];

        // Combino y luego ordeno los parámetros
        $oauthAndParameters = array_merge($params, $oauthData);
        sort($oauthAndParameters);
        
        $hash = implode("&", $oauthAndParameters);

        $base = implode("&", [
            "GET",
            rawurlencode("https://api.twitter.com/1.1/statuses/user_timeline.json"),
            rawurlencode($hash)
        ]);

        $key = implode("&", [
            rawurlencode($this->consumerSecret),
            rawurlencode($this->oauthAccessTokenSecret)
        ]);

        $signature = base64_encode(hash_hmac("sha1", $base, $key, true));

        return rawurlencode($signature);
    }

    // Generación del header
    private function getCurlHeader($rawParams) {
        $time = time();

        $signature = $this->generateSignature($rawParams);

        $params = [];

        // Proceso los parámetros para darle el formato adecuado
        foreach ($rawParams as $param => $value) {
            $params[] = $param . '="' . $value . '"';
        }

        $oauthData = [
            "oauth_consumer_key=\"$this->consumerKey\"",
            "oauth_nonce=\"$time\"",
            "oauth_signature=\"$signature\"",
            'oauth_signature_method="HMAC-SHA1"',
            "oauth_timestamp=\"$time\"",
            "oauth_token=\"$this->oauthAccessToken\"",
            'oauth_version="1.0"'
        ];

        // Combino y luego ordeno los parámetros
        $oauthAndParameters = array_merge($params, $oauthData);
        sort($oauthAndParameters);
        
        $header = implode(", ", $oauthAndParameters);

        return [ "Authorization: Oauth $header", "Expect:" ];
    }

    private function processFeed($feed) {
        // Recorro el feed
        return array_map(function ($entry) {
            // Me guardo solo la información que necesito
            $newEntry = new \stdClass();
            $newEntry->created_at = $entry->created_at;
            $newEntry->text = $entry->text;
            
            // Si es una respuesta, me quedo con la información que necesito
            if (!is_null($entry->in_reply_to_user_id)) {
                $inReply = new \stdClass();
                $inReply->id = $entry->in_reply_to_user_id;
                $inReply->name = $entry->in_reply_to_screen_name;
    
                $newEntry->in_reply = $inReply;
            }
    
            return $newEntry;
        }, $feed);
    }

    public function getFeedFrom($screenName, $count = 10) {
        $params = [
            "count" => $count,
            "screen_name" => $screenName
        ];
        
        // Construyo la URL
        $url = "https://api.twitter.com/1.1/statuses/user_timeline.json?" . http_build_query($params);

        // Genero el header
        $curlHeader = $this->getCurlHeader($params);
        
        // Inicializo cURL con sus respectivos parámetros
        $curlRequest = curl_init();

        curl_setopt($curlRequest, CURLOPT_HTTPHEADER, $curlHeader);

        curl_setopt($curlRequest, CURLOPT_HEADER, false);

        curl_setopt($curlRequest, CURLOPT_URL, $url);

        curl_setopt($curlRequest, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($curlRequest, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curlRequest);

        curl_close($curlRequest);

        // Si json_decode devuelve NULL, entonces hubo un error en la request
        if (!($responseArray = json_decode($response))) {
            throw new \Exception("There was an error on the Twitter API request");
        }
        // Si devuelve errores, lanzo una excepción
        else if (isset($responseArray->errors)) {
            throw new \Exception($responseArray->errors[0]->message);
        }

        // Devuelvo los datos procesados
        return $this->processFeed($responseArray);
    }
}