<?php
/**
 * GroupSlugRouter Component
 * Auteur: Eric Redegeld
 * Slug router voor groepen via metadata, geen database wijzigingen nodig
 */

define('__GROUPSLUGROUTER__', ossn_route()->com . 'GroupSlugRouter/');

// ✅ Laad de helper en metadata library
require_once __GROUPSLUGROUTER__ . 'helpers/slug.php';
require_once dirname(dirname(dirname(__FILE__))) . '/libraries/ossn.lib.entities.php';

// ✅ juiste absolute pad naar core metadata-library
require_once dirname(dirname(dirname(__FILE__))) . '/libraries/ossn.lib.entities.php';

/**
 * Initialiseer de component
 */
function com_GroupSlugRouter_init() {
    ossn_register_page('g', 'groupslugrouter_vanity_handler');
    ossn_register_page('slugdebug', 'groupslugrouter_debug_slug');

    // ✅ Gebruik de juiste callback: group, add
    ossn_register_callback('group', 'add', 'groupslugrouter_on_group_added');
}
ossn_register_callback('ossn', 'init', 'com_GroupSlugRouter_init');

/**
 * Callback die wordt getriggerd na aanmaken groep
 */
function groupslugrouter_on_group_added($event, $type, $params) {
    if (!isset($params['group_guid'])) {
        error_log("[SLUG] ❌ group:add callback zonder group_guid");
        return;
    }

    $group = ossn_get_group_by_guid($params['group_guid']);
    if (!$group) {
        error_log("[SLUG] ❌ groep niet gevonden bij group:add callback");
        return;
    }

    error_log("[SLUG] ✳️ Slug genereren voor groep: {$group->guid} - {$group->title}");
    groupslugrouter_generate_slug($group);
}

/**
 * Vanity URL handler /g/slug → group/<guid>
 */
function groupslugrouter_vanity_handler($pages) {
    require_once __GROUPSLUGROUTER__ . 'helpers/slug.php';
    if (empty($pages[0])) {
        ossn_error_page();
        return;
    }

    $slug = $pages[0];
    error_log("[SLUG] 🌐 Opgevraagd: {$slug}");
    $group = groupslugrouter_get_group_by_slug($slug);

    if ($group) {
        redirect("group/{$group->guid}");
    } else {
        error_log("[SLUG] ❌ Geen groep gevonden voor slug '{$slug}'");
        ossn_error_page();
    }
}

/**
 * Debug pagina om slugs op te zoeken
 */
function groupslugrouter_debug_slug($pages) {
    if (!ossn_isAdminLoggedin()) {
        ossn_error_page();
        return;
    }

    $output = '<div class="ossn-page-contents">';
    $output .= '<h2>Slug Debug Tool</h2>';
    $output .= '<form method="GET"><input name="s" value="' . htmlentities($_GET['s'] ?? '') . '" />';
    $output .= '<button type="submit">Zoek</button></form>';

    if (isset($_GET['s'])) {
        $group = groupslugrouter_get_group_by_slug($_GET['s']);
        if ($group) {
            $output .= "<p>✅ Gevonden: <a href='" . ossn_site_url("group/{$group->guid}") . "'>group/{$group->guid}</a></p>";
        } else {
            $output .= "<p>❌ Niet gevonden</p>";
        }
    }

    $output .= '</div>';
    echo ossn_view_page('Slug Debug', $output);
}
