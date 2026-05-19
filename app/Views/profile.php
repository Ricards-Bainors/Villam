<!DOCTYPE html>
<html lang="lv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <title>Profils | AgriConnect</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= base_url('css/agriconnect.css') ?>" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>

<body class="ag-app">

<header class="ag-topbar">
  <a class="ag-brand" href="<?= base_url('posts') ?>">AgriConnect</a>
  <label class="ag-search" aria-label="Meklēt">
    <i data-lucide="search"></i>
    <input type="search" placeholder="Meklēt...">
  </label>
  <div class="ag-top-actions">
    <a class="ag-icon-btn" href="<?= base_url('posts') ?>" title="Ziņu plūsma"><i data-lucide="rss"></i></a>
    <a class="ag-icon-btn" href="<?= base_url('profile/settings') ?>" title="Iestatījumi"><i data-lucide="settings"></i></a>
    <a class="ag-icon-btn" href="<?= base_url('auth/logout') ?>" title="Izrakstīties"><i data-lucide="log-out"></i></a>
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
      <?php if (session()->get('username') === 'admin'): ?>
        <a href="<?= base_url('categories') ?>"><i data-lucide="tags"></i> Kategorijas</a>
        <a href="<?= base_url('admin/users') ?>"><i data-lucide="users-round"></i> Lietotāji</a>
      <?php endif; ?>
    </nav>
  </aside>

  <main class="ag-main">
    <div class="ag-content-narrow">
      <div class="ag-page-header">
        <div>
          <h1>Mans profils</h1>
          <p>Tava AgriConnect konta informācija.</p>
        </div>
        <a href="<?= base_url('auth/logout') ?>" class="btn btn-outline-secondary">Izrakstīties</a>
      </div>

      <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
      <?php endif; ?>

      <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
      <?php endif; ?>

      <div class="ag-card">
        <div class="ag-post-head">
          <span class="ag-avatar">
            <?php if (!empty($profile_image)): ?>
              <img src="<?= base_url($profile_image) ?>" alt="<?= esc($username) ?>">
            <?php else: ?>
              <?= esc(strtoupper(substr($username, 0, 2))) ?>
            <?php endif; ?>
          </span>
          <div class="ag-post-meta">
            <h3><?= esc($username) ?></h3>
            <p><?= esc($email) ?></p>
          </div>
        </div>
      </div>

      <div class="ag-page-header mt-5">
        <div>
          <h1>Mani ieraksti</h1>
          <p>Visi ieraksti, kurus esi publicējis AgriConnect.</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPostModal">
          Pievienot ierakstu
        </button>
      </div>

      <?php if (empty($posts)): ?>
        <div class="ag-card">Tu vēl neesi pievienojis nevienu ierakstu.</div>
      <?php else: ?>
        <?php foreach ($posts as $post): ?>
          <article class="ag-card ag-post">
            <div class="ag-post-head">
              <span class="ag-avatar"><?= esc(strtoupper(substr($post['title'] ?? 'I', 0, 2))) ?></span>
              <div class="ag-post-meta">
                <h3><?= esc($post['title']) ?></h3>
                <p><?= esc($post['category'] ?? 'Nav kategorijas') ?> • <?= esc($post['created_at']) ?></p>
              </div>
            </div>

            <p class="ag-post-body"><?= nl2br(esc($post['body'])) ?></p>

            <?php if (!empty($post['images'])): ?>
              <div class="ag-post-images">
                <?php foreach ($post['images'] as $image): ?>
                  <?php $imagePath = ltrim((string) $image, '/'); ?>
                  <img src="<?= base_url($imagePath) ?>" alt="<?= esc($post['title']) ?>" loading="lazy">
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </main>
</div>

<div class="modal fade" id="addPostModal" tabindex="-1" aria-labelledby="addPostModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form id="addPostForm" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="addPostModalLabel">Pievienot jaunu ierakstu</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Aizvērt"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="postTitle" class="form-label">Virsraksts</label>
            <input type="text" class="form-control" id="postTitle" name="title" required>
          </div>

          <div class="mb-3">
            <label for="postCategory" class="form-label">Kategorija</label>
            <select class="form-select" id="postCategory" name="category" required>
              <option value="">Izvēlies kategoriju</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="postBody" class="form-label">Saturs</label>
            <textarea class="form-control" id="postBody" name="body" rows="5" required></textarea>
          </div>

          <div class="mb-3">
            <label for="postImages" class="form-label">Attēli</label>
            <input type="file" class="form-control" id="postImages" name="images[]" accept="image/*" multiple>
          </div>

          <div id="postImagePreview" class="row g-2"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Atcelt</button>
          <button type="submit" class="btn btn-primary">Publicēt</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  let selectedPostImages = [];

  function getCsrfToken() {
    return $('meta[name="csrf-token"]').attr('content');
  }

  function updateCsrfToken(token) {
    if (token) {
      $('meta[name="csrf-token"]').attr('content', token);
    }
  }

  function resetPostForm() {
    selectedPostImages = [];
    $('#addPostForm')[0].reset();
    $('#postImagePreview').empty();
  }

  function fetchPostCategories() {
    $.ajax({
      url: '<?= base_url('categories/fetch') ?>',
      type: 'GET',
      dataType: 'json',
      success: function(response) {
        const categorySelect = $('#postCategory');
        categorySelect.html('<option value="">Izvēlies kategoriju</option>');

        if (response.success && Array.isArray(response.data)) {
          response.data.forEach(function(category) {
            categorySelect.append(
              $('<option>', {
                value: category.id,
                text: category.category_name
              })
            );
          });
        }
      },
      error: function() {
        $('#postCategory').html('<option value="">Kategorijas neizdevās ielādēt</option>');
      }
    });
  }

  $(document).ready(function() {
    if (window.lucide) {
      lucide.createIcons();
    }

    fetchPostCategories();

    $('#postImages').on('change', function(event) {
      selectedPostImages = Array.from(event.target.files);
      const preview = $('#postImagePreview');
      preview.empty();

      selectedPostImages.forEach(function(file) {
        if (!file.type.startsWith('image/')) {
          return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
          preview.append(`
            <div class="col-6 col-md-3">
              <img src="${e.target.result}" class="img-fluid rounded" alt="Ieraksta attēla priekšskatījums">
            </div>
          `);
        };
        reader.readAsDataURL(file);
      });
    });

    $('#addPostForm').on('submit', function(event) {
      event.preventDefault();

      const submitButton = $(this).find('button[type="submit"]');
      const formData = new FormData(this);
      formData.append('<?= csrf_token() ?>', getCsrfToken());

      submitButton.prop('disabled', true).text('Publicē...');

      $.ajax({
        url: '<?= base_url('post/add') ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
          updateCsrfToken(response.csrfToken);

          if (response.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('addPostModal'));
            if (modal) {
              modal.hide();
            }

            resetPostForm();

            Swal.fire({
              icon: 'success',
              title: 'Ieraksts pievienots',
              text: response.message || 'Tavs ieraksts ir publicēts.',
              timer: 1400,
              showConfirmButton: false
            }).then(function() {
              window.location.reload();
            });
          } else {
            Swal.fire('Kļūda', response.message || 'Neizdevās pievienot ierakstu.', 'error');
          }
        },
        error: function(xhr) {
          if (xhr.responseJSON && xhr.responseJSON.csrfToken) {
            updateCsrfToken(xhr.responseJSON.csrfToken);
          }

          Swal.fire('Kļūda', 'Pievienojot ierakstu, radās kļūda.', 'error');
        },
        complete: function() {
          submitButton.prop('disabled', false).text('Publicēt');
        }
      });
    });
  });
</script>
</body>
</html>
