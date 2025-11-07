<?php
require_once 'AdminAuth.php';
AdminAuth::logout();
header('Location: admin_login.php');
exit;