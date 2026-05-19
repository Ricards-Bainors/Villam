<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AdvertisementModel;

class MessageController extends BaseController
{
    private function currentUserId()
    {
        return session()->get('user_id');
    }

    public function index()
    {
        return view('messages');
    }

    public function inbox()
    {
        $userId = $this->currentUserId();
        $db = \Config\Database::connect();

        $conversations = $db->table('conversations c')
            ->select('c.*, a.title AS advertisement_title, a.status AS advertisement_status, buyer.username AS buyer_name, seller.username AS seller_name')
            ->join('advertisements a', 'a.id = c.advertisement_id', 'left')
            ->join('users buyer', 'buyer.id = c.buyer_id', 'left')
            ->join('users seller', 'seller.id = c.seller_id', 'left')
            ->groupStart()
                ->where('c.buyer_id', $userId)
                ->orWhere('c.seller_id', $userId)
            ->groupEnd()
            ->orderBy('c.updated_at', 'DESC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'data' => $conversations,
            'current_user_id' => $userId,
        ]);
    }

    public function start($id)
    {
        if ($this->request->getPost('start_type') === 'user') {
            return $this->startDirectConversation((int) $id);
        }

        return $this->startAdvertisementConversation((int) $id);
    }

    private function startDirectConversation(int $targetUserId)
    {
        $userId = $this->currentUserId();

        if (!$userId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Lietotājs nav ielogojies.',
                'csrfToken' => csrf_hash(),
            ])->setStatusCode(401);
        }

        if ((string) $targetUserId === (string) $userId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tu nevari sākt saraksti pats ar sevi.',
                'csrfToken' => csrf_hash(),
            ])->setStatusCode(400);
        }

        $db = \Config\Database::connect();
        $targetUser = $db->table('users')
            ->select('id')
            ->where('id', $targetUserId)
            ->get()
            ->getRowArray();

        if (!$targetUser) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Lietotājs nav atrasts.',
                'csrfToken' => csrf_hash(),
            ])->setStatusCode(404);
        }

        $conversation = $db->table('conversations')
            ->where('advertisement_id', null)
            ->groupStart()
                ->groupStart()
                    ->where('buyer_id', $userId)
                    ->where('seller_id', $targetUserId)
                ->groupEnd()
                ->orGroupStart()
                    ->where('buyer_id', $targetUserId)
                    ->where('seller_id', $userId)
                ->groupEnd()
            ->groupEnd()
            ->get()
            ->getRowArray();

        if (!$conversation) {
            $db->table('conversations')->insert([
                'advertisement_id' => null,
                'buyer_id' => $userId,
                'seller_id' => $targetUserId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $conversationId = $db->insertID();
        } else {
            $conversationId = $conversation['id'];
        }

        return $this->response->setJSON([
            'success' => true,
            'conversation_id' => $conversationId,
            'csrfToken' => csrf_hash(),
        ]);
    }

    private function startAdvertisementConversation(int $advertisementId)
    {
        $userId = $this->currentUserId();
        $adModel = new AdvertisementModel();
        $ad = $adModel->find($advertisementId);

        if (!$ad) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sludinājums nav atrasts.',
                'csrfToken' => csrf_hash(),
            ])->setStatusCode(404);
        }

        if (($ad['status'] ?? 'active') === 'sold') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Par pārdotu sludinājumu saraksti sākt nevar.',
                'csrfToken' => csrf_hash(),
            ])->setStatusCode(400);
        }

        if (empty($ad['user_id'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Šim sludinājumam nav norādīts pārdevējs.',
                'csrfToken' => csrf_hash(),
            ])->setStatusCode(400);
        }

        if ((string) $ad['user_id'] === (string) $userId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tu nevari sākt saraksti pats ar sevi.',
                'csrfToken' => csrf_hash(),
            ])->setStatusCode(400);
        }

        $db = \Config\Database::connect();
        $conversation = $db->table('conversations')
            ->where('advertisement_id', $advertisementId)
            ->where('buyer_id', $userId)
            ->where('seller_id', $ad['user_id'])
            ->get()
            ->getRowArray();

        if (!$conversation) {
            $db->table('conversations')->insert([
                'advertisement_id' => $advertisementId,
                'buyer_id' => $userId,
                'seller_id' => $ad['user_id'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $conversationId = $db->insertID();
        } else {
            $conversationId = $conversation['id'];
        }

        return $this->response->setJSON([
            'success' => true,
            'conversation_id' => $conversationId,
            'csrfToken' => csrf_hash(),
        ]);
    }

    public function thread($conversationId)
    {
        $conversation = $this->findConversationForCurrentUser($conversationId);

        if (!$conversation) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sarakste nav atrasta.',
            ])->setStatusCode(404);
        }

        $db = \Config\Database::connect();
        $messages = $db->table('conversation_messages m')
            ->select('m.*, u.username AS sender_name')
            ->join('users u', 'u.id = m.sender_id', 'left')
            ->where('m.conversation_id', $conversationId)
            ->orderBy('m.created_at', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'success' => true,
            'conversation' => $conversation,
            'messages' => $messages,
            'current_user_id' => $this->currentUserId(),
        ]);
    }

    public function send()
    {
        $conversationId = $this->request->getPost('conversation_id');
        $message = trim((string) $this->request->getPost('message'));

        if (empty($conversationId) || $message === '') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ziņas teksts ir obligāts.',
                'csrfToken' => csrf_hash(),
            ])->setStatusCode(400);
        }

        $conversation = $this->findConversationForCurrentUser($conversationId);

        if (!$conversation) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sarakste nav atrasta.',
                'csrfToken' => csrf_hash(),
            ])->setStatusCode(404);
        }

        $db = \Config\Database::connect();
        $db->table('conversation_messages')->insert([
            'conversation_id' => $conversationId,
            'sender_id' => $this->currentUserId(),
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $db->table('conversations')
            ->where('id', $conversationId)
            ->update(['updated_at' => date('Y-m-d H:i:s')]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Ziņa nosūtīta.',
            'csrfToken' => csrf_hash(),
        ]);
    }

    private function findConversationForCurrentUser($conversationId): ?array
    {
        $userId = $this->currentUserId();
        $db = \Config\Database::connect();

        return $db->table('conversations c')
            ->select('c.*, a.title AS advertisement_title, a.status AS advertisement_status, buyer.username AS buyer_name, seller.username AS seller_name')
            ->join('advertisements a', 'a.id = c.advertisement_id', 'left')
            ->join('users buyer', 'buyer.id = c.buyer_id', 'left')
            ->join('users seller', 'seller.id = c.seller_id', 'left')
            ->where('c.id', $conversationId)
            ->groupStart()
                ->where('c.buyer_id', $userId)
                ->orWhere('c.seller_id', $userId)
            ->groupEnd()
            ->get()
            ->getRowArray() ?: null;
    }
}
