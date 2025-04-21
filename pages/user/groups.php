<?php
/**
 * 🇳🇱 Overzicht van groepen op gebruikersprofielpagina
 * 🇬🇧 User's group overview on their profile page
 */

$user = ossn_user_by_guid(ossn_get_page_owner_guid());
if (!$user) {
    ossn_error_page();
}

// 🇳🇱 Sorteeroptie ophalen uit URL
// 🇬🇧 Get sort preference from query
$sort = input('sort') ?: 'newest';

// 🇳🇱 Toon wel/geen covers: opgeslagen in sessie
// 🇬🇧 Show covers: stored in session per browser
if (isset($_GET['covers'])) {
    $_SESSION['usergroups_show_covers'] = input('covers');
}
$show_covers = $_SESSION['usergroups_show_covers'] ?? '1';
$show_covers = ($show_covers === '0' || $show_covers === 'false') ? false : true;

// 🇳🇱 Groepen ophalen en tellen
// 🇬🇧 Fetch and count user groups
$group_class = new OssnGroup();
$all_groups = $group_class->getUserGroups($user->guid);
$count_groups = $group_class->getUserGroups($user->guid, ['count' => true]);

// 🇳🇱 Pagina titel
// 🇬🇧 Page title
$title = ossn_print('usergroups:title', [$user->username]);

// 🇳🇱 Sorteer- en weergaveformulier
// 🇬🇧 Sorting and view toggle form
$sorting_form = '<form method="GET" style="margin-bottom:20px;">
    <label for="sort">' . ossn_print('ossn:user:groups:sort') . '</label>
    <select name="sort">
        <option value="newest" ' . ($sort == 'newest' ? 'selected' : '') . '>' . ossn_print('ossn:user:groups:newest') . '</option>
        <option value="oldest" ' . ($sort == 'oldest' ? 'selected' : '') . '>' . ossn_print('ossn:user:groups:oldest') . '</option>
        <option value="members" ' . ($sort == 'members' ? 'selected' : '') . '>' . ossn_print('ossn:user:groups:members') . '</option>
        <option value="az" ' . ($sort == 'az' ? 'selected' : '') . '>' . ossn_print('ossn:user:groups:az') . '</option>
        <option value="za" ' . ($sort == 'za' ? 'selected' : '') . '>' . ossn_print('ossn:user:groups:za') . '</option>
    </select>

    <label for="covers" style="margin-left:15px;">' . ossn_print('usergroups:showcovers') . '</label>
    <select name="covers">
        <option value="1" ' . ($show_covers ? 'selected' : '') . '>' . ossn_print('yes') . '</option>
        <option value="0" ' . (!$show_covers ? 'selected' : '') . '>' . ossn_print('no') . '</option>
    </select>

    <button type="submit" style="margin-left:10px;">' . ossn_print('usergroups:apply') . '</button>
</form>';

// 🇳🇱 Begin van contentblock
// 🇬🇧 Start of content block
$content = $sorting_form;
$content .= '<div class="user-groups-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">';

// 🇳🇱 Groepen sorteren op basis van selectie
// 🇬🇧 Sort groups based on selected filter
if (!empty($all_groups)) {
    switch ($sort) {
        case 'oldest':
            usort($all_groups, fn($a, $b) => $a->time_created - $b->time_created);
            break;
        case 'members':
            usort($all_groups, fn($a, $b) =>
                ossn_get_relationships(['to' => $b->guid, 'type' => 'group:join', 'count' => true]) -
                ossn_get_relationships(['to' => $a->guid, 'type' => 'group:join', 'count' => true])
            );
            break;
        case 'az':
            usort($all_groups, fn($a, $b) => strcasecmp($a->title, $b->title));
            break;
        case 'za':
            usort($all_groups, fn($a, $b) => strcasecmp($b->title, $a->title));
            break;
        default:
            usort($all_groups, fn($a, $b) => $b->time_created - $a->time_created);
    }

    // 🇳🇱 Genereer kaartweergave per groep
    // 🇬🇧 Generate group card per group
    foreach ($all_groups as $group) {
        $group_data = ossn_get_group_by_guid($group->guid);
        if (!$group_data) continue;

        $cover = $group_data->coverURL();
        $cover_url = $cover ?: ossn_site_url() . 'components/GroupSlugRouter/images/banner.png';

        $members = ossn_get_relationships([
            'to' => $group_data->guid,
            'type' => 'group:join',
            'count' => true
        ]);

        $privacy = ($group->membership == OSSN_PUBLIC)
            ? ossn_print('public')
            : (($group->membership == OSSN_PRIVATE)
                ? ossn_print('private')
                : ossn_print('usergroups:privacy:unknown'));

        $group_title = $group_data->title;
        $group_url = ossn_site_url("group/{$group_data->guid}");

        // 🔗 Slug ophalen (indien beschikbaar)
        $tooltip = '';
        $slug_path = '';
        $slug_entity = ossn_get_entities([
            'type' => 'object',
            'subtype' => 'groupslugname',
            'owner_guid' => $group_data->guid,
            'limit' => 1
        ]);
        if ($slug_entity && isset($slug_entity[0]->value)) {
            $slug_path = "g/{$slug_entity[0]->value}";
            $tooltip = "Snelle toegang via vanity URL: /{$slug_path}";
        }

        // 📦 HTML-output per groep
        $content .= "<div class='group-card' style='border:1px solid #ddd; border-radius:10px; overflow:hidden; background:#fff; box-shadow:0 2px 5px rgba(0,0,0,0.05);'>";
        if ($show_covers) {
            $content .= "<a href='{$group_url}'><img src='{$cover_url}' alt='cover' style='width:100%; height:150px; object-fit:cover; border-bottom:1px solid #eee;'></a>";
        }
        $content .= "<div style='padding:12px;'>
            <a href='{$group_url}'><strong>{$group_title}</strong></a><br>";
        if ($slug_path) {
            $content .= "<small><a href='" . ossn_site_url($slug_path) . "' title='{$tooltip}'>/{$slug_path}</a></small><br>";
        }
        $content .= "<small>{$privacy}</small><br>
            <small>" . ossn_print('usergroups:members', [$members]) . "</small>
        </div></div>";
    }

    // ✅ Paginatie
    echo ossn_view_pagination($count_groups);
} else {
    $content .= '<p>' . ossn_print('usergroups:no_groups') . '</p>';
}
$content .= '</div>';

// 🧱 Render via module-layout
$mod = [
    'title'   => $title,
    'content' => $content
];

echo ossn_set_page_layout('module', $mod);
