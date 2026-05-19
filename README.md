# Page Builder

This repository contains an open source, self hostable visual website builder developed as a bachelor thesis prototype by Noah Howadt at the University of Applied Sciences Technikum Wien in 2026. The project explores how non technical users can create and maintain websites while keeping control over hosting and data. Editors work in a browser based administrative interface. Visitors receive pages rendered on the server as static HTML and CSS without loading the admin JavaScript bundle.

The application is built with Laravel 12, Inertia.js, Vue 3, and PostgreSQL. Production deployment is supported through Docker and Docker Compose.

## Features

The visual page builder lets authenticated users compose pages by dragging and dropping blocks onto a canvas. Supported primitive blocks include containers, headings, and paragraphs. Users can edit text inline and adjust block specific settings such as heading level or container layout.

All page content is stored as a JSON block tree. The same structure powers both the editor and the public website. The model supports nested blocks, inline text marks, and internal links between pages.

Reusable components allow shared sections such as headers or footers to be defined once and placed on multiple pages. When a component is updated, every page that references it reflects the change after the structure is resolved on the server.

The system separates authoring from delivery. The admin area uses Inertia and Vue for interactive editing. Public routes are served through Blade templates and PHP view components that render the persisted structure recursively on each request.

First time setup is protected by a deployer configured setup token. The token must be at least 32 characters long and is required to create the initial administrator account. Self registration remains disabled. After the first admin exists, new users can only be added through normal authenticated flows.

Authentication is handled by Laravel Fortify and includes login as well as optional two factor authentication. Pages have a title, slug, structure, and publication state. Only published pages are returned on the public catch all route.

## Deployment

Deployment is defined in `docker-compose.prod.yml`. The stack runs two services: a PostgreSQL database and the pre built application image `noahhowadt/pagebuilder:latest`. You can also build your own version of the software.

### Configure the environment

Open `docker-compose.prod.yml` and fill in every empty value before starting the stack. The database credentials on the `postgres` service must match the corresponding `DB_` variables on the `pagebuilder` service.

On the `postgres` service, set `POSTGRES_USER`, `POSTGRES_PASSWORD`, and `POSTGRES_DB`.

On the `pagebuilder` service, set `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` to the same values you chose for Postgres.

Generate an application key and assign it to `APP_KEY`. You can produce a key with `php artisan key:generate --show` on any machine that has the Laravel project available.

Choose a long random string of at least 32 characters and assign it to `SETUP_TOKEN`. You will need this value when creating the first administrator.

Set `APP_URL` to the URL where the site will be reachable, for example `http://localhost:8080` for local deployment.

For a production installation, set `APP_ENV` to `production` and `APP_DEBUG` to `false`.

### Start the containers

From the project directory, run:

```bash
docker compose -f docker-compose.prod.yml up -d
```

Docker will pull the images if needed, create the network, and start both containers. The application listens on port 8080 on the host, which maps to port 80 inside the container.

### Create the first administrator

Visit `/admin/setup` in your browser. Enter the setup token you configured in `SETUP_TOKEN` along with the details for the first admin user. Once that account exists, the setup route is no longer used and you sign in through the normal login page at `/admin`. From there you can create and edit pages, manage reusable components, and publish content.

## Local development

To work on the source code directly, clone this repository and follow the usual Laravel setup steps. Install PHP dependencies with Composer, install frontend dependencies with pnpm, copy and configure `.env`, run migrations, and start the development servers. The bachelor thesis document includes further detail on architecture, the block model, and implementation choices in its appendix.

## License

This software is licensed under the MIT License. See [LICENSE.md](LICENSE.md) for the full text.

This project was developed as a bachelor thesis at the University of Applied Sciences Technikum Wien in 2026. The thesis document and this repository describe the same prototype, but only the software in this repository is covered by the MIT License above.
