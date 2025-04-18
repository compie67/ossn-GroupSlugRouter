<?php
/**
 * GroupSlugRouter Component
 * Author: Eric Redegeld
 * Friendly vanity URLs for groups using OSSN entity slugs (no DB schema changes)
 */

define('__GROUPSLUGROUTER__', ossn_route()->com . 'GroupSlugRouter/');
require_once __GROUPSLUGROUTER__ . 'helpers/slug.php';

/**
 * Component initialisatie
 */
function com_GroupSlugRouter_init() {
    // Vanity URL handler: /g/slug
    ossn_register_page('g', 'groupslugrouter_vanity_handler');

    // Debug tool voor admins
    ossn_register_page('slugdebug', 'groupslugrouter_debug_slug');

    // Callback wanneer een groep is aangemaakt
    ossn_register_callback('group', 'add', 'groupslugrouter_on_group_added');
}
ossn_register_callback('ossn', 'init', 'com_GroupSlugRouter_init');

/**
 * Callback: na het aanmaken van een groep
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

    groupslugrouter_generate_slug($group);
}

/**
 * Handler voor vanity URL's: /g/slug → /group/guid
 *
 * 👉 Let op: deze redirect gebruikt de owner_guid van de slug entity
 *    Idee en oplossing dankzij communitylid **Michael Zülsdorff**
 */
function groupslugrouter_vanity_handler($pages) {
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
        error_log("[SLUG] ❌ Geen redirect, groep niet gevonden voor slug '{$slug}'");
        ossn_error_page();
    }
}

/**
 * Debug-tool voor admins
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
