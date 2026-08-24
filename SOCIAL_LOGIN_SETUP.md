# Social Login — Provider Console Setup

This is the manual configuration required in Google Cloud Console and Meta for
Developers so that the Google/Facebook OAuth flow works end-to-end. Code and
route changes are already in the repo; this file is the one-time console
checklist.

The `.env` and `backend/.env` variables consumed here are defined in
[.env](.env) and [backend/config/services.php](backend/config/services.php).

## Google Cloud Console

Project: your existing OAuth 2.0 Client (Client ID
`526819845510-3113hpi5q1512cu628p9q0civ7dajtem.apps.googleusercontent.com`).

1. Open **APIs & Services → Credentials → OAuth 2.0 Client IDs → (the client above)**.
2. Under **Authorized JavaScript origins**, add:
   - `https://ratemyestate.castledigital.com.au`
   - `https://ratemyestatecms-react.castledigital.com.au`
   - `http://localhost:8000` (only if you develop locally)
3. Under **Authorized redirect URIs**, add:
   - `https://ratemyestate.castledigital.com.au/login/google/callback` — public site
   - `https://ratemyestate.castledigital.com.au/cms/auth/google/callback` — React CMS
   - `http://localhost:8000/login/google/callback` (only if you develop locally)
4. Under **OAuth consent screen**, confirm the app is **In production** (not
   Testing) so real users can sign in without being on an allowlist. Scopes
   should include `openid`, `profile`, `email` (all default).

## Facebook — Meta for Developers

App: `2026027518059672` (from `FACEBOOK_CLIENT_ID`).

1. Open **App Dashboard → Products → Facebook Login → Settings**.
2. Set **Valid OAuth Redirect URIs** to:
   - `https://ratemyestate.castledigital.com.au/login/facebook/callback`
   - `https://ratemyestate.castledigital.com.au/cms/auth/facebook/callback`
3. In **Settings → Basic**:
   - **App Domains**: `ratemyestate.castledigital.com.au`
   - **Site URL**: `https://ratemyestate.castledigital.com.au/`
4. Toggle the app to **Live** mode (top of dashboard). `public_profile` and
   `email` are default permissions and need no App Review.

## After changes go live

1. On the server, edit `backend/.env` (never commit) and fill in the credentials
   plus these variables:
   ```
   GOOGLE_CLIENT_ID=526819845510-3113hpi5q1512cu628p9q0civ7dajtem.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=<current secret from Google Cloud Console>
   GOOGLE_REDIRECT=https://ratemyestate.castledigital.com.au/login/google/callback

   FACEBOOK_CLIENT_ID=2026027518059672
   FACEBOOK_CLIENT_SECRET="<current secret from Meta>"
   FACEBOOK_REDIRECT=https://ratemyestate.castledigital.com.au/login/facebook/callback

   CMS_URL=https://ratemyestatecms-react.castledigital.com.au
   ```
2. Run `docker-compose exec php php artisan config:clear && php artisan config:cache`.
3. Run `docker-compose exec php php artisan migrate --force` to apply the
   `social_facebook_accounts` hardening migration.

## Smoke test checklist

Run through these after deploy. Each item should succeed without a 500 page and
each error path should land back on `/login` with a specific toast (public site)
or `?social_error=<code>` (CMS).

Public Blade site (`https://ratemyestate.castledigital.com.au/login`):

- [ ] Click the Google icon. Sign in with a **fresh** Google account whose email
      is not in the DB. Verify: redirected to `/`, `users.email_verified_at` is
      set, `social_facebook_accounts` has a `(google, provider_user_id)` row,
      role is `reviewer`.
- [ ] Same account again — verify no duplicate row was inserted (unique index
      does its job), still logged in.
- [ ] Click Facebook icon. Sign in with a fresh Facebook account. Same asserts
      as above with `provider = facebook`.
- [ ] Start a Google sign-in but click **Cancel** at the consent screen. Verify
      landing on `/login` with the toast "Sign-in cancelled."
- [ ] Manually navigate the browser to `login/google/callback` without going
      through the redirect first. Verify the toast "Your sign-in session
      expired" — this is the InvalidStateException path.
- [ ] Register a user with password `foo@example.com`, verify email, log out.
      Then Google-sign-in as `foo@example.com`. Should link silently and log
      in. Verify `social_facebook_accounts` now has a linked row for that user.
- [ ] Register a user `bar@example.com` and do **not** verify the email.
      Google-sign-in as `bar@example.com`. Verify the toast "An account with
      this email already exists…" — this is the `email_taken` guard.
- [ ] Verify `storage/logs/laravel.log` has entries like `Social login refused`
      and `Social login provider error` where appropriate.

React CMS (`https://ratemyestatecms-react.castledigital.com.au/login`):

- [ ] Precondition: an admin user exists in the DB (`admin@ratemyestate.com.au`
      per `AdminUserSeeder`).
- [ ] Sign the admin's Google account into Google in another tab. On the CMS
      login page click "Continue with Google". Verify redirect to
      `/auth/social-callback#...` then to `/dashboard`, and that
      `localStorage.rme-auth` now has `token` and `user.roles[].name === 'admin'`.
- [ ] Sign out. Click Continue with Google using a **non-admin** Google account
      (or any account not in `users`). Verify redirect to `/login?social_error=no_account`
      (or `not_admin` if the account exists but lacks the role) and the
      corresponding toast.
- [ ] Cancel the Google consent. Verify `?social_error=cancelled` toast.
- [ ] Same three scenarios repeated for Facebook.
- [ ] Refresh a page after a successful CMS social login. Verify the bearer
      token is still valid (existing `/auth/logout` and any authed endpoint
      work).

## Security

The Google client secret and Facebook app secret were shared in chat. Rotate
both in their respective consoles once this rollout is verified and update
`backend/.env` with the new values.
