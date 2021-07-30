# Shoppero <img src=".\style\icon.png" width="50" height="50" />
`PHP` `JavaScript` `RestAPI` `MySQL`

Single page web application providing easy-to-use shopping list.
Add new items to the list, edit their amount or delete the unnecessary ones.
Organize the items according to your needs - you can swap their position
using the specialized `swap button`.

## Shopping list overview
<img src=".\documentation\gallery\overview.png"/>

## Add new item
For your convenience an autocomplete is provided. It offers all items that
have already appeared in the shopping list. The history of known items is
stored in the json file data/list_items_history. Clearing or deleting this
file results in clearing the autocomplete history as well.

If you add a new item and you happen to have an item of the same name in your
shopping list, no new item is added, only the amount is updated. However,
updating the item's amount can be done easier using the `edit button`.

<img src=".\documentation\gallery\add_item_autocomplete_1.png" width="450"/>    <img src=".\documentation\gallery\add_item_autocomplete_2.png" width="510"/>

## Edit an existing item
You can easily change the current amount of the chosen item or delete
the item permanently.

<img src=".\documentation\gallery\item_amount_edit.png" />
