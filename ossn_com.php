<?php
/**
 * GroupSlugRouter Component
 * Auteur: Eric Redegeld
 */

define('__GROUPSLUGROUTER__', ossn_route()->com . 'GroupSlugRouter/');
require_once __GROUPSLUGROUTER__ . 'helpers/slug.php';

function com_GroupSlugRouter_init() {
    ossn_extend_view('ossn/site/head', 'css/usergroups.css');

    // Vanity URL
    ossn_register_page('g', 'groupslugrouter_vanity_handler');

    // Debug
    ossn_register_page('slugdebug', 'groupslugrouter_debug_slug');

    // Profielsubpagina
    ossn_profile_subpage('groups');
    ossn_add_hook('profile', 'subpage', 'groupslugrouter_subpage_handler');

    // Profiel-link
    ossn_register_callback('page', 'load:profile', 'groupslugrouter_profile_link');

    // Bij aanmaken en updaten
    ossn_register_callback('group', 'add', 'groupslugrouter_on_group_added');
    ossn_register_callback('group', 'update', 'groupslugrouter_on_group_updated');

    // Admin link
    ossn_register_admin_sidemenu('fixslugs', 'Slugs herstellen', 'administrator/group-slugs/fix', 'admin');
}
ossn_register_callback('ossn', 'init', 'com_GroupSlugRouter_init');

function groupslugrouter_on_group_added($event, $type, $params) {
    if (isset($params['group_guid'])) {
        $group = ossn_get_group_by_guid($params['group_guid']);
        if ($group) {
            groupslugrouter_generate_slug($group);
        }
    }
}

function groupslugrouter_on_group_updated($event, $type, $params) {
    if (isset($params['group_guid'])) {
        $group = ossn_get_group_by_guid($params['group_guid']);
        if ($group) {
            groupslugrouter_generate_slug($group);
        }
    }
}

function groupslugrouter_vanity_handler($pages) {
    if (empty($pages[0])) {
        return ossn_error_page();
    }

    $slug = strtolower($pages[0]);
    $guid = groupslugrouter_get_group_by_slug($slug);

    if ($guid) {
        return redirect("group/{$guid}");
    }

    return ossn_error_page();
}

function groupslugrouter_debug_slug($pages) {
    if (!ossn_isAdminLoggedin()) {
        return ossn_error_page();
    }

    $output = '<div class="ossn-page-contents">';
    $output .= '<h2>Slug Debug Tool</h2>';
    $output .= '<form method="GET"><input name="s" value="' . htmlentities($_GET['s'] ?? '') . '" />';
    $output .= '<button type="submit">Zoek / Search</button></form>';

    if (isset($_GET['s'])) {
        $guid = groupslugrouter_get_group_by_slug($_GET['s']);
        if ($guid) {
            $output .= "<p>✅ Gevonden: <a href='" . ossn_site_url("group/{$guid}") . "'>group/{$guid}</a></p>";
        } else {
            $output .= "<p>❌ Niet gevonden / Not found</p>";
        }
    }

    $output .= '</div>';
    echo ossn_view_page('Slug Debug', $output);
}

function groupslugrouter_subpage_handler($hook, $type, $return, $params) {
    if ($params['subpage'] == 'groups' && isset($params['user'])) {
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
