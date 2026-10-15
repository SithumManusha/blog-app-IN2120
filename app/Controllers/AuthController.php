<?php
namespace App\Controllers;

use App\Helpers\Auth;
use App\Helpers\Csrf;
use App\Models\User;

class AuthController {
    public function showLogin(?string $error = null): void {
        if (Auth::check()) {
            header('Location: ?route=home');
            exit;
        }
        require __DIR__ . '/../../views/auth/login.php';
    }

    public function showRegister(?string $error = null): void {
        if (Auth::check()) {
            header('Location: ?route=home');
            exit;
        }
        require __DIR__ . '/../../views/auth/register.php';
    }

    public function login(): void {
        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            $this->showLogin('Security token expired. Please try again.');
            return;
        }

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $this->showLogin('Please fill in all required fields.');
            return;
        }

        $user = User::findByEmail($email);
        if (!$user || !password_verify($password, $user['password'])) {
            $this->showLogin('Invalid email or password.');
            return;
        }

        Auth::login($user);
        header('Location: ?route=home&msg=welcome');
        exit;
    }

    public function register(): void {
        if (!Csrf::validate($_POST['csrf_token'] ?? null)) {
            $this->showRegister('Security token expired. Please try again.');
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (strlen($username) < 3 || strlen($password) < 6 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->showRegister('Please provide a valid email, username (min 3 chars), and password (min 6 chars).');
            return;
        }

        if (User::findByEmail($email)) {
            $this->showRegister('An account with this email already exists.');
            return;
        }

        if (User::findByUsername($username)) {
            $this->showRegister('This username is already taken. Please choose another.');
            return;
        }

        $userId = User::create($username, $email, $password);
        $user = User::findById($userId);
        Auth::login($user);

        header('Location: ?route=home&msg=registered');
        exit;
    }

    public function logout(): void {
        Auth::logout();
        header('Location: ?route=login&msg=logged_out');
        exit;
    }
}
