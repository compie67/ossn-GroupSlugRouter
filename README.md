# ossn-GroupSlugRouter
# OSSN GroupSlugRouter  A custom Open Source Social Network (OSSN) component that adds "vanity URLs" (slugs) to public groups.



---

## ❓ What it does(want it to be)

- Automatically generates a slug from the group title when a new group is created.
- Stores the slug as **metadata** (`username`) attached to the group.
- Adds a `/g/slug` vanity route that redirects to the original group page.
- Includes a `/slugdebug?s=your-slug` tool (admin-only) for checking if a slug was registered.

---

## 🔧 Why this component?

OSSN discourages direct modifications to the core database schema. This component follows best practices by:

- Using metadata (via `ossn_add_metadata`) to store the slug.
- Avoiding core file changes.
- Registering the slug via the `group:add` callback, officially documented here:
  https://www.opensource-socialnetwork.org/documentation/view/5566/entity-types

---

## 🐛 Current issue

At the moment, the component fails to save the metadata due to this error:


Even though `ossn_add_metadata()` is documented and expected to exist in OSSN core, it appears to be unavailable or not loaded in some environments.

---

## ✅ Files

- `ossn_com.php`: component init, route registration, and callback handling
- `helpers/slug.php`: handles slug creation, checking, and metadata writing

---

## 📋 To Do

- [ ] Resolve missing function `ossn_add_metadata()` or find alternative
- [ ] Confirm compatibility with OSSN 8.1 (Premium)
- [ ] Add group slug editing via admin interface (optional)

---

## 👨‍💻 Author

Eric Redegeld  
https://nlsociaal.nl – OSSN-based social platform in The Netherlands

