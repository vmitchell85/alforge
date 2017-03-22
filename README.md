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
- `forge reload` : Fetches new data from Forge API
- `forge clear` : Deletes your API Key and Forge Data

## Thanks
- [Laravel Forge / Taylor Otwell](https://forge.laravel.com)
- [Alfred 3 Workflows PHP Helper](https://github.com/joetannenbaum/alfred-workflow)