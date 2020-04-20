<?php
// Point model. For GPS coordinate.
// Provide database CRUD methods.
class Media_model extends CI_Model {
    public function __construct() {
		$this->load->database();
   	}

    // Read all GPS coordinates for a given user.
    public function read($username = NULL) {
        // SELECT * FROM point WHERE username = $username;
        $query = $this->db->get_where('media', array('username' => $username));
        $data = $query->result_array();

        return $data;
    }

    // Insert a GPS coordinate.
    // Auto set the point's timestamp to current time (see database schema).
    public function create($data) {
        $data = array(
            "id" => $data["id"],
            "file_name" => $data["file_name"],
            "has_file" => $data["has_file"],
            "text_message" => $data["text_message"],
            "username" => $data["username"],
            "longitude" => $data["longitude"],
            "latitude" => $data["latitude"],
            "ip_address" => $data["ip_address"]
        );

        // INSERT INTO point("username", "longitude", "latitude") VALUES(?, ?, ?);
        return $this->db->insert('media', $data);
    }

    public function search($conditions) {
        $phrase = $conditions["phrase"];
        $time_lower = $conditions["time_lower"];
        $time_upper = $conditions["time_upper"];
        $long_lower = $conditions["long_lower"];
        $long_upper = $conditions["long_upper"];
        $lat_lower = $conditions["lat_lower"];
        $lat_upper = $conditions["lat_upper"];
        
    }
}