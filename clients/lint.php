<?php
exec('php -l app/Database/MarketingIntelSeeder.php 2>&1', $out);
echo "Seeder:\n" . implode("\n", $out) . "\n\n";

exec('php -l app/Modules/MarketingIntel/ChannelRepository.php 2>&1', $out2);
echo "ChannelRepo:\n" . implode("\n", $out2) . "\n\n";

exec('php -l app/Modules/MarketingIntel/RequirementRepository.php 2>&1', $out3);
echo "RequirementRepo:\n" . implode("\n", $out3) . "\n\n";
