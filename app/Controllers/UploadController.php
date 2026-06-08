<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class UploadController extends BaseController
{
    public function show(string $filename)
    {
        $filename = basename($filename);

        if (!preg_match('/^[A-Za-z0-9._-]+$/', $filename)) {
            return $this->response->setStatusCode(404);
        }

        $paths = [
            FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $filename,
            ROOTPATH . 'uploads' . DIRECTORY_SEPARATOR . $filename,
            WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . $filename,
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $this->response
                    ->setHeader('Content-Type', mime_content_type($path) ?: 'application/octet-stream')
                    ->setHeader('Cache-Control', 'public, max-age=31536000')
                    ->setBody(file_get_contents($path));
            }
        }

        $defaultPath = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . 'default.jpg';

        if (is_file($defaultPath)) {
            return $this->response
                ->setHeader('Content-Type', mime_content_type($defaultPath) ?: 'image/jpeg')
                ->setHeader('Cache-Control', 'public, max-age=3600')
                ->setBody(file_get_contents($defaultPath));
        }

        return $this->response->setStatusCode(404);
    }
}
