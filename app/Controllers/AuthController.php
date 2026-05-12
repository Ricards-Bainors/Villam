<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class AuthController extends Controller
{
    // Handles GET requests to show the registration form
    public function showRegisterForm()
    {
        log_message('debug', 'Rendering registration form.');
        return view('auth/register'); // Ensure this view exists
    }

    // Handles POST requests to process the registration form
    public function register()
    {
        if (!$this->request->isAJAX() || $this->request->getMethod(true) !== 'POST') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request method.'
            ])->setStatusCode(405);
        }

        $data = $this->request->getJSON(true);

        // Remove CSRF token from data
        $csrfName = csrf_token();
        if (isset($data[$csrfName])) {
            unset($data[$csrfName]);
        }

        if (!isset($data['username'], $data['email'], $data['password'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Missing required fields.'
            ])->setStatusCode(400);
        }

        $userModel = new \App\Models\UserModel();

        if ($userModel->createUser($data)) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Registration successful.'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to create user.'
            ])->setStatusCode(500);
        }
    }

    public function showLoginForm()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/posts'); // Redirect logged-in users to posts
        }

        return view('auth/login'); // Render the login page for unauthenticated users
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

        // Store session or token
        session()->set([
            'user_id' => $user['id'],
            'username' => $user['username'],
            'isLoggedIn' => true
        ]);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Login successful',
            'csrfToken' => csrf_hash() // optional
        ]);
    }

    public function logout()
    {
        session()->destroy(); // Clear all session data
        return redirect()->to('/auth/login')->with('success', 'You have been logged out.');
    }

    public function jsonResponse($data)
    {
        return $this->response->setJSON($data)->setHeader('Content-Type', 'application/json');
    }
}