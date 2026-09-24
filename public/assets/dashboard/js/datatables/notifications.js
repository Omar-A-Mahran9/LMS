"use strict";

var datatable;

var KTDatatablesServerSide = (function () {
    let dbTable = "notifications";

    const escapeHtml = (value) =>
        $("<div>").text(value ?? "").html();

    const typeBadge = (type) => {
        const types = {
            message: ["badge-light-primary", __("Message")],
            new_class: ["badge-light-success", __("New class")],
            new_course: ["badge-light-warning", __("New course")],
            subscription: ["badge-light-info", __("Subscription activated")],
        };
        const [cls, label] = types[type] ?? ["badge-light", type];
        return `<span class="badge ${cls}">${label}</span>`;
    };

    const targetLabel = (row) => {
        if (row.target === "categories") {
            const names = (row.categories ?? [])
                .map((c) => escapeHtml(c.name))
                .join("، ");
            return `${__("Grades")}: ${names || "-"}`;
        }
        if (row.target === "students") {
            return `${row.students_count} ${__("student(s)")}`;
        }
        return __("All students");
    };

    var initDatatable = function () {
        datatable = $("#kt_datatable").DataTable({
            language: language,
            searchDelay: searchDelay,
            processing: processing,
            serverSide: serverSide,
            order: [],
            stateSave: saveState,
            select: {
                style: "multi",
                selector: 'td:first-child input[type="checkbox"]',
                className: "row-selected",
            },
            ajax: {
                url: `/dashboard/${dbTable}`,
                data: (d) => {
                    d.filter_type = $("#filter_type").val();
                },
            },
            columns: [
                { data: "id" },
                { data: "title_ar" },
                { data: "type" },
                { data: "target" },
                { data: "readers_count" },
                { data: "created_at" },
                { data: null },
            ],
            columnDefs: [
                { targets: "_all", orderable: false },
                {
                    targets: 0,
                    render: (data) => `
                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" value="${data}" />
                        </div>`,
                },
                {
                    targets: 1,
                    className: "text-start",
                    render: (data, type, row) => `
                        <div class="d-flex flex-column mw-400px">
                            <span class="text-gray-800 fw-bold mb-1">${escapeHtml(row.title_ar)}</span>
                            <span class="text-gray-600 fs-7">${escapeHtml(row.body_ar)}</span>
                            ${row.link ? `<span class="text-primary fs-8 mt-1" dir="ltr">${escapeHtml(row.link)}</span>` : ""}
                        </div>`,
                },
                { targets: 2, render: (data) => typeBadge(data) },
                { targets: 3, render: (data, type, row) => targetLabel(row) },
                {
                    targets: 4,
                    render: (data) =>
                        `<span class="badge badge-light">${data ?? 0}</span>`,
                },
                {
                    targets: 5,
                    render: (data, type, row) => `
                        <div class="d-flex flex-column">
                            <span>${data ?? ""}</span>
                            ${row.sender ? `<span class="text-muted fs-8">${escapeHtml(row.sender.name)}</span>` : `<span class="text-muted fs-8">${__("Automatic")}</span>`}
                        </div>`,
                },
                {
                    targets: -1,
                    render: () =>
                        canDeleteNotifications
                            ? `<a href="#" class="btn btn-sm btn-light-danger" data-kt-docs-table-filter="delete_row">${__("Delete")}</a>`
                            : "",
                },
            ],
        });

        datatable.on("draw", function () {
            initToggleToolbar();
            toggleToolbars();
            deleteRowWithURL(`/dashboard/${dbTable}/`);
            deleteSelectedRowsWithURL({
                url: `/dashboard/${dbTable}/delete-selected`,
                restoreUrl: null,
            });
        });
    };

    return {
        init: function () {
            initDatatable();
            handleSearchDatatable();
            initToggleToolbar();
            $("#filter_type").on("change", () => datatable.draw());
        },
    };
})();

KTUtil.onDOMContentLoaded(function () {
    KTDatatablesServerSide.init();
});
