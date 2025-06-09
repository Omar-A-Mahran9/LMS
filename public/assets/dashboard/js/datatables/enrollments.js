"use strict";

var datatable;
// Class definition
var KTDatatablesServerSide = (function () {
    let dbTable = "enrollments";
    // Private functions
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
                { data: "id" }, // checkbox
                { data: "student.first_name" }, // student name
                { data: "course.title" }, // course title
                { data: "payment_type" }, // payment type
                { data: "status" }, // status
                { data: "created_at" }, // enrollment date
                { data: null }, // actions
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
                    render: function (data, type, row) {
                        return `${row.student.first_name} ${row.student.last_name}`;
                    },
                },
                {
                    targets: 2,
                    render: function (data, type, row) {
                        return row.course?.title ?? "-";
                    },
                },
                {
                    targets: 3,
                    render: function (data) {
                        return `<span class="badge badge-light-info">${data.replace(
                            "_",
                            " "
                        )}</span>`;
                    },
                },
                {
                    targets: 4,
                    render: function (data) {
                        let color =
                            {
                                approved: "success",
                                pending: "warning",
                                rejected: "danger",
                            }[data] ?? "secondary";

                        return `<span class="badge badge-light-${color}">${__(data)}</span>`;
                    },
                },
                {
                    targets: 5,
                    render: function (data) {
                        return new Date(data).toLocaleDateString();
                    },
                },
                {
                    targets: -1,
                    orderable: false,
                    render: function (data, type, row) {
                        return `
                        <div>
                            <a href="#" class="btn btn-light btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                <i class="ki-outline ki-dots-horizontal fs-2"></i>
                            </a>
                            <div class="menu menu-sub menu-sub-dropdown" data-kt-menu="true">
                                <div class="menu-item px-3">
                                    <a href="javascript:;" class="menu-link px-3" data-kt-docs-table-filter="edit_row">
                                        ${__("Edit")}
                                    </a>
                                </div>
                                <div class="menu-item px-3">
                                    <a href="javascript:;" class="menu-link px-3" data-kt-docs-table-filter="delete_row">
                                        ${__("Delete")}
                                    </a>
                                </div>
                            </div>
                        </div>`;
                    },
                },
            ],

            // Add data-filter attribute
            createdRow: function (row, data, dataIndex) {
                // $(row).find('td:eq(4)').attr('data-filter', data.CreditCardType);
            },
        });

        // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
        datatable.on("draw", function () {
            initToggleToolbar();
            toggleToolbars();
            handleEditRows();
            deleteRowWithURL(`/dashboard/${dbTable}/`);
            deleteSelectedRowsWithURL({
                url: `/dashboard/${dbTable}/delete-selected`,
                restoreUrl: `/dashboard/${dbTable}/restore-selected`,
            });
            KTMenu.createInstances();
        });
    };

    var handleEditRows = () => {
        // Select all edit buttons
        const editButtons = document.querySelectorAll(
            '[data-kt-docs-table-filter="edit_row"]'
        );

        editButtons.forEach((d) => {
            // edit button on click
            d.addEventListener("click", function (e) {
                e.preventDefault();

                let currentBtnIndex = $(editButtons).index(d);
                let data = datatable.row(currentBtnIndex).data();

                $("#form_title").text(__("Edit city"));
                $(".image-input-wrapper").css(
                    "background-image",
                    `url('${data.full_image_path}')`
                );
                $("#name_ar_inp").val(data.name_ar);
                $("#name_en_inp").val(data.name_en);
                $("#crud_form").attr(
                    "action",
                    `/dashboard/${dbTable}/${data.id}`
                );
                $("#crud_form").prepend(
                    `<input type="hidden" name="_method" value="PUT">`
                );
                $("#crud_modal").modal("show");
            });
        });
    };

    var handlePreviewAttachments = () => {
        // Select all edit buttons
        const previewButtons = $('[data-action="preview_attachments"]');

        $.each(previewButtons, function (indexInArray, button) {
            $(button).on("click", function (e) {
                e.preventDefault();

                let data = datatable.row(indexInArray).data();
                $(".attachments").html("");

                $(".attachments").append(`
                    <!--begin::Overlay-->
                    <a class="d-block overlay" data-fslightbox="lightbox-basic" href="${data.full_image_path}">
                        <!--begin::Action-->
                        <div class="overlay-layer card-rounded bg-dark bg-opacity-25 shadow">
                            <i class="bi bi-eye-fill text-white fs-3x"></i>
                        </div>
                        <!--end::Action-->

                    </a>
                    <!--end::Overlay-->
                `);
                refreshFsLightbox();
                $("[data-fslightbox='lightbox-basic']:first").trigger("click");
            });
        });
    };
    // Public methods
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
            handlePreviewAttachments();
        },
    };
})();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTDatatablesServerSide.init();
});
