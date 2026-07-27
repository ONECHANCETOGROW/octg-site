<?php

declare(strict_types=1);

/**
 * Registry model representing the available system modules (Google Ads, SEO, GBP, etc.)
 */
class PortalModule extends Model
{
    private static array $cache = [];

    public function getAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM portal_modules ORDER BY sort_order ASC");
        return $stmt->fetchAll();
    }

    public function getActive(): array
    {
        $stmt = $this->db->query("SELECT * FROM portal_modules WHERE is_enabled = 1 ORDER BY sort_order ASC");
        return $stmt->fetchAll();
    }

    public function getBySlug(string $slug): ?array
    {
        if (isset(self::$cache[$slug])) {
            return self::$cache[$slug];
        }

        $stmt = $this->db->prepare("SELECT * FROM portal_modules WHERE slug = ?");
        $stmt->execute([$slug]);
        $res = $stmt->fetch();
        if ($res) {
            self::$cache[$slug] = $res;
            return $res;
        }
        return null;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM portal_modules WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function getIdBySlug(string $slug): int
    {
        $mod = $this->getBySlug($slug);
        return $mod ? (int) $mod['id'] : 0;
    }
}
