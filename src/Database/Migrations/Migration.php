<?php

namespace App\Database\Migrations;

use Exception;
use Phinx\Migration\AbstractMigration;
use Illuminate\Database\Capsule\Manager as Capsule;
use Symfony\Component\Console\Output\OutputInterface;

class Migration extends AbstractMigration
{
    protected $schema;

    public function init()
    {        
        $db = config('app.db');
       /* $this->getOutput()->writeln(
            $db,
            OutputInterface::OUTPUT_NORMAL
        );*/

        // try{
        //     $this->schema = (new Capsule)->schema();
        // }catch( Exception $error){
        //     $this->getOutput()->writeln($error,OutputInterface::OUTPUT_NORMAL
        //     );

        //     $capsule = new Capsule;
        //     $capsule->addConnection($db);
        //     $capsule->setAsGlobal();
    
        //     $this->schema = $capsule->schema();
        // }
        $capsule = new Capsule;
        $capsule->addConnection($db);
        $capsule->setAsGlobal();

        $this->schema = $capsule->schema();
       // $this->schema = (new Capsule)->schema();
    }
}
