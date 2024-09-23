import sortableList from "./slist";

class CBRepeater {
    constructor() {
        this.removeRowClick = this.removeRow.bind(this);
        this.addRowClick = this.addRow.bind(this);

        this.repeaters = document.querySelectorAll('ol.repeater-field-set');

        this.addButtons();

        this.repeaters.forEach((repeater, index) => {
            this.repeaters[index].template = repeater.querySelector('li.repeater-field:last-child').cloneNode(true);
        });
    }

    instantiateSList(repeater) {
        if (repeater.classList.contains('sortable')) {
            new sortableList(repeater);
        }
    }

    addButtons() {
        this.repeaters.forEach((repeater) => {
            this.instantiateSList(repeater);

            const fields = repeater.querySelectorAll('li.repeater-field');
            fields.forEach((field) => {
                this.addRemoveButton(field, repeater);
            });
            let addButton = document.createElement('button');
            addButton.innerText = repeater.getAttribute('data-add-text');
            addButton.classList.add('repeater-add-row');
            addButton.classList.add('components-button');
            addButton.classList.add('is-secondary');
            addButton.setAttribute('type', 'button');
            addButton.addEventListener('click', this.addRowClick);
            repeater.closest('fieldset').appendChild(addButton);
        });
    }

    addRemoveButton(field, repeater) {
        let removeButton = document.createElement('button');
        let removeSpan = document.createElement('span');
        removeSpan.innerText = repeater.getAttribute('data-remove-text');
        removeButton.setAttribute('title', repeater.getAttribute('data-remove-text'));
        removeButton.appendChild(removeSpan);
        removeButton.classList.add('repeater-remove-row');
        removeButton.setAttribute('data-remove-what', field.getAttribute('data-number'));
        removeButton.setAttribute('type', 'button');
        removeButton.addEventListener('click', this.removeRowClick);
        field.querySelector('.repeater-input-container').appendChild(removeButton);
    }

    addRow(event) {
        const button = event.target;
        const repeater = button.closest('fieldset').querySelector('ol.repeater-field-set');
        const row = repeater.template.cloneNode(true);
        const idBase = repeater.getAttribute('data-root-id');

        let counter = repeater.querySelectorAll('li.repeater-field, li.repeater-field-static').length;
        let newIndex = (counter + 1);
        row.setAttribute('data-number', newIndex);
        let label = row.querySelector('label');
        label.setAttribute('for', idBase + '_' + newIndex);
        let leg = repeater.closest('fieldset').querySelector('legend');
        if (leg.getAttribute('data-shortname')) {
            label.innerText = leg.getAttribute('data-shortname') + ' ' + newIndex;
        } else {
            label.innerText = leg.innerText + ' ' + newIndex;
        }
        let input = row.querySelector('input');
        input.setAttribute('name', idBase + '[' + newIndex + ']');
        input.setAttribute('id', idBase + '_' + newIndex);
        input.setAttribute('value', '');
        input.value = '';

        repeater.appendChild(row);

        this.instantiateSList(repeater);

        input.focus();

        repeater.querySelectorAll('button.repeater-remove-row').forEach((b) => {
            b.addEventListener('click', this.removeRowClick);
        });
    }

    removeRow(event) {
        const button = event.target;
        const row = button.closest('li.repeater-field');
        const repeater = row.closest('ol.repeater-field-set');
        row.remove();
        repeater.querySelector('li:last-child input').focus();

        this.reNumber(repeater);
    }

    reNumber(repeater) {
        const rows = repeater.querySelectorAll('li.repeater-field, li.repeater-field-static');
        let i = 1;
        rows.forEach((row) => {
            let current = row.getAttribute('data-number');
            const idBase = repeater.getAttribute('data-root-id');

            row.setAttribute('data-number', i);

            /* If this is a static row, there are no other properties to set, so we bail */
            if (row.classList.contains('repeater-field-static')) {
                i++;
                return;
            }

            let label = row.querySelector('label');
            label.setAttribute('for', idBase + '_' + i);
            let leg = row.closest('fieldset').querySelector('legend');
            if (leg.getAttribute('data-shortname')) {
                label.innerText = leg.getAttribute('data-shortname') + ' ' + i;
            } else {
                label.innerText = leg.innerText + ' ' + i;
            }
            let input = row.querySelector('input');
            input.setAttribute('name', idBase + '[' + i + ']');
            input.setAttribute('id', idBase + '_' + i);

            i++;
        });

        repeater.querySelectorAll('button.repeater-remove-row').forEach((b) => {
            b.addEventListener('click', this.removeRowClick);
        });
    }
}

export default CBRepeater;