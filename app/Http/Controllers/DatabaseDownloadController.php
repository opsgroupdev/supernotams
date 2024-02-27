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
            $user = config('database.connections.mysql.username');
            echo Process::run("$mysqlDump -u $user $password notams notams | /usr/bin/gzip -c -f")->output();
        }, 'notams-'.now()->toDateString().'.sql.gz');
    }
}
