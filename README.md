# Fastfony Pro : pragmatic boilerplate and starter kit for fastly develop a project or launch a SaaS idea with Symfony 7

## Getting Started

1. If not already, [install PHP](https://www.php.net/manual/en/install.php), [install Composer](https://getcomposer.org) [install Symfony CLI](https://symfony.com/download), [install Node](https://nodejs.org/en/download), [install Docker Compose](https://docs.docker.com/compose/install/) and [install Taskfile](https://taskfile.dev/installation/)
2. First time, run `task fixtures` and `task oauth2-server-init`
3. Run `task start`
4. Open `https://fastfony-pro.wip/` (if you have setting up the [Symfony local proxy](https://symfony.com/doc/current/setup/symfony_server.html#setting-up-the-local-proxy)) or `https://127.0.0.1:9876` in your favorite web browser

If you want to change the domain name, you can edit the `.symfony.local.yaml` file and change the `proxy.domains` variable.

## Start & stop

- Start : just run `task start`
- Stop : just run `task stop` (Thanks to Taskfile!)

## Execute tests

- Run `task tests`

## Features

- Simple pages SEO friendly (front and backoffice)
- Permissions matrix for the management of the user's rights
- Register and received a login link by email
- Reset password by email
- Login form with email that send a login link by email
- Login form with password
- Users management with Profile with photo (and Groups, Roles, Role Categories)
- OAuth2 Server for the registered users and by applications/clients
- Settings panel
- Toasts notifications (flash messages from Symfony and others with [vue-toastification](https://vue-toastification.maronato.dev/))
- DaisyUI themes chooser
- API Platform 4
- EasyAdmin (with Bootstrap 5) and CRUD controllers for : Parameters, Parameter categories etc...
- Webpack Encore, Vue.js 3, Tailwind CSS 4 & DaisyUI 5
- Taskfile for easy install & start commands (just `task start` and develop)

## Pro Features

- Scheduler dashboard and logs : list configured recurring messages and display logs

## Docs

1. [Coding Style](docs/coding_style.md)
2. [Entities](docs/entities.md)
3. [Repositories](docs/repositories.md)
4. [CSS Theme](docs/css_theme.md)

# Updating Your Project

To import the changes made to the _Fastfony_ template into your project, we recommend using
[_template-sync_](https://github.com/coopTilleuls/template-sync):

1. Run the script on your branch `main` to synchronize your project with the latest version of Fastfony:

```console
curl -sSL https://raw.githubusercontent.com/coopTilleuls/template-sync/main/template-sync.sh | sh -s -- git@github.com:fastfony/fastfony-pro.git
```

2. Resolve conflicts, if any
3. Run `git cherry-pick --continue`

For more advanced options, refer to [the documentation of _template sync_](https://github.com/coopTilleuls/template-sync#template-sync).

## Contribute

You can contribute to Fastfony by creating a pull request on the GitHub repository.

This repository use the git plugin [git-flow](https://github.com/nvie/gitflow), so please create your feature branch from the `develop` branch and install [git-flow](https://git-flow.readthedocs.io/fr/latest/index.html).

The Conventional Commits specification is a lightweight convention on top of commit messages. Fastfony uses it. You can find more information on the [Conventional Commits website](https://www.conventionalcommits.org/en/v1.0.0/).

## License

Fastfony Pro is a private project. Paid licence is required to use it.

## Special thanks

Without the following projects, Fastfony Pro would not exist:

- [Symfony](https://symfony.com)
- [API Platform](https://api-platform.com)
- [EasyAdmin](https://symfony.com/doc/current/bundles/EasyAdminBundle/index.html)
- [Webpack Encore](https://symfony.com/doc/current/frontend.html)
- [Vue.js](https://vuejs.org)
- [Tailwind CSS](https://tailwindcss.com)
- [DaisyUI](https://daisyui.com)
- [Taskfile](https://taskfile.dev)
- [Editorjs](https://editorjs.io)
- and many others... \*Thank you!

## Credits

- Fastfony Pro is created by [Mathieu Dumoutier](https://mathieu.dumoutier.fr) and sponsored by [Minuit 11](https://minuit11.fr).
