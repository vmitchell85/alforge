<?php

namespace AlForge;

use Alfred\Workflows\Workflow;

class Deploy extends Forge
{
    public function search($query)
    {
        $workflow = new Workflow;

        foreach ($this->data->servers as $server) {
            foreach ($server->sites as $site) {
                if (strpos($site->name, $query) > -1 || strpos($server->name, $query) > -1) {
                    $workflow->result()
                        ->uid($site->id)
                        ->title('Site: ' . $site->name)
                        ->subtitle($server->name)
                        ->arg($server->id . ' ' . $site->id)
                        ->mod('cmd', 'Deploy ' . $site->name, 'deploy ' . $server->id . ' ' . $site->id)
                        ->valid(true);
                }
            }
        }

        echo $workflow->output();
    }

    public function execute($command)
    {
        $cmdParts = explode(' ', $command);

        $server = $this->getServerInfo($cmdParts[0]);
        $site = $this->getSiteInfo($cmdParts[0], $cmdParts[1]);

        if ($this->confirm("Are you sure you want to deploy the site `$site->name` on `$server->name`?")) {
            $response = $this->apiRequest("https://forge.laravel.com/api/v1/servers/$cmdParts[0]/sites/$cmdParts[1]/deployment/deploy");

            $data = json_decode($response);

            $this->respond(
                "Status: ".$data->site->deployment_status,
                ["push_title" => "Deploying `$site->name` on `$server->name`"]
            );
        }
    }
}
