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

    private function isAdmin(): bool
    {
        return session()->get('username') === 'admin';
    }

    private function canManageDiscussion(array $discussion): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return !empty($discussion['user_id'])
            && (string) $discussion['user_id'] === (string) session()->get('user_id');
    }

    public function fetch()
    {
        $db = \Config\Database::connect();

        $builder = $db->table('discussions');
        $builder->select('discussions.*, categories.category_name as category');
        $builder->join('categories', 'categories.id = discussions.category_id', 'left');
        $builder->orderBy('discussions.created_at', 'DESC');

        $data = $builder->get()->getResultArray();

        foreach ($data as &$discussion) {
            $discussion['can_manage'] = $this->canManageDiscussion($discussion);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $data
        ]);
    }

    public function add()
    {
        $title = $this->request->getPost('title');
        $body = $this->request->getPost('body');
        $category = $this->request->getPost('category_id');

        if (empty($title) || empty($body)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Virsraksts un teksts ir obligāti.',
                'csrfToken' => csrf_hash()
            ]);
        }

        $this->discussionModel->insert([
            'user_id' => session()->get('user_id'),
            'title' => $title,
            'body' => $body,
            'category_id' => $category ?: null,
            'status' => 'open',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Diskusija veiksmīgi izveidota.',
            'csrfToken' => csrf_hash()
        ]);
    }

    public function detail($id)
    {
        $discussion = $this->discussionModel->find($id);

        if (!$discussion) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Diskusija nav atrasta.'
            ])->setStatusCode(404);
        }

        $discussion['can_manage'] = $this->canManageDiscussion($discussion);

        return $this->response->setJSON([
            'success' => true,
            'data' => $discussion
        ]);
    }

    public function update()
    {
        $id = $this->request->getPost('id');
        $title = $this->request->getPost('title');
        $body = $this->request->getPost('body');
        $status = $this->request->getPost('status');

        if (empty($id) || empty($title) || empty($body)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Virsraksts un teksts ir obligāti.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(400);
        }

        $discussion = $this->discussionModel->find($id);

        if (!$discussion) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Diskusija nav atrasta.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(404);
        }

        if (!$this->canManageDiscussion($discussion)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tu vari labot tikai savas diskusijas.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(403);
        }

        if (!in_array($status, ['open', 'closed'], true)) {
            $status = 'open';
        }

        $this->discussionModel->update($id, [
            'title' => $title,
            'body' => $body,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Diskusija veiksmīgi atjaunināta.',
            'csrfToken' => csrf_hash()
        ]);
    }

    public function delete($id)
    {
        $discussion = $this->discussionModel->find($id);

        if (!$discussion) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Diskusija nav atrasta.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(404);
        }

        if (!$this->canManageDiscussion($discussion)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tu vari dzēst tikai savas diskusijas.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(403);
        }

        $db = \Config\Database::connect();
        $db->table('discussion_replies')->where('discussion_id', $id)->delete();
        $this->discussionModel->delete($id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Diskusija veiksmīgi dzēsta.',
            'csrfToken' => csrf_hash()
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
            'current_is_admin' => $this->isAdmin(),
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
                'message' => 'Atbilde nevar būt tukša.',
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
            'message' => 'Atbilde pievienota.',
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
                'message' => 'Atbilde nav atrasta.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(404);
        }

        if (!$this->isAdmin() && $reply['user_id'] != session()->get('user_id')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tu vari dzēst tikai savas atbildes.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(403);
        }

        $db->table('discussion_replies')
            ->where('id', $replyId)
            ->delete();

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Atbilde dzēsta.',
            'csrfToken' => csrf_hash()
        ]);
    }
}
