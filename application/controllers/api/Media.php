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
        $this->load->helper('download');
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
            $file_name = $this->security->sanitize_filename($_FILES['file']['name']);
        }
        $uploadfile = $uploaddir.$uuid."_".$file_name;

        // create upload directory if not exist yet.
        $this->ensureDirExist($uploaddir);

        // Create media, only if upload successfully or there is no file.
        if (!$has_file || move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
            if ($has_file) {
                chmod($uploadfile, 0755);
            }

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
    public function index_get() {
        // Authentication check
        $username = $this->checkCredential();
        if ($username === NULL) {
            $this->response(array("status" => "Unauthorized"), REST_Controller::HTTP_UNAUTHORIZED);
            return;
        }
        $conditions = array(
            "phrase" => $this->input->get('phrase', TRUE),
            "lat_lower" => floatval($this->input->get('lat_lower', TRUE)),
            "lat_upper" => floatval($this->input->get('lat_upper', TRUE)),
            "long_lower" => floatval($this->input->get('long_lower', TRUE)),
            "long_upper" => floatval($this->input->get('long_upper', TRUE)),
            "time_lower" => $this->input->get('time_lower', TRUE),
            "time_upper" => $this->input->get('time_upper', TRUE)
        );
        $data = $this->media_model->search($conditions);

        if ($data === NULL) { // no data
            $this->response(array(), REST_Controller::HTTP_OK);
        } else { // some data
            $this->response($data, REST_Controller::HTTP_OK);
        }
    }

    public function config_get() {
        var_dump(ini_get("upload_max_filesize"));
    }

    public function file_get($id) {
        $media =  $this->media_model->get($id);
        $filename = $media["id"]."_".$media["file_name"];

        $data = file_get_contents("./uploads/media/".$filename);
        $name = $media["username"]."_".$media["file_name"];

        force_download($name, $data);
    }

    private function ensureDirExist($dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}

function get($arr, $key) {
    return (isset($arr[$key]) && $arr[$key] != "") ? $arr[$key] : NULL;
}