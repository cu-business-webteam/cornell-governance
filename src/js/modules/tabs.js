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

        this.tabs.forEach((tab) => {
            tab.addEventListener("click", this.changeTabs);
        });

        this.container.addEventListener("keydown", (e) => {
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
        tabList
            .querySelectorAll(':scope > [aria-selected="true"]')
            .forEach((t) => t.setAttribute("aria-selected", false));

        // Set this tab as selected
        targetTab.setAttribute("aria-selected", true);

        // Hide all tab panels
        tabGroup
            .querySelectorAll(':scope > [role="tabpanel"]')
            .forEach((p) => p.setAttribute("hidden", true));

        // Show the selected panel
        activePanel.removeAttribute("hidden");

        let targetScroll = targetTab;
        let metabox = targetTab.closest('.postbox.cornell-governance-metabox, .cornell-governance-tablist');
        if (metabox !== null) {
            targetScroll = metabox;
        }

        targetScroll.scrollIntoView({behavior: 'smooth'});

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

export default governanceTabs;