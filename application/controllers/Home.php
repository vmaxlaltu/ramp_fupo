<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
class Home extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		date_default_timezone_set('Asia/Kolkata');
		$this->load->model('Login/LoginModel');
		if ($this->session->userdata('login_status') == TRUE) 
		{
            // $this->session->set_flashdata('ErrorLoginCredentials', 'Invalid Credentials');
            redirect(base_url().'dashboard');
        }
	}

	public function index() // loading login view
    {
		if ($this->session->userdata('loginotp')) {
			$this->session->unset_userdata('loginotp');
			$this->session->unset_userdata('login_number_email');
        }
		$data['user_types'] = $this->LoginModel->loginusertypes('role_info', array('status' => 'Enable', 'role_cd' => 'A'));
        $this->load->view('Login/login_form', $data);   
        $this->load->view('Login/login_script');
	}
	
	public function otp() // sending otp if user is valid
    {
		 // validatiom 
		$this->form_validation->set_rules('user_type', 'User Type', 'trim|required');
		$this->form_validation->set_rules('email_phone', 'Email Phone', 'trim|required|xss_clean');
		if ($this->form_validation->run() == TRUE) // if validatiom true
        {
			$type = htmlspecialchars(strip_tags($this->input->post('user_type')));
			$email_phone = htmlspecialchars(strip_tags($this->input->post('email_phone')));

			$mobile_check = $email_check = "";
			if(is_numeric($email_phone)) 
			{
			    $mobile_parameter_Array = array('contact_number' => $email_phone, 'status' => 'ACTIVE');
			    $mobile_check = $this->LoginModel->get_count('employee_info', $mobile_parameter_Array); // checking user count by mobile
			}
			else
			{
			    $email_parameter_Array = array('email_id' => $email_phone, 'status' => 'ACTIVE');
			    $email_check = $this->LoginModel->get_count('employee_info', $email_parameter_Array); // checking user count by Email
			}
			//exit;
			
			if($email_check)// if email match
			{
				$loginUSERdata = $this->LoginModel->get_row('employee_info', $email_parameter_Array);  // getting login user details by Email
				$send_otp = $this->sendotp($loginUSERdata);
				if($send_otp == "1")
				{
					$this->OTPsucessMESSAGE();// calling otp success function with Message
				}
				if($send_otp == "2")
				{
				    $this->OTPMESSAGENOTMAPPED();// calling otp success function with Message
				}
			}
			else if($mobile_check) //  if mobile match
			{
				$loginUSERdata = $this->LoginModel->get_row('employee_info', $mobile_parameter_Array);  // getting login user details by Mobile
				
				$send_otp = $this->sendotp($loginUSERdata);
				if($send_otp == "1")
				{
					$this->OTPsucessMESSAGE(); // calling otp success function with Message
				}
				if($send_otp == "2")
				{
				    $this->OTPMESSAGENOTMAPPED();// calling otp not mapped function with Message
				}
				if($send_otp == "4")
				{
				    $this->OTPMESSAGEWARNING(); // warning
				}
				if($send_otp == "5")
				{
				    $this->OTPMESSAGETRYAFTER(); // try after some time 
				}
			}
			else // if email or mobile not match 
			{
				$this->OTPerrorMESSAGE(); // calling otp Error function with Message
			}
		}
	}

	public function CheckUserBlockFunctionality($UserMobile)
	{
        $OtpFromDateTime = date('Y-m-d H:i:s');
        $OtpToDateTime = date("Y-m-d H:i:s", strtotime("+15 minutes", strtotime($OtpFromDateTime)));

		$GetUserDetail = $this->LoginModel->get_row('employee_info', array('contact_number' => $UserMobile));
		//update from to datetime 
		if($GetUserDetail->block_status == 0)
		{
			if($GetUserDetail->user_otp_send_count < 5)
			{
				if(strtotime($OtpFromDateTime) >= strtotime($GetUserDetail->user_otp_send_from) AND strtotime($OtpFromDateTime) <= strtotime($GetUserDetail->user_otp_send_to))// if time in between last 
				{
					// echo 'In';
					$this->LoginModel->update('employee_info', array('user_otp_send_count' => ($GetUserDetail->user_otp_send_count+1)), array('contact_number' => $UserMobile));//update as count one increment
					
					if($GetUserDetail->user_otp_send_count == 3)
					{
						$return = 2; // warning
					}
					else
					{
						$return = 1; // ok
					}
				}
				else
				{
					$UpdateArray = array(
						'user_otp_send_from' => $OtpFromDateTime,
						'user_otp_send_count' => '1',
						'user_otp_send_to' => $OtpToDateTime,
						'block_status' => '0'
					);	
					$whereArray = array(
						'contact_number' => $UserMobile
					);
					
					$this->LoginModel->update('employee_info', $UpdateArray, $whereArray);

					
					if($GetUserDetail->user_otp_send_count == 3)
					{
						$return = 2; // warning
					}
					else
					{
						$return = 1; // ok
					}
				}
			}
			else
			{
				$this->LoginModel->update('employee_info', array('block_status' => '1'), array('contact_number' => $UserMobile));// update as block status 1
				$return = 3; // try after some time 
			}
		}
		else  //check last block time
		{
			$GetBlockLastDateTime =  $GetUserDetail->user_otp_send_to; //check last block time
			$CheckDateTime = date("Y-m-d H:i:s", strtotime("+20 minutes", strtotime($GetBlockLastDateTime)));
			if($OtpFromDateTime > $CheckDateTime)
			{
				$UpdateArray = array(
					'user_otp_send_from' => $OtpFromDateTime,
					'user_otp_send_count' => '1',
					'user_otp_send_to' => $OtpToDateTime,
					'block_status' => '0'
				);	
				$whereArray = array(
					'contact_number' => $UserMobile
				);
				
				$this->LoginModel->update('employee_info', $UpdateArray, $whereArray);
				$return = 1; // ok
			}
			else
			{
				$return = 3; // try after some time 
			}
		}
		return $return;
	}
	
	public function sendotp($loginUSERdata) //send otp
	{
		$CheckUserBlockStatus = $this->CheckUserBlockFunctionality($loginUSERdata->contact_number); 	// Check User Block Status
		// echo $CheckUserBlockStatus;exit;
		if($CheckUserBlockStatus == 1)
		{
			$userId = $loginUSERdata->employee_info_id; // get user id
			$contact_number = $loginUSERdata->contact_number; // get user id
			//$OTP = mt_rand(1000,9999); // generating randam otp
			$OTP = 9093; // generating randam otp
			
			$otp_from = date('Y-m-d H:i:s');
			$otp_to = date("Y-m-d H:i:s", strtotime("+1 minutes", strtotime($otp_from)));
			
			$UpdateArray = array('otp' => $OTP, 'otp_from'=> $otp_from, 'otp_to' => $otp_to, 'otp_dt_tm'=>date('Y-m-d H:i:s'),'otp_status'=>0); // creating Update Array
			
			$whereArray = array('employee_info_id' => $userId); // Creating Where Array
			$updateOTP = $this->LoginModel->update('employee_info', $UpdateArray, $whereArray); // update Otp
			
			if($loginUSERdata->employee_type == "E")// check if user as a employee then employee is mapped or not
			{
				$headCOUNT = $this->LoginModel->get_count('employee_head_map', array('employee_ids' => $loginUSERdata->employee_info_id));  // checking if it's as a Employee Head is there or not
				if($headCOUNT)
				{
					$otpANDmobile = array('loginotp' => $OTP, 'login_number_email' => $contact_number); 
					$this->session->set_userdata($otpANDmobile); // store in session otp and mobile number
					// sent otp to mobile api here
					$this->sendOTPtoPhone($otpANDmobile);
					// sent otp to mobile api here
					return 1; // return true if Message Send
				}
				else
				{
					return 2; // return false if not mapped
				}
			}
			if($loginUSERdata->employee_type == "A") // if as a admin
			{
				$otpANDmobile = array('loginotp' => $OTP, 'login_number_email' => $contact_number); 
				$this->session->set_userdata($otpANDmobile); // store in session otp and mobile number
				// sent otp to mobile api here
				$this->sendOTPtoPhone($otpANDmobile);
				// sent otp to mobile api here
				return 1; // return true if Message Send
			}
			if($loginUSERdata->employee_type == "SA") // if as a admin
			{
				$otpANDmobile = array('loginotp' => $OTP, 'login_number_email' => $contact_number); 
				$this->session->set_userdata($otpANDmobile); // store in session otp and mobile number
				// sent otp to mobile api here
				$this->sendOTPtoPhone($otpANDmobile);
				// sent otp to mobile api here
				return 1; // return true if Message Send
			}
			if($loginUSERdata->employee_type == "MA") // if as a admin
			{
				$otpANDmobile = array('loginotp' => $OTP, 'login_number_email' => $contact_number); 
				$this->session->set_userdata($otpANDmobile); // store in session otp and mobile number
				// sent otp to mobile api here
				// sent otp to mobile api here
				return 1; // return true if Message Send
			}
			//echo $this->db->last_query();
		}
		if($CheckUserBlockStatus == 2)
		{
			$userId = $loginUSERdata->employee_id; // get user id
			$contact_number = $loginUSERdata->contact_number; // get user id
			//$OTP = mt_rand(1000,9999); // generating randam otp
			$OTP = 9093; // generating randam otp
			
			$otp_from = date('Y-m-d H:i:s');
			$otp_to = date("Y-m-d H:i:s", strtotime("+1 minutes", strtotime($otp_from)));
			
			$UpdateArray = array('otp' => $OTP, 'otp_from'=> $otp_from, 'otp_to' => $otp_to, 'otp_dt_tm'=>date('Y-m-d H:i:s'),'otp_status'=>0); // creating Update Array
			
			$whereArray = array('employee_id' => $userId); // Creating Where Array
			$updateOTP = $this->LoginModel->update('employee_info', $UpdateArray, $whereArray); // update Otp
			
			if($loginUSERdata->employee_type == "E")// check if user as a employee then employee is mapped or not
			{
				$headCOUNT = $this->LoginModel->get_count('employee_head_map', array('employee_ids' => $loginUSERdata->employee_id));  // checking if it's as a Employee Head is there or not
				if($headCOUNT)
				{
					$otpANDmobile = array('loginotp' => $OTP, 'login_number_email' => $contact_number); 
					$this->session->set_userdata($otpANDmobile); // store in session otp and mobile number
					// sent otp to mobile api here
					$this->sendOTPtoPhone($otpANDmobile);
					// sent otp to mobile api here
					return 4; // warning
				}
				else
				{
					return 2; // return false if not mapped
				}
			}
			if($loginUSERdata->employee_type == "A") // if as a admin
			{
				$otpANDmobile = array('loginotp' => $OTP, 'login_number_email' => $contact_number); 
				$this->session->set_userdata($otpANDmobile); // store in session otp and mobile number
				// sent otp to mobile api here
				$this->sendOTPtoPhone($otpANDmobile);
				// sent otp to mobile api here
				return 4; // warning
			}
			if($loginUSERdata->employee_type == "SA") // if as a admin
			{
				$otpANDmobile = array('loginotp' => $OTP, 'login_number_email' => $contact_number); 
				$this->session->set_userdata($otpANDmobile); // store in session otp and mobile number
				// sent otp to mobile api here
				$this->sendOTPtoPhone($otpANDmobile);
				// sent otp to mobile api here
				return 4; // warning
			}
			if($loginUSERdata->employee_type == "MA") // if as a admin
			{
				$otpANDmobile = array('loginotp' => $OTP, 'login_number_email' => $contact_number); 
				$this->session->set_userdata($otpANDmobile); // store in session otp and mobile number
				// sent otp to mobile api here
				// sent otp to mobile api here
				return 4; // warning
			}
		}
		if($CheckUserBlockStatus == 3)
		{
			return 5; // try after some time 
		}
	}

	public function OTPsucessMESSAGE() //OTP sucess MESSAGE
	{
		$message = '<div class="alert alert-success mt-2" role="alert">
					OTP Sent to your Mobile Number...! '.$this->session->userdata('loginotp').'
					</div>';
		$return_array = array(
			'status' => "1", // Yes otp
			'otp' => $this->session->userdata('loginotp'),
			'message' => $message
		);			
		echo json_encode($return_array);
	}

	public function OTPMESSAGENOTMAPPED() //OTP sucess MESSAGE
	{
		$message = '<div class="alert alert-danger mt-2" role="alert">
					Employee Not Mapped
					</div>';
		$return_array = array(
			'status' => "3", // NOT MAPPED
			'otp' => "",
			'message' => $message
		);			
		echo json_encode($return_array);
	}
	
	public function OTPMESSAGEWARNING() //Warning MESSAGE
	{
		$message = '<div class="alert alert-warning mt-2" role="alert">
					If you attempt to send more than two OTPs to your device in 10 mins, your account will be blocked for 20 minutes.
					'.$this->session->userdata('loginotp').'</div> ';
		$return_array = array(
			'status' => "4", // Warning
			'otp' => "",
			'message' => $message
		);			
		echo json_encode($return_array);
	}
	
	public function OTPMESSAGETRYAFTER() //Try after sometime
	{
		$message = '<div class="alert alert-danger mt-2" role="alert">
					Your account is blocked for 20 mins. please try again after 20 mins..
					</div>';
		$return_array = array(
			'status' => "5", // Warning
			'otp' => "",
			'message' => $message
		);			
		echo json_encode($return_array);
	}

	public function OTPerrorMESSAGE() //OTP Error MESSAGE 
	{
		$message = '<div class="alert alert-danger mt-2" role="alert">
							Mobile or Email not registered with us...!
					</div>';
		$return_array = array(
			'status' => "2", // no otp
			'otp' => "",
			'message' => $message
		);			
		echo json_encode($return_array);
	}
	public function loginCheck() //Checking Login
	{
		// validatiom 
		$this->form_validation->set_rules('user_type', 'User Type', 'trim|required');
		$this->form_validation->set_rules('email_phone', 'Email Phone', 'trim|required');
		$this->form_validation->set_rules('enter_otp', 'Email Phone', 'trim|required');
		if ($this->form_validation->run() == TRUE) // if validatiom true
		{
			$type = htmlspecialchars(strip_tags($this->input->post('user_type')));
			$email_phone = htmlspecialchars(strip_tags($this->input->post('email_phone')));
			$enter_otp = htmlspecialchars(strip_tags($this->input->post('enter_otp')));
			
			
			$otpChkStatusParams = array('contact_number'=>$email_phone,'otp'=>$enter_otp,'otp_status'=>0);
                     
            $otpChkStatus = $this->LoginModel->checkLoginOTP('employee_info',$otpChkStatusParams);
            //print_r($otpChkStatus);exit;
			//if($enter_otp == $this->session->userdata('loginotp')) //Checking Entered OTP and Send otp match or not (if match)
			if(count($otpChkStatus) > 0) //Checking Entered OTP and Send otp match or not (if match)
			{
				$email_parameter_Array = array('email_id' => $this->session->userdata('login_number_email'), 'status' => 'ACTIVE');
				$email_check = $this->LoginModel->get_count('employee_info', $email_parameter_Array); 

				$mobile_parameter_Array = array('contact_number' => $this->session->userdata('login_number_email'), 'status' => 'ACTIVE');
				$mobile_check = $this->LoginModel->get_count('employee_info', $mobile_parameter_Array); 
				if($email_check)// if email match
				{
					$loginUSERdata = $this->LoginModel->get_row('employee_info', $email_parameter_Array); // getting login user details by email
				}
				else if($mobile_check) //  if mobile match
				{
					$loginUSERdata = $this->LoginModel->get_row('employee_info', $mobile_parameter_Array); // getting login user details by Mobile
				}
				$logoDTL = $this->LoginModel->get_row('organization_info', array('organization_info_id' => $loginUSERdata->organization_info_id)); // get logo
				if(!empty($logoDTL))
				{
				    $companyLOGO = $logoDTL->company_logo;
				}
				else
				{
				    $companyLOGO = "";
				}
				
				if(!empty($loginUSERdata)) //if user valid
				{
					$loginSESSIONarray = array(
						'login_id' 	=> 	$loginUSERdata->employee_info_id,
						'login_fname' 	=> $loginUSERdata->first_name,
						'login_lname' 	=> $loginUSERdata->last_name,
						'login_mobile' 	=> $loginUSERdata->contact_number,
						'employee_type' 	=> $loginUSERdata->employee_type,
						'organization_info_id' 	=> $loginUSERdata->organization_info_id,
						'organization_structure_info_id' 	=> $loginUSERdata->organization_structure_info_id,
						'employee_type_info_id' => $loginUSERdata->employee_type_info_id,
						'organization_internal_structure_details_id' 	=> $loginUSERdata->organization_internal_structure_details_id,
						'company_logo' => $companyLOGO,
						'priority' 	=> 	$loginUSERdata->priority,
						'parentsids' => $loginUSERdata->parentsids,
						'login_status' 	=> TRUE,
					); // creating login session array
					
					// print_r($loginSESSIONarray); 
					$this->session->sess_regenerate();
					$this->session->set_userdata($loginSESSIONarray); // storeing user details in session

					$loginmessage = '<div class="alert alert-success mt-2" role="alert">
								<i class="fa fa-check" aria-hidden="true"></i>	Login Successful...!  Please Wait...
									</div>';
					if($this->session->userdata('employee_type') == 'A')	 // if user as a admin
					{ 
						$return_array = array(
							'status' => "1", // Yes Success if user as a admin
							'loginmessage' => $loginmessage
						);			
						echo json_encode($return_array);
					}
					if($this->session->userdata('employee_type') == 'MA')	 // if user as a admin
					{ 
						$return_array = array(
							'status' => "1", // Yes Success if user as a admin
							'loginmessage' => $loginmessage
						);			
						echo json_encode($return_array);
					}
					
					if($this->session->userdata('employee_type') == 'SA')	 // if user as a admin
					{ 
						$return_array = array(
							'status' => "1", // Yes Success if user as a admin
							'loginmessage' => $loginmessage
						);			
						echo json_encode($return_array);
					}
					if($this->session->userdata('employee_type') == 'E')	 // if user as a Employee
					{
						// checking if it's as a Employee Head is there or not
						$headCOUNT = $this->LoginModel->get_count('employee_head_map', array('employee_ids' => $this->session->userdata('login_id'))); 
						// checking if it's as a Employee Head is there or not
						if($headCOUNT > 0)
						{
							$return_array = array(
								'status' => "2", // Yes Success if user as a Employee
								'loginmessage' => $loginmessage
							);			
							echo json_encode($return_array);
						}
						else
						{
							$loginmessage = '<div class="alert alert-danger mt-2" role="alert">
								 Employee Not Mapped...!
								</div>';
							$return_array = array(
									'status' => "7", // Not Mapped Employee
									'loginmessage' => $loginmessage
								);			
							echo json_encode($return_array);
						}
						
					}	
				} 
				else //if user Invalid
				{
					$loginmessage = '<div class="alert alert-danger mt-2" role="alert">
								Invalid User...!
								</div>';
					$return_array = array(
							'status' => "7", // Invalid Employee 
							'loginmessage' => $loginmessage
						);			
					echo json_encode($return_array);
				}
			}
			else //Checking Entered OTP and Send otp match or not (if not match) 
			{
				$loginmessage = '<div class="alert alert-danger mt-2" role="alert">
								OTP do not Match...!
								</div>';
				$return_array = array(
						'status' => "6", // OTP not Match
						'loginmessage' => $loginmessage
					);			
				echo json_encode($return_array);
			}
		}
	}
	function sendOTPtoPhone($DTLarray)
	{
	    $otp = $DTLarray['loginotp'];
 	    $key='562134E3F043D8';
 	    $timeout = 160;
        $user_mobile = $DTLarray['login_number_email'];
        $senderid='RAMPIT';
        $ch = curl_init();
        // $myurl="https://manage.smssolutions.in/smsapi/index?key=35FD85B7BD7DA4&campaign=0&routeid=16&type=text&contacts=".$user_mobile."&senderid=VESIPL&msg=Dear%20Sir/Madam,%20Download%20APP%20from%20below%20link%20".$apk."%20Username:".$user_mobile."%20Support%20No:".$supno."%20-VESIPL";
        // $myurl="https://manage.smssolutions.in/smsapi/index?key=".$key."&campaign=0&routeid=16&type=text&contacts=".$user_mobile."&senderid=".$senderid."&msg=OTP-".$otp."Thank%20you%20for%20logging%20in.%20For%20any%20issues,%20write%20us%20on%20support@rampglobal.com.%20Thank%20you,TEAM%20FUPO%20(Powered%20by%20RAMP)";
        //$myurl = "https://manage.smssolutions.in/smsapi/index?key=".$key."&campaign=0&routeid=16&type=text&contacts=".$user_mobile."&senderid=".$senderid."&msg=Dear%20User%20OTP%20".$otp."%20for%20login.%20-TEAM%20FUPO%20RAMP";
        // curl_setopt ($ch, CURLOPT_URL, $myurl);
		// curl_setopt ($ch, CURLOPT_HEADER, 0);
		// curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		// curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		// $file_contents = curl_exec($ch);
		// curl_close($ch);
	}
}

?>