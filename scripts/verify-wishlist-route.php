<?php

$root = dirname(__DIR__);
$routesPath = $root . DIRECTORY_SEPARATOR . 'backend' . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'api.php';
$collectionPath = $root . DIRECTORY_SEPARATOR . 'Eyewear-System.postman_collection.json';

$routes = file_get_contents($routesPath);
if ($routes === false) {
    fwrite(STDERR, "Unable to read backend routes.\n");
    exit(1);
}

$collectionRaw = file_get_contents($collectionPath);
if ($collectionRaw === false) {
    fwrite(STDERR, "Unable to read Postman collection.\n");
    exit(1);
}

$collection = json_decode($collectionRaw, true);
if (!is_array($collection)) {
    fwrite(STDERR, "Postman collection is not valid JSON.\n");
    exit(1);
}

if (!str_contains($routes, "Router::group(['prefix' => 'wishlist'], function ()")) {
    fwrite(STDERR, "Wishlist routes must require only authentication, not manage_cart permission.\n");
    exit(1);
}

if (!str_contains($routes, "Router::delete('{id}', [WishlistController::class, 'destroy'])")) {
    fwrite(STDERR, "Missing wishlist delete route.\n");
    exit(1);
}

$removeRequest = null;
$stack = $collection['item'] ?? [];
while ($stack) {
    $item = array_shift($stack);
    if (($item['name'] ?? '') === 'Remove Wishlist Item') {
        $removeRequest = $item['request'] ?? null;
        break;
    }
    foreach (($item['item'] ?? []) as $child) {
        $stack[] = $child;
    }
}

if (!$removeRequest) {
    fwrite(STDERR, "Postman request 'Remove Wishlist Item' was not found.\n");
    exit(1);
}

if (($removeRequest['method'] ?? '') !== 'DELETE') {
    fwrite(STDERR, "Remove Wishlist Item must use DELETE.\n");
    exit(1);
}

if (($removeRequest['url'] ?? '') !== '{{baseUrl}}/wishlist/{{productId}}') {
    fwrite(STDERR, "Remove Wishlist Item URL must be {{baseUrl}}/wishlist/{{productId}}.\n");
    exit(1);
}

echo "Remove Wishlist Item route and collection mapping are valid.\n";
