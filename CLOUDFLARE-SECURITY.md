# Cloudflare security baseline
1. SSL/TLS: Full (Strict); install an Origin Certificate on cPanel.
2. Proxy the website A/AAAA records; keep mail/FTP records DNS-only.
3. Disable caching for `/`, `/parent.php`, `/api/*`, and authenticated responses.
4. WAF: enable Cloudflare managed rules and OWASP rules conservatively.
5. Rate-limit login, password recovery and parent lookup endpoints.
6. Add Turnstile after repeated attempts, not as a replacement for server-side limits.
7. If hosting permits, restrict ports 80/443 at origin to Cloudflare IP ranges and keep SSH restricted to trusted IPs.
8. Enable HSTS only after HTTPS is verified on all required subdomains.
