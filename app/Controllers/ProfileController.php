<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PostModel;
use App\Models\UserModel;

class ProfileController extends BaseController
{
    public function index()
    {
        $postModel = new PostModel();
        $userModel = new UserModel();
        $userId = session()->get('user_id');

        $user = !empty($userId)
            ? $userModel->findUserById((int) $userId)
            : null;

        $posts = [];

        if (!empty($userId)) {
            $posts = $postModel->getPostsByUserId((int) $userId);

            foreach ($posts as &$post) {
                $post['images'] = json_decode($post['images'] ?? '[]', true) ?? [];
            }
        }

        return view('profile', [
            'user_id' => $user['id'] ?? '',
            'username' => $user['username'] ?? '',
            'email' => $user['email'] ?? '',
            'profile_image' => $user['profile_image'] ?? '',
            'posts' => $posts,
        ]);
    }

    public function settings()
    {
        $userModel = new UserModel();
        $userId = session()->get('user_id');
        $user = !empty($userId)
            ? $userModel->findUserById((int) $userId)
            : null;

        return view('profile_settings', [
            'username' => $user['username'] ?? '',
            'email' => $user['email'] ?? '',
            'profile_image' => $user['profile_image'] ?? '',
        ]);
    }

    public function updatePhoto()
    {
        $userId = session()->get('user_id');
        $userModel = new UserModel();
        $user = !empty($userId) ? $userModel->findUserById((int) $userId) : null;
        $file = $this->request->getFile('profile_image');

        if (!$user || !$file || !$file->isValid()) {
            return redirect()->to('/profile/settings')->with('error', 'Neizdevās augšupielādēt profila bildi.');
        }

        if (!str_starts_with((string) $file->getMimeType(), 'image/')) {
            return redirect()->to('/profile/settings')->with('error', 'Lūdzu, izvēlies attēla failu.');
        }

        $uploadPath = FCPATH . 'uploads/profile';

        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0775, true);
        }

        $newName = $file->getRandomName();
        $file->move($uploadPath, $newName);
        $profileImage = 'uploads/profile/' . $newName;

        if (!empty($user['profile_image'])) {
            $oldPath = FCPATH . ltrim($user['profile_image'], '/');

            if (is_file($oldPath)) {
                unlink($oldPath);
            }
        }

        $userModel->updateUser((int) $userId, [
            'profile_image' => $profileImage,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/profile/settings')->with('success', 'Profila bilde veiksmīgi atjaunināta.');
    }

    public function updatePassword()
    {
        $userId = session()->get('user_id');
        $userModel = new UserModel();
        $user = !empty($userId) ? $userModel->findUserById((int) $userId) : null;
        $currentPassword = (string) $this->request->getPost('current_password');
        $newPassword = (string) $this->request->getPost('new_password');
        $confirmPassword = (string) $this->request->getPost('confirm_password');

        if (!$user) {
            return redirect()->to('/profile/settings')->with('error', 'Lietotājs nav atrasts.');
        }

        if (!password_verify($currentPassword, $user['password'])) {
            return redirect()->to('/profile/settings')->with('error', 'Pašreizējā parole nav pareiza.');
        }

        if (strlen($newPassword) < 8) {
            return redirect()->to('/profile/settings')->with('error', 'Jaunajai parolei jābūt vismaz 8 rakstzīmes garai.');
        }

        if ($newPassword !== $confirmPassword) {
            return redirect()->to('/profile/settings')->with('error', 'Jaunās paroles nesakrīt.');
        }

        $userModel->updateUser((int) $userId, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/profile/settings')->with('success', 'Parole veiksmīgi nomainīta.');
    }

    public function updateEmail()
    {
        $userId = session()->get('user_id');
        $userModel = new UserModel();
        $user = !empty($userId) ? $userModel->findUserById((int) $userId) : null;
        $email = trim((string) $this->request->getPost('email'));

        if (!$user) {
            return redirect()->to('/profile/settings')->with('error', 'Lietotājs nav atrasts.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->to('/profile/settings')->with('error', 'Lūdzu, ievadi derīgu e-pasta adresi.');
        }

        $userModel->updateUser((int) $userId, [
            'email' => $email,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/profile/settings')->with('success', 'E-pasts veiksmīgi nomainīts.');
    }
}
