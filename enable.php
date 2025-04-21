<?php
/**
 * enable.php - GroupSlugRouter
 *
 * 🇳🇱 Genereert slug-URLs voor bestaande groepen bij activatie van de module.
 * 🇬🇧 Generates vanity slugs for existing groups when the module is enabled.
 *
 * Auteur: Eric Redegeld
 * Feedback & structuur: ChatGPT & Michael Zülsdorff
 */

require_once dirname(__FILE__) . '/helpers/slug.php';

error_log("[SLUG] ✅ helpers/slug.php is geladen via enable.php");

// 🛑 Controleer op conflict met oude 'UserGroups'-component
// 🔁 Check if 'UserGroups' component is still active (may conflict)
if (class_exists('OssnComponents') && OssnComponents::isActive('UserGroups')) {
    ossn_trigger_message("⚠️ De component 'UserGroups' is actief. Deze kan conflicteren met GroupSlugRouter. Zet 'UserGroups' eerst uit voor correcte werking.", 'error');
    return;
}

error_log("[SLUG] 🚀 Module geactiveerd, controleren op bestaande groepen zonder slug...");

// 🇳🇱 Haal alle object-entities op (inclusief groepen)
// 🇬🇧 Fetch all object entities (some of which may be groups)
$entities = ossn_get_entities([
    'type' => 'object',
    'page_limit' => false,
]) ?: [];

if (!$entities) {
    error_log("[SLUG] ⚠️ Geen object-entities gevonden.");
    return;
}

$gevonden = 0; // 🇳🇱 Aantal gevonden groepen zonder slug

foreach ($entities as $entity) {
    // 🇳🇱 Probeer groep op te halen via GUID (alleen als het echt een groep is)
    // 🇬🇧 Try to load group from entity GUID (only real groups will return)
    $group = ossn_get_group_by_guid($entity->guid);
    if (!$group || empty($group->title)) {
        continue;
    }

    // 🇳🇱 Slug genereren en opslaan
    // 🇬🇧 Generate and store slug
    $slug = groupslugrouter_generate_slug($group);
    if ($slug) {
        error_log("[SLUG] ➕ Slug '{$slug}' gegenereerd voor groep {$group->guid} ({$group->title})");
        $gevonden++;
    } else {
        error_log("[SLUG] ❌ Kon slug niet genereren voor groep {$group->guid} ({$group->title})");
    }
}

// 🇳🇱 Eindmelding
// 🇬🇧 Final log message
if ($gevonden === 0) {
    error_log("[SLUG] ℹ️ Geen nieuwe slugs toegevoegd.");
} else {
    error_log("[SLUG] ✅ {$gevonden} slugs toegevoegd voor bestaande groepen.");
}
