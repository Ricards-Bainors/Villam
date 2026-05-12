<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Load jQuery -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.13.6/underscore-min.js"></script> <!-- Load underscore.js -->
  <script src="<?= base_url('js/jsonform.js') ?>"></script> <!-- Load jsonForm -->
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow">
          <div class="card-header text-center bg-success text-white">
            <h4>Register</h4>
          </div>
          <div class="card-body">
            <form id="register_form_container">
              <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
            </form> <!-- Form will be rendered here -->
          </div>
          <div class="card-footer text-center">
            <p>Already have an account? <a href="<?= base_url('auth/login') ?>" class="text-primary">Login here</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>
<script>
$(document).ready(function () {
    $('#register_form_container').jsonForm({
        schema: {
            username: {
                type: "string",
                title: "Username",
                required: true
            },
            email: {
                type: "string",
                title: "Email",
                required: true
            },
            password: {
                type: "string",
                title: "Password",
                required: true
            }
        },
        form: [
            { key: "username", placeholder: "Enter your username" },
            { key: "email", placeholder: "Enter your email" },
            { key: "password", type: "password", placeholder: "Enter your password" },
            {
                type: "actions",
                items: [
                    {
                        type: "submit",
                        title: "Register",
                        htmlClass: "btn btn-primary w-100 mt-3"
                    }
                ]
            }
        ],
        onSubmit: function (errors, values) {
            if (errors) {
                console.log('Validation errors:', errors);
                alert("Please fix the errors in the form.");
            } else {
                console.log("Form submitted successfully:", values);

                // Get CSRF token from the page
                var csrfName = '<?= csrf_token() ?>';
                var csrfHash = '<?= csrf_hash() ?>';
                values[csrfName] = csrfHash;

                $.ajax({
                    url: '<?= base_url('auth/register') ?>',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(values),   // Convert values to JSON string
                    success: function (response) {
                        if (response.success) {
                            window.location.href = '<?= base_url('auth/login') ?>';
                        } else {
                            alert('Registration failed: ' + response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error submitting form:', xhr.responseText || error);
                        const response = xhr.responseJSON || {};
                        alert('An error occurred: ' + (response.message || 'Please try again.'));
                    }
                });
            }
        }
    });
});
</script>
</body>
</html>