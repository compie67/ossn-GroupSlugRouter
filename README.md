# GroupSlugRouter Component

> Friendly vanity URLs for OSSN groups using slugs stored as entity metadata — no database modifications required.

---

## 🧩 What does this module do?

This component allows your Open Source Social Network (OSSN) site to use **vanity URLs** for groups. Instead of visiting a group via:



---

## 🚀 Features

- Auto-generates a **URL-friendly slug** from the group title.
- Works with OSSN's `ossn_add_entity()` API (no table modifications).
- Slugs are stored as `type = object`, `subtype = groupslugname` entities.
- Includes debug tool for admin users (`/slugdebug`).
- Automatically assigns slugs for **existing groups on activation**.
- Prevents slug collisions (appends group GUID if necessary).
- Full support from OSSN version **6.0+**.

---

## 📦 Installation

1. Drop the folder `GroupSlugRouter` into your `components/` directory.
2. Ensure the folder structure looks like:

components/ └── GroupSlugRouter/ ├── ossn_com.php ├── enable.php ├── helpers/ │ └── slug.php └── ossn_com.xml

3. Log in as admin and enable the component via the admin panel.

Upon activation, the module will automatically assign slugs to all existing groups that don’t already have one.

---

## 🔍 Debug

Admins can visit:
https://yoursite.tld/slugdebug


…to manually check if a slug exists and test redirection.

---

## 🛠 How it works

When a new group is created:
- The title is converted into a lowercase, hyphenated slug.
- We check if the slug is already in use.
- If it is, a fallback with `-group-guid` is appended.
- The slug is saved using OSSN's `ossn_add_entity()` as:
  - `type = object`
  - `subtype = groupslugname`
  - `value = <slug>`
  - `owner_guid = group_guid`

When accessing `/g/<slug>`, we retrieve the slug entity and redirect to the group using `ossn_get_group_by_guid(owner_guid)`.

### 💡 Why the redirect uses owner_guid

Each slug is stored as a separate entity, and OSSN doesn’t support direct linking between arbitrary objects and groups. So we store the `group_guid` as the **owner_guid** of the slug entity. This allows fast retrieval via:

```php
return ossn_get_group_by_guid($entity->owner_guid);

This workaround was inspired by community member Michael Zülsdorff, who also suggested redirecting based on the group owner's GUID rather than relying on entity matching alone.

🙌 Credits
Eric Redegeld (developer)

OSSN community for testing

Special thanks to Michael Zülsdorff for his clear insights and suggestions during development.

📜 License
GNU General Public License v2
https://www.gnu.org/licenses/old-licenses/gpl-2.0.html

🌐 Live Demo
Component is used at:
https://nlsociaal.nl
OSSN-based platform built by Eric Redegeld.
