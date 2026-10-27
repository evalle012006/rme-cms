# RME-173 — Public website copy: CMS-editable vs still hardcoded

Audit of the **public website** served by Laravel (`backend/`) against the **React Admin Portal** (`cms/`). The Angular `admin/` folder is not used for this check.

**Ticket context:** live website copy does not appear in the Admin Portal CMS, so the client cannot edit it. RME-173 expected existing front-end copy to be pulled into CMS and made editable.

**Why this happens:** the React CMS can edit `cms_page_sections` rows. The seeder only creates **empty** section shells (name + slug). The original Blade copy was commented out as `CMS fallback (kept for later restore)` instead of being imported into the database. Anything that was never given a CMS section is still hardcoded in Blade.

---

## 1. What the React CMS can edit (RME-173 coverage)

These nine pages have a CMS screen in `cms/` and are wired into the public site via `CmsGeneralViewComposer`.

| Public URL | CMS screen (`cms/`) | Editable fields |
|---|---|---|
| `/` Home | CMS → Home | Banner title + image; Review ratio title + two percentages; Why write a review title, body, image; Recent reviews title + body; Our blogs title + body |
| `/about` | CMS → About | Banner title + image; Get to know title, body, image; Our mission title + 4 cards; Our platform title, body, image; Recent reviews title + body |
| `/property-guide` | CMS → Property Guide | Banner title + image only |
| `/property-blog` | CMS → Property Blog | Banner title + image only |
| `/contact` | CMS → Contact | Banner title + image only |
| `/reviews-listing` | CMS → Reviews Listing | Banner title + image only |
| `/review-guidelines` | CMS → Review Guidelines | Banner + 5 body sections (titles and HTML bodies) |
| `/terms-of-use` | CMS → Terms of Use | Banner + General, Copyright, Disclaimer |
| `/privacy-policy` | CMS → Privacy Policy | Banner + Privacy commitment, Definitions, Collecting info |

**Separate from page CMS (dynamic, but not “static page copy”):**

- Blog posts and blog categories — Blogs module
- Reviews, projects, companies, tags, ratings, categories — their own modules
- Header review-category submenu and footer category list — Project Category module

If a CMS section field is empty, that heading/body is **blank** on the public site (fallbacks are commented out). The strings below still show because they are hardcoded.

---

## 2. Main list — still hardcoded on the public website

This is the client-visible copy the Admin Portal **cannot** change.

### Site-wide header (`layouts/app-header.blade.php`)

- Contact email: `admin@ratemyestate.com.au`
- Button: `+ For Companies`
- Nav labels: Home, About, Property Guide, Property Blog, Contact, Reviews
- Buttons: Write a review, Login, Signup, My Profile, Logout
- Search placeholder: `Search`

### Site-wide footer (`layouts/app-footer.blade.php`)

- Heading: `About us`
- About paragraph: *“Ratemyestate is a review platform developed to create a community that allows buyers to tell their story…”*
- Button: `Read More`
- Headings: `Categories`, `Quick Links`, `Contact Us`, `Follow Us`
- Quick link labels: Property Guide, Property Blog, Review Guidelines, Terms Of Use, Privacy Policy
- Contact email: `admin@ratemyestate.com.au`
- Facebook and Instagram URLs
- Copyright: `Copyright © 2020 All Rights Reserved By ratemyestate`

---

### Home `/` — hardcoded even when CMS Home is filled

| Location | Hardcoded copy |
|---|---|
| Search box | Placeholder `Search brand or projects` |
| Review ratio labels | `Trust online reviews`, `Validate advise` |
| Why write a review | Button `Write A Review` |
| Recent reviews | Heading `Recent Reviews` (the CMS field is the **subtitle/body**, not this H2) |
| Recent reviews | Button `Show all Reviews` |
| Map section | Entire block: heading `Seek Reviews by Location` |
| Category section | Entire block: heading `Browse reviews by category` |
| Category fallback | `There are many variations of passages of Lorem Ipsum available` (when a category has no description) |
| Blogs eyebrow | `Read our Blog` |
| Blogs | Button `Show all Stories` |
| Empty state | `No Recent Reviews Found..` |
| Default hero image | `images/homepage/HomepageHero.jpg` if no CMS image |

---

### About `/about` — hardcoded even when CMS About is filled

| Location | Hardcoded copy |
|---|---|
| Breadcrumb | `Home / About Us` |
| Side label | `Get to know about us` |
| Section heading | `Get to know` (CMS only edits the title **under** this) |
| Section heading | `Our mission` (CMS only edits the title **under** this) |
| Mission icons | Four static images (`start-icon1.png` … `start-icon4.png`) |
| Recent reviews | Heading `Recent Reviews` |
| Recent reviews | Button `Show all Reviews` |
| Empty state | `No Recent Reviews Found..` |
| Page title (meta) | `About Us` |

---

### Property Guide `/property-guide` — CMS = banner only

| Location | Hardcoded copy |
|---|---|
| Results label | `Best Results for: All` |
| Empty list | `No projects found` |
| Map search | Placeholder `search here ...` |
| Map cards | Labels `Company name :`, `Overall Rating :` |

The map, project list, and all UI chrome are hardcoded.

---

### Property Blog `/property-blog` — CMS = banner only

| Location | Hardcoded copy |
|---|---|
| Breadcrumb | `Home / Property Blog` |
| Section headings | `Popular Blogs`, `Finance`, `Building`, `Investment`, `Buying` |
| Buttons | `Show All Blogs`, `Learn More` |

Blog **articles** themselves are editable in Blogs. The page chrome and section titles are not.

---

### Contact `/contact` — CMS = banner only

| Location | Hardcoded copy |
|---|---|
| Breadcrumb | `Home / Contact Us` |
| Form heading | `Send Us a Message` |
| Form labels | First Name, Last Name, Email, Subject, Message |
| Form placeholders | `John`, `Smith`, `Johnsmith@ratemyestate.com`, `Business Talk`, `Hello, Do you have a moment to talk about....` |
| Info heading | `We are Located At:` |
| Email | `admin@ratemyestate.com.au` |
| Social links | Facebook and Instagram URLs |
| Meta title/description | Hardcoded |

---

### Reviews Listing `/reviews-listing` — CMS = banner only

| Location | Hardcoded copy |
|---|---|
| Breadcrumb | `Home / Reviews Listing` |
| Page heading | `Reviews Listing` |
| Filters | `View by:`, `Filter by:`, `Advanced Search`, `Sort Category`, `Sort By Ratings`, `Sort By Review Tags` |
| Placeholders | `Search by Suburb`, `Search by Project Name`, `Search by Company Name` |
| Button | `Search` |

---

### Review Guidelines `/review-guidelines`

- Breadcrumb: `Home / Review Guidelines`
- Body sections **are** CMS-editable (if the DB rows have content)
- Original live guidelines copy still sits in commented Blade fallbacks and was **not** seeded into CMS

---

### Terms of Use `/terms-of-use`

- Breadcrumb: `Home / Terms Of Use`
- General / Copyright / Disclaimer **are** CMS-editable (if populated)
- Original legal copy is in commented Blade fallbacks, **not** seeded into CMS

---

### Privacy Policy `/privacy-policy`

- Breadcrumb: `Home / Privacy Policy`
- Three body sections **are** CMS-editable (if populated)
- Original privacy copy is in commented Blade fallbacks, **not** seeded into CMS

---

### Pages with no CMS page at all (entirely hardcoded chrome)

These have **no** screen under CMS in the React admin. User-generated records (reviews, blogs, companies) still come from the database.

| Public URL | Hardcoded page copy (examples) |
|---|---|
| `/review-listing` | Banner `Review Listing`; heading `Recent Reviews`; subtitle `Buy with Confidence` |
| `/blog-posts` | Banner `Popular Blog Post`; breadcrumb `Popular Blog Post` |
| `/blog-detail/{slug}` | Fixed hero image; labels such as Property Blog breadcrumb. Article body is from Blogs, not page CMS |
| `/search` | Headings `Search`, `Projects`, `Reviews`, `Blogs`; button `Search` |
| `/review-step-1` | Banner `Write your Review`; `Write your review by Categories`; step labels Basic Details / Your Review / Submit Review; Lorem Ipsum category fallback |
| `/review-step-2` and `/review-step-2-1` | Banner `Write a Review`; form labels (`Title of Review`, `Rate Your Experience`, etc.) |
| `/review-step-3` | Banner `Write a Review`; `Thank you for sharing`; `Your review will be pending until it is approved by admin.` |
| `/login` | `Login to ratemyestate.com.au`; `Or use your email account`; `Register for Business Portal`; `Sign up to be kept up to date on the upcoming business solutions.` |
| `/register` | `Already have an account? Log in.`; `To keep connected with us Please log in with your personal info`; `Few steps ahead to join us`; `Or use your email for registration` |
| `/builder/register` | Company registration form labels and intro copy |
| `/password/reset` and related auth | Reset / forgot-password headings and helper text |
| `/my-profile`, `/my-review` | Profile and “my reviews” chrome |
| `/builder/dashboard`, `/builder/my-project`, `/builder/company-users` | Builder portal chrome |
| `/property/{slug}` (company / brand page) | Layout labels around company name, ratings, projects |
| `/property/{category}/{slug}` | Same for category-filtered brand pages |
| `/property-review/{slug}` | Labels such as `Rated Experience`, `Overall Ratings`, `Reviews` |
| `/review/{slug}` | Individual review page chrome |

---

## 3. Original live copy that never landed in CMS

These strings are the current (or previous) front-end copy. They live only in **commented Blade fallbacks**, not in `cms_page_sections`. That is why the Admin Portal fields look empty while the old website copy is known.

### Home

- Banner: `Seek Reviews. Buy with Confidence.`
- Review ratio: `Buying a home doesn’t have to be a mystery.` / `88%` / `89%`
- Why write a review: `WHY WRITE A REVIEW. Be part of a community that is doing good.` plus the three bullet points
- Recent reviews subtitle: `Buy with Confidence` and *“Your home is one of the most expensive assets you will own…”*
- Our blogs: `Property Blog` / `Stay updated with the latest property news, content and insights`

### About

- Banner: `About Us`
- Get to know: `About RateMyEstate` plus the “artist impression” / “hand over hundreds of thousands” paragraphs
- Mission cards: the four original mission paragraphs
- Our platform: `Be Confident` plus the two “search the reviews / be a hero” paragraphs
- Recent reviews: same `Buy with Confidence` block as Home

### Other CMS pages (banner fallbacks)

- Property Guide → `Property Guide`
- Property Blog → `Property Blog`
- Contact → `Contact Us`
- Reviews Listing → `Reviews Listing`
- Review Guidelines / Terms / Privacy → full original legal and guidelines text in commented fallbacks

---

## 4. Coverage summary for RME-173

| Page | In React CMS? | Front-end uses CMS? | Live copy imported into CMS? | Still hardcoded? |
|---|---|---|---|---|
| Home | Yes | Partial | No (empty shells + commented fallbacks) | Yes — map, categories, labels, buttons, header/footer |
| About | Yes | Partial | No | Yes — section eyebrows, breadcrumbs, recent-reviews H2 |
| Property Guide | Yes | Banner only | No | Yes — all map/list UI |
| Property Blog | Yes | Banner only | No | Yes — Popular / Finance / Building / Investment / Buying |
| Contact | Yes | Banner only | No | Yes — form, address, email, social |
| Reviews Listing | Yes | Banner only | No | Yes — heading, filters, placeholders |
| Review Guidelines | Yes | Yes (if DB filled) | No | Breadcrumb + empty CMS until copy is imported |
| Terms of Use | Yes | Yes (if DB filled) | No | Breadcrumb + empty CMS until copy is imported |
| Privacy Policy | Yes | Yes (if DB filled) | No | Breadcrumb + empty CMS until copy is imported |
| Write-a-review steps | No | No | n/a | Entire page |
| Login / Register | No | No | n/a | Entire page |
| Search | No | No | n/a | Entire page |
| Blog posts listing / detail | No page CMS | Blogs module for articles | n/a | Banners and section chrome |
| Header / Footer | No | No | n/a | Entire chrome |

**RME-173 is incomplete.** Screens exist for the nine static pages, but:

1. Existing website copy was not pulled into the CMS database.
2. Several high-visibility blocks on Home, About, Contact, Property Blog, Property Guide, and Reviews Listing were never given CMS fields.
3. Header, footer, review flow, auth, and search were never in scope.

---

## 5. Suggested next work (not done in this audit)

1. Import the commented Blade fallback copy into `cms_page_sections` so the React CMS shows the live text.
2. Add CMS sections (or a global settings page) for header email, footer about text, footer copyright, contact email/social, and the Home map/category/blog eyebrows.
3. Decide whether write-a-review, login/register, and search copy should be CMS-managed or stay as product UI.
