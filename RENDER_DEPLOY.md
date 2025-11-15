# Deploying to Render

## Quick Deploy

1. **Push your code to GitHub** (already done)

2. **Create a new Web Service on Render**
   - Go to https://dashboard.render.com
   - Click "New +" → "Web Service"
   - Connect your GitHub repository: `Souri-Dev/personal_lms`
   - Configure:
     - **Name**: `csit-dept-lms`
     - **Environment**: `Docker`
     - **Region**: Choose closest to you
     - **Branch**: `master`
     - **Plan**: Free

3. **Set Environment Variables** in Render Dashboard:
   ```
   APP_ENV=prod
   APP_SECRET=your-random-64-char-secret
   DATABASE_URL=mysql://user:pass@host:3306/dbname
   ```

4. **Create a MySQL Database** (if not using external):
   - In Render, go to "New +" → "PostgreSQL" or use external MySQL
   - Copy the connection string
   - Update `DATABASE_URL` environment variable

5. **Deploy**
   - Render will automatically build and deploy
   - Check logs for any errors
   - Access your app at the provided URL

## Environment Variables Required

| Variable | Description | Example |
|----------|-------------|---------|
| `APP_ENV` | Environment | `prod` |
| `APP_SECRET` | Secret key | Random 64-char string |
| `DATABASE_URL` | Database connection | `mysql://user:pass@host:3306/db` |
| `MAILER_DSN` | Email config (optional) | `null://null` |

## Generate APP_SECRET

### PowerShell:
```powershell
-join ((48..57) + (65..90) + (97..122) | Get-Random -Count 64 | ForEach-Object {[char]$_})
```

### Linux/Mac:
```bash
openssl rand -hex 32
```

## Troubleshooting

### Logs show "Database connection error"
- Verify `DATABASE_URL` is correct
- Ensure database is accessible from Render

### Application not loading
- Check Render logs for errors
- Verify all environment variables are set
- Check health check endpoint: `/login`

### Permission errors
- These should be handled automatically by the start script
- Check logs for specific error messages

## Manual Deployment Steps

If automatic deployment fails:

1. **Local Build Test**:
   ```bash
   docker build -t csit-lms .
   docker run -p 8000:80 csit-lms
   ```

2. **Check Logs**:
   - In Render dashboard, go to "Logs"
   - Look for error messages
   - Common issues: missing env vars, database connection

3. **Force Redeploy**:
   - In Render dashboard, click "Manual Deploy" → "Clear build cache & deploy"

## Database Setup

### Using Render PostgreSQL (Recommended for Free Tier)

1. Create PostgreSQL database in Render
2. Update Dockerfile to install pdo_pgsql:
   ```dockerfile
   docker-php-ext-install pdo pdo_pgsql
   ```
3. Update DATABASE_URL to use postgresql://

### Using External MySQL

1. Set DATABASE_URL to your MySQL host
2. Ensure MySQL accepts external connections
3. Whitelist Render's IP addresses

## Post-Deployment

After successful deployment:

1. Visit your app URL
2. Login with your credentials
3. Test all features
4. Monitor logs for any errors

## Updates

To deploy updates:

1. Push changes to GitHub
2. Render automatically detects and deploys
3. Monitor deployment in Render dashboard

## Support

- Render Docs: https://render.com/docs
- Symfony Docs: https://symfony.com/doc/current/deployment.html
