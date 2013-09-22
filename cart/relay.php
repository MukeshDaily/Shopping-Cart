<?php

// This file takes input from Ajax requests and passes data to cart.php
// Returns updated cart HTML back to submitting page

header('Content-type: text/html; charset=utf-8');

// Include cart before session start
include_once('cart.php');

// Process input and return updated cart HTML
$cart->display_cart();

?>