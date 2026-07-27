<?php

declare(strict_types=1);

require_once BASE_PATH . '/app/Models/PortalModule.php';

class ClientPortalScore extends Model
{
    private function resolveModuleId($module): int
    {
        if (is_numeric($module)) {
            return (int) $module;
        }
        $pm = new PortalModule();
        return $pm->getIdBySlug((string) $module);
    }

    public function latest($clientId, $module)
    {
        $moduleId = $this->resolveModuleId($module);
        $stmt = $this->db->prepare(
            "SELECT s.*, pm.slug as module_slug, pm.name as module_name 
             FROM client_portal_scores s
             JOIN portal_modules pm ON pm.id = s.module_id
             WHERE s.client_id = ? AND s.module_id = ?
             ORDER BY s.period_start DESC LIMIT 1"
        );
        $stmt->execute([$clientId, $moduleId]);
        return $stmt->fetch() ?: null;
    }

    public function getForPeriod($clientId, $module, $periodStart)
    {
        $moduleId = $this->resolveModuleId($module);
        $stmt = $this->db->prepare(
            "SELECT * FROM client_portal_scores
             WHERE client_id = ? AND module_id = ? AND period_start = ?"
        );
        $stmt->execute([$clientId, $moduleId, $periodStart]);
        return $stmt->fetch() ?: null;
    }

    public function previous($clientId, $module, $beforePeriodStart)
    {
        $moduleId = $this->resolveModuleId($module);
        $stmt = $this->db->prepare(
            "SELECT * FROM client_portal_scores
             WHERE client_id = ? AND module_id = ? AND period_start < ?
             ORDER BY period_start DESC LIMIT 1"
        );
        $stmt->execute([$clientId, $moduleId, $beforePeriodStart]);
        return $stmt->fetch() ?: null;
    }

    public function history($clientId, $module, $limit = 12)
    {
        $moduleId = $this->resolveModuleId($module);
        $stmt = $this->db->prepare(
            "SELECT * FROM client_portal_scores
             WHERE client_id = ? AND module_id = ?
             ORDER BY period_start DESC LIMIT " . (int) $limit
        );
        $stmt->execute([$clientId, $moduleId]);
        return $stmt->fetchAll();
    }

    public function upsertForPeriod(
        int $clientId,
        $module,
        string $periodStart,
        string $periodEnd,
        int $score,
        ?string $grade,
        ?string $healthStatus,
        ?string $trend,
        ?string $biggestWin,
        ?string $biggestRisk,
        ?string $priorityThisMonth,
        ?int $enteredByUserId
    ): int {
        $moduleId = $this->resolveModuleId($module);
        $existing = $this->getForPeriod($clientId, $moduleId, $periodStart);

        if ($existing) {
            $update = $this->db->prepare(
                "UPDATE client_portal_scores 
                 SET score = ?, grade = ?, health_status = ?, trend = ?, 
                     biggest_win = ?, biggest_risk = ?, priority_this_month = ?, 
                     entered_by_user_id = ?, period_end = ?
                 WHERE id = ?"
            );
            $update->execute([
                $score, $grade, $healthStatus, $trend,
                $biggestWin, $biggestRisk, $priorityThisMonth,
                $enteredByUserId, $periodEnd, $existing['id']
            ]);
            return (int) $existing['id'];
        }

        $insert = $this->db->prepare(
            "INSERT INTO client_portal_scores
                (client_id, module_id, period_start, period_end, score, grade, health_status, 
                 trend, biggest_win, biggest_risk, priority_this_month, entered_by_user_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $insert->execute([
            $clientId, $moduleId, $periodStart, $periodEnd, $score, $grade, $healthStatus,
            $trend, $biggestWin, $biggestRisk, $priorityThisMonth, $enteredByUserId
        ]);
        return (int) $this->db->lastInsertId();
    }
}
