<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DiscussionModel;

class ForumController extends BaseController
{
    protected $discussionModel;

    public function __construct()
    {
        $this->discussionModel = new DiscussionModel();
    }

    public function index()
    {
        return view('forum');
    }

    public function fetch()
    {
        $db = \Config\Database::connect();

        $builder = $db->table('discussions');
        $builder->select('discussions.*, categories.category_name as category');
        $builder->join('categories', 'categories.id = discussions.category_id', 'left');
        $builder->orderBy('discussions.created_at', 'DESC');

        $data = $builder->get()->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'data' => $data
        ]);
    }

    public function add()
    {
        $title = $this->request->getPost('title');
        $body = $this->request->getPost('body');
        $category = $this->request->getPost('category');

        if (empty($title) || empty($body)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Title and body are required.',
                'csrfToken' => csrf_hash()
            ]);
        }

        $this->discussionModel->insert([
            'title' => $title,
            'body' => $body,
            'category_id' => $category ?: null,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Discussion created successfully.',
            'csrfToken' => csrf_hash()
        ]);
    }

    public function detail($id)
    {
        $discussion = $this->discussionModel->find($id);

        return $this->response->setJSON([
            'success' => true,
            'data' => $discussion
        ]);
    }


    public function replies($discussionId)
    {
        $db = \Config\Database::connect();

        $replies = $db->table('discussion_replies')
            ->select('discussion_replies.*, users.username as user_name')
            ->join('users', 'users.id = discussion_replies.user_id', 'left')
            ->where('discussion_replies.discussion_id', $discussionId)
            ->orderBy('discussion_replies.created_at', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'current_user_id' => session()->get('user_id'),
            'data' => $replies
        ]);
    }


    public function addReply()
    {
        $discussionId = $this->request->getPost('discussion_id');
        $reply = $this->request->getPost('reply');

        if (empty($discussionId) || empty($reply)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Reply cannot be empty.',
                'csrfToken' => csrf_hash()
            ]);
        }

        $db = \Config\Database::connect();

        $db->table('discussion_replies')->insert([
            'discussion_id' => $discussionId,
            'user_id' => session()->get('user_id'),
            'reply' => $reply,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Reply added.',
            'csrfToken' => csrf_hash()
        ]);
    }

    public function deleteReply($replyId)
    {
        $db = \Config\Database::connect();

        $reply = $db->table('discussion_replies')
            ->where('id', $replyId)
            ->get()
            ->getRowArray();

        if (!$reply) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Reply not found.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(404);
        }

        if ($reply['user_id'] != session()->get('user_id')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You can only delete your own replies.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(403);
        }

        $db->table('discussion_replies')
            ->where('id', $replyId)
            ->delete();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Reply deleted.',
            'csrfToken' => csrf_hash()
        ]);
    }
}