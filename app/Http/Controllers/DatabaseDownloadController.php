<?php

namespace App\Http\Controllers;

use File;
use Illuminate\Support\Facades\Process;

class DatabaseDownloadController extends Controller
{
    public function index()
    {
        return response()->streamDownload(function () {
            $mysqlDump = File::exists('/usr/bin/mysqldump') ? '/usr/bin/mysqldump' : '/opt/homebrew/bin/mysqldump';
            $password = config('database.connections.mysql.password') ? '-p'.config('database.connections.mysql.password') : '';

            echo Process::run("$mysqlDump -u root $password notams notams | gzip -c -f")->output();
        }, 'notams-'.now()->toDateString().'.sql.gz');
    }
}
