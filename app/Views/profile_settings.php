<!DOCTYPE html>
<html lang="lv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iestatījumi | AgriConnect</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= base_url('css/agriconnect.css') ?>" rel="stylesheet">
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
    <a class="ag-icon-btn" href="<?= base_url('profile') ?>" title="Profils"><i data-lucide="circle-user-round"></i></a>
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
          <h1>Iestatījumi</h1>
          <p>Pārvaldi profila bildi, e-pastu un paroli.</p>
        </div>
        <a href="<?= base_url('profile') ?>" class="btn btn-outline-secondary">Atpakaļ uz profilu</a>
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

      <div class="ag-grid mt-4">
        <div class="ag-card">
          <h2 class="ag-section-title">Profila bilde</h2>
          <form action="<?= base_url('profile/photo') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="mb-3">
              <label for="profile_image" class="form-label">Izvēlies jaunu bildi</label>
              <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Saglabāt bildi</button>
          </form>
        </div>

        <div class="ag-card">
          <h2 class="ag-section-title">E-pasts</h2>
          <form action="<?= base_url('profile/email') ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
              <label for="email" class="form-label">Jaunais e-pasts</label>
              <input type="email" class="form-control" id="email" name="email" value="<?= esc($email) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Nomainīt e-pastu</button>
          </form>
        </div>

        <div class="ag-card">
          <h2 class="ag-section-title">Paroles maiņa</h2>
          <form action="<?= base_url('profile/password') ?>" method="post">
            <?= csrf_field() ?>
            <div class="mb-3">
              <label for="current_password" class="form-label">Pašreizējā parole</label>
              <input type="password" class="form-control" id="current_password" name="current_password" required>
            </div>
            <div class="mb-3">
              <label for="new_password" class="form-label">Jaunā parole</label>
              <input type="password" class="form-control" id="new_password" name="new_password" minlength="8" required>
            </div>
            <div class="mb-3">
              <label for="confirm_password" class="form-label">Atkārto jauno paroli</label>
              <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="8" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Nomainīt paroli</button>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
  if (window.lucide) {
    lucide.createIcons();
  }
</script>
</body>
</html>
