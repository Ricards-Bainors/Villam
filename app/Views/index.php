<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <title>CRUD App Using CI 4 and Ajax</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.13.6/underscore-min.js"></script>
  <script src="<?= base_url('js/jsonform.js') ?>"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>

<!-- Add New Post Modal -->
<div class="modal fade" id="add_post_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add New Post</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="add_post_form_container">
          <div class="mb-3">
            <label for="add_images_input" class="form-label">Upload Images</label>
            <input type="file" class="form-control" id="add_images_input" multiple>
            <div id="add_image_previews" class="d-flex flex-wrap gap-2 mt-2"></div>
          </div>

          <button type="submit" class="btn btn-primary w-100 mt-3">Add Post</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Edit Post Modal -->
<div class="modal fade" id="edit_post_modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Post</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="edit_post_form_container"></form>
      </div>
    </div>
  </div>
</div>

<!-- Comments Modal -->
<div class="modal fade" id="commentsModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Comments</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <input type="hidden" id="comment_post_id">

        <div id="commentsList" class="mb-3">
          Loading comments...
        </div>

        <textarea id="commentText" class="form-control mb-2" rows="3" placeholder="Write comment..."></textarea>

        <button class="btn btn-primary w-100" onclick="addComment()">
          Add Comment
        </button>
      </div>

    </div>
  </div>
</div>

<!-- Main Posts Page -->
<div class="container">
  <div class="row my-4">
    <div class="col-lg-12">
      <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
          <div class="text-secondary fw-bold fs-3">All Posts</div>

          <div>
            <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#add_post_modal">Add New Post</button>
            <a href="<?= base_url('categories') ?>" class="btn btn-primary">Manage Categories</a>
            <a href="<?= base_url('advertisements') ?>" class="btn btn-warning">Marketplace</a>
            <a href="<?= base_url('forum') ?>" class="btn btn-info">Forum</a>
            <a href="<?= base_url('profile') ?>" class="btn btn-secondary">Profile</a>
            <a href="<?= base_url('auth/logout') ?>" class="btn btn-success">Logout</a>
          </div>
        </div>

        <div class="card-body">
          <div class="row" id="show_posts">
            Posts will be dynamically loaded here
          </div>
        </div>
      </div>
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

let allSelectedFilesEdit = [];

// ================= EDIT POST =================
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
            <label class="form-label fw-bold">Existing Images</label>
            <div id="existing_images_container" class="d-flex flex-wrap gap-2"></div>
          </div>
        `);

        $('#edit_post_form_container').append(existingImagesContainer);

        existingImages.forEach((image) => {
          const imageElement = $(`
            <div class="d-inline-block text-center" style="max-width: 120px;">
              <img src="<?= base_url() ?>/${image}" class="img-thumbnail" style="max-width: 100px; max-height: 100px;" alt="Image">
              <button type="button" class="btn btn-danger btn-sm mt-1 delete-image-btn" data-image="${image}">Delete</button>
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
            <label class="form-label fw-bold">New Images</label>
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
                  <img src="${e.target.result}" class="img-thumbnail" style="max-width: 100px; max-height: 100px;" alt="New Image">
                  <button type="button" class="btn btn-danger btn-sm mt-1 delete-edit-new-image-btn" data-index="${allSelectedFilesEdit.length - 1}">Delete</button>
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
            title: { type: 'string', title: 'Post Title', required: true },
            category: {
              type: 'string',
              title: 'Post Category',
              enum: categories.map(category => category.id),
              enumNames: categories.map(category => category.category_name),
              required: true
            },
            body: { type: 'string', title: 'Post Body', required: true }
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
                  Swal.fire('Updated', response.message, 'success');
                  $('#edit_post_modal').modal('hide');
                  fetchAllPosts();
                } else {
                  Swal.fire('Error', response.message, 'error');
                }
              },
              error: function () {
                Swal.fire('Error', 'Failed to update post.', 'error');
              }
            });
          }
        });

        $('#edit_post_form_container').append(`
          <div class="mt-4">
            <button type="submit" class="btn btn-primary w-100">Update Post</button>
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
        Swal.fire('Error', 'Failed to fetch post details.', 'error');
      }
    },
    error: function () {
      Swal.fire('Error', 'Failed to fetch post details.', 'error');
    }
  });
}

// ================= DELETE POST =================
function deletePost(postId) {
  Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!'
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
            Swal.fire('Deleted!', response.message, 'success');
            fetchAllPosts();
          } else {
            Swal.fire('Error', response.message, 'error');
          }
        },
        error: function () {
          Swal.fire('Error', 'Failed to delete post.', 'error');
        }
      });
    }
  });
}

// ================= LIKE POST =================
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
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function() {
      Swal.fire('Error', 'Failed to like post.', 'error');
    }
  });
}

// ================= COMMENTS =================
function openComments(postId) {
  $('#comment_post_id').val(postId);
  $('#commentText').val('');
  $('#commentsList').html('Loading comments...');

  $.ajax({
    url: `<?= base_url('post/comments') ?>/${postId}`,
    method: 'GET',
    success: function(response) {
      if (response.success) {
        renderComments(response.data, response.current_user_id);
        $('#commentsModal').modal('show');
      }
    },
    error: function() {
      Swal.fire('Error', 'Failed to load comments.', 'error');
    }
  });
}

function renderComments(comments, currentUserId) {
  if (comments.length === 0) {
    $('#commentsList').html('<p>No comments yet.</p>');
    return;
  }

  let html = '';

  comments.forEach(comment => {
    const canDelete = String(comment.user_id) === String(currentUserId);

    html += `
      <div class="border rounded p-2 mb-2">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <strong>${comment.user_name ?? 'Unknown user'}</strong>
            <p class="mb-1">${comment.comment}</p>
            <small class="text-muted">${comment.created_at}</small>
          </div>

          ${
            canDelete
              ? `<button class="btn btn-danger btn-sm" onclick="deleteComment(${comment.id})">Delete</button>`
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
    Swal.fire('Error', 'Comment cannot be empty.', 'error');
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
        Swal.fire('Error', response.message, 'error');
      }
    },
    error: function() {
      Swal.fire('Error', 'Failed to add comment.', 'error');
    }
  });
}


function deleteComment(commentId) {
  const postId = $('#comment_post_id').val();

  Swal.fire({
    title: 'Delete comment?',
    text: 'This cannot be undone.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it'
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
            Swal.fire('Error', response.message, 'error');
          }
        },
        error: function(xhr) {
          Swal.fire('Error', 'Failed to delete comment.', 'error');
        }
      });
    }
  });
}

// ================= FETCH POSTS =================
async function fetchAllPosts() {
  try {
    const response = await $.ajax({
      url: '<?= base_url('post/fetch') ?>',
      method: 'GET'
    });

    if (response.success) {
      if (response.data.length === 0) {
        $('#show_posts').html('<p>No posts available.</p>');
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

            imagesHtml += `
              <img src="<?= base_url() ?>/${imagePath}" 
                   class="img-fluid mb-2 me-1" 
                   alt="${post.title}" 
                   style="max-width: 100px; max-height: 100px; object-fit: cover;" 
                   loading="lazy">
            `;
          });
        } else {
          imagesHtml = `
            <img src="<?= base_url('uploads/default.jpg') ?>" 
                 class="img-fluid mb-2" 
                 alt="Default Image" 
                 style="max-width: 100px; max-height: 100px; object-fit: cover;" 
                 loading="lazy">
          `;
        }

        postsHtml += `
          <div class="col-md-4 mb-3">
            <div class="card shadow-sm h-100">
              <div class="card-body">
                <h5 class="card-title">${post.title}</h5>
                <p class="card-text">${post.body.substring(0, 100)}...</p>
                <p class="text-muted">Category: ${post.category ?? 'No category'}</p>

                <div class="mb-2">
                  ${imagesHtml}
                </div>

                <div class="d-flex flex-wrap gap-1">
                  <button class="btn btn-warning btn-sm" onclick="editPost(${post.id})">Edit</button>
                  <button class="btn btn-danger btn-sm" onclick="deletePost(${post.id})">Delete</button>

                  <button class="btn btn-outline-danger btn-sm" onclick="likePost(${post.id})">
                    ❤️ Like (<span id="likes-${post.id}">${post.likes_count ?? 0}</span>)
                  </button>

                  <button class="btn btn-outline-primary btn-sm" onclick="openComments(${post.id})">
                    💬 Comments (${post.comments_count ?? 0})
                  </button>
                </div>
              </div>

              <div class="card-footer text-muted">
                Created At: ${post.created_at}
              </div>
            </div>
          </div>
        `;
      });

      $('#show_posts').html(postsHtml);
    } else {
      Swal.fire('Error', response.message, 'error');
    }
  } catch (error) {
    Swal.fire('Error', 'Failed to fetch posts.', 'error');
  }
}

// ================= ADD POST FORM =================
$(document).ready(function () {
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
            <img src="${e.target.result}" class="img-thumbnail" style="max-width: 100px; max-height: 100px;" alt="New Image">
            <button type="button" class="btn btn-danger btn-sm mt-1 delete-new-image-btn" data-index="${allSelectedFiles.length - 1}">Delete</button>
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
      title: { type: 'string', title: 'Post Title', required: true },
      category: {
        type: 'string',
        title: 'Post Category',
        enum: [],
        required: true
      },
      body: { type: 'string', title: 'Post Body', required: true }
    },
    form: [
      { key: 'title', placeholder: 'Enter title' },
      { key: 'category', type: 'select', placeholder: 'Select a category' },
      { key: 'body', type: 'textarea', placeholder: 'Enter body' }
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
            Swal.fire('Added', response.message, 'success');
            fetchAllPosts();
            $('#add_post_modal').modal('hide');
            clearForm('add_post_form_container', 'add_image_previews', allSelectedFiles);
          } else {
            Swal.fire('Error', response.message, 'error');
          }
        },
        error: function () {
          Swal.fire('Error', 'Failed to add post.', 'error');
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
          let categoryOptions = '<option value="">Select a category</option>';

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