class DataModel {
	/**
	 * Initialize the data model with URL pointing to the corresponding REST API.
	 * @param {string} apiUrl Api URL prefix (with no query part)
	 */
	constructor(apiUrl)
	{
		this.apiUrlPrefix = apiUrl;
    }
    /**
	 * Modify the given list item's amount.
	 * @param {string} id Item ID of the list item (foreign key)
	 * @param {string} amount New value of the item's amount
	 * @param {Function} callback Function invoked once the operation is completed.
	 *                            Error message is passed to the callback function if operation fails.
	 */
	setAmount(id, amount, callback = null)
	{
		let query = "?action=amount&id=" + id + "&amount=" + amount;
		this.fetchResult(query, 'PUT', callback);
	}

	/**
	 * Delete item of the given ID from the list.
	 * @param {string} id Item ID of the list item (foreign key)
	 * @param {Function} callback Function invoked once the operation is completed.
	 *                            Error message is passed to the callback function if operation fails.
	 */
	deleteItem(id, callback = null)
	{
		let query = "?action=delete&id=" + id;
		this.fetchResult(query, 'DELETE', callback);
	}

	/**
	 * Swap positions of two items of the given IDs in the list.
	 * @param {string} firstId ID of the first list item (foreign key)
	 * @param {string} secondId ID of the second list item (foreign key)
	 * @param {Function} callback Function invoked once the operation is completed.
	 *                            Error message is passed to the callback function if operation fails.
	 */
	swapItemPositions(firstId, secondId, callback = null)
	{
		let query = "?action=position&firstId=" + firstId + "&secondId=" + secondId;
		this.fetchResult(query, 'PUT', callback);
	}

	/**
	 * Perform asynchronous data fetch from the REST API.
	 * @param {string} query Query part of the URL.
	 * @param {string} currMethod Method corresponding the desired update.
	 * @param {Function} callback Function invoked once the operation is completed.
	 *                            Error message is passed to the callback function if operation fails.
	 */
	fetchResult(query, currMethod, callback) {
		this.successful = false;
		fetch(this.apiUrlPrefix + query, {method: currMethod})
			.then((response) => response.json())
			.then((responseCollection) => this.setResponseResult(responseCollection))
			.catch((error) => {
				if (callback) {
					callback(error.message);
				}
			})
			.finally(() => {
				if (this.successful) {
					if (callback) {
						callback();
					}
				}
			});
	}

	setResponseResult(responseCollection)
	{
		this.successful = responseCollection.ok; 
		if (!this.successful) {
			throw new Error(this.getErrorMessage(responseCollection));
		}
    }
    
    getErrorMessage(collection) 
	{
		let errorMessage = "";
		if ('error' in collection) {
			errorMessage = collection.error;
		}
		return errorMessage;
	}
	
}

module.exports = { DataModel };
