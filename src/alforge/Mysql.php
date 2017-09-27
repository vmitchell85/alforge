<?php

namespace AlForge;

class Mysql extends Forge
{
    public function search($query)
    {
        echo $this->serverSearch($query);
    }

    public function execute($server_id)
    {
        $server = $this->getServerInfo($server_id);

        if ($this->confirm("Are you sure you want to restart MYSQL on `$server->name`?")) {
            $response = $this->apiRequest("https://forge.laravel.com/api/v1/servers/$server->id/mysql/reboot");

            $data = json_decode($response);

            $this->respond(
                "Command sent to Forge",
                ["push_title" => "Restarting MYSQL on `$server->name`"]
            );
        }
    }
}
