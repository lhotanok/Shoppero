<?php

/**
 * Safely extract value from the given array and validate it via regex.
 */
function safe_get_value(array $array, string $key, string $type)
{
	if (array_key_exists($key, $array)) {
        $regex = get_regex($type);
        if (!$regex || ($regex && preg_match($regex, $array[$key]))) {
            return trim($array[$key]);
        }
    }
    return null; // invalid value
}

function get_regex(string $type) {
    $regex_checks = ['string' => '/^[a-z_]+$/',
                     'number' => '/^[0-9]+$/'];
    if (array_key_exists($type, $regex_checks)) {
        return $regex_checks[$type];
    }
    return null; 
}

/**
 * Create and send JSON response.
 */
function send_json_response($error = null)
{
	header('Content-Type: application/json');
	$response = ['ok' => !$error];
	if ($error) {
        $response['error'] = $error;
    }
	echo json_encode($response);
	exit;
}

function try_apply_changes ($db_model, $post_fnc, $args) {
    try {
        $db_model->$post_fnc(...$args);
    } 
    catch (Exception $e) {
        send_json_response($e->getMessage());
    }
    send_json_response(); // send response with OK state
}


/**
 * Set the new amount of the given item.
 */
function set_amount($db_model)
{
	$id = safe_get_value($_GET, 'id', 'number');
    $amount = safe_get_value($_GET, 'amount', 'number');
	if (!$id || (int)$id < 0) {
        send_json_response('Invalid ID given');
	} else {
        if ($amount === null || (int)$amount <= 0) {
            send_json_response('Invalid amount value given');
        } else {
            try_apply_changes($db_model, 'edit_list_item_amount', [$id, $amount]);
        }
    }
}
/**
 * Delete given item from the list.
 */
function delete_item($db_model)
{
	$id = safe_get_value($_GET, 'id', 'number');
	if (!$id || (int)$id < 0) {
        send_json_response('Invalid ID given');
	} else {
        try_apply_changes($db_model, 'delete_item', [$id]);
    }
}

/**
 * Swap positions of two given items.
 */
function swap_items($db_model)
{
    $first_id = safe_get_value($_GET, 'firstId', 'number');
    $second_id = safe_get_value($_GET, 'secondId', 'number');
	if (!$first_id || !$second_id || (int)$first_id < 0 || (int)$second_id < 0) {
        send_json_response('Invalid ID given');
	} else {
        try_apply_changes($db_model, 'swap_item_positions', [$first_id, $second_id]);
    }
}

/**
 * Main function.
 */
function run()
{
    require_once(dirname(__DIR__,1) . '/data/db_model.php');

    $db_model = get_database_model();

    $action = safe_get_value($_GET, 'action', 'string');

    if ($_SERVER['REQUEST_METHOD'] === 'PUT') { // modify existing record in database
        $put_method_functions = ['amount' => 'set_amount',
                                 'position' => 'swap_items'];
        if (array_key_exists($action, $put_method_functions)) {
            $put_method_functions[$action]($db_model);
        } else {
            send_json_response('No method to process query-specified action available');
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') { // delete existing record in database
        $delete_method_functions = ['delete' => 'delete_item'];
        if (array_key_exists($action, $delete_method_functions)) {
            $delete_method_functions[$action]($db_model);
        } else {
            send_json_response('No method to process query-specified action available');
        }
    } else {
        send_json_response('No method to process query-specified action available');
    }
}

try {
	run();
}
catch (Exception $e) {
	http_response_code(500);
	header('Content-Type: text/plain');
	echo $e->getMessage();
}