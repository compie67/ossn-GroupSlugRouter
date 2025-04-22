<?php
/**
 * fix.php - Admin tool voor het herstellen van ontbrekende slugs
 * URL: /administrator/group-slugs/fix
 */

require_once __DIR__ . '/../../../helpers/slug.php';

if (!ossn_isAdminLoggedin()) {
    redirect();
}

echo "<div class='ossn-page-contents'>";
echo "<h2>🔧 Slug-hersteltool</h2>";

$object = new OssnObject;
$groepen = $object->searchObject([
    'types'      => 'user',
    'subtype'    => 'ossngroup',
    'page_limit' => false,
]);

$hersteld = 0;

if ($groepen) {
    foreach ($groepen as $g) {
        $check = ossn_get_entities([
            'owner_guid' => $g->guid,
            'type'       => 'object',
            'subtype'    => 'groupslugname',
        ]);
        if ($check) {
            echo "<p>✅ Groep {$g->guid} heeft al een slug.</p>";
            continue;
        }

        $slug = groupslugrouter_generate_slug($g);
        if ($slug) {
            echo "<p>🛠️ Slug '{$slug}' aangemaakt voor groep {$g->guid} ({$g->title})</p>";
            $hersteld++;
        } else {
            echo "<p>⚠️ Mislukt: groep {$g->guid}</p>";
        }
    }
    echo "<p>✅ Slugs hersteld: {$hersteld}</p>";
} else {
    echo "<p>⚠️ Geen groepen gevonden in de database.</p>";
}
echo "</div>";
