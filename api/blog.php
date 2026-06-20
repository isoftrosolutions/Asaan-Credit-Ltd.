<?php
require __DIR__ . '/../config/bootstrap.php';
cors_headers();

$path = $_GET['_path'] ?? '';
$slug = '';

if (preg_match('#^/api/blog/([a-z0-9-]+)$#', $path, $m)) {
    $slug = $m[1];
}

if ($slug !== '') {
    $stmt = db()->prepare("SELECT * FROM blog_posts WHERE slug = ? AND status='published' LIMIT 1");
    $stmt->execute([$slug]);
    $post = $stmt->fetch();
    if (!$post) {
        json_error('Blog post not found.', 404);
    }
    json_success($post);
}

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = min(20, max(1, (int)($_GET['per_page'] ?? 12)));

$countStmt = db()->query("SELECT COUNT(*) FROM blog_posts WHERE status='published'");
$meta = api_paginate($countStmt, $page, $perPage);

$stmt = db()->prepare("SELECT id, title, slug, excerpt, author, published_at FROM blog_posts WHERE status='published' ORDER BY published_at DESC, id DESC LIMIT {$meta['per_page']} OFFSET {$meta['offset']}");
$stmt->execute();
$posts = $stmt->fetchAll();

json_success($posts, $meta);
