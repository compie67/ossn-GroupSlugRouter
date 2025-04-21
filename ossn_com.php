<?php
/**
 * GroupSlugRouter Component
 * Auteur: Eric Redegeld
 * Friendly vanity URLs for OSSN groups using slug entities (zonder DB-aanpassing)
 * Met dank aan Michael Zülsdorff voor feedback en optimalisaties.
 */

define('__GROUPSLUGROUTER__', ossn_route()->com . 'GroupSlugRouter/');
require_once __GROUPSLUGROUTER__ . 'helpers/slug.php';

/**
 * 🇳🇱 Initialisatie van de component
 * 🇬🇧 Component initialization
 */
function com_GroupSlugRouter_init() {
    // 📌 CSS 
    ossn_extend_view('ossn/site/head', 'css/usergroups.css');
    // 📌 Vanity URL redirect
    ossn_register_page('g', 'groupslugrouter_vanity_handler');

    // 📌 Debug tool voor admins
    ossn_register_page('slugdebug', 'groupslugrouter_debug_slug');

    // 📌 Profielsubpagina /u/gebruikersnaam/groups
    ossn_profile_subpage('groups');
    ossn_add_hook('profile', 'subpage', 'groupslugrouter_subpage_handler');

    // 📌 Voeg menu-link toe aan profiel (Groepen-tab)
    ossn_register_callback('page', 'load:profile', 'groupslugrouter_profile_link');

    // 📌 Groepsslug genereren bij aanmaken groep
    ossn_register_callback('group', 'add', 'groupslugrouter_on_group_added');
}

ossn_register_callback('ossn', 'init', 'com_GroupSlugRouter_init');

/**
 * Groep toegevoegd → slug genereren
 */
function groupslugrouter_on_group_added($event, $type, $params) {
    if (!isset($params['group_guid'])) {
        return;
    }
    $group = ossn_get_group_by_guid($params['group_guid']);
    if ($group) {
        groupslugrouter_generate_slug($group);
    }
}

/**
 * 📌 Handler voor /g/slug → redirect naar /group/guid
 */
function groupslugrouter_vanity_handler($pages) {
    if (empty($pages[0])) {
        ossn_error_page();
        return;
    }
    $slug = $pages[0];
    $group = groupslugrouter_get_group_by_slug($slug);
    if ($group) {
        redirect("group/{$group->guid}");
    } else {
        ossn_error_page();
    }
}

/**
 * 📌 Admin debug tool (optioneel)
 */
function groupslugrouter_debug_slug($pages) {
    if (!ossn_isAdminLoggedin()) {
        ossn_error_page();
        return;
    }

    $output = '<div class="ossn-page-contents">';
    $output .= '<h2>Slug Debug Tool</h2>';
    $output .= '<form method="GET"><input name="s" value="' . htmlentities($_GET['s'] ?? '') . '" />';
    $output .= '<button type="submit">Zoek / Search</button></form>';

    if (isset($_GET['s'])) {
        $group = groupslugrouter_get_group_by_slug($_GET['s']);
        if ($group) {
            $output .= "<p>✅ Gevonden: <a href='" . ossn_site_url("group/{$group->guid}") . "'>group/{$group->guid}</a></p>";
        } else {
            $output .= "<p>❌ Niet gevonden / Not found</p>";
        }
    }

    $output .= '</div>';
    echo ossn_view_page('Slug Debug', $output);
}

/**
 * 📌 Subpage handler voor /u/gebruikersnaam/groups
 */
function groupslugrouter_subpage_handler($hook, $type, $return, $params) {
    if ($params['subpage'] == 'groups' && isset($params['user'])) {
        ossn_set_input('username', $params['user']->username);
        include __GROUPSLUGROUTER__ . 'pages/user/groups.php';
        return true;
    }
    return $return;
}

/**
 * 📌 Voeg "Groepen" tab toe aan profiel
 */
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
