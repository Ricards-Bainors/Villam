<!DOCTYPE html>
<html lang="lv">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <title>Lietotāji | AgriConnect</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= base_url('css/agriconnect.css') ?>" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>

<body class="ag-app">
  <header class="ag-topbar">
    <a class="ag-brand" href="<?= base_url('posts') ?>">AgriConnect</a>
    <label class="ag-search" aria-label="Meklēt lietotājus">
      <i data-lucide="search"></i>
      <input type="search" placeholder="Meklēt lietotājus...">
    </label>
    <div class="ag-top-actions">
      <a class="ag-icon-btn" href="<?= base_url('posts') ?>" title="Ziņu plūsma"><i data-lucide="rss"></i></a>
      <a class="ag-icon-btn" href="<?= base_url('profile') ?>" title="Profils"><i data-lucide="circle-user-round"></i></a>
    </div>
  </header>

  <div class="ag-shell">
    <aside class="ag-sidebar">
      <div class="ag-sidebar-title">
        <h2>AgriConnect</h2>
        <p>Mūsdienīgs lauksaimnieku tīkls</p>
      </div>

      <nav class="ag-nav" aria-label="Galvenā navigācija">
        <a href="<?= base_url('posts') ?>"><i data-lucide="rss"></i> Ziņu plūsma</a>
        <a href="<?= base_url('advertisements') ?>"><i data-lucide="store"></i> Sludinājumi</a>
        <a href="<?= base_url('messages') ?>"><i data-lucide="message-circle"></i> Sarakstes</a>
        <a href="<?= base_url('forum') ?>"><i data-lucide="messages-square"></i> Forums</a>
        <a href="<?= base_url('categories') ?>"><i data-lucide="tags"></i> Kategorijas</a>
        <a class="active" href="<?= base_url('admin/users') ?>"><i data-lucide="users-round"></i> Lietotāji</a>
      </nav>
    </aside>

    <main class="ag-main">
      <div class="ag-content-narrow">
        <div class="ag-page-header">
          <div>
            <h1>Lietotāji</h1>
            <p>Administratora sadaļa lietotāju apskatei un datu labošanai.</p>
          </div>
        </div>

        <div class="ag-table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Lietotājvārds</th>
                <th>E-pasts</th>
                <th>Izveidots</th>
                <th>Darbības</th>
              </tr>
            </thead>
            <tbody id="users_table_body"></tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <div class="modal fade" id="edit_user_modal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editUserModalLabel">Rediģēt lietotāju</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Aizvērt"></button>
        </div>
        <div class="modal-body">
          <form id="edit_user_form">
            <input type="hidden" id="edit_user_id" name="id">
            <div class="mb-3">
              <label for="edit_username" class="form-label">Lietotājvārds</label>
              <input type="text" class="form-control" id="edit_username" name="username" required>
            </div>
            <div class="mb-3">
              <label for="edit_email" class="form-label">E-pasts</label>
              <input type="email" class="form-control" id="edit_email" name="email" required>
            </div>
            <div class="mb-3">
              <label for="edit_password" class="form-label">Jauna parole (nav obligāta)</label>
              <input type="password" class="form-control" id="edit_password" name="password">
            </div>
            <button type="submit" class="btn btn-primary">Saglabāt lietotāju</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function () {
      if (window.lucide) {
        lucide.createIcons();
      }

      function getCsrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
      }

      function updateCsrfToken(token) {
        if (token) {
          $('meta[name="csrf-token"]').attr('content', token);
        }
      }

      function fetchUsers() {
        $.ajax({
          url: '<?= base_url('admin/users/fetch') ?>',
          method: 'get',
          success: function (response) {
            updateCsrfToken(response.csrfToken);
            if (response.success) {
              let rows = '';
              response.data.forEach(user => {
                rows += `<tr>
                  <td>${user.id}</td>
                  <td>${user.username}</td>
                  <td>${user.email}</td>
                  <td>${user.created_at ?? ''}</td>
                  <td>
                    <button class="btn btn-warning btn-sm edit-user" data-id="${user.id}" data-username="${user.username}" data-email="${user.email}">Rediģēt</button>
                    ${user.username === 'admin' ? '' : `<button class="btn btn-danger btn-sm delete-user" data-id="${user.id}" data-username="${user.username}">Dzēst</button>`}
                  </td>
                </tr>`;
              });
              $('#users_table_body').html(rows || '<tr><td colspan="5">Lietotāju nav</td></tr>');
              $('.edit-user').on('click', handleEditUser);
              $('.delete-user').on('click', handleDeleteUser);
            }
          },
          error: function () {
            $('#users_table_body').html('<tr><td colspan="5">Neizdevās ielādēt lietotājus</td></tr>');
          }
        });
      }

      function handleEditUser() {
        $('#edit_user_id').val($(this).data('id'));
        $('#edit_username').val($(this).data('username'));
        $('#edit_email').val($(this).data('email'));
        $('#edit_password').val('');
        $('#edit_user_modal').modal('show');
      }

      function handleDeleteUser() {
        const userId = $(this).data('id');
        const username = $(this).data('username');

        Swal.fire({
          title: 'Dzēst lietotāju?',
          text: `Lietotājs "${username}" tiks neatgriezeniski dzēsts.`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Jā, dzēst',
          cancelButtonText: 'Atcelt'
        }).then((result) => {
          if (!result.isConfirmed) {
            return;
          }

          $.ajax({
            url: `<?= base_url('admin/users/delete') ?>/${userId}`,
            method: 'delete',
            data: {
              '<?= csrf_token() ?>': getCsrfToken()
            },
            success: function (response) {
              updateCsrfToken(response.csrfToken);
              if (response.success) {
                Swal.fire('Dzēsts', response.message, 'success');
                fetchUsers();
              } else {
                Swal.fire('Kļūda', response.message, 'error');
              }
            },
            error: function (xhr) {
              const response = xhr.responseJSON || {};
              updateCsrfToken(response.csrfToken);
              Swal.fire('Kļūda', response.message || 'Neizdevās dzēst lietotāju.', 'error');
            }
          });
        });
      }

      $('#edit_user_form').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
          url: '<?= base_url('admin/users/update') ?>',
          method: 'post',
          data: {
            id: $('#edit_user_id').val(),
            username: $('#edit_username').val(),
            email: $('#edit_email').val(),
            password: $('#edit_password').val(),
            '<?= csrf_token() ?>': getCsrfToken()
          },
          success: function (response) {
            updateCsrfToken(response.csrfToken);
            if (response.success) {
              Swal.fire('Saglabāts', response.message, 'success');
              $('#edit_user_modal').modal('hide');
              fetchUsers();
            } else {
              Swal.fire('Kļūda', response.message, 'error');
            }
          },
          error: function (xhr) {
            const response = xhr.responseJSON || {};
            Swal.fire('Kļūda', response.message || 'Neizdevās atjaunināt lietotāju.', 'error');
          }
        });
      });

      fetchUsers();
    });
  </script>
</body>

</html>
