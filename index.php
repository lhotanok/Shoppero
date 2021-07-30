<?php

define('DATA_FILE', __DIR__ . '/data/list_items_history.json');
define('REGEX_FILE', __DIR__ . '/data/forbidden_regex.json');

ini_set("highlight.html", "#ff0000");

function process_form_post_request($database) {
    // add item posted in the form into the database
    add_form_item($database);
    
    // redirect to self
    $relative_URL = $_SERVER['REQUEST_URI'];
    header('Location: ' . $relative_URL);
    exit;
}

function safe_get_value(array $array, string $key) {
    if (array_key_exists($key, $array)) {
        return trim($array[$key]);
    }
    return null;
}

function matches_valid_regex ($key, $value) {
    $regex_data = json_decode(file_get_contents(REGEX_FILE));
    foreach ($regex_data as $regex) {
        if (preg_match($regex, $value)) {
            return false;
        }
    }
    return true;
}

function is_valid_mandatory_item($key, $value) {
    return $value !== null;
}

function is_non_empty_string($key, $value) {
    return $value !== '';
}

function is_valid_positive_number($key, $value) {
    return is_numeric($value) && intval($value) > 0;
}

function has_valid_length($key, $value) {
    $max_lengths = [
        'name'  => 100,
        'amount' => 3
    ];
    return strlen($value) <= $max_lengths[$key];
}

function highlight_words_with_invalid_regex (&$items) {
    $regex_data = json_decode(file_get_contents(REGEX_FILE));
    foreach ($items as &$item) {
        foreach ($regex_data as $regex) {
            if (preg_match($regex, $item['name'])) {
                $item['name'] = highlight_string($item['name'], true); 
                break;  
            }
        }
    }
}

function add_form_item($db_model) {
    $form_items = [
        'name' => ['is_valid_mandatory_item', 'is_non_empty_string', 'has_valid_length', 'matches_valid_regex'], 
        'amount' => ['is_valid_mandatory_item', 'is_valid_positive_number', 'has_valid_length']
    ];

    $item_record = array();
    foreach ($form_items as $item_name => $validators) {
        $item_value = safe_get_value($_POST, $item_name); // null if not in $_POST array
        foreach ($validators as $fnc) {
            $valid = $fnc($item_name, $item_value);
            if (!$valid) {
                throw new Exception('Invalid form posted');
            }
        }
        $item_record[$item_name] = $item_value;
    }

    $db_model->add_item($item_record['name'], intval($item_record['amount']));
}

function get_list_items_history() {
    if (file_exists(DATA_FILE)) {
        $data = json_decode(file_get_contents(DATA_FILE));
	}
	else {
		$data = [];
    }
    return $data;
}

function save_into_list_items_history($items)
{
    $curr_data = get_list_items_history();
    foreach ($items as $item) {
        if (!in_array($item['name'], $curr_data)) {
            $curr_data[] = $item['name'];
        }
    }
	file_put_contents(DATA_FILE, json_encode($curr_data, JSON_PRETTY_PRINT));
}

/**
 * Main function.
 */
function run()
{
    require_once(__DIR__ . '/data/db_model.php');
    require_once(__DIR__ . '/webpage_viewer.php');
    
    $db_model = get_database_model();
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        process_form_post_request($db_model);
    }

    $curr_items = $db_model->get_list_items();
    save_into_list_items_history($curr_items);

    highlight_words_with_invalid_regex($curr_items);

    $known_items = get_list_items_history();

    display_page($curr_items, $known_items);
}

try {
	run();
}
catch (Exception $e) {
	http_response_code(500);
	header('Content-Type: text/plain');
	echo $e->getMessage();
}
