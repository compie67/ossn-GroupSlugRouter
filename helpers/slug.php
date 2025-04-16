<?php
/**
 * Helper functies voor de GroupSlugRouter component.
 */

error_log("[SLUG] ✅ helpers/slug.php is geladen");

/**
 * Haalt een groep op basis van de slug die is opgeslagen als metadata.
 *
 * @param string $slug De te zoeken slug.
 * @return OssnGroup|false Het OssnGroup object als het is gevonden, anders false.
 */
function groupslugrouter_get_group_by_slug($slug) {
    $params = [
        'type' => 'group',
        'subtype' => 'username',
        'value' => $slug,
        'limit' => 1,
    ];
    $groups = ossn_get_entities($params);
    if ($groups && isset($groups[0])) {
        error_log("[SLUG] ✅ Groep gevonden voor slug '{$slug}': GUID {$groups[0]->guid} (via metadata)");
        return $groups[0];
    }
    error_log("[SLUG] ❌ Geen groep gevonden voor slug '{$slug}' (via metadata)");
    return false;
}

/**
 * Genereert een unieke slug op basis van de groepstitel en slaat deze op als metadata.
 *
 * @param OssnGroup $group Het OssnGroup object.
 * @return string|false De gegenereerde slug of false bij een fout.
 */
function groupslugrouter_generate_slug($group) {
    error_log("[SLUG] ✳️ Slug genereren voor groep: {$group->guid} - {$group->title}");

    if (!isset($group->guid) || !isset($group->title)) {
        error_log("[SLUG] ❌ Ontbrekende groep info.");
        return false;
    }

    $base = strtolower(trim($group->title));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $base);
    $slug = trim($slug, '-');

    if (empty($slug)) {
        $slug = 'groep-' . $group->guid;
    }

    // Check of slug al bestaat
    $existing = groupslugrouter_get_group_by_slug($slug);
    if ($existing && $existing->guid !== $group->guid) {
        $slug .= '-' . $group->guid;
    }

    $entity = new OssnEntities;
    $entity->owner_guid = $group->guid;
    $entity->type = 'group';
    $entity->subtype = 'username';
    $entity->value = $slug;
    $entity->time_created = time();

    error_log("[SLUG] 📎 Slug opslaan als metadata: " . json_encode([
        'owner_guid' => $entity->owner_guid,
        'type' => $entity->type,
        'subtype' => $entity->subtype,
        'value' => $entity->value
    ]));

    $result = $entity->add();

    if ($result) {
        error_log("[SLUG] ✅ Slug opgeslagen: {$slug} voor groep {$group->guid}");
        return $slug;
    } else {
        error_log("[SLUG] ❌ Slug kon niet opgeslagen worden. SQL fout of ontbrekende data?");
        return false;
    }
}
