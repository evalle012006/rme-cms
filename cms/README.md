# RateMyEstate CMS — React

New admin panel built with React 18, Vite, Tailwind CSS, and Zustand.
Replaces the legacy Angular 9 admin panel.

## Stack

| Concern | Library |
|---------|---------|
| Framework | React 18 + Vite |
| Routing | React Router v6 |
| State | Zustand |
| Forms | React Hook Form + Zod |
| Tables | TanStack Table v8 |
| UI | Tailwind CSS |
| Charts | Recharts |
| File Upload | react-dropzone |
| HTTP | Axios with interceptors |
| Toast | react-hot-toast |

## Local Development

```bash
# Install Node 18+ (use nvm)
nvm install 18
nvm use 18

# Install dependencies
npm install

# Copy env file
cp .env.production.example .env.local
# Edit .env.local to point to your local Laravel backend

# Start dev server (runs on http://localhost:4221)
npm run dev
```

The Laravel backend must be running on `http://localhost:8000`.

## Building for Production

```bash
npm run build
```

Output goes to `dist/`. The deploy script copies this to `backend/public/admin/`.

## Folder Structure

```
src/
├── components/       # Shared UI components
│   ├── AuthGuard.jsx
│   ├── DefaultLayout.jsx
│   └── Sidebar.jsx
├── lib/
│   ├── api.js        # Axios instance with interceptors
│   └── utils.js      # Helper functions
├── pages/            # One folder per feature
│   ├── LoginPage.jsx
│   ├── DashboardPage.jsx
│   └── ...
├── store/
│   └── authStore.js  # Zustand auth state
├── App.jsx           # Router
├── main.jsx          # Entry point
└── index.css         # Global styles
```

## Adding a New Page

1. Create `src/pages/YourPage.jsx`
2. Add the route in `src/App.jsx`
3. Add the nav link in `src/components/Sidebar.jsx` if needed
