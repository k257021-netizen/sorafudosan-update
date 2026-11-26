# Sora Living setup

## Local database upgrade

For an existing `sora` installation, select the database in phpMyAdmin and import `databases/upgrade_full_features.sql`, `databases/upgrade_password_security.sql`, `databases/upgrade_site_documents.sql`, and `databases/upgrade_property_images.sql`. These migrations are repeatable and keep existing records. Existing customer sessions will need to sign in again after the password-security migration. Do not import the older one-time account migration.

Admin can upload up to 10 JPG, PNG, or WebP photos when creating a listing. Each photo must be 5 MB or less; the first selected photo is used as the cover, and the rest appear in the detail-page gallery. The upload directory is created automatically, but PHP must be allowed to write to `images/uploads/`. Set PHP's `upload_max_filesize` to at least 5 MB and `post_max_size` above the combined upload size.

Property floor area and floor number are optional. Existing listings are left blank rather than assigned invented values. For an existing database, import `databases/upgrade_initial_cost_breakdown.sql` once in phpMyAdmin before using the updated Admin forms. It preserves existing listings and their current cost estimates. For new installs, `databases/sora.sql` already includes the new fields.

For rental listings, Admin can enter the confirmed contract-cost breakdown and automatically total the first/prorated rent, management fee, deposit, key money, brokerage fee, guarantee fee, fire insurance, key exchange, and other contract charges. Enter `0` for a confirmed non-applicable charge; leave unknown charges blank and do not enable the breakdown until all items are confirmed. The sale listing's purchase-cost estimate remains a manual total because actual costs depend on taxes, registration, and financing conditions. Estimates should be checked against the current property-specific quote before publication.

Import `databases/upgrade_reservations.sql` once so customer applications can be stored. Customer applications now remain pending until an administrator approves or rejects them under Admin → 申込確認. A pending application holds the listing as reserved; rejection reopens it, and approval sends a status email when mail is configured. The Admin dashboard also flags active listings with missing station, floor area, initial-cost estimate, description, sample text, or placeholder photos so verified listing data can be completed before publication. Admin sign-in is limited to five failed attempts per username/IP pair and twenty per IP in a 15-minute window.

## Production account and mail settings

Set these environment variables in the PHP/Apache service before using this site publicly:

- `SORA_APP_ENV=production` enables email verification at signup and hides development reset links.
- `SORA_BASE_URL=https://your-domain.example` sets the canonical public URL used in emails.
- `SORA_MAIL_FROM=accounts@your-domain.example` sets the verified sender address.
- `SORA_ADMIN_EMAIL=staff@your-domain.example` receives inquiry notifications.
- `SORA_DB_HOST`, `SORA_DB_NAME`, `SORA_DB_USER`, and `SORA_DB_PASSWORD` configure MySQL. Use a dedicated database user with only the permissions this app needs.

PHP `mail()` must be configured by the host for outgoing email. The app reports a sending failure during production signup if mail is unavailable. For reliable production delivery, configure the server's authenticated mail transport.

The default environment is `development` for the local XAMPP workspace: signup verifies the account immediately and password recovery displays a local development link. Never use that default on a public deployment; explicitly set `SORA_APP_ENV=production`.

## Included account and inquiry features

Registered favorites, email verification, password reset and change, login attempt throttling, inquiry status and internal notes, property availability status, search sorting and pagination, and terms/privacy consent are available after migration. Administrators can edit the terms and privacy policy from Admin → 規約・ポリシー編集; saved plain text is immediately shown on the public pages, and existing users are not asked to consent again. The admin dashboard flags available/reserved listings not reviewed in 30 days; it does not hide them automatically, and an admin can mark a listing reviewed after checking its details. Unresolved inquiries are flagged after 24 hours and can be filtered from the inquiry page. Password changes notify the account email address and invalidate other sessions. Security events retain the event type, IP address, and browser user-agent for 180 days. Schedule `C:\xampp\php\php.exe C:\xampp\htdocs\sorafudosan-update\cleanup-security-events.php` to run daily in Windows Task Scheduler (or cron on Linux) to remove expired security events and login attempts. The privacy policy should be reviewed for the operator's actual business and jurisdiction before public launch.
