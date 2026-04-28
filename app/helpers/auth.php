<?php

session_start();

function require_login()
{
    if (!isset($_SESSION['usuari_id'])) {
        header('Location: login.php');
        exit;
    }
}

function require_role($rols)
{
    if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], $rols)) {
        header('Location: dashboard.php');
        exit;
    }
}
