<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container my-4">
  <div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h3 class="m-0">My Profile</h3>

      <div>
        <a href="<?= base_url('/') ?>" class="btn btn-secondary">Back to Posts</a>
        <a href="<?= base_url('advertisements') ?>" class="btn btn-warning">Marketplace</a>
        <a href="<?= base_url('forum') ?>" class="btn btn-info">Forum</a>
        <a href="<?= base_url('auth/logout') ?>" class="btn btn-success">Logout</a>
      </div>
    </div>

    <div class="card-body">

      <div class="mb-3">
        <label class="form-label fw-bold">User ID</label>
        <input type="text" class="form-control" value="<?= esc($user_id) ?>" readonly>
      </div>

      <div class="mb-3">
        <label class="form-label fw-bold">Username</label>
        <input type="text" class="form-control" value="<?= esc($username) ?>" readonly>
      </div>

      <div class="mb-3">
        <label class="form-label fw-bold">Email</label>
        <input type="text" class="form-control" value="<?= esc($email) ?>" readonly>
      </div>

    </div>
  </div>
</div>

</body>
</html>