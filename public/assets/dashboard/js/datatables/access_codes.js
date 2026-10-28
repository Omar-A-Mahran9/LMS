"use strict";

var datatable;
// Class definition
var KTDatatablesServerSide = (function () {
    let dbTable = "generateCode";

    const statusBadges = {
        unused: ["badge-light-success", __("Unused")],
        used: ["badge-light-info", __("Partially used")],
        exhausted: ["badge-light-secondary text-gray-700", __("Exhausted")],
        inactive: ["badge-light-danger", __("Inactive")],
    };

    // Values of the filters bar, sent with every table request and with the PDF export
    var currentFilters = function () {
        let filters = {};
        $("#codes_filters [data-filter]").each(function () {
            let value = $(this).val();
            if (value) filters[$(this).data("filter")] = value;
        });

        let search = $('[data-kt-docs-table-filter="search"]').val().trim();
        if (search) filters.search = search;

        return filters;
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
                data: function (params) {
                    let filters = currentFilters();
                    delete filters.search; // DataTables sends its own search[value]
                    return Object.assign(params, filters);
                },
            },
            columns: [
                { data: "id" },
                { data: "code" },
                { data: "class_id" },
                { data: "used_count" },
                { data: "is_active" },
                { data: "created_at" },
                { data: null },
            ],
            columnDefs: [
                {
                    targets: 0,
                    orderable: false,
                    render: function (data) {
                        return `
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" value="${data}" />
                            </div>`;
                    },
                },
                {
                    targets: 1,
                    render: function (data) {
                        return `
                            <div class="d-inline-flex align-items-center gap-2">
                                <span class="code-text fw-bold text-gray-800 fs-5">${data}</span>
                                <button type="button" class="btn btn-icon btn-sm btn-light btn-active-light-primary w-25px h-25px" data-copy="${data}" title="${__("Copy")}">
                                    <i class="far fa-copy fs-7"></i>
                                </button>
                            </div>`;
                    },
                },
                {
                    targets: 2,
                    orderable: false,
                    render: function (data, type, row) {
                        return `
                            <div class="d-flex flex-column">
                                <span class="text-gray-800">${row.class?.title ?? "-"}</span>
                                <span class="text-muted fs-7">${row.class?.course?.title ?? ""}</span>
                            </div>`;
                    },
                },
                {
                    targets: 3,
                    render: function (data, type, row) {
                        let max = row.single_use ? 1 : row.usage_limit;
                        let percent = max ? Math.min(100, (row.used_count / max) * 100) : 0;

                        return `
                            <div class="d-flex flex-column align-items-center">
                                <span class="fw-bold">${row.used_count} / ${max ?? "∞"}</span>
                                ${max ? `<div class="progress h-4px w-75px mt-1"><div class="progress-bar bg-primary" style="width:${percent}%"></div></div>` : ""}
                                <span class="text-muted fs-8 mt-1">${row.single_use ? __("Single Use") : max ? __("Multi use") : __("Unlimited")}</span>
                            </div>`;
                    },
                },
                {
                    targets: 4,
                    render: function (data, type, row) {
                        let [badge, label] = statusBadges[row.status] ?? ["", row.status];
                        return `<span class="badge ${badge}">${label}</span>`;
                    },
                },
                {
                    targets: -1,
                    data: null,
                    orderable: false,
                    render: function (data, type, row) {
                        return `
                            <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                ${__("Actions")} <i class="fas fa-angle-down ms-1"></i>
                            </a>
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-150px py-4" data-kt-menu="true">
                                <div class="menu-item px-3">
                                    <a href="/dashboard/${dbTable}/${row.id}" class="menu-link px-3">${__("Show")}</a>
                                </div>
                                <div class="menu-item px-3">
                                    <a href="javascript:;" class="menu-link px-3" data-kt-docs-table-filter="edit_row">${__("Edit")}</a>
                                </div>
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3 text-danger" data-kt-docs-table-filter="delete_row">${__("Delete")}</a>
                                </div>
                            </div>`;
                    },
                },
            ],
        });

        // Status cards follow the same filters as the table
        datatable.on("xhr", function (e, settings, json) {
            if (!json?.stats) return;
            $.each(json.stats, (status, count) => $(`[data-stat="${status}"]`).text(count));
        });

        datatable.on("draw", function () {
            initToggleToolbar();
            toggleToolbars();
            deleteRowWithURL(`/dashboard/${dbTable}/`);
            deleteSelectedRowsWithURL({ url: `/dashboard/${dbTable}/delete-selected` });
            KTMenu.createInstances();
        });
    };

    var handleFilters = function () {
        $("#filter_class").select2({ allowClear: true, width: "100%" });

        $("#codes_filters [data-filter]").on("change", function () {
            if ($(this).data("filter") === "status") syncStatusCards();
            datatable.draw();
        });

        $(".status-card").on("click", function () {
            $("#filter_status").val($(this).data("status")).trigger("change");
        });

        $("#reset_filters").on("click", function () {
            $("#codes_filters [data-filter]").val("");
            $("#filter_class").trigger("change.select2");
            $('[data-kt-docs-table-filter="search"]').val("");
            syncStatusCards();
            datatable.search("").draw();
        });

        // The export follows the filters at the moment of the click
        $(".btn-export-pdf").on("click", function () {
            let params = Object.assign(currentFilters(), { layout: $(this).data("layout") });
            $(this).attr("href", `${codesExportUrl}?${$.param(params)}`);
        });
    };

    var syncStatusCards = function () {
        let status = $("#filter_status").val();
        $(".status-card").removeClass("active");
        $(`.status-card[data-status="${status}"]`).addClass("active");
    };

    var handleCopy = function () {
        $("#kt_datatable").on("click", "[data-copy]", function () {
            navigator.clipboard.writeText($(this).data("copy"));
            showToast(__("Copied"));
        });
    };

    var handleForm = function () {
        const $modal = $("#crud_modal");
        const $form = $("#crud_form");

        // Inside the modal select2 needs dropdownParent, or its search box can't take focus
        $("#class_id_inp").select2({ dropdownParent: $modal, width: "100%" });

        // A single-use code has one use: the limit doesn't apply
        const toggleUsageLimit = () => {
            let singleUse = $("#single_use_switch").is(":checked");
            $("#usage_limit_inp").prop("disabled", singleUse);
            if (singleUse) $("#usage_limit_inp").val("");
        };

        // A custom code is a single code: the count only applies to auto-generated ones
        const toggleCodeCount = () => {
            let isEdit = $form.find('input[name="_method"]').length > 0;
            let hasCode = $("#code_inp").val().trim() !== "";
            $("#code_count_wrapper").toggle(!isEdit);
            $("#code_count_inp").prop("disabled", isEdit || hasCode);
        };

        $("#single_use_switch").on("change", toggleUsageLimit);
        $("#code_inp").on("input", toggleCodeCount);

        const resetForm = () => {
            $form[0].reset();
            $form.find('input[name="_method"]').remove();
            $form.find(".invalid-feedback").text("");
            $form.find(".is-invalid").removeClass("is-invalid");
            $("#class_id_inp").val($("#filter_class").val() || "").trigger("change");
        };

        $("#add_btn").on("click", function (e) {
            e.preventDefault();
            resetForm();
            $form.attr("action", codesStoreUrl);
            $("#form_title").text(__("Generate codes"));
            $("#code_inp").attr("placeholder", __("Leave blank to auto-generate"));
            toggleUsageLimit();
            toggleCodeCount();
            $modal.modal("show");
        });

        $("#kt_datatable").on("click", '[data-kt-docs-table-filter="edit_row"]', function (e) {
            e.preventDefault();
            let data = datatable.row($(this).closest("tr")).data();

            resetForm();
            $form.prepend(`<input type="hidden" name="_method" value="PUT">`);
            $form.attr("action", `/dashboard/${dbTable}/${data.id}`);
            $("#form_title").text(__("Edit Access Code"));

            $("#class_id_inp").val(data.class_id).trigger("change");
            $("#code_inp").val(data.code).attr("placeholder", "");
            $("#usage_limit_inp").val(data.usage_limit ?? "");
            $("#is_active_switch").prop("checked", !!data.is_active);
            $("#single_use_switch").prop("checked", !!data.single_use);

            toggleUsageLimit();
            toggleCodeCount();
            $modal.modal("show");
        });
    };

    // Public methods
    return {
        init: function () {
            initDatatable();
            handleSearchDatatable();
            initToggleToolbar();
            handleFilters();
            handleCopy();
            handleForm();
        },
    };
})();

// After generating a batch: offer its PDF right away
window["onCodesSaved"] = (response) => {
    onAjaxSuccess();

    if (!response?.export_url) return;

    Swal.fire({
        icon: "success",
        title: __("Codes generated successfully"),
        text: `${response.count} ${__("codes are ready. Download them now?")}`,
        showDenyButton: true,
        showCancelButton: true,
        buttonsStyling: false,
        confirmButtonText: __("Cards to print and cut"),
        denyButtonText: __("Report table"),
        cancelButtonText: __("Later"),
        customClass: {
            confirmButton: "btn fw-bold btn-primary",
            denyButton: "btn fw-bold btn-light-primary",
            cancelButton: "btn fw-bold btn-light",
        },
    }).then((result) => {
        if (result.isConfirmed) window.open(`${response.export_url}&layout=cards`, "_blank");
        else if (result.isDenied) window.open(`${response.export_url}&layout=table`, "_blank");
    });
};

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTDatatablesServerSide.init();
});
