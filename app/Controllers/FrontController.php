<?php
namespace App\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

use App\Models\Twitter;

class FrontController {

    public function index(Request $request, Response $response, array $args) {
        $url = $request->getUri() . "jorgeeferreyra";

        echo json_encode([
            "message" => "Please provide a username. Example: $url"
        ]);
    }

    public function getTwitterFeed(Request $request, Response $response, array $args) {
        if (!TWITTER_ACCESS_TOKEN ||
            !TWITTER_TOKEN_SECRET ||
            !TWITTER_CONSUMER_KEY ||
            !TWITTER_CONSUMER_SECRET) {
            echo json_encode([
                "message" => "Please provide the Twitter credentials on app/constants.php"
            ]); 
            die();
        }

        try {
            $twitter = new Twitter([
                "oauthAccessToken"       => TWITTER_ACCESS_TOKEN,
                "oauthAccessTokenSecret" => TWITTER_TOKEN_SECRET,
                "consumerKey"            => TWITTER_CONSUMER_KEY,
                "consumerSecret"         => TWITTER_CONSUMER_SECRET
            ]);

            echo json_encode($twitter->getFeedFrom($args["username"]));
        }
        catch (\Exception $e) {
            die($e->getMessage());
        }
    }

}