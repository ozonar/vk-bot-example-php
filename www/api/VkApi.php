<?php


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 *
 */
class VkApi
{

    const VK_API_VERSION = '5.131';
    const VK_API_ENDPOINT = 'https://api.vk.com/method/';

    /**
     * @param $peer_id
     * @param $message
     * @param array $attachments
     * @return mixed
     * @throws Exception|GuzzleException
     */
    public function messagesSend($peer_id, $message, $attachments = [])
    {
        return $this->call('messages.send', [
            'random_id' => rand(1000000000000, 9999999999999),
            'peer_id' => $peer_id,
            'message' => $message,
            'attachment' => implode(',', $attachments),

        ]);
    }

    /**
     * @param $user_id
     * @return mixed
     * @throws Exception|GuzzleException
     */
    public function usersGet($user_id)
    {
        return $this->call('users.get', [
            'user_id' => $user_id,
        ]);
    }

    /**
     * @param $peer_id
     * @return mixed
     * @throws Exception|GuzzleException
     */
    public function photosGetMessagesUploadServer($peer_id)
    {
        return $this->call('photos.getMessagesUploadServer', [
            'peer_id' => $peer_id,
        ]);
    }

    /**
     * @param $photo
     * @param $server
     * @param $hash
     * @return mixed
     * @throws Exception|GuzzleException
     */
    public function photosSaveMessagesPhoto($photo, $server, $hash)
    {
        return $this->call('photos.saveMessagesPhoto', [
            'photo' => $photo,
            'server' => $server,
            'hash' => $hash,
        ]);
    }

    /**
     * @param $peer_id
     * @param $type
     * @return mixed
     * @throws Exception|GuzzleException
     */
    public function docsGetMessagesUploadServer($peer_id, $type)
    {
        return $this->call('docs.getMessagesUploadServer', [
            'peer_id' => $peer_id,
            'type' => $type,
        ]);
    }

    /**
     * @param $file
     * @param $title
     * @return mixed
     * @throws Exception|GuzzleException
     */
    public function docsSave($file, $title)
    {
        return $this->call('docs.save', [
            'file' => $file,
            'title' => $title,
        ]);
    }

    /**
     * @param $method
     * @param array $params
     * @return mixed
     * @throws Exception|GuzzleException
     */
    private function call($method, $params = [])
    {
        $params['access_token'] = VK_API_ACCESS_TOKEN;
        $params['v'] = self::VK_API_VERSION;

        $query = http_build_query($params);
        $url = self::VK_API_ENDPOINT . $method . '?' . $query;

        $client = new Client();
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
     * @throws Exception|GuzzleException
     */
    public function upload($url, $file_name)
    {
        if (!file_exists($file_name)) {
            throw new Exception('File not found: ' . $file_name);
        }

        $client = new Client();
        $result = $client->post($url, [
            'multipart' => [
                [
                    'name' => 'file',
                    'contents' => GuzzleHttp\Psr7\Utils::tryFopen($file_name, 'r')
                ],
            ]
        ]);

        $response = json_decode($result->getBody()->getContents(), true);

        if (!$response) {
            throw new Exception("Invalid response for $url request");
        }

        return $response;
    }

}