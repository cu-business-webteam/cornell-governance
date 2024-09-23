import CBRepeater from "./modules/repeater";
import updateNotice from "./modules/updateNotice";
import governanceDocs from "./cornell-governance/documentation";
import governanceTooltip from "./modules/tooltip";

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
        this.finishedSave = this.savedMeta.bind(this);
        this.saveError = this.errorOnSave.bind(this);
        this.updateTimestamp = this.setTimestamp.bind(this);

        this.somethingChanged = false;
        this.inputChanged = this.inputChange.bind(this);
        this.confirmLeave = this.abandonChanges.bind(this);
        this.saveToDo = this.saveTaskCheck.bind(this);

        if (document.querySelectorAll('.cornell-governance-save-info').length >= 1) {
            document.querySelector('.cornell-governance-save-info button:not(.governance-tooltip-opener)').addEventListener('click', (e) => {
                this.saveInfoClick(e);
            });
        }
        if (document.querySelectorAll('.cornell-governance-save-notes').length >= 1) {
            document.querySelector('.cornell-governance-save-notes button:not(.governance-tooltip-opener)').addEventListener('click', (e) => {
                this.saveNotesClick(e);
            });
        }

        const inputs = document.querySelectorAll('.postbox:not(#cornell-governance-page-revisions) .cornell-governance-metabox input, .postbox:not(#cornell-governance-page-revisions) .cornell-governance-metabox select, .postbox:not(#cornell-governance-page-revisions) .cornell-governance-metabox textarea');
        inputs.forEach((input) => {
            input.addEventListener('change', this.inputChanged);
        });

        if (document.querySelectorAll('ol.repeater-field-set').length >= 1) {
            this.repeater = new CBRepeater();

            const repeaterButtons = document.querySelectorAll('.postbox:not(#cornell-governance-page-revisions) .cornell-governance-metabox button.repeater-remove-row, .postbox:not(#cornell-governance-page-revisions) .cornell-governance-metabox button.repeater-add-row');
            repeaterButtons.forEach((input) => {
                input.addEventListener('click', this.inputChanged);
            });
        } else if (document.querySelectorAll('.cornell-governance-page-info-tasks input[type="checkbox"]').length >= 1) {
            document.querySelectorAll('.cornell-governance-page-info-tasks input[type="checkbox"]').forEach((checkbox) => {
                checkbox.addEventListener('change', this.saveToDo);
            });
        }

        this.tooltips = [];
        const tooltips = document.querySelectorAll('.governance-tooltip-container');
        tooltips.forEach((tooltip) => {
            this.tooltips.push(new governanceTooltip(tooltip));
        });

        this.updateNotice = new updateNotice();
    }

    setupLightboxes() {
        new governanceDocs();
    }

    inputChange(e) {
        this.somethingChanged = true;
        window.addEventListener('beforeunload', this.confirmLeave);
        this.showSaveInfoInstructions();
    }

    saveTaskCheck(e) {
        let checkbox = e.target;
        if (checkbox.checked) {
            checkbox.closest('label').classList.add('done');
        } else {
            checkbox.closest('label').classList.remove('done');
        }

        this.saveInfo(e, {'save-action': 'completed-tasks'});
    }

    abandonChanges(e) {
        document.querySelector('#cornell-governance-page-info').scrollIntoView();

        e.preventDefault();

        return (e.returnValue = 'Are you sure you want to leave without saving changes to the Governace Information?');
    }

    prepareSave(target, atts) {
        const form = target.querySelector(".cornell-governance-metabox");
        let formData = new FormData();
        formData.set('action', atts.ajax_action);

        if (atts.hasOwnProperty('save-action')) {
            formData.set('save-action', atts['save-action']);
        }

        const fields = form.querySelectorAll("input, textarea, select");
        fields.forEach((field) => {
            if (null === field.getAttribute('name')) {
                return;
            }
            if ('radio' === field.getAttribute('type') || 'checkbox' === field.getAttribute('type')) {
                if (!field.checked) {
                    return;
                }
            }
            this.log('We are going to append ' + field.value + ' as the value of ' + field.getAttribute('name'));
            formData.append(field.getAttribute("name"), field.value);
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
                this.log(text);
                this.finishedSave(target)
            })
            .catch((error) => {
                this.log(error);
                this.saveError(error);
            });
    }

    saveInfo(e, attributes) {
        e.preventDefault();

        attributes = attributes || {};

        let container = e.target.closest('.postbox');
        let head = container.querySelector('.hndle');
        this.currentBox = container.querySelector('.cornell-governance-metabox');
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

        this.prepareSave(document.getElementById('cornell-governance-page-info'), atts);

        this.hideSaveInfoInstructions();
    }

    savedMeta(target) {
        this.updateTimestamp(target);
        window.removeEventListener('beforeunload', this.confirmLeave);
        this.log('The request appears to have finished loading');
        this.t = setTimeout(this.removeOverlay.bind(this), 2000, target);
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
        this.currentBoxText = head.innerText;
        this.timestampField = this.currentBox.querySelector('.cornell-governance-timestamp');
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
        this.log('Creating the progress overlay div');
        const metabox = target.querySelector('.inside .cornell-governance-metabox');
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
        let overlay = target.querySelector('.inside .cornell-governance-metabox .progress-overlay');
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
        if (document.querySelectorAll('.cornell-governance-save-info-instructions').length <= 0) {
            return;
        }

        document.querySelector('.cornell-governance-save-info-instructions').style.display = 'block';
    }

    hideSaveInfoInstructions() {
        if (document.querySelectorAll('.cornell-governance-save-info-instructions').length <= 0) {
            return;
        }

        document.querySelector('.cornell-governance-save-info-instructions').style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new CornellGovernanceAdmin();
});