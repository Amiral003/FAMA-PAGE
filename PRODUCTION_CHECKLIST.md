# FAMA - Production Checklist

## P0 - Bloquant

### Configuration
- [ ] APP_ENV=production
- [ ] APP_DEBUG=false
- [ ] config:cache OK
- [ ] route:cache OK

### Database
- [ ] Index posts.slug
- [ ] Index posts.published_at
- [ ] Index posts.type
- [ ] N+1 vérifié

### Rate limiting
- [ ] Throttle API
- [ ] Throttle contact
- [ ] Throttle login admin

### Backup
- [ ] Spatie backup installé
- [ ] Backup planifié
- [ ] Restore testé
# FAMA - Production Checklist (Go/No-Go)

## P0 - Bloquant

### 1) Configuration prod (à faire sur serveur)
- [ ] APP_ENV=production
- [ ] APP_DEBUG=false
- [ ] APP_KEY OK
- [ ] php artisan config:cache OK
- [ ] php artisan route:cache OK

### 2) Database / Performance
- [x] Default posts.status = 'brouillon'
- [x] Index unique slug (partial WHERE slug IS NOT NULL)
- [x] Index feed public (status=publie + tri dates)
- [x] Index feed type (type + tri dates, status=publie)
- [x] Index flash (status=publie AND type=flash)
- [x] Index post_media (post_id, order)
- [x] Search: pg_trgm + GIN indexes (title/content)
- [x] N+1 guard en local: preventLazyLoading()

### 3) Rate limiting / Anti-abus
- [x] throttle:api-public sur routes publiques API
- [x] throttle:contact sur POST /api/public/contact
- [x] throttle login admin OK

### 4) Cache public assets
- [x] Middleware CachePublicAssets présent
- [x] Middleware enregistré via bootstrap/app.php (Laravel 12)
- [ ] Vérif navigateur: Cache-Control sur /storage/*.jpg|png|webp|pdf

### 5) Backup / Restauration (Spatie)
- [x] spatie/laravel-backup installé + config publiée
- [ ] Test backup DB (bloqué local: pg_dump absent)
- [ ] Test restore (à faire sur serveur/test)

## P1 - Recommandé
- [ ] Logs prod (stack / daily) + rotation
- [ ] Monitoring basique (health endpoint /up, erreurs 500, espace disque)
- [ ] Politique de rétention backups (7j/16j/8w etc.) validée