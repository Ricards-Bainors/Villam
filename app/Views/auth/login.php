<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header text-center bg-primary text-white">
                        <h4>Login</h4>
                    </div>
                    <div class="card-body">
                        <form id="login_form" method="POST" action="<?= base_url('auth/login') ?>">
                            <?= csrf_field() ?> <!-- Include CSRF token if enabled -->
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <p>Don't have an account? <a href="<?= base_url('auth/register') ?>" class="text-primary">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $('#login_form').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: '<?= site_url('auth/login') ?>',
            type: 'POST', // Ensure this is POST
            data: $(this).serialize(),
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    window.location.href = '<?= base_url('posts') ?>';
                } else {
                    alert(response.message || 'Login failed.');
                }

                // Refresh CSRF token if returned
                if (response.csrfToken) {
                    $('input[name="<?= csrf_token() ?>"]').val(response.csrfToken);
                }
            },
            error: function (xhr) {
                alert('Error: ' + xhr.status + ' ' + xhr.responseText);
            }
        });
    });
</script>
</body>
</html>