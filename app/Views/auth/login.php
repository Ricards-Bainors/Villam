<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pieslēgties | AgriConnect</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('css/agriconnect.css') ?>" rel="stylesheet">
</head>
<body class="ag-auth">
    <div class="ag-auth-card">
        <div class="ag-auth-brand">
            <h1>AgriConnect</h1>
            <p>Mūsdienīgs lauksaimnieku tīkls</p>
        </div>
        <div class="ag-card">
                    <div class="text-center mb-4">
                        <h4 class="m-0">Laipni lūdzam atpakaļ</h4>
                    </div>
                        <form id="login_form" method="POST" action="<?= base_url('auth/login') ?>">
                            <?= csrf_field() ?> <!-- Include CSRF token if enabled -->
                            <div class="mb-3">
                                <label for="username" class="form-label">Lietotājvārds</label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Ievadi lietotājvārdu" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Parole</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Ievadi paroli" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Pieslēgties</button>
                        </form>
                    <div class="text-center mt-4">
                        <p>Nav konta? <a href="<?= base_url('auth/register') ?>" class="text-primary">Reģistrējies šeit</a></p>
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
                    alert(response.message || 'Pieslēgšanās neizdevās.');
                }

                // Refresh CSRF token if returned
                if (response.csrfToken) {
                    $('input[name="<?= csrf_token() ?>"]').val(response.csrfToken);
                }
            },
            error: function (xhr) {
                alert('Kļūda: ' + xhr.status + ' ' + xhr.responseText);
            }
        });
    });
</script>
</body>
</html>
