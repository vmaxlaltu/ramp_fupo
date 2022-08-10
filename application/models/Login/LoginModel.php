<?php 
 
class LoginModel extends CI_Model{ 
    
function __construct() { 
    parent::__construct();

} 

public function insert($table, $array)
{
    $query = $this->db->insert($table, $array);
    $insert_id = $this->db->insert_id();
    if ($query) {
        return $insert_id;
    } else {
        return false;
    }
}
public function update($table, $array, $wherearray)
{
    $this->db->where($wherearray);
    $query = $this->db->update($table, $array);
    if ($query) {
        return 1;
    } else {
        return 0;
    }
}

public function all($table)
{
    $this->db->select('*');
    $query = $this->db->get($table);
    if ($query) {
        return $query->result();
    } else {
        return false;
    }
}
public function all_by_array($table, $array)
{
    $this->db->select('*');
    $this->db->where($array);
    $query = $this->db->get($table);
    // echo $this->db->last_query();
    // exit;
    if ($query) {
        return $query->result();
    } else {
        return false;
    }
}
public function delete($table, $array)
{
    $this->db->where($array);
    $query = $this->db->delete($table);
    // echo $this->db->last_query();
    // exit;
    if ($query) {
        return true;
    } else {
        return false;
    }
}
public function get_row($table, $array)
{
    $this->db->select('*');
    $this->db->where($array);
    $query = $this->db->get($table);
    if ($query) {
        return $query->row();
    } else {
        return false;
    }
}

public function get_count($table, $array)
{
    $this->db->select('*');
    $this->db->where($array);
    $query = $this->db->get($table);
    $num = $query->num_rows();
    if ($num) {
        return $num;
    } else {
        return 0;
    }
}
public function loginusertypes($table, $array)
{
    $this->db->select("*");
    $this->db->where($array);
    $query = $this->db->get($table);
    
    if ($query) {
        return $query->result();
    } else {
        return false;
    }
}
public function checkLoginOTP($table, $params)
{
    $nowTIMT = date('Y-m-d H:i:s');
    return $this->db->where($params)->where('otp_from <= ',$nowTIMT)->where('otp_to >= ',$nowTIMT)->get($table)->result_array();
}

}
    
?>