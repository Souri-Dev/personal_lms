# Deployment Guide

## Production Deployment Steps

### 1. Update Environment Variables

Edit `.env.prod` and change the `APP_SECRET`:

```bash
APP_SECRET=$(openssl rand -hex 32)
```

Or on Windows PowerShell:
```powershell
-join ((48..57) + (65..90) + (97..122) | Get-Random -Count 64 | ForEach-Object {[char]$_})
```

### 2. Build and Start Containers

```bash
docker-compose up -d --build
```

### 3. Access Your Application

- **Application**: http://localhost:8000
- **PhpMyAdmin**: http://localhost:8081
- **MySQL**: localhost:3308

### 4. First Time Setup

The container automatically:
- Waits for MySQL to be ready
- Runs database migrations
- Warms up Symfony cache

### 5. View Logs

```bash
# All services
docker-compose logs -f

# Just the app
docker-compose logs -f app

# Just MySQL
docker-compose logs -f mysql
```

### 6. Stop Services

```bash
docker-compose down
```

### 7. Clean Restart (Removes Database)

```bash
docker-compose down -v
docker-compose up -d --build
```

## Production Deployment to Cloud

### AWS/DigitalOcean/VPS

1. Install Docker and Docker Compose on your server
2. Clone your repository
3. Copy `.env.prod` to `.env` and update values
4. Run: `docker-compose up -d --build`
5. Set up reverse proxy (nginx) for domain and SSL

### Using a Load Balancer

Update `docker-compose.yaml` to scale:

```bash
docker-compose up -d --scale app=3
```

## Environment Variables

| Variable | Description | Example |
|----------|-------------|---------|
| APP_ENV | Application environment | prod |
| APP_SECRET | Secret key for Symfony | random_64_char_string |
| DATABASE_URL | Database connection | mysql://user:pass@host:3306/db |

## Health Check

Check if the app is running:

```bash
curl http://localhost:8000
```

## Troubleshooting

### Container won't start
```bash
docker-compose logs app
```

### Database connection error
```bash
# Check if MySQL is running
docker-compose ps mysql

# Check MySQL logs
docker-compose logs mysql
```

### Permission errors
```bash
# Fix permissions on host
sudo chown -R $USER:$USER var/
```

### Clear cache manually
```bash
docker-compose exec app php bin/console cache:clear --env=prod
```

## Security Checklist

- [ ] Change `APP_SECRET` to a random value
- [ ] Update MySQL passwords in docker-compose.yaml
- [ ] Set up SSL/TLS certificate (Let's Encrypt)
- [ ] Configure firewall rules
- [ ] Enable HTTPS only
- [ ] Regular security updates: `docker-compose pull && docker-compose up -d`
- [ ] Regular database backups

## Backup Database

```bash
docker-compose exec mysql mysqldump -u csit_dept_lms_user -pcsit_dept_lms_pass csit_dept_lms > backup.sql
```

## Restore Database

```bash
docker-compose exec -T mysql mysql -u csit_dept_lms_user -pcsit_dept_lms_pass csit_dept_lms < backup.sql
```
