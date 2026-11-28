# KuraPoa

KuraPoa is a mobile-first student election platform built with Laravel 13, Breeze authentication scaffolding, Blade, Tailwind CSS 3, Alpine.js, and MySQL. The interface is in Swahili and uses the tagline “Kura yako, sauti yako.”

## Requirements

- PHP 8.3 or later with PDO MySQL and PDO SQLite extensions
- Composer
- Node.js and npm
- MySQL
- PHP ZIP extension for XLSX voter imports (CSV imports work without it)

## Local setup

1. Install the existing PHP and JavaScript dependencies:

   ```sh
   composer install
   npm install
   ```

2. Create the local environment file and application key:

   ```sh
   cp .env.example .env
   php artisan key:generate
   ```

3. Create a MySQL database, then set `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env`.

4. Create the storage link for candidate photos:

   ```sh
   php artisan storage:link
   ```

5. Apply the migrations and seed the demo election:

   ```sh
   php artisan migrate:fresh --seed
   ```

   **Warning:** `migrate:fresh` drops all tables in the configured database before rebuilding them. Use it only with a disposable local database.

6. Start the application and Vite in separate terminals:

   ```sh
   php artisan serve
   npm run dev
   ```

   Open `http://127.0.0.1:8000`.

7. For scheduled elections to open automatically and expired elections to close automatically, run Laravel's scheduler in a third terminal:

   ```sh
   php artisan schedule:work
   ```

The `elections:cleanup-test` command defaults to a dry run and targets only closed elections explicitly marked `is_test=true` and ended more than 30 days ago. To actually remove eligible test data, pass `--force`; production elections are never eligible.

## Demo accounts

Admin login:

- Email: `admin@kurapoa.test`
- Password: `Password123!`

Demo voter access codes are for local testing only. Choose the demo election on `/kura`, enter the corresponding registration number, and use its code:

| Registration number | Access code |
| --- | --- |
| `MOCU/2026/001` | `111111` |
| `MOCU/2026/002` | `222222` |
| `MOCU/2026/003` | `333333` |
| `MOCU/2026/004` | `444444` |
| `MOCU/2026/005` | `555555` |
| `MOCU/2026/006` | `666666` |
| `MOCU/2026/007` | `777777` |
| `MOCU/2026/008` | `888888` |
| `MOCU/2026/009` | `999999` |
| `MOCU/2026/010` | `100000` |

Do not use these credentials for a real election. Public admin registration is disabled; create the initial admin through the seeder and manage subsequent accounts through trusted database or deployment tooling.

The seeded administrator is a super administrator linked to the demo institution. Institution scoping is enforced on institution-owned election, position, candidate, and voter model queries. A regular administrator without an assigned institution has no institution-owned records visible.

## Institution structure

The application supports institutions, faculties, departments, courses, academic years, class groups, institution-specific elections, voter eligibility metadata, candidate approval metadata, and sponsors. Academic-year current status is maintained by application-level transactions so one institution can have at most one current year. The old migrations remain unchanged; this structure is added through a new migration.

Sponsor logos are optional and can be uploaded by a super administrator as PNG, JPG, WEBP, or SVG files up to 2 MB. SVG files are restricted to a small allow-list of elements and attributes before storage, and are served as image files rather than inserted as inline markup. Candidate photos are cropped into 512 px profile images and 160 px thumbnails; administrators can preview, replace, or remove them. Candidate uploads are limited to 3 MB. Run `php artisan storage:link` to publish uploaded sponsor and candidate images. Obtain written permission from each organization before displaying its real logo; demo sponsors use text placeholders.

Super administrators can configure YouTube and Instagram HTTPS links, a Tanzanian WhatsApp number (12 digits beginning with `255`), and a contact email from **Admin → Mipangilio**. Social icons and the WhatsApp contact button are shown only when their corresponding values are configured.

The multitenancy migration intentionally leaves an existing `elections.is_test` column in place on rollback because the column may predate the migration. This avoids dropping application data when reversing the upgrade.

## Languages

The interface supports Swahili (`sw`) and English (`en`). Visitors choose a language using the SW|EN switch; the choice is stored in the session, while administrator choices are also saved to the administrator profile. The default locale is Swahili.

## Interface design

The admin and voter authentication screens share the split-screen `auth-split-layout` and reusable auth card, status badge, form field, OTP input, button, and illustration components in `resources/views/components`. The ballot, review, and receipt use a compact voter shell that keeps the election context visible and places the primary action within easy reach on mobile. Plus Jakarta Sans is used throughout.

Tailwind semantic colors include primary green (`#0B6E4F`), gold accent (`#F2B705`), ink (`#0F172A`), body (`#334155`), muted (`#64748B`), and page background (`#F8FAF9`). Interactive controls have visible focus rings; motion respects reduced-motion preferences, and the receipt has print-specific styling.

The ballot wizard is presentation-only Alpine.js: it shows one position at a time, keeps the randomized candidate order provided by the server, and submits the existing `choices[position_id]` fields to the review route. Editing restores the requested step with `return_step`. Choices are not saved to the voter session. The receipt exposes only its non-choice reference code, and copy failures show instructions to save it manually. New screen text is available through the Swahili and English JSON translation files.

## Importing voters

Upload a UTF-8 CSV or XLSX file while an election is a draft. Required headers are `reg_number,name`. Optional headers are `phone,department_code,course_code,class_name`; academic codes/names must match the selected institution's structure. Duplicate registration numbers are skipped, invalid rows are counted, and a CSV containing each newly generated access code is streamed to the administrator immediately. Row errors can be downloaded separately. The plain codes are not written to the database, logs, or session. Save the downloaded file securely; codes cannot be retrieved later.

XLSX files are parsed with PhpSpreadsheet. Enable PHP's ZIP extension in the PHP runtime serving the app to use XLSX imports; CSV remains available if ZIP is disabled.

## Anonymity and security design

- `votes` stores only `position_id` and `candidate_id`. It has no voter reference and no timestamps.
- `ballot_participations` stores only `voter_id` and `position_id`, with a unique constraint preventing repeat participation for a position. It does not store a candidate or timestamp.
- The voter’s `has_voted` flag and `voted_at` timestamp prevent duplicate ballots and support turnout. The vote rows have no timestamp that could be matched to that value.
- Selected candidates are sent to the server only for review and final submission. Choices are not put in the voter session or audit log. The service validates every position and candidate, then inserts votes and participation rows in one transaction.
- Voter access codes are stored as Laravel password hashes. An additional keyed HMAC fingerprint with a unique database index prevents accidental reuse of a six-digit code without storing the code itself.
- Admin audit logs record management actions and aggregate import counts only. They never record a voter’s candidate choices or plain access codes.
- Results are aggregated only after an election is closed or its end time has passed. Admins can view turnout while voting is open, but not live candidate totals.
- Election positions and candidates, as well as voter imports, are locked after the election opens. This keeps the ballot and registered-voter denominator stable.

The database design prevents a direct voter-to-candidate association. As with any system that publishes small-group totals, administrators should avoid publishing partial or identifying turnout details outside the aggregate results page.

## Tests and assets

Run the feature and unit test suites:

```sh
php artisan test
```

Build production assets:

```sh
npm run build
```
