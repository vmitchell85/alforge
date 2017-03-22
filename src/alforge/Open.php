<?php

namespace AlForge;

use AlForge\Forge;
use Alfred\Workflows\Workflow;

class Open extends Forge
{
    public function search($query)
    {
        echo $this->allSearch($query);
    }

    public function execute($command)
    {
        $cmdParts = split(' ', $command);

        if( $cmdParts[1] ){
            $this->respond("https://forge.laravel.com/servers/$cmdParts[0]/sites/$cmdParts[1]");
        }
        else{
            $this->respond("https://forge.laravel.com/servers/$cmdParts[0]");
        }
    }
}