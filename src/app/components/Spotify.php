<?php
//class to handle api requests
namespace App\Components;

use Phalcon\Di\Injectable;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;
use Users;

class Spotify extends injectable
{
    private $key;
    private $client;

    public function __construct()
    {
        $user = Users::find($this->session->get('user_id'))[0];
        if ($user)
            $this->key =     $user->token;

        $this->client = $this->setClient();
    }


    /**
     * setClient()
     * 
     * function to initialze Guzzle
     *
     * @return $client object of class Client
     */
    private function setClient()
    {
        $client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $this->config->api->get('base_url'),
            // You can set any number of default request options.
            'timeout'  => 2.0,
            'headers' => ['Authorization' => "Bearer $this->key", 'Content-Type' => 'application/json']
        ]);
        return $client;
    }



    /**
     * getResponse($action, $city)
     * 
     * function to handle api requests
     *
     * @param [type] $action
     * @param [type] $city
     * @return array
     */
    private function getResponse($url)
    {
        $eventManager = $this->di->get('EventsManager');
        try {
            //common request url for all type of operations from api
            $response = $this->client->request(
                'GET',
                $url
            );

            $body = $response->getBody();
            $data = json_decode($body, true);
            return $data;
        } catch (ClientException $e) {
            echo Psr7\Message::toString($e->getRequest());
            echo Psr7\Message::toString($e->getResponse());
            $this->session->set('uri', $_SERVER['REQUEST_URI']);
            $eventManager->fire('api:access', $this);
            // die;
        }
    }



    /**
     * function to handle all the post requests
     *
     * @param [type] $url
     * @param [type] $body
     * @return void
     */
    private function postResponse($url, $body)
    {
        //common request url for all type of operations from api
        $response = $this->client->request(
            'POST',
            $url,
            ['body' => json_encode($body)]
        );

        $body = $response->getBody();
        $data = json_decode($body, true);

        return $data;
    }


    /**
     * function to delete track
     *
     * @param [type] $url
     * @param [type] $body
     * @return void
     */
    private function deleteResponse($url, $body)
    {
        //common request url for all type of operations from api
        $response = $this->client->request(
            'DELETE',
            $url,
            ['body' => json_encode($body)]
        );

        $body = $response->getBody();
        $data = json_decode($body, true);

        return $data;
    }



    /**
     * getDetails($action, $city)
     * 
     * function to return display data
     *
     * @param [type] $action
     * @param [type] $city
     * @return void
     */
    public function getDetails($url)
    {

        //calling  class private function to get response
        $result = $this->getResponse($url);


        return $result;
    }


    /**
     * public function to call postresponse for creating playlist
     *
     * @param [type] $playlist
     * @param [type] $id
     * @return void
     */
    public function createPlaylist($playlist, $id)
    {
        $body = array(
            "name" => $playlist,
            "description" => $playlist,
            "public" => false
        );

        $url = "users/$id/playlists";
        //calling  class private function to post 
        $result = $this->postResponse($url, $body);
        return $result;
    }


    /**
     * public function calling post function to add track
     *
     * @param [type] $url
     * @param [type] $track
     * @return void
     */
    public function addTrack($url, $track)
    {
        $body = array("uris" => array($track));
        //calling  class private function to post 
        $result = $this->postResponse($url, $body);
        return $result;
    }

    /**
     * function calling class private function for deleting track
     *
     * @param [type] $url
     * @param [type] $track
     * @return void
     */
    public function deleteTrack($url, $track)
    {
        $body = array("uris" => array($track));
        //calling  class private function to delete
        $result = $this->deleteResponse($url, $body);
        return $result;
    }
}
