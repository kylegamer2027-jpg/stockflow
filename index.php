<?php
require_once __DIR__ . '/includes/functions.php';
redirect(current_user() ? 'modules/dashboard/index.php' : 'auth/login.php');
