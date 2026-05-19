<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AdvertisementModel;
use App\Models\CategoryModel;

class AdvertisementController extends BaseController
{
    protected $adModel;

    public function __construct()
    {
        $this->adModel = new AdvertisementModel();
    }

    public function index()
    {
        return view('advertisements');
    }

    private function currentUserId()
    {
        return session()->get('user_id');
    }

    private function isAdmin(): bool
    {
        return session()->get('username') === 'admin';
    }

    private function canManageAdvertisement(array $ad): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return !empty($ad['user_id'])
            && (string) $ad['user_id'] === (string) $this->currentUserId();
    }

    public function fetch()
    {
        $ads = $this->adModel
            ->select('advertisements.*, users.username AS seller_name')
            ->join('users', 'users.id = advertisements.user_id', 'left')
            ->orderBy('advertisements.created_at', 'DESC')
            ->findAll();

        foreach ($ads as &$ad) {
            $ad['images'] = json_decode($ad['images'], true) ?? [];
            $ad['can_manage'] = $this->canManageAdvertisement($ad);
            $ad['can_contact'] = !$ad['can_manage']
                && !empty($ad['user_id'])
                && ($ad['status'] ?? 'active') !== 'sold';
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $ads
        ]);
    }

    public function add()
    {
        $title = $this->request->getPost('title');
        $description = $this->request->getPost('description');
        $price = $this->request->getPost('price');
        $category = $this->request->getPost('category_id');
        $location = $this->request->getPost('location');
        $uploadedFiles = $this->request->getFiles();

        if (empty($title) || empty($description) || empty($price)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Virsraksts, apraksts un cena ir obligāti.',
                'csrfToken' => csrf_hash()
            ]);
        }

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

        $this->adModel->insert([
            'user_id' => $this->currentUserId(),
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'category_id' => $category,
            'location' => $location,
            'images' => json_encode($imagePaths),
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Sludinājums veiksmīgi pievienots.',
            'csrfToken' => csrf_hash()
        ]);
    }

    public function detail($id)
    {
        $ad = $this->adModel
            ->select('advertisements.*, users.username AS seller_name')
            ->join('users', 'users.id = advertisements.user_id', 'left')
            ->find($id);

        if (!$ad) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sludinājums nav atrasts.'
            ])->setStatusCode(404);
        }

        $ad['images'] = json_decode($ad['images'], true) ?? [];
        $ad['can_manage'] = $this->canManageAdvertisement($ad);
        $ad['can_contact'] = !$ad['can_manage']
            && !empty($ad['user_id'])
            && ($ad['status'] ?? 'active') !== 'sold';

        return $this->response->setJSON([
            'success' => true,
            'data' => $ad
        ]);
    }

    public function update()
    {
        $id = $this->request->getPost('id');
        $title = $this->request->getPost('title');
        $description = $this->request->getPost('description');
        $price = $this->request->getPost('price');
        $location = $this->request->getPost('location');
        $status = $this->request->getPost('status');

        if (empty($id) || empty($title) || empty($description) || empty($price)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Virsraksts, apraksts un cena ir obligāti.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(400);
        }

        if (!in_array($status, ['active', 'sold'], true)) {
            $status = 'active';
        }

        $ad = $this->adModel->find($id);

        if (!$ad) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sludinājums nav atrasts.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(404);
        }

        if (!$this->canManageAdvertisement($ad)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tu vari labot tikai savus sludinājumus.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(403);
        }

        $this->adModel->update($id, [
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'location' => $location,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Sludinājums veiksmīgi atjaunināts.',
            'csrfToken' => csrf_hash()
        ]);
    }

    public function delete($id)
    {
        $ad = $this->adModel->find($id);

        if (!$ad) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sludinājums nav atrasts.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(404);
        }

        if (!$this->canManageAdvertisement($ad)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tu vari dzēst tikai savus sludinājumus.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(403);
        }

        $images = json_decode($ad['images'] ?? '[]', true) ?? [];

        foreach ($images as $image) {
            $imagePath = FCPATH . ltrim((string) $image, '/');

            if (is_file($imagePath)) {
                unlink($imagePath);
            }
        }

        $this->adModel->delete($id);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Sludinājums veiksmīgi dzēsts.',
            'csrfToken' => csrf_hash()
        ]);
    }
}
