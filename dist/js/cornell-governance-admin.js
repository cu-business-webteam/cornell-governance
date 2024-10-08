/******/ (() => { // webpackBootstrap
/******/ 	"use strict";

;// ./src/js/modules/slist.js
/**
 * Adapted from CodeBoxx sample
 * @see https://code-boxx.com/drag-drop-sortable-list-javascript/#:~:text=In%20the%20simplest%20design%2C%20drag-and-drop%20in%20HTML%20and,%28%22drop%22%29.ondrop%20%3D%20%28%29%20%3D%3E%20%7B%20DO%20SOMETHING%20%7D%3B
 */
class sortableList {
  /**
   * Instantiate our slist
   * @param el the element that contains the sortable list
   */
  constructor(el) {
    this.container = el;
    this.slist();
  }

  /**
   * Build the sortable list
   * @param target the element that contains the sortable list
   */
  slist() {
    const target = this.container;

    // (A) SET CSS + GET ALL LIST ITEMS
    target.classList.add("slist");
    let items = target.getElementsByTagName("li"),
      current = null;

    // (B) MAKE ITEMS DRAGGABLE + SORTABLE
    for (let i of items) {
      // (B1) ATTACH DRAGGABLE
      i.draggable = true;

      // (B2) DRAG START - YELLOW HIGHLIGHT DROPZONES
      i.ondragstart = e => {
        current = i;
        for (let it of items) {
          if (it != current) {
            it.classList.add("hint");
          }
        }
      };

      // (B3) DRAG ENTER - RED HIGHLIGHT DROPZONE
      i.ondragenter = e => {
        if (i != current) {
          i.classList.add("active");
        }
      };

      // (B4) DRAG LEAVE - REMOVE RED HIGHLIGHT
      i.ondragleave = () => i.classList.remove("active");

      // (B5) DRAG END - REMOVE ALL HIGHLIGHTS
      i.ondragend = () => {
        for (let it of items) {
          it.classList.remove("hint");
          it.classList.remove("active");
        }
      };

      // (B6) DRAG OVER - PREVENT THE DEFAULT "DROP", SO WE CAN DO OUR OWN
      i.ondragover = e => e.preventDefault();

      // (B7) ON DROP - DO SOMETHING
      i.ondrop = e => {
        e.preventDefault();
        if (i != current) {
          let currentpos = 0,
            droppedpos = 0;
          for (let it = 0; it < items.length; it++) {
            if (current == items[it]) {
              currentpos = it;
            }
            if (i == items[it]) {
              droppedpos = it;
            }
          }
          if (currentpos < droppedpos) {
            i.parentNode.insertBefore(current, i.nextSibling);
          } else {
            i.parentNode.insertBefore(current, i);
          }
        }
      };
    }
  }
}
/* harmony default export */ const slist = (sortableList);
;// ./src/js/modules/repeater.js

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
      new slist(repeater);
    }
  }
  addButtons() {
    this.repeaters.forEach(repeater => {
      this.instantiateSList(repeater);
      const fields = repeater.querySelectorAll('li.repeater-field');
      fields.forEach(field => {
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
    let newIndex = counter + 1;
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
    repeater.querySelectorAll('button.repeater-remove-row').forEach(b => {
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
    rows.forEach(row => {
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
    repeater.querySelectorAll('button.repeater-remove-row').forEach(b => {
      b.addEventListener('click', this.removeRowClick);
    });
  }
}
/* harmony default export */ const repeater = (CBRepeater);
;// ./src/js/modules/updateNotice.js
class updateNotice {
  constructor() {}
  setOuterContainer(el) {
    this.outerContainer = el;
  }
  createNotice() {
    this.noticeContainer = document.createElement('div');
    this.noticeContainer.classList.add('cornell-governance-notice', 'updated');
    const noticeText = document.createElement('p');
    noticeText.innerText = this.message;
    this.noticeContainer.appendChild(noticeText);
  }
  insertNotice(message) {
    this.message = message;
    this.createNotice();
    if (this.outerContainer) {
      this.outerContainer.appendChild(this.noticeContainer);
    } else {
      document.querySelector('body').appendChild(this.noticeContainer);
    }
    // We also need to update the CSS timing
    this.t = setTimeout(this.removeNotice.bind(this), 7000);
  }
  removeNotice() {
    document.querySelector('.cornell-governance-notice').remove();
  }
}
/* harmony default export */ const modules_updateNotice = (updateNotice);
;// ./src/js/modules/imageLightbox.js
class imageLightbox {
  /**
   * Construct our lightbox module
   *
   * @param {} args an object containing the following settings:
   *      * selectors:
   *          * parent - a CSS selector pointing to the gallery container elements
   *          * links - a CSS selector pointing to the link elements inside the galleries
   *          * images - a CSS selector pointing to the images found within the galleries
   *          * captions - a CSS selector pointing to the captions found within the galleries
   *      * container - string - a CSS class to assign to the lightbox container, itself
   *      * gallery - bool (true) - whether to allow sliding between images within the lightbox
   *      * caption - bool (true) - whether to include the image caption in the lightbox
   */
  constructor(args) {
    this.args = args;
    this.registerSettings();
    this.galleries = document.querySelectorAll(this.selectors.parent);
    if (this.galleries.length <= 0) {
      return;
    }
    this.initLightbox = this.init.bind(this);
    this.addTrigger = this.addTrigger.bind(this);
    this.openLightbox = this.openLightbox.bind(this);
    this.closeLightbox = this.closeLightbox.bind(this);
    this.galleries.forEach(gallery => {
      this.initLightbox(gallery);
    });
  }
  registerSettings() {
    if (this.args.hasOwnProperty('selectors')) {
      this.selectors = this.args.selectors;
      if (!this.selectors.hasOwnProperty('parent')) {
        this.selectors.parent = '.gallery';
      }
      if (!this.selectors.hasOwnProperty('images')) {
        this.selectors.images = 'img';
      }
      if (!this.selectors.hasOwnProperty('links')) {
        this.selectors.links = 'a';
      }
      if (!this.selectors.hasOwnProperty('captions')) {
        this.selectors.captions = 'figcaption';
      }
    }
    if (this.args.hasOwnProperty('container')) {
      this.containerClass = this.args.container;
    } else {
      this.containerClass = 'lightbox-container';
    }
    if (this.args.hasOwnProperty('gallery')) {
      this.useGallery = this.args.gallery;
    } else {
      this.useGallery = true;
    }
    if (this.args.hasOwnProperty('caption')) {
      this.useCaptions = this.args.caption;
    } else {
      this.useCaptions = true;
    }
  }
  init(gallery) {
    const images = gallery.querySelectorAll(this.selectors.images);
    const links = gallery.querySelectorAll(this.selectors.links);
    console.log(links);
    const captions = gallery.querySelectorAll(this.selectors.captions);
    links.forEach(link => {
      this.addTrigger(link);
    });
    this.createLightbox();
    window.addEventListener('keydown', this.escLightbox.bind(this));
  }
  addTrigger(link) {
    link.setAttribute('aria-label', 'Open the full-sized image in a lightbox');
    link.addEventListener('click', this.openLightbox);
  }
  openLightbox(e) {
    e.preventDefault();
    let link = e.target;
    if (e.target.tagName.toLowerCase() === this.selectors.images || e.target.tagName.toLowerCase() === this.selectors.captions) {
      link = e.target.closest(this.selectors.links);
    }
    const caption = link.querySelector(this.selectors.captions);
    console.log(link);
    console.log(caption);
    this.opener = link;
    this.lightboxImage.setAttribute('src', link.getAttribute('href'));
    this.lightboxCaption.innerHTML = caption.innerHTML;
    this.lightbox.classList.remove('hidden');
    this.lightbox.classList.add('open');
    this.lightbox.setAttribute('aria-hidden', 'false');
    this.trapFocus(this.lightbox);
    return false;
  }
  closeLightbox(e) {
    this.lightbox.classList.add('hidden');
    this.lightbox.classList.add('open');
    this.lightbox.setAttribute('aria-hidden', 'true');
    this.opener.focus();
  }
  escLightbox(e) {
    if (this.lightbox.classList.contains('hidden')) {
      return;
    }
    switch (e.key) {
      case 'Esc':
      case 'Escape':
        this.lightboxClose.click();
        break;
      default:
        return;
    }
  }
  createLightbox() {
    if (document.querySelectorAll(this.containerClass).length >= 1) {
      return;
    }
    this.lightbox = document.createElement('div');
    this.lightbox.classList.add(this.containerClass);
    this.lightbox.classList.add('cornell-governance-lightbox');
    this.lightbox.classList.add('hidden');
    this.lightbox.setAttribute('role', 'dialog');
    this.lightbox.setAttribute('aria-describedby', 'lightbox-caption');
    this.lightbox.setAttribute('aria-hidden', 'true');
    this.lightboxClose = document.createElement('button');
    this.lightboxClose.innerText = 'Close';
    this.lightboxClose.addEventListener('click', this.closeLightbox);
    this.lightboxInner = document.createElement('div');
    this.lightboxInner.classList.add('lightbox');
    this.lightboxFigure = document.createElement('figure');
    this.lightboxImage = document.createElement('img');
    this.lightboxCaption = document.createElement('figcaption');
    this.lightboxCaption.id = 'lightbox-caption';
    if (!this.useCaptions) {
      this.lightboxCaption.classList.add('screen-reader-text');
    }
    this.lightboxFigure.append(this.lightboxImage);
    this.lightboxFigure.append(this.lightboxCaption);
    this.lightboxInner.append(this.lightboxClose);
    this.lightboxInner.append(this.lightboxFigure);
    this.lightbox.append(this.lightboxInner);
    document.querySelector('body').append(this.lightbox);
  }
  trapFocus(element) {
    var focusableEls = element.querySelectorAll('a[href]:not([disabled]), button:not([disabled]), textarea:not([disabled]), input[type="text"]:not([disabled]), input[type="radio"]:not([disabled]), input[type="checkbox"]:not([disabled]), select:not([disabled])');
    var firstFocusableEl = focusableEls[0];
    var lastFocusableEl = focusableEls[focusableEls.length - 1];
    var KEYCODE_TAB = 9;
    element.addEventListener('keydown', function (e) {
      var isTabPressed = e.key === 'Tab' || e.keyCode === KEYCODE_TAB;
      if (!isTabPressed) {
        return;
      }
      if (e.shiftKey) /* shift + tab */{
          if (document.activeElement === firstFocusableEl) {
            lastFocusableEl.focus();
            e.preventDefault();
          }
        } else /* tab */{
          if (document.activeElement === lastFocusableEl) {
            firstFocusableEl.focus();
            e.preventDefault();
          }
        }
    });
  }
}
/* harmony default export */ const modules_imageLightbox = (imageLightbox);
;// ./src/js/cornell-governance/documentation.js

class governanceDocs {
  constructor() {
    this.gallery = new modules_imageLightbox({
      selectors: {
        parent: 'ol:has(li > a > img + em)',
        captions: 'em'
      }
    });
  }
}
/* harmony default export */ const documentation = (governanceDocs);
;// ./src/js/modules/tooltip.js
class governanceTooltip {
  /**
   * Instantiate our tooltip element
   * @param el the element that contains the tooltip
   */
  constructor(el) {
    this.container = el;
    this.opener = el.querySelector('[aria-describedby]');
    this.content = el.querySelector('[role="tooltip"]');
    this.closer = this.content.querySelector('button[rel="' + this.content.getAttribute('id') + '"]');
    this.closeTooltip = this.closeTooltip.bind(this);
    this.toggleTooltip = this.toggleTooltip.bind(this);
    this.escTooltip = this.escTooltip.bind(this);
    this.focusTrapEvent = this.focusTrapEvent.bind(this);
    this.init();
  }
  init() {
    this.opener.addEventListener('click', this.toggleTooltip);
    this.closer.addEventListener('click', this.closeTooltip);
  }
  toggleTooltip(e) {
    e.preventDefault();
    if (this.content.classList.contains('open')) {
      this.closeTooltip();
    } else {
      this.openTooltip();
    }
    return false;
  }
  openTooltip() {
    this.trapFocus(this.content);
    this.content.classList.add('open');
    window.addEventListener('keydown', this.escTooltip);
  }
  closeTooltip(e) {
    e.preventDefault();
    this.untrapFocus(this.content);
    window.removeEventListener('keydown', this.escTooltip);
    this.content.classList.remove('open');
    this.opener.focus();
    return false;
  }
  escTooltip(e) {
    if (!this.content.classList.contains('open')) {
      return;
    }
    switch (e.key) {
      case 'Esc':
      case 'Escape':
        this.closer.click();
        break;
      default:
        return;
    }
  }
  focusTrapEvent(e, options) {
    const [focusableEls, firstFocusableEl, lastFocusableEl, KEYCODE_TAB] = options;
    var isTabPressed = e.key === 'Tab' || e.keyCode === KEYCODE_TAB;
    if (!isTabPressed) {
      return;
    }
    if (e.shiftKey) /* shift + tab */{
        if (document.activeElement === firstFocusableEl) {
          lastFocusableEl.focus();
          e.preventDefault();
        }
      } else /* tab */{
        if (document.activeElement === lastFocusableEl) {
          firstFocusableEl.focus();
          e.preventDefault();
        }
      }
  }
  trapFocus(element) {
    var focusableEls = element.querySelectorAll('a[href]:not([disabled]), button:not([disabled]), textarea:not([disabled]), input[type="text"]:not([disabled]), input[type="radio"]:not([disabled]), input[type="checkbox"]:not([disabled]), select:not([disabled])');
    var firstFocusableEl = focusableEls[0];
    var lastFocusableEl = focusableEls[focusableEls.length - 1];
    var KEYCODE_TAB = 9;
    firstFocusableEl.focus();
    element.addEventListener('keydown', e => {
      this.focusTrapEvent(e, [focusableEls, firstFocusableEl, lastFocusableEl, KEYCODE_TAB]);
    });
  }
  untrapFocus(element) {
    var focusableEls = element.querySelectorAll('a[href]:not([disabled]), button:not([disabled]), textarea:not([disabled]), input[type="text"]:not([disabled]), input[type="radio"]:not([disabled]), input[type="checkbox"]:not([disabled]), select:not([disabled])');
    var firstFocusableEl = focusableEls[0];
    var lastFocusableEl = focusableEls[focusableEls.length - 1];
    var KEYCODE_TAB = 9;
    element.removeEventListener('keydown', e => {
      this.focusTrapEvent(e, [focusableEls, firstFocusableEl, lastFocusableEl, KEYCODE_TAB]);
    });
  }
}
/* harmony default export */ const modules_tooltip = (governanceTooltip);
;// ./src/js/cornell-governance-admin.js




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
      document.querySelector('.cornell-governance-save-info button:not(.governance-tooltip-opener)').addEventListener('click', e => {
        this.saveInfoClick(e);
      });
    }
    if (document.querySelectorAll('.cornell-governance-save-notes').length >= 1) {
      document.querySelector('.cornell-governance-save-notes button:not(.governance-tooltip-opener)').addEventListener('click', e => {
        this.saveNotesClick(e);
      });
    }
    const inputs = document.querySelectorAll('.postbox:not(#cornell-governance-page-revisions) .cornell-governance-metabox input, .postbox:not(#cornell-governance-page-revisions) .cornell-governance-metabox select, .postbox:not(#cornell-governance-page-revisions) .cornell-governance-metabox textarea');
    inputs.forEach(input => {
      input.addEventListener('change', this.inputChanged);
    });
    if (document.querySelectorAll('ol.repeater-field-set').length >= 1) {
      this.repeater = new repeater();
      const repeaterButtons = document.querySelectorAll('.postbox:not(#cornell-governance-page-revisions) .cornell-governance-metabox button.repeater-remove-row, .postbox:not(#cornell-governance-page-revisions) .cornell-governance-metabox button.repeater-add-row');
      repeaterButtons.forEach(input => {
        input.addEventListener('click', this.inputChanged);
      });
    } else if (document.querySelectorAll('.cornell-governance-page-info-tasks input[type="checkbox"]').length >= 1) {
      document.querySelectorAll('.cornell-governance-page-info-tasks input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', this.saveToDo);
      });
    }
    this.tooltips = [];
    const tooltips = document.querySelectorAll('.governance-tooltip-container');
    tooltips.forEach(tooltip => {
      this.tooltips.push(new modules_tooltip(tooltip));
    });
    this.updateNotice = new modules_updateNotice();
  }
  setupLightboxes() {
    new documentation();
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
    this.saveInfo(e, {
      'save-action': 'completed-tasks'
    });
  }
  abandonChanges(e) {
    document.querySelector('#cornell-governance-page-info').scrollIntoView();
    e.preventDefault();
    return e.returnValue = 'Are you sure you want to leave without saving changes to the Governace Information?';
  }
  prepareSave(target, atts) {
    const form = target.querySelector(".cornell-governance-metabox");
    let formData = new FormData();
    formData.set('action', atts.ajax_action);
    if (atts.hasOwnProperty('save-action')) {
      formData.set('save-action', atts['save-action']);
    }
    const fields = form.querySelectorAll("input, textarea, select");
    fields.forEach(field => {
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
    fetch(request).then(response => {
      if (!response.ok) {
        throw response;
      }
      return response.json();
    }).then(text => {
      this.log(text);
      this.finishedSave(target);
    }).catch(error => {
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
      'ajax_url': new URL('/wp-admin/admin-ajax.php', 'http://cornell-governance.local')
    };
    if (typeof CornellGovernanceAdminAJAX !== "undefined" && 'info' in CornellGovernanceAdminAJAX) {
      atts = CornellGovernanceAdminAJAX.info;
    }
    if (Object.keys(attributes).length >= 1) {
      atts = {
        ...atts,
        ...attributes
      };
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
    error.json().then(body => {
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
      'ajax_url': new URL('/wp-admin/admin-ajax.php', 'http://cornell-governance.local')
    };
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
      'seconds': '00' + seconds
    };
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
/******/ })()
;