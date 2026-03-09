<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth_functions.php';

logout_user();

redirect('sign_in.php');

