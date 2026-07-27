<?php
class User extends Model {
    public function findByEmailOrUsername($identifier) {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email OR username = :username');
        $stmt->execute(['email' => $identifier, 'username' => $identifier]);
        return $stmt->fetch();
    }
    
    public function updatePassword($userId, $newHash) {
        $stmt = $this->db->prepare('UPDATE users SET password_hash = :hash, force_password_change = 0 WHERE id = :id');
        return $stmt->execute(['hash' => $newHash, 'id' => $userId]);
    }
}
