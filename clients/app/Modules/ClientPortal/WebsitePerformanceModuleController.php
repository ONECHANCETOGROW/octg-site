<?php

declare(strict_types=1);

namespace App\Modules\ClientPortal;

require_once BASE_PATH . '/app/Modules/ClientPortal/ManualEntryModuleController.php';

final class WebsitePerformanceModuleController extends ManualEntryModuleController
{
    protected string $moduleKey = 'website_performance';
    protected string $viewFolder = 'website_performance';
    protected string $activeMenu = 'website-performance';

    protected function pageTitle(): string
    {
        return 'Website Performance';
    }
}
