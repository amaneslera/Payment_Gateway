<?php
// filepath: e:\GitHub\Payment_Gateway\Source Codes\PaymentSystem\backend\jwt_config.php

// Secret key for signing JWT tokens - CHANGE THIS TO A SECURE RANDOM STRING
define('JWT_SECRET_KEY', 'emQzx7$pGrT!9Bvf@KwL#2Hs5*yUn8aC'); // Change this to a different secure key

// Token expiration time (in seconds)
define('JWT_EXPIRATION', 3600); // 1 hour

// Refresh token expiration (in seconds)
define('JWT_REFRESH_EXPIRATION', 86400 * 7); // 1 week