<?php
session_start();
session_destroy();
header("Location: /restaurant-booking-system/auth/login.php");
exit();
