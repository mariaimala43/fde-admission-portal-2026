#!/usr/bin/env bash
# =============================================================================
#  FDE Admission Portal 2026 — One-Click Linux Installer
#  Tested on: Ubuntu 22.04 LTS / Debian 12
#  Requirements: PHP 8.2, Composer, Node.js 18+, MySQL 8, Nginx
# =============================================================================

set -e
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

ok()   { echo -e "  ${GREEN}[OK]${NC} $1"; }
warn() { echo -e "  ${YELLOW}[WARN]${NC} $1"; }
err()  { echo -e "  ${RED}[ERROR]${NC} $1"; exit 1; }
step() { echo -e "\n${CYAN}${BOLD}── $1 ──────────────────────────────────────${NC}"; }

echo ""
echo -e "${CYAN}${BOLD} ================================================================${NC}"
echo -e "${CYAN}${BOLD}   FDE Admission Portal 2026 — One-Click Installer (Linux)       ${NC}"
echo -e "${CYAN}${BOLD} ================================================================${NC}"
echo ""

# ── 1. Check required tools ──────────────────────────────────────────────────
step "Checking requirements"

command -v php  >/dev/null 2>&1 || err "PHP not found. Install: sudo apt install php8.2-cli php8.2-fpm php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-bcmath"
ok "PHP found: $(php -r 'echo PHP_VERSION;')"

command -v composer >/dev/null 2>&1 || err "Composer not found. Install: https://getcomposer.org/download/"
ok "Composer found: $(composer --version --no-ansi 2>/dev/null | head -1)"

command -v node >/dev/null 2>&1 || err "Node.js not found. Install: https://nodejs.org/ or use nvm"
ok "Node.js found: $(node --version)"

command -v npm >/dev/null 2>&1 || err "npm not found. Install with Node.js."
ok "npm found: $(npm --version)"

# ── 2. PHP dependencies ──────────────────────────────────────────────────────
step "Step 1 of 4: Installing PHP dependencies"
composer install --no-dev --optimize-autoloader
ok "Composer install complete."

# ── 3. Setup wizard ──────────────────────────────────────────────────────────
step "Step 2 of 4: Running setup wizard"
php artisan app:install
ok "Setup wizard complete."

# ── 4. Frontend ──────────────────────────────────────────────────────────────
step "Step 3 of 4: Installing frontend dependencies"
npm install
ok "npm install complete."

step "Step 4 of 4: Building frontend assets"
npm run build
ok "Frontend assets built."

# ── 5. File permissions ──────────────────────────────────────────────────────
step "Setting file permissions"
chmod -R 775 storage bootstrap/cache
ok "storage/ and bootstrap/cache/ set to 775."

if [ "$EUID" -eq 0 ]; then
    chown -R www-data:www-data .
    ok "Ownership set to www-data:www-data."
else
    warn "Not running as root — skipping chown. Run manually if needed:"
    warn "  sudo chown -R www-data:www-data $(pwd)"
fi

# ── 6. Nginx reminder ────────────────────────────────────────────────────────
echo ""
echo -e "${YELLOW}${BOLD} ── Nginx Setup ────────────────────────────────────────────────${NC}"
echo -e "  Copy the Nginx config template and edit it:"
echo -e "  ${CYAN}sudo cp nginx.conf.example /etc/nginx/sites-available/fde-portal${NC}"
echo -e "  ${CYAN}sudo nano /etc/nginx/sites-available/fde-portal${NC}  (set server_name & root)"
echo -e "  ${CYAN}sudo ln -s /etc/nginx/sites-available/fde-portal /etc/nginx/sites-enabled/${NC}"
echo -e "  ${CYAN}sudo nginx -t && sudo systemctl reload nginx${NC}"

# ── 7. Done ──────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}${BOLD} ================================================================${NC}"
echo -e "${GREEN}${BOLD}   ✅  Installation complete!                                    ${NC}"
echo -e "${GREEN}${BOLD}   Open your browser at the URL you configured + /login          ${NC}"
echo -e "${GREEN}${BOLD} ================================================================${NC}"
echo ""
