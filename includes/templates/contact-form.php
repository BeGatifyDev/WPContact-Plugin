<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Enquiry Form</title>
  

  <style>
    /* Styling enquiry form container */
    #enquiry_form {
      max-width: 500px;
      margin: 50px auto;
      padding: 30px;
      background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
      border-radius: 10px;
      box-shadow: 0 8px 20px rgba(0,0,0,0.1);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      opacity: 0;
      transform: translateY(20px);
      animation: fadeInForm 0.8s forwards;
    }

    /* Form labels styling */
    #enquiry_form label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
      color: #333;
    }

    /* Inputs and textarea styling */
    #enquiry_form input[type="text"],
    #enquiry_form textarea {
      width: 100%;
      padding: 10px 15px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: 6px;
      box-sizing: border-box;
      transition: border 0.3s, box-shadow 0.3s;
    }

    /* Focus effect */
    #enquiry_form input[type="text"]:focus,
    #enquiry_form textarea:focus {
      border-color: #4facfe;
      box-shadow: 0 0 5px rgba(79, 172, 254, 0.5);
      outline: none;
    }

    /* Submit button styling */
    #enquiry_form button {
      background: linear-gradient(45deg, #4facfe, #00f2fe);
      color: #fff;
      border: none;
      padding: 12px 20px;
      border-radius: 6px;
      font-size: 16px;
      cursor: pointer;
      transition: background 0.3s;
    }

    /* Hover effect for submit button */
    #enquiry_form button:hover {
      background: linear-gradient(45deg, #00f2fe, #4facfe);
    }

    /* Fade in animation for form */
    @keyframes fadeInForm {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    #form_success, #form_error {
  max-width: 500px;
  margin: 15px auto;
  padding: 10px 15px;
  border-radius: 4px;
  font-weight: 500;
  text-align: center;
  display: none; /* hidden by default */
}

#form_success {
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

#form_error {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}
  </style>
</head>
<body>
    <div id = "form_success"></div>
    <div id = "form_error"></div>

  <form id="enquiry_form" enctype="multipart/form-data">

  <?php wp_nonce_field('wp_rest'); ?>

  <label>Name:</label>
  <input type="text" name="name" required>

  <label>Email Address:</label>
  <input type="text" name="email" required>

  <label>Phone Number:</label>
  <input type="text" name="phone" required>

  <label>Your Feedback:</label>
  <textarea name="message" required></textarea>

  <!-- ✅ File Upload Field -->
  <label>Attach File:</label>
  <input type="file" name="attachment">

  <!-- ✅ reCAPTCHA widget -->
  <div class="g-recaptcha" data-sitekey="6LfpNXkrAAAAABoXm-Fehn3iUXDUpjKGt7oPCVhb"></div>

  <button type="submit">Submit form</button>

</form>

<!-- ✅ reCAPTCHA JS -->
<script src="https://www.google.com/recaptcha/api.js" async defer></script>


 <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
jQuery(document).ready(function($){
    $("#enquiry_form").submit(function(event){
        event.preventDefault();

        var form = $(this);
        var formData = new FormData(this); // use FormData for file uploads

        $.ajax({
            type: "POST",
            url: "<?php echo get_rest_url(null, 'v1/contact-form/submit'); ?>",
            data: formData,
            processData: false,
            contentType: false,
            success: function(res){
                console.log(res);
                form.hide();

                if(res && res.message){
                    $("#form_success").html(res.message).fadeIn();
                } else if(typeof res === "string") {
                    $("#form_success").html(res).fadeIn();
                } else {
                    $("#form_success").html("✅ Your message has been sent successfully!").fadeIn();
                }
            },
            error: function(){
                $("#form_error").html("❌ There was an error sending your message. Please try again.").fadeIn();
            }
        });
    });
});

</script>
</body>
</html>
