<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PostModel;
use Config\Services as ConfigServices;
use App\Models\CategoryModel;

class PostController extends BaseController {
    protected $postModel;

    public function __construct() {
        $this->postModel = new PostModel();
    }
    public function index() {
        $data['posts'] = $this->postModel->findAll(); // Fetch all posts
        return view('index', $data);
    }

    // handle add new post ajax request
    public function add()
    {
        try {
            $title = $this->request->getPost('title');
            $category = $this->request->getPost('category');
            $body = $this->request->getPost('body');
            $uploadedFiles = $this->request->getFiles();

            // Validate required fields
            if (empty($title) || empty($category) || empty($body)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'All fields are required.'
                ]);
            }

            // Process uploaded images
            $imagePaths = [];
            if (isset($uploadedFiles['images'])) {
                foreach ($uploadedFiles['images'] as $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                        $newName = $file->getRandomName();
                        $file->move(FCPATH . 'uploads', $newName);
                        $imagePaths[] = 'uploads/' . $newName;
                    }
                }
            }

            $postData = [
                'title' => $title,
                'category_id' => $category,
                'body' => $body,
                'images' => json_encode($imagePaths),
                'created_at' => date('Y-m-d H:i:s')
            ];

            if ($this->postModel->createPost($postData)) { 
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Post added successfully.',
                    'csrfToken' => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to add post.',
                    'csrfToken' => csrf_hash()
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error adding post: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while adding the post.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    // handle edit post ajax request
    public function edit($id = null)
    {
        $postModel = new PostModel();
        $categoryModel = new CategoryModel();

        $post = $postModel->find($id);
        $categories = $categoryModel->findAll();

        if ($post) {
            // Decode the images field to ensure it's an array
            $post['images'] = json_decode($post['images'], true) ?? [];

            return $this->response->setJSON([
                'error' => false,
                'message' => [
                    'post' => $post,
                    'categories' => $categories
                ]
            ]);
        } else {
            return $this->response->setJSON([
                'error' => true,
                'message' => 'Post not found.'
            ])->setStatusCode(404);
        }
    }

    // handle update post ajax request
    public function update()
    {
        try {
            $postId = $this->request->getPost('id');
            $title = $this->request->getPost('title');
            $category = $this->request->getPost('category');
            $body = $this->request->getPost('body');
            $existingImages = json_decode($this->request->getPost('existing_images'), true);
            $imagesToDelete = json_decode($this->request->getPost('images_to_delete'), true);
            $newImages = $this->request->getFiles();

            // Validate required fields
            if (empty($postId) || empty($title) || empty($category) || empty($body)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Missing required fields.'
                ])->setStatusCode(400);
            }

            // Initialize image paths
            $imagePaths = $existingImages ?? [];

            // Delete specified existing images
            if (!empty($imagesToDelete)) {
                foreach ($imagesToDelete as $image) {
                    $imagePath = FCPATH . $image;
                    if (file_exists($imagePath)) {
                        unlink($imagePath); // Delete the image file
                    }
                    $imagePaths = array_filter($imagePaths, fn($img) => $img !== $image);
                }
            }

            // Process new images
            if (!empty($newImages['new_images'])) {
                foreach ($newImages['new_images'] as $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                        $newName = $file->getRandomName();
                        $file->move(FCPATH . 'uploads', $newName);
                        $imagePaths[] = 'uploads/' . $newName;
                    }
                }
            }

            $updateData = [
                'title' => $title,
                'category_id' => $category,
                'body' => $body,
                'images' => json_encode($imagePaths),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if ($this->postModel->updatePost($postId, $updateData)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Post updated successfully.',
                    'csrfToken' => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to update the post.',
                    'csrfToken' => csrf_hash()
                ])->setStatusCode(500);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error updating post: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while updating the post.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(500);
        }
    }



    public function like()
    {
        $postId = $this->request->getPost('post_id');

        if (empty($postId)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Post ID is required.',
                'csrfToken' => csrf_hash()
            ]);
        }

        $db = \Config\Database::connect();

        // Temporary user id until session user is connected
        $userId = session()->get('user_id') ?? null;

        $existing = $db->table('post_likes')
            ->where('post_id', $postId)
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();

        if ($existing) {
            $db->table('post_likes')->delete([
                'post_id' => $postId,
                'user_id' => $userId
            ]);

            $liked = false;
        } else {
            $db->table('post_likes')->insert([
                'post_id' => $postId,
                'user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $liked = true;
        }

        $count = $db->table('post_likes')
            ->where('post_id', $postId)
            ->countAllResults();

        return $this->response->setJSON([
            'success' => true,
            'liked' => $liked,
            'likes' => $count,
            'csrfToken' => csrf_hash()
        ]);
    }


    public function comments($postId)
    {
        $db = \Config\Database::connect();

        $comments = $db->table('post_comments')
            ->select('post_comments.*, users.username as user_name')
            ->join('users', 'users.id = post_comments.user_id', 'left')
            ->where('post_comments.post_id', $postId)
            ->orderBy('post_comments.created_at', 'DESC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'current_user_id' => session()->get('user_id'),
            'data' => $comments
        ]);
    }


    public function addComment()
    {
        $postId = $this->request->getPost('post_id');
        $comment = $this->request->getPost('comment');

        if (empty($postId) || empty($comment)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Comment is required.',
                'csrfToken' => csrf_hash()
            ]);
        }

        $db = \Config\Database::connect();

        $db->table('post_comments')->insert([
            'post_id' => $postId,
            'user_id' => session()->get('user_id') ?? null,
            'comment' => $comment,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Comment added.',
            'csrfToken' => csrf_hash()
        ]);
    }



    public function deleteComment($commentId)
    {
        $db = \Config\Database::connect();

        $comment = $db->table('post_comments')
            ->where('id', $commentId)
            ->get()
            ->getRowArray();

        if (!$comment) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Comment not found.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(404);
        }

        $currentUserId = session()->get('user_id');

        if ($comment['user_id'] != $currentUserId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You can only delete your own comments.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(403);
        }

        $db->table('post_comments')
            ->where('id', $commentId)
            ->delete();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Comment deleted.',
            'csrfToken' => csrf_hash()
        ]);
    }


    // handle delete post ajax request
    public function delete($id = null)
    {
        try {
            if ($this->postModel->deletePost($id)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Post deleted successfully.',
                    'csrfToken' => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to delete post.',
                    'csrfToken' => csrf_hash()
                ])->setStatusCode(500);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error deleting post: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'An error occurred while deleting the post.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(500);
        }
    }

    //handle fetch post detail ajax request
    public function detail($id = null)
    {
        try {
            $db = \Config\Database::connect();
            $builder = $db->table('posts');
            $post = $builder->where('id', $id)->get()->getRowArray();

            if ($post) {
                $post['images'] = json_decode($post['images'], true); // Decode images field
                return $this->response->setJSON([
                    'error' => false,
                    'message' => $post
                ]);
            } else {
                return $this->response->setJSON([
                    'error' => true,
                    'message' => 'Post not found.'
                ])->setStatusCode(404);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error fetching post details: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => true,
                'message' => 'An error occurred while fetching post details.'
            ])->setStatusCode(500);
        }
    }

    public function fetch()
    {
        try {
            $posts = $this->postModel->getAllPosts();
            $db = \Config\Database::connect();

            foreach ($posts as &$post) {
                $post['images'] = json_decode($post['images'], true) ?? [];

                $post['likes_count'] = $db->table('post_likes')
                    ->where('post_id', $post['id'])
                    ->countAllResults();

                $post['comments_count'] = $db->table('post_comments')
                    ->where('post_id', $post['id'])
                    ->countAllResults();
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $posts,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error fetching posts.',
            ])->setStatusCode(500);
        }
    }

    public function fetchCategories()
    {
        try {
            $categoryModel = new \App\Models\CategoryModel(); // Ensure this model exists
            $categories = $categoryModel->getAllCategories(); // Fetch all categories

            return $this->response->setJSON([
                'success' => true,
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error fetching categories: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error fetching categories.',
            ])->setStatusCode(500);
        }
    }

    public function categories()
    {
        return view('categories');
    }

    public function add_category()
    {
        $categoryName = $this->request->getPost('category_name');

        if (empty($categoryName)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Category name is required.',
                'csrfToken' => csrf_hash()
            ]);
        }

        $data = [
            'category_name' => $categoryName,
            'created_at' => date('Y-m-d H:i:s')
        ];

        $db = \Config\Database::connect();
        $builder = $db->table('categories');

        if ($builder->insert($data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Category added successfully.',
                'csrfToken' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to add category.',
                'csrfToken' => csrf_hash()
            ]);
        }
    }

    public function delete_category($id)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('categories');

        if ($builder->delete(['id' => $id])) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Category deleted successfully.',
                'csrfToken' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete category.',
                'csrfToken' => csrf_hash()
            ]);
        }
    }

    public function update_category()
    {
        $id = $this->request->getPost('id');
        $categoryName = $this->request->getPost('category_name');

        if (empty($categoryName)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Category name is required.',
                'csrfToken' => csrf_hash()
            ]);
        }

        $db = \Config\Database::connect();
        $builder = $db->table('categories');

        if ($builder->update(['category_name' => $categoryName], ['id' => $id])) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Category updated successfully.',
                'csrfToken' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update category.',
                'csrfToken' => csrf_hash()
            ]);
        }
    }
}