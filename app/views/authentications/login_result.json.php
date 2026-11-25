<?php

$json = $success
    ? ['success' => true, 'redirect' => $redirect]
    : ['success' => false, 'message' => $message];
