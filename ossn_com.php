<?php
/**
 * GroupSlugRouter Component
 * Auteur: Eric Redegeld – nlsociaal.nl
 */

define('__GROUPSLUGROUTER__', ossn_route()->com . 'GroupSlugRouter/');
require_once __GROUPSLUGROUTER__ . 'helpers/slug.php';

function com_GroupSlugRouter_init() {
    // CSS
    ossn_extend_view('ossn/site/head', 'css/usergroups.css');

    // Admin instellingenpagina (component settings view)
    ossn_register_com_panel('GroupSlugRouter', 'groupslugrouter');

    // Admin menu-link naar OSSN fix-pagina
    ossn_register_menu_link(
        'groupslugrouter:fix',
        ossn_print('groupslugrouter:fix') ?: 'Slug herstel',
        'administrator/component/GroupSlugRouter?view=fix',
        'admin'
    );

    // Vanity URL handler: /g/slug → redirect naar group/GUID
    ossn_register_page('g', 'groupslugrouter_vanity_handler');

    // Debug pagina via /slugdebug
    ossn_register_page('slugdebug', 'groupslugrouter_debug_slug');

    // Custom tool-pagina via /group-slugs/fix
    ossn_register_page('group-slugs', 'groupslugrouter_fix_page_handler');

    // Profielsubpagina: /u/username/groups
    ossn_profile_subpage('groups');
    ossn_add_hook('profile', 'subpage', 'groupslugrouter_subpage_handler');

    // Menu-link naar profielsubpagina
    ossn_register_callback('page', 'load:profile', 'groupslugrouter_profile_link');

    // Automatisch slugs aanmaken bij group add/update
    ossn_register_callback('group', 'add', 'groupslugrouter_on_group_added');
    ossn_register_callback('group', 'update', 'groupslugrouter_on_group_updated');
}
ossn_register_callback('ossn', 'init', 'com_GroupSlugRouter_init');

// === FUNCTIES ===

function groupslugrouter_on_group_added($event, $type, $params) {
    if (!empty($params['group_guid'])) {
        $group = ossn_get_group_by_guid($params['group_guid']);
        if ($group) {
            groupslugrouter_generate_slug($group);
        }
    }
}

function groupslugrouter_on_group_updated($event, $type, $params) {
    if (!empty($params['group_guid'])) {
        $group = ossn_get_group_by_guid($params['group_guid']);
        if ($group) {
            groupslugrouter_generate_slug($group);
        }
    }
}

function groupslugrouter_vanity_handler($pages) {
    if (empty($pages[0])) return ossn_error_page();
    $slug = strtolower($pages[0]);
    $guid = groupslugrouter_get_group_by_slug($slug);
    return $guid ? redirect("group/{$guid}") : ossn_error_page();
}

function groupslugrouter_debug_slug($pages) {
    if (!ossn_isAdminLoggedin()) return ossn_error_page();

    $output = '<div class="ossn-page-contents">';
    $output .= '<h2>' . ossn_print('slugdebug:title') . '</h2>';
    $output .= '<form method="GET"><input name="s" value="' . htmlentities($_GET['s'] ?? '') . '" />';
    $output .= '<button type="submit">Zoek / Search</button></form>';

    if (!empty($_GET['s'])) {
        $guid = groupslugrouter_get_group_by_slug($_GET['s']);
        $output .= $guid
            ? "<p>✅ Gevonden: <a href='" . ossn_site_url("group/{$guid}") . "'>group/{$guid}</a></p>"
            : "<p>❌ Niet gevonden / Not found</p>";
    }

    $output .= '</div>';
    echo ossn_view_page('Slug Debug', $output);
}

function groupslugrouter_subpage_handler($hook, $type, $return, $params) {
    if ($params['subpage'] == 'groups' && !empty($params['user'])) {
        ossn_set_input('username', $params['user']->username);
        include __GROUPSLUGROUTER__ . 'pages/user/groups.php';
        return true;
    }
    return $return;
}

function groupslugrouter_profile_link() {
    $user = ossn_user_by_guid(ossn_get_page_owner_guid());
    if ($user) {
        ossn_register_menu_link(
            'groups',
            ossn_print('groups'),
            ossn_site_url("u/{$user->username}/groups"),
            'user_timeline'
        );
    }
}

/**
 * Handler voor custom pagina /group-slugs/fix
 */
function groupslugrouter_fix_page_handler($pages) {
    if (!ossn_isAdminLoggedin()) {
        redirect();
    }

    if (!isset($pages[0]) || $pages[0] !== 'fix') {
        return ossn_error_page();
    }

    // Direct opnemen van de tooloutput
    include_once __GROUPSLUGROUTER__ . 'pages/group-slugs/fix.php';
    return true;
}
