# CasaVera Furniture - Render Deployment

This project is configured for deployment on Render with automatic service connections.

## 📁 Files Created for Deployment

1. **`render.yaml`** - Main Render configuration file
   - Defines PostgreSQL database
   - Configures backend API service
   - Configures frontend service
   - Sets up automatic environment variable linking

2. **`RENDER_DEPLOYMENT_GUIDE.md`** - Complete deployment guide
   - Step-by-step instructions
   - Troubleshooting tips
   - Production optimizations

3. **`QUICK_DEPLOY.md`** - Fast 5-minute deployment guide
   - Quick start instructions
   - Essential steps only

4. **`DEPLOYMENT_CHECKLIST.md`** - Pre and post-deployment checklist
   - Ensure nothing is missed
   - Track deployment status

5. **`.renderignore`** - Files to exclude from deployment
   - Reduces deployment size
   - Excludes unnecessary files

6. **`.env.production.example`** files
   - Template for production environment variables
   - Reference for required settings

## 🚀 Quick Start

1. **Push to Git:**
   ```bash
   git add .
   git commit -m "Ready for Render deployment"
   git push origin main
   ```

2. **Deploy on Render:**
   - Go to https://dashboard.render.com
   - Click "New +" → "Blueprint"
   - Connect your repository
   - Click "Apply"

3. **Configure:**
   - Set Google OAuth credentials in backend environment
   - Update Google OAuth redirect URI
   - Verify frontend API URL

## 🔗 Service Connections

All services are automatically connected:

```
Frontend (Vue)
    ↓ VITE_API_URL
Backend (Laravel)
    ↓ Database credentials
PostgreSQL Database
```

**Automatic Configuration:**
- ✅ Frontend API URL → Backend service URL
- ✅ Backend database → PostgreSQL service
- ✅ CORS → Frontend URL
- ✅ Sanctum domains → Frontend URL

## 📋 What Gets Deployed

### Database
- PostgreSQL 16
- Auto-configured connection
- Migrations run automatically

### Backend
- Laravel API
- PHP 8.2+
- Optimized for production
- Auto-runs migrations

### Frontend
- Vue 3 + Vite
- Built and optimized
- Served with proper SPA routing

## 🔧 Environment Variables

Most environment variables are auto-configured. You only need to set:

**Backend:**
- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`
- `GOOGLE_REDIRECT_URI` (auto-set, but verify)

**Frontend:**
- `VITE_API_URL` (auto-set from backend)

## 📚 Documentation

- **Quick Deploy:** See `QUICK_DEPLOY.md`
- **Full Guide:** See `RENDER_DEPLOYMENT_GUIDE.md`
- **Checklist:** See `DEPLOYMENT_CHECKLIST.md`

## ✅ Verification

After deployment, verify:

1. **Backend Health:**
   ```
   https://your-backend.onrender.com/up
   ```

2. **API Works:**
   ```
   https://your-backend.onrender.com/api/home
   ```

3. **Frontend Loads:**
   ```
   https://your-frontend.onrender.com
   ```

## 🆘 Support

If you encounter issues:
1. Check service logs in Render dashboard
2. Review `RENDER_DEPLOYMENT_GUIDE.md` troubleshooting section
3. Verify all environment variables are set
4. Check that services are in "Live" status

---

**Ready to deploy?** Follow `QUICK_DEPLOY.md` for the fastest path to production! 🚀
