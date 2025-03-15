<?php
$connection = mysqli_connect("localhost", "root", "root", "WeCare","8889");

if (!$connection) {
    die("Connection failed: " . mysqli_connect_error());
}