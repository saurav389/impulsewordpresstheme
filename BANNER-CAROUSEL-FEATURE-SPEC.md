# Banner Carousel Feature Specification

## Purpose

Add a CMS-driven banner management feature for the homepage hero carousel so admins can create promotional banners such as:

- Summer Vacation Bootcamp
- New Course Launch
- Admission Open Campaign
- Scholarship Announcement
- Workshop / Seminar Promotion

The goal is to let non-technical admins manage banner slides from WordPress admin without editing code.

---

## Current Project Context

The homepage currently contains a hard-coded hero slider in [index.php](./index.php) inside the `premium-hero` section.

Current observations:

- The right side of the hero already uses a Swiper-based carousel structure.
- Slides are currently static HTML blocks.
- There is already an empty `<!-- Announcement Banner -->` placeholder near the top of the homepage.
- This makes the homepage a good candidate for a dynamic banner system instead of a separate custom frontend pattern.

Because the slider already exists, the recommended approach is:

1. Keep the current hero layout.
2. Replace hard-coded slide data with admin-managed banner records.
3. Preserve a fallback if no published banners exist.

---

## Primary Business Requirement

Admin should be able to:

1. Create a banner from WordPress admin.
2. Upload a poster/image for that banner.
3. Add a specific page link or custom URL for the banner.
4. Show the banner inside the homepage hero carousel.
5. Let visitors click the banner and open the related detail page.

---

## Recommended Architecture

### Option Recommended

Use a **custom post type** for banners.

Recommended post type:

- Slug: `ica_banner`
- Admin label: `Homepage Banners`

Why this is the best fit:

- Native WordPress admin UI
- Easy publish/draft workflow
- Supports featured image/media library
- Supports ordering and status
- Easy future expansion
- Cleaner than storing everything in one settings page

### Why not a single options page only

A settings-only approach becomes difficult when:

- multiple banners are needed
- banners need scheduling
- banners need ordering
- banners need draft/publish states
- banners need revision-like content management

---

## Banner Data Model

Each banner should contain the following fields.

### Required Fields

1. `Banner Title`
- Internal/admin title
- Also usable as visible heading if needed

2. `Banner Poster`
- Main image/poster for desktop display
- Recommended aspect ratio: landscape, optimized for hero slider

3. `Target Link`
- Link to a page with banner details
- Should support:
  - internal page/post selection
  - optional custom external URL

4. `Status`
- Draft / Published

### Strongly Recommended Fields

5. `Short Caption`
- Small visible text on the slide
- Example: `Summer Vacation Bootcamp 2026`

6. `CTA Text`
- Example: `View Details`, `Register Now`, `Learn More`

7. `Display Order`
- Numeric order to control slide sequence

8. `Start Date`
- Banner becomes visible from this date/time

9. `End Date`
- Banner hides automatically after this date/time

10. `Open in New Tab`
- Useful for external links

11. `Mobile Banner Image`
- Optional alternate poster for phones

12. `Banner Active Toggle`
- Quick on/off switch without moving to draft

### Optional Advanced Fields

13. `Badge Label`
- Example: `New`, `Limited Seats`, `Admissions Open`

14. `Background Overlay Color`
- To improve text readability on posters

15. `Button Style Variant`
- Primary / Secondary / Outline

16. `Text Alignment`
- Left / Center / Right

17. `Audience Tag`
- Example: students, parents, professionals

18. `Campaign Code`
- Useful later for analytics and reporting

---

## Admin Panel Requirements

### Admin Menu

Recommended admin location:

- Left menu item: `Homepage Banners`

Submenus:

- `All Banners`
- `Add New Banner`
- `Banner Settings`

### Banner List Screen

The admin list page should show:

- Banner poster thumbnail
- Banner title
- Target page / URL
- Status
- Active toggle
- Display order
- Start date
- End date
- Last updated date

Useful admin actions:

- Edit
- Quick disable
- Duplicate banner
- Trash

### Banner Edit Screen

The add/edit form should include:

- Banner title
- Short caption
- CTA text
- Desktop image upload
- Mobile image upload
- Internal page selector
- External URL field
- Toggle for opening in new tab
- Display order
- Start/end schedule
- Active toggle
- Publish controls
- Preview link

### Validation Rules

The system should validate:

- banner image is present before publish
- at least one valid destination exists
- if both internal page and external URL are provided, priority rules are clear
- end date cannot be earlier than start date
- invalid or broken URLs are rejected
- file type is restricted to safe image types

Recommended link priority:

1. Internal page link
2. External URL
3. No link if both are empty

---

## Frontend Feature Requirements

### Homepage Display

The homepage hero carousel should:

- load all active published banners
- sort banners by display order, then date
- ignore expired banners
- ignore future-scheduled banners
- ignore inactive banners
- ignore incomplete banners

### Slide Content Behavior

Each slide can support:

- poster image
- title/caption overlay
- CTA button
- clickable poster area

### Click Behavior

If target link exists:

- clicking the slide or CTA opens the linked detail page

If no target link exists:

- slide remains visible but non-clickable

### Mobile Behavior

On mobile:

- use mobile banner image if provided
- otherwise fallback to desktop image
- ensure text remains readable
- keep touch swipe enabled

### Fallback Behavior

If no active banners are available, choose one of these strategies:

#### Recommended fallback

Show the current hard-coded default hero slides.

Why:

- homepage never looks empty
- safe for launch
- avoids breaking the existing design

---

## Banner Settings Page

A small settings page is recommended for system-wide behavior.

Suggested settings:

1. `Enable Banner Carousel`
- master on/off switch

2. `Autoplay`
- enable / disable automatic sliding

3. `Autoplay Delay`
- e.g. 3000ms, 5000ms, 7000ms

4. `Loop Slides`
- enable / disable repeat loop

5. `Show Navigation Arrows`
- yes / no

6. `Show Pagination Dots`
- yes / no

7. `Pause on Hover`
- yes / no

8. `Fallback Mode`
- use default static slides
- hide carousel
- show latest published banner only

9. `Default CTA Text`
- used when a banner-specific CTA is empty

10. `Image Crop Recommendation Notice`
- admin helper text only

---

## Recommended Additional Features

These are not mandatory for version 1, but they are valuable enough to document now.

### 1. Scheduling

Very useful for time-based campaigns like:

- summer bootcamp
- festive offers
- admission deadlines

This prevents manual enable/disable work.

### 2. Duplicate Banner

Useful when re-running similar campaigns next year.

Example:

- duplicate `Summer Bootcamp 2026`
- update title to `Summer Bootcamp 2027`

### 3. Draft + Preview Workflow

Important when the team wants to review banners before publishing.

### 4. Internal Page Picker

Better than plain URL-only entry because:

- fewer typing mistakes
- easier admin experience
- safer linking

### 5. Analytics Hooks

Optional future feature:

- count impressions
- count clicks
- store campaign performance

### 6. Device-Specific Image

Very helpful because banner posters often crop badly on phones.

### 7. Banner Expiry Warning

Admin notice for banners nearing expiry.

### 8. Banner Preview in Admin

Thumbnail or mini-slide preview while editing.

### 9. Accessibility Support

Should be treated as required quality, even if simple:

- alt text for images
- keyboard navigation support
- readable contrast
- button labels

### 10. Reusable Placement Support

Future-ready idea:

- homepage hero
- top announcement strip
- course page sidebar banner
- popup campaign banner

Not required now, but good if data structure is designed cleanly.

---

## Feature Scope Recommendation

### Version 1: Must Have

These should be in the first implementation:

1. Custom post type for banners
2. Banner title
3. Desktop poster upload
4. Optional mobile poster upload
5. Internal page link or external URL
6. CTA text
7. Display order
8. Active toggle
9. Publish/draft support
10. Homepage hero carousel integration
11. Fallback to existing static slides

### Version 1.5: Very Useful

1. Start date / end date scheduling
2. Duplicate banner action
3. Global banner settings page
4. Preview in admin

### Version 2: Advanced

1. Analytics
2. Placement support beyond homepage
3. campaign reports
4. A/B testing or banner variants

---

## User Flow

### Admin Flow

1. Admin opens `Homepage Banners`.
2. Admin clicks `Add New`.
3. Admin enters title like `Summer Vacation Bootcamp 2026`.
4. Admin uploads poster image.
5. Admin selects a related detail page or inserts a URL.
6. Admin adds CTA text like `Register Now`.
7. Admin sets order and schedule.
8. Admin publishes the banner.
9. Banner appears automatically in homepage carousel.

### Visitor Flow

1. Visitor opens homepage.
2. Visitor sees banner slide in hero carousel.
3. Visitor clicks slide or button.
4. Visitor opens the linked details page.
5. Visitor reads details or registers.

---

## Technical Notes For Future Implementation

These notes are for implementation planning only.

### Content Source

Recommended source:

- custom post type + post meta

### Query Logic

Homepage should query only:

- published banners
- active banners
- currently valid by schedule
- sorted by order ascending

### Integration Point

The dynamic banner system should replace or feed the current slide area in:

- [index.php](./index.php)

Likely integration area:

- hero right-side Swiper slides

Possible secondary integration later:

- the announcement banner placeholder above the hero

### Media Handling

Use WordPress media library instead of custom file upload handling.

### Security

Implementation should include:

- nonce protection in admin save actions
- capability checks
- URL sanitization
- image sanitization through WordPress APIs
- escaped output in frontend templates

---

## SEO and UX Considerations

### SEO

If banners link to detail pages:

- those detail pages should have meaningful titles
- detail pages should have proper meta descriptions
- banner images should have alt text

### UX

Recommended UX rules:

- avoid too much text on the banner
- keep CTA visible
- do not allow unreadable text over noisy posters
- keep slide timing comfortable
- prioritize mobile readability

---

## Content Guidelines For Admin Team

Recommended banner content rules:

- Use one primary message per banner
- Use short title and short CTA
- Upload optimized image size
- Use a dedicated details page for each campaign
- Avoid adding too many active banners at once

Recommended maximum active homepage banners:

- 3 to 5 banners

---

## Risks If Implemented Poorly

1. Homepage becomes slow due to oversized images
2. Broken links if admins paste invalid URLs
3. Empty carousel if no fallback exists
4. Bad mobile cropping without mobile image support
5. Poor usability if banner ordering is unclear
6. Scheduling bugs if timezone is not handled correctly

---

## Acceptance Criteria

The banner feature will be considered successful when:

1. Admin can create a banner from wp-admin without code changes.
2. Banner can include a poster image.
3. Banner can link to a specific page or URL.
4. Published active banners appear in the homepage hero carousel.
5. Clicking a banner opens the correct details page.
6. Banner ordering is manageable from admin.
7. Homepage still works correctly when no banners are created.
8. Banner display works on desktop and mobile.

---

## Final Recommendation

For this project, the best approach is:

1. Build a `Homepage Banners` custom post type.
2. Use the existing hero carousel on the homepage as the display location.
3. Support image upload + page link as the core feature.
4. Include order, active toggle, and mobile image in version 1.
5. Add scheduling and settings page if you approve a slightly broader but much more practical feature scope.

---

## Suggested Approval Decision

If you want a balanced first implementation, approve this scope:

- Custom post type
- Image upload
- Internal page link / custom URL
- CTA text
- Display order
- Active toggle
- Optional mobile image
- Hero carousel integration
- Fallback to current static slides

If approved, the next step will be implementation planning and then code changes.
