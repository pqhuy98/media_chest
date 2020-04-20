<?php

require APPPATH . 'libraries/REST_Controller.php';

class Media extends REST_Controller {

    // Construction method
    // to load database and model
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('user_model');
        $this->load->model('media_model');
    }

    // Get the BASIC username and password of the request, then check if they are correct.
    // If yes, return the username. If not, return NULL.
    private function checkCredential() {
        // Get header "Authorization"
        if ($this->input->get_request_header('Authorization') === NULL) {
            return NULL;
        }
        // Header content is in the form "Basic dTE6cDI=".
        // Value "dTE6cDI=" is base64 encoded of "username:password".
        // Now decode and explode the string to get username and password.
        $authHeader = explode(':' , base64_decode(substr($this->input->get_request_header('Authorization'), 6)));

        // The array $authHeader must contain 2 elements for username and password.
        if (count($authHeader) != 2) {
            return NULL;
        }
        $username = $authHeader[0];
        $password = $authHeader[1];

        // Check if $config["authentication"] is true. If not, no authentication check is needed, always allow access.
        if (getenv("AUTHENTICATION") === "false") {
            return $username;
        }

        // Validating
        if ($this->user_model->validate_password($username, $password)) {
            return $username;
        } else {
            return NULL;
        }
    }

    // // Controller method for "GET /$username".
    // // Get all points belong to a user with a specific username.
    // public function index_get($username = NULL) {
    //     // Authentication check
    //     if ($this->checkCredential() === NULL) {
    //         $this->response(array("status" => "Unauthorized"), REST_Controller::HTTP_UNAUTHORIZED);
    //         return;
    //     }

    //     // Real work

    //     $data = $this->point_model->read($username);

    //     if ($data === NULL) { // no data
    //         $this->response(array(), REST_Controller::HTTP_OK);
    //     } else { // some data
    //         $this->response($data, REST_Controller::HTTP_OK);
    //     }
    // }

    // Controller method for "POST /".
    // Create a new media, given the uploaded file, text message, gps coordinate and ip address.
    public function index_post() {
        // Authentication check
        $username = $this->checkCredential();
        if ($username === NULL) {
            $this->response(array("status" => "Unauthorized"), REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }

        // Real work

        // Data from request's body.
        $uuid = bin2hex(random_bytes(16)); // 128-bit randomly generated uuid. https://en.wikipedia.org/wiki/Universally_unique_identifier

        // Get file's name, or empty if no file is uploaded.
        $uploaddir = './uploads/media/';
        $file_name = "";
        $has_file = false;
        if (isset($_FILES['file'])) {
            $has_file = true;
            $file_name = underscore($_FILES['file']['name']);
        }
        $uploadfile = $uploaddir.$uuid."_".$file_name;

        // create upload directory if not exist yet.
        $this->ensureDirExist($uploaddir);

        // Create media, only if upload successfully or there is no file.
        if (!$has_file || move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
            $data = $this->input->post();
            $media = array(
                "id" => $uuid,
                "file_name" => $file_name,
                "has_file" => $has_file ? 1 : 0,
                "text_message" => isset($data["text_message"]) ? $data["text_message"] : "", // empty if no message included.
                "username" => $username,
                "longitude" => isset($data["longitude"]) ? $data["longitude"] : NULL, // null if no coordinate included.
                "latitude" => isset($data["latitude"]) ? $data["latitude"] : NULL, // null if no coordinate included.
                "ip_address" => $this->input->ip_address()
            );

            if ($this->media_model->create($media)) {
                $this->response(array("status" => "success"), REST_Controller::HTTP_OK);
                return;
            } else {
                $this->response(array("status" => "failure", "message" => "Writing to database failed."), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
                return;
            }
        } else {
            $this->response(array("status" => "failure", "message" => "Cannot upload the file."), REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
            return;
        }
    }

    // Controller method for "GET /coordinate".
    // Get distinct longitude and latitude.
    public function coordinates_get() {
        // Authentication check
        $username = $this->checkCredential();
        if ($username === NULL) {
            $this->response(array("status" => "Unauthorized"), REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }
    }

    private function ensureDirExist($dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}