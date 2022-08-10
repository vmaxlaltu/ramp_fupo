
<script>
    $(document).ready( function() {
        $('#enter_otp').prop("readonly", true);
    });
    // login validation 
    $(function() {
        $("form[name='login']").validate({
            rules: {
            user_type: "required",
            email_phone: "required",
            enter_otp: "required",
            },
            messages: {
            user_type: "Please Select User Type",
            email_phone: "Please enter Registered Phone No / Email Id",
            enter_otp: "Please enter Valid OTP"
            },
            submitHandler: function(form) {
                // var user_type = $('#user_type').val();
                var user_type = 1;
                var email_phone = $('#email_phone').val();
                var enter_otp = $('#enter_otp').val();
                var post_data = {
                'user_type': user_type,
                'email_phone': email_phone,
                'enter_otp': enter_otp,
                '<?php echo $this->security->get_csrf_token_name(); ?>' : '<?php echo $this->security->get_csrf_hash(); ?>'
                };
                $.ajax({
                    type: "POST",
                    url: "<?php echo base_url(); ?>login",
                    data: post_data,
                    dataType: "JSON",  
                    cache:false,
                    beforeSend: function() 
                    { 
                        $("#overlay").fadeIn();
                    },
                    success:function(data)
                    {      
                        if(data.status == "1")
                        {
                            $('#login_msg').html(data.loginmessage); 
                            $('#otp_msg').html('');
                            setTimeout(function () {
                                window.location.href = "<?php echo base_url() ?>dashboard-role"; 
                            }, 2000);
                        }
                        else if(data.status == "2") 
                        {
                            $('#login_msg').html(data.loginmessage); 
                            $('#otp_msg').html('');
                            setTimeout(function () {
                                window.location.href = "<?php echo base_url() ?>dashboard-role"; 
                            }, 2000);

                        }
                        else if(data.status == "6")
                        {
                            $('#login_msg').html(data.loginmessage);
                            setTimeout(function () {
                                $('#otp_msg').html('');
                            }, 2000)
                        }
                        else if(data.status == "7")
                        {
                            $('#login_msg').html(data.loginmessage);
                            setTimeout(function () {
                                $('#otp_msg').html('');
                            }, 2000)
                        }
                    },
                    complete: function() 
                    { 
                        $("#overlay").fadeOut();
                    }
                });
                return false;
            }
        });
    });
 // login validation 

   //  login otp
   $(".otpsend").click(function(){
        // var user_type = $('#user_type').val();
        var user_type = 1;
        var email_phone = $('#email_phone').val();
        if(user_type == "")
        {
            alert('Please Select User Type');
            return false;
        }
        if( email_phone == "")
        {
            alert('Please Enter your Registered Phone No / Email Id');
            return false;
        }
        else
        {
            var post_data = {
                'user_type': user_type,
                'email_phone': email_phone,
                '<?php echo $this->security->get_csrf_token_name(); ?>' : '<?php echo $this->security->get_csrf_hash(); ?>'
                };
            $.ajax({
                type: "POST",
                url: "<?php echo base_url(); ?>sendotp",
                data: post_data,
                dataType: "JSON",  
                cache:false,
                beforeSend: function() 
                { 
                    $("#overlay").fadeIn();
                },
                success:function(data)
                {      
                    if(data.status == "1")
                    {
                        $('#otp_msg').html(data.message);
                        $('.otpsend').prop('disabled', true);
                        $('#enter_otp').prop("readonly", false);
                        $('#email_phone').prop("readonly", true);
                        // if otp send 
                        $('#otptimer').css('display', 'block');
                        var timeleft = 60;
                        var downloadTimer = setInterval(function(){
                        timeleft--;
                        document.getElementById("countdowntimer").textContent = timeleft;
                        if(timeleft <= 0)
                            clearInterval(downloadTimer);
                        },1000);
                        // if otp send 
                        
                        setTimeout(function () {
                            $('.otpsend').prop('disabled', false);
                            $('#otp_msg').html("");
                            $('.otpsend').text('Resend OTP');
                            $('#otptimer').css('display', 'none');
                            $('#countdowntimer').text('60');
                            $('#enter_otp').prop("readonly", false);
                        $('#email_phone').prop("readonly", false);
                        }, 60000)
                    }
                    else if(data.status == "2")
                    {
                        $('#otp_msg').html(data.message);
                        setTimeout(function () {
                            $('#otp_msg').html('');
                        }, 2500)
                    }
                    else if(data.status == "3")
                    {
                        $('#otp_msg').html(data.message);
                        setTimeout(function () {
                            $('#otp_msg').html('');
                        }, 2500)
                    }
                    else if(data.status == "4")
                    {
                        $('#otp_msg').html(data.message);
                        setTimeout(function () {
                            $('#otp_msg').html('');
                        }, 5000)
                    }
                    else if(data.status == "5")
                    {
                        $('#otp_msg').html(data.message);
                        setTimeout(function () {
                            $('#otp_msg').html('');
                        }, 5000)
                    }
                },
                complete: function() 
                { 
                    $("#overlay").fadeOut();
                }
            });
        }
    }); 


    function viewIC(viewid)
    {
        var post_data = {
        'viewid': viewid,
        '<?php echo $this->security->get_csrf_token_name(); ?>' : '<?php echo $this->security->get_csrf_hash(); ?>'
        };
       $.ajax({
        type: "POST",
        url: "<?php echo base_url(); ?>getICdata",
        data: post_data,
        dataType: "JSON",  
        cache:false,
        beforeSend: function() 
        { 
            $("#overlay").fadeIn();
        },
        success:function(data)
            {      
             $('#view_company_name').val(data.ic_name);
            },
            complete: function() 
            { 
                $("#overlay").fadeOut();
            }
       });
    }
    // get view details after click individually each

    // remove alert 
    $("#error-alert").fadeTo(2000, 500).slideUp(500, function(){
    $("#error-alert").slideUp(500);
    });
    // remove  alert 
</script>