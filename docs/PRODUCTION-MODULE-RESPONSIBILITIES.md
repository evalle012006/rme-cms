# RateMyEstate — Production Module Responsibilities

**Client:** Castle Technology Solutions  
**Last updated:** August 2026

This document describes **what each part of the platform shows** and **what actions users can perform**. It is written for business and operational use, not for developers.

---

## 1. Platform Overview

RateMyEstate is a property review platform where home buyers submit reviews of developments, builders manage their company profiles and properties, and administrators moderate content through an admin portal.


| Application    | URL                                                                                                      |
| -------------- | -------------------------------------------------------------------------------------------------------- |
| Public website | [https://ratemyestate.castledigital.com.au](https://ratemyestate.castledigital.com.au)                   |
| Admin portal   | [https://ratemyestatecms-react.castledigital.com.au](https://ratemyestatecms-react.castledigital.com.au) |


The platform has three main areas:

- **Public website** — where visitors browse reviews and reviewers submit feedback
- **Builder portal** — where builder/company users manage their business on the site
- **Admin portal** — where administrators approve content and manage the platform

---

## 2. User Roles & Access Boundaries


| Role               | Where they use the platform     | What they can do                                                            |
| ------------------ | ------------------------------- | --------------------------------------------------------------------------- |
| **Admin**          | Admin portal                    | Manage all content, approve submissions, edit pages, view reports           |
| **Reviewer**       | Public website                  | Register, submit property reviews, manage personal profile                  |
| **Builder**        | Public website (builder portal) | Manage company profile, properties, advertisements, team users, and reports |
| **Public visitor** | Public website                  | Browse reviews, properties, blogs, and static pages without logging in      |


---

## 3. High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        PLATFORM USERS                            │
├──────────────────┬──────────────────────┬───────────────────────┤
│  Public visitors │  Reviewers/Builders  │       Admins          │
│  (browse/review) │  (login + portal)    │  (admin portal)       │
└────────┬─────────┴──────────┬───────────┴───────────┬───────────┘
         │                    │                       │
         ▼                    ▼                       ▼
┌─────────────────────────────────────────────────────────────────┐
│                     PUBLIC WEBSITE                               │
│         (homepage, reviews, blogs, builder portal)               │
└──────────────────────────────┬──────────────────────────────────┘
                               │
                               ▼
                    ┌──────────────────────┐
                    │    ADMIN PORTAL      │
                    │  (content management)│
                    └──────────────────────┘
```

---

## 4. Public Website — What Is Shown & What Users Can Do

### 4.1 Homepage

**What is shown**

- Recent approved property reviews
- Recent blog posts from Property Blog
- Property categories
- Map of review locations grouped by suburb

**Actions users can perform**

- Browse featured reviews and blogs
- Navigate to property listings, review submission, or other site sections

---

### 4.2 Property & Review Listings

**What is shown**

- List of properties that have approved reviews
- Search and filter by company, location, tags, and other criteria
- Review counts and summary information per property

**Actions users can perform**

- Search and filter properties
- Open a property detail page
- Open individual review detail pages

---

### 4.3 Property & Review Detail Pages

**What is shown**

- Property name, developer/company, logo, and gallery images
- All approved reviews for that property
- Ratings, tags, comments, and media attached to each review
- Linked company information

**Actions users can perform**

- Read full review content
- Navigate between related properties and companies

---

### 4.4 Submit a Review (Reviewers)

**What is shown**

- A guided 3-step review form:
  - **Step 1:** Select or add property and company, location, buyer type, and property category
  - **Step 2:** Rate the property on configured attributes, select tags, and add comments
  - **Step 3:** Upload photos/documents and complete the submission

**Actions users can perform**

- Save progress while moving through the steps
- Submit a review for admin approval
- Receive email confirmation when the review is approved

*Only logged-in reviewers can submit reviews.*

---

### 4.5 User Account & Profile (Reviewers)

**What is shown**

- Personal profile details (name, email, photo, location)
- List of the reviewer’s own submitted reviews

**Actions users can perform**

- Register and log in (email/password or Google/Facebook)
- Verify email address and reset password
- Update profile photo and personal details
- Request a change to public display name (requires admin approval)
- View past reviews

---

### 4.6 Property Blog & Blogs

**What is shown**

- Property Blog hub with blog posts grouped by category (e.g. Finance, Buying, Investment, Building)
- Individual blog article pages
- Blog listings and featured posts on the homepage

**Actions users can perform**

- Browse articles by category
- Read full blog posts

---

### 4.7 Static & Informational Pages

**What is shown**

- **About** — company/platform information
- **Contact** — contact details and enquiry form
- **Property Guide** — buyer guidance content
- **Reviews Listing** — curated listing page content
- **Review Guidelines** — rules for submitting reviews
- **Privacy Policy** and **Terms of Use** — legal pages

**Actions users can perform**

- Read informational content
- Submit a contact enquiry via the contact form

---

### 4.8 Site Search

**What is shown**

- Search results across properties, companies, and reviews

**Actions users can perform**

- Search the site from the general search feature

---

## 5. Builder Portal — What Is Shown & What Builders Can Do

*Available to logged-in builder/company users on the public website.*

### 5.1 Builder Dashboard

**What is shown**

- Overview of review activity for the builder’s company
- Review counts and charts over time
- Performance summaries by property

**Actions builders can perform**

- View review trends and statistics
- Filter data by date range and property
- Export company and review reports to Excel

---

### 5.2 Company Profile

**What is shown**

- Company name, logo, bio, website, and contact details as shown on the public site

**Actions builders can perform**

- Update company bio and logo
- Edit public company information

---

### 5.3 Properties

**What is shown**

- List of properties linked to the builder’s company
- Review counts per property

**Actions builders can perform**

- Add new properties
- Update property details and logos
- View reviews linked to each property
- Export reviews for a specific property

*New properties require admin approval before appearing on the public site.*

---

### 5.4 Advertisements

**What is shown**

- Advertisements submitted for the builder’s properties

**Actions builders can perform**

- Create new advertisements with title, description, and image
- Submit ads for admin approval

---

### 5.5 Team Users

**What is shown**

- Users linked to the builder’s company

**Actions builders can perform**

- Add new team users to the company account
- Manage company user access

---

### 5.6 Builder Registration

**What is shown**

- Registration form for new builder/company accounts

**Actions builders can perform**

- Register as a builder/company user
- Wait for admin approval before full portal access

---

## 6. Admin Portal — What Is Shown & What Admins Can Do

### 6.1 Dashboard

**What is shown**

- Total counts of companies, properties, reviews, and reviewers
- Charts showing review activity over time
- Charts breaking down reviews by rating attribute and by property

**Actions admins can perform**

- Monitor platform activity
- Filter charts by date, property, and rating attributes
- Export dashboard data to CSV

---

### 6.2 Users (Reviewers)

**What is shown**

- List of all reviewer accounts
- User profile details and account status
- Pending display-name change requests

**Actions admins can perform**

- Add new reviewer accounts
- Edit reviewer profiles
- Activate or deactivate users
- Approve or reject display-name change requests

---

### 6.3 Company Users (Builders)

**What is shown**

- Separate lists of approved and pending builder accounts
- Builder profile and linked company details

**Actions admins can perform**

- Add new company user (builder/developer) accounts from CMS
- Approve or reject new builder registrations
- Edit builder profiles and company assignments

---

### 6.4 Companies

**What is shown**

- Separate lists of approved and pending companies
- Company name, logo, bio, website, and contact information

**Actions admins can perform**

- Add new companies manually
- Edit company profiles
- Approve or reject pending companies
- Delete companies

---

### 6.5 Reviews

**What is shown**

- Separate lists of approved and pending reviews
- Full review detail including property, reviewer, ratings, tags, and uploaded media

**Actions admins can perform**

- Approve or reject submitted reviews
- Edit review content before or after approval
- Remove reviews

*Approved reviews appear on the public website; pending reviews do not.*

---

### 6.6 Properties

**What is shown**

- Separate lists of approved and pending properties
- Property details, gallery images, linked company, and categories

**Actions admins can perform**

- Add new properties
- Edit property details and images
- Approve or reject pending properties
- Delete properties
- Import multiple properties from an Excel file

---

### 6.7 Advertisements

**What is shown**

- Separate lists of approved and pending advertisements
- Ad title, description, image, and linked property

**Actions admins can perform**

- Approve or reject builder-submitted advertisements

---

### 6.8 Property Settings

**What is shown**

- **Property Categories** — property types shown on the site (e.g. apartment, house-and-land)
- **Manage Rating** — which rating questions apply to each property category and in what order

**Actions admins can perform**

- Create, edit, and delete property categories
- Assign rating attributes to each category
- Control the order in which rating questions appear during review submission

---

### 6.9 Rating Settings

**What is shown**

- List of rating attributes used in reviews (e.g. location, design, value)
- Icon and colour associated with each rating

**Actions admins can perform**

- Add, edit, and delete rating attributes

---

### 6.10 Tags

**What is shown**

- List of tags reviewers can attach to reviews

**Actions admins can perform**

- Create, edit, activate, or deactivate tags
- Control which tags appear as filters on the public site

---

### 6.11 Blogs

**What is shown**

- Blog categories and blog posts
- Post title, content, feature image, and category

**Actions admins can perform**

- Manage blog categories
- Create, edit, and delete blog posts
- Publish content to the Property Blog section and homepage

---

### 6.12 CMS Pages (Website Content)

**What is shown**

- Editable content sections for key public pages:
  - Home
  - About
  - Property Guide
  - Property Coach
  - Contact
  - Reviews Listing
  - Review Guidelines
  - Terms of Use
  - Privacy Policy

**Actions admins can perform**

- Update banners, headings, descriptions, and other page sections
- Control the text and images visitors see on informational pages

---

### 6.13 Contact Inbox

**What is shown**

- Messages submitted through the public contact form

**Actions admins can perform**

- View and manage contact enquiries

---

### 6.14 Profile Settings

**What is shown**

- Logged-in admin’s own account details

**Actions admins can perform**

- Update personal profile
- Change password

---

## 7. Content Approval Process

Most user-generated content must be approved by an admin before it appears on the public website.


| Content type    | Submitted by           | Admin action required     | Visible on public site when |
| --------------- | ---------------------- | ------------------------- | --------------------------- |
| Review          | Reviewer               | Approve or reject         | Approved                    |
| Property        | Builder or review flow | Approve or reject         | Approved                    |
| Company         | Builder registration   | Approve or reject         | Approved                    |
| Builder account | Self-registration      | Approve or reject         | Approved                    |
| Advertisement   | Builder                | Approve or reject         | Approved                    |
| Blog post       | Admin                  | Publish from admin portal | Published                   |


Until content is approved, it remains hidden from public visitors.

---

## 8. Email Notifications

The platform sends automated emails for key events:

- **Account verification** — when a new user registers
- **Password reset** — when a user requests a password reset
- **Review submitted** — notifies admins when a new review is waiting for approval
- **Review approved** — notifies the reviewer when their review goes live
- **Company/builder registration** — notifies relevant parties when a builder or company registers
- **Builder approved** — notifies the builder when their account is approved
- **Display name change** — notifies admin and user when a display-name request is submitted or decided
- **User report** — when a builder reports a problematic user

---

## 9. Key Business Rules

- Only **reviewers** can submit property reviews
- Only **approved** reviews, properties, companies, and advertisements are shown publicly
- **Builders** can only manage content linked to their own companyer3
- **Admins** have full control over moderation, website content, and platform settings
- Review questions shown during submission depend on the **property category** selected
- Tags and rating attributes configured in the admin portal directly affect what reviewers see and select

---

*This document covers the public website and admin portal as used in production. It does not cover internal development, hosting, or the legacy admin panel.*