# Banner Test 3

## Scope

Utility and fallback behavior verification.

Covered areas:

- duplicate banner feature
- hide-carousel fallback mode
- latest-banner fallback mode
- cleanup and default-state recovery

## Test Date

2026-05-08

## Environment

- Homepage URL: `http://localhost/wordpress/`
- Temporary banner test data from `banner_test1.md`

## Commands Run

### 1. Duplicate banner verification

Used reflection to invoke the duplicate method on banner `153`.

Observed output:

```text
DUPLICATE_ID=157
DUPLICATE_STATUS=draft
DUPLICATE_TITLE=Copy of Banner QA Live One
DUPLICATE_ORDER=1
DUPLICATE_TARGET=49
```

Interpretation:

- duplicate banner was created successfully
- duplicate was stored as draft
- important metadata was copied correctly

### 2. Hide-carousel fallback verification

Banner settings were switched to:

- `enabled = 0`
- `fallback_mode = hide_carousel`

Homepage checks returned:

```text
STATUS=200
HAS_SWIPER_MARKUP=False
HAS_HERO_RIGHT=False
HAS_NO_BANNER_CLASS=True
HAS_LIVE_ONE=False
```

Interpretation:

- hero-right carousel area was correctly removed
- hero container switched to the no-banner layout
- test banners were not rendered

### 3. Latest-banner fallback verification

Banner settings were switched to:

- `enabled = 0`
- `fallback_mode = latest_banner`
- `show_navigation = 0`
- `show_pagination = 1`

Observed payload output after improving the fallback selection logic:

```text
PAYLOAD_MODE=dynamic
COUNT=1
BANNER=Banner QA Live One
```

Homepage checks returned:

```text
HAS_DYNAMIC_TITLE=True
HAS_INACTIVE_TITLE=False
HAS_SWIPER_MARKUP=True
HAS_NAV_MARKUP=False
HAS_PAGINATION_MARKUP=True
```

Interpretation:

- latest fallback now prefers a usable active banner
- inactive banner was no longer selected
- navigation and pagination settings were reflected correctly

### 4. Cleanup verification

Temporary test banners `152` to `157` were deleted and settings were restored to:

- `enabled = 1`
- `fallback_mode = static_slides`

Final homepage verification:

```text
STATUS=200
HAS_QA_BANNERS=False
HAS_STATIC_SLIDE=True
HAS_DYNAMIC_CLASS=False
```

Interpretation:

- temporary test content was removed successfully
- homepage returned to clean fallback state
- existing static slides still render correctly

## Result

`PASS`

## Notes

- During this test, the original latest-banner fallback behavior exposed a logic issue by selecting the newest complete banner even when inactive.
- That logic was corrected so fallback now prefers active, usable content first.
