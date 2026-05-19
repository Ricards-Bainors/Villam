<!DOCTYPE html>
<html lang="lv">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <title>Kategorijas | AgriConnect</title>
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
    <label class="ag-search" aria-label="Meklēt kategorijas">
      <i data-lucide="search"></i>
      <input type="search" placeholder="Meklēt kategorijas...">
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
        <a class="active" href="<?= base_url('categories') ?>"><i data-lucide="tags"></i> Kategorijas</a>
        <a href="<?= base_url('admin/users') ?>"><i data-lucide="users-round"></i> Lietotāji</a>
      </nav>
    </aside>

    <main class="ag-main">
      <div class="ag-content-narrow">
        <div class="ag-page-header">
          <div>
            <h1>Kategorijas</h1>
            <p>Administratora sadaļa kategoriju apskatei un labošanai.</p>
          </div>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_category_modal">Pievienot kategoriju</button>
        </div>

        <div class="ag-table-wrap" id="categories_table">
          <table class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Nosaukums</th>
                <th>Izveidots</th>
                <th>Darbības</th>
              </tr>
            </thead>
            <tbody id="categories_table_body"></tbody>
          </table>
        </div>
      </div>
    </main>
  </div>

  <div class="modal fade" id="add_category_modal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addCategoryModalLabel">Pievienot kategoriju</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Aizvērt"></button>
        </div>
        <div class="modal-body">
          <form id="add_category_form">
            <div class="mb-3">
              <label for="category_name" class="form-label">Kategorijas nosaukums</label>
              <input type="text" class="form-control" id="category_name" name="category_name" required>
            </div>
            <button type="submit" class="btn btn-primary">Pievienot kategoriju</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="edit_category_modal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editCategoryModalLabel">Rediģēt kategoriju</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Aizvērt"></button>
        </div>
        <div class="modal-body">
          <form id="edit_category_form">
            <input type="hidden" id="edit_category_id" name="id">
            <div class="mb-3">
              <label for="edit_category_name" class="form-label">Kategorijas nosaukums</label>
              <input type="text" class="form-control" id="edit_category_name" name="category_name" required>
            </div>
            <button type="submit" class="btn btn-primary">Atjaunināt kategoriju</button>
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

      function fetchCategories() {
        $.ajax({
          url: '<?= base_url('categories/fetch') ?>',
          method: 'get',
          success: function (response) {
            if (response.success) {
              let rows = '';
              response.data.forEach(item => {
                rows += `<tr>
                  <td>${item.id}</td>
                  <td>${item.category_name}</td>
                  <td>${item.created_at ?? ''}</td>
                  <td>
                    <button class="btn btn-warning btn-sm edit-category" data-id="${item.id}" data-name="${item.category_name}">Rediģēt</button>
                    <button class="btn btn-danger btn-sm delete-category" data-id="${item.id}">Dzēst</button>
                  </td>
                </tr>`;
              });
              $('#categories_table_body').html(rows || '<tr><td colspan="4">Datu nav</td></tr>');
              $('.edit-category').on('click', handleEditCategory);
              $('.delete-category').on('click', handleDeleteCategory);
            }
          },
          error: function () {
            $('#categories_table_body').html('<tr><td colspan="4">Neizdevās ielādēt datus</td></tr>');
          }
        });
      }

      $('#add_category_form').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
          url: '<?= base_url('categories/add') ?>',
          method: 'post',
          data: {
            category_name: $('#category_name').val(),
            '<?= csrf_token() ?>': getCsrfToken()
          },
          success: function (response) {
            updateCsrfToken(response.csrfToken);
            if (response.success) {
              Swal.fire('Izdevās', response.message, 'success');
              $('#add_category_modal').modal('hide');
              $('#add_category_form')[0].reset();
              fetchCategories();
            } else {
              Swal.fire('Kļūda', response.message, 'error');
            }
          },
          error: function (xhr) {
            const response = xhr.responseJSON || {};
            Swal.fire('Kļūda', response.message || 'Neizdevās pievienot kategoriju.', 'error');
          }
        });
      });

      function handleEditCategory() {
        $('#edit_category_id').val($(this).data('id'));
        $('#edit_category_name').val($(this).data('name'));
        $('#edit_category_modal').modal('show');
      }

      $('#edit_category_form').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
          url: '<?= base_url('categories/update') ?>',
          method: 'post',
          data: {
            id: $('#edit_category_id').val(),
            category_name: $('#edit_category_name').val(),
            '<?= csrf_token() ?>': getCsrfToken()
          },
          success: function (response) {
            updateCsrfToken(response.csrfToken);
            if (response.success) {
              Swal.fire('Izdevās', response.message, 'success');
              $('#edit_category_modal').modal('hide');
              fetchCategories();
            } else {
              Swal.fire('Kļūda', response.message, 'error');
            }
          },
          error: function (xhr) {
            const response = xhr.responseJSON || {};
            Swal.fire('Kļūda', response.message || 'Neizdevās atjaunināt kategoriju.', 'error');
          }
        });
      });

      function handleDeleteCategory() {
        const id = $(this).data('id');
        Swal.fire({
          title: 'Vai tiešām?',
          text: 'Šo darbību nevarēs atsaukt!',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Jā, dzēst!',
          cancelButtonText: 'Atcelt'
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: '<?= base_url('categories/delete') ?>/' + id,
              method: 'post',
              data: {
                '<?= csrf_token() ?>': getCsrfToken()
              },
              success: function (response) {
                updateCsrfToken(response.csrfToken);
                if (response.success) {
                  Swal.fire('Dzēsts!', response.message, 'success');
                  fetchCategories();
                } else {
                  Swal.fire('Kļūda', response.message, 'error');
                }
              },
              error: function (xhr) {
                const response = xhr.responseJSON || {};
                Swal.fire('Kļūda', response.message || 'Neizdevās dzēst kategoriju.', 'error');
              }
            });
          }
        });
      }

      fetchCategories();
    });
  </script>
</body>

</html>
