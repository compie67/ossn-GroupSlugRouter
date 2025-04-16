<?php
/**
 * GroupSlugRouter - helpers/slug.php
 * Hulpfuncties voor het genereren en ophalen van slugs op basis van groepsnamen.
 * Slugs worden opgeslagen als metadata met de key 'username'.
 */

error_log("[SLUG] ✅ helpers/slug.php is geladen");

/**
 * Haal een groep op op basis van de slug (username metadata).
 *
 * @param string $slug De slug die je zoekt (bijvoorbeeld 'amsterdam-makers')
 * @return OssnGroup|false De bijbehorende groep of false als niet gevonden
 */
function groupslugrouter_get_group_by_slug($slug) {
    $params = [
        'type' => 'group',
        'metadata_name' => 'username',
        'metadata_value' => $slug,
        'limit' => 1,
    ];
    $groups = ossn_get_entities($params);

    if ($groups && isset($groups[0])) {
        error_log("[SLUG] ✅ Groep gevonden voor slug '{$slug}': GUID {$groups[0]->guid}");
        return $groups[0];
    }

    error_log("[SLUG] ❌ Geen groep gevonden voor slug '{$slug}'");
    return false;
}

/**
 * Genereer een slug van de groepsnaam, en sla deze op als metadata.
 *
 * @param OssnGroup $group De groep waarvoor een slug gegenereerd moet worden
 * @return string|false De slug string, of false bij fout
 */
function groupslugrouter_generate_slug($group) {
    if (!isset($group->guid) || !isset($group->title)) {
        error_log("[SLUG] ❌ Ongeldige groep voor slug generatie");
        return false;
    }

    $base = strtolower(trim($group->title));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $base);
    $slug = trim($slug, '-');

    error_log("[SLUG] ✳️ Slug genereren voor groep: {$group->guid} - {$group->title}");

    // Fallback als slug leeg is
    if (empty($slug)) {
        $slug = 'groep-' . $group->guid;
        error_log("[SLUG] ⚠️ Lege slug, fallback naar: {$slug}");
    }

    // Check of slug al bestaat
    $existing = groupslugrouter_get_group_by_slug($slug);
    if ($existing && $existing->guid != $group->guid) {
        $slug .= '-' . $group->guid;
        error_log("[SLUG] ⚠️ Dubbele slug gevonden, aangepast naar: {$slug}");
    }

    // Slug opslaan als metadata (gebruik standaard OSSN functie)
    $metadata_params = [
        'entity_guid' => $group->guid,
        'name' => 'username',
        'value' => $slug,
    ];

    error_log("[SLUG] 💾 Slug opslaan als metadata: " . json_encode($metadata_params));

    if (ossn_add_metadata($metadata_params)) {
        error_log("[SLUG] ✅ Slug succesvol opgeslagen voor groep {$group->guid}");
        return $slug;
    } else {
        error_log("[SLUG] ❌ Opslaan van slug metadata mislukt");
        return false;
    }
}
