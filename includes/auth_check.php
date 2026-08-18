<?php
require_once '../config/constants.php';
if (!is_logged_in()) {
    redirect('index.php');
}
