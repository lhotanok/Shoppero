# Shoppero <img src=".\style\icon.png" width="50" height="50" />
`PHP` `JavaScript` `RestAPI` `MySQL`

Single page web application providing easy-to-use shopping list. Add new items to the list, edit their amount or delete the unnecessary ones. Organize the items according to your needs - you can swap their position using the specialized `swap button`.

The list items are stored in the MySQL database. Operations of amount editing and item's deleting are performed asynchronously (using standard AJAX and Rest API). New item addition is implemented by PHP form processing.

## App overview
<img src=".\gallery\overview.png"/>

## Write down new list item
For your convenience an autocomplete is provided. It offers all items that have already appeared in the shopping list. The history of known items is stored in the json file *data/list_items_history.json*. Clearing or deleting this file results in clearing the autocomplete history as well.

<img src=".\gallery\add_item_autocomplete_1.png" height="200" />
<img src=".\gallery\add_item_autocomplete_2.png" height="177" />

### Item name collision
If you add a new item and you happen to have an item of the same name in your shopping list, no new item is added, only the amount is updated. However, updating the item's amount can be done easier using the `edit button`.

### Valid item name
New item's name is checked against the regex stored in *data/forbidden_regex.json*. Currently only the alphabet characters (including Czech diacritics) are allowed along with the whitespace character. The regular expression matching the forbidden item's name can be modified. Another regex could be added as well into the json list in *data/forbidden_regex.json*.

## Edit an existing item
You can easily change the current amount of the chosen item or delete the item permanently.

<img src=".\gallery\item_amount_edit.png" />

---
Application was tested locally with the following setup:
- Apache 2.4.48
- PHP 8.0.8
- MySQL 
- Win64

Database schema includes 2 tables:
- **items** (id, name)
- **list** (id, item_id, amount, position)
