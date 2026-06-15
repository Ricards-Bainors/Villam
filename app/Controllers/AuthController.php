<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class AuthController extends Controller
{
    public function showRegisterForm()
    {
        log_message('debug', 'Rendering registration form.');
        return view('auth/register');
    }

    public function register()
    {
        if (!$this->request->isAJAX() || $this->request->getMethod(true) !== 'POST') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method.'
            ])->setStatusCode(405);
        }

        $data = $this->request->getJSON(true);

        $csrfName = csrf_token();
        if (isset($data[$csrfName])) {
            unset($data[$csrfName]);
        }

        if (!isset($data['username'], $data['email'], $data['password'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Trūkst obligāto lauku.'
            ])->setStatusCode(400);
        }

        $userModel = new \App\Models\UserModel();
        $data['username'] = trim((string) $data['username']);
        $data['email'] = trim((string) $data['email']);
        $data['password'] = (string) $data['password'];

        if ($data['username'] === '' || $data['email'] === '' || $data['password'] === '') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Visi lauki ir obligāti.'
            ])->setStatusCode(400);
        }

        if (strlen($data['password']) < 7 || !preg_match('/\d/', $data['password'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Parolei jābūt vismaz 7 rakstzīmes garai un jāsatur vismaz viens cipars.'
            ])->setStatusCode(400);
        }

        if ($userModel->usernameExists($data['username'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Šis lietotājvārds jau ir aizņemts.'
            ])->setStatusCode(409);
        }

        if ($userModel->createUser($data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Registration successful.'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Neizdevās izveidot lietotāju.'
            ])->setStatusCode(500);
        }
    }

    public function showLoginForm()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/posts');
        }

        return view('auth/login');
    }

    public function login()
    {
        if (!$this->request->isAJAX() || $this->request->getMethod() !== 'POST') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method.',
                'csrfToken' => csrf_hash()
            ])->setStatusCode(405);
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $userModel = new \App\Models\UserModel();
        $user = $userModel->findUserByUsername($username);

        if (!$user || !password_verify($password, $user['password'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid credentials',
                'csrfToken' => csrf_hash()
            ]);
        }

        session()->set([
            'user_id' => $user['id'],
            'username' => $user['username'],
            'isLoggedIn' => true
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Pieslēgšanās veiksmīga',
            'csrfToken' => csrf_hash()
        ]);
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/auth/login')->with('success', 'You have been logged out.');
    }

    public function jsonResponse($data)
    {
        return $this->response->setJSON($data)->setHeader('Content-Type', 'application/json');
    }
}
