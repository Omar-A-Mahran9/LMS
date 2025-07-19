"use strict";

var datatable;
// Class definition
var KTDatatablesServerSide = (function () {
    let dbTable = "courses";

    $("#filter_combined").on("change", function () {
        $("#kt_datatable").DataTable().ajax.reload();
    });

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
                url: "/dashboard/courses",
                data: function (d) {
                    d.filter_combined = $("#filter_combined").val();
                },
            },

            columns: [
                { data: "id" },
                { data: "title" },
                { data: "image" },
                { data: "price" },
                { data: "instructor" },
                { data: "start_date" },
                { data: "is_active" },
                { data: "created_at" },
                { data: "views" },
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
                    render: function (data, type, row) {
                        return `
                            <div>
                                <!--begin::Info-->
                                <div class="d-flex flex-column justify-content-center">
<a href="/dashboard/courses/${row.id}" class="mb-1 text-gray-800 text-hover-primary">
                        ${row.title}
                    </a>                                </div>
                                <!--end::Info-->
                            </div>
                        `;
                    },
                },

                {
                    targets: 2,
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

                {
                    targets: 3, // Price column
                    render: function (data, type, row) {
                        // If course is marked free or price is 0
                        if (row.is_free || !data || data == 0) {
                            return '<span class="text-muted">Free</span>';
                        }

                        // If there is a discount
                        if (row.have_discount && row.discount_percentage) {
                            let originalPrice = parseFloat(data).toFixed(2);
                            let discount = parseFloat(row.discount_percentage);
                            let discountedPrice = (
                                originalPrice *
                                (1 - discount / 100)
                            ).toFixed(2);

                            return `
                <span class="text-muted text-decoration-line-through">${originalPrice} EGP</span>
                <br/>
                <span class="text-danger fw-bold">${discountedPrice} EGP</span>
            `;
                        }

                        // No discount, show regular price
                        return `${parseFloat(data).toFixed(2)} EGP`;
                    },
                },
                {
                    targets: 4,
                    render: function (data, type, row) {
                        return `
                            <div>
                                <!--begin::Info-->
                                <div class="d-flex flex-column justify-content-center">
                                    <a href="javascript:;" class="mb-1 text-gray-800 text-hover-primary">${row.instructor.name}</a>
                                </div>
                                <!--end::Info-->
                            </div>
                        `;
                    },
                },
                {
                    targets: 6, // This is the "Status" column
                    render: function (data, type, row) {
                        if (row.is_active) {
                            return `
                                     <span class="badge badge-success">${__(
                                         "active"
                                     )}</span>

                            `;
                        } else {
                            return `
                                     <span class="badge badge-danger">${__(
                                         "inactive"
                                     )}</span>
                             `;
                        }
                    },
                },

                {
                    targets: -1,
                    data: null,
                    orderable: false,
                    render: function (data, type, row) {
                        return `
        <div>
            <a href="#" class="btn btn-light btn-active-light-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-flip="top-end">
                <span class="svg-icon svg-icon-dark svg-icon-1 m-0">...</span>
            </a>
            <!--begin::Menu-->
            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-150px py-4" data-kt-menu="true">

                <!--begin::Menu item-->
                <div class="menu-item px-3">
                    <a href="javascript:;" class="menu-link px-3" data-kt-docs-table-filter="edit_row">
                        ${__("Edit")}
                    </a>
                </div>

                <!--begin::Menu item-->
                <div class="menu-item px-3">
                    <a href="/dashboard/courses/${
                        data.id
                    }" class="menu-link px-3 show_button" data-kt-docs-table-filter="show_row">
                        ${__("Show")}
                    </a>
                </div>

                <!--begin::All Sections/Classes item-->
                <div class="menu-item px-3">
                    <a href="${
                        data.is_class == 1
                            ? "/dashboard/classes?course_id=" + data.id
                            : "/dashboard/sections?course_id=" + data.id
                    }"
                       class="menu-link px-3">
                        ${
                            data.is_class == 1
                                ? __("Classes")
                                : __("Sections")
                        }
                    </a>
                </div>

                <!--begin::Menu item-->
                <div class="menu-item px-3">
                    <a href="#" class="menu-link px-3" data-kt-docs-table-filter="delete_row">
                        ${__("Delete")}
                    </a>
                </div>

            </div>
            <!--end::Menu-->
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
                $("#form_title").text(__("Edit Course"));

                $(".image-input-wrapper").css(
                    "background-image",
                    `url('${data.full_image_path}')`
                );

                $("#slide_image_inp").css(
                    "background-image",
                    `url('${data.full_slide_image_path}')`
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

                // Video URL
                $("#video_url_inp").val(data.video_url);

                // Relationships
                $("#instructor_id_inp")
                    .val(data.instructor_id)
                    .trigger("change");
                $("#category_id_inp").val(data.category_id).trigger("change");

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

                // Enrollment
                $("#max_students_inp").val(data.max_students);
                $("#enrollment_open_switch").prop(
                    "checked",
                    data.is_enrollment_open
                );

                // SEO
                $("#slug_inp").val(data.slug);
                $("#meta_title_inp").val(data.meta_title);
                $("#meta_description_inp").val(data.meta_description);

                // Dates
                $("#start_date_inp").val(data.start_date);
                $("#end_date_inp").val(data.end_date);

                // Flags
                $("#show_in_home_switch").prop("checked", data.show_in_home);
                $("#featured_switch").prop("checked", data.featured);
                $("#certificate_switch").prop(
                    "checked",
                    data.certificate_available
                );

                // If you have subcategories
                if (
                    data.subcategory_ids &&
                    Array.isArray(data.subcategory_ids)
                ) {
                    $("#subcategory_ids_inp")
                        .val(data.subcategory_ids)
                        .trigger("change");
                } else {
                    $("#subcategory_ids_inp").val([]).trigger("change");
                }

                // Reset form method & action
                $("#crud_form").attr("action", `/dashboard/courses/${data.id}`);

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
