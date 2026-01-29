# Fix Rollup Optional Dependencies Error

## Error
```
Error: Cannot find module @rollup/rollup-linux-x64-gnu
```

## Solution

This is a known npm issue with optional dependencies. Change your build command in Render.

### Fix: Update Build Command

1. Go to your frontend service in Render Dashboard
2. Go to **"Settings"** tab
3. Find **"Build Command"**
4. Change from:
   ```bash
   npm ci && npm run build
   ```
   
   To:
   ```bash
   npm install && npm run build
   ```

### Why This Works

- `npm ci` is strict and may skip optional dependencies
- `npm install` properly handles optional dependencies like Rollup's native binaries
- This ensures `@rollup/rollup-linux-x64-gnu` gets installed on Linux (Render's build environment)

### Alternative: Force Optional Dependencies

If you prefer to keep `npm ci`, you can try:
```bash
npm ci --include=optional && npm run build
```

But `npm install` is the simpler and more reliable solution.

### After Updating

1. Save the changes
2. Render will automatically trigger a new build
3. Or manually trigger: **"Manual Deploy"** → **"Clear build cache & deploy"**

The build should now succeed! ✅
