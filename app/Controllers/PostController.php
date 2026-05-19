<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PostModel;
use Config\Services as ConfigServices;
use App\Models\CategoryModel;
use App\Models\UserModel;

class PostController extends BaseController {
    protected $postModel;

    public function __construct() {
        $this->postModel = new PostModel();
    }

    private function currentUserId()
    {
        return session()->get('user_id');
    }

    private function isAdmin(): bool
    {
        return session()->get('username') === 'admin';
    }

    private function ownsPost(array $post): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $currentUserId = $this->currentUserId();

        return !empty($currentUserId)
            && isset($post['user_id'])
            && (string) $post['user_id'] === (string) $currentUserId;
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
                    'message' => 'Visi lauki ir obligāti.'
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
                'user_id' => $this->currentUserId(),
                'title' => $title,
                'category_id' => $category,
                'body' => $body,
                'images' => json_encode($imagePaths),
                'created_at' => date('Y-m-d H:i:s')
            ];

            if ($this->postModel->createPost($postData)) { 
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Ieraksts veiksmīgi pievienots.',
                    'csrfToken' => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Neizdevās pievienot ierakstu.',
                    'csrfToken' => csrf_hash()
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error adding post: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Pievienojot ierakstu, radās kļūda.',
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
            if (!$this->ownsPost($post)) {
                return $this->response->setJSON([
                    'error' => true,
                    'message' => 'Tu vari rediģēt tikai savus ierakstus.'
                ])->setStatusCode(403);
            }

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
                'message' => 'Ieraksts nav atrasts.'
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
                    'message' => 'Trūkst obligāto lauku.'
                ])->setStatusCode(400);
            }

            $post = $this->postModel->find($postId);

            if (!$post) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Ieraksts nav atrasts.',
                    'csrfToken' => csrf_hash()
                ])->setStatusCode(404);
            }

            if (!$this->ownsPost($post)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tu vari atjaunināt tikai savus ierakstus.',
                    'csrfToken' => csrf_hash()
                ])->setStatusCode(403);
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
                    'message' => 'Ieraksts veiksmīgi atjaunināts.',
                    'csrfToken' => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Neizdevās atjaunināt ierakstu.',
                    'csrfToken' => csrf_hash()
                ])->setStatusCode(500);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error updating post: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Atjauninot ierakstu, radās kļūda.',
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
                'message' => 'Ieraksta ID ir obligāts.',
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
            'current_is_admin' => $this->isAdmin(),
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
                'message' => 'Komentārs ir obligāts.',
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
            'message' => 'Komentārs pievienots.',
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
                'message' => 'Komentārs nav atrasts.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(404);
        }

        $currentUserId = session()->get('user_id');

        if (!$this->isAdmin() && $comment['user_id'] != $currentUserId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tu vari dzēst tikai savus komentārus.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(403);
        }

        $db->table('post_comments')
            ->where('id', $commentId)
            ->delete();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Komentārs dzēsts.',
            'csrfToken' => csrf_hash()
        ]);
    }


    // handle delete post ajax request
    public function delete($id = null)
    {
        try {
            $post = $this->postModel->find($id);

            if (!$post) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Ieraksts nav atrasts.',
                    'csrfToken' => csrf_hash()
                ])->setStatusCode(404);
            }

            if (!$this->ownsPost($post)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tu vari dzēst tikai savus ierakstus.',
                    'csrfToken' => csrf_hash()
                ])->setStatusCode(403);
            }

            if ($this->postModel->deletePost($id)) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Ieraksts veiksmīgi dzēsts.',
                    'csrfToken' => csrf_hash()
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Neizdevās dzēst ierakstu.',
                    'csrfToken' => csrf_hash()
                ])->setStatusCode(500);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error deleting post: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Dzēšot ierakstu, radās kļūda.',
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
                    'message' => 'Ieraksts nav atrasts.'
                ])->setStatusCode(404);
            }
        } catch (\Exception $e) {
            log_message('error', 'Error fetching post details: ' . $e->getMessage());
            return $this->response->setJSON([
                'error' => true,
                'message' => 'Ielādējot ieraksta informāciju, radās kļūda.'
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
                $post['can_manage'] = $this->ownsPost($post);

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
                'message' => 'Kļūda, ielādējot ierakstus.',
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
                'message' => 'Kļūda, ielādējot kategorijas.',
            ])->setStatusCode(500);
        }
    }

    public function categories()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/posts');
        }

        return view('categories', [
            'is_admin' => $this->isAdmin(),
        ]);
    }

    public function users()
    {
        if (!$this->isAdmin()) {
            return redirect()->to('/posts');
        }

        return view('admin_users');
    }

    private function requireAdminJson()
    {
        if ($this->isAdmin()) {
            return null;
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'Šī darbība pieejama tikai administratoram.',
            'csrfToken' => csrf_hash()
        ])->setStatusCode(403);
    }

    public function add_category()
    {
        if ($response = $this->requireAdminJson()) {
            return $response;
        }

        $categoryName = $this->request->getPost('category_name');

        if (empty($categoryName)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Kategorijas nosaukums ir obligāts.',
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
                'message' => 'Kategorija veiksmīgi pievienota.',
                'csrfToken' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Neizdevās pievienot kategoriju.',
                'csrfToken' => csrf_hash()
            ]);
        }
    }

    public function delete_category($id)
    {
        if ($response = $this->requireAdminJson()) {
            return $response;
        }

        $db = \Config\Database::connect();
        $builder = $db->table('categories');

        if ($builder->delete(['id' => $id])) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Kategorija veiksmīgi dzēsta.',
                'csrfToken' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Neizdevās dzēst kategoriju.',
                'csrfToken' => csrf_hash()
            ]);
        }
    }

    public function update_category()
    {
        if ($response = $this->requireAdminJson()) {
            return $response;
        }

        $id = $this->request->getPost('id');
        $categoryName = $this->request->getPost('category_name');

        if (empty($categoryName)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Kategorijas nosaukums ir obligāts.',
                'csrfToken' => csrf_hash()
            ]);
        }

        $db = \Config\Database::connect();
        $builder = $db->table('categories');

        if ($builder->update(['category_name' => $categoryName], ['id' => $id])) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Kategorija veiksmīgi atjaunināta.',
                'csrfToken' => csrf_hash()
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Neizdevās atjaunināt kategoriju.',
                'csrfToken' => csrf_hash()
            ]);
        }
    }

    public function fetchUsers()
    {
        if ($response = $this->requireAdminJson()) {
            return $response;
        }

        $userModel = new UserModel();

        return $this->response->setJSON([
            'success' => true,
            'data' => $userModel->getAllUsers(),
            'csrfToken' => csrf_hash()
        ]);
    }

    public function updateUser()
    {
        if ($response = $this->requireAdminJson()) {
            return $response;
        }

        $id = (int) $this->request->getPost('id');
        $username = trim((string) $this->request->getPost('username'));
        $email = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');

        if (empty($id) || empty($username) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Lietotājvārds un derīgs e-pasts ir obligāti.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(400);
        }

        $data = [
            'username' => $username,
            'email' => $email,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($password !== '') {
            if (strlen($password) < 4) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Parolei jābūt vismaz 4 rakstzīmes garai.',
                    'csrfToken' => csrf_hash()
                ])->setStatusCode(400);
            }

            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $userModel = new UserModel();
        $userModel->updateUser($id, $data);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Lietotāja dati veiksmīgi atjaunināti.',
            'csrfToken' => csrf_hash()
        ]);
    }

    public function deleteUser($id)
    {
        if ($response = $this->requireAdminJson()) {
            return $response;
        }

        $id = (int) $id;

        if (empty($id)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Lietotāja ID ir obligāts.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(400);
        }

        $userModel = new UserModel();
        $user = $userModel->findUserById($id);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Lietotājs nav atrasts.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(404);
        }

        if ((string) $user['username'] === 'admin' || (string) $id === (string) $this->currentUserId()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Administratora kontu nevar dzēst.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(400);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $this->cleanupUserReferences($id);
        $deleted = $userModel->deleteUser($id);

        $db->transComplete();

        if (!$deleted || $db->transStatus() === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Neizdevās dzēst lietotāju.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Lietotājs veiksmīgi dzēsts.',
            'csrfToken' => csrf_hash()
        ]);
    }

    private function cleanupUserReferences(int $userId): void
    {
        $db = \Config\Database::connect();

        if ($db->tableExists('conversation_messages') && $db->tableExists('conversations')) {
            $conversationIds = $db->table('conversations')
                ->select('id')
                ->groupStart()
                    ->where('buyer_id', $userId)
                    ->orWhere('seller_id', $userId)
                ->groupEnd()
                ->get()
                ->getResultArray();

            $conversationIds = array_column($conversationIds, 'id');

            if (!empty($conversationIds)) {
                $db->table('conversation_messages')
                    ->whereIn('conversation_id', $conversationIds)
                    ->delete();
                $db->table('conversations')
                    ->whereIn('id', $conversationIds)
                    ->delete();
            }
        }

        if ($db->tableExists('post_likes')) {
            $db->table('post_likes')->where('user_id', $userId)->delete();
        }

        if ($db->tableExists('post_comments')) {
            $db->table('post_comments')->where('user_id', $userId)->delete();
        }

        if ($db->tableExists('discussion_replies')) {
            $db->table('discussion_replies')->where('user_id', $userId)->delete();
        }

        foreach (['posts', 'advertisements', 'discussions'] as $table) {
            if ($db->tableExists($table)) {
                $db->table($table)
                    ->where('user_id', $userId)
                    ->update(['user_id' => null]);
            }
        }
    }
}
