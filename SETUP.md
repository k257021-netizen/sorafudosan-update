# Sora Living setup

## Local database upgrade

The current local `sora` database has the full feature migration applied. For another existing installation, select the `sora` database in phpMyAdmin and import `databases/upgrade_full_features.sql`. The migration is repeatable and keeps existing records. Do not import the older one-time account migration.

Property floor area, floor number, and initial-cost estimate are optional. Existing listings are left blank rather than assigned invented values; enter verified listing details in Admin so they can be displayed and matched by those filters.

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

Registered favorites, email verification, password reset and change, login attempt throttling, inquiry status and internal notes, property availability status, search sorting and pagination, and terms/privacy consent are available after migration. Terms and privacy pages are starter policy text and should be reviewed for the operator's actual business and jurisdiction before public launch.
