import React from 'react'
import ReactDOM from 'react-dom/client'
import App from './App'
import './index.css'

// Non-null assertion is safe here — Vite's index.html always provides #root.
ReactDOM.createRoot(document.getElementById('root')!).render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
)