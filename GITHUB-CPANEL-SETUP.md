# GitHub → cPanel deployment for DrMahmoud003/NewLesson

## 1) Create the repository
Create a **Private** repository named `NewLesson` under `DrMahmoud003`. Do not initialize it with sample files.

## 2) Upload this project
```bash
git init
git branch -M main
git remote add origin git@github.com:DrMahmoud003/NewLesson.git
git add .
git commit -m "Initial secure modular platform"
git push -u origin main
```

## 3) Generate a dedicated deployment key
Run in cPanel Terminal (do not paste the private key into chat):
```bash
ssh-keygen -t ed25519 -a 100 -C "github-actions-NewLesson" -f ~/.ssh/newlesson_deploy -N ""
cat ~/.ssh/newlesson_deploy.pub >> ~/.ssh/authorized_keys
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys ~/.ssh/newlesson_deploy
```
Copy the **private** key content only to GitHub repository → Settings → Secrets and variables → Actions → `CPANEL_SSH_PRIVATE_KEY`.

## 4) Required GitHub Secrets
- `CPANEL_HOST`
- `CPANEL_PORT` (usually 22)
- `CPANEL_USER`
- `CPANEL_PATH` (project path outside public_html)
- `CPANEL_SSH_PRIVATE_KEY`
- `CPANEL_KNOWN_HOSTS` generated locally with `ssh-keyscan -p PORT HOST` and verified with the hosting provider.

## 5) cPanel
Point the domain Document Root to `CPANEL_PATH/public`. Create `.env` only on cPanel. Never commit it. Run database/schema.sql once, then create the manager from Terminal.

## 6) Cloudflare
Cloudflare proxies the domain to cPanel; code is not uploaded to Cloudflare. Use Full (Strict), WAF, Turnstile and rate limits. Keep admin/API responses uncached.
