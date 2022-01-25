<?php


use GuzzleHttp\Exception\GuzzleException;

class Bot {

    private $vkApi;
    public function __construct()
    {
        $this->vkApi = new VkApi();
    }

    /**
     * @param $user_id
     * @throws Exception
     * @throws GuzzleException
     */
    public function sendMessage($user_id)
    {

        $users_get_response = $this->vkApi->usersGet($user_id);
        $user = array_pop($users_get_response);
        $msg = "Привет, {$user['first_name']}!";
        $photo = $this->uploadPhoto($user_id, BOT_IMAGES_DIRECTORY . '/cat.jpeg');

        $attachments = array(
            'photo' . $photo['owner_id'] . '_' . $photo['id'],
        );

        $this->vkApi->messagesSend($user_id, $msg, $attachments);
    }

    /**
     * @param $user_id
     * @param $file_name
     * @return mixed|null
     * @throws Exception
     * @throws GuzzleException
     */
    private function uploadPhoto($user_id, $file_name)
    {
        $upload_server_response = $this->vkApi->photosGetMessagesUploadServer($user_id);
        $upload_response = $this->vkApi->upload($upload_server_response['upload_url'], $file_name);

        $photo = $upload_response['photo'];
        $server = $upload_response['server'];
        $hash = $upload_response['hash'];

        $save_response = $this->vkApi->photosSaveMessagesPhoto($photo, $server, $hash);

        return array_pop($save_response);
    }
}