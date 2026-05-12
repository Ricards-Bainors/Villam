<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= csrf_hash() ?>">

  <title>Advertisements</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
<div class="container my-4">

  <div class="card shadow">
    <a href="<?= base_url('/') ?>" class="btn btn-secondary">Back to Posts</a>
    <div class="card-header d-flex justify-content-between align-items-center">
      <h3 class="m-0">Marketplace / Advertisements</h3>
      
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdModal">Add Advertisement</button>
    </div>

    <div class="card-body">
      <div class="row mb-3">
        <div class="col-md-6">
          <input type="text" id="searchInput" class="form-control" placeholder="Search advertisements...">
        </div>
      </div>

      <div class="row" id="adsContainer">
        <p>Loading advertisements...</p>
      </div>
    </div>
  </div>

</div>

<!-- Add Advertisement Modal -->
<div class="modal fade" id="addAdModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Add Advertisement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <form id="addAdForm">
        <div class="modal-body">

          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4" required></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label">Price (€)</label>
            <input type="number" step="0.01" name="price" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Location</label>
            <input type="text" name="location" class="form-control" placeholder="Example: Bauska">
          </div>

          <div class="mb-3">
            <label class="form-label">Images</label>
            <input type="file" id="adImages" class="form-control" multiple>
            <div id="imagePreview" class="d-flex flex-wrap gap-2 mt-2"></div>
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success w-100">Save Advertisement</button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="adDetailModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title" id="detailTitle">Advertisement Details</h5>
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

let selectedAdImages = [];
let allAdvertisements = [];

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
    data: formData,
    processData: false,
    contentType: false,
    success: function(response) {
      updateCsrfToken(response.csrfToken);

      if (response.success) {
        Swal.fire('Success', response.message, 'success');

        $('#addAdModal').modal('hide');
        $('#addAdForm')[0].reset();
        $('#imagePreview').empty();
        selectedAdImages = [];

        fetchAdvertisements();
      } else {
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function() {
      Swal.fire('Error', 'Failed to add advertisement.', 'error');
    }
  });
});

function fetchAdvertisements() {
  $.ajax({
    url: '<?= base_url('advertisements/fetch') ?>',
    method: 'GET',
    success: function(response) {
      if (response.success) {
        allAdvertisements = response.data;
        renderAdvertisements(allAdvertisements);
      } else {
        $('#adsContainer').html('<p>Failed to load advertisements.</p>');
      }
    },
    error: function() {
      $('#adsContainer').html('<p>Error loading advertisements.</p>');
    }
  });
}

function renderAdvertisements(ads) {
  if (ads.length === 0) {
    $('#adsContainer').html('<p>No advertisements available.</p>');
    return;
  }

  let html = '';

  ads.forEach(ad => {
    let image = '<?= base_url('uploads/default.jpg') ?>';

    if (Array.isArray(ad.images) && ad.images.length > 0) {
      image = '<?= base_url() ?>/' + ad.images[0];
    }

    html += `
      <div class="col-md-4 mb-3">
        <div class="card h-100 shadow-sm">
          <img src="${image}" class="card-img-top" style="height:200px;object-fit:cover;">

          <div class="card-body">
            <h5 class="card-title">${ad.title}</h5>
            <p class="card-text">${ad.description.substring(0, 100)}...</p>
            <p class="fw-bold text-success">${ad.price} €</p>
            <p class="text-muted">${ad.location ?? ''}</p>

            <button class="btn btn-primary btn-sm" onclick="viewAdvertisement(${ad.id})">
              View Details
            </button>
          </div>

          <div class="card-footer text-muted">
            ${ad.created_at}
          </div>
        </div>
      </div>
    `;
  });

  $('#adsContainer').html(html);
}

function viewAdvertisement(id) {
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
              <img src="<?= base_url() ?>/${img}" 
                   class="img-fluid mb-2 me-2" 
                   style="max-width:180px;max-height:180px;object-fit:cover;">
            `;
          });
        } else {
          imagesHtml = `
            <img src="<?= base_url('uploads/default.jpg') ?>" 
                 class="img-fluid mb-2" 
                 style="max-width:180px;">
          `;
        }

        $('#detailTitle').text(ad.title);

        $('#detailBody').html(`
          <div class="mb-3">${imagesHtml}</div>
          <h4>${ad.title}</h4>
          <p>${ad.description}</p>
          <p><strong>Price:</strong> ${ad.price} €</p>
          <p><strong>Location:</strong> ${ad.location ?? 'Not specified'}</p>
          <p><strong>Created:</strong> ${ad.created_at}</p>
        `);

        $('#adDetailModal').modal('show');
      }
    },
    error: function() {
      Swal.fire('Error', 'Failed to load advertisement details.', 'error');
    }
  });
}

$('#searchInput').on('keyup', function() {
  const keyword = $(this).val().toLowerCase();

  const filtered = allAdvertisements.filter(ad => {
    return (
      ad.title.toLowerCase().includes(keyword) ||
      ad.description.toLowerCase().includes(keyword) ||
      String(ad.price).includes(keyword) ||
      (ad.location && ad.location.toLowerCase().includes(keyword))
    );
  });

  renderAdvertisements(filtered);
});

$(document).ready(function() {
  fetchAdvertisements();
});
</script>

</body>
</html>