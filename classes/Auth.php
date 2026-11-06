
<?php

class Auth
{
    public static function login(string $identifier, string $password): bool
    {
        $db = Database::getInstance();

        $user = $db->queryOne(
            "SELECT id, role_id, name, username, email, password_hash
             FROM `user`
             WHERE username = ? OR email = ?
             LIMIT 1",
            [$identifier, $identifier]
        );

        if (!$user) {
            return false;
        }

        $isValid = false;

        if (password_verify($password, $user['password_hash'])) {
            $isValid = true;
        } elseif ($user['password_hash'] === $password) {
            $isValid = true;

            $newHash = password_hash($password, PASSWORD_BCRYPT);

            $db->query(
                "UPDATE `user` SET password_hash = ? WHERE id = ?",
                [$newHash, $user['id']]
            );
        }

        if (!$isValid) {
            return false;
        }

        $roles = [
            1 => 'Admin',
            2 => 'PA-KPA',
            3 => 'PPK'
        ];

        $user['role'] = $roles[(int)$user['role_id']] ?? '';

        session_regenerate_id(true);

        unset($user['password_hash']);

        $_SESSION['user'] = $user;

        return true;
    }

    public static function createUser(
        string $name,
        string $username,
        string $email,
        string $password,
        string $role
    ): bool|string {

        $db = Database::getInstance();

        $name = trim($name);
        $username = trim($username);
        $email = trim($email);
        $role = trim($role);

        if ($name === '' || $username === '' || $email === '' || $password === '') {
            return 'Semua kolom wajib diisi.';
        }

        if (!preg_match('/^[a-zA-Z0-9._-]{3,30}$/', $username)) {
            return 'Username harus 3–30 karakter dan hanya boleh berisi huruf, angka, titik, garis bawah, atau tanda hubung.';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Format email tidak valid.';
        }

        if (strlen($password) < 4) {
            return 'Password minimal 4 karakter.';
        }

        $allowedRoles = [
            'Admin' => 1,
            'PA-KPA' => 2,
            'PPK' => 3
        ];

        if (!isset($allowedRoles[$role])) {
            return 'Role pengguna tidak valid.';
        }

        $exist = $db->queryOne(
            "SELECT id FROM `user` WHERE username = ? OR email = ? LIMIT 1",
            [$username, $email]
        );

        if ($exist) {
            return 'Username atau email sudah terdaftar.';
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        try {
            $db->query(
                "INSERT INTO `user`
                (role_id, name, username, email, password_hash)
                VALUES (?, ?, ?, ?, ?)",
                [
                    $allowedRoles[$role],
                    $name,
                    $username,
                    $email,
                    $hashedPassword
                ]
            );

            return true;

        } catch (Throwable $e) {
            return 'Gagal menyimpan pengguna: ' . $e->getMessage();
        }
    }

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    public static function check(): bool
    {
        return !empty($_SESSION['user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function role(): string
    {
        return $_SESSION['user']['role'] ?? '';
    }

    public static function id(): int
    {
        return (int)($_SESSION['user']['id'] ?? 0);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: login.php');
            exit;
        }
    }
}
