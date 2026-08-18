<?php
require_once '../config/constants.php';
if (!is_logged_in() || !is_admin()) {
    redirect('index.php');
}
