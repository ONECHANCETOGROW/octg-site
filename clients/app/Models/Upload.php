<?php
class Upload extends Model {
    public function create($data) {
        $sql = "INSERT INTO uploads (audit_id, client_id, file_path, original_name, mime_type, size, checksum, status) 
                VALUES (:audit_id, :client_id, :file_path, :original_name, :mime_type, :size, :checksum, :status)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($data);
        return $this->db->lastInsertId();
    }
    
    public function getByAuditId($auditId) {
        $stmt = $this->db->prepare("SELECT * FROM uploads WHERE audit_id = ? ORDER BY created_at ASC");
        $stmt->execute([$auditId]);
        return $stmt->fetchAll();
    }
    
    public function findByChecksumAndAudit($checksum, $auditId) {
        $stmt = $this->db->prepare("SELECT * FROM uploads WHERE checksum = ? AND audit_id = ?");
        $stmt->execute([$checksum, $auditId]);
        return $stmt->fetch();
    }
}
