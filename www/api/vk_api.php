<?php

const VK_API_VERSION = '5.131'; //Используемая версия API
const VK_API_ENDPOINT = 'https://api.vk.com/method/';

/**
 * @param $peer_id
 * @param $message
 * @param array $attachments
 * @return mixed
 * @throws Exception
 */
function vkApi_messagesSend($peer_id, $message, $attachments = array())
{
    return _vkApi_call('messages.send', [
        'random_id' => rand(1000000000000, 9999999999999),
        'peer_id' => $peer_id,
        'message' => $message,
        'attachment' => implode(',', $attachments)
    ]);
}

/**
 * @param $user_id
 * @return mixed
 * @throws Exception
 */
function vkApi_usersGet($user_id)
{
    return _vkApi_call('users.get', array(
        'user_id' => $user_id,
    ));
}

/**
 * @param $peer_id
 * @return mixed
 * @throws Exception
 */
function vkApi_photosGetMessagesUploadServer($peer_id)
{
    return _vkApi_call('photos.getMessagesUploadServer', array(
        'peer_id' => $peer_id,
    ));
}

/**
 * @param $photo
 * @param $server
 * @param $hash
 * @return mixed
 * @throws Exception
 */
function vkApi_photosSaveMessagesPhoto($photo, $server, $hash)
{
    return _vkApi_call('photos.saveMessagesPhoto', array(
        'photo' => $photo,
        'server' => $server,
        'hash' => $hash,
    ));
}

/**
 * @param $peer_id
 * @param $type
 * @return mixed
 * @throws Exception
 */
function vkApi_docsGetMessagesUploadServer($peer_id, $type)
{
    return _vkApi_call('docs.getMessagesUploadServer', array(
        'peer_id' => $peer_id,
        'type' => $type,
    ));
}

/**
 * @param $file
 * @param $title
 * @return mixed
 * @throws Exception
 */
function vkApi_docsSave($file, $title)
{
    return _vkApi_call('docs.save', array(
        'file' => $file,
        'title' => $title,
    ));
}

/**
 * @param $method
 * @param array $params
 * @return mixed
 * @throws Exception
 */
function _vkApi_call($method, $params = array())
{
    $params['access_token'] = VK_API_ACCESS_TOKEN;
    $params['v'] = VK_API_VERSION;

    $query = http_build_query($params);
    $url = VK_API_ENDPOINT . $method . '?' . $query;

    $client = new \GuzzleHttp\Client();
    $result = $client->post($url, $params);
    $response = json_decode($result->getBody()->getContents(), true);

    if (!$response || !isset($response['response'])) {
        log_error($response);
        throw new Exception("Invalid response for $method request\n" . $response['error']['error_msg']);
    }

    return $response['response'];
}

/**
 * @param $url
 * @param $file_name
 * @return mixed
 * @throws Exception
 */
function vkApi_upload($url, $file_name)
{
    if (!file_exists($file_name)) {
        throw new Exception('File not found: ' . $file_name);
    }

    $client = new \GuzzleHttp\Client();
    $result = $client->post($url, [
        'multipart' => [
            [
                'name' => 'file',
                'contents' => GuzzleHttp\Psr7\Utils::tryFopen($file_name, 'r')
            ],
        ]
    ]);

    $response = json_decode($result->getBody()->getContents(), true);

//    $curl = curl_init($url);
//    curl_setopt($curl, CURLOPT_POST, true);
//    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//    curl_setopt($curl, CURLOPT_POSTFIELDS, array('file' => new CURLfile($file_name)));
//    $json = curl_exec($curl);
//    $error = curl_error($curl);
//    if ($error) {
//        log_error($error);
//        throw new Exception("Failed $url request");
//    }
//
//    curl_close($curl);

//    $response = json_decode($json, true);

    if (!$response) {
        throw new Exception("Invalid response for $url request");
    }

    return $response;
}
