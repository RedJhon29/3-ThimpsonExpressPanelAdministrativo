<?php
class loginModel {
    private static $users = [
        [
            'id' => 1,
            'name' => 'Allan Thimpson',
            'email' => 'allan@thimpsonexpress.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password: "password"
            'role' => 'Super Admin',
            'status' => 'active',
            'last_login' => '2026-09-18 08:00'
        ],
        [
            'id' => 2,
            'name' => 'Operador 1',
            'email' => 'operador1@thimpsonexpress.com',
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password: "password"
            'role' => 'Operador',
            'status' => 'active',
            'last_login' => '2026-09-17 14:30'
        ],
    ];

    public static function all() { return self::$users; }

    public static function findByEmail($email) {
        foreach (self::$users as $u) {
            if ($u['email'] === $email) return $u;
        }
        return null;
    }

    public static function findById($id) {
        foreach (self::$users as $u) {
            if ($u['id'] == $id) return $u;
        }
        return null;
    }

    public static function verifyPassword($email, $password) {
        $user = self::findByEmail($email);
        if (!$user) return false;
        return password_verify($password, $user['password']);
    }

    public static function updateLastLogin($id) {
        foreach (self::$users as &$u) {
            if ($u['id'] == $id) {
                $u['last_login'] = date('Y-m-d H:i:s');
                break;
            }
        }
    }
}