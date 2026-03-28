<?php
/**
 * API: toggle like en un artículo del blog
 * POST /api/blog-like.php
 * Body JSON: { "post_id": 123 }
 * Responde JSON: { "liked": true, "likes": 42 }
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$body = (string)file_get_contents('php://input');
$data = json_decode($body, true);
$postId = isset($data['post_id']) ? (int)$data['post_id'] : 0;

if ($postId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'post_id inválido']);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/ContentRepository.php';

$sessionId = session_id();
$contentRepo = new ContentRepository();

$isLiked = $contentRepo->isLikedBySession($postId, $sessionId);

if ($isLiked) {
    $newCount = $contentRepo->removeLike($postId, $sessionId);
    echo json_encode(['liked' => false, 'likes' => $newCount]);
} else {
    $newCount = $contentRepo->addLike($postId, $sessionId);
    echo json_encode(['liked' => true, 'likes' => $newCount]);
}
