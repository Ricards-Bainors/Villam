<!DOCTYPE html>
<html lang="lv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= csrf_hash() ?>">

  <title>Sludinājumi | AgriConnect</title>

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
  <label class="ag-search" aria-label="Meklēt sludinājumos">
    <i data-lucide="search"></i>
    <input type="search" id="searchInput" placeholder="Meklēt sludinājumus...">
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
      <a class="active" href="<?= base_url('advertisements') ?>"><i data-lucide="store"></i> Sludinājumi</a>
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
          <h1>Sludinājumi</h1>
          <p>Pārlūko tehniku, materiālus un vietējos saimniecību piedāvājumus.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdModal">Pievienot sludinājumu</button>
      </div>

      <div class="ag-grid" id="adsContainer">
        <div class="ag-card">Ielādē sludinājumus...</div>
      </div>
    </div>
  </main>
</div>

<div class="modal fade" id="addAdModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Pievienot sludinājumu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form id="addAdForm">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Virsraksts</label>
            <input type="text" name="title" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Apraksts</label>
            <textarea name="description" class="form-control" rows="4" required></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Cena (EUR)</label>
            <input type="number" step="0.01" name="price" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Atrašanās vieta</label>
            <input type="text" name="location" class="form-control" placeholder="Piemēram: Bauska">
          </div>

          <div class="mb-3">
            <label class="form-label">Attēli</label>
            <input type="file" id="adImages" class="form-control" multiple>
            <div id="imagePreview" class="d-flex flex-wrap gap-2 mt-2"></div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success w-100">Saglabāt sludinājumu</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editAdModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Rediģēt sludinājumu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form id="editAdForm">
        <input type="hidden" name="id" id="editAdId">
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Virsraksts</label>
            <input type="text" name="title" id="editAdTitle" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Apraksts</label>
            <textarea name="description" id="editAdDescription" class="form-control" rows="4" required></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Cena (EUR)</label>
            <input type="number" step="0.01" name="price" id="editAdPrice" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Atrašanās vieta</label>
            <input type="text" name="location" id="editAdLocation" class="form-control">
          </div>

          <div class="mb-3">
            <label class="form-label">Statuss</label>
            <select name="status" id="editAdStatus" class="form-select">
              <option value="active">Pārdodas</option>
              <option value="sold">Pārdots</option>
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

<div class="modal fade" id="adDetailModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailTitle">Sludinājuma informācija</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" id="detailBody"></div>
    </div>
  </div>
</div>

<script>
function getCsrfToken() {
  return $('meta[name="csrf-token"]').attr('content');
}

function updateCsrfToken(token) {
  if (token) {
    $('meta[name="csrf-token"]').attr('content', token);
  }
}

function ajaxMessage(xhr, fallback) {
  const response = xhr.responseJSON || {};

  if (response.message) {
    return response.message;
  }

  if (xhr.responseText && xhr.responseText.trim().startsWith('<')) {
    return 'Serveris atgrieza lapu, nevis JSON datus. Pārliecinies, ka esi pieslēdzies un maršruts ir pareizs.';
  }

  return fallback;
}

function statusLabel(status) {
  return status === 'sold' ? 'Pārdots' : 'Pārdodas';
}

function statusBadge(status) {
  if (status === 'sold') {
    return '<p class="text-danger fw-bold fs-4 mb-2 text-uppercase">Pārdots</p>';
  }

  return '<p class="ag-muted mb-2">Pārdodas</p>';
}

const adBaseUrl = '<?= rtrim(base_url(), '/') ?>';
const defaultAdImage = '<?= base_url('uploads/default.jpg') ?>';

function asText(value, fallback = '') {
  return value === null || value === undefined ? fallback : String(value);
}

function escapeHtml(value) {
  return asText(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function truncateText(value, maxLength = 120) {
  const text = asText(value);

  if (text.length <= maxLength) {
    return text;
  }

  return `${text.substring(0, maxLength)}...`;
}

function advertisementImageUrl(path) {
  const imagePath = asText(path).trim();

  if (!imagePath) {
    return defaultAdImage;
  }

  if (/^https?:\/\//i.test(imagePath)) {
    return imagePath;
  }

  return `${adBaseUrl}/${imagePath.replace(/^\/+/, '')}`;
}

function contactButton(ad) {
  if (!ad.can_contact) {
    return '';
  }

  return `<button class="btn btn-outline-primary btn-sm ms-2" onclick="startConversation(${ad.id})">
    Sazināties
  </button>`;
}

let selectedAdImages = [];
let allAdvertisements = [];
let lastAdvertisementModalTrigger = null;

function blurActiveElement() {
  const activeElement = document.activeElement;

  if (
    activeElement
    && activeElement !== document.body
    && typeof activeElement.blur === 'function'
  ) {
    activeElement.blur();
  }
}

function focusFirstModalControl(modal) {
  const focusable = modal.querySelector(
    'input:not([type="hidden"]), textarea, select, button:not(.btn-close), [tabindex]:not([tabindex="-1"])'
  );

  if (focusable) {
    focusable.focus();
  }
}

function clearStaleModalState() {
  if (document.querySelector('.modal.show')) {
    return;
  }

  document.querySelectorAll('.ag-shell, .ag-topbar').forEach(element => {
    element.removeAttribute('aria-hidden');
    element.removeAttribute('inert');
  });

  document.body.classList.remove('modal-open');
  document.body.style.removeProperty('overflow');
  document.body.style.removeProperty('padding-right');
  document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
}

function showAdvertisementsContainer() {
  const container = document.getElementById('adsContainer');

  if (!container) {
    return;
  }

  container.style.removeProperty('display');

  if (getComputedStyle(container).display === 'none') {
    container.style.setProperty('display', 'grid', 'important');
  }
}

['pointerdown', 'click', 'keydown'].forEach(eventName => {
  document.addEventListener(eventName, function(event) {
    const trigger = event.target.closest('[data-bs-toggle="modal"]');

    if (!trigger) {
      return;
    }

    lastAdvertisementModalTrigger = trigger;

    if (eventName !== 'keydown' || event.key === 'Enter' || event.key === ' ') {
      blurActiveElement();
    }
  }, true);
});

$('.modal').on('show.bs.modal', function() {
  if (!this.contains(document.activeElement)) {
    blurActiveElement();
  }
});

$('.modal').on('shown.bs.modal', function() {
  focusFirstModalControl(this);
});

$('.modal').on('hide.bs.modal', function() {
  if (this.contains(document.activeElement)) {
    blurActiveElement();
  }
});

$('.modal').on('hidden.bs.modal', function() {
  clearStaleModalState();

  if (lastAdvertisementModalTrigger && document.body.contains(lastAdvertisementModalTrigger)) {
    lastAdvertisementModalTrigger.focus();
  }
});

$('#adImages').on('change', function(event) {
  const files = event.target.files;

  Array.from(files).forEach(file => {
    selectedAdImages.push(file);

    const reader = new FileReader();
    reader.onload = function(e) {
      $('#imagePreview').append(`
        <div class="border p-1">
          <img src="${e.target.result}" style="width:100px;height:100px;object-fit:cover;">
        </div>
      `);
    };

    reader.readAsDataURL(file);
  });

  $(this).val('');
});

$('#addAdForm').on('submit', function(e) {
  e.preventDefault();

  const formData = new FormData(this);

  selectedAdImages.forEach(file => {
    formData.append('images[]', file);
  });

  formData.append('<?= csrf_token() ?>', getCsrfToken());

  $.ajax({
    url: '<?= base_url('advertisements/add') ?>',
    method: 'POST',
    dataType: 'json',
    data: formData,
    processData: false,
    contentType: false,
    success: function(response) {
      updateCsrfToken(response.csrfToken);

      if (response.success) {
        Swal.fire('Izdevās', response.message, 'success');
        $('#addAdModal').modal('hide');
        $('#addAdForm')[0].reset();
        $('#imagePreview').empty();
        selectedAdImages = [];
        fetchAdvertisements();
      } else {
        Swal.fire('Kļūda', response.message, 'error');
      }
    },
    error: function(xhr) {
      Swal.fire('Kļūda', ajaxMessage(xhr, 'Neizdevās pievienot sludinājumu.'), 'error');
    }
  });
});

$('#editAdForm').on('submit', function(e) {
  e.preventDefault();

  const formData = new FormData(this);
  formData.append('<?= csrf_token() ?>', getCsrfToken());

  $.ajax({
    url: '<?= base_url('advertisements/update') ?>',
    method: 'POST',
    dataType: 'json',
    data: formData,
    processData: false,
    contentType: false,
    success: function(response) {
      updateCsrfToken(response.csrfToken);

      if (response.success) {
        Swal.fire('Saglabāts', response.message, 'success');
        $('#editAdModal').modal('hide');
        fetchAdvertisements();
      } else {
        Swal.fire('Kļūda', response.message, 'error');
      }
    },
    error: function(xhr) {
      const response = xhr.responseJSON || {};
      Swal.fire('Kļūda', response.message || 'Neizdevās atjaunināt sludinājumu.', 'error');
    }
  });
});

function fetchAdvertisements() {
  $.ajax({
    url: '<?= base_url('advertisements/fetch') ?>',
    method: 'GET',
    dataType: 'json',
    success: function(response) {
      showAdvertisementsContainer();

      if (response.success) {
        allAdvertisements = Array.isArray(response.data) ? response.data : [];
        renderAdvertisements(allAdvertisements);
      } else {
        $('#adsContainer').html(`<div class="ag-card">${escapeHtml(response.message || 'Neizdevās ielādēt sludinājumus.')}</div>`);
      }
    },
    error: function(xhr) {
      showAdvertisementsContainer();
      $('#adsContainer').html(`<div class="ag-card">${escapeHtml(ajaxMessage(xhr, 'Kļūda, ielādējot sludinājumus.'))}</div>`);
    }
  });
}

function renderAdvertisements(ads) {
  clearStaleModalState();
  showAdvertisementsContainer();

  if (ads.length === 0) {
    $('#adsContainer').html('<div class="ag-card">Sludinājumu nav.</div>');
    return;
  }

  let html = '';

  ads.forEach(ad => {
    const title = asText(ad.title, 'Bez virsraksta');
    const description = asText(ad.description);
    const location = asText(ad.location);
    const sellerName = asText(ad.seller_name, 'Nav norādīts');
    const createdAt = asText(ad.created_at);
    let image = defaultAdImage;

    if (Array.isArray(ad.images) && ad.images.length > 0) {
      image = advertisementImageUrl(ad.images[0]);
    }

    const manageButtons = ad.can_manage
      ? `<div class="ag-post-admin mt-3">
          <button class="btn btn-outline-secondary btn-sm" onclick="editAdvertisement(${ad.id})">Rediģēt</button>
          <button class="btn btn-danger btn-sm" onclick="deleteAdvertisement(${ad.id})">Dzēst</button>
        </div>`
      : '';

    html += `
      <article class="ag-card ag-ad-card">
        <img src="${escapeHtml(image)}" alt="${escapeHtml(title)}" loading="lazy">
        <div class="ag-ad-body">
          <h3 class="ag-post-title">${escapeHtml(title)}</h3>
          ${statusBadge(ad.status)}
          <p>${escapeHtml(truncateText(description))}</p>
          <p class="ag-price">${escapeHtml(ad.price)} EUR</p>
          <p class="ag-muted">Pārdevējs: ${escapeHtml(sellerName)}</p>
          <p class="ag-muted">${escapeHtml(location)}${location && createdAt ? ' • ' : ''}${escapeHtml(createdAt)}</p>
          <button class="btn btn-primary btn-sm" onclick="viewAdvertisement(${ad.id})">Skatīt informāciju</button>
          ${contactButton(ad)}
          ${manageButtons}
        </div>
      </article>
    `;
  });

  $('#adsContainer').html(html);
  if (window.lucide) {
    lucide.createIcons();
  }
}

function editAdvertisement(id) {
  blurActiveElement();

  $.ajax({
    url: `<?= base_url('advertisements/detail') ?>/${id}`,
    method: 'GET',
    success: function(response) {
      if (response.success) {
        const ad = response.data;
        $('#editAdId').val(ad.id);
        $('#editAdTitle').val(ad.title);
        $('#editAdDescription').val(ad.description);
        $('#editAdPrice').val(ad.price);
        $('#editAdLocation').val(ad.location ?? '');
        $('#editAdStatus').val(ad.status === 'sold' ? 'sold' : 'active');
        blurActiveElement();
        $('#editAdModal').modal('show');
      }
    },
    error: function(xhr) {
      const response = xhr.responseJSON || {};
      Swal.fire('Kļūda', response.message || 'Neizdevās ielādēt sludinājumu.', 'error');
    }
  });
}

function deleteAdvertisement(id) {
  Swal.fire({
    title: 'Dzēst sludinājumu?',
    text: 'Šo darbību nevarēs atsaukt.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Jā, dzēst',
    cancelButtonText: 'Atcelt'
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: `<?= base_url('advertisements/delete') ?>/${id}`,
        method: 'DELETE',
        data: {
          '<?= csrf_token() ?>': getCsrfToken()
        },
        success: function(response) {
          updateCsrfToken(response.csrfToken);

          if (response.success) {
            Swal.fire('Dzēsts', response.message, 'success');
            fetchAdvertisements();
          } else {
            Swal.fire('Kļūda', response.message, 'error');
          }
        },
        error: function(xhr) {
          const response = xhr.responseJSON || {};
          Swal.fire('Kļūda', response.message || 'Neizdevās dzēst sludinājumu.', 'error');
        }
      });
    }
  });
}

function viewAdvertisement(id) {
  blurActiveElement();

  $.ajax({
    url: `<?= base_url('advertisements/detail') ?>/${id}`,
    method: 'GET',
    success: function(response) {
      if (response.success) {
        const ad = response.data;

        let imagesHtml = '';

        if (Array.isArray(ad.images) && ad.images.length > 0) {
          ad.images.forEach(img => {
            imagesHtml += `
              <img src="${escapeHtml(advertisementImageUrl(img))}"
                   class="img-fluid mb-2 me-2"
                   style="max-width:180px;max-height:180px;object-fit:cover;">
            `;
          });
        } else {
          imagesHtml = `
            <img src="${escapeHtml(defaultAdImage)}"
                 class="img-fluid mb-2"
                 style="max-width:180px;">
          `;
        }

        $('#detailTitle').text(asText(ad.title, 'Sludinājuma informācija'));

        $('#detailBody').html(`
          <div class="mb-3">${imagesHtml}</div>
          <h4>${escapeHtml(ad.title)}</h4>
          ${statusBadge(ad.status)}
          <p>${escapeHtml(ad.description)}</p>
          <p><strong>Cena:</strong> ${escapeHtml(ad.price)} EUR</p>
          <p><strong>Atrašanās vieta:</strong> ${escapeHtml(asText(ad.location, 'Nav norādīta'))}</p>
          <p><strong>Pārdevējs:</strong> ${escapeHtml(asText(ad.seller_name, 'Nav norādīts'))}</p>
          <p><strong>Izveidots:</strong> ${escapeHtml(ad.created_at)}</p>
          ${contactButton(ad)}
        `);

        blurActiveElement();
        $('#adDetailModal').modal('show');
      }
    },
    error: function() {
      Swal.fire('Kļūda', 'Neizdevās ielādēt sludinājuma informāciju.', 'error');
    }
  });
}

function startConversation(adId) {
  $.ajax({
    url: `<?= base_url('messages/start') ?>/${adId}`,
    method: 'POST',
    data: {
      '<?= csrf_token() ?>': getCsrfToken()
    },
    success: function(response) {
      updateCsrfToken(response.csrfToken);

      if (response.success) {
        window.location.href = `<?= base_url('messages') ?>?conversation=${response.conversation_id}`;
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

$('#searchInput').on('keyup', function() {
  const keyword = $(this).val().toLowerCase();

  const filtered = allAdvertisements.filter(ad => {
    return (
      asText(ad.title).toLowerCase().includes(keyword) ||
      asText(ad.description).toLowerCase().includes(keyword) ||
      statusLabel(ad.status).toLowerCase().includes(keyword) ||
      String(ad.price).includes(keyword) ||
      asText(ad.location).toLowerCase().includes(keyword)
    );
  });

  renderAdvertisements(filtered);
});

$(document).ready(function() {
  if (window.lucide) {
    lucide.createIcons();
  }
  fetchAdvertisements();
});
</script>

</body>
</html>
