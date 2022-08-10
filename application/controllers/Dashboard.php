<?php defined('BASEPATH') OR exit('No direct script access allowed'); 
 ob_start();

class Dashboard extends CI_Controller {
    function __construct() 
    {
        parent::__construct(); 
        error_reporting(0);
        $this->load->model('Dashboard/DashboardModel');
        if($this->session->userdata('login_status') !== TRUE) // if user is valid
        {
            $this->session->set_flashdata('ErrorLoginCredentials','Invalid Credentials');
            redirect(base_url());
        } 
    	
        if($this->session->userdata('role') == "") // if role not selected redirect to select role page
        {
            if(!$this->session->userdata('employee_type') == 'A')// checking if user as a admin or employee
            {
                $this->session->set_flashdata('ErrorLoginCredentials','Invalid Credentials');
                redirect(base_url()."dashboard-role");
            }
        } 
        if($this->session->userdata('employee_type') == 'E') // if user as Employee and checking Mapped Head and Valid User
        {
           $empCount = $this->DashboardModel->get_count('employee_info', array('employee_id' => $this->session->userdata('login_id'))); // employee table
           $empHeadCount = $this->DashboardModel->get_count('employee_head_map', array('employee_ids' => $this->session->userdata('login_id'))); // employee Head table
           if($empCount == 0 || $empHeadCount == 0)
           {
               $this->session->set_flashdata('ErrorLoginCredentials','Invalid Credentials');
               redirect(base_url());
           } 
        }
        if($this->session->userdata('employee_type') == 'SA') // if user as Employee and checking Mapped Head and Valid User
        {
            //echo 1;exit;
        }
    } 
    public function index()
    {
        if($this->session->userdata('employee_type') == 'A' || $this->session->userdata('employee_type') == 'E' || $this->session->userdata('employee_type') == 'MA') // if user as Employee and checking Mapped Head and Valid User
        {
            $priority_array = array('org_structure_info_id' => $this->session->userdata('structure_id'));// crating where array
            $get_session_priority_details = $this->DashboardModel->get_row('organization_structure_info', $priority_array); //get session priority details
            $priority = $get_session_priority_details->priority; // priority
            
            $whereArray = array(
                'organization_id' => $this->session->userdata('organization_id'),
                'priority>' => $priority,
            ); // crating where array
            $data['child_structure'] = $this->DashboardModel->child_structure('organization_structure_info', $whereArray); // get all child Structure
    
            $orgnazationid = $this->session->userdata('organization_id');
            $data['lastLavel'] = $this->db->query("SELECT MAX(`priority`) as levelstructure FROM `organization_structure_info` WHERE `organization_id` = '$orgnazationid' ")->row();//Get last lavel of employee
        }
        else
        {
            $data['child_structure'] = "";
            $data['lastLavel'] = "";
        }
        
        //print_r($data['lastLavel']->lavel);exit;
        $this->load->view('Template/header'); 
        $this->load->view('Dashboard/dashboard', $data); 
        $this->load->view('Template/footer');
    }
    public function getOrganizationStructureForm()
    {
       $whereArray = array(
       'organization_id' => $this->session->userdata('organization_id') 
       );
       $data['Details'] = $this->DashboardModel->child_structure('organization_structure_info', $whereArray); 
       $this->load->view('template/edit-organization-structure-form', $data); 
    }
    public function getOrganizationStructureAddMore()
    {
       $total_num_of_emp_list01 = htmlspecialchars(strip_tags($this->input->post('total_num_of_emp_list01')));
       //echo $total_num_of_emp_list01;exit;
       $data['num_of_emp_list'] = $total_num_of_emp_list01;
       $this->load->view('template/organization-structure-add-more', $data); 
    }
    public function addLevelStructure()
    {
        if($this->input->post('update'))
        {
            $levels = $this->input->post('level');
            
            foreach($levels as $key=>$value)
            {
             if (empty($value)) 
             {
               unset($levels[$key]);
             }
            }
            $Exists_array = array('organization_id' => $this->session->userdata('organization_id'));
            $GetExistsDTL = $this->DashboardModel->all_by_array('organization_structure_info', $Exists_array);
           
            if(count($GetExistsDTL) == count($levels))
            {
                foreach($GetExistsDTL as $key=>$row)
                {
                  $Exists_UpdateArray  = array('structure_name' => $levels[$key]);
                  $Exists_WhereArray = array('org_structure_info_id' => $row->org_structure_info_id, 'organization_id' => $this->session->userdata('organization_id'));
                  $update = $this->DashboardModel->update('organization_structure_info', $Exists_UpdateArray, $Exists_WhereArray);
                }
                $this->session->set_flashdata('success','Structure Level Updated Successfully...');
                redirect(base_url().'dashboard');// update
            }
            else
            {
                // echo count($levels);
                $new = (count($levels)-count($GetExistsDTL));
                for($i = count($GetExistsDTL)+1; $i <= count($levels); $i++)
                {
                    $maxColumn = 'priority';
                    $WhereArray = array('organization_id' => $this->session->userdata('organization_id'));
                    $GetMaxPriority = $this->DashboardModel->GetMaxValue('organization_structure_info', $maxColumn, $WhereArray);// Get last Priority Id
                    
                    $InsertArray = array(
                        'uuid' => $this->uuid->v4(),
                        'organization_id' => $this->session->userdata('organization_id'),
                        'structure_name' => $levels[$i-1],
                        'priority' => $GetMaxPriority->priority+1
                        );
                    $InsertNew = $this->DashboardModel->insert('organization_structure_info', $InsertArray);// 
                }
                $this->session->set_flashdata('success','Structure Level Updated Successfully...');
                redirect(base_url().'dashboard');// update// insert
            }
            
        }
    }
    
    public function DeleteStructureLevel()
    {
        $del_id = $this->input->post('del_id');
        $wherearray = array('organization_id' => $this->session->userdata('organization_id'), 'uuid' => $del_id); // creating where array
        $DeleteIDdetails = $this->DashboardModel->get_row('organization_structure_info', $wherearray); // Delete Id Details
        if($DeleteIDdetails)
        {
            $check = $this->DashboardModel->get_count('organization_internal_structure_details', array('structure_id' => $DeleteIDdetails->org_structure_info_id));// check if any organization created or not before 
            if($check)
            {
                echo "2"; // can't delete
            }
            else
            {
                $del = $this->DashboardModel->delete('organization_structure_info', $wherearray);
                if($del)
                {
                    echo "1"; // yes delete
                }
            }
        }
        else // InValid as Something wrong went
        {
            echo "5"; // return response array
        }
        
    }
    public function dashboard()
    {
        $priority_array = array('org_structure_info_id' => $this->session->userdata('structure_id'));// crating where array
        
        $get_session_priority_details = $this->DashboardModel->get_row('organization_structure_info', $priority_array); //get session priority details
        $priority = $get_session_priority_details->priority; // priority
        
        $whereArray = array(
            'organization_id' => $this->session->userdata('organization_id'),
            'priority>' => $priority,
        ); // crating where array
        $data['child_structure'] = $this->DashboardModel->child_structure('organization_structure_info', $whereArray); // get all child Structure

        $orgnazationid = $this->session->userdata('organization_id');
        $data['lastLavel'] = $this->db->query("SELECT MAX(`priority`) as levelstructure FROM `organization_structure_info` WHERE `organization_id` = '$orgnazationid' ")->row();//Get last lavel of employee
        //print_r($data['lastLavel']->lavel);exit;
        $this->load->view('template/header'); 
        $this->load->view('dashboard1', $data); 
        $this->load->view('template/dashboard-footer');
    }
}
?>
 

