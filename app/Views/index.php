<!DOCTYPE html>
<html lang="lv">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <title>Ziņu plūsma | AgriConnect</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= base_url('css/agriconnect.css') ?>" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.13.6/underscore-min.js"></script>
  <script src="<?= base_url('js/jsonform.js') ?>"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
</head>

<body class="ag-app">

<div class="modal fade" id="edit_post_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Rediģēt ierakstu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="edit_post_form_container"></form>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="commentsModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Komentāri</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="comment_post_id">

        <div id="commentsList" class="mb-3">
          Ielādē komentārus...
        </div>

        <textarea id="commentText" class="form-control mb-2" rows="3" placeholder="Uzraksti komentāru..."></textarea>

        <button class="btn btn-primary w-100" onclick="addComment()">
          Pievienot komentāru
        </button>
      </div>

    </div>
  </div>
</div>

<header class="ag-topbar">
  <a class="ag-brand" href="<?= base_url('posts') ?>">AgriConnect</a>
  <label class="ag-search" aria-label="Meklēt ziņu plūsmā">
    <i data-lucide="search"></i>
    <input type="search" placeholder="Meklēt...">
  </label>
  <div class="ag-top-actions">
    <a class="ag-icon-btn" href="<?= base_url('posts') ?>" title="Paziņojumi"><i data-lucide="bell"></i></a>
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
      <a class="active" href="<?= base_url('posts') ?>"><i data-lucide="rss"></i> Ziņu plūsma</a>
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
    <div class="ag-feed-layout">
      <section>
        <div id="show_posts">
          <div class="ag-card">Ieraksti tiks ielādēti šeit</div>
        </div>
      </section>

      <aside class="ag-side-stack">
        <div class="ag-card">
          <h2 class="ag-section-title">Forumā</h2>
          <div id="forumSidebar">
            <div class="ag-muted">Ielādē diskusijas...</div>
          </div>
          <a class="btn btn-outline-secondary w-100 mt-3" href="<?= base_url('forum') ?>">Rādīt vairāk</a>
        </div>
      </aside>
    </div>
  </main>
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

function escapeHtml(value) {
  return $('<div>').text(value ?? '').html();
}

let allSelectedFilesEdit = [];

function editPost(postId) {
  $.ajax({
    url: `<?= base_url('post/edit') ?>/${postId}`,
    method: 'get',
    success: function (response) {
      if (!response.error) {
        const post = response.message.post;
        const categories = response.message.categories;
        let existingImages = Array.isArray(post.images) ? post.images : [];
        let imagesToDelete = [];

        allSelectedFilesEdit = [];
        $('#edit_post_form_container').empty();

        const existingImagesContainer = $(`
          <div class="mb-4">
            <label class="form-label fw-bold">Esošie attēli</label>
            <div id="existing_images_container" class="d-flex flex-wrap gap-2"></div>
          </div>
        `);

        $('#edit_post_form_container').append(existingImagesContainer);

        existingImages.forEach((image) => {
          const imageElement = $(`
            <div class="d-inline-block text-center" style="max-width: 120px;">
              <img src="<?= base_url() ?>/${image}" class="img-thumbnail" style="max-width: 100px; max-height: 100px;" alt="Image">
              <button type="button" class="btn btn-danger btn-sm mt-1 delete-image-btn" data-image="${image}">Dzēst</button>
            </div>
          `);

          $('#existing_images_container').append(imageElement);
        });

        $('#edit_post_form_container').off('click', '.delete-image-btn').on('click', '.delete-image-btn', function () {
          const image = $(this).data('image');
          imagesToDelete.push(image);
          existingImages = existingImages.filter(existingImage => existingImage !== image);
          $(this).parent().remove();
        });

        const newImagesContainer = $(`
          <div class="mb-4">
            <label class="form-label fw-bold">Jauni attēli</label>
            <div id="new_images_container" class="d-flex flex-wrap gap-2"></div>
            <input type="file" class="form-control mt-3" id="new_images_input" multiple>
          </div>
        `);

        $('#edit_post_form_container').append(newImagesContainer);

        $('#edit_post_form_container').off('change', '#new_images_input').on('change', '#new_images_input', function () {
          const files = this.files;

          Array.from(files).forEach((file) => {
            allSelectedFilesEdit.push(file);

            const reader = new FileReader();

            reader.onload = function (e) {
              const previewElement = $(`
                <div class="d-inline-block text-center" style="max-width: 120px;">
                  <img src="${e.target.result}" class="img-thumbnail" style="max-width: 100px; max-height: 100px;" alt="Jauns attēls">
                  <button type="button" class="btn btn-danger btn-sm mt-1 delete-edit-new-image-btn" data-index="${allSelectedFilesEdit.length - 1}">Dzēst</button>
                </div>
              `);

              $('#new_images_container').append(previewElement);
            };

            reader.readAsDataURL(file);
          });

          $(this).val('');
        });

        $('#edit_post_form_container').off('click', '.delete-edit-new-image-btn').on('click', '.delete-edit-new-image-btn', function () {
          const imageIndex = $(this).data('index');
          allSelectedFilesEdit.splice(imageIndex, 1);
          $(this).parent().remove();

          $('#new_images_container .delete-edit-new-image-btn').each(function (index) {
            $(this).data('index', index);
          });
        });

        $('#edit_post_form_container').jsonForm({
          schema: {
            title: { type: 'string', title: 'Ieraksta virsraksts', required: true },
            category: {
              type: 'string',
              title: 'Ieraksta kategorija',
              enum: categories.map(category => category.id),
              enumNames: categories.map(category => category.category_name),
              required: true
            },
            body: { type: 'string', title: 'Ieraksta teksts', required: true }
          },
          form: [
            { key: 'title', value: post.title },
            { key: 'category', value: post.category_id },
            { key: 'body', type: 'textarea', value: post.body }
          ],
          onSubmit: function (errors, values) {
            if (errors) {
              console.log('Validation errors:', errors);
              return;
            }

            const formData = new FormData();

            formData.append('id', post.id);
            formData.append('title', values.title);
            formData.append('category', values.category);
            formData.append('body', values.body);
            formData.append('existing_images', JSON.stringify(existingImages));
            formData.append('images_to_delete', JSON.stringify(imagesToDelete));

            allSelectedFilesEdit.forEach(file => {
              formData.append('new_images[]', file);
            });

            formData.append('<?= csrf_token() ?>', getCsrfToken());

            $.ajax({
              url: `<?= base_url('post/update') ?>`,
              method: 'post',
              processData: false,
              contentType: false,
              data: formData,
              success: function (response) {
                updateCsrfToken(response.csrfToken);

                if (response.success) {
                  Swal.fire('Atjaunināts', response.message, 'success');
                  $('#edit_post_modal').modal('hide');
                  fetchAllPosts();
                } else {
                  Swal.fire('Kļūda', response.message, 'error');
                }
              },
              error: function () {
                Swal.fire('Kļūda', 'Neizdevās atjaunināt ierakstu.', 'error');
              }
            });
          }
        });

        $('#edit_post_form_container').append(`
          <div class="mt-4">
          <button type="submit" class="btn btn-primary w-100">Atjaunināt ierakstu</button>
          </div>
        `);

        const $select = $('#edit_post_form_container select[name="category"]');

        $select.find('option').each(function () {
          const val = $(this).val();
          const cat = categories.find(c => c.id == val);

          if (cat) {
            $(this).text(cat.category_name);
          }
        });

        $('#edit_post_modal').modal('show');
      } else {
        Swal.fire('Kļūda', response.message || 'Neizdevās ielādēt ieraksta informāciju.', 'error');
      }
    },
    error: function (xhr) {
      const response = xhr.responseJSON || {};
      Swal.fire('Kļūda', response.message || 'Neizdevās ielādēt ieraksta informāciju.', 'error');
    }
  });
}

function deletePost(postId) {
  Swal.fire({
    title: 'Vai tiešām?',
    text: "Šo darbību nevarēs atsaukt!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Jā, dzēst!'
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: `<?= base_url('post/delete') ?>/${postId}`,
        method: 'delete',
        data: {
          '<?= csrf_token() ?>': getCsrfToken()
        },
        success: function (response) {
          updateCsrfToken(response.csrfToken);

          if (response.success) {
            Swal.fire('Dzēsts!', response.message, 'success');
            fetchAllPosts();
          } else {
            Swal.fire('Kļūda', response.message, 'error');
          }
        },
        error: function () {
          Swal.fire('Kļūda', 'Neizdevās dzēst ierakstu.', 'error');
        }
      });
    }
  });
}

function likePost(postId) {
  const formData = new FormData();

  formData.append('post_id', postId);
  formData.append('<?= csrf_token() ?>', getCsrfToken());

  $.ajax({
    url: '<?= base_url('post/like') ?>',
    method: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success: function(response) {
      updateCsrfToken(response.csrfToken);

      if (response.success) {
        $(`#likes-${postId}`).text(response.likes);
      } else {
        Swal.fire('Kļūda', response.message, 'error');
      }
    },
    error: function() {
      Swal.fire('Kļūda', 'Neizdevās atzīmēt ierakstu.', 'error');
    }
  });
}

function openComments(postId) {
  $('#comment_post_id').val(postId);
  $('#commentText').val('');
  $('#commentsList').html('Ielādē komentārus...');

  $.ajax({
    url: `<?= base_url('post/comments') ?>/${postId}`,
    method: 'GET',
    success: function(response) {
      if (response.success) {
        renderComments(response.data, response.current_user_id, response.current_is_admin);
        $('#commentsModal').modal('show');
      }
    },
    error: function() {
      Swal.fire('Kļūda', 'Neizdevās ielādēt komentārus.', 'error');
    }
  });
}

function renderComments(comments, currentUserId, currentIsAdmin) {
  if (comments.length === 0) {
    $('#commentsList').html('<p>Komentāru vēl nav.</p>');
    return;
  }

  let html = '';

  comments.forEach(comment => {
    const canDelete = currentIsAdmin || String(comment.user_id) === String(currentUserId);

    html += `
      <div class="border rounded p-2 mb-2">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <strong>${comment.user_name ?? 'Nezināms lietotājs'}</strong>
            <p class="mb-1">${comment.comment}</p>
            <small class="text-muted">${comment.created_at}</small>
          </div>

          ${
            canDelete
              ? `<button class="btn btn-danger btn-sm" onclick="deleteComment(${comment.id})">Dzēst</button>`
              : ''
          }
        </div>
      </div>
    `;
  });

  $('#commentsList').html(html);
}

function addComment() {
  const postId = $('#comment_post_id').val();
  const comment = $('#commentText').val();

  if (!comment.trim()) {
    Swal.fire('Kļūda', 'Komentārs nevar būt tukšs.', 'error');
    return;
  }

  const formData = new FormData();

  formData.append('post_id', postId);
  formData.append('comment', comment);
  formData.append('<?= csrf_token() ?>', getCsrfToken());

  $.ajax({
    url: '<?= base_url('post/comment/add') ?>',
    method: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    success: function(response) {
      updateCsrfToken(response.csrfToken);

      if (response.success) {
        $('#commentText').val('');
        openComments(postId);
        fetchAllPosts();
      } else {
        Swal.fire('Kļūda', response.message, 'error');
      }
    },
    error: function() {
      Swal.fire('Kļūda', 'Neizdevās pievienot komentāru.', 'error');
    }
  });
}


function deleteComment(commentId) {
  const postId = $('#comment_post_id').val();

  Swal.fire({
    title: 'Dzēst komentāru?',
    text: 'Šo darbību nevarēs atsaukt.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Jā, dzēst'
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: `<?= base_url('post/comment/delete') ?>/${commentId}`,
        method: 'DELETE',
        data: {
          '<?= csrf_token() ?>': getCsrfToken()
        },
        success: function(response) {
          updateCsrfToken(response.csrfToken);

          if (response.success) {
            openComments(postId);
            fetchAllPosts();
          } else {
            Swal.fire('Kļūda', response.message, 'error');
          }
        },
        error: function(xhr) {
          Swal.fire('Kļūda', 'Neizdevās dzēst komentāru.', 'error');
        }
      });
    }
  });
}

async function fetchAllPosts() {
  try {
    const response = await $.ajax({
      url: '<?= base_url('post/fetch') ?>',
      method: 'GET'
    });

    if (response.success) {
      if (response.data.length === 0) {
        $('#show_posts').html('<div class="ag-card">Ierakstu nav.</div>');
        return;
      }

      let postsHtml = '';

      response.data.forEach(post => {
        let imagesHtml = '';
        let images = [];

        if (Array.isArray(post.images)) {
          images = post.images;
        } else if (typeof post.images === 'string' && post.images.trim() !== '') {
          try {
            images = JSON.parse(post.images);
          } catch (e) {
            console.error('Error parsing images JSON:', e);
          }
        }

        if (images.length > 0) {
          images.forEach(image => {
            const imagePath = image.startsWith('/') ? image.substring(1) : image;

            imagesHtml += `<img src="<?= base_url() ?>/${imagePath}" alt="${post.title}" loading="lazy">`;
          });
        } else {
          imagesHtml = `<img src="<?= base_url('uploads/default.jpg') ?>" alt="Noklusējuma attēls" loading="lazy">`;
        }

        const authorName = post.author_name || 'Nezināms lietotājs';
        const authorInitials = authorName.substring(0, 2).toUpperCase();
        const authorAvatar = post.author_image
          ? `<img src="<?= base_url() ?>/${escapeHtml(post.author_image)}" alt="${escapeHtml(authorName)}">`
          : escapeHtml(authorInitials);

        postsHtml += `
          <article class="ag-card ag-post">
            <div class="ag-post-head">
              <span class="ag-avatar">${authorAvatar}</span>
              <div class="ag-post-meta">
                <h3>${escapeHtml(authorName)}</h3>
                <p>${escapeHtml(post.category ?? 'Nav kategorijas')} • ${escapeHtml(post.created_at)}</p>
              </div>
              <button class="ag-icon-btn" type="button" title="Vairāk"><i data-lucide="ellipsis"></i></button>
            </div>

            <h3 class="ag-post-title">${escapeHtml(post.title)}</h3>
            <p class="ag-post-body">${escapeHtml(post.body)}</p>

            <div class="ag-post-images">
              ${imagesHtml}
            </div>

            <div class="ag-post-actions">
              <button type="button" onclick="likePost(${post.id})">
                <i data-lucide="thumbs-up"></i>
                <span id="likes-${post.id}">${post.likes_count ?? 0}</span>
              </button>

              <button type="button" onclick="openComments(${post.id})">
                <i data-lucide="message-square"></i>
                <span>${post.comments_count ?? 0} komentāri</span>
              </button>

              <button type="button">
                <i data-lucide="share-2"></i>
                <span>Dalīties</span>
              </button>

              ${
                post.can_manage
                  ? `<div class="ag-post-admin">
                      <button class="btn btn-outline-secondary btn-sm" onclick="editPost(${post.id})">Rediģēt</button>
                      <button class="btn btn-danger btn-sm" onclick="deletePost(${post.id})">Dzēst</button>
                    </div>`
                  : ''
              }
            </div>
          </article>
        `;
      });

      $('#show_posts').html(postsHtml);
      if (window.lucide) {
        lucide.createIcons();
      }
    } else {
      Swal.fire('Kļūda', response.message, 'error');
    }
  } catch (error) {
    Swal.fire('Kļūda', 'Neizdevās ielādēt ierakstus.', 'error');
  }
}

function fetchForumSidebar() {
  $.ajax({
    url: '<?= base_url('forum/fetch') ?>',
    method: 'GET',
    success: function(response) {
      if (!response.success || response.data.length === 0) {
        $('#forumSidebar').html('<div class="ag-muted">Diskusiju vēl nav.</div>');
        return;
      }

      let html = '';
      response.data.slice(0, 4).forEach(discussion => {
        html += `
          <div class="ag-trend">
            <small>${discussion.category ?? 'Nav kategorijas'} • ${discussion.created_at}</small>
            <h3>${discussion.title}</h3>
          </div>
        `;
      });

      $('#forumSidebar').html(html);
    },
    error: function() {
      $('#forumSidebar').html('<div class="ag-muted">Neizdevās ielādēt diskusijas.</div>');
    }
  });
}

$(document).ready(function () {
  if (window.lucide) {
    lucide.createIcons();
  }

  let allSelectedFiles = [];

  $('#add_images_input').on('change', function (event) {
    const files = event.target.files;
    const previewContainer = $('#add_image_previews');

    Array.from(files).forEach((file) => {
      allSelectedFiles.push(file);

      const reader = new FileReader();

      reader.onload = function (e) {
        const previewElement = `
          <div class="d-inline-block text-center me-2 mb-2" style="max-width: 120px;">
            <img src="${e.target.result}" class="img-thumbnail" style="max-width: 100px; max-height: 100px;" alt="Jauns attēls">
            <button type="button" class="btn btn-danger btn-sm mt-1 delete-new-image-btn" data-index="${allSelectedFiles.length - 1}">Dzēst</button>
          </div>
        `;

        previewContainer.append(previewElement);
      };

      reader.readAsDataURL(file);
    });

    $(this).val('');
  });

  $('#add_image_previews').on('click', '.delete-new-image-btn', function () {
    const imageIndex = $(this).data('index');

    allSelectedFiles.splice(imageIndex, 1);
    $(this).parent().remove();

    $('#add_image_previews .delete-new-image-btn').each(function (index) {
      $(this).data('index', index);
    });
  });

  $('#add_post_form_container').jsonForm({
    schema: {
      title: { type: 'string', title: 'Ieraksta virsraksts', required: true },
      category: {
        type: 'string',
        title: 'Ieraksta kategorija',
        enum: [],
        required: true
      },
      body: { type: 'string', title: 'Ieraksta teksts', required: true }
    },
    form: [
      { key: 'title', placeholder: 'Ievadi virsrakstu' },
      { key: 'category', type: 'select', placeholder: 'Izvēlies kategoriju' },
      { key: 'body', type: 'textarea', placeholder: 'Ievadi tekstu' }
    ],
    onSubmit: function (errors, values) {
      if (errors) {
        console.log('Validation errors:', errors);
        return;
      }

      const formData = new FormData();

      formData.append('title', values.title);
      formData.append('category', values.category);
      formData.append('body', values.body);

      allSelectedFiles.forEach(file => {
        formData.append('images[]', file);
      });

      formData.append('<?= csrf_token() ?>', getCsrfToken());

      $.ajax({
        url: '<?= base_url('post/add') ?>',
        method: 'post',
        processData: false,
        contentType: false,
        data: formData,
        success: function (response) {
          updateCsrfToken(response.csrfToken);

          if (response.success) {
            Swal.fire('Pievienots', response.message, 'success');
            fetchAllPosts();
            $('#add_post_modal').modal('hide');
            clearForm('add_post_form_container', 'add_image_previews', allSelectedFiles);
          } else {
            Swal.fire('Kļūda', response.message, 'error');
          }
        },
        error: function () {
          Swal.fire('Kļūda', 'Neizdevās pievienot ierakstu.', 'error');
        }
      });
    }
  });

  function fetchCategories() {
    $.ajax({
      url: '<?= base_url('categories/fetch') ?>',
      method: 'get',
      success: function (response) {
        if (response.success) {
          let categoryOptions = '<option value="">Izvēlies kategoriju</option>';

          response.data.forEach(category => {
            categoryOptions += `<option value="${category.id}">${category.category_name}</option>`;
          });

          $('#add_post_form_container select[name="category"]').html(categoryOptions);
        } else {
          console.error('Failed to fetch categories:', response);
        }
      },
      error: function (xhr, status, error) {
        console.error('Error fetching categories:', error);
      }
    });
  }

  fetchCategories();
  fetchAllPosts();
  fetchForumSidebar();
});

function clearForm(formId, previewContainerId, fileArray) {
  $(`#${formId} input[type="text"], #${formId} textarea`).val('');
  $(`#${formId} select`).val('');
  $(`#${formId} input[type="file"]`).val('');

  if (fileArray) {
    fileArray.length = 0;
  }

  if (previewContainerId) {
    $(`#${previewContainerId}`).empty();
  }
}

$('#add_post_modal').on('show.bs.modal', function () {
  $(this).removeAttr('inert');
});

$('#add_post_modal').on('hide.bs.modal', function () {
  $(this).attr('inert', '');
});

$('#edit_post_modal').on('show.bs.modal', function () {
  $(this).removeAttr('inert');
});

$('#edit_post_modal').on('hide.bs.modal', function () {
  $(this).attr('inert', '');
});
</script>

</body>
</html>
