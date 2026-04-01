### Tashyik Backend

#### Information

-   Laravel version: `12.20`
-   Admin login data
-   -   Email: `admin@example.com`
-   -   Password: `admin`

#

#### Installation

-   Make `.env` file from `.env.example`
-   Run `composer install` command
-   Run `php artisan key:generate` to generate app key
-   Run `php artisan migrate --seed` to migrate and seed the database
-   Run `php artisan storage:link` to create storage symlink
-   Run `npm install` & `npm run build` commands

#### Google Cloud APIs

-   Update the Google cloud service account file `google-service-account.json` at `storage/app/private` folder
-   Update Firebase credentials at `resources/js/firebase-config.json` file
