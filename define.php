<?php

error_reporting(E_ALL);

define("TOKEN_LEN", 64); //characters.
define("TOKEN_TTL", 6000000); //secs.
//define("TOKEN_TTL", 60); //secs.

define("MSG_REQUEST_SUCCESS", "Request successfully executed!");
define("MSG_REQUEST_INVALID", "Invalid Request, skipping!");

define("MSG_INVALID_INPUT_JSON", "Input Json don't parsed, skipped request");
define("MSG_INVALID_LOGIN_REQUEST", "Login Request is invalid, skipped request");
define("MSG_INVALID_SERVICE", "Service requested doesn't exists, skipped request");
define("MSG_USER_NOT_ACTIVE", "User Not Active, skipped request");
define("MSG_INVALID_ORDER", "Invalid Order Request");
define("MSG_ORDER_NOT_FOUND", "Order Not Found");
define("MSG_SUBSCRIPTION_FAILED", "User Not Subscribed, Errors during subscription");
define("USER_NOT_ACTIVE", "not active");
define("USER_ACTIVE", "active");
define("USER_TOKEN_EXPIRED", "Token Expired, skipped request");



