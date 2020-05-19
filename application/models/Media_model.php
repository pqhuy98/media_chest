<?php
// Point model. For GPS coordinate.
// Provide database CRUD methods.
class Media_model extends CI_Model {
    public function __construct() {
		$this->load->database();
   	}

    // Read all GPS coordinates for a given user.
    public function get($id) {
        // SELECT * FROM point WHERE username = $username;
        $query = $this->db->get_where('media', array('id' => $id));
        $data = $query->row_array();

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
        $phrase = get($conditions, "phrase");
        $ip_address = get($conditions, "ip_address");
        $time_lower = get($conditions, "time_lower");
        $time_upper = get($conditions, "time_upper");
        $long_lower = get($conditions, "long_lower");
        $long_upper = get($conditions, "long_upper");
        $lat_lower = get($conditions, "lat_lower");
        $lat_upper = get($conditions, "lat_upper");

        $SELECT = "SELECT * FROM media";

        $args = array();
        $WHERE = " WHERE 1=1";

        if ($phrase != NULL) {
            $likeStr = "%".$this->db->escape_like_str($phrase)."%";
            $WHERE .= " AND (text_message LIKE '".$likeStr."' OR file_name LIKE '".$likeStr."')";
        }

        if ($ip_address != NULL) {
            $likeStr = "%".$this->db->escape_like_str($ip_address)."%";
            $WHERE .= " AND (ip_address LIKE '".$likeStr."')";
        }

        if ($time_lower != NULL) {
            $WHERE .= " AND (created_at >= ?)";
            array_push($args, $time_lower);
        }

        if ($time_upper != NULL) {
            $WHERE .= " AND (created_at <= ?)";
            array_push($args, $time_upper);
        }

        if ($long_lower != NULL) {
            $WHERE .= " AND (longitude >= ?)";
            array_push($args, $long_lower);
        }

        if ($long_upper != NULL) {
            $WHERE .= " AND (longitude <= ?)";
            array_push($args, $long_upper);
        }

        if ($lat_lower != NULL) {
            $WHERE .= " AND (latitude >= ?)";
            array_push($args, $lat_lower);
        }

        if ($lat_upper != NULL) {
            $WHERE .= " AND (latitude <= ?)";
            array_push($args, $lat_upper);
        }

        $ORDER = " ORDER BY created_at ASC";

        // var_dump($SELECT.$WHERE);
        // var_dump($args);
        
        $query = $this->db->query($SELECT.$WHERE.$ORDER, $args);
        $data = $query->result_array();

        return $data;
    }
}

// function isdf($value, $default) {
//     return isset($value) ? $value : $default;
// }