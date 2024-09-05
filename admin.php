<?php
$adminconfig = require('./config/adminconfig.php');
header("Location:/index.php/{$adminconfig['admin_path']}");
exit();
