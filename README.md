# Marshal

Marshal is a Symfony 7.2 project that aims to provide a platform for managing users, roles and application.

It's also a OAuth2 server for authenticated registered users and by applications/clients via OAuth2 grant types (password, client credentials, authorization code, refresh token).

<a href="https://github.com/planete-croisiere/marshal/actions/workflows/test.yaml"><img src="https://github.com/planete-croisiere/marshal/actions/workflows/test.yaml/badge.svg" alt="Unit & Functional Tests"></a>
[![Test Coverage](https://github.com/planete-croisiere/marshal/blob/badges/coverage.svg?raw=true)](https://github.com/planete-croisiere/marshal/actions/workflows/test.yaml)

## Getting Started

1. If not already, [install PHP](https://www.php.net/manual/en/install.php), [install Composer](https://getcomposer.org), [install Symfony CLI](https://symfony.com/download), [install Node](https://nodejs.org/en/download), [install Docker Compose](https://docs.docker.com/compose/install/) and [install Taskfile](https://taskfile.dev/installation/)
2. First time, run `task fixtures`
3. Run `task start`
4. Open `https://local.marshal.planete-croisiere.wip/` (if you have setting up the [Symfony local proxy](https://symfony.com/doc/current/setup/symfony_server.html#setting-up-the-local-proxy)) or `https://127.0.0.1:9876` in your favorite web browser

If you want to change the domain name, you can edit the `.symfony.local.yaml` file and change the `proxy.domains` variable.

## Start & stop

- Start : just run `task start`
- Stop : just run `task stop` (Thanks to Taskfile!)

## Execute tests

- Run `task tests` or `task coverage`

## Execute analytics

- Run `task phpstan` for PHPStan
- Run `task phpinsights` for PHPInsights

# Updating the Project from Fastfony Pro template

To import the changes made to the _Fastfony Pro_ template into your project, we recommend using
[_template-sync_](https://github.com/coopTilleuls/template-sync):

1. Run the script on your branch `main` to synchronize your project with the latest version of Fastfony:

```console
curl -sSL https://raw.githubusercontent.com/coopTilleuls/template-sync/main/template-sync.sh | sh -s -- git@github.com:fastfony/fastfony-pro.git
```

2. Resolve conflicts, if any
3. Run `git cherry-pick --continue`

For more advanced options, refer to [the documentation of _template sync_](https://github.com/coopTilleuls/template-sync#template-sync).
