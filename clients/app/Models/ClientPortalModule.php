<?php
class ClientPortalModule extends Model {
    const ALL_MODULES = ['dashboard', 'google-ads', 'seo', 'gbp', 'social', 'website-performance', 'reports', 'recommendations', 'timeline'];

    /**
     * @return array<string,bool> module_code => enabled. Every module not
     * present in this map should be treated as enabled (default-on).
     */
    public function disabledFor($clientId) {
        $stmt = $this->db->prepare(
            "SELECT module_code FROM client_portal_modules WHERE client_id = ? AND is_enabled = 0"
        );
        $stmt->execute([$clientId]);
        return array_column($stmt->fetchAll(), 'module_code');
    }

    public function setEnabled($clientId, $moduleCode, $enabled) {
        $stmt = $this->db->prepare(
            "INSERT INTO client_portal_modules (client_id, module_code, is_enabled)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE is_enabled = VALUES(is_enabled)"
        );
        return $stmt->execute([$clientId, $moduleCode, $enabled ? 1 : 0]);
    }
}
