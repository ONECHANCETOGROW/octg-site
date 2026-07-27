<?php

declare(strict_types=1);

namespace App\Modules\ClientPortal;

use App\Core\DbAdapter;

require_once BASE_PATH . '/app/Services/Storage/Storage.php';

/**
 * Shared read access to "this client's completed intelligence reports",
 * reused by the Executive Summary dashboard, the Google Ads module, and
 * the Reports library -- one query/one contract-reading path so the three
 * screens can't silently drift into showing different numbers for the
 * same audit. Mirrors ReportController::index()'s old-system/mi-system
 * UNION, but scoped to a single client_id (the isolation guarantee --
 * every method here takes clientId as a mandatory first argument).
 */
final class ClientReportAccess
{
    public function __construct(private readonly DbAdapter $db)
    {
    }

    /**
     * @return array<int,array<string,mixed>> newest first
     */
    public function completedReportsForClient(int $clientId, ?string $channel = null): array
    {
        $old = $this->db->all(
            "SELECT a.id, a.name AS title, a.channel, a.status, a.client_id, a.created_at, 'old' AS audit_type
             FROM audits a
             WHERE a.client_id = ? AND a.status = 'completed'",
            [$clientId]
        );

        $mi = $this->db->all(
            "SELECT a.id, a.title, 'google_ads' AS channel, a.status, a.client_id, a.created_at, 'mi' AS audit_type
             FROM mi_audits a
             WHERE a.client_id = ? AND a.status = 'completed'",
            [$clientId]
        );

        $reports = array_merge($old, $mi);
        usort($reports, static fn (array $a, array $b) => strtotime((string) $b['created_at']) <=> strtotime((string) $a['created_at']));

        if ($channel !== null) {
            $reports = array_values(array_filter(
                $reports,
                static fn (array $r) => strtolower((string) $r['channel']) === strtolower($channel)
            ));
        }

        return $reports;
    }

    /**
     * @return array<string,mixed>|null decoded 09-contract/intelligence.json
     */
    public function contractFor(int $clientId, int $auditId): ?array
    {
        $contractPath = "clients/{$clientId}/{$auditId}/09-contract/intelligence.json";
        if (!\Storage::exists($contractPath)) {
            return null;
        }

        $decoded = json_decode((string) \Storage::read($contractPath), true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * The most recent report AND its contract in one call -- what the
     * Executive Summary / Google Ads modules actually need.
     *
     * @return array{report:array<string,mixed>,contract:array<string,mixed>}|null
     */
    public function latestWithContract(int $clientId, ?string $channel = null): ?array
    {
        foreach ($this->completedReportsForClient($clientId, $channel) as $report) {
            $contract = $this->contractFor($clientId, (int) $report['id']);
            if ($contract !== null) {
                return ['report' => $report, 'contract' => $contract];
            }
        }
        return null;
    }

    /**
     * Second-most-recent report+contract, for month-over-month
     * comparisons. Null if there's nothing to compare against yet --
     * callers must handle that honestly (no trend) rather than fabricate
     * a delta from one data point.
     *
     * @return array{report:array<string,mixed>,contract:array<string,mixed>}|null
     */
    public function previousWithContract(int $clientId, ?string $channel = null): ?array
    {
        $reports = $this->completedReportsForClient($clientId, $channel);
        for ($i = 1, $n = count($reports); $i < $n; $i++) {
            $contract = $this->contractFor($clientId, (int) $reports[$i]['id']);
            if ($contract !== null) {
                return ['report' => $reports[$i], 'contract' => $contract];
            }
        }
        return null;
    }
}
