<?php
class Client extends Model {
    public function getAll() {
        $stmt = $this->db->query("SELECT * FROM clients ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM clients WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getBySlug($slug) {
        $stmt = $this->db->prepare("SELECT * FROM clients WHERE slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    /**
     * Lowercase, hyphenated slug from a business name, disambiguated
     * against existing rows so it's always unique. Mirrors the backfill
     * logic in migration 037.
     */
    public function generateUniqueSlug($businessName, $excludeId = null) {
        $base = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $businessName), '-'));
        if ($base === '') {
            $base = 'client';
        }

        $slug = $base;
        $suffix = 2;
        while (true) {
            $stmt = $this->db->prepare(
                "SELECT id FROM clients WHERE slug = ? AND id != ?"
            );
            $stmt->execute([$slug, $excludeId ?? 0]);
            if (!$stmt->fetch()) {
                return $slug;
            }
            $slug = $base . '-' . $suffix;
            $suffix++;
        }
    }

    public function create($data) {
        $data['slug'] = $data['slug'] ?? $this->generateUniqueSlug($data['business_name']);
        $sql = "INSERT INTO clients (business_name, slug, website, industry, contact_person, email, phone, status)
                VALUES (:business_name, :slug, :website, :industry, :contact_person, :email, :phone, :status)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
    
    public function update($id, $data) {
        $data['id'] = $id;
        $sql = "UPDATE clients SET 
                business_name = :business_name, 
                website = :website, 
                industry = :industry, 
                contact_person = :contact_person, 
                email = :email, 
                phone = :phone, 
                status = :status 
                WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
    
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM clients WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
