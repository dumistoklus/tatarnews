<?php

include 'core/core.php';

DB_Provider::Instance()->loadProvider('Core');

add_file('views/views_core.php', __FILE__);

new Template(env::vars()->PAGE);

Logger::print_log();
