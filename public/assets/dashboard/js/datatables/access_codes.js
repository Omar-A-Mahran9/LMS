"use strict";

var datatable;
// Class definition
var KTDatatablesServerSide = (function () {
    let dbTable = "generateCode";
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
                { data: "id" }, // Checkbox
                { data: "code" }, // Code
                { data: "class.title" }, // Class
                { data: "usage_limit" }, // Usage Limit
                { data: "used_count" }, // Used Count
                { data: "is_single_use" }, // Single Use
                { data: "is_active" }, // Active
                { data: "created_at" }, // Created At
                { data: null }, // Actions
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
                    targets: 5, // Single Use column
                    render: function (data, type, row) {
                        return row.is_single_use
                            ? `<span class="badge badge-info">${__(
                                  "Yes"
                              )}</span>`
                            : `<span class="badge badge-secondary">${__(
                                  "No"
                              )}</span>`;
                    },
                },
                {
                    targets: 6, // Active column
                    render: function (data, type, row) {
                        return row.is_active
                            ? `<span class="badge badge-success">${__(
                                  "Active"
                              )}</span>`
                            : `<span class="badge badge-danger">${__(
                                  "Inactive"
                              )}</span>`;
                    },
                },
                {
                    targets: 8, // Actions
                    data: null,
                    orderable: false,
                    render: function (data, type, row) {
                        return `
                <div>
                    <a href="#" class="btn btn-sm btn-light" data-kt-docs-table-filter="edit_row">${__(
                        "Edit"
                    )}</a>
                    <a href="/dashboard/${dbTable}/${
                            row.id
                        }" class="btn btn-sm btn-info">${__("Show")}</a>
                    <a href="#" class="btn btn-sm btn-danger" data-kt-docs-table-filter="delete_row">${__(
                        "Delete"
                    )}</a>
                </div>
            `;
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
            handlePreviewAttachments();
        });
    };

    var handleEditRows = () => {
        const editButtons = document.querySelectorAll(
            '[data-kt-docs-table-filter="edit_row"]'
        );

        editButtons.forEach((btn) => {
            btn.addEventListener("click", function (e) {
                e.preventDefault();

                let currentBtnIndex = $(editButtons).index(btn);
                let data = datatable.row(currentBtnIndex).data();

                // Set form title
                $("#form_title").text(__("Edit Book"));

                $(".image-input-wrapper").css(
                    "background-image",
                    `url('${data.full_image_path}')`
                );

                // Titles
                $("#title_ar_inp").val(data.title_ar);
                $("#title_en_inp").val(data.title_en);

                tinymce
                    .get("description_ar_inp")
                    .setContent(data.description_ar);
                tinymce
                    .get("description_en_inp")
                    .setContent(data.description_en);
                tinymce.get("note_ar_inp").setContent(data.note_ar);
                tinymce.get("note_en_inp").setContent(data.note_en);

                // Pricing controls
                if (data.is_free) {
                    $("#is_free_switch").prop("checked", true);
                    $("#price_inp").val(0).prop("disabled", true);
                } else {
                    $("#is_free_switch").prop("checked", false);
                    $("#price_inp").val(data.price).prop("disabled", false);
                }

                if (data.have_discount) {
                    $("#have_discount_switch").prop("checked", true);
                    $("#discount_percentage_inp")
                        .val(data.discount_percentage)
                        .prop("disabled", false);
                } else {
                    $("#have_discount_switch").prop("checked", false);
                    $("#discount_percentage_inp")
                        .val("")
                        .prop("disabled", true);
                }

                // Attachment preview (link or filename)
                if (data.full_attachment_path) {
                    $("#attachment_preview").html(
                        `<a href="${
                            data.full_attachment_path
                        }" target="_blank" class="btn btn-sm btn-info">
                        ${__("Current Attachment")}
                    </a>`
                    );
                } else {
                    $("#attachment_preview").html("");
                }

                // Reset file input (clear any previously selected file)
                $("#attachment_inp").val("");
                // Relationships

                // Flags
                $("#is_active_switch").prop("checked", data.is_active);
                $("#is_featured_switch").prop("checked", data.is_featured);

                // Reset form method & action
                $("#crud_form").attr("action", `/dashboard/books/${data.id}`);

                // Remove previous _method input if any, then add PUT
                $("#crud_form").find('input[name="_method"]').remove();
                $("#crud_form").prepend(
                    `<input type="hidden" name="_method" value="PUT">`
                );

                // Show modal
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
