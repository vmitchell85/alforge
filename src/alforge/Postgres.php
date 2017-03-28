<?php

namespace AlForge;

use AlForge\Forge;
use Alfred\Workflows\Workflow;

class Postgres extends Forge
{
    public function search($query)
    {
        echo $this->serverSearch($query);
    }

    public function execute($server_id)
    {
        $server = $this->getServerInfo($server_id);

        if($this->confirm("Are you sure you want to restart Postgres on `$server->name`?")){
            $response = $this->apiRequest("https://forge.laravel.com/api/v1/servers/$server->id/postgres/reboot");
            $this->respond(
                "Command sent to Forge",
                ["push_title" => "Restarting Postgres on `$server->name`"]
            );
        }

    }
}