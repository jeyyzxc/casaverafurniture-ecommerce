# Deep Analysis: Rollup Optional Dependency Issue

## Root Cause Analysis

### The Problem
1. **Vite 7.3.1** uses **Rollup 4.x** which requires platform-specific native binaries
2. `@rollup/rollup-linux-x64-gnu` is an **optional dependency** of Rollup
3. npm has a known bug (issue #4828) where optional dependencies aren't always installed
4. The package-lock.json may have the dependency listed, but npm isn't installing it
5. Render's build environment is Linux x64, so it needs the `-gnu` variant

### Why Standard Solutions Fail
- `npm install` should work but doesn't due to the npm bug
- `npm ci` definitely fails (it's too strict)
- `--include=optional` flag exists but may not work in all npm versions
- Build cache might be preserving broken state

## Comprehensive Solution

### Solution 1: Explicitly Install Missing Package (Recommended)

**Build Command:**
```bash
npm install && npm install @rollup/rollup-linux-x64-gnu --save-optional && npm run build-only
```

This explicitly installs the missing package after the main install.

### Solution 2: Force Clean Install with Optional Dependencies

**Build Command:**
```bash
rm -rf node_modules package-lock.json && npm install --force && npm run build-only
```

The `--force` flag ensures optional dependencies are installed.

### Solution 3: Use npm ci with Override (If package-lock.json is correct)

**Build Command:**
```bash
npm ci --include=optional || (rm -rf node_modules && npm install && npm run build-only)
```

This tries `npm ci` first, falls back to `npm install` if it fails.

### Solution 4: Pin Rollup Version (Nuclear Option)

If nothing else works, we can pin Rollup to a version that's more reliable. But this requires code changes.

## Recommended Action Plan

### Step 1: Try Solution 1 First
Update your Render build command to:
```bash
npm install && npm install @rollup/rollup-linux-x64-gnu --save-optional && npm run build-only
```

### Step 2: If That Fails, Try Solution 2
```bash
rm -rf node_modules package-lock.json && npm install --force && npm run build-only
```

### Step 3: If Still Failing
We may need to:
1. Update package-lock.json locally
2. Commit it with the optional dependency explicitly included
3. Or downgrade Vite/Rollup to a more stable version

## Why This Keeps Happening

1. **npm bug**: Optional dependencies aren't reliably installed
2. **Build cache**: Render might be caching a broken node_modules
3. **Package-lock.json state**: The lock file might not properly track optional deps
4. **Vite 7.x**: Newer version might have stricter requirements

## Long-term Fix

Consider adding to package.json:
```json
{
  "optionalDependencies": {
    "@rollup/rollup-linux-x64-gnu": "^4.56.0"
  }
}
```

This makes it a required optional dependency, ensuring it gets installed.
