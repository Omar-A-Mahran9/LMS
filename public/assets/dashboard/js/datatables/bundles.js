"use strict";

var datatable;

var KTDatatablesServerSide = (function () {
    let dbTable = "bundles";

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
            },

            columns: [
                { data: "id" },
                { data: "image_url" },
                { data: "title_ar" },
                { data: "classes_count" },
                { data: "codes_count" },
                { data: "is_active" },
                { data: "created_at" },
                { data: null },
            ],

            columnDefs: [
                /*
                |--------------------------------------------------------------------------
                | Checkbox
                |--------------------------------------------------------------------------
                */

                {
                    targets: 0,
                    orderable: false,
                    render: function (data) {
                        return `
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" value="${data}" />
                            </div>
                        `;
                    },
                },

                /*
                |--------------------------------------------------------------------------
                | Image
                |--------------------------------------------------------------------------
                */

                {
                    targets: 1,
                    orderable: false,
                    render: function (data, type, row) {
                        return `
                            <!--begin::Overlay-->
                            <a class="d-block overlay" data-action="preview_attachments" href="#">
                                <!--begin::Image-->
                                <div class="overlay-wrapper bgi-no-repeat bgi-position-center bgi-size-cover card-rounded h-100px"
                                    style="background-image:url('${row.full_image_path}')">
                                </div>
                                <!--end::Image-->

                                <!--begin::Action-->
                                <div class="overlay-layer card-rounded bg-dark bg-opacity-25 shadow">
                                    <i class="bi bi-eye-fill text-white fs-3x"></i>
                                </div>
                                <!--end::Action-->
                            </a>
                            <!--end::Overlay-->
                        `;
                    },
                },

                /*
                |--------------------------------------------------------------------------
                | Title
                |--------------------------------------------------------------------------
                */

                {
                    targets: 2,
                    render: function (data, type, row) {
                        return `
                            <div class="d-flex flex-column">
                                <span class="fw-bold">
                                    ${row.title_ar ?? "-"}
                                </span>

                                <small class="text-muted">
                                    ${row.title_en ?? ""}
                                </small>
                            </div>
                        `;
                    },
                },

                /*
                |--------------------------------------------------------------------------
                | Classes Count
                |--------------------------------------------------------------------------
                */

                {
                    targets: 3,
                    render: function (data) {
                        return `
                            <span class="badge badge-light-primary">
                                ${data ?? 0}
                            </span>
                        `;
                    },
                },

                /*
                |--------------------------------------------------------------------------
                | Codes Count
                |--------------------------------------------------------------------------
                */

                {
                    targets: 4,
                    render: function (data) {
                        return `
                            <span class="badge badge-light-info">
                                ${data ?? 0}
                            </span>
                        `;
                    },
                },

                /*
                |--------------------------------------------------------------------------
                | Status
                |--------------------------------------------------------------------------
                */

                {
                    targets: 5,
                    render: function (data, type, row) {
                        return row.is_active
                            ? `<span class="badge badge-success">${__("Active")}</span>`
                            : `<span class="badge badge-danger">${__("Inactive")}</span>`;
                    },
                },

                /*
                |--------------------------------------------------------------------------
                | Actions
                |--------------------------------------------------------------------------
                */

                {
                    targets: -1,
                    data: null,
                    orderable: false,

                    render: function (data, type, row) {
                        return `
                        <div>
                            <a href="#"
                                class="btn btn-light btn-active-light-primary btn-sm"
                                data-kt-menu-trigger="click">

                                <span class="svg-icon svg-icon-dark svg-icon-1 m-0">
                                    <i class="ki-outline ki-setting-2 fs-2"></i>
                                </span>
                            </a>

                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-150px py-4"
                                data-kt-menu="true">

                                <div class="menu-item px-3">
                                    <a href="javascript:;"
                                        class="menu-link px-3"
                                        data-kt-docs-table-filter="edit_row">

                                        ${__("Edit")}
                                    </a>
                                </div>

                                <div class="menu-item px-3">
                                    <a href="/dashboard/bundles/${row.id}"
                                        class="menu-link px-3">

                                        ${__("Show")}
                                    </a>
                                </div>

                                <div class="menu-item px-3">
                                    <a href="#"
                                        class="menu-link px-3"
                                        data-kt-docs-table-filter="delete_row">

                                        ${__("Delete")}
                                    </a>
                                </div>

                            </div>
                        </div>
                        `;
                    },
                },
            ],

            createdRow: function () {},
        });

        datatable.on("draw", function () {
            initToggleToolbar();

            // toggleToolbars();

            handleEditRows();

            deleteRowWithURL(`/dashboard/${dbTable}/`);

            deleteSelectedRowsWithURL({
                url: `/dashboard/${dbTable}/delete-selected`,
                restoreUrl: `/dashboard/${dbTable}/restore-selected`,
            });

            KTMenu.createInstances();
        });
    };

    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    var handleEditRows = () => {
        const editButtons = document.querySelectorAll(
            '[data-kt-docs-table-filter="edit_row"]',
        );

        editButtons.forEach((btn) => {
            btn.addEventListener("click", function (e) {
                e.preventDefault();

                let currentBtnIndex = $(editButtons).index(btn);

                let data = datatable.row(currentBtnIndex).data();

                $("#form_title").text(__("Edit Bundle"));

                $("#title_ar_inp").val(data.title_ar);

                $("#title_en_inp").val(data.title_en);

                tinymce
                    .get("description_ar_inp")
                    .setContent(data.description_ar);
                tinymce
                    .get("description_en_inp")
                    .setContent(data.description_en);
                $("#code_count_inp").val(data.codes_count);
                $("#usage_limit_inp").val(data.usage_limit ?? "");
                 $("#single_use_switch").prop("checked", data.single_use == 1);

                $("#classes_inp")
                    .val(data.classes.map((x) => x.id))
                    .trigger("change");

                $("#is_active_switch").prop("checked", data.is_active == 1);

                $("#crud_form").attr(
                    "action",
                    `/dashboard/${dbTable}/${data.id}`,
                );

                $("#crud_form").find('input[name="_method"]').remove();

                $("#crud_form").prepend(`
                    <input type="hidden"
                        name="_method"
                        value="PUT">
                `);

                $("#crud_modal").modal("show");
            });
        });
    };

    return {
        init: function () {
            initDatatable();

            handleSearchDatatable();

            initToggleToolbar();

            handleEditRows();

            deleteRowWithURL(`/dashboard/${dbTable}/`);

            deleteSelectedRowsWithURL({
                url: `/dashboard/${dbTable}/delete-selected`,
                restoreUrl: `/dashboard/${dbTable}/restore-selected`,
            });
        },
    };
})();

KTUtil.onDOMContentLoaded(function () {
    KTDatatablesServerSide.init();
});
