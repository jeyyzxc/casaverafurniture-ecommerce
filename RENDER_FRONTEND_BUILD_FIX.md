# Fix Rollup Build Error - Complete Solution

## Error
```
Error: Cannot find module @rollup/rollup-linux-x64-gnu
```

## Solution: Update Build Command in Render

Go to your frontend service → **Settings** → **Build Command** and use one of these:

### Option 1: Simple Fix (Recommended)
```bash
npm install && npm run build-only
```

### Option 2: Clean Install (If Option 1 doesn't work)
```bash
rm -rf node_modules package-lock.json && npm install && npm run build-only
```

### Option 3: Force Optional Dependencies
```bash
npm install --include=optional && npm run build-only
```

### Option 4: Most Robust (Nuclear Option)
```bash
cd frontend && rm -rf node_modules package-lock.json && npm install --legacy-peer-deps && npm run build-only
```

## Step-by-Step

1. **Go to Render Dashboard**
2. **Click on your frontend service**
3. **Go to "Settings" tab**
4. **Find "Build Command" field**
5. **Replace the current command with:**
   ```bash
   npm install && npm run build-only
   ```
6. **Save changes**
7. **Render will automatically rebuild**

## Why This Works

- `npm install` (not `npm ci`) properly handles optional dependencies
- Rollup needs platform-specific native binaries (`@rollup/rollup-linux-x64-gnu` for Linux)
- `npm ci` is too strict and skips optional dependencies
- `build-only` skips TypeScript checking (faster, avoids type errors)

## Verify It's Updated

After saving, check the build logs. You should see:
- `npm install` running (not `npm ci`)
- Packages being installed including optional ones
- Build completing successfully

## If Still Failing

Try Option 2 (clean install) which removes cached files and does a fresh install.

---

**Important:** Make sure you're updating the **Build Command** in Render, not just reading this guide!
