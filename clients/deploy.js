require('dotenv').config();
const ftp = require('basic-ftp');
const path = require('path');

const filesToUpload = [
    // Root files
    { local: "index.php", remote: "index.php" },
    { local: "migrate.php", remote: "migrate.php" },
    { local: "db_diagnostic.php", remote: "db_diagnostic.php" },
    { local: ".env", remote: ".env" },
    
    // Core & Models
    { local: "app/Core/PlatformIconHelper.php", remote: "app/Core/PlatformIconHelper.php" },
    { local: "app/Models/PortalModule.php", remote: "app/Models/PortalModule.php" },
    { local: "app/Models/ClientPortalScore.php", remote: "app/Models/ClientPortalScore.php" },
    { local: "app/Models/ClientPortalMetric.php", remote: "app/Models/ClientPortalMetric.php" },
    { local: "app/Models/ClientPortalRecommendation.php", remote: "app/Models/ClientPortalRecommendation.php" },
    
    // Controllers
    { local: "app/Controllers/MarketingWorkspaceController.php", remote: "app/Controllers/MarketingWorkspaceController.php" },
    { local: "app/Modules/ClientPortal/ModuleFieldDefinitions.php", remote: "app/Modules/ClientPortal/ModuleFieldDefinitions.php" },
    { local: "app/Modules/ClientPortal/ClientDashboardController.php", remote: "app/Modules/ClientPortal/ClientDashboardController.php" },
    { local: "app/Modules/ClientPortal/ManualEntryModuleController.php", remote: "app/Modules/ClientPortal/ManualEntryModuleController.php" },
    { local: "app/Modules/ClientPortal/TimelineController.php", remote: "app/Modules/ClientPortal/TimelineController.php" },
    { local: "app/Modules/MarketingIntel/IntelAuditController.php", remote: "app/Modules/MarketingIntel/IntelAuditController.php" },
    
    // Views
    { local: "app/Views/clients/index.php", remote: "app/Views/clients/index.php" },
    { local: "app/Views/clients/marketing_workspace.php", remote: "app/Views/clients/marketing_workspace.php" },
    { local: "app/Views/portal/layouts/main.php", remote: "app/Views/portal/layouts/main.php" },
    { local: "app/Views/portal/dashboard/index.php", remote: "app/Views/portal/dashboard/index.php" },
    { local: "app/Views/portal/timeline/index.php", remote: "app/Views/portal/timeline/index.php" },
    { local: "app/Views/portal/_shared/metric_module.php", remote: "app/Views/portal/_shared/metric_module.php" },
    { local: "app/Views/portal/social/index.php", remote: "app/Views/portal/social/index.php" },
    
    // Assets & CSS
    { local: "public/assets/css/app.css", remote: "public/assets/css/app.css" },
    
    // SVG platform icons
    { local: "public/assets/platform-icons/google-ads.svg", remote: "public/assets/platform-icons/google-ads.svg" },
    { local: "public/assets/platform-icons/google-business-profile.svg", remote: "public/assets/platform-icons/google-business-profile.svg" },
    { local: "public/assets/platform-icons/google-search.svg", remote: "public/assets/platform-icons/google-search.svg" },
    { local: "public/assets/platform-icons/facebook.svg", remote: "public/assets/platform-icons/facebook.svg" },
    { local: "public/assets/platform-icons/instagram.svg", remote: "public/assets/platform-icons/instagram.svg" },
    { local: "public/assets/platform-icons/linkedin.svg", remote: "public/assets/platform-icons/linkedin.svg" },
    { local: "public/assets/platform-icons/analytics.svg", remote: "public/assets/platform-icons/analytics.svg" },
    { local: "public/assets/platform-icons/seo.svg", remote: "public/assets/platform-icons/seo.svg" },
    { local: "public/assets/platform-icons/website.svg", remote: "public/assets/platform-icons/website.svg" },
    { local: "public/assets/platform-icons/backlinks.svg", remote: "public/assets/platform-icons/backlinks.svg" },
    
    // Migrations
    { local: "app/Database/Migrations/042_create_portal_modules_registry.sql", remote: "app/Database/Migrations/042_create_portal_modules_registry.sql" },
    { local: "app/Database/Migrations/043_create_client_portal_scores_table.sql", remote: "app/Database/Migrations/043_create_client_portal_scores_table.sql" },
    { local: "app/Database/Migrations/044_rebuild_metrics_and_recommendations.sql", remote: "app/Database/Migrations/044_rebuild_metrics_and_recommendations.sql" },
    { local: "app/Database/Migrations/045_create_client_portal_notes_table.sql", remote: "app/Database/Migrations/045_create_client_portal_notes_table.sql" }
];

async function deploy() {
    const client = new ftp.Client();
    client.ftp.verbose = true;
    
    try {
        console.log("Connecting to FTP...");
        await client.access({
            host: process.env.FTP_HOST,
            user: process.env.FTP_USER,
            password: process.env.FTP_PASSWORD,
            secure: false
        });
        
        console.log("Connected! Resetting working directory to /public_html/clients...");
        await client.cd("/public_html/clients");
        
        for (const file of filesToUpload) {
            const localPath = path.join(__dirname, file.local);
            const remotePath = file.remote;
            
            // Ensure remote directory exists
            const remoteDir = path.dirname(remotePath).replace(/\\/g, '/');
            if (remoteDir !== '.') {
                await client.cd("/public_html/clients");
                await client.ensureDir(remoteDir);
            }
            
            console.log(`Uploading ${file.local} -> ${file.remote}...`);
            await client.cd("/public_html/clients");
            await client.uploadFrom(localPath, remotePath);
        }
        
        console.log("Deployment Successful!");
    }
    catch(err) {
        console.log("Deployment Error:", err);
    }
    client.close();
}

deploy();
