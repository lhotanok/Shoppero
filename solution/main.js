function getChildByClass(elementClass, parentElement) {
    // get list of elements nodes, only 1 item of such class exists within each row (in this context)
    let elements = parentElement.getElementsByClassName(elementClass);
    return elements[0]; // elements[0] is the actual cell element
}

function getGrandchildByClasses(childClass, grandchildClass, grandparentElement) {
    let child = getChildByClass(childClass, grandparentElement);
    let grandchild = getChildByClass(grandchildClass, child);
    return grandchild;
}

function toggleElementDisplay(element) {
    if (isElementHidden(element)) {
        showElement(element);
    } else {
        hideElement(element);
    }
}

function isElementHidden(element) {
    return element.classList.contains('element-hidden');
}

function hideElement(element) {
    element.classList.add('element-hidden');
}

function showElement(element) {
    element.classList.remove('element-hidden');
}

function deleteElement(element) {
    element.parentNode.removeChild(element);
}

function deleteElementById(elementId) {
    let element = document.getElementById(elementId);
    deleteElement(element);
}

function toggleEditRelatedComponentsDisplay (row, cellClasses) {
    const elementsToToggle = ["amountValue", "editTextbox", "deleteButton", "cancelButton", "editButton", "saveButton"];
    for (elementType of elementsToToggle) {
        let element = getChildByClass(cellClasses[elementType], row);
        toggleElementDisplay(element);
    }
}

function isValidInputNumber (value) {
    return !isNaN(value) && value > 0;
}

function getRowIdValue (row) {
    const rowIdPrefixLength = 8; // id of each row is of the following format: item-id-xx
    return row.id.substring(rowIdPrefixLength);
}

function hideLastRowPositionButton(rows, cellClasses) {
    let lastRow = rows[rows.length - 1];
    let positionButton = getGrandchildByClasses(cellClasses["positionButton"], "btn", lastRow);
    hideElement(positionButton);
}

function swapButtonsBetweenRows(buttonClass, firstRow, secondRow, firstButton, secondButton) {
    let firstButtonFrame = getChildByClass(buttonClass, firstRow);
    let secondButtonFrame = getChildByClass(buttonClass, secondRow);

    firstButtonFrame.removeChild(firstButton);
    secondButtonFrame.removeChild(secondButton);

    firstButtonFrame.appendChild(secondButton);
    secondButtonFrame.appendChild(firstButton);

    if (!secondRow.nextElementSibling) {
        showElement(firstButton);
        hideElement(secondButton);
    }
}

function handleEditButtonClick(row, cellClasses) {
    let editButton = getGrandchildByClasses(cellClasses["editButton"], "btn", row);
    editButton.addEventListener('click', function(ev) {
        let currRow = editButton.parentNode.parentNode;
        toggleEditRelatedComponentsDisplay(currRow, cellClasses);
        let currAmount = getChildByClass(cellClasses["amountValue"], currRow);
        let editTextbox = getGrandchildByClasses(cellClasses["editTextbox"], "form-control", currRow);
        editTextbox.value = currAmount.textContent;
    });
}

function handleCancelButtonClick(row, cellClasses) {
    let cancelButton = getGrandchildByClasses(cellClasses["cancelButton"], "btn", row);
    cancelButton.addEventListener('click', function(ev) {
        let currRow = cancelButton.parentNode.parentNode;
        toggleEditRelatedComponentsDisplay(currRow, cellClasses);
    });
}

function handleSaveButtonClick(row, cellClasses) {
    let saveButton = getGrandchildByClasses(cellClasses["saveButton"], "btn", row);
    saveButton.addEventListener('click', function(ev) {
        let currRow = saveButton.parentNode.parentNode;
        let editTextbox = getGrandchildByClasses(cellClasses["editTextbox"], "form-control", currRow);
        let amount = parseInt(editTextbox.value);

        if (!isValidInputNumber(amount)) {
            window.alert("Invalid number given.");
        } else {
            let id = getRowIdValue(currRow);

            // Set item's amount asynchronously
            model.setAmount(id, amount, function(error = null) {
                if (error) { // async update failed
                    window.alert(error);
                } else {
                    // Update amount value and Hide edit related components
                    let prevAmount = getChildByClass(cellClasses["amountValue"], currRow);
                    prevAmount.textContent = amount;
                    toggleEditRelatedComponentsDisplay(currRow, cellClasses);
                }
            });
        }
    });
}

function handleDeleteButtonClick(row, cellClasses) {
    let deleteButton = getGrandchildByClasses(cellClasses["deleteButton"], "btn", row);
    deleteButton.addEventListener('click', function(ev) {
        let currRow = deleteButton.parentNode.parentNode;
        let id = getRowIdValue(currRow);

        // Delete row with chosen item asynchronously
        model.deleteItem(id, function(error = null) {
            if (error) { // async update failed
                window.alert(error);
            } else {
                // Delete the whole row with all its components
                if (!currRow.nextElementSibling) {
                    if (currRow.previousElementSibling) {
                        let prevPositionButton = getGrandchildByClasses(cellClasses["positionButton"], "btn", currRow.previousElementSibling);
                        hideElement(prevPositionButton);
                    }
                }
                deleteElementById(currRow.id);
            }
        });
    });
}

function handlePositionButtonClick(row, cellClasses) {
    let positionButton = getGrandchildByClasses(cellClasses["positionButton"], "btn", row);
    positionButton.addEventListener('click', function(ev) {
        let currRow = positionButton.parentNode.parentNode;
        let nextRow = currRow.nextElementSibling;

        let firstId = getRowIdValue(currRow);
        let secondId = getRowIdValue(nextRow);

        // Swap rows with chosen items asynchronously
        model.swapItemPositions(firstId, secondId, function(error = null) {
            if (error) { // async update failed
                window.alert(error);
            } else {
                // Swap table rows' values (positionButton stays in place)
                let secondPositionButton = getGrandchildByClasses(cellClasses["positionButton"], "btn", nextRow);
                swapButtonsBetweenRows(cellClasses["positionButton"], currRow, nextRow, positionButton, secondPositionButton);
                let tableBody = document.getElementById("items-table-body");
                tableBody.insertBefore(nextRow, currRow); // swap rows
            }
        });
    });
}

function handleAddItemButtonClick() {
    let addItemButton = document.getElementById("add-item-btn");

    let forbiddenRegExpressions;
    fetch("././data/forbidden_regex.json")
        .then(response => response.json())
        .then(json => forbiddenRegExpressions = json);
    
    addItemButton.addEventListener('click', function(ev) {
        let newItemFormField = document.getElementById("new-item-name");
        let newItemName = newItemFormField.value;
        for (let regex of forbiddenRegExpressions) {
            if (newItemName.match(regex)) {
                window.alert("Invalid item name given.");
                ev.preventDefault(); // prevents POST request (POST form)
                return;
            }  
        }
    });
}

document.addEventListener('DOMContentLoaded', function(){
    const cellSpecificClasses = {"saveButton": "td-amount-save", 
                                "editButton": "td-amount-edit",
                                "cancelButton": "td-amount-cancel",
                                "deleteButton": "td-item-delete",
                                "positionButton": "td-change-position",
                                "editTextbox": "td-amount-textbox",
                                "amountValue": "td-amount-value",
                                "itemName" : "td-item-name"
                            };

    let tableBody = document.getElementById("items-table-body");
    let rows = tableBody.getElementsByClassName("tr-row");

    hideLastRowPositionButton(rows, cellSpecificClasses);

    for (let row of rows) {        
        handleEditButtonClick(row, cellSpecificClasses);
        handleCancelButtonClick(row, cellSpecificClasses);
        handleSaveButtonClick(row, cellSpecificClasses);
        handleDeleteButtonClick(row, cellSpecificClasses);
        handlePositionButtonClick(row, cellSpecificClasses);
    }

    handleAddItemButtonClick();

    const DataModel = module.exports.DataModel;
	model = new DataModel('restapi/index.php');
    
});
