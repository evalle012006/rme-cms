#!/bin/bash
# =============================================================================
# One-time server setup: add Nginx proxy + SSL for the React admin preview
# Run on the Ubuntu droplet as a user with sudo
# =============================================================================

DOMAIN="ratemyestatecms-react.castledigital.com.au"
CONF="/etc/nginx/sites-available/${DOMAIN}"

# 1. Write the server block
sudo tee "$CONF" > /dev/null << 'NGINX'
server {
    server_name ratemyestatecms-react.castledigital.com.au;

    location / {
        proxy_pass http://localhost:8002;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }

    listen 80;
}
NGINX

# 2. Enable the site
sudo ln -sf "$CONF" /etc/nginx/sites-enabled/

# 3. Test and reload Nginx
sudo nginx -t && sudo systemctl reload nginx

# 4. Issue SSL certificate
sudo certbot --nginx -d "$DOMAIN"

echo ""
echo "Done. React admin now available at https://${DOMAIN}"
echo "The Angular admin at ratemyestatecms.castledigital.com.au is untouched."