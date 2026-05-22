# Production Deployment Checklist

## 1. Required `.env` keys

Create/update `apps/.env` on production. Do not commit this file.

```env
APP_TIMEZONE=Africa/Accra

DB_HOST=localhost
DB_PORT=3306
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password
DB_CHARSET=utf8mb4

API_TOKEN_SECRET=replace_with_a_long_random_secret
API_RATE_LIMIT_DIR=
ASSET_IMAGE_MAX_BYTES=2097152
ASSET_IMAGE_MAX_IMAGES=10

PAYSTACK_SECRET_KEY=replace_with_rotated_paystack_secret
SECRET_KEY=

GOOGLE_MAPS_BROWSER_KEY=replace_with_restricted_browser_key

FIREBASE_PROJECT_ID=your_firebase_project_id
FIREBASE_CREDENTIALS_PATH=apps\notifications\controller\api\firebase_service_key.json

SMTP_HOST=replace_with_smtp_host
SMTP_PORT=465
SMTP_USER=replace_with_smtp_username
SMTP_PASS=replace_with_smtp_password
SMTP_SECURE=smtps
SMTP_FROM_EMAIL=no-reply@your-domain.com
SMTP_FROM_NAME=Tuetra
SMTP_REPLY_TO=
SMTP_REPLY_TO_NAME=Support
SMTP_DEBUG=0
```

Notes:
- `API_TOKEN_SECRET` must be stable across deploys. Changing it logs users out because existing API tokens stop validating.
- `API_RATE_LIMIT_DIR` is optional. Leave it empty to use the server temp directory, or set it to a writable directory outside public web access.
- `ASSET_IMAGE_MAX_BYTES` and `ASSET_IMAGE_MAX_IMAGES` control listing image upload limits. Keep the defaults unless mobile uploads need a larger cap.
- Use `PAYSTACK_SECRET_KEY` for Paystack. `SECRET_KEY` is legacy compatibility only.
- Keep Firebase/service account JSON files outside public web paths when possible.
- `FIREBASE_PROJECT_ID` should match the Firebase project that owns the service account.
- Firebase credential paths must be server filesystem paths, not `https://` URLs. Relative paths resolve from the backend project root.
- `GOOGLE_MAPS_BROWSER_KEY` is a browser key, but it must be restricted in Google Cloud by HTTP referrer and allowed API.

## 2. Rotate Paystack secret

The old Paystack key appeared in source and should be treated as exposed.

1. Generate a new secret key in the Paystack dashboard.
2. Set it in production `apps/.env` as `PAYSTACK_SECRET_KEY`.
3. Deploy the code that reads Paystack secrets from `.env` only.
4. Revoke the old Paystack key in Paystack.
5. Test payment initialization and verification.


## 3. Rotate/restrict Google Maps key

Google reported the old browser API key as publicly accessible, so treat it as exposed.

1. Regenerate the affected Google API key in Google Cloud Console, or create a replacement browser key.
2. Restrict the key by HTTP referrer, for example `https://naana.threnz.com/*`.
3. Restrict the key to only the APIs the listing map needs, such as Maps JavaScript API.
4. Set production `apps/.env` as `GOOGLE_MAPS_BROWSER_KEY`.
5. Confirm the add-listing map loads in the web admin/listing page.

## 4. File permissions

Ensure Apache/PHP can write uploaded asset images:

```powershell
uploads\asset_images
```

The directory should contain `.htaccess` to block PHP execution and directory listing.

## 5. Migrate existing asset images

Run from the backend project root.

Dry run first:

```powershell
php tools\migrate-asset-images-to-files.php
```

Apply migration:

```powershell
php tools\migrate-asset-images-to-files.php --apply
```

Optional batch mode:

```powershell
php tools\migrate-asset-images-to-files.php --limit=50
php tools\migrate-asset-images-to-files.php --apply --limit=50
```

Expected result after migration:

```text
Rows found: 0
```


## 6. Database indexes and duplicate checks

Run the duplicate check before adding unique constraints or future stricter migrations. Run the index check after applying `001_indexes.sql`; before the migration, it should report missing indexes:

```powershell
php tools\check-db-duplicates.php
php tools\check-db-indexes.php
```

Apply the safe MySQL index migration from the backend project root using your preferred MySQL client or phpMyAdmin:

```powershell
mysql your_database_name < tools\sql\001_indexes.sql
```

The index migration is designed to be re-runnable. It only creates an index when the target table, columns, and index name are present/missing as expected. Long text columns use MySQL prefix indexes to avoid key-length errors. After applying it, run `php tools\check-db-indexes.php` again and expect all index checks to pass.

## 7. Smoke tests

Run these from the backend project root after deployment.

```powershell
php -l apps\template\configs\bootstrap.php
php -l apps\template\statics\conn\anthrax.php
php -l apps\template\statics\conn\DALCONN.php
php -l apps\auth\controller\ApiAuthToken.php
php -l apps\auth\controller\ApiRateLimiter.php
php -l apps\auth\controller\api\UserLogin.php
php -l apps\auth\controller\api\AddNewUser.php
php -l apps\auth\controller\api\APIVerifyUserEmail.php
php -l apps\auth\controller\api\ResendVerificationCode.php
php -l apps\auth\controller\api\UpdateMyPassword.php
php -l apps\listing\controller\AssetImageUploadValidator.php
php -l apps\rental\controller\api\rent_pmt.php
php -l apps\rental\controller\api\v_pmt.php
php -l apps\listing\model\MDLSaveThisListing.php
php -l tools\migrate-asset-images-to-files.php
php -l tools\health-check.php
php -l tools\check-db-duplicates.php
php -l tools\check-db-indexes.php
```

DB connection smoke test:

```powershell
php -r "require 'apps/template/statics/conn/anthrax.php'; `$pdo = Connection::connect(); echo (`$pdo instanceof PDO ? 'ok' : 'fail') . PHP_EOL;"
```


Health check:

```powershell
php tools\health-check.php
```

This prints safe pass/fail lines only and exits non-zero if a required production dependency is missing.
Auth token smoke test:

```powershell
php -r "require 'apps/auth/controller/ApiAuthToken.php'; `$t = ApiAuthToken::issue(array('user_id'=>123,'email'=>'test@example.com','user_type'=>'individual')); `$server = array('HTTP_AUTHORIZATION'=>'Bearer '.`$t['token']); `$bearer = ApiAuthToken::bearerFromServer(`$server); `$v = ApiAuthToken::validate(`$bearer); echo ((`$v && `$v['user_id'] === 123) ? 'ok' : 'fail') . PHP_EOL;"
```

## 8. Manual app checks

After deployment, test these flows from the mobile app:

- Log in and confirm the response includes `token`.
- Add a listing with images.
- View listing images from list and detail screens.
- Update a listing with new images.
- Start a rental payment and verify Paystack amount.
- Issue asset, confirm receipt, return asset, and confirm return.

## 9. Git hygiene

Before committing or deploying from Git:

```powershell
git status --short
git check-ignore -v apps/.env
git check-ignore -v uploads/asset_images/1/example.jpg
```

Confirm:
- `apps/.env` is ignored.
- Generated upload files are ignored.
- `apps/.env.example` is tracked.
- `uploads/asset_images/.htaccess` is tracked.
