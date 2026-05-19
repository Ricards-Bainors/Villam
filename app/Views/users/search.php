<!DOCTYPE html>
<html lang="lv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <title>Lietotāju meklēšana | AgriConnect</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= base_url('css/agriconnect.css') ?>" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>

  <style>
    .user-search-shell {
      min-height: 100vh;
      padding: 48px 18px;
      background: var(--ag-bg);
    }

    .user-search-panel {
      width: min(100%, 920px);
      margin: 0 auto;
    }

    .user-search-card {
      border: 1px solid var(--ag-border);
      border-radius: 12px;
      background: var(--ag-panel);
      padding: 28px;
    }

    .user-search-form {
      display: grid;
      grid-template-columns: minmax(0, 1fr) auto;
      gap: 12px;
      margin-top: 22px;
    }

    .user-result {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 18px;
      padding: 18px 0;
      border-bottom: 1px solid var(--ag-line);
    }

    .user-result:last-child {
      border-bottom: 0;
      padding-bottom: 0;
    }

    .user-result-main {
      display: flex;
      align-items: center;
      gap: 14px;
      min-width: 0;
    }

    .user-result-main h2 {
      margin: 0;
      font-size: 18px;
      font-weight: 800;
    }

    .user-result-main p {
      margin: 2px 0 0;
      color: var(--ag-muted);
      word-break: break-word;
    }

    @media (max-width: 640px) {
      .user-search-form,
      .user-result {
        grid-template-columns: 1fr;
        align-items: stretch;
      }

      .user-search-form {
        display: grid;
      }

      .user-result {
        display: grid;
      }
    }
  </style>
</head>

<body class="ag-app">
  <main class="user-search-shell">
    <div class="user-search-panel">
      <div class="ag-page-header">
        <div>
          <h1>Lietotāju meklēšana</h1>
          <p>Atrodi lietotāju pēc lietotājvārda vai e-pasta un sāc saraksti.</p>
        </div>
        <a class="btn btn-outline-secondary" href="<?= base_url('messages') ?>">Sarakstes</a>
      </div>

      <section class="user-search-card">
        <form class="user-search-form" method="get" action="<?= base_url('users/search') ?>">
          <input
            type="search"
            class="form-control"
            name="q"
            value="<?= esc($query) ?>"
            placeholder="Meklēt pēc username vai email..."
            aria-label="Meklēt lietotāju"
            required
          >
          <button type="submit" class="btn btn-primary">
            Meklēt
          </button>
        </form>
      </section>

      <section class="user-search-card mt-3">
        <?php if ($query === ''): ?>
          <p class="ag-muted mb-0">Ievadi meklēšanas tekstu, lai atrastu lietotājus.</p>
        <?php elseif (empty($users)): ?>
          <p class="ag-muted mb-0">Lietotāji netika atrasti.</p>
        <?php else: ?>
          <?php foreach ($users as $user): ?>
            <div class="user-result">
              <div class="user-result-main">
                <span class="ag-avatar">
                  <?php if (!empty($user['profile_image'])): ?>
                    <img src="<?= base_url($user['profile_image']) ?>" alt="<?= esc($user['username']) ?>">
                  <?php else: ?>
                    <?= esc(strtoupper(substr($user['username'] ?? 'L', 0, 2))) ?>
                  <?php endif; ?>
                </span>
                <div>
                  <h2><?= esc($user['username']) ?></h2>
                  <p><?= esc($user['email']) ?></p>
                </div>
              </div>

              <button
                type="button"
                class="btn btn-primary start-conversation-btn"
                data-user-id="<?= esc($user['id']) ?>"
              >
                Sākt saraksti
              </button>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </section>
    </div>
  </main>

  <script>
    function getCsrfToken() {
      return document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    }

    function updateCsrfToken(token) {
      if (token) {
        document.querySelector('meta[name="csrf-token"]').setAttribute('content', token);
      }
    }

    document.querySelectorAll('.start-conversation-btn').forEach(function(button) {
      button.addEventListener('click', function() {
        const userId = this.dataset.userId;
        const formData = new FormData();
        formData.append('start_type', 'user');
        formData.append('<?= csrf_token() ?>', getCsrfToken());

        this.disabled = true;
        this.textContent = 'Atver...';

        fetch(`<?= base_url('messages/start') ?>/${userId}`, {
          method: 'POST',
          body: formData
        })
          .then(function(response) {
            return response.json();
          })
          .then(function(response) {
            updateCsrfToken(response.csrfToken);

            if (response.success) {
              window.location.href = `<?= base_url('messages') ?>?conversation=${response.conversation_id}`;
              return;
            }

            Swal.fire('Kļūda', response.message || 'Neizdevās sākt saraksti.', 'error');
            button.disabled = false;
            button.textContent = 'Sākt saraksti';
          })
          .catch(function() {
            Swal.fire('Kļūda', 'Neizdevās sākt saraksti.', 'error');
            button.disabled = false;
            button.textContent = 'Sākt saraksti';
          });
      });
    });

    if (window.lucide) {
      lucide.createIcons();
    }
  </script>
</body>
</html>
