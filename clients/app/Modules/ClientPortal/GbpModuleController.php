<?php

declare(strict_types=1);

namespace App\Modules\ClientPortal;

require_once BASE_PATH . '/app/Modules/ClientPortal/ManualEntryModuleController.php';

final class GbpModuleController extends ManualEntryModuleController
{
    protected string $moduleKey = 'gbp';
    protected string $viewFolder = 'gbp';
    protected string $activeMenu = 'gbp';

    protected function pageTitle(): string
    {
        return 'Google Business Profile';
    }
}
