class governanceTabs {
    constructor(el) {
        this.container = el;
        this.tabs = this.container.querySelectorAll(':scope > [role="tab"]');
        this.tabFocus = 0;

        this.changeTabs = this.toggleTab.bind(this);
        this.init();
    }

    init() {
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
                        tabFocus = 0;
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
        tabGroup
            .querySelector(`#${targetTab.getAttribute("aria-controls")}`)
            .removeAttribute("hidden");
    }
}

export default governanceTabs;