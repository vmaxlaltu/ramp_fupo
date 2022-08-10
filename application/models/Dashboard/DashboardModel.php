<?php
defined('BASEPATH') or exit('No direct script access allowed');

class DashboardModel extends CI_Model
{
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
        // echo $this->db->last_query();
        // exit;
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
    public function child_structure($table, $array)
    {
        $this->db->select('*');
        $this->db->where($array);
        $this->db->order_by("priority", "asc");
        $query = $this->db->get($table);
        // echo $this->db->last_query();
        // exit;
        if ($query) {
            return $query->result();
        } else {
            return false;
        }
        // return $query ? $query->results() : false;
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
    public function GetMaxValue($table, $maxVal, $array)
    {
        $this->db->select_max($maxVal);
        $this->db->where($array);
        $query = $this->db->get($table);
        $num = $query->num_rows();
        if ($num) {
            return $query->row();
        } else {
            return 0;
        }
    }
    // return $query ?? $query->result() ;
}
