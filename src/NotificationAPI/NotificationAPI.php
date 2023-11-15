<?php

namespace NotificationAPI;

class NotificationAPI
{
    public $clientId;
    public $clientSecret;
    function __construct($clientId, $clientSecret)
    {
        if (!$clientId) {
            throw 'Bad clientId';
        }

        if (!$clientSecret) {
            throw 'Bad clientSecret';
        }

        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function send($sendRequest)
    {
        return $this->request('POST', 'sender', $sendRequest);
    }

    public function retract($retractRequest)
    {
        return $this->request('POST', 'sender/retract', $retractRequest);
    }

    public function createSubNotification($params)
    {
        $data = new \stdClass();
        $data->title = $params['title'];
        return $this->request("PUT", 'notifications/' . $params['notificationId'] . '/' . 'subNotifications/' . $params['subNotificationId'], $data);
    }

    public function deleteSubNotification($params)
    {
        return $this->request("DELETE", 'notifications/' . $params['notificationId'] . '/' . 'subNotifications/' . $params['subNotificationId'], null);
    }

    public function setUserPreferences($userId, $userPreferences)
    {
        return $this->request('POST', 'user_preferences/' . $userId, $userPreferences);
    }

    public function request($method, $uri, $data, $customAuthHeader = null)
    {
        $curl = curl_init();
        $url = "https://api.notificationapi.com/" . $this->clientId . "/" . $uri;
    
        if ($data) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    
        // Use customAuthHeader if provided, otherwise use basic auth
        $authorizationHeader = $customAuthHeader ? 
                               $customAuthHeader : 
                               'Authorization: Basic ' . base64_encode($this->clientId . ":" . $this->clientSecret);
    
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            $authorizationHeader
        ]);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
    
        if ($info['http_code'] >= 300) {
            print_r([
                "NotificationAPI error.",
                $response,
                $info
            ]);
        }
    
        if ($info['http_code'] == "202") {
            print_r([
                "NotificationAPI warning.",
                $response,
                $info
            ]);
        }
    
        return $response;
    }
}    