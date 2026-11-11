/*
 * Dashboard layout helpers (loaded on every page from partials/foot).
 *  1. Sidebar minimize toggle (desktop), remembered in localStorage.
 *  2. List pages: move the "Add" toolbar from the card body into the
 *     card header so the header reads "Title .......... [+ Add]".
 */
(function () {
    "use strict";

    var STORAGE_KEY = "lms_sidebar_minimize";

    function storageSet(value) {
        try {
            localStorage.setItem(STORAGE_KEY, value);
        } catch (e) {}
    }

    function initSidebarToggle() {
        var toggle = document.getElementById("kt_app_sidebar_toggle");
        var sidebar = document.getElementById("kt_app_sidebar");
        if (!toggle || !sidebar) return;

        toggle.addEventListener("click", function (e) {
            e.preventDefault();
            var body = document.body;
            var minimized = body.getAttribute("data-kt-app-sidebar-minimize") === "on";

            if (minimized) {
                body.removeAttribute("data-kt-app-sidebar-minimize");
                storageSet("off");
            } else {
                body.setAttribute("data-kt-app-sidebar-minimize", "on");
                storageSet("on");
                // Stay collapsed until the pointer leaves, otherwise the
                // hover-to-expand behaviour reopens it straight away.
                sidebar.classList.add("animating");
                var release = function () {
                    sidebar.classList.remove("animating");
                    sidebar.removeEventListener("mouseleave", release);
                };
                sidebar.addEventListener("mouseleave", release);
            }

            toggle.setAttribute(
                "aria-expanded",
                body.getAttribute("data-kt-app-sidebar-minimize") === "on" ? "false" : "true"
            );
        });
    }

    function moveAddButtonsToCardHeader() {
        var toolbars = document.querySelectorAll(
            '#kt_app_content .card-body [data-kt-docs-table-toolbar="base"]'
        );

        toolbars.forEach(function (toolbar) {
            var cardBody = toolbar.closest(".card-body");
            var card = cardBody && cardBody.parentElement;
            if (!card || !card.classList.contains("card")) return;

            var header = Array.prototype.find.call(card.children, function (el) {
                return el.classList.contains("card-header");
            });
            if (!header) return;

            var headerToolbar = Array.prototype.find.call(header.children, function (el) {
                return el.classList.contains("card-toolbar");
            });
            if (!headerToolbar) {
                headerToolbar = document.createElement("div");
                headerToolbar.className = "card-toolbar";
                header.appendChild(headerToolbar);
            }

            var selected = toolbar.parentElement.querySelector(
                '[data-kt-docs-table-toolbar="selected"]'
            );

            headerToolbar.appendChild(toolbar);
            if (selected) headerToolbar.appendChild(selected);

            // The list headers carry a leftover collapse toggle pointing at an
            // element that doesn't exist; drop it so the header isn't "clickable".
            var target = header.getAttribute("data-bs-target");
            if (
                header.getAttribute("data-bs-toggle") === "collapse" &&
                (!target || !document.querySelector(target))
            ) {
                header.removeAttribute("data-bs-toggle");
                header.removeAttribute("data-bs-target");
                header.removeAttribute("role");
                header.removeAttribute("aria-expanded");
                header.removeAttribute("aria-controls");
                header.classList.remove("cursor-pointer");
            }
        });
    }

    function init() {
        initSidebarToggle();
        moveAddButtonsToCardHeader();
    }

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init);
    } else {
        init();
    }
})();
