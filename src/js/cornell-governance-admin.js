import CBRepeater from "./modules/repeater";
import updateNotice from "./modules/updateNotice";
import governanceDocs from "./cornell-governance/documentation";
import governanceTooltip from "./modules/tooltip";
import governanceTabs from "./modules/tabs";
import HTMLParser from "./modules/HTMLParser";

class CornellGovernanceAdmin {
    constructor() {
        this.init();
        this.setupLightboxes();
        this.currentBoxText = '';
        this.currentBox = false;
    }

    log(message) {
        if (typeof console !== 'undefined') {
            console.log(message);
        }
    }

    init() {
        if (document.querySelectorAll('.cornell-governance-save-info, .cornell-governance-save-notes, ol.repeater-field-set').length <= 0) {
            return;
        }

        this.hideSaveInfoInstructions();

        this.saveInfoClick = this.saveInfo.bind(this);
        this.saveNotesClick = this.saveNotes.bind(this);
        this.saveDeleteClick = this.saveDeletion.bind(this);
        this.finishedSave = this.savedMeta.bind(this);
        this.updateDataAfterSave = this.afterSave.bind(this);
        this.saveError = this.errorOnSave.bind(this);
        this.updateTimestamp = this.setTimestamp.bind(this);

        this.somethingChanged = false;
        this.inputChanged = this.inputChange.bind(this);
        this.confirmLeave = this.abandonChanges.bind(this);
        this.saveToDo = this.saveTaskCheck.bind(this);

        this.revealNotesEditor = this.notesEditorReveal.bind(this);
        this.hideNotesEditor = this.notesEditorHide.bind(this);
        this.updateNotesEditor = this.updateViewableNotes.bind(this);

        if (document.querySelectorAll('.cornell-governance-save-info').length >= 1) {
            const buttons = document.querySelectorAll('.cornell-governance-save-info button:not(.governance-tooltip-opener)');
            buttons.forEach((button) => {
                button.addEventListener('click', (e) => {
                    this.saveInfoClick(e);
                });
            });
        }
        if (document.querySelectorAll('.cornell-governance-save-notes').length >= 1) {
            document.querySelector('.cornell-governance-save-notes button:not(.governance-tooltip-opener, .is-secondary)').addEventListener('click', (e) => {
                this.saveNotesClick(e);
            });
        }
        if(document.querySelectorAll('.cornell-governance-deletion-submit').length >= 1) {
            document.querySelector('.cornell-governance-deletion-submit button:not(.governance-tooltip-opener)').addEventListener('click',(e) => {
                this.saveDeleteClick(e);
            });
        }

        const inputs = document.querySelectorAll('.postbox#cornell-governance-page-info .cornell-governance-tabpanel > :not(#cornell-governance-page-notes) :is(input, select, textarea)');
        inputs.forEach((input) => {
            this.log(input);
            input.addEventListener('change', this.inputChanged);
        });

        if (document.querySelectorAll('ol.repeater-field-set').length >= 1) {
            this.repeater = new CBRepeater();

            const repeaterButtons = document.querySelectorAll('.postbox#cornell-governance-page-info .cornell-governance-tabpanel > :not(#cornell-governance-page-notes) :is(button.repeater-remove-row, button.repeater-add-row)');
            repeaterButtons.forEach((input) => {
                this.log(input);
                input.addEventListener('click', this.inputChanged);
            });
        }
        this.maybeHideConfirm();

        this.tooltips = [];
        const tooltips = document.querySelectorAll('.governance-tooltip-container');
        tooltips.forEach((tooltip) => {
            this.tooltips.push(new governanceTooltip(tooltip));
        });

        this.tabLists = [];
        const tabLists = document.querySelectorAll('.cornell-governance-metabox .inside [role="tablist"], .cornell-governance-tablist [role="tablist"]');
        tabLists.forEach((tabList) => {
            this.tabLists.push(new governanceTabs(tabList));
        });

        this.updateNotice = new updateNotice();

        if (document.querySelectorAll('.cornell-governance-reveal-toggle-notes-editor button').length >= 1) {
            const notesEditors = document.querySelectorAll('.cornell-governance-reveal-toggle-notes-editor button');
            notesEditors.forEach((editor) => {
                editor.addEventListener('click', this.revealNotesEditor);
                this.hideNotesEditor(editor);
            });
        }
    }

    setupLightboxes() {
        new governanceDocs();
    }

    inputChange(e) {
        this.somethingChanged = true;
        window.addEventListener('beforeunload', this.confirmLeave);
        this.showSaveInfoInstructions();
    }

    /**
     * @TODO: Look at using sendBeacon instead of running AJAX calls every time this occurs
     * @see https://developer.mozilla.org/en-US/docs/Web/API/Navigator/sendBeacon
     *
     * @param e the event that triggered this action
     */
    saveTaskCheck(e) {
        let checkbox = e.target;
        if (checkbox.checked) {
            checkbox.closest('label').classList.add('done');
        } else {
            checkbox.closest('label').classList.remove('done');
        }

        this.saveInfo(e, {'save-action': 'completed-tasks'});

        this.toggleConfirm(checkbox);
    }

    abandonChanges(e) {
        document.querySelector('#cornell-governance-page-info').scrollIntoView();

        e.preventDefault();

        return (e.returnValue = 'Are you sure you want to leave without saving changes to the Governance Information?');
    }

    prepareSave(target, atts) {
        const form = target.closest('[role="tabpanel"]:not([hidden])');
        this.log('The form constant is set to: ' + form);

        let formData = new FormData();
        formData.set('action', atts.ajax_action);

        if (atts.hasOwnProperty('save-action')) {
            formData.set('save-action', atts['save-action']);
        }

        let isReadOnly = false;
        this.log(this.activeFormTab);
        if (typeof this.activeFormTab !== 'undefined' && this.activeFormTab.querySelectorAll('[name$="-readonly"]').length >= 1) {
            isReadOnly = true;
        }

        const fields = form.querySelectorAll(':is(input, select, textarea, button)');
        fields.forEach((field) => {
            if (null === field.getAttribute('name')) {
                return;
            }
            if ('radio' === field.getAttribute('type') || 'checkbox' === field.getAttribute('type')) {
                if (!field.checked) {
                    return;
                }
            }
            let value = field.value;
            if ('BUTTON' === field.tagName) {
                value = field.innerText;
            }

            this.log('We are going to append ' + value + ' as the value of ' + field.getAttribute('name'));
            formData.append(field.getAttribute("name"), value);
        });


        const request = new Request(atts.ajax_url, {
            method: "POST",
            body: formData
        });

        this.createOverlay(target);
        fetch(request)
            .then((response) => {
                if (!response.ok) {
                    throw response;
                }

                return response.json()
            })
            .then((text) => {
                let json = text.data;
                this.log(text);
                this.finishedSave(target);
                return json;
            })
            .then((json) => {
                this.log(json);
                this.updateDataAfterSave(target, json);
            })
            .catch((error) => {
                this.log(error);
                this.saveError(error);
            });
    }

    saveInfo(e, attributes) {
        e.preventDefault();

        attributes = attributes || {};

        this.log('The selected input appears to be: ' + e.target);

        let viewBox = e.target.closest('#cornell-governance-metabox-tab-liaison');
        if ( typeof viewBox === 'undefined' || viewBox === null ) {
            this.log('The selected input does not appear to be inside of the Liaison View');
            viewBox = e.target.closest('#cornell-governance-metabox-tab-steward');
        }

        let container = e.target.closest('.postbox');
        let head = container.querySelector('.hndle');
        this.currentBox = container.querySelector('.cornell-governance-metabox');
        this.activeFormTab = this.currentBox;
        if (this.currentBox.querySelectorAll('[role="tabpanel"]').length >= 1) {
            this.activeFormTab = this.currentBox.querySelector('[role="tabpanel"]:not([hidden])');
        }
        this.currentBoxText = head.innerText;
        this.timestampField = this.currentBox.querySelector('.cornell-governance-timestamp');
        this.timestampField.querySelector('input[type=hidden]').value = this.getCurrentDateTime();

        let atts = {
            'ajax_action': 'cornell_governance_save_meta_info',
            'ajax_url': new URL('/wp-admin/admin-ajax.php', 'http://cornell-governance.local'),
        }

        if (typeof CornellGovernanceAdminAJAX !== "undefined" && 'info' in CornellGovernanceAdminAJAX) {
            atts = CornellGovernanceAdminAJAX.info;
        }

        if (Object.keys(attributes).length >= 1) {
            atts = {...atts, ...attributes};
        }

        this.log('The viewBox variable is set to:' + viewBox);
        this.prepareSave(viewBox, atts);

        this.hideSaveInfoInstructions();
    }

    saveDeletion(e, attributes) {
        e.preventDefault();

        let container = e.target.closest('.postbox');
        let head = container.querySelector('.hndle');
        this.currentBox = container.querySelector('.cornell-governance-metabox');
        this.activeFormTab = this.currentBox;
        if (this.currentBox.querySelectorAll('[role="tabpanel"]').length >= 1) {
            this.log('It appears there are tab panels inside the metabox');
            this.activeFormTab = this.currentBox.querySelector('[role="tabpanel"]:not([hidden])');
            head = this.activeFormTab.querySelector('legend');
        }
        this.currentBoxText = head.innerText;
        this.timestampField = this.activeFormTab.querySelector('.cornell-governance-timestamp');
        this.timestampField.querySelector('input[type=hidden]').value = this.getCurrentDateTime();

        this.log('Head: ');
        this.log(head);
        this.log('Current Box Text: ' + this.currentBoxText);
        this.log('Current Box: ');
        this.log(this.currentBox);
        this.log('Current Active Tab:');
        this.log(this.activeFormTab);

        let atts = {
            'ajax_action': 'cornell_governance_save_meta',
            'ajax_url': new URL('/wp-admin/admin-ajax.php', 'http://cornell-governance.local'),
        }

        if (typeof CornellGovernanceAdminAJAX !== "undefined" && 'deletion' in CornellGovernanceAdminAJAX) {
            atts = CornellGovernanceAdminAJAX.deletion;
        }

        this.prepareSave(document.getElementById('cornell-governance-page-deletion'), atts);
    }

    savedMeta(target) {
        this.updateTimestamp(target);
        window.removeEventListener('beforeunload', this.confirmLeave);
        this.log('The request appears to have finished loading');
        this.t = setTimeout(this.removeOverlay.bind(this), 2000, target);
    }

    afterSave(target, json) {
        this.log('Here is the value of json:');
        this.log(json);

        const fields = [
            'goals',
            'liaison',
            'primary-audience',
            'problem',
            'review-cycle',
            'secondary-audience',
            'supervisor'
        ];

        fields.forEach((field) => {
            let fieldname = field + '-formatted';
            if (json.hasOwnProperty(fieldname)) {
                if (document.querySelectorAll('#cornell-governance-page-info-' + field + '-readonly').length >= 1) {
                    const placeholder = document.createElement('div');
                    placeholder.innerHTML = json[fieldname];
                    let input = document.getElementById('cornell-governance-page-info-' + field + '-readonly');
                    input.replaceWith(placeholder.firstElementChild);
                }
            }
        });

        if (json.hasOwnProperty('compliance-fieldset')) {
            this.updateComplianceFieldset(json);
        }

        if (json.hasOwnProperty('task-list-checkboxes')) {
            this.updateTaskListCheckboxes(json);
        }

        if (json.hasOwnProperty('notes')) {
            this.updateNotesEditor(json);
        }

        this.maybeHideConfirm();

        return json;
    }

    updateComplianceFieldset(json) {
        const fieldsets = document.querySelectorAll('.compliance-status-fieldset');
        fieldsets.forEach((fieldset) => {
            const fieldsetParent = fieldset.closest('.cornell-governance-fieldset');

            let parsed = null;

            if (fieldsetParent.querySelectorAll('input[type="radio"]').length >= 1) {
                const parser = new HTMLParser(json['compliance-fieldset'].liaison);
                parsed = parser.parseHTML();
            } else {
                const parser = new HTMLParser(json['compliance-fieldset'].steward);
                parsed = parser.parseHTML();
            }

            const taskList = fieldsetParent.querySelector('.cornell-governance-tasks');

            if (taskList) {
                if (json.hasOwnProperty('task-list-checkboxes') && taskList.querySelectorAll('input[type="checkbox"]').length >= 1) {
                    const taskParser = new HTMLParser(json['task-list-checkboxes']);
                    const parsedTasks = taskParser.parseHTML();
                    taskList.replaceWith(parsedTasks.querySelector('.cornell-governance-tasks'));
                } else {
                    const allTasks = taskList.querySelectorAll('input[type="checkbox"]');
                    if (allTasks.length >= 1) {
                        allTasks.forEach((task) => {
                            task.checked = false;
                            task.closest('label').classList.remove('done');
                        })
                    }
                }
            }

            fieldset.replaceWith(parsed.querySelector('.compliance-status-fieldset'));
        });
    }

    updateTaskListCheckboxes(json) {
        const taskLists = document.querySelectorAll('.cornell-governance-tasks');
        if (taskLists.length <= 0) {
            return;
        }

        taskLists.forEach((list) => {
            const tasks = list.querySelectorAll('input[type="checkbox"], ol.cornell-governance-page-info-tasks-readonly > li');
            if (tasks.length <= 0) {
                return;
            }

            const taskParser = new HTMLParser(json['task-list-checkboxes']);
            const parsedTasks = taskParser.parseHTML();
            list.replaceWith(parsedTasks.querySelector('.cornell-governance-tasks'));
        });
    }

    updateViewableNotes(json) {
        const viewableNotes = document.getElementById('cornell-governance-page-notes-notes-readonly');
        this.log(viewableNotes);

        const container = viewableNotes.closest('.cornell-governance-notes-container');
        this.log(container);

        if (json.hasOwnProperty('notes-rendered')) {
            viewableNotes.querySelector('.input-value').innerHTML = json['notes-rendered'];
        } else {
            this.log('The notes property does not appear to exist in the JSON object');
        }

        this.hideNotesEditor(viewableNotes);

        return;
    }

    errorOnSave(error) {
        this.log(error);

        error.json().then((body) => {
            //Here is already the payload from API
            console.log(body);
        });

        let overlay = document.querySelector('.inside .cornell-governance-metabox .progress-overlay');
        if (overlay) {
            this.log('Removing the progress overlay div');
            overlay.remove();
        } else {
            this.log('The overlay does not exist, so there is nothing to remove');
        }

        this.updateNotice.setOuterContainer(this.currentBox);
        this.updateNotice.insertNotice('There was an error saving ' + this.currentBoxText);
        this.currentBox = false;
        this.currentBoxText = '';
    }

    saveNotes(e) {
        e.preventDefault();

        let container = e.target.closest('.postbox');
        let head = container.querySelector('.hndle');
        this.currentBox = container.querySelector('.cornell-governance-metabox');
        this.activeFormTab = this.currentBox;
        if (this.currentBox.querySelectorAll('[role="tabpanel"]').length >= 1) {
            this.activeFormTab = this.currentBox.querySelector('[role="tabpanel"]:not([hidden])');
            head = this.currentBox.querySelector('legend');
        }
        this.currentBoxText = head.innerText;
        this.timestampField = this.activeFormTab.querySelector('.cornell-governance-timestamp');
        this.timestampField.querySelector('input[type=hidden]').value = this.getCurrentDateTime();

        let atts = {
            'ajax_action': 'cornell_governance_save_meta_notes',
            'ajax_url': new URL('/wp-admin/admin-ajax.php', 'http://cornell-governance.local'),
        }

        if (typeof CornellGovernanceAdminAJAX !== "undefined" && 'notes' in CornellGovernanceAdminAJAX) {
            atts = CornellGovernanceAdminAJAX.notes;
        }

        this.prepareSave(document.getElementById('cornell-governance-page-notes'), atts);
    }

    createOverlay(target) {
        const metabox = target.closest('.inside .cornell-governance-metabox');
        if (metabox.querySelectorAll('.progress-overlay').length >= 1) {
            this.log('Progress overlay appears to already exist, so we will not add it a second time');
            return;
        }

        this.log('Creating the progress overlay div');
        const overlay = document.createElement('div');
        overlay.classList.add('progress-overlay');
        const spinner = document.createElement('div');
        spinner.classList.add('lds-dual-ring');
        const loadingText = document.createElement('p');
        loadingText.classList.add('screen-reader-text');
        loadingText.innerText = 'Saving your changes';
        spinner.appendChild(loadingText);
        overlay.appendChild(spinner);
        metabox.appendChild(overlay);
    }

    removeOverlay(target) {
        const metabox = target.closest('.inside .cornell-governance-metabox');
        let overlay = metabox.querySelector('.progress-overlay');
        if (overlay) {
            this.log('Removing the progress overlay div');
            overlay.remove();
        } else {
            this.log('The overlay does not exist, so there is nothing to remove');
        }
        this.updateNotice.setOuterContainer(this.currentBox);
        this.updateNotice.insertNotice('The ' + this.currentBoxText + ' has been saved');
        this.currentBox = false;
        this.currentBoxText = '';
    }

    setTimestamp(target) {
        if (target.id === 'cornell-governance-metabox-tab-info-liaison') {
            this.setTimestamp(document.getElementById('cornell-governance-metabox-tab-info-steward'));
        } else if (target.id === 'cornell-governance-metabox-tab-info-steward') {
            this.setTimestamp(document.getElementById('cornell-governance-metabox-tab-info-liaison'));
        }

        const timestampField = target.querySelector('.cornell-governance-timestamp');
        const timestampInput = timestampField.querySelector('input[type=hidden]');
        const timestampLabel = timestampField.querySelector('label');
        const timeValue = timestampInput.value;
        const originalLabel = timestampInput.getAttribute('data-original-label');
        timestampLabel.innerText = originalLabel + ': ' + timeValue;
    }

    getCurrentDateTime() {
        const now = new Date();
        let year = now.getFullYear();
        let month = now.getMonth() + 1; // Note: Months are zero-based (0 = January)
        let day = now.getDate();
        let hours = now.getHours();
        let minutes = now.getMinutes();
        let seconds = now.getSeconds();

        let formatted = {
            'month': '00' + month,
            'day': '00' + day,
            'hours': '00' + hours,
            'minutes': '00' + minutes,
            'seconds': '00' + seconds,
        }

        for (let i in formatted) {
            if (formatted.hasOwnProperty(i)) {
                formatted[i] = formatted[i].substring(formatted[i].length - 2);
            }
        }

        month = formatted.month;
        day = formatted.day;
        hours = formatted.hours;
        minutes = formatted.minutes;
        seconds = formatted.seconds;

        const formattedDateTime = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;

        return formattedDateTime;
    }

    showSaveInfoInstructions() {
        if (document.querySelectorAll('.cornell-governance-save-box').length <= 0) {
            return;
        }

        document.querySelector('.cornell-governance-save-box').style.display = 'block';
    }

    hideSaveInfoInstructions() {
        if (document.querySelectorAll('.cornell-governance-save-box').length <= 0) {
            return;
        }

        document.querySelector('.cornell-governance-save-box').style.display = 'none';
    }

    maybeHideConfirm() {
        const taskCheckboxes = document.querySelectorAll(':is(.cornell-governance-page-info-tasks, .cornell-governance-page-info-tasks-readonly) input[type="checkbox"]');
        const taskVisuals = document.querySelectorAll(':is(.cornell-governance-page-info-tasks, .cornell-governance-page-info-tasks-readonly):not(:has(input))');
        if (taskCheckboxes.length >= 1) {
            taskCheckboxes.forEach((checkbox) => {
                this.toggleConfirm(checkbox);
                checkbox.addEventListener('change', this.saveToDo);
            });
        } else if (taskVisuals.length >= 1) {
            this.toggleConfirm(taskVisuals[0]);
        }
    }

    toggleConfirm(checkbox) {
        const tasklist = checkbox.closest('.cornell-governance-tasks');
        if (tasklist.querySelectorAll('input[type="checkbox"]:not(:checked)').length >= 1) {
            this.hideConfirm(tasklist);
        } else if (tasklist.querySelectorAll('input[type="checkbox"]').length <= 0) {
            // There do not appear to be any tasks, so there is nothing to review
            this.hideConfirm(tasklist);
        } else {
            this.showConfirm(tasklist);
        }
    }

    showConfirm(tasklist) {
        if (document.querySelectorAll('.cornell-governance-confirm-page-review').length <= 0) {
            return;
        }

        let tabPanel = document.querySelector('#cornell-governance-page-info');
        if (document.querySelectorAll('#cornell-governance-page-info [role="tabpanel"]').length >= 1) {
            tabPanel = tasklist.closest('[role="tabpanel"]');
        }

        const saveButton = tabPanel.querySelector('.cornell-governance-confirm-page-review');
        const reviewFieldset = saveButton.closest('fieldset');
        reviewFieldset.style.display = 'block';
    }

    hideConfirm(tasklist) {
        if (document.querySelectorAll('.cornell-governance-confirm-page-review').length <= 0) {
            return;
        }

        let tabPanel = document.querySelector('#cornell-governance-page-info');
        if (document.querySelectorAll('#cornell-governance-page-info [role="tabpanel"]').length >= 1) {
            tabPanel = tasklist.closest('[role="tabpanel"]');
        }

        const saveButton = tabPanel.querySelector('.cornell-governance-confirm-page-review');
        const reviewFieldset = saveButton.closest('fieldset');
        reviewFieldset.style.display = 'none';
    }

    notesEditorReveal(e) {
        e.preventDefault();

        const container = e.target.closest('.cornell-governance-notes-container');
        const hide = container.querySelector('.cornell-governance-viewable-field');
        const show = container.querySelector('.cornell-governance-writable-field');

        this.showHide(show, hide);

        return false;
    }

    notesEditorHide(target) {
        const container = target.closest('.cornell-governance-notes-container');
        const show = container.querySelector('.cornell-governance-viewable-field');
        const hide = container.querySelector('.cornell-governance-writable-field');

        this.showHide(show, hide);

        return false;
    }

    showHide(show, hide) {
        show.style.display = 'block';
        hide.style.display = 'none';
        return false;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new CornellGovernanceAdmin();
});