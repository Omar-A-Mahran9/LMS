"use strict";

var datatable;
// Class definition
var KTDatatablesServerSide = (function () {
    let dbTable = "orders";
    // if(typeof userId !== 'undefined'){
    //     dbTable +='?user_id='+userId;
    //     console.log(dbTable);
    // }
    // Private functions
    var initDatatable = function () {
        datatable = $("#kt_datatable_orders").DataTable({
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
                { data: "name" },
                { data: "phone" },
                { data: "status" },

                { data: "created_at" },
                { data: null },
            ],
            columnDefs: [
                {
                    targets: 0,
                    orderable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    },
                },
                {
                    targets: 3,
                    orderable: false,
                    render: function (data, type, row) {
                        const statuses = ["pending", "approved", "rejected"];
                        const color =
                            {
                                approved: "success",
                                pending: "warning",
                                rejected: "danger",
                            }[data] ?? "secondary";

                        const dropdownItems = statuses
                            .map((status) => {
                                return `
                    <div class="menu-item px-3">
                        <a href="javascript:;" class="menu-link px-3 change-status-item "
                            data-id="${row.id}"
                            data-status="${status}">
                            ${__(status)}
                        </a>
                    </div>`;
                            })
                            .join("");

                        return `
            <div >
                <a href="#" class="badge badge-light-${color} fw-bold border rounded"
                    data-kt-menu-trigger="click"
                    data-kt-menu-placement="bottom-end">
                    ${__(data)}
                </a>
                <div class="menu menu-sub menu-sub-dropdown" data-kt-menu="true">
                    ${dropdownItems}
                </div>
            </div>
        `;
                    },
                },
                {
                    targets: -1,
                    data: null,
                    orderable: false,
                    render: function (data, type, row) {
                        return `
                        <a href="/dashboard/orders/${
                            data.id
                        }" class="btn btn-light btn-active-light-primary btn-sm ">
                            <span class="indicator-label">
                                ${__("Show")}
                            </span>
                            <i class="fa-regular fa-eye fs-4"></i>
                        </a>
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
            //initToggleToolbar();
            // if(typeof userId === 'undefined'){
            // toggleToolbars();
            // }
            KTMenu.createInstances();
        });
    };

    // Public methods
    return {
        init: function () {
            initDatatable();
            // if(typeof userId === 'undefined'){
            handleSearchDatatable();
            handleFilterRowsByColumnIndex();
            //initToggleToolbar();
            // }
        },
    };
})();

// On document ready
KTUtil.onDOMContentLoaded(function () {
    KTDatatablesServerSide.init();
});
$(document).on("click", ".change-status-item", function (e) {
    e.preventDefault();

    const id = $(this).data("id");
    const newStatus = $(this).data("status");

    $.ajax({
        url: `/dashboard/orders/${id}/status`,
        type: "POST",
        data: {
            status: newStatus,
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            if (response.success) {
                // Reload or redraw the DataTable to reflect the status change
                datatable.ajax.reload(null, false); // false to stay on the current page
            } else {
                alert(response.message || "Failed to change status.");
            }
        },
        error: function () {
            alert("An error occurred while updating status.");
        },
    });
});
