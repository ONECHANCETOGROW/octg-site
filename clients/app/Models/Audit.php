<?php
class Audit extends Model {
    public function create($data) {
        $sql = "INSERT INTO audits (client_id, name, audit_month, channel, notes, status) 
                VALUES (:client_id, :name, :audit_month, :channel, :notes, :status)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT a.*, c.business_name FROM audits a JOIN clients c ON a.client_id = c.id WHERE a.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getByClientId($clientId) {
        $stmt = $this->db->prepare("SELECT * FROM audits WHERE client_id = ? ORDER BY created_at DESC");
        $stmt->execute([$clientId]);
        return $stmt->fetchAll();
    }
    
    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE audits SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
}
