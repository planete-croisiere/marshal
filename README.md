# Marshal by Planète-Croisière : OAuth2 app server and user management tool

## Use project

```bash
make start
```

(Others commands are available, see Makefile)

## First user for first connection

* Login : admin@test.com
* Password : test

(Delete it very quickly!)

Go to https://127.0.0.1:8000/admin

## Configuration

### Scopes available in config/services.yaml

You can dit edit this lines :

```yaml
parameters:
    oauth2_client_available_scopes: ['email']
```



