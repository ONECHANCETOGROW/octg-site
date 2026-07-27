<?php
/**
 * Client-facing login identity. Deliberately a separate table/model from
 * `User` (staff/agency logins) -- a client and a staff member must never
 * be able to authenticate through the same code path, even by accident.
 * See docs/CLIENT_PORTAL.md "Client Access Model" for the isolation
 * guarantee this table exists to support.
 */
class ClientUser extends Model {
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM client_users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM client_users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function forClient($clientId) {
        $stmt = $this->db->prepare("SELECT * FROM client_users WHERE client_id = ? ORDER BY created_at DESC");
        $stmt->execute([$clientId]);
        return $stmt->fetchAll();
    }

    public function create($clientId, $email, $passwordHash) {
        $stmt = $this->db->prepare(
            "INSERT INTO client_users (client_id, email, password_hash, is_active, must_reset_password)
             VALUES (:client_id, :email, :password_hash, 1, 1)"
        );
        $stmt->execute([
            'client_id' => $clientId,
            'email' => $email,
            'password_hash' => $passwordHash,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function updatePassword($id, $newHash, $mustReset = false) {
        $stmt = $this->db->prepare(
            "UPDATE client_users SET password_hash = :hash, must_reset_password = :must_reset WHERE id = :id"
        );
        return $stmt->execute([
            'hash' => $newHash,
            'must_reset' => $mustReset ? 1 : 0,
            'id' => $id,
        ]);
    }

    public function setActive($id, $isActive) {
        $stmt = $this->db->prepare("UPDATE client_users SET is_active = ? WHERE id = ?");
        return $stmt->execute([$isActive ? 1 : 0, $id]);
    }

    public function updateEmail($id, $newEmail) {
        $stmt = $this->db->prepare("UPDATE client_users SET email = ? WHERE id = ?");
        return $stmt->execute([$newEmail, $id]);
    }

    public function recordLogin($id) {
        $stmt = $this->db->prepare("UPDATE client_users SET last_login_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function createPasswordReset($clientUserId, $tokenHash, $expiresAt) {
        $stmt = $this->db->prepare(
            "INSERT INTO client_password_resets (client_user_id, token_hash, expires_at) VALUES (?, ?, ?)"
        );
        return $stmt->execute([$clientUserId, $tokenHash, $expiresAt]);
    }

    public function findValidReset($tokenHash) {
        $stmt = $this->db->prepare(
            "SELECT * FROM client_password_resets
             WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW()"
        );
        $stmt->execute([$tokenHash]);
        return $stmt->fetch();
    }

    public function consumeReset($id) {
        $stmt = $this->db->prepare("UPDATE client_password_resets SET used_at = NOW() WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
