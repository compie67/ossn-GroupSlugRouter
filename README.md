
# GroupSlugRouter for OSSN

**Vanity URLs for OSSN Groups + Profile Group Overview**

GroupSlugRouter is an OSSN component that gives your groups clean, friendly, and shareable URLs like:

```
https://yourdomain.com/g/music-lovers
```

It also adds a beautiful **"Groups"** tab to each user profile, showing all the groups they manage with sorting and quick access.

---

## 🌟 Features

- ✅ Vanity URL redirect: `/g/slug` ➝ `/group/guid`
- ✅ Profile subpage: `/u/username/groups`
- ✅ Sorting: Newest, A-Z, Most Members, etc.
- ✅ Toggle group cover images on/off
- ✅ Localized: 🇬🇧 English & 🇳🇱 Dutch
- ✅ No database changes required
- ✅ Automatically generates slugs for existing & new groups
- ✅ Clean uninstall: deletes all slug entities
- ✅ Debug page: `/slugdebug` (admin only)

---

## 🔧 Installation

1. Place the `GroupSlugRouter` folder in your OSSN `components/` directory.
2. Log into your OSSN Admin Panel.
3. Enable **GroupSlugRouter** under *Components*.
4. Make sure `OssnGroups` is active.
5. Done! Slugs are generated automatically and profile pages will now show a **Groups** tab.

---

## ❗ Important Notes

- This module **requires** the default `OssnGroups` component to be active.
- If you previously used the `UserGroups` module, please **disable it** to avoid conflicts.
- Disabling this component removes all slug entities, but groups themselves are untouched.

---

## 🛠️ Admin Tools

- Access the [slug debug tool](https://yourdomain.com/slugdebug) to manually test slugs (admin only).
- Slugs are unique per group title. Conflicts are handled with automatic suffixes like `group-name-1`, `group-name-2`, etc.

---

## 🙌 Thanks

Special thanks to the OSSN community —    
for valuable insights, performance suggestions, and OSSN best practices.

His feedback helped me:
- believe in yourself, never trust AI(it keeps you going in circles)
- avoid unnecessary lookups for redirect logic  
- streamline slug generation  
- better manage component cleanup behavior

---

## 📜 License

This component is released under the [GNU General Public License v2](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html).

---

**Developed by:**  
Eric Redegeld  
[https://nlsociaal.nl](https://nlsociaal.nl)
