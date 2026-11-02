<?php
$siteBasePath = str_replace('\\', '/', dirname((string)($_SERVER['SCRIPT_NAME'] ?? '/')));
if (basename($siteBasePath) === 'admin') {
    $siteBasePath = dirname($siteBasePath);
}
$siteBasePath = rtrim($siteBasePath, '/.');
$siteHomeUrl = htmlspecialchars($siteBasePath . '/index.php', ENT_QUOTES, 'UTF-8');
$siteLogoUrl = htmlspecialchars($siteBasePath . '/images/Gemini_Generated_Image_ku7bpvku7bpvku7b.jpg', ENT_QUOTES, 'UTF-8');
?>
<header class="site-brand-header">
    <a class="brand" href="<?= $siteHomeUrl ?>" aria-label="Sora Living ホーム">
        <img src="<?= $siteLogoUrl ?>" alt="Sora Living">
    </a>
</header>
