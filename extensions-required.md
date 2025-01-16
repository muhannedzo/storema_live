# PHP Extensions Required for Project

This document lists all the PHP extensions that need to be enabled for the project to work correctly after installing xampp so I don't forget them everytime i have to make a new setup.

---

## Required Extensions

| Extension       | Description                                        | Why It's Needed                     |
|-----------------|----------------------------------------------------|-------------------------------------|
| `gd`           | Image processing and manipulation library         | For handling and compressing images |

---

## How to Enable Extensions in XAMPP

1. Open the XAMPP Control Panel in admin mode.
2. Click "Config" next to Apache and select `php.ini`.
3. Search for the extensions listed above (e.g., `;extension=gd`).
4. Uncomment the required extensions by removing the leading `;`. For example:
   ```ini
   ;extension=gd
