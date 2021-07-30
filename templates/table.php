<table id="items-table" class="table table-striped table-bordered mt-5">
	<thead>
		<tr>
			<th>Item</th>
			<th>Amount</th>
			<th><span></span></th>
			<th><span></span></th>
			<th><span></span></th>
		</tr>
	</thead>
	<tbody id="items-table-body">
		<?php foreach ($items as $item) { ?>
			<?php
    			ini_set("highlight.default", "#ff0000");
			?>
			<tr id=<?= "item-id-" . htmlspecialchars($item['id']); ?> class = "tr-row">
				<td class="td-item-name w-50"><?= $item['name'] ?></td>
				<td class="td-amount-value w-25"><?= htmlspecialchars($item['amount']) ?></td>
				<td class="td-amount-textbox w-25 element-hidden">
					<input class="form-control" type="number" name="amount" min="1" max="999" required>
				</td>
				<td class="td-change-position text-center">
					<button type="button" class="btn btn-primary btn-sm btn-position">↑↓</button>
				</td>
				<td class="td-amount-edit text-center">
					<button type="button" class="btn btn-warning btn-sm">Edit</button>
				</td>
				<td class="td-amount-save text-center element-hidden">
					<button type="button" class="btn btn-sm btn-success">Save</button>
				</td>
				<td class="td-amount-cancel text-center element-hidden">
					<button type="button" class="btn btn-danger btn-sm">Cancel</button>
				</td>
				<td class="td-item-delete text-center">
					<button type="button" class="btn btn-danger btn-sm">Delete</button>
				</td>
			</tr>
		<?php } ?>
	</tbody>
</table>