# alForge - Alfred 3 Workflow for Laravel Forge

Quickly interact with your Forge servers & sites.

Latest stable version is tagged with a release and uploaded to [Packal.org](http://www.packal.org/workflow/alforge).

## Setup

1. Run `forge key ` and paste your API key in.
2. You should get a notification that the key was saved.
3. Run `forge open ` and start searching for your sites or servers.

## Available Keywords
- `forge key {key}` : Sets your Forge API Key
- `forge open {site or server}` OR `fo {site or server}` : Opens the specified server or site's Forge page in your browser
- `forge deploy {site}` : Deploys the site specified (must confirm)
- `forge reboot {server}` : Reboots the specified server (must confirm)
- `forge mysql {server}` : Restarts MySQL on the specified server (must confirm)
- `forge postgres {server}` : Restarts Postgres on the specified server (must confirm)
- `forge nginx {server}` : Restarts Nginx on the specified server (must confirm)
- `forge term {server}` : Opens SSH connection in Terminal to the specified server
- `forge iterm {server}` : Opens SSH connection in iTerm to the specified server
- `forge reload` : Fetches new data from Forge API
- `forge clear` : Deletes your API Key and Forge Data

## Thanks
- [Laravel Forge / Taylor Otwell](https://forge.laravel.com)
- [Alfred 3 Workflows PHP Helper](https://github.com/joetannenbaum/alfred-workflow)