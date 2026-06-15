<!DOCTYPE html>
<html lang="lv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reģistrēties | AgriConnect</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= base_url('css/agriconnect.css') ?>" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.13.6/underscore-min.js"></script>
  <script src="<?= base_url('js/jsonform.js') ?>"></script>
</head>
<body class="ag-auth">
  <div class="ag-auth-card">
    <div class="ag-auth-brand">
      <h1>AgriConnect</h1>
      <p>Mūsdienīgs lauksaimnieku tīkls</p>
    </div>
        <div class="ag-card">
          <div class="text-center mb-4">
            <h4 class="m-0">Izveidot kontu</h4>
          </div>
            <form id="register_form_container">
              <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
            </form>
          <div class="text-center mt-4">
            <p>Jau ir konts? <a href="<?= base_url('auth/login') ?>" class="text-primary">Pieslēdzies šeit</a></p>
          </div>
        </div>
  </div>
<script>
$(document).ready(function () {
    $('#register_form_container').jsonForm({
        schema: {
            username: {
                type: "string",
                title: "Lietotājvārds",
                required: true
            },
            email: {
                type: "string",
                title: "E-pasts",
                required: true
            },
            password: {
                type: "string",
                title: "Parole",
                required: true,
                minLength: 7,
                pattern: "^(?=.*\\d).{7,}$"
            }
        },
        form: [
            { key: "username", placeholder: "Ievadi lietotājvārdu" },
            { key: "email", placeholder: "Ievadi e-pastu" },
            { key: "password", type: "password", placeholder: "Vismaz 7 rakstzīmes un 1 cipars" },
            {
                type: "actions",
                items: [
                    {
                        type: "submit",
                        title: "Reģistrēties",
                        htmlClass: "btn btn-primary w-100 mt-3"
                    }
                ]
            }
        ],
        onSubmit: function (errors, values) {
            if (errors) {
                console.log('Validation errors:', errors);
                alert("Lūdzu, izlabo kļūdas formā.");
            } else {
                values.username = String(values.username || '').trim();
                values.email = String(values.email || '').trim();
                values.password = String(values.password || '');

                if (values.password.length < 7 || !/\d/.test(values.password)) {
                    alert("Parolei jābūt vismaz 7 rakstzīmes garai un jāsatur vismaz viens cipars.");
                    return;
                }

                console.log("Form submitted successfully:", values);

                var csrfName = '<?= csrf_token() ?>';
                var csrfHash = '<?= csrf_hash() ?>';
                values[csrfName] = csrfHash;

                $.ajax({
                    url: '<?= base_url('auth/register') ?>',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(values),
                    success: function (response) {
                        if (response.success) {
                            window.location.href = '<?= base_url('auth/login') ?>';
                        } else {
                            alert('Reģistrācija neizdevās: ' + response.message);
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error submitting form:', xhr.responseText || error);
                        const response = xhr.responseJSON || {};
                        alert('Radās kļūda: ' + (response.message || 'Lūdzu, mēģini vēlreiz.'));
                    }
                });
            }
        }
    });
});
</script>
</body>
</html>
