<?php

function hashPassword($password) {
    $hash = hash('sha512', $password);
    return $hash;
}

function hashPasswordWithSalt($password, $salt) {
    $hash = hash('sha512', $password . $salt);
    return $hash;
}

function generateSalt() {
    $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    $salt = '';
    $length = 16;

    for ($i = 0; $i < $length; $i++) {
        $salt .= $chars[rand(0, strlen($chars) - 1)];
    }

    return $salt;
}