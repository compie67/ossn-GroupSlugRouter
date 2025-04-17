<?php
/**
 * GroupSlugRouter - Helper functions
 * Author: Eric Redegeld
 * Description: Generate and lookup group slugs using OSSN entities
 */

error_log("[SLUG] ✅ helpers/slug.php is geladen");

/**
 * Get group by slug (stored as entity)
 *
 * @param string $slug
 * @return OssnGroup|false
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
 * Generate slug from group title and store as metadata
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

    $base = strtolower(trim($group->title));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $base);
    $slug = trim($slug, '-');

    if (empty($slug)) {
        $slug = 'groep-' . $group->guid;
    }

    // Ensure unique slug
    $existing = groupslugrouter_get_group_by_slug($slug);
    if ($existing && $existing->guid !== $group->guid) {
        $slug .= '-' . $group->guid;
    }

    // 🔍 DEBUG parameters logging
    $entityParams = [
        'owner_guid' => $group->guid,
        'type'       => 'group',
        'subtype'    => 'username',
        'value'      => $slug,
    ];
    error_log("[SLUG] 📎 Slug opslaan via ossn_add_entity: " . var_export($entityParams, true));

    // ⛑️ Add entity
    $result = ossn_add_entity($entityParams);

    if ($result) {
        error_log("[SLUG] ✅ Slug opgeslagen: {$slug} voor groep {$group->guid}");
        return $slug;
    } else {
        error_log("[SLUG] ❌ Slug kon niet opgeslagen worden. Mogelijk SQL-fout of ontbrekende rechten.");
        return false;
    }
}
