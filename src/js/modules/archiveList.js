import TabsManual from "./tabs-manual";

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

        if ( this.totalPages <= 1 ) {
            return;
        }

        const newContainer = document.createElement('div');
        newContainer.classList.add('governance-paginated-list');
        this.container = newContainer;

        this.separate();

        this.tabList = new TabsManual(this.container);

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
            let end = (((i + 1) * this.perPage) - 1);

            console.log(`Creating a list that should have items ${currentIndex} through ${end}`);

            separateLists[i] = Array.from(this.listItems).slice(currentIndex, end);
        }

        let page = 1;

        separateLists.forEach((items) => {
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
        this.listOfClasses.forEach((c) => {
            newList.classList.add(c);
        });

        items.forEach((item) => {
            const newItem = document.createElement('li');
            newItem.innerHTML = item.innerHTML;
            newList.appendChild(newItem);
        });

        newList.classList.add('is-hidden');

        this.container.appendChild(newList);
    }
}

export default archiveList;