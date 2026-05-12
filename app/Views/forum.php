<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Forum</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>

<div class="container my-4">
  <div class="card shadow">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h3 class="m-0">Forum Discussions</h3>

      <div>
        <a href="<?= base_url('/') ?>" class="btn btn-secondary">Back to Posts</a>
        <a href="<?= base_url('advertisements') ?>" class="btn btn-warning">Marketplace</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDiscussionModal">
          Start Discussion
        </button>
      </div>
    </div>

    <div class="card-body">
      <input type="text" id="searchDiscussion" class="form-control mb-3" placeholder="Search discussions...">

      <div id="discussionList">
        <p>Loading discussions...</p>
      </div>
    </div>
  </div>
</div>

<!-- Add Discussion Modal -->
<div class="modal fade" id="addDiscussionModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Start New Discussion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form id="addDiscussionForm">
        <div class="modal-body">

          <div class="mb-3">
            <label class="form-label">Discussion Title</label>
            <input type="text" name="title" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Discussion Body</label>
            <textarea name="body" class="form-control" rows="5" required></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Category ID optional</label>
            <input type="number" name="category_id" class="form-control">
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success w-100">Create Discussion</button>
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
        <h5 class="modal-title" id="discussionDetailTitle">Discussion</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="reply_discussion_id">

        <div id="discussionDetailBody"></div>

        <hr>

        <h5>Replies</h5>
        <div id="repliesList" class="mb-3">
            Loading replies...
        </div>

        <textarea id="replyText" class="form-control mb-2" rows="3" placeholder="Write a reply..."></textarea>

        <button class="btn btn-primary w-100" onclick="addReply()">
            Add Reply
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

function fetchDiscussions() {
  $.ajax({
    url: '<?= base_url('forum/fetch') ?>',
    method: 'GET',
    success: function(response) {
      if (response.success) {
        allDiscussions = response.data;
        renderDiscussions(allDiscussions);
      } else {
        $('#discussionList').html('<p>Failed to load discussions.</p>');
      }
    },
    error: function() {
      $('#discussionList').html('<p>Error loading discussions.</p>');
    }
  });
}



function renderDiscussions(discussions) {
  if (discussions.length === 0) {
    $('#discussionList').html('<p>No discussions yet.</p>');
    return;
  }

  let html = '';

  discussions.forEach(discussion => {
    html += `
      <div class="card mb-3">
        <div class="card-body">
          <h5>${discussion.title}</h5>
          <p>${discussion.body.substring(0, 150)}...</p>
          <p class="text-muted mb-1">
            Category: ${discussion.category ?? 'No category'} |
            Status: ${discussion.status}
          </p>
          <p class="text-muted">Created: ${discussion.created_at}</p>

          <button class="btn btn-primary btn-sm" onclick="viewDiscussion(${discussion.id})">
            Open Discussion
          </button>
        </div>
      </div>
    `;
  });

  $('#discussionList').html(html);
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
          <p><strong>Status:</strong> ${discussion.status}</p>
          <p><strong>Created:</strong> ${discussion.created_at}</p>
        `);

        fetchReplies(discussion.id);
        $('#discussionDetailModal').modal('show');
      }
    },
    error: function() {
      Swal.fire('Error', 'Failed to open discussion.', 'error');
    }
  });
}

function fetchReplies(discussionId) {
  $('#repliesList').html('Loading replies...');

  $.ajax({
    url: `<?= base_url('forum/replies') ?>/${discussionId}`,
    method: 'GET',
    success: function(response) {
      if (response.success) {
        renderReplies(response.data, response.current_user_id);
      }
    },
    error: function() {
      $('#repliesList').html('<p>Failed to load replies.</p>');
    }
  });
}

function renderReplies(replies, currentUserId) {
  if (replies.length === 0) {
    $('#repliesList').html('<p>No replies yet.</p>');
    return;
  }

  let html = '';

  replies.forEach(reply => {
    const canDelete = String(reply.user_id) === String(currentUserId);

    html += `
      <div class="border rounded p-2 mb-2">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <strong>${reply.user_name ?? 'Unknown user'}</strong>
            <p class="mb-1">${reply.reply}</p>
            <small class="text-muted">${reply.created_at}</small>
          </div>

          ${
            canDelete
              ? `<button class="btn btn-danger btn-sm" onclick="deleteReply(${reply.id})">Delete</button>`
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
    Swal.fire('Error', 'Reply cannot be empty.', 'error');
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
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function() {
      Swal.fire('Error', 'Failed to add reply.', 'error');
    }
  });
}

function deleteReply(replyId) {
  const discussionId = $('#reply_discussion_id').val();

  Swal.fire({
    title: 'Delete reply?',
    text: 'This cannot be undone.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it'
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
            Swal.fire('Error', response.message, 'error');
          }
        },
        error: function() {
          Swal.fire('Error', 'Failed to delete reply.', 'error');
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
        Swal.fire('Created', response.message, 'success');
        $('#addDiscussionModal').modal('hide');
        $('#addDiscussionForm')[0].reset();
        fetchDiscussions();
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function() {
      Swal.fire('Error', 'Failed to create discussion.', 'error');
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
  fetchDiscussions();
});
</script>

</body>
</html>