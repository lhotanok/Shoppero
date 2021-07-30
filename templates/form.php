<h2>Add Item</h2>
<hr>
<form action="?" method="POST">
    <div class="row">
        <div class="form-group col-md-8">
			<label for="name">Item:</label>
            <input id= "new-item-name" list="names" class="form-control" type="text" id="name" name="name" maxlength="100" required>
            <datalist id="names">
                <?php foreach ($known_items as $item) { ?>
                    <option value="<?= htmlspecialchars($item) ?>"></option>
                <?php } ?>
            </datalist>
		</div>
        <div class="form-group col-md-2">
            <label for="age">Amount:</label>
            <input class="form-control" type="number" id="amount" name="amount" min="1" max="999" required>
        </div>
    </div>
    <div class="form-group text-center mt-4">
		<button id="add-item-btn" type="submit" class="btn btn-primary">Add</button>
	</div>
</form>