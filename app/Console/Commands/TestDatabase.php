<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestDatabase extends Command
{
    protected $signature = 'db:test';
    protected $description = 'Test connection to db and check if it works';

    public function handle()
    {
        try {
            DB::connection()->getPdo();
            $this->info('✅ Connection to database is successful!');
            
            $version = DB::select('SELECT version()');
            $this->info('PostgreSQL version: ' . $version[0]->version);
            
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error connecting to database: ' . $e->getMessage());
            return 1;
        }
    }
}