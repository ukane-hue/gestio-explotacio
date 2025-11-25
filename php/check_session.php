<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    json_out(true, ['nom' => $_SESSION['nom']]);
} else {
    json_out(false, ['missatge' => 'No loguejat']);
}
?>