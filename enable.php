<?php
/**
 * enable.php - Wordt uitgevoerd bij activeren van de component.
 */

require_once __DIR__ . '/helpers/slug.php';
error_log("[SLUG] ✅ helpers/slug.php is geladen via enable.php");
error_log("[SLUG] 🚀 Module geactiveerd, controleren op bestaande groepen zonder slug...");

$object = new OssnObject;
$groepen = $object->searchObject([
    'types'      => 'user', // zoals OSSN standaard doet
    'subtype'    => 'ossngroup',
    'page_limit' => false,
]);

$slugCount = 0;

if ($groepen && is_array($groepen)) {
    foreach ($groepen as $g) {
        $check = ossn_get_entities([
            'owner_guid' => $g->guid,
            'type'       => 'object',
            'subtype'    => 'groupslugname',
        ]);
        if ($check) {
            continue;
        }

        if (!empty($g->title)) {
            $result = groupslugrouter_generate_slug($g);
            if ($result) {
                $slugCount++;
            }
        }
    }
}

error_log("[SLUG] ✅ {$slugCount} slugs toegevoegd voor bestaande groepen.");
