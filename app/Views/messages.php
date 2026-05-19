<!DOCTYPE html>
<html lang="lv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <title>Sarakstes | AgriConnect</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= base_url('css/agriconnect.css') ?>" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>

<body class="ag-app">
<header class="ag-topbar">
  <a class="ag-brand" href="<?= base_url('posts') ?>">AgriConnect</a>
  <label class="ag-search" aria-label="Meklēt sarakstēs">
    <i data-lucide="search"></i>
    <input type="search" id="conversationSearch" placeholder="Meklēt sarakstes...">
    <div id="topUserSearchResults" class="ag-search-results d-none"></div>
  </label>
  <div class="ag-top-actions">
    <a class="ag-icon-btn" href="<?= base_url('advertisements') ?>" title="Sludinājumi"><i data-lucide="store"></i></a>
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
      <a class="active" href="<?= base_url('messages') ?>"><i data-lucide="message-circle"></i> Sarakstes</a>
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
          <h1>Sarakstes</h1>
          <p>Ziņas par sludinājumiem starp pircēju un pārdevēju.</p>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-lg-4">
          <div class="ag-card">
            <h2 class="ag-section-title">Sarunas</h2>
            <input
              type="search"
              class="form-control mb-3"
              id="userConversationSearch"
              placeholder="Meklēt lietotāju..."
              aria-label="Meklēt lietotāju sarakstēs"
            >
            <div id="conversationList" class="ag-side-stack">
              <div class="ag-muted">Ielādē sarakstes...</div>
            </div>
          </div>
        </div>

        <div class="col-lg-8">
          <div class="ag-card">
            <div id="threadHeader" class="mb-3">
              <h2 class="ag-section-title">Izvēlies saraksti</h2>
              <p class="ag-muted mb-0">Atver sarunu, lai lasītu vai nosūtītu ziņu.</p>
            </div>

            <div id="messageList" class="mb-3" style="max-height: 460px; overflow-y: auto;">
              <div class="ag-muted">Ziņas parādīsies šeit.</div>
            </div>

            <form id="messageForm" class="d-none">
              <input type="hidden" id="conversationId" name="conversation_id">
              <div class="mb-2">
                <textarea class="form-control" id="messageText" name="message" rows="3" placeholder="Uzraksti ziņu..." required></textarea>
              </div>
              <button type="submit" class="btn btn-primary w-100">Nosūtīt</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
let allConversations = [];
let currentUserId = null;

function getCsrfToken() {
  return $('meta[name="csrf-token"]').attr('content');
}

function updateCsrfToken(token) {
  if (token) {
    $('meta[name="csrf-token"]').attr('content', token);
  }
}

function escapeHtml(value) {
  return $('<div>').text(value ?? '').html();
}

function getUrlConversationId() {
  return new URLSearchParams(window.location.search).get('conversation');
}

function conversationTitle(conversation) {
  return conversation.advertisement_title || 'Tieša sarakste';
}

function fetchConversations(openConversationId = null) {
  $.ajax({
    url: '<?= base_url('messages/list') ?>',
    method: 'GET',
    success: function(response) {
      if (!response.success) {
        $('#conversationList').html('<div class="ag-muted">Neizdevās ielādēt sarakstes.</div>');
        return;
      }

      allConversations = response.data;
      currentUserId = response.current_user_id;
      renderConversations(allConversations);

      const conversationId = openConversationId || getUrlConversationId();
      if (conversationId) {
        openConversation(conversationId);
      }
    },
    error: function() {
      $('#conversationList').html('<div class="ag-muted">Neizdevās ielādēt sarakstes.</div>');
    }
  });
}

function renderConversations(conversations) {
  if (conversations.length === 0) {
    $('#conversationList').html('<div class="ag-muted">Sarakstes netika atrastas.</div>');
    return;
  }

  let html = '';

  conversations.forEach(function(conversation) {
    const otherUser = String(conversation.buyer_id) === String(currentUserId)
      ? conversation.seller_name
      : conversation.buyer_name;

    html += `
      <button type="button" class="btn btn-light text-start w-100 mb-2" onclick="openConversation(${conversation.id})">
        <strong>${escapeHtml(conversationTitle(conversation))}</strong><br>
        <span class="ag-muted">${escapeHtml(otherUser || 'Lietotājs')}</span><br>
        <small class="ag-muted">${escapeHtml(conversation.updated_at || conversation.created_at || '')}</small>
      </button>
    `;
  });

  $('#conversationList').html(html);
}

function openConversation(conversationId) {
  $.ajax({
    url: `<?= base_url('messages/thread') ?>/${conversationId}`,
    method: 'GET',
    success: function(response) {
      if (!response.success) {
        Swal.fire('Kļūda', response.message || 'Neizdevās ielādēt saraksti.', 'error');
        return;
      }

      $('#conversationId').val(conversationId);
      $('#messageForm').removeClass('d-none');

      const conversation = response.conversation;
      const otherUser = String(conversation.buyer_id) === String(response.current_user_id)
        ? conversation.seller_name
        : conversation.buyer_name;

      $('#threadHeader').html(`
        <h2 class="ag-section-title">${escapeHtml(conversationTitle(conversation))}</h2>
        <p class="ag-muted mb-0">Sarakste ar ${escapeHtml(otherUser || 'lietotāju')}</p>
      `);

      renderMessages(response.messages, response.current_user_id);
      window.history.replaceState({}, '', `<?= base_url('messages') ?>?conversation=${conversationId}`);
    },
    error: function(xhr) {
      const response = xhr.responseJSON || {};
      Swal.fire('Kļūda', response.message || 'Neizdevās ielādēt saraksti.', 'error');
    }
  });
}

function renderMessages(messages, userId) {
  if (messages.length === 0) {
    $('#messageList').html('<div class="ag-muted">Ziņu vēl nav. Uzraksti pirmo ziņu.</div>');
    return;
  }

  let html = '';

  messages.forEach(function(message) {
    const own = String(message.sender_id) === String(userId);
    html += `
      <div class="d-flex ${own ? 'justify-content-end' : 'justify-content-start'} mb-2">
        <div class="border rounded p-2 ${own ? 'bg-primary text-white' : 'bg-light'}" style="max-width: 75%;">
          <strong>${escapeHtml(message.sender_name || 'Lietotājs')}</strong>
          <p class="mb-1">${escapeHtml(message.message)}</p>
          <small class="${own ? 'text-white-50' : 'text-muted'}">${escapeHtml(message.created_at)}</small>
        </div>
      </div>
    `;
  });

  $('#messageList').html(html);
  $('#messageList').scrollTop($('#messageList')[0].scrollHeight);
}

$('#messageForm').on('submit', function(event) {
  event.preventDefault();

  const formData = new FormData(this);
  formData.append('<?= csrf_token() ?>', getCsrfToken());

  $.ajax({
    url: '<?= base_url('messages/send') ?>',
    method: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success: function(response) {
      updateCsrfToken(response.csrfToken);

      if (response.success) {
        $('#messageText').val('');
        openConversation($('#conversationId').val());
        fetchConversations($('#conversationId').val());
      } else {
        Swal.fire('Kļūda', response.message || 'Neizdevās nosūtīt ziņu.', 'error');
      }
    },
    error: function(xhr) {
      const response = xhr.responseJSON || {};
      if (response.csrfToken) {
        updateCsrfToken(response.csrfToken);
      }
      Swal.fire('Kļūda', response.message || 'Neizdevās nosūtīt ziņu.', 'error');
    }
  });
});

let topUserSearchTimer = null;

function renderTopUserResults(users) {
  if (users.length === 0) {
    $('#topUserSearchResults')
      .removeClass('d-none')
      .html('<div class="p-3 ag-muted">Lietotāji netika atrasti.</div>');
    return;
  }

  let html = '';

  users.forEach(function(user) {
    const initials = String(user.username || 'L').substring(0, 2).toUpperCase();
    const avatar = user.profile_image
      ? `<img src="<?= base_url() ?>/${escapeHtml(user.profile_image)}" alt="${escapeHtml(user.username)}">`
      : escapeHtml(initials);

    html += `
      <div class="ag-search-result">
        <div class="ag-search-result-user">
          <span class="ag-avatar">${avatar}</span>
          <strong>${escapeHtml(user.username)}</strong>
        </div>
        <button type="button" class="btn btn-primary btn-sm" onclick="startConversationWithUser(${user.id})">
          Sākt
        </button>
      </div>
    `;
  });

  $('#topUserSearchResults').removeClass('d-none').html(html);
}

function searchUsersFromTopbar(keyword) {
  $.ajax({
    url: '<?= base_url('users/search-json') ?>',
    method: 'GET',
    data: { q: keyword },
    success: function(response) {
      if (response.success) {
        renderTopUserResults(response.data);
      }
    },
    error: function() {
      $('#topUserSearchResults')
        .removeClass('d-none')
        .html('<div class="p-3 ag-muted">Neizdevās meklēt lietotājus.</div>');
    }
  });
}

function startConversationWithUser(userId) {
  const formData = new FormData();
  formData.append('start_type', 'user');
  formData.append('<?= csrf_token() ?>', getCsrfToken());

  $.ajax({
    url: `<?= base_url('messages/start') ?>/${userId}`,
    method: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success: function(response) {
      updateCsrfToken(response.csrfToken);

      if (response.success) {
        $('#topUserSearchResults').addClass('d-none').empty();
        $('#conversationSearch').val('');
        fetchConversations(response.conversation_id);
        openConversation(response.conversation_id);
      } else {
        Swal.fire('Kļūda', response.message || 'Neizdevās sākt saraksti.', 'error');
      }
    },
    error: function(xhr) {
      const response = xhr.responseJSON || {};
      if (response.csrfToken) {
        updateCsrfToken(response.csrfToken);
      }
      Swal.fire('Kļūda', response.message || 'Neizdevās sākt saraksti.', 'error');
    }
  });
}

$('#conversationSearch').on('keyup', function() {
  const keyword = $(this).val().trim();
  clearTimeout(topUserSearchTimer);

  if (keyword.length < 2) {
    $('#topUserSearchResults').addClass('d-none').empty();
    return;
  }

  topUserSearchTimer = setTimeout(function() {
    searchUsersFromTopbar(keyword);
  }, 250);
});

$(document).on('click', function(event) {
  if (!$(event.target).closest('.ag-search').length) {
    $('#topUserSearchResults').addClass('d-none');
  }
});

$('#userConversationSearch').on('keyup', function() {
  const keyword = $(this).val().toLowerCase();
  const filtered = allConversations.filter(function(conversation) {
    const otherUser = String(conversation.buyer_id) === String(currentUserId)
      ? conversation.seller_name
      : conversation.buyer_name;

    return String(otherUser || '').toLowerCase().includes(keyword);
  });

  renderConversations(filtered);
});

$(document).ready(function() {
  if (window.lucide) {
    lucide.createIcons();
  }

  $('#conversationSearch')
    .attr('autocomplete', 'off')
    .attr('placeholder', 'Meklēt lietotāju...');

  fetchConversations();
});
</script>
</body>
</html>
