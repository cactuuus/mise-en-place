<?php

use Illuminate\Support\Facades\Schedule;

// DB backup commands, using spatie backup
Schedule::command('backup:run --only-db')->daily();
Schedule::command('backup:clean')->daily();
Schedule::command('backup:monitor')->daily();
