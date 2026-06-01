# RateMyEstate CMS — React Admin Portal

React 18 + Vite admin portal, replacing the legacy Angular 9 admin panel.
Communicates exclusively with the Laravel 6 backend API.

## Stack

| Concern        | Library                          |
| -------------- | -------------------------------- |
| Framework      | React 18 + Vite 8                |
| Language       | TypeScript (strict)              |
| Routing        | React Router v6                  |
| State          | Zustand                          |
| Forms          | React Hook Form + Zod            |
| Tables         | TanStack Table v8                |
| UI             | Tailwind CSS v4                  |
| Charts         | Recharts                         |
| Rich Text      | TipTap                           |
| File Upload    | react-dropzone                   |
| HTTP           | Axios with interceptors          |
| Toast          | Sonner                           |

## Prerequisites

- Node 18+ (use nvm: `nvm use 18`)
- Laravel backend running on `http://localhost:8000`

## Local Development

```bash
# Install dependencies
npm install

# Copy and configure environment
cp .env.example .env.local
# Edit VITE_API_URL in .env.local if needed

# Start dev server → http://localhost:4221
npm run dev
```

## Build

```bash
# Production build → dist/
npm run build

# Preview production build locally
npm run preview
```

## Code Quality

```bash
# Run all checks (type-check + lint + format)
npm run validate

# Individual checks
npm run type-check    # TypeScript — zero errors required
npm run lint          # ESLint — zero warnings required
npm run format:check  # Prettier — must be clean before committing

# Auto-fix
npm run lint:fix
npm run format
```

> CI enforces `npm run validate`. PRs with type errors, lint warnings, or
> formatting drift will not merge.

## Folder Structure

```
src/
├── features/           # One folder per domain (auth, users, companies, …)
│   ├── auth/
│   ├── dashboard/
│   ├── users/
│   ├── companies/
│   ├── properties/
│   ├── reviews/
│   ├── blogs/
│   ├── cms-pages/
│   └── settings/
├── components/
│   ├── layout/         # DefaultLayout, SimpleLayout, Sidebar, Header
│   ├── shared/         # DataTable, Modal, ConfirmDialog, etc.
│   └── ui/             # Atomic primitives: Button, Input, Badge, etc.
├── lib/
│   ├── api.ts          # Axios instance with auth interceptors
│   └── utils.ts        # Shared helper functions
├── store/
│   └── authStore.ts    # Zustand auth state (persisted)
├── hooks/              # Custom hooks (useDebounce, usePagination, …)
├── types/
│   └── api.ts          # Shared API response types
├── routes/             # Route definitions and AuthGuard
├── App.tsx             # Root router
├── main.tsx            # Entry point
└── index.css           # Tailwind base import + CSS vars
```

### Feature folder convention

Each feature owns its own components, hooks, and types:

```
features/properties/
├── components/
│   ├── PropertyList.tsx
│   └── PropertyForm.tsx
├── hooks/
│   └── useProperties.ts
├── types.ts
└── index.ts            # public API for the feature
```

Nothing outside a feature folder imports from inside it except via `index.ts`.

## Deploy

The deploy script (at the repo root) copies `dist/` into
`backend/public/admin/` so the Laravel server can serve the SPA.

```bash
# From monorepo root
bash deploy.sh
```

## Environment Variables

| Variable       | Default                    | Purpose              |
| -------------- | -------------------------- | -------------------- |
| `VITE_API_URL` | `http://localhost:8000/api` | Laravel API base URL |

Never commit `.env.local`. Use `.env.example` as the template.