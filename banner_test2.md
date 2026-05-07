# Banner Test 2

## Scope

Homepage frontend rendering verification for dynamic banner display inside the hero carousel.

Covered areas:

- dynamic slide output on homepage
- filtering of non-visible banners
- CTA link output
- slider configuration data output

## Test Date

2026-05-08

## Environment

- Homepage URL: `http://localhost/wordpress/`
- Temporary banner test data from `banner_test1.md`

## Commands Run

### 1. Template syntax validation

```powershell
C:\xampp\php\php.exe -l .\index.php
C:\xampp\php\php.exe -l .\footer.php
```

### 2. Frontend fetch verification

```powershell
Invoke-WebRequest -Uri 'http://localhost/wordpress/' -UseBasicParsing
```

Checked for these conditions:

- homepage returns `200`
- live banners appear
- scheduled / expired / inactive banners do not appear
- dynamic banner class is present
- target page link is present
- Swiper delay setting is present

## Results

### Syntax validation

- `No syntax errors detected in .\index.php`
- `No syntax errors detected in .\footer.php`

### Homepage rendering

Observed output:

```text
STATUS=200
HAS_LIVE_ONE=True
HAS_LIVE_TWO=True
HAS_SCHEDULED=False
HAS_EXPIRED=False
HAS_INACTIVE=False
HAS_DYNAMIC_CLASS=True
HAS_CONTACT_LINK=True
HAS_SWIPER_DELAY=True
```

Interpretation:

- homepage successfully rendered the dynamic banner slides
- only valid live banners were shown
- filtered banners stayed out of the carousel
- banner link output correctly pointed to the selected detail page
- frontend slider settings were passed into the markup

## Result

`PASS`

## Notes

- The hero carousel now supports dynamic banner replacement while preserving the existing layout.
- The static carousel remains available as fallback when no usable banners exist.
