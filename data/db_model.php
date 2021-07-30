<?php

class Database
{
    private $connection, $curr_free_items_id, $curr_free_list_id, $curr_free_position;

	public function __construct($connection)
	{
        $this->connection = $connection;
        $this->curr_free_items_id = 1 + $this->get_max_value('items', 'id'); 
        $this->curr_free_list_id = 1 + $this->get_max_value('list', 'id');
        $this->curr_free_position = 1 + $this->get_max_value('list', 'position');
    }
    
    /**
     * Get items present in the 'list' table. 
     * Return the result as an array of associative arrays representing individual items.
     */
    public function get_list_items() {
        // SQL query execution
        $query = 'SELECT items.id, items.name, list.amount, list.position FROM items JOIN list ON (items.id = list.item_id) ORDER BY list.position ASC';
        $result = $this->exec_query_and_get_result($query);

        // items properties extraction
        $items = array();
        $keys = ['id', 'name', 'amount', 'position'];
        while ($row = $result->fetch_assoc()) {
            $item = array();
            foreach ($keys as $key) {
                $item[$key] = $row[$key];
            }
            $items[$row['id']] = $item; // items saved under their specific id as a key
        }

        return $items;
    }

    /**
     * Edit the given item's amount in the 'list' table.
     */
    public function edit_list_item_amount($item_id, $new_amount) {
        // SQL query execution with escaping
        $safe_id = $this->get_safe_value($item_id);
        $safe_amount = $this->get_safe_value($new_amount);
        if ($this->is_valid_non_negative_number($safe_id) && $this->is_valid_non_negative_number($safe_amount)) {
            $this->set_list_item_property($safe_id, 'amount', $safe_amount);
        } else {
            throw new Exception("Invalid ID or amount value given");
        }    
    }

    /**
     * Add new item to the database. 
     * If present in 'items' table -> update 'list' table only .
     * If not present in 'items' -> update both 'list' and 'items' table. 
     */
    public function add_item($item_name, $amount) {
        // SQL query execution with escaping
        $safe_name = $this->get_safe_value($item_name);
        $safe_amount = $this->get_safe_value($amount);
        $query = 'SELECT name, id FROM items WHERE name = "' . $safe_name . '"';
        $result = $this->exec_query_and_get_result($query);

        // add item record to the database
        $item_id = $this->get_item_id($item_name, $result);
        if ($item_id !== '-1') {
            // item exists in 'items' table --> add item to list only (or increase its amount by $amount if present already)
            $this->add_list_item($item_id, $safe_amount);
        } else {
            // add item to both 'items' and 'list' table
            $this->add_items_item($safe_name);
            $this->add_new_list_item($this->curr_free_items_id - 1, $safe_amount);
        } 
    }

    /**
     * Delete item from the 'list' table.
     */
    public function delete_item($item_id) {
        // delete item
        $safe_id = $this->get_safe_value($item_id);
        $query = 'DELETE FROM list WHERE item_id = "' . $safe_id . '"';
        $result = $this->exec_query_and_get_result($query);

        // update positions of remaining items
        $curr_position = 1;
        $item_ids = $this->get_all_list_items_ids();
        foreach ($item_ids as $id) {
            $this->set_list_item_property($id, 'position', $curr_position);
            $curr_position ++;
        }
    }

    /**
     * Swap the positions of 2 items.
     */
    public function swap_item_positions($first_id, $second_id) {
        $safe_first_id = $this->get_safe_value($first_id);
        $safe_second_id = $this->get_safe_value($second_id);

        $first_position = $this->get_item_property_value('list', $safe_first_id, 'position');
        $second_position = $this->get_item_property_value('list', $safe_second_id, 'position');
        
        $this->set_list_item_property($safe_first_id, 'position',  $second_position);
        $this->set_list_item_property($safe_second_id, 'position',  $first_position);
    }

    private function get_safe_value ($value) {
        return $this->connection->real_escape_string($value);
    }

    private function is_valid_non_negative_number($number) {
        return is_numeric($number) && intval($number) >= 0;
    }

    private function get_item_id($item_name, $query_result) {
        $item_id = '-1'; // initialized with invalid id
        while ($row = $query_result->fetch_assoc()) {
            // $safe_name might differ from the original $item_name --> comparing to $item_name directly
            if ($row['name'] === $item_name) {
                $item_id = $row['id'];
                break;
            }
        }
        return $item_id;
    }

    private function get_item_property_value ($table_name, $item_id, $property_key) {
        $query = 'SELECT ' . $property_key . ' FROM ' . $table_name . ' WHERE item_id = "' . $item_id . '"';
        $result = $this->exec_query_and_get_result($query);
        $row = $result->fetch_assoc();
        return $row[$property_key];
    }

    private function set_list_item_property ($item_id, $property_key, $property_value) {
        $query = 'UPDATE list SET ' . $property_key . ' = "' . $property_value . '" WHERE item_id = "' . $item_id . '"';
        $result = $this->exec_query_and_get_result($query);
    }

    private function get_all_list_items_ids() {
        $query = 'SELECT item_id FROM list ORDER BY position';
        $result = $this->exec_query_and_get_result($query);
        $item_ids = array();
        while ($row = $result->fetch_assoc()) {
            $item_ids[] = $row['item_id'];
        }
        return $item_ids;
    }

    private function add_items_item($safe_name) {
        $query = 'INSERT INTO items (`id`, `name`) VALUES ("' . $this->curr_free_items_id . '", "' . $safe_name . '")';
        $result = $this->exec_query_and_get_result($query);
        $this->curr_free_items_id ++;
    }

    private function add_list_item($item_id, $amount) {
        $query = 'SELECT item_id, amount FROM list WHERE item_id = "' . $item_id . '"';
        $result = $this->exec_query_and_get_result($query);
        $row = $result->fetch_assoc();
        if ($row['item_id'] === $item_id) {
            $new_amount = strval(intval($row['amount'])+$amount);
            if (strlen($new_amount) < 4) {
                $this->edit_list_item_amount($item_id, $new_amount);
            } else {
                throw new Exception("Operation was not successful. Maximal amount value exceeded.");
            }
        } else {
            $this->add_new_list_item($item_id, $amount);
        }
    }

    private function add_new_list_item($item_id, $amount) {
        $query = 'INSERT INTO list (`id`, `item_id`, `amount`, `position`) VALUES 
            ("' . $this->curr_free_list_id . '", "' . $item_id . '", "' . $amount . '", "' . $this->curr_free_position . '")';
        $result = $this->exec_query_and_get_result($query);
        $this->curr_free_position ++;
        $this->curr_free_list_id ++;
    }

    private function get_max_value($table_name, $column) {
        $query = 'SELECT MAX(' . $column . ') FROM ' . $table_name . '';
        $result = $this->exec_query_and_get_result($query);
        $row = $result->fetch_assoc();
        return intval($row['MAX(' . $column . ')']);
    }

    private function exec_query_and_get_result($query) {
        $result = $this->connection->query($query) or $this->handle_invalid_query();
        return $result;
    }

    private function handle_invalid_query() {
        throw new Exception("Invalid query: " . $this->connection->error);
        //die("Invalid query: " . $this->connection->error);
    }

}

/**
 * Create and return an instance of Database class.
 */
function get_database_model() {
    $connection = get_database_connection();
    
    try {
        $database = new Database($connection);
    }
    catch (Exception $e) {
        throw new Exception('Exception thrown from Database constructor\n');
    }

    return $database;
}

/**
 * Connect to the database and return the instance representing the connection.
 */
function get_database_connection() {
    require_once(__DIR__ . '/db_config.php');

    $connection = new mysqli($db_config['server'],
                             $db_config['login'],
                             $db_config['password'],
                             $db_config['database']);
    if ($connection->connect_error) {
        throw new Exception('Connection to the database failed.');
    }

    return $connection;
}