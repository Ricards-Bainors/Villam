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

    public function fetch()
    {
        $ads = $this->adModel
            ->where('status', 'active')
            ->orderBy('created_at', 'DESC')
            ->findAll();

        foreach ($ads as &$ad) {
            $ad['images'] = json_decode($ad['images'], true) ?? [];
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
                'message' => 'Title, description and price are required.',
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
            'message' => 'Advertisement added successfully.',
            'csrfToken' => csrf_hash()
        ]);
    }

    public function detail($id)
    {
        $ad = $this->adModel->find($id);

        if (!$ad) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Advertisement not found.'
            ])->setStatusCode(404);
        }

        $ad['images'] = json_decode($ad['images'], true) ?? [];

        return $this->response->setJSON([
            'success' => true,
            'data' => $ad
        ]);
    }
}