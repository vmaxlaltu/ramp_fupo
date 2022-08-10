<!doctype html>
<html class="no-js" lang="">
<head>
	<meta charset="utf-8">
	<meta http-equiv="x-ua-compatible" content="ie=edge">
	<title>:: FUPO</title>
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	 
	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="<?php echo base_url();?>assets/login/css/bootstrap.min.css">
	<!-- Fontawesome CSS -->
	<link rel="stylesheet" href="<?php echo base_url();?>assets/login/css/fontawesome-all.min.css">
	<!-- Flaticon CSS -->
	<link rel="stylesheet" href="<?php echo base_url();?>assets/login/font/flaticon.css">
	<!-- Google Web Fonts -->
	 
	<link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@200;300;400;600;700;900&display=swap" rel="stylesheet">
	
	<!-- Custom CSS -->
	<link rel="stylesheet" href="<?php echo base_url();?>assets/login/css/style.css"> 
	
	<link rel="stylesheet" href="<?php echo base_url();?>assets/login/css/toastr.min.css">
</head>

<body>
<div id="overlay">
        <div class="cv-spinner">
            <span class="spinner"></span>
        </div>
    </div>
	<section class="fxt-template-animation fxt-template-layout10">
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-6 col-12 fxt-bg-img mb_bg_hidden" data-bg-image="assets/login/img/landing-banner.jpg">
					<div class="fxt-header">
						<!--<a href="#" class="fxt-logo"><img src="assets/img/logo.png" alt="Logo"></a>	 -->
					</div>
				</div>
				
				<div class="col-md-6 col-12 fxt-bg-color">
					<div class="fxt-content">
						<div class="fxt-form">
						<?php
						if ($this->session->flashdata('EroorLoginCredentials')) {
						?>
							<div class="alert alert-danger alert-dismissible" id="error-alert">
								<button type="button" class="close" data-dismiss="alert">&times;</button>
								<strong> Error !</strong> <?php echo $this->session->flashdata('EroorLoginCredentials'); ?>
							</div>
						<?php
						}
        				?>
						<div class="mb_disy_blk">
							<img src="assets/login/img/logo_black.png">
						</div>
							<!-- <h2>Login into your account</h2> -->
							<form method="POST" action="" name="login"> 
								
								<div class="form-group">
								  <label for="usr">Registered Phone No / Email Id:</label>	
								  <input type="text" name="email_phone" class="form-control" id="email_phone" placeholder="Registered Phone No / Email Id">
								</div>

								<div id="otp_msg"> 
								</div>

								<div class="form-group mb0 text-right">
								   <button class="btn btn-primary otpsend" >Send OTP</button>
								</div>
								
                                <div id="otptimer" style="display: none;">
                                    <p> Resend OTP in <span id="countdowntimer" style="color:blue">60 </span> Seconds</p>
                                </div>
                                
								<div class="form-group">
								  <label for="usr">Enter OTP:</label>								  
								  <input type="text" name="enter_otp" id="enter_otp" class="form-control" placeholder="Enter OTP" onkeydown="if(event.key==='.'){event.preventDefault();}" oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');" maxlength="6">
								 
								</div>
								<div class="form-group">
									<div>
										<div class="text-center">
											<button type="submit" name="submit" value="submit" class="fxt-btn-fill btn-block">SUBMIT</button>
										</div>
									</div>
								</div>

								<div id="login_msg">
								
								</div>
								<div class="form-group">
									<div  >
										<div class="text-center mt-3">
											<div class="cal_support"><a href="tel:+91 9700081818">Call tech support</a> +91 9700081818</div>
										</div>
									</div>
								</div>
								
								
							</form>
						</div>
						 
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- jquery-->
	<script src="<?php echo base_url();?>assets/login/js/jquery-3.5.0.min.js"></script>
	<!-- Popper js -->
	<script src="<?php echo base_url();?>assets/login/js/popper.min.js"></script>
	<!-- Bootstrap js -->
	<script src="<?php echo base_url();?>assets/login/js/bootstrap.min.js"></script>
	<!-- Imagesloaded js -->
	<script src="<?php echo base_url();?>assets/login/js/imagesloaded.pkgd.min.js"></script>
	<!-- Validator js -->
	<script src="<?php echo base_url();?>assets/login/js/validator.min.js"></script>
	<!-- Custom Js -->
	<script src="<?php echo base_url();?>assets/login/js/main.js"></script>
	
	<script src='https://cdn.jsdelivr.net/jquery.validation/1.15.1/jquery.validate.min.js'></script>

	
    <script src="<?php echo base_url();?>assets/login/js/toastr.min.js"></script>
	<script>
	    $(document).ready(function(){
			
		});
	</script>
</body>


 
</html>