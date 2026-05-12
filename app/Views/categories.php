<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= csrf_hash() ?>">
  <title>Categories</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.13.6/underscore-min.js"></script>
  <script src="<?= base_url('js/jsonform.js') ?>"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
  <div class="container">
    <h1 class="my-4">Categories</h1>
    <div class="d-flex justify-content-between mb-3">
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#add_category_modal">Add New Category</button>
      <a href="<?= base_url('') ?>" class="btn btn-secondary">Go to Posts</a> <!-- Button to redirect to posts -->
    </div>
    <div id="categories_table">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="categories_table_body">
          <!-- Rows will be dynamically added here -->
        </tbody>
      </table>
    </div>
  </div>
  <!-- Add New Category Modal -->
  <div class="modal fade" id="add_category_modal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addCategoryModalLabel">Add New Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="add_category_form">
            <div class="mb-3">
              <label for="category_name" class="form-label">Category Name</label>
              <input type="text" class="form-control" id="category_name" name="category_name" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Category</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Category Modal -->
  <div class="modal fade" id="edit_category_modal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="edit_category_form">
            <input type="hidden" id="edit_category_id" name="id">
            <div class="mb-3">
              <label for="edit_category_name" class="form-label">Category Name</label>
              <input type="text" class="form-control" id="edit_category_name" name="category_name" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Category</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script>
    $(document).ready(function () {
      // Helper to get CSRF token from meta tag
      function getCsrfToken() {
        return $('meta[name="csrf-token"]').attr('content');
      }

      // Helper to update CSRF token in meta tag after AJAX response
      function updateCsrfToken(token) {
        if (token) {
          $('meta[name="csrf-token"]').attr('content', token);
        }
      }

      // Handle Add Category Form Submission
      $('#add_category_form').on('submit', function (e) {
        e.preventDefault(); // Prevent the default form submission

        const categoryName = $('#category_name').val();
        console.log('Category Name:', categoryName); // Debugging: Log the category name

        $.ajax({
          url: '<?= base_url('categories/add') ?>', // Backend endpoint for adding a category
          method: 'post',
          data: { 
            category_name: categoryName,
            '<?= csrf_token() ?>': getCsrfToken()
          },
          success: function (response) {
            updateCsrfToken(response.csrfToken);
            if (response.success) {
              Swal.fire('Success', response.message, 'success');
              $('#add_category_modal').modal('hide'); // Close the modal
              $('#add_category_form')[0].reset(); // Reset the form
              fetchCategories(); // Refresh the categories table
            } else {
              Swal.fire('Error', response.message, 'error');
            }
          },
          error: function (xhr, status, error) {
            console.error('Error adding category:', error);
            Swal.fire('Error', 'Failed to add category.', 'error');
          }
        });
      });

      // Fetch categories
      function fetchCategories() {
        $.ajax({
          url: '<?= base_url('categories/fetch') ?>',
          method: 'get',
          success: function (response) {
            if (response.success) {
              let rows = '';
              response.data.forEach(item => {
                rows += `<tr>
                  <td>${item.id}</td>
                  <td>${item.category_name}</td>
                  <td>${item.created_at}</td>
                  <td>
                    <button class="btn btn-warning btn-sm edit-category" data-id="${item.id}" data-name="${item.category_name}">Edit</button>
                    <button class="btn btn-danger btn-sm delete-category" data-id="${item.id}">Delete</button>
                  </td>
                </tr>`;
              });
              $('#categories_table_body').html(rows);

              // Attach event listeners for Edit and Delete buttons
              $('.edit-category').on('click', handleEditCategory);
              $('.delete-category').on('click', handleDeleteCategory);
            } else {
              $('#categories_table_body').html('<tr><td colspan="4">No data available</td></tr>');
            }
          },
          error: function (xhr, status, error) {
            console.error('Error fetching data:', error);
            $('#categories_table_body').html('<tr><td colspan="4">Failed to load data</td></tr>');
          }
        });
      }

      // Handle Edit Category
      function handleEditCategory() {
        const id = $(this).data('id');
        const name = $(this).data('name');

        // Populate the modal with the category data
        $('#edit_category_id').val(id);
        $('#edit_category_name').val(name);

        // Show the edit modal
        $('#edit_category_modal').modal('show');
      }

      // Handle Edit Category Form Submission
      $('#edit_category_form').on('submit', function (e) {
        e.preventDefault();

        const id = $('#edit_category_id').val();
        const categoryName = $('#edit_category_name').val();

        $.ajax({
          url: '<?= base_url('categories/update') ?>',
          method: 'post',
          data: { 
            id: id, 
            category_name: categoryName,
            '<?= csrf_token() ?>': getCsrfToken()
          },
          success: function (response) {
            updateCsrfToken(response.csrfToken);
            if (response.success) {
              Swal.fire('Success', response.message, 'success');
              $('#edit_category_modal').modal('hide'); // Close the modal
              fetchCategories(); // Refresh the table
            } else {
              Swal.fire('Error', response.message, 'error');
            }
          },
          error: function (xhr, status, error) {
            console.error('Error updating category:', error);
            Swal.fire('Error', 'Failed to update category.', 'error');
          }
        });
      });

      // Handle Delete Category
      function handleDeleteCategory() {
        const id = $(this).data('id');

        Swal.fire({
          title: 'Are you sure?',
          text: 'This action cannot be undone!',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, delete it!',
          cancelButtonText: 'Cancel'
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: '<?= base_url('categories/delete') ?>/' + id,
              method: 'post',
              data: {
                '<?= csrf_token() ?>': getCsrfToken()
              },
              success: function (response) {
                updateCsrfToken(response.csrfToken);
                if (response.success) {
                  Swal.fire('Deleted!', response.message, 'success');
                  fetchCategories(); // Refresh the table
                } else {
                  Swal.fire('Error', response.message, 'error');
                }
              },
              error: function (xhr, status, error) {
                console.error('Error deleting category:', error);
                Swal.fire('Error', 'Failed to delete category.', 'error');
              }
            });
          }
        });
      }

      // Call fetchCategories on page load
      fetchCategories();
    });
  </script>
</body>

</html>