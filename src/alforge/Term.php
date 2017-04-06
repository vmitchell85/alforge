<?php

namespace AlForge;

use AlForge\Forge;
use Alfred\Workflows\Workflow;

class Term extends Forge
{
    public function search($query)
    {
        echo $this->serverSearch($query);
    }

    public function execute($server_id)
    {
        $server = $this->getServerInfo($server_id);

        echo $server->ip_address;
    }
}