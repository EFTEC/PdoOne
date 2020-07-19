<?php

/**
 * @throws Exception
 */
function error1() {
    throw new Exception("Error");
}

function error2() {
    trigger_error("some error",E_USER_ERROR);
}

try {
    @error1();
} catch (Exception $e) {
    var_dump($e->getMessage());
}
try {
    @error2();
} catch (Exception $e) {
    var_dump($e->getMessage());
}