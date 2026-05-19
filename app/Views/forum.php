<!DOCTYPE html>
<html lang="lv">
<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Forums | AgriConnect</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= base_url('css/agriconnect.css') ?>" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>

<body class="ag-app">

<header class="ag-topbar">
  <a class="ag-brand" href="<?= base_url('posts') ?>">AgriConnect</a>
  <label class="ag-search" aria-label="Meklēt diskusijas">
    <i data-lucide="search"></i>
    <input type="search" id="searchDiscussion" placeholder="Meklēt diskusijas...">
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
      <a class="active" href="<?= base_url('forum') ?>"><i data-lucide="messages-square"></i> Forums</a>
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
          <h1>Foruma diskusijas</h1>
          <p>Apspried lauku darbus, tehnikas izvēli un tirgus izmaiņas.</p>
        </div>
        <div class="ag-actions">
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDiscussionModal">Sākt diskusiju</button>
        </div>
      </div>

      <div id="discussionList">
        <div class="ag-card">Ielādē diskusijas...</div>
      </div>
    </div>
  </main>
</div>

<!-- Add Discussion Modal -->
<div class="modal fade" id="addDiscussionModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Sākt jaunu diskusiju</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form id="addDiscussionForm">
        <div class="modal-body">

          <div class="mb-3">
            <label class="form-label">Diskusijas virsraksts</label>
            <input type="text" name="title" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Diskusijas teksts</label>
            <textarea name="body" class="form-control" rows="5" required></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Kategorija (nav obligāta)</label>
            <select name="category_id" id="discussionCategory" class="form-select">
              <option value="">Bez kategorijas</option>
            </select>
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success w-100">Izveidot diskusiju</button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- Edit Discussion Modal -->
<div class="modal fade" id="editDiscussionModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Rediģēt diskusiju</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form id="editDiscussionForm">
        <input type="hidden" name="id" id="editDiscussionId">
        <div class="modal-body">

          <div class="mb-3">
            <label class="form-label">Diskusijas virsraksts</label>
            <input type="text" name="title" id="editDiscussionTitle" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Diskusijas teksts</label>
            <textarea name="body" id="editDiscussionBody" class="form-control" rows="5" required></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Statuss</label>
            <select name="status" id="editDiscussionStatus" class="form-select">
              <option value="open">Atvērta</option>
              <option value="closed">Slēgta</option>
            </select>
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success w-100">Saglabāt izmaiņas</button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="discussionDetailModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="discussionDetailTitle">Diskusija</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="reply_discussion_id">

        <div id="discussionDetailBody"></div>

        <hr>

        <h5>Atbildes</h5>
        <div id="repliesList" class="mb-3">
            Ielādē atbildes...
        </div>

        <textarea id="replyText" class="form-control mb-2" rows="3" placeholder="Uzraksti atbildi..."></textarea>

        <button class="btn btn-primary w-100" onclick="addReply()">
            Pievienot atbildi
        </button>
        </div>

    </div>
  </div>
</div>

<script>
let allDiscussions = [];

function getCsrfToken() {
  return $('meta[name="csrf-token"]').attr('content');
}

function updateCsrfToken(token) {
  if (token) {
    $('meta[name="csrf-token"]').attr('content', token);
  }
}

function discussionStatusLabel(status) {
  return status === 'closed' ? 'Slēgta' : 'Atvērta';
}

function fetchDiscussions() {
  $.ajax({
    url: '<?= base_url('forum/fetch') ?>',
    method: 'GET',
    success: function(response) {
      if (response.success) {
        allDiscussions = response.data;
        renderDiscussions(allDiscussions);
      } else {
        $('#discussionList').html('<p>Neizdevās ielādēt diskusijas.</p>');
      }
    },
    error: function() {
      $('#discussionList').html('<p>Kļūda, ielādējot diskusijas.</p>');
    }
  });
}

function fetchDiscussionCategories() {
  $.ajax({
    url: '<?= base_url('categories/fetch') ?>',
    method: 'GET',
    success: function(response) {
      if (response.success) {
        let options = '<option value="">Bez kategorijas</option>';

        response.data.forEach(category => {
          options += `<option value="${category.id}">${category.category_name}</option>`;
        });

        $('#discussionCategory').html(options);
      }
    }
  });
}



function renderDiscussions(discussions) {
  if (discussions.length === 0) {
    $('#discussionList').html('<div class="ag-card">Diskusiju vēl nav.</div>');
    return;
  }

  let html = '';

  discussions.forEach(discussion => {
    const manageButtons = discussion.can_manage
      ? `<div class="ag-post-admin mt-3">
          <button class="btn btn-outline-secondary btn-sm" onclick="editDiscussion(${discussion.id})">Rediģēt</button>
          <button class="btn btn-danger btn-sm" onclick="deleteDiscussion(${discussion.id})">Dzēst</button>
        </div>`
      : '';

    html += `
      <article class="ag-card ag-discussion-card">
        <div class="ag-post-head">
          <span class="ag-avatar">${(discussion.title || 'D').substring(0, 2).toUpperCase()}</span>
          <div class="ag-post-meta">
            <h3>${discussion.title}</h3>
            <p>${discussion.category ?? 'Nav kategorijas'} • ${discussionStatusLabel(discussion.status)} • ${discussion.created_at}</p>
          </div>
        </div>
        <p class="ag-post-body">${discussion.body.substring(0, 180)}...</p>
        <button class="btn btn-primary btn-sm" onclick="viewDiscussion(${discussion.id})">Atvērt diskusiju</button>
        ${manageButtons}
      </article>
    `;
  });

  $('#discussionList').html(html);
  if (window.lucide) {
    lucide.createIcons();
  }
}

function viewDiscussion(id) {
  $.ajax({
    url: `<?= base_url('forum/detail') ?>/${id}`,
    method: 'GET',
    success: function(response) {
      if (response.success) {
        const discussion = response.data;

        $('#reply_discussion_id').val(discussion.id);
        $('#replyText').val('');

        $('#discussionDetailTitle').text(discussion.title);

        $('#discussionDetailBody').html(`
          <h4>${discussion.title}</h4>
          <p>${discussion.body}</p>
          <hr>
          <p><strong>Statuss:</strong> ${discussionStatusLabel(discussion.status)}</p>
          <p><strong>Izveidots:</strong> ${discussion.created_at}</p>
        `);

        fetchReplies(discussion.id);
        $('#discussionDetailModal').modal('show');
      }
    },
    error: function() {
      Swal.fire('Kļūda', 'Neizdevās atvērt diskusiju.', 'error');
    }
  });
}

function editDiscussion(id) {
  $.ajax({
    url: `<?= base_url('forum/detail') ?>/${id}`,
    method: 'GET',
    success: function(response) {
      if (response.success) {
        const discussion = response.data;
        $('#editDiscussionId').val(discussion.id);
        $('#editDiscussionTitle').val(discussion.title);
        $('#editDiscussionBody').val(discussion.body);
        $('#editDiscussionStatus').val(discussion.status === 'closed' ? 'closed' : 'open');
        $('#editDiscussionModal').modal('show');
      }
    },
    error: function(xhr) {
      const response = xhr.responseJSON || {};
      Swal.fire('Kļūda', response.message || 'Neizdevās ielādēt diskusiju.', 'error');
    }
  });
}

function deleteDiscussion(id) {
  Swal.fire({
    title: 'Dzēst diskusiju?',
    text: 'Tiks dzēstas arī diskusijas atbildes. Šo darbību nevarēs atsaukt.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Jā, dzēst',
    cancelButtonText: 'Atcelt'
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: `<?= base_url('forum/delete') ?>/${id}`,
        method: 'DELETE',
        data: {
          '<?= csrf_token() ?>': getCsrfToken()
        },
        success: function(response) {
          updateCsrfToken(response.csrfToken);

          if (response.success) {
            Swal.fire('Dzēsts', response.message, 'success');
            fetchDiscussions();
          } else {
            Swal.fire('Kļūda', response.message, 'error');
          }
        },
        error: function(xhr) {
          const response = xhr.responseJSON || {};
          Swal.fire('Kļūda', response.message || 'Neizdevās dzēst diskusiju.', 'error');
        }
      });
    }
  });
}

function fetchReplies(discussionId) {
  $('#repliesList').html('Ielādē atbildes...');

  $.ajax({
    url: `<?= base_url('forum/replies') ?>/${discussionId}`,
    method: 'GET',
    success: function(response) {
      if (response.success) {
        renderReplies(response.data, response.current_user_id, response.current_is_admin);
      }
    },
    error: function() {
      $('#repliesList').html('<p>Neizdevās ielādēt atbildes.</p>');
    }
  });
}

function renderReplies(replies, currentUserId, currentIsAdmin) {
  if (replies.length === 0) {
    $('#repliesList').html('<p>Atbilžu vēl nav.</p>');
    return;
  }

  let html = '';

  replies.forEach(reply => {
    const canDelete = currentIsAdmin || String(reply.user_id) === String(currentUserId);

    html += `
      <div class="border rounded p-2 mb-2">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <strong>${reply.user_name ?? 'Nezināms lietotājs'}</strong>
            <p class="mb-1">${reply.reply}</p>
            <small class="text-muted">${reply.created_at}</small>
          </div>

          ${
            canDelete
              ? `<button class="btn btn-danger btn-sm" onclick="deleteReply(${reply.id})">Dzēst</button>`
              : ''
          }
        </div>
      </div>
    `;
  });

  $('#repliesList').html(html);
}


function addReply() {
  const discussionId = $('#reply_discussion_id').val();
  const reply = $('#replyText').val();

  if (!reply.trim()) {
    Swal.fire('Kļūda', 'Atbilde nevar būt tukša.', 'error');
    return;
  }

  const formData = new FormData();

  formData.append('discussion_id', discussionId);
  formData.append('reply', reply);
  formData.append('<?= csrf_token() ?>', getCsrfToken());

  $.ajax({
    url: '<?= base_url('forum/reply/add') ?>',
    method: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success: function(response) {
      updateCsrfToken(response.csrfToken);

      if (response.success) {
        $('#replyText').val('');
        fetchReplies(discussionId);
      } else {
        Swal.fire('Kļūda', response.message, 'error');
      }
    },
    error: function() {
      Swal.fire('Kļūda', 'Neizdevās pievienot atbildi.', 'error');
    }
  });
}

function deleteReply(replyId) {
  const discussionId = $('#reply_discussion_id').val();

  Swal.fire({
    title: 'Dzēst atbildi?',
    text: 'Šo darbību nevarēs atsaukt.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Jā, dzēst'
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: `<?= base_url('forum/reply/delete') ?>/${replyId}`,
        method: 'DELETE',
        data: {
          '<?= csrf_token() ?>': getCsrfToken()
        },
        success: function(response) {
          updateCsrfToken(response.csrfToken);

          if (response.success) {
            fetchReplies(discussionId);
          } else {
            Swal.fire('Kļūda', response.message, 'error');
          }
        },
        error: function() {
          Swal.fire('Kļūda', 'Neizdevās dzēst atbildi.', 'error');
        }
      });
    }
  });
}

$('#addDiscussionForm').on('submit', function(e) {
  e.preventDefault();

  const formData = new FormData(this);
  formData.append('<?= csrf_token() ?>', getCsrfToken());

  $.ajax({
    url: '<?= base_url('forum/add') ?>',
    method: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success: function(response) {
      updateCsrfToken(response.csrfToken);

      if (response.success) {
        Swal.fire('Izveidots', response.message, 'success');
        $('#addDiscussionModal').modal('hide');
        $('#addDiscussionForm')[0].reset();
        fetchDiscussions();
      } else {
        Swal.fire('Kļūda', response.message, 'error');
      }
    },
    error: function() {
      Swal.fire('Kļūda', 'Neizdevās izveidot diskusiju.', 'error');
    }
  });
});

$('#editDiscussionForm').on('submit', function(e) {
  e.preventDefault();

  const formData = new FormData(this);
  formData.append('<?= csrf_token() ?>', getCsrfToken());

  $.ajax({
    url: '<?= base_url('forum/update') ?>',
    method: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success: function(response) {
      updateCsrfToken(response.csrfToken);

      if (response.success) {
        Swal.fire('Saglabāts', response.message, 'success');
        $('#editDiscussionModal').modal('hide');
        fetchDiscussions();
      } else {
        Swal.fire('Kļūda', response.message, 'error');
      }
    },
    error: function(xhr) {
      const response = xhr.responseJSON || {};
      Swal.fire('Kļūda', response.message || 'Neizdevās atjaunināt diskusiju.', 'error');
    }
  });
});

$('#searchDiscussion').on('keyup', function() {
  const keyword = $(this).val().toLowerCase();

  const filtered = allDiscussions.filter(discussion => {
    return (
      discussion.title.toLowerCase().includes(keyword) ||
      discussion.body.toLowerCase().includes(keyword) ||
      (discussion.category && discussion.category.toLowerCase().includes(keyword))
    );
  });

  renderDiscussions(filtered);
});

$(document).ready(function() {
  if (window.lucide) {
    lucide.createIcons();
  }
  fetchDiscussionCategories();
  fetchDiscussions();
});
</script>

</body>
</html>
