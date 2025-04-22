<?php
/**
 * GroupSlugRouter - Helper functies
 * 🇳🇱 Voor het genereren en ophalen van slug-URLs voor groepen.
 * 🇬🇧 Helper functions to generate and retrieve slug URLs for OSSN groups.
 */

error_log("[SLUG] ✅ helpers/slug.php is geladen");

/**
 * 🔍 Zoek het owner_guid van een groep op basis van een slug
 *
 * @param string $slug
 * @return int|false - Geeft de owner_guid terug, of false bij niet gevonden
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
        $guid = (int)$entities[0]->owner_guid;
        error_log("[SLUG] ✅ Slug '{$slug}' gevonden met owner_guid: {$guid}");
        return $guid;
    }

    error_log("[SLUG] ❌ Geen groep gevonden voor slug '{$slug}' (via metadata)");
    return false;
}

/**
 * 🔧 Genereer een slug en sla deze veilig op
 *
 * @param OssnGroup $group
 * @return string|false - Slug bij succes, false bij mislukking
 */
function groupslugrouter_generate_slug($group) {
    error_log("[SLUG] ✳️ Slug genereren voor groep: {$group->guid} - {$group->title}");

    if (!isset($group->guid) || !isset($group->title)) {
        error_log("[SLUG] ❌ Ontbrekende groep info.");
        return false;
    }

    // Verwijder bestaande slug-entities
    $existing = ossn_get_entities([
        'type' => 'object',
        'subtype' => 'groupslugname',
        'owner_guid' => $group->guid,
        'page_limit' => false,
    ]);
    $handler = new OssnEntities;
    if ($existing) {
        foreach ($existing as $slug) {
            $handler->deleteEntity($slug->guid);
            error_log("[SLUG] 🔁 Oude slug verwijderd: {$slug->value} (entity: {$slug->guid})");
        }
    }

    // Slug maken
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', $group->title), '-'));
    if (empty($slug)) {
        $slug = 'groep-' . $group->guid;
    }

    // Uniek maken
    $original = $slug;
    $suffix = 1;
    while (true) {
        $existing_guid = groupslugrouter_get_group_by_slug($slug);
        if (!$existing_guid || $existing_guid === (int)$group->guid) {
            break;
        }
        $slug = $original . '-' . $suffix++;
    }

    $params = [
        'owner_guid' => $group->guid,
        'type'       => 'object',
        'subtype'    => 'groupslugname',
        'value'      => $slug,
    ];
    error_log("[SLUG] 📎 Slug opslaan via ossn_add_entity: " . var_export($params, true));

    $saved = ossn_add_entity($params);
    if ($saved) {
        error_log("[SLUG] ✅ Slug opgeslagen: {$slug} voor groep {$group->guid}");
        return $slug;
    }

    error_log("[SLUG] ❌ Slug kon niet opgeslagen worden.");
    return false;
}
