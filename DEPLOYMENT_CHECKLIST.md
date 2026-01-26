# Deployment Checklist for Render

Use this checklist to ensure everything is properly configured before and after deployment.

## Pre-Deployment

### Code Preparation
- [ ] All code is committed to Git repository
- [ ] `render.yaml` is in the root directory
- [ ] `.renderignore` is configured (optional)
- [ ] No sensitive data in code (use environment variables)
- [ ] Database migrations are ready
- [ ] Seeders are ready (if needed)

### Environment Variables Preparation
- [ ] Google OAuth credentials ready
- [ ] Email service credentials ready (if using)
- [ ] Any API keys ready

### Repository Setup
- [ ] Repository is connected to Render
- [ ] Branch to deploy is selected (usually `main` or `master`)

## Deployment Steps

### 1. Initial Deployment
- [ ] Create new Blueprint in Render
- [ ] Connect Git repository
- [ ] Review services in `render.yaml`:
  - [ ] PostgreSQL database
  - [ ] Backend API
  - [ ] Frontend
- [ ] Click "Apply" to deploy

### 2. Wait for Services to Deploy
- [ ] Database service starts (usually first)
- [ ] Backend service builds and starts
- [ ] Frontend service builds and starts
- [ ] All services show "Live" status

### 3. Verify Database Connection
- [ ] Check backend logs for database connection success
- [ ] Verify migrations ran successfully
- [ ] Check for any migration errors

### 4. Configure Environment Variables

#### Backend Environment Variables
- [ ] `APP_KEY` is set (auto-generated or manual)
- [ ] `APP_URL` matches backend service URL
- [ ] `FRONTEND_URL` matches frontend service URL
- [ ] `SANCTUM_STATEFUL_DOMAINS` includes frontend URL
- [ ] Database credentials are auto-populated (from `render.yaml`)
- [ ] `GOOGLE_CLIENT_ID` is set
- [ ] `GOOGLE_CLIENT_SECRET` is set
- [ ] `GOOGLE_REDIRECT_URI` is set correctly

#### Frontend Environment Variables
- [ ] `VITE_API_URL` points to backend API URL
- [ ] `VITE_APP_NAME` is set
- [ ] Any Pusher keys (if using)

### 5. Update External Services

#### Google OAuth
- [ ] Go to Google Cloud Console
- [ ] Update OAuth redirect URI to: `https://your-backend-url.onrender.com/api/auth/google/callback`
- [ ] Save changes

### 6. Test Deployment

#### Backend Tests
- [ ] Health check: `https://your-backend-url.onrender.com/up`
- [ ] API endpoint: `https://your-backend-url.onrender.com/api/home`
- [ ] Check CORS headers in response
- [ ] Test authentication endpoints

#### Frontend Tests
- [ ] Homepage loads: `https://your-frontend-url.onrender.com`
- [ ] API calls work (check browser console)
- [ ] No CORS errors
- [ ] Authentication works
- [ ] Products load
- [ ] Cart functionality works

#### Database Tests
- [ ] Can create records
- [ ] Can read records
- [ ] Migrations applied correctly
- [ ] Seeders ran (if applicable)

### 7. Post-Deployment

#### Security
- [ ] `APP_DEBUG=false` in production
- [ ] No sensitive data in logs
- [ ] HTTPS is enabled (automatic on Render)
- [ ] CORS is properly configured

#### Performance
- [ ] Backend caching is enabled
- [ ] Frontend assets are optimized
- [ ] Database indexes are in place

#### Monitoring
- [ ] Logs are accessible
- [ ] Error tracking is set up (optional)
- [ ] Uptime monitoring is configured (optional)

## Common Issues & Solutions

### Issue: Frontend shows blank page
**Check:**
- [ ] Build completed successfully
- [ ] `VITE_API_URL` is set correctly
- [ ] Check browser console for errors
- [ ] Verify frontend service is running

### Issue: API returns 500 errors
**Check:**
- [ ] Database is running and connected
- [ ] Migrations ran successfully
- [ ] Environment variables are set
- [ ] Check backend logs for specific errors

### Issue: CORS errors
**Check:**
- [ ] `FRONTEND_URL` matches actual frontend URL
- [ ] `SANCTUM_STATEFUL_DOMAINS` includes frontend URL
- [ ] CORS config allows frontend origin
- [ ] Backend service restarted after env var changes

### Issue: Database connection failed
**Check:**
- [ ] Database service is running
- [ ] Database credentials are correct
- [ ] Network connectivity between services
- [ ] Database is in same region (recommended)

## Production Readiness

### Before Going Live
- [ ] All tests pass
- [ ] Error handling is in place
- [ ] Logging is configured
- [ ] Backup strategy is in place
- [ ] Monitoring is set up
- [ ] Custom domain is configured (if needed)
- [ ] SSL certificates are valid
- [ ] Performance is acceptable

### Maintenance Tasks
- [ ] Regular database backups
- [ ] Monitor error rates
- [ ] Update dependencies regularly
- [ ] Review logs periodically
- [ ] Monitor resource usage

## Rollback Plan

If something goes wrong:
1. [ ] Identify the issue in logs
2. [ ] Fix the issue in code
3. [ ] Redeploy or rollback to previous version
4. [ ] Verify fix works

## Support Resources

- Render Documentation: https://render.com/docs
- Render Status: https://status.render.com
- Laravel Documentation: https://laravel.com/docs
- Vue.js Documentation: https://vuejs.org

---

**Deployment Status:** ⬜ Not Started | ⬜ In Progress | ⬜ Completed

**Date Deployed:** _______________

**Deployed By:** _______________

**Backend URL:** _______________

**Frontend URL:** _______________

**Database Status:** ⬜ Connected | ⬜ Issues

**Notes:**
_________________________________________________
_________________________________________________
_________________________________________________
