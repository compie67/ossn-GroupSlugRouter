<?php
/**
 * GroupSlugRouter - Helper functies
 * 🇳🇱 Voor het genereren en ophalen van slug-URLs voor groepen.
 * 🇬🇧 Helper functions to generate and retrieve slug URLs for OSSN groups.
 *
 * Auteur: Eric Redegeld
 */

error_log("[SLUG] ✅ helpers/slug.php is geladen");

/**
 * 🇳🇱 Zoek een groep op basis van een slug (entity-waarde)
 * 🇬🇧 Look up a group using a slug (entity value)
 *
 * @param string $slug
 * @return OssnGroup|false
 */
function groupslugrouter_get_group_by_slug($slug) {
    $params = [
        'type'    => 'object',
        'subtype' => 'groupslugname',
        'value'   => $slug,
        'limit'   => 1,
    ];
    $entities = ossn_get_entities($params);

    if ($entities && isset($entities[0])) {
        $entity = $entities[0];
        error_log("[SLUG] ✅ Slug '{$slug}' gevonden met owner_guid: {$entity->owner_guid}");
        return (object) ['guid' => $entity->owner_guid]; // Direct redirect gebruiken
    }

    error_log("[SLUG] ❌ Geen groep gevonden voor slug '{$slug}' (via metadata)");
    return false;
}


/**
 * 🇳🇱 Genereer een slug uit de titel van een groep en sla deze op
 * 🇬🇧 Generate a slug from group title and store it
 *
 * @param OssnGroup $group
 * @return string|false
 */
function groupslugrouter_generate_slug($group) {
    error_log("[SLUG] ✳️ Slug genereren voor groep: {$group->guid} - {$group->title}");

    if (!isset($group->guid) || !isset($group->title)) {
        error_log("[SLUG] ❌ Ontbrekende groep info.");
        return false;
    }

    // 🧼 Verwijder bestaande slug-entities voor deze groep
$existing_slugs = ossn_get_entities([
    'type' => 'object',
    'subtype' => 'groupslugname',
    'owner_guid' => $group->guid,
    'page_limit' => false,
]);

$entity_handler = new OssnEntities;

if ($existing_slugs) {
    foreach ($existing_slugs as $old_slug) {
        if ($entity_handler->deleteEntity($old_slug->guid)) {
            error_log("[SLUG] 🔁 Oude slug verwijderd: {$old_slug->value} (entity: {$old_slug->guid})");
        } else {
            error_log("[SLUG] ⚠️ Kon oude slug niet verwijderen: entity {$old_slug->guid}");
        }
    }
}


    // Slug genereren
    $base = strtolower(trim($group->title));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $base);
    $slug = trim($slug, '-');

    if (empty($slug)) {
        $slug = 'groep-' . $group->guid;
    }

    // Slug uniek maken (indien nodig met -1, -2, enz.)
    $original_slug = $slug;
    $suffix = 1;
    while (true) {
        $existing = groupslugrouter_get_group_by_slug($slug);
        if (!$existing || $existing->guid === $group->guid) {
            break;
        }
        $slug = $original_slug . '-' . $suffix;
        $suffix++;
    }

    $entityParams = [
        'owner_guid' => $group->guid,
        'type'       => 'object',
        'subtype'    => 'groupslugname',
        'value'      => $slug,
    ];

    error_log("[SLUG] 📎 Slug opslaan via ossn_add_entity: " . var_export($entityParams, true));

    $result = ossn_add_entity($entityParams);
    if ($result) {
        error_log("[SLUG] ✅ Slug opgeslagen: {$slug} voor groep {$group->guid}");
        return $slug;
    } else {
        error_log("[SLUG] ❌ Slug kon niet opgeslagen worden.");
        return false;
    }
}
