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