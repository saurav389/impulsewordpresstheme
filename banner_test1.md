# Banner Test 1

## Scope

Foundation and admin data-model verification for the new banner carousel feature.

Covered areas:

- custom post type registration
- settings storage
- active/inactive banner filtering
- schedule filtering
- banner ordering
- link-priority resolution

## Test Date

2026-05-08

## Environment

- WordPress local URL: `http://localhost/wordpress/`
- Theme: `impulse-academy-clone`
- PHP binary: `C:\xampp\php\php.exe`

## Test Setup

Temporary published test banners were created with existing media attachment `59` and target page `49` (`Contact Us`).

Created test banner IDs:

- `152` `Banner QA Live Two`
- `153` `Banner QA Live One`
- `154` `Banner QA Scheduled`
- `155` `Banner QA Expired`
- `156` `Banner QA Inactive`

## Commands Run

### 1. Syntax validation

```powershell
C:\xampp\php\php.exe -l .\inc\class-impulse-banner-manager.php
C:\xampp\php\php.exe -l .\functions.php
```

### 2. Data-model and query verification

```php
$payload = Impulse_Clone_Banner_Manager::get_homepage_payload();
$active_banners = Impulse_Clone_Banner_Manager::get_active_banners();
```

### 3. Link-priority verification

Banner `153` was given both:

- internal page ID `49`
- external URL `https://example.com/summer-camp`

Then this was checked:

```php
$link = Impulse_Clone_Banner_Manager::get_banner_link(153);
```

## Results

### Syntax validation

- `No syntax errors detected in .\inc\class-impulse-banner-manager.php`
- `No syntax errors detected in .\functions.php`

### Active banner query

Observed output:

```text
CREATED=152,153,154,155,156
PAYLOAD_MODE=dynamic
ACTIVE_COUNT=2
ACTIVE=153|Banner QA Live One|1|Live
ACTIVE=152|Banner QA Live Two|2|Live
```

Interpretation:

- only the 2 live banners were returned
- scheduled banner `154` was correctly excluded
- expired banner `155` was correctly excluded
- inactive banner `156` was correctly excluded
- ordering was correct: order `1` appeared before order `2`

### Link priority

Observed output:

```text
RESOLVED_URL=http://localhost/wordpress/contact-us/
RESOLVED_LABEL=Contact Us
```

Interpretation:

- internal page link correctly took priority over external URL

## Result

`PASS`

## Notes

- The foundation layer for banner management is working correctly.
- Query logic, scheduling, and ordering all behaved as designed.
