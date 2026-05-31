# Nginx Server Block Setup
# Run these commands on the Ubuntu server

# =============================================================================
# 1. Create server block for the CMS admin domain
#    (ratemyestate.castledigital.com.au already exists — leave it untouched)
# =============================================================================

sudo nano /etc/nginx/sites-available/ratemyestatecms.castledigital.com.au

# Paste this content:
# ---
# server {
#     server_name ratemyestatecms.castledigital.com.au;
#
#     location / {
#         proxy_pass http://localhost:8001;
#         proxy_http_version 1.1;
#         proxy_set_header Upgrade $http_upgrade;
#         proxy_set_header Connection 'upgrade';
#         proxy_set_header Host $host;
#         proxy_set_header X-Real-IP $remote_addr;
#         proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
#         proxy_set_header X-Forwarded-Proto $scheme;
#         proxy_cache_bypass $http_upgrade;
#     }
#
#     listen 80;
# }
# ---

# =============================================================================
# 2. Update existing ratemyestate block to point to new container on port 8000
#    (only needed if switching from old rme-container setup)
# =============================================================================

sudo nano /etc/nginx/sites-available/ratemyestate.castledigital.com.au
# The proxy_pass http://localhost:8000 line stays the same — no change needed

# =============================================================================
# 3. Enable the new CMS site
# =============================================================================

sudo ln -s /etc/nginx/sites-available/ratemyestatecms.castledigital.com.au \
           /etc/nginx/sites-enabled/

# =============================================================================
# 4. Test and reload Nginx
# =============================================================================

sudo nginx -t && sudo systemctl reload nginx

# =============================================================================
# 5. Issue SSL certificate for the CMS domain
# =============================================================================

sudo certbot --nginx -d ratemyestatecms.castledigital.com.au
