<?php
function site_document(PDO $pdo, string $key): array {
    $stmt = $pdo->prepare('SELECT content, updated_at FROM site_documents WHERE document_key = ?');
    $stmt->execute([$key]);
    $document = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$document) {
        throw new RuntimeException('The requested site document is not configured.');
    }
    return $document;
}

function render_site_document(string $content): string {
    $content = str_replace(["\r\n", "\r"], "\n", trim($content));
    $blocks = preg_split('/\n{2,}/', $content);
    $html = '';
    foreach ($blocks as $block) {
        $block = trim($block);
        if ($block === '') {
            continue;
        }
        if (preg_match('/^##[ \t]+([^\n]+)(?:\n(.*))?$/us', $block, $matches)) {
            $html .= '<h2>' . h($matches[1]) . '</h2>';
            $body = trim($matches[2] ?? '');
            if ($body !== '') {
                $html .= '<p>' . nl2br(h($body), false) . '</p>';
            }
        } else {
            $html .= '<p>' . nl2br(h($block), false) . '</p>';
        }
    }
    return $html;
}
