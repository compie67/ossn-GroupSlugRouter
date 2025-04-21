<?php
/**
 * disable.php - GroupSlugRouter
 * 🇳🇱 Bij het uitschakelen van de module worden vanity slugs opgeruimd.
 * 🇬🇧 When disabling the module, vanity slugs are cleaned up.
 */

error_log('[SLUG] 🧹 Module uitgeschakeld, slugs worden verwijderd...');

// 🇳🇱 Zoek alle slug-entities op basis van subtype
// 🇬🇧 Find all slug entities based on their subtype
$slugs = ossn_get_entities([
    'type'       => 'object',
    'subtype'    => 'groupslugname',
    'page_limit' => false,
]);

if ($slugs && is_array($slugs)) {
    foreach ($slugs as $slug_entity) {
        $entity_obj = new OssnEntities;
        if ($entity_obj->deleteEntity($slug_entity->guid)) {
            error_log("[SLUG] 🗑️ Slug verwijderd: {$slug_entity->value} (entity: {$slug_entity->guid})");
        } else {
            error_log("[SLUG] ⚠️ Kon slug niet verwijderen: {$slug_entity->guid}");
        }
    }
    error_log('[SLUG] ✅ Alle slug-entities verwijderd.');
} else {
    error_log('[SLUG] ℹ️ Geen slug-entities gevonden om te verwijderen.');
}
