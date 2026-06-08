<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AdvertisementModel;

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

    private function advertisementFieldExists(string $field): bool
    {
        return db_connect()->fieldExists($field, 'advertisements');
    }

    private function advertisementDataForExistingFields(array $data): array
    {
        return array_filter(
            $data,
            fn (string $field): bool => $this->advertisementFieldExists($field),
            ARRAY_FILTER_USE_KEY
        );
    }

    private function usersTableCanJoin(): bool
    {
        $db = db_connect();

        return $db->tableExists('users')
            && $db->fieldExists('id', 'users')
            && $db->fieldExists('username', 'users');
    }

    private function prepareAdvertisement(array $ad): array
    {
        $images = $ad['images'] ?? '[]';
        $ad['images'] = is_string($images) ? (json_decode($images, true) ?? []) : (array) $images;
        $ad['status'] = $ad['status'] ?? 'active';
        $ad['seller_name'] = $ad['seller_name'] ?? null;
        $ad['can_manage'] = $this->canManageAdvertisement($ad);
        $ad['can_contact'] = !$ad['can_manage']
            && !empty($ad['user_id'])
            && $ad['status'] !== 'sold';

        return $ad;
    }

    public function fetch()
    {
        $db = db_connect();

        if (!$db->tableExists('advertisements')) {
            return $this->response->setJSON([
                'success' => true,
                'data' => [],
                'message' => 'Sludinājumu tabula vēl nav izveidota.'
            ]);
        }

        $query = $this->adModel->select('advertisements.*');

        if ($this->advertisementFieldExists('user_id') && $this->usersTableCanJoin()) {
            $query
                ->select('users.username AS seller_name')
                ->join('users', 'users.id = advertisements.user_id', 'left');
        }

        if ($this->advertisementFieldExists('created_at')) {
            $query->orderBy('advertisements.created_at', 'DESC');
        } else {
            $query->orderBy('advertisements.id', 'DESC');
        }

        $ads = array_map(
            fn (array $ad): array => $this->prepareAdvertisement($ad),
            $query->findAll()
        );

        return $this->response->setJSON([
            'success' => true,
            'data' => $ads
        ]);
    }

    public function add()
    {
        if (!db_connect()->tableExists('advertisements')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sludinājumu tabula vēl nav izveidota.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(500);
        }

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
            $uploadPath = FCPATH . 'uploads';

            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0775, true);
            }

            foreach ($uploadedFiles['images'] as $file) {
                if ($file->isValid() && !$file->hasMoved()) {
                    $mimeType = (string) $file->getMimeType();

                    if (!str_starts_with($mimeType, 'image/')) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Lūdzu, augšupielādē tikai attēlu failus.',
                            'csrfToken' => csrf_hash()
                        ])->setStatusCode(400);
                    }

                    $newName = $file->getRandomName();
                    $file->move($uploadPath, $newName);

                    if (!$file->hasMoved()) {
                        return $this->response->setJSON([
                            'success' => false,
                            'message' => 'Neizdevās saglabāt sludinājuma attēlu.',
                            'csrfToken' => csrf_hash()
                        ])->setStatusCode(500);
                    }

                    $imagePaths[] = 'uploads/' . $newName;
                }
            }
        }

        $adData = $this->advertisementDataForExistingFields([
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

        $adId = $this->adModel->insert($adData);

        if ($adId === false) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Neizdevās saglabāt sludinājumu.',
                'errors' => $this->adModel->errors(),
                'csrfToken' => csrf_hash()
            ])->setStatusCode(500);
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Sludinājums veiksmīgi pievienots.',
            'id' => $adId,
            'csrfToken' => csrf_hash()
        ]);
    }

    public function detail($id)
    {
        if (!db_connect()->tableExists('advertisements')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sludinājums nav atrasts.'
            ])->setStatusCode(404);
        }

        $query = $this->adModel->select('advertisements.*');

        if ($this->advertisementFieldExists('user_id') && $this->usersTableCanJoin()) {
            $query
                ->select('users.username AS seller_name')
                ->join('users', 'users.id = advertisements.user_id', 'left');
        }

        $ad = $query->find($id);

        if (!$ad) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sludinājums nav atrasts.'
            ])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $this->prepareAdvertisement($ad)
        ]);
    }

    public function update()
    {
        if (!db_connect()->tableExists('advertisements')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sludinājumu tabula vēl nav izveidota.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(500);
        }

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

        $this->adModel->update($id, $this->advertisementDataForExistingFields([
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'location' => $location,
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ]));

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Sludinājums veiksmīgi atjaunināts.',
            'csrfToken' => csrf_hash()
        ]);
    }

    public function delete($id)
    {
        if (!db_connect()->tableExists('advertisements')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Sludinājums nav atrasts.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(404);
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
                'message' => 'Tu vari dzēst tikai savus sludinājumus.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(403);
        }

        $images = json_decode($ad['images'] ?? '[]', true) ?? [];

        foreach ($images as $image) {
            $relativeImage = ltrim((string) $image, '/');
            $imagePaths = [
                FCPATH . $relativeImage,
                ROOTPATH . $relativeImage,
                WRITEPATH . preg_replace('#^uploads/#', 'uploads/', $relativeImage),
            ];

            foreach ($imagePaths as $imagePath) {
                if (is_file($imagePath)) {
                    unlink($imagePath);
                }
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
