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
;// ./src/js/modules/tabs.js
class governanceTabs {
  constructor(el) {
    this.container = el;
    this.tabs = this.container.querySelectorAll(':scope > [role="tab"]');
    this.tabFocus = 0;
    this.changeTabs = this.toggleTab.bind(this);
    this.hashChange = this.changedHash.bind(this);
    this.init();
  }
  init() {
    window.addEventListener('hashchange', this.hashChange);
    window.addEventListener('load', this.hashChange);
    this.tabs.forEach(tab => {
      tab.addEventListener("click", this.changeTabs);
    });
    this.container.addEventListener("keydown", e => {
      // Move right
      if (e.key === "ArrowRight" || e.key === "ArrowLeft") {
        this.tabs[this.tabFocus].setAttribute("tabindex", -1);
        if (e.key === "ArrowRight") {
          this.tabFocus++;
          // If we're at the end, go to the start
          if (this.tabFocus >= this.tabs.length) {
            this.tabFocus = 0;
          }
          // Move left
        } else if (e.key === "ArrowLeft") {
          this.tabFocus--;
          // If we're at the start, move to the end
          if (this.tabFocus < 0) {
            this.tabFocus = this.tabs.length - 1;
          }
        }
        this.tabs[this.tabFocus].setAttribute("tabindex", 0);
        this.tabs[this.tabFocus].focus();
      }
    });
  }
  toggleTab(e) {
    e.preventDefault();
    const targetTab = e.target;
    const tabList = targetTab.parentNode;
    const tabGroup = tabList.parentNode;
    const activePanel = tabGroup.querySelector(`#${targetTab.getAttribute("aria-controls")}`);
    if (location.hash !== '#' + activePanel.id) {
      history.pushState({}, "", '#' + activePanel.id);
    }

    // Remove all current selected tabs
    tabList.querySelectorAll(':scope > [aria-selected="true"]').forEach(t => t.setAttribute("aria-selected", false));

    // Set this tab as selected
    targetTab.setAttribute("aria-selected", true);

    // Hide all tab panels
    tabGroup.querySelectorAll(':scope > [role="tabpanel"]').forEach(p => p.setAttribute("hidden", true));

    // Show the selected panel
    activePanel.removeAttribute("hidden");
    let targetScroll = targetTab;
    let metabox = targetTab.closest('.postbox.cornell-governance-metabox, .cornell-governance-tablist');
    if (metabox !== null) {
      targetScroll = metabox;
    }
    targetScroll.scrollIntoView({
      behavior: 'smooth'
    });
    return false;
  }
  changedHash(e) {
    e.preventDefault();
    const searchString = '#tab-';
    if (searchString === location.hash.substring(0, searchString.length)) {
      const newTab = location.hash.replace('#', '');
      const newTabEl = document.querySelector('#' + newTab);
      /*const activeTab = location.hash.replace( 'panel', 'tab' );
      const activeTabEl = document.querySelector( '#' + activeTab );
      activeTabEl.scrollIntoView({ behavior: 'smooth' });*/
      newTabEl.click();
    }
  }
  stopScroll(e) {
    e.preventDefault();
  }
}
/* harmony default export */ const tabs = (governanceTabs);
;// ./src/js/modules/HTMLParser.js
class HTMLParser {
  constructor(html) {
    this.html = html;
    this.parser = new DOMParser();
  }
  parseHTML() {
    return this.parser.parseFromString(this.html, 'text/html');
  }
}
/* harmony default export */ const modules_HTMLParser = (HTMLParser);
;// ./src/js/modules/tabs-manual.js
/*
 *   This content is licensed according to the W3C Software License at
 *   https://www.w3.org/Consortium/Legal/2015/copyright-software-and-document
 *
 *   File:   tabs-manual.js
 *
 *   Desc:   Tablist widget that implements ARIA Authoring Practices
 */



class TabsManual {
  constructor(groupNode) {
    this.tablistNode = groupNode;
    this.tabs = [];
    this.firstTab = null;
    this.lastTab = null;
    this.tabs = Array.from(this.tablistNode.querySelectorAll('[role=tab]'));
    this.tabpanels = [];
    for (var i = 0; i < this.tabs.length; i += 1) {
      var tab = this.tabs[i];
      var tabpanel = document.getElementById(tab.getAttribute('aria-controls'));
      tab.tabIndex = -1;
      tab.setAttribute('aria-selected', 'false');
      this.tabpanels.push(tabpanel);
      tab.addEventListener('keydown', this.onKeydown.bind(this));
      tab.addEventListener('click', this.onClick.bind(this));
      if (!this.firstTab) {
        this.firstTab = tab;
      }
      this.lastTab = tab;
    }
    this.setSelectedTab(this.firstTab);
  }
  setSelectedTab(currentTab) {
    for (var i = 0; i < this.tabs.length; i += 1) {
      var tab = this.tabs[i];
      if (currentTab === tab) {
        tab.setAttribute('aria-selected', 'true');
        tab.removeAttribute('tabindex');
        this.tabpanels[i].classList.remove('is-hidden');
        console.log(tab);
      } else {
        tab.setAttribute('aria-selected', 'false');
        tab.tabIndex = -1;
        this.tabpanels[i].classList.add('is-hidden');
      }
    }
  }
  moveFocusToTab(currentTab) {
    currentTab.focus();
  }
  moveFocusToPreviousTab(currentTab) {
    var index;
    if (currentTab === this.firstTab) {
      this.moveFocusToTab(this.lastTab);
    } else {
      index = this.tabs.indexOf(currentTab);
      this.moveFocusToTab(this.tabs[index - 1]);
    }
  }
  moveFocusToNextTab(currentTab) {
    var index;
    if (currentTab === this.lastTab) {
      this.moveFocusToTab(this.firstTab);
    } else {
      index = this.tabs.indexOf(currentTab);
      this.moveFocusToTab(this.tabs[index + 1]);
    }
  }

  /* EVENT HANDLERS */

  onKeydown(event) {
    var tgt = event.currentTarget,
      flag = false;
    switch (event.key) {
      case 'ArrowLeft':
        this.moveFocusToPreviousTab(tgt);
        flag = true;
        break;
      case 'ArrowRight':
        this.moveFocusToNextTab(tgt);
        flag = true;
        break;
      case 'Home':
        this.moveFocusToTab(this.firstTab);
        flag = true;
        break;
      case 'End':
        this.moveFocusToTab(this.lastTab);
        flag = true;
        break;
      default:
        break;
    }
    if (flag) {
      event.stopPropagation();
      event.preventDefault();
    }
  }

  // Since this example uses buttons for the tabs, the click onr also is activated
  // with the space and enter keys
  onClick(event) {
    event.preventDefault();
    this.setSelectedTab(event.currentTarget);
    return false;
  }
}
/* harmony default export */ const tabs_manual = (TabsManual);
;// ./src/js/modules/archiveList.js

class archiveList {
  constructor(handle, counter) {
    this.idBase = `archivelist-${counter}-`;
    console.group('archiveList class');
    console.log('Preparing a new archiveList object');
    this.container = null;
    this.list = handle;
    this.listItems = null;
    this.lists = [];
    this.paginate = this.insertPageNumbers.bind(this);
    this.separate = this.buildSeparateLists.bind(this);
    this.buildList = this.createList.bind(this);
    this.perPage = 20;
    this.currentPage = 1;
    this.totalPages = 1;
    this.totalItems = 1;
    this.currentIndex = 0;
    this.init();
    console.groupEnd();
  }
  init() {
    this.listOfClasses = this.list.classList;
    this.listItems = this.list.querySelectorAll('li');
    this.totalItems = this.listItems.length;
    this.totalPages = this.totalItems / this.perPage;
    if (this.totalPages <= 1) {
      return;
    }
    const newContainer = document.createElement('div');
    newContainer.classList.add('governance-paginated-list');
    this.container = newContainer;
    this.separate();
    this.tabList = new tabs_manual(this.container);
    console.log('Completed init method');
  }
  insertPageNumbers() {
    const pageList = document.createElement('nav');
    pageList.classList.add('governance-pagination');
    pageList.setAttribute('role', 'tablist');
    pageList.classList.add('manual');
    pageList.setAttribute('aria-label', 'Pagination of Archive Snapshot List');
    const paginationTitle = document.createElement('h4');
    paginationTitle.innerText = 'Pages: ';
    pageList.appendChild(paginationTitle);
    const pageListUL = document.createElement('ul');

    // Insert the "Previous Page" pagination link
    //pageListUL.appendChild(this.makePageLink('Go to previous page', 'Previous', this.currentPage - 1, ['previous']));

    for (let i = 1; i <= this.totalPages; i++) {
      pageListUL.appendChild(this.makePageLink(`Go to Page ${i}`, i, i));
    }

    // Insert the "Next Page" pagination link
    //pageListUL.appendChild(this.makePageLink('Go to next page', 'Next', this.currentPage + 1, ['next']));

    pageList.appendChild(pageListUL);
    this.container.appendChild(pageList);
  }

  /**
   * Create a list item with a pagination button inside
   *
   * @param label string - the label text to use as the aria-label attribute
   * @param text string - the text to use inside the button
   * @param navTo int - the page to which this button should navigate
   * @param classes array - a list of classes to add to the button li
   */
  makePageLink(label, text, navTo, classes) {
    if (typeof classes === "undefined") {
      classes = [];
    }
    const pageItem = document.createElement('li');
    const pageLink = document.createElement('button');
    pageLink.setAttribute('role', 'tab');
    if (navTo === this.currentPage) {
      classes.push('current');
      pageLink.setAttribute('aria-selected', 'true');
    }
    if (classes.indexOf('previous') >= 0) {
      pageLink.setAttribute('id', `${this.idBase}tab-previous`);
    } else if (classes.indexOf('next') >= 0) {
      pageLink.setAttribute('id', `${this.idBase}tab-next`);
    } else {
      pageLink.setAttribute('id', `${this.idBase}tab-${navTo}`);
    }
    pageLink.setAttribute('aria-controls', `${this.idBase}tabpanel-${navTo}`);
    pageLink.setAttribute('aria-label', label);
    pageLink.innerText = text;
    pageLink.classList.add('pagination-link');
    if (classes.length > 0) {
      pageItem.classList.add(classes);
    }
    pageLink.setAttribute('data-page-target', navTo);
    pageItem.appendChild(pageLink);
    return pageItem;
  }
  buildSeparateLists() {
    let separateLists = [];
    for (let i = 0; i <= this.totalPages; i++) {
      let currentIndex = i * this.perPage;
      let end = (i + 1) * this.perPage - 1;
      console.log(`Creating a list that should have items ${currentIndex} through ${end}`);
      separateLists[i] = Array.from(this.listItems).slice(currentIndex, end);
    }
    let page = 1;
    separateLists.forEach(items => {
      this.buildList(items, page);
      page++;
    });
    this.paginate();
    const parentContainer = this.list.parentNode;
    console.log('Preparing to replace the original list with paginated lists');
    parentContainer.replaceChild(this.container, this.list);
  }

  /**
   * Create a list of items
   *
   * @param items array - the list of items to add to the list
   * @param page int - the page number associated with this list
   */
  createList(items, page) {
    const newList = document.createElement('ul');
    newList.setAttribute('id', `${this.idBase}tabpanel-${page}`);
    newList.setAttribute('role', 'tabpanel');
    newList.setAttribute('aria-labeled-by', `${this.idBase}tab-${page}`);
    newList.classList.add('paginated-list');
    this.listOfClasses.forEach(c => {
      newList.classList.add(c);
    });
    items.forEach(item => {
      const newItem = document.createElement('li');
      newItem.innerHTML = item.innerHTML;
      newList.appendChild(newItem);
    });
    newList.classList.add('is-hidden');
    this.container.appendChild(newList);
  }
}
/* harmony default export */ const modules_archiveList = (archiveList);
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
    // Keep a list of the query selectors used to determine whether we should use any of these scripts or not
    const activeSelectors = ['.cornell-governance-save-info', '.cornell-governance-save-notes', 'ol.repeater-field-set', '.cornell-governance-tablist'];

    // If none of these selectors exist on the page, we abandon processing this javascript
    if (document.querySelectorAll(activeSelectors.join(',')).length <= 0) {
      return;
    }
    this.waybackAPIBase = 'https://web.archive.org/cdx/search/cdx/';
    this.handleArchiveLists();
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
      buttons.forEach(button => {
        button.addEventListener('click', e => {
          this.saveInfoClick(e);
        });
      });
    }
    if (document.querySelectorAll('.cornell-governance-save-notes').length >= 1) {
      document.querySelector('.cornell-governance-save-notes button:not(.governance-tooltip-opener, .is-secondary)').addEventListener('click', e => {
        this.saveNotesClick(e);
      });
    }
    if (document.querySelectorAll('.cornell-governance-deletion-submit').length >= 1) {
      document.querySelector('.cornell-governance-deletion-submit button:not(.governance-tooltip-opener)').addEventListener('click', e => {
        this.saveDeleteClick(e);
      });
    }
    const inputs = document.querySelectorAll('.postbox#cornell-governance-page-info .cornell-governance-tabpanel > :not(#cornell-governance-page-notes) :is(input, select, textarea)');
    if (inputs.length >= 1) {
      inputs.forEach(input => {
        this.log(input);
        input.addEventListener('change', this.inputChanged);
      });
    }
    if (document.querySelectorAll('ol.repeater-field-set').length >= 1) {
      this.repeater = new repeater();
      const repeaterButtons = document.querySelectorAll('.postbox#cornell-governance-page-info .cornell-governance-tabpanel > :not(#cornell-governance-page-notes) :is(button.repeater-remove-row, button.repeater-add-row)');
      repeaterButtons.forEach(input => {
        this.log(input);
        input.addEventListener('click', this.inputChanged);
      });
    }
    this.maybeHideConfirm();
    this.tooltips = [];
    const tooltips = document.querySelectorAll('.governance-tooltip-container');
    if (tooltips.length >= 1) {
      tooltips.forEach(tooltip => {
        this.tooltips.push(new modules_tooltip(tooltip));
      });
    }
    this.tabLists = [];
    const tabLists = document.querySelectorAll('.cornell-governance-metabox .inside :not(.governance-paginated-list) [role="tablist"], .cornell-governance-tablist [role="tablist"]');
    if (tabLists.length >= 1) {
      tabLists.forEach(tabList => {
        this.tabLists.push(new tabs(tabList));
      });
    }
    this.updateNotice = new modules_updateNotice();
    if (document.querySelectorAll('.cornell-governance-reveal-toggle-notes-editor button').length >= 1) {
      const notesEditors = document.querySelectorAll('.cornell-governance-reveal-toggle-notes-editor button');
      notesEditors.forEach(editor => {
        editor.addEventListener('click', this.revealNotesEditor);
        this.hideNotesEditor(editor);
      });
    }
  }
  handleArchiveLists() {
    if (document.querySelectorAll('.cornell-governance-wayback-list').length <= 0) {
      return;
    }
    this.waybackLists = [];
    let listCounter = 1;
    const archiveLists = document.querySelectorAll('.cornell-governance-wayback-list');
    archiveLists.forEach(list => {
      this.waybackLists.push(new modules_archiveList(list, listCounter));
      listCounter++;
    });
  }
  setupLightboxes() {
    new documentation();
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
    this.saveInfo(e, {
      'save-action': 'completed-tasks'
    });
    this.toggleConfirm(checkbox);
  }
  abandonChanges(e) {
    document.querySelector('#cornell-governance-page-info').scrollIntoView();
    e.preventDefault();
    return e.returnValue = 'Are you sure you want to leave without saving changes to the Governance Information?';
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
    fields.forEach(field => {
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
    fetch(request).then(response => {
      if (!response.ok) {
        throw response;
      }
      return response.json();
    }).then(text => {
      let json = text.data;
      this.log(text);
      this.finishedSave(target);
      return json;
    }).then(json => {
      this.log(json);
      this.updateDataAfterSave(target, json);
    }).catch(error => {
      this.log(error);
      this.saveError(error);
    });
  }
  saveInfo(e, attributes) {
    e.preventDefault();
    attributes = attributes || {};
    this.log('The selected input appears to be: ' + e.target);
    let viewBox = e.target.closest('#cornell-governance-metabox-tab-liaison');
    if (typeof viewBox === 'undefined' || viewBox === null) {
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
    this.timestampField = this.currentBox.querySelectorAll('.cornell-governance-timestamp');
    this.timestampField.forEach(field => {
      field.querySelector('input[type="hidden"]').value = this.getCurrentDateTime();
    });
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
    this.timestampField = this.activeFormTab.querySelectorAll('.cornell-governance-timestamp');
    this.timestampField.forEach(field => {
      field.querySelector('input[type="hidden"]').value = this.getCurrentDateTime();
    });
    this.log('Head: ');
    this.log(head);
    this.log('Current Box Text: ' + this.currentBoxText);
    this.log('Current Box: ');
    this.log(this.currentBox);
    this.log('Current Active Tab:');
    this.log(this.activeFormTab);
    let atts = {
      'ajax_action': 'cornell_governance_save_meta',
      'ajax_url': new URL('/wp-admin/admin-ajax.php', 'http://cornell-governance.local')
    };
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
    const fields = ['goals', 'liaison', 'primary-audience', 'problem', 'review-cycle', 'secondary-audience', 'supervisor'];
    fields.forEach(field => {
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
    fieldsets.forEach(fieldset => {
      const fieldsetParent = fieldset.closest('.cornell-governance-fieldset');
      let parsed = null;
      if (fieldsetParent.querySelectorAll('input[type="radio"]').length >= 1) {
        const parser = new modules_HTMLParser(json['compliance-fieldset'].liaison);
        parsed = parser.parseHTML();
      } else {
        const parser = new modules_HTMLParser(json['compliance-fieldset'].steward);
        parsed = parser.parseHTML();
      }
      const taskList = fieldsetParent.querySelector('.cornell-governance-tasks');
      if (taskList) {
        if (json.hasOwnProperty('task-list-checkboxes') && taskList.querySelectorAll('input[type="checkbox"]').length >= 1) {
          const taskParser = new modules_HTMLParser(json['task-list-checkboxes']);
          const parsedTasks = taskParser.parseHTML();
          taskList.replaceWith(parsedTasks.querySelector('.cornell-governance-tasks'));
        } else {
          const allTasks = taskList.querySelectorAll('input[type="checkbox"]');
          if (allTasks.length >= 1) {
            allTasks.forEach(task => {
              task.checked = false;
              task.closest('label').classList.remove('done');
            });
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
    taskLists.forEach(list => {
      const tasks = list.querySelectorAll('input[type="checkbox"], ol.cornell-governance-page-info-tasks-readonly > li');
      if (tasks.length <= 0) {
        return;
      }
      const taskParser = new modules_HTMLParser(json['task-list-checkboxes']);
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
    this.activeFormTab = this.currentBox;
    if (this.currentBox.querySelectorAll('[role="tabpanel"]').length >= 1) {
      this.activeFormTab = this.currentBox.querySelector('[role="tabpanel"]:not([hidden])');
      head = this.currentBox.querySelector('legend');
    }
    this.currentBoxText = head.innerText;
    this.timestampField = this.activeFormTab.querySelectorAll('.cornell-governance-timestamp');
    this.timestampField.forEach(field => {
      field.querySelector('input[type="hidden"]').value = this.getCurrentDateTime();
    });
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
    const timestampField = target.querySelectorAll('.cornell-governance-timestamp');
    this.timestampField.forEach(field => {
      const timestampInput = field.querySelector('input[type=hidden]');
      const timestampLabel = field.querySelector('label');
      const timeValue = timestampInput.value;
      const originalLabel = timestampInput.getAttribute('data-original-label');
      timestampLabel.innerText = originalLabel + ': ' + timeValue;
    });
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
      taskCheckboxes.forEach(checkbox => {
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
/******/ })()
;