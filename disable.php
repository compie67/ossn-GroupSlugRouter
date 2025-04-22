<?php
/**
 * disable.php - Bij het uitschakelen van de module worden slugs verwijderd.
 */

require_once __DIR__ . '/helpers/slug.php';
error_log('[SLUG] 🧹 Module uitgeschakeld, slugs worden verwijderd...');

$slugs = ossn_get_entities([
    'type'       => 'object',
    'subtype'    => 'groupslugname',
    'page_limit' => false,
]);

if ($slugs && is_array($slugs)) {
    $entity = new OssnEntities;
    foreach ($slugs as $slug) {
        if ($entity->deleteEntity($slug->guid)) {
            error_log("[SLUG] 🗑️ Slug verwijderd: {$slug->value} (entity: {$slug->guid})");
        } else {
            error_log("[SLUG] ⚠️ Kon slug niet verwijderen: {$slug->guid}");
        }
    }
    error_log('[SLUG] ✅ Alle slug-entities verwijderd.');
} else {
    error_log('[SLUG] ℹ️ Geen slug-entities gevonden om te verwijderen.');
}
