<div class="container-fluid" style="padding:24px;">
    <div style="margin-bottom:24px; display:flex; justify-content:space-between; align-items:flex-start;">
        <div>
            <h1 style="font-size:24px; font-weight:700; color:var(--dark); margin:0 0 8px 0;">Validate Extracted Intelligence</h1>
            <p style="color:var(--text-muted); margin:0;">Review the data extracted from the AI response before saving. Low confidence items are highlighted.</p>
        </div>
        <div>
            <a href="/clients/portal-data?id=<?php echo $client['id']; ?>&period=<?php echo $periodStart; ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </div>

    <form method="POST" action="/clients/portal-data/confirm-ai">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">

        <?php foreach($imports as $import): 
            $sec = $import['section'];
            $parsed = json_decode($import['parsed_json'], true) ?: [];
            if (empty($parsed)) continue;
        ?>
            <div class="card" style="margin-bottom:24px; border:1px solid var(--border);">
                <div class="card-header" style="background:#f8fafc; font-weight:700; text-transform:capitalize;">
                    <?php echo str_replace('_', ' ', $sec); ?>
                </div>
                <div class="card-body">
                    <?php if ($sec === 'kpis' || $sec === 'executive_summary'): ?>
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                            <?php foreach ($parsed as $key => $meta): 
                                // Handling array items for executive summary
                                if (isset($meta[0]) && is_array($meta[0])) {
                                    // It's a list (e.g. biggest_wins)
                                    foreach ($meta as $idx => $listItem) {
                                        $val = $listItem['value'] ?? '';
                                        $conf = $listItem['confidence'] ?? 0;
                                        $warning = $conf < 80 ? 'border-color:var(--danger-text); background:#fef2f2;' : '';
                                        echo "<div style='grid-column:1/-1;'>";
                                        echo "<label style='font-size:12px; font-weight:600; text-transform:capitalize;'>".str_replace('_',' ',$key)." #".($idx+1)."</label>";
                                        echo "<div style='display:flex; gap:8px; align-items:center;'>";
                                        echo "<input type='text' name='{$sec}[{$key}][]' value='".htmlspecialchars((string)$val)."' class='form-control' style='flex:1; $warning'>";
                                        if ($conf < 80) echo "<span style='color:var(--danger-text); font-size:12px; font-weight:600;'>⚠ $conf%</span>";
                                        else echo "<span style='color:var(--success); font-size:12px;'>✓ $conf%</span>";
                                        echo "</div>";
                                        echo "<div style='font-size:10px; color:#94a3b8; margin-top:2px;'>Source: ".htmlspecialchars($listItem['source_line'] ?? '')."</div>";
                                        echo "</div>";
                                    }
                                } else {
                                    // It's a scalar value (KPIs or executive_summary string)
                                    $val = $meta['value'] ?? '';
                                    $conf = $meta['confidence'] ?? 0;
                                    $warning = $conf < 80 ? 'border-color:var(--danger-text); background:#fef2f2;' : '';
                                    
                                    // Find existing fact
                                    $existingVal = null;
                                    $entityTypeMap = ['kpis' => 'statistics', 'executive_summary' => 'ai_executive_summary'];
                                    $eType = $entityTypeMap[$sec] ?? $sec;
                                    $eKey = $sec === 'kpis' ? 'kpis' : 'summary';
                                    foreach ($existingFacts as $fact) {
                                        if ($fact['entity_type'] === $eType && $fact['entity_key'] === $eKey && $fact['field_name'] === $key) {
                                            $existingVal = $fact['field_value_text'] ?? $fact['field_value_numeric'];
                                            break;
                                        }
                                    }

                                    echo "<div style='margin-bottom:12px;'>";
                                    echo "<label style='font-size:12px; font-weight:600; text-transform:capitalize;'>".str_replace('_',' ',$key)."</label>";
                                    
                                    if ($existingVal !== null) {
                                        echo "<div style='font-size:11px; color:var(--text-muted); margin-bottom:4px;'><span style='background:#f1f5f9; padding:2px 6px; border-radius:4px; border:1px solid var(--border);'>Existing: " . htmlspecialchars((string)$existingVal) . "</span></div>";
                                    }

                                    echo "<div style='display:flex; gap:8px; align-items:center;'>";
                                    if (strlen((string)$val) > 100) {
                                        echo "<textarea name='{$sec}[{$key}]' class='form-control' rows='3' style='flex:1; $warning'>".htmlspecialchars((string)$val)."</textarea>";
                                    } else {
                                        echo "<input type='text' name='{$sec}[{$key}]' value='".htmlspecialchars((string)$val)."' class='form-control' style='flex:1; $warning'>";
                                    }
                                    if ($conf < 80) echo "<span style='color:var(--danger-text); font-size:12px; font-weight:600;'>⚠ $conf%</span>";
                                    else echo "<span style='color:var(--success); font-size:12px;'>✓ $conf%</span>";
                                    echo "</div>";
                                    echo "<div style='font-size:10px; color:#94a3b8; margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;'>Source: ".htmlspecialchars($meta['source_line'] ?? '')."</div>";
                                    echo "</div>";
                                }
                            ?>
                            <?php endforeach; ?>
                        </div>

                    <?php elseif (in_array($sec, ['campaigns', 'keywords', 'search_terms', 'recommendations', 'opportunities'])): ?>
                        <!-- Tables or Lists of Objects -->
                        <div style="overflow-x:auto;">
                            <table style="width:100%; border-collapse:collapse; font-size:13px; text-align:left;">
                                <thead>
                                    <tr style="border-bottom:2px solid var(--border);">
                                        <?php 
                                        $headers = array_keys($parsed[0] ?? []);
                                        foreach ($headers as $h) {
                                            echo "<th style='padding:8px; text-transform:capitalize;'>".str_replace('_', ' ', $h)."</th>";
                                        }
                                        ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($parsed as $i => $row): ?>
                                        <tr style="border-bottom:1px solid var(--border-light);">
                                            <?php foreach ($headers as $h): 
                                                $meta = $row[$h] ?? [];
                                                $val = $meta['parsed_value'] ?? $meta['value'] ?? '';
                                                $conf = $meta['confidence'] ?? 0;
                                                $warning = $conf < 80 ? 'border-color:var(--danger-text); background:#fef2f2;' : '';
                                            ?>
                                                <td style="padding:8px;">
                                                    <div style="display:flex; flex-direction:column; gap:2px;">
                                                        <div style="display:flex; gap:4px; align-items:center;">
                                                            <input type="text" name="<?php echo $sec; ?>[<?php echo $i; ?>][<?php echo $h; ?>]" value="<?php echo htmlspecialchars((string)$val); ?>" class="form-control" style="width:100%; min-width:120px; font-size:12px; padding:4px; <?php echo $warning; ?>">
                                                            <?php if($conf < 80) echo "<span style='color:var(--danger-text); font-weight:700;' title='Low confidence'>⚠</span>"; ?>
                                                        </div>
                                                        <div style="font-size:9px; color:#94a3b8; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:200px;" title="<?php echo htmlspecialchars($meta['source_line'] ?? ''); ?>">
                                                            <?php echo htmlspecialchars($meta['source_line'] ?? ''); ?>
                                                        </div>
                                                    </div>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div style="position:sticky; bottom:0; background:#fff; padding:16px; border-top:1px solid var(--border); display:flex; justify-content:flex-end; gap:16px; box-shadow:0 -4px 6px -1px rgba(0,0,0,0.05);">
            <a href="/clients/portal-data?id=<?php echo $client['id']; ?>&period=<?php echo $periodStart; ?>" class="btn btn-secondary">Discard & Cancel</a>
            <button type="submit" class="btn btn-primary">Confirm & Process Intelligence</button>
        </div>
    </form>
</div>
