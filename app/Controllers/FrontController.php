<?php
namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use App\Models\Twitter;

class FrontController {

    public function index(Request $request, Response $response, array $args) {
        $url = $request->getUri() . "jorgeeferreyra";

        $data = [
            "message" => "Please provide a username. Example: $url"
        ];

        return $response->withHeader("Content-type", "application/json")
                        ->withJson($data);
    
    }

    public function getTwitterFeed(Request $request, Response $response, array $args) {
        // Si las credenciales de Twitter no están seteadas
        if (!TWITTER_ACCESS_TOKEN ||
            !TWITTER_TOKEN_SECRET ||
            !TWITTER_CONSUMER_KEY ||
            !TWITTER_CONSUMER_SECRET) {
            $data = [
                "message" => "Please provide the Twitter credentials on app/constants.php"
            ];
        }
        else {

            try {
                $twitter = new Twitter([
                    "oauthAccessToken"       => TWITTER_ACCESS_TOKEN,
                    "oauthAccessTokenSecret" => TWITTER_TOKEN_SECRET,
                    "consumerKey"            => TWITTER_CONSUMER_KEY,
                    "consumerSecret"         => TWITTER_CONSUMER_SECRET
                ]);

                $data = $twitter->getFeedFrom($args["username"]);
            }
            catch (\Exception $e) {
                $data = [
                    "message" => $e->getMessage()
                ];
            }

        }

        return $response->withHeader("Content-type", "application/json")
                        ->withJson($data);
    }

}